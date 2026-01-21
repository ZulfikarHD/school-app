<?php

namespace App\Http\Controllers\Parent;

use App\Http\Controllers\Controller;
use App\Http\Requests\Parent\SubmitPaymentRequest;
use App\Models\Bill;
use App\Models\Payment;
use App\Models\PaymentTransaction;
use App\Services\PaymentService;
use App\Services\PaymentTransactionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;

/**
 * Controller untuk menampilkan tagihan dan pembayaran kepada orang tua
 *
 * Orang tua dapat melihat tagihan untuk semua anak mereka,
 * status pembayaran, dan riwayat pembayaran.
 * Mendukung combined payment (1 transaksi untuk multiple bills).
 */
class PaymentController extends Controller
{
    public function __construct(
        protected PaymentService $paymentService,
        protected PaymentTransactionService $transactionService
    ) {}

    /**
     * Display bills for all children of the logged-in parent
     */
    public function index(Request $request)
    {
        $user = $request->user();

        // Get guardian record untuk user ini
        $guardian = $user->guardian;

        if (! $guardian) {
            return Inertia::render('Parent/Payments/Index', [
                'children' => [],
                'activeBills' => [],
                'paidBills' => [],
                'pendingTransactions' => [],
                'summary' => $this->getEmptySummary(),
                'message' => 'Data orang tua tidak ditemukan.',
            ]);
        }

        // Get all students linked to this guardian
        $studentIds = $guardian->students()
            ->where('status', 'aktif')
            ->pluck('students.id');

        if ($studentIds->isEmpty()) {
            return Inertia::render('Parent/Payments/Index', [
                'children' => [],
                'activeBills' => [],
                'paidBills' => [],
                'pendingTransactions' => [],
                'summary' => $this->getEmptySummary(),
                'message' => 'Tidak ada data anak yang terdaftar.',
            ]);
        }

        // Get children with basic info
        $children = $guardian->students()
            ->where('status', 'aktif')
            ->with('kelas')
            ->get(['students.id', 'students.nama_lengkap', 'students.nis', 'students.kelas_id']);

        // Get active bills (belum_bayar or sebagian)
        $activeBills = Bill::query()
            ->whereIn('student_id', $studentIds)
            ->whereIn('status', ['belum_bayar', 'sebagian'])
            ->with(['student.kelas', 'paymentCategory'])
            ->orderBy('tanggal_jatuh_tempo', 'asc')
            ->get()
            ->map(fn ($bill) => $this->formatBill($bill));

        // Get paid bills (riwayat) - limited to last 12 months
        $paidBills = Bill::query()
            ->whereIn('student_id', $studentIds)
            ->where('status', 'lunas')
            ->with(['student.kelas', 'paymentCategory'])
            ->where('updated_at', '>=', now()->subMonths(12))
            ->orderBy('updated_at', 'desc')
            ->limit(50)
            ->get()
            ->map(fn ($bill) => $this->formatBill($bill));

        // Get pending transactions (menunggu verifikasi) - NEW: Transaction-based
        $pendingTransactions = PaymentTransaction::query()
            ->where('guardian_id', $guardian->id)
            ->where('status', 'pending')
            ->with(['items.bill.paymentCategory', 'items.student.kelas'])
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(fn ($transaction) => $this->transactionService->formatTransactionForList($transaction));

        // Legacy: Get pending payments (for backward compatibility during transition)
        $pendingPayments = Payment::query()
            ->whereIn('student_id', $studentIds)
            ->where('status', 'pending')
            ->whereDoesntHave('bill.paymentItems') // Hanya yang belum di-migrate
            ->with(['bill.paymentCategory', 'student.kelas'])
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(fn ($payment) => $this->formatPendingPayment($payment));

        // Calculate summary
        $summary = $this->calculateSummary($studentIds, $guardian->id);

        return Inertia::render('Parent/Payments/Index', [
            'children' => $children,
            'activeBills' => $activeBills,
            'paidBills' => $paidBills,
            'pendingTransactions' => $pendingTransactions,
            'pendingPayments' => $pendingPayments, // Legacy support
            'summary' => $summary,
        ]);
    }

    /**
     * Format bill data for frontend
     */
    protected function formatBill(Bill $bill): array
    {
        $isOverdue = $bill->isOverdue();

        // Check if bill has pending payment (transaction-based or legacy)
        $hasPendingTransaction = $bill->paymentItems()
            ->whereHas('paymentTransaction', fn ($q) => $q->where('status', 'pending'))
            ->exists();

        $hasPendingPayment = $hasPendingTransaction || $bill->payments()
            ->where('status', 'pending')
            ->exists();

        return [
            'id' => $bill->id,
            'nomor_tagihan' => $bill->nomor_tagihan,
            'student' => [
                'id' => $bill->student->id,
                'nama_lengkap' => $bill->student->nama_lengkap,
                'nis' => $bill->student->nis,
                'kelas' => $bill->student->kelas?->nama_lengkap ?? '-',
            ],
            'category' => [
                'id' => $bill->paymentCategory->id,
                'nama' => $bill->paymentCategory->nama,
                'kode' => $bill->paymentCategory->kode,
            ],
            'bulan' => $bill->bulan,
            'tahun' => $bill->tahun,
            'nama_bulan' => $bill->nama_bulan,
            'periode' => $bill->nama_bulan.' '.$bill->tahun,
            'nominal' => (float) $bill->nominal,
            'nominal_terbayar' => (float) $bill->nominal_terbayar,
            'sisa_tagihan' => $bill->sisa_tagihan,
            'formatted_nominal' => $bill->formatted_nominal,
            'formatted_sisa' => $bill->formatted_sisa,
            'status' => $bill->status,
            'status_label' => $bill->status_label,
            'is_overdue' => $isOverdue,
            'has_pending_payment' => $hasPendingPayment,
            'tanggal_jatuh_tempo' => $bill->tanggal_jatuh_tempo?->format('Y-m-d'),
            'formatted_due_date' => $bill->tanggal_jatuh_tempo?->format('d M Y'),
            'created_at' => $bill->created_at?->format('Y-m-d H:i:s'),
        ];
    }

    /**
     * Format pending payment data for frontend (legacy)
     */
    protected function formatPendingPayment(Payment $payment): array
    {
        return [
            'id' => $payment->id,
            'nomor_kwitansi' => $payment->nomor_kwitansi,
            'bill' => [
                'id' => $payment->bill->id,
                'nomor_tagihan' => $payment->bill->nomor_tagihan,
                'category' => $payment->bill->paymentCategory->nama,
                'periode' => $payment->bill->nama_bulan.' '.$payment->bill->tahun,
            ],
            'student' => [
                'id' => $payment->student->id,
                'nama_lengkap' => $payment->student->nama_lengkap,
                'nis' => $payment->student->nis,
                'kelas' => $payment->student->kelas?->nama_lengkap ?? '-',
            ],
            'nominal' => (float) $payment->nominal,
            'formatted_nominal' => 'Rp '.number_format($payment->nominal, 0, ',', '.'),
            'metode_pembayaran' => $payment->metode_pembayaran,
            'tanggal_bayar' => $payment->tanggal_bayar?->format('d M Y'),
            'status' => $payment->status,
            'status_label' => 'Menunggu Verifikasi',
            'created_at' => $payment->created_at?->format('d M Y H:i'),
            'has_bukti' => ! empty($payment->bukti_transfer),
        ];
    }

    /**
     * Calculate payment summary for students
     */
    protected function calculateSummary($studentIds, int $guardianId): array
    {
        // Total tagihan belum lunas
        $unpaidBills = Bill::query()
            ->whereIn('student_id', $studentIds)
            ->whereIn('status', ['belum_bayar', 'sebagian'])
            ->get();

        $totalTunggakan = $unpaidBills->sum(fn ($bill) => $bill->sisa_tagihan);
        $totalBelumBayar = $unpaidBills->where('status', 'belum_bayar')->count();
        $totalSebagian = $unpaidBills->where('status', 'sebagian')->count();

        // Overdue count
        $overdueCount = $unpaidBills->filter(fn ($bill) => $bill->isOverdue())->count();

        // Tagihan terdekat (nearest due date)
        $nearestBill = $unpaidBills
            ->sortBy('tanggal_jatuh_tempo')
            ->first();

        // Total lunas bulan ini
        $paidThisMonth = Bill::query()
            ->whereIn('student_id', $studentIds)
            ->where('status', 'lunas')
            ->whereMonth('updated_at', now()->month)
            ->whereYear('updated_at', now()->year)
            ->count();

        // Count pending transactions (NEW)
        $pendingTransactionCount = PaymentTransaction::query()
            ->where('guardian_id', $guardianId)
            ->where('status', 'pending')
            ->count();

        // Legacy pending payments count
        $pendingPaymentCount = Payment::query()
            ->whereIn('student_id', $studentIds)
            ->where('status', 'pending')
            ->count();

        return [
            'total_tunggakan' => $totalTunggakan,
            'formatted_tunggakan' => 'Rp '.number_format($totalTunggakan, 0, ',', '.'),
            'total_belum_bayar' => $totalBelumBayar,
            'total_sebagian' => $totalSebagian,
            'total_overdue' => $overdueCount,
            'total_lunas_bulan_ini' => $paidThisMonth,
            'pending_count' => $pendingTransactionCount + $pendingPaymentCount,
            'pending_transaction_count' => $pendingTransactionCount,
            'nearest_due_date' => $nearestBill?->tanggal_jatuh_tempo?->format('d M Y'),
            'nearest_bill' => $nearestBill ? [
                'category' => $nearestBill->paymentCategory->nama,
                'periode' => $nearestBill->nama_bulan.' '.$nearestBill->tahun,
                'nominal' => 'Rp '.number_format($nearestBill->sisa_tagihan, 0, ',', '.'),
            ] : null,
        ];
    }

    /**
     * Get empty summary for when no data is available
     */
    protected function getEmptySummary(): array
    {
        return [
            'total_tunggakan' => 0,
            'formatted_tunggakan' => 'Rp 0',
            'total_belum_bayar' => 0,
            'total_sebagian' => 0,
            'total_overdue' => 0,
            'total_lunas_bulan_ini' => 0,
            'pending_count' => 0,
            'pending_transaction_count' => 0,
            'nearest_due_date' => null,
            'nearest_bill' => null,
        ];
    }

    /**
     * Display payment history for all children (transaction-centric view)
     */
    public function history(Request $request)
    {
        $user = $request->user();
        $guardian = $user->guardian;

        if (! $guardian) {
            return Inertia::render('Parent/Payments/History', [
                'transactions' => [],
                'payments' => [], // Legacy
                'children' => [],
                'message' => 'Data orang tua tidak ditemukan.',
            ]);
        }

        $studentIds = $guardian->students()
            ->where('status', 'aktif')
            ->pluck('students.id');

        if ($studentIds->isEmpty()) {
            return Inertia::render('Parent/Payments/History', [
                'transactions' => [],
                'payments' => [], // Legacy
                'children' => [],
                'message' => 'Tidak ada data anak yang terdaftar.',
            ]);
        }

        $children = $guardian->students()
            ->where('status', 'aktif')
            ->with('kelas')
            ->get(['students.id', 'students.nama_lengkap', 'students.nis', 'students.kelas_id']);

        // Get verified transactions for this guardian (NEW)
        $transactions = PaymentTransaction::query()
            ->where('guardian_id', $guardian->id)
            ->where('status', 'verified')
            ->with(['items.bill.paymentCategory', 'items.student.kelas', 'verifier'])
            ->orderBy('payment_date', 'desc')
            ->orderBy('created_at', 'desc')
            ->paginate(20)
            ->withQueryString();

        // Transform transactions
        $transactions->getCollection()->transform(
            fn ($transaction) => $this->transactionService->formatTransactionForList($transaction)
        );

        // Legacy: Get verified payments that don't have associated transactions
        $payments = Payment::query()
            ->whereIn('student_id', $studentIds)
            ->where('status', 'verified')
            ->whereDoesntHave('bill.paymentItems.paymentTransaction', fn ($q) => $q->where('guardian_id', $guardian->id))
            ->with(['bill.paymentCategory', 'student.kelas'])
            ->orderBy('tanggal_bayar', 'desc')
            ->orderBy('created_at', 'desc')
            ->limit(50)
            ->get()
            ->map(fn ($payment) => $this->formatPayment($payment));

        return Inertia::render('Parent/Payments/History', [
            'transactions' => $transactions,
            'payments' => $payments, // Legacy support
            'children' => $children,
        ]);
    }

    /**
     * Download receipt PDF for a transaction
     */
    public function downloadTransactionReceipt(PaymentTransaction $transaction, Request $request)
    {
        $user = $request->user();
        $guardian = $user->guardian;

        if (! $guardian) {
            abort(403, 'Anda tidak memiliki akses ke kwitansi ini.');
        }

        // Verify transaction belongs to this guardian
        if ($transaction->guardian_id !== $guardian->id) {
            abort(403, 'Anda tidak memiliki akses ke kwitansi ini.');
        }

        // Only allow verified transactions to be downloaded
        if ($transaction->status !== 'verified') {
            abort(404, 'Kwitansi tidak tersedia.');
        }

        try {
            $pdf = $this->transactionService->generateReceiptPdf($transaction);

            $filename = "Kwitansi-{$transaction->transaction_number}.pdf";
            $filename = str_replace('/', '-', $filename);

            return $pdf->download($filename);
        } catch (\Exception $e) {
            Log::error('Failed to generate transaction receipt PDF for parent', [
                'transaction_id' => $transaction->id,
                'user_id' => $user->id,
                'error' => $e->getMessage(),
            ]);

            return back()->withErrors(['error' => 'Gagal mengunduh kwitansi.']);
        }
    }

    /**
     * Download receipt PDF for a payment (legacy)
     */
    public function downloadReceipt(Payment $payment, Request $request)
    {
        $user = $request->user();
        $guardian = $user->guardian;

        if (! $guardian) {
            abort(403, 'Anda tidak memiliki akses ke kwitansi ini.');
        }

        // Get all student IDs belonging to this parent
        $studentIds = $guardian->students()
            ->where('status', 'aktif')
            ->pluck('students.id')
            ->toArray();

        // Verify payment belongs to one of parent's children
        if (! in_array($payment->student_id, $studentIds)) {
            abort(403, 'Anda tidak memiliki akses ke kwitansi ini.');
        }

        // Only allow verified payments to be downloaded
        if ($payment->status !== 'verified') {
            abort(404, 'Kwitansi tidak tersedia.');
        }

        try {
            $pdf = $this->paymentService->generateReceiptPdf($payment);

            $filename = "Kwitansi-{$payment->nomor_kwitansi}.pdf";
            $filename = str_replace('/', '-', $filename);

            return $pdf->download($filename);
        } catch (\Exception $e) {
            Log::error('Failed to generate receipt PDF for parent', [
                'payment_id' => $payment->id,
                'user_id' => $user->id,
                'error' => $e->getMessage(),
            ]);

            return back()->withErrors(['error' => 'Gagal mengunduh kwitansi.']);
        }
    }

    /**
     * Format payment data for frontend (legacy)
     */
    protected function formatPayment(Payment $payment): array
    {
        return [
            'id' => $payment->id,
            'nomor_kwitansi' => $payment->nomor_kwitansi,
            'student' => [
                'id' => $payment->student->id,
                'nama_lengkap' => $payment->student->nama_lengkap,
                'nis' => $payment->student->nis,
                'kelas' => $payment->student->kelas?->nama_lengkap ?? '-',
            ],
            'bill' => [
                'id' => $payment->bill->id,
                'nomor_tagihan' => $payment->bill->nomor_tagihan,
                'category' => $payment->bill->paymentCategory->nama,
                'periode' => $payment->bill->nama_bulan.' '.$payment->bill->tahun,
            ],
            'nominal' => (float) $payment->nominal,
            'formatted_nominal' => $payment->formatted_nominal,
            'metode_pembayaran' => $payment->metode_pembayaran,
            'metode_label' => $payment->metode_label,
            'tanggal_bayar' => $payment->tanggal_bayar?->format('Y-m-d'),
            'formatted_tanggal' => $payment->tanggal_bayar?->format('d M Y'),
            'waktu_bayar' => $payment->waktu_bayar?->format('H:i'),
            'status' => $payment->status,
            'status_label' => $payment->status_label,
            'created_at' => $payment->created_at?->format('d M Y H:i'),
        ];
    }

    /**
     * Menampilkan halaman submit pembayaran dengan tagihan terpilih
     *
     * Halaman ini menampilkan form untuk upload bukti transfer
     * dengan ringkasan tagihan yang akan dibayar
     */
    public function showSubmit(Request $request)
    {
        $user = $request->user();
        $guardian = $user->guardian;

        if (! $guardian) {
            return redirect()->route('parent.payments.index')
                ->with('error', 'Data orang tua tidak ditemukan.');
        }

        // Get student IDs belonging to this parent
        $studentIds = $guardian->students()
            ->where('status', 'aktif')
            ->pluck('students.id');

        // Get selected bill IDs from query param
        $billIds = $request->query('bills', []);
        if (is_string($billIds)) {
            $billIds = explode(',', $billIds);
        }

        if (empty($billIds)) {
            return redirect()->route('parent.payments.index')
                ->with('error', 'Pilih tagihan yang akan dibayar.');
        }

        // Get bills with validation
        $bills = Bill::query()
            ->whereIn('id', $billIds)
            ->whereIn('student_id', $studentIds)
            ->whereIn('status', ['belum_bayar', 'sebagian'])
            ->with(['student.kelas', 'paymentCategory'])
            ->get()
            ->map(fn ($bill) => $this->formatBill($bill));

        if ($bills->isEmpty()) {
            return redirect()->route('parent.payments.index')
                ->with('error', 'Tagihan tidak valid atau sudah lunas.');
        }

        // Calculate total
        $totalAmount = $bills->sum('sisa_tagihan');

        // Bank info (configurable via settings in future)
        $bankInfo = [
            'bank_name' => 'BCA',
            'account_number' => '1234567890',
            'account_name' => 'Yayasan Pendidikan ABC',
            'note' => 'Pastikan transfer sesuai dengan total tagihan. Simpan bukti transfer untuk diupload.',
        ];

        return Inertia::render('Parent/Payments/Submit', [
            'bills' => $bills,
            'totalAmount' => $totalAmount,
            'formattedTotal' => 'Rp '.number_format($totalAmount, 0, ',', '.'),
            'bankInfo' => $bankInfo,
        ]);
    }

    /**
     * Submit pembayaran dengan bukti transfer
     *
     * UPDATED: Membuat 1 PaymentTransaction dengan N PaymentItems
     * untuk semua tagihan terpilih (combined payment)
     */
    public function submitPayment(SubmitPaymentRequest $request)
    {
        $user = $request->user();
        $guardian = $user->guardian;

        // Get student IDs for authorization check
        $studentIds = $guardian->students()
            ->where('status', 'aktif')
            ->pluck('students.id');

        // Validate bills belong to parent's children
        $validBillIds = Bill::query()
            ->whereIn('id', $request->bill_ids)
            ->whereIn('student_id', $studentIds)
            ->whereIn('status', ['belum_bayar', 'sebagian'])
            ->pluck('id')
            ->toArray();

        if (empty($validBillIds)) {
            return back()->withErrors(['error' => 'Tagihan tidak valid atau sudah lunas.']);
        }

        // Upload bukti transfer
        $buktiPath = null;
        if ($request->hasFile('bukti_transfer')) {
            $file = $request->file('bukti_transfer');
            $filename = 'bukti_'.now()->format('Ymd_His').'_'.uniqid().'.'.$file->getClientOriginalExtension();
            $buktiPath = $file->storeAs('payment-proofs', $filename, 'public');
        }

        // Create transaction using service
        $result = $this->transactionService->createTransaction([
            'bills' => $validBillIds,
            'payment_method' => 'transfer',
            'payment_date' => $request->tanggal_bayar,
            'payment_time' => now()->format('H:i:s'),
            'proof_file' => $buktiPath,
            'notes' => $request->catatan ?? 'Upload bukti transfer oleh orang tua',
        ], $guardian);

        if (! $result['success']) {
            // Clean up uploaded file if exists
            if ($buktiPath) {
                Storage::disk('public')->delete($buktiPath);
            }

            return back()->withErrors(['error' => $result['message']]);
        }

        Log::info('Parent submitted combined payment transaction', [
            'user_id' => $user->id,
            'guardian_id' => $guardian->id,
            'transaction_id' => $result['transaction']->id,
            'transaction_number' => $result['transaction']->transaction_number,
            'bill_count' => count($validBillIds),
            'total_amount' => $result['transaction']->total_amount,
        ]);

        return redirect()->route('parent.payments.index')
            ->with('success', 'Pembayaran berhasil disubmit. Menunggu verifikasi dari Admin.');
    }

    /**
     * Get pending payments/transactions for parent
     */
    public function pendingPayments(Request $request)
    {
        $user = $request->user();
        $guardian = $user->guardian;

        if (! $guardian) {
            return Inertia::render('Parent/Payments/Pending', [
                'transactions' => [],
                'payments' => [],
                'message' => 'Data orang tua tidak ditemukan.',
            ]);
        }

        $studentIds = $guardian->students()
            ->where('status', 'aktif')
            ->pluck('students.id');

        // Get pending transactions (NEW)
        $pendingTransactions = PaymentTransaction::query()
            ->where('guardian_id', $guardian->id)
            ->where('status', 'pending')
            ->with(['items.bill.paymentCategory', 'items.student.kelas'])
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(fn ($transaction) => $this->formatTransactionWithProof($transaction));

        // Legacy: Get pending payments
        $pendingPayments = Payment::query()
            ->whereIn('student_id', $studentIds)
            ->where('status', 'pending')
            ->with(['bill.paymentCategory', 'student.kelas'])
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(fn ($payment) => $this->formatPaymentWithProof($payment));

        return Inertia::render('Parent/Payments/Pending', [
            'transactions' => $pendingTransactions,
            'payments' => $pendingPayments, // Legacy support
        ]);
    }

    /**
     * Get transaction detail
     */
    public function showTransaction(PaymentTransaction $transaction, Request $request)
    {
        $user = $request->user();
        $guardian = $user->guardian;

        if (! $guardian || $transaction->guardian_id !== $guardian->id) {
            abort(403, 'Anda tidak memiliki akses ke transaksi ini.');
        }

        $transaction->load([
            'items.bill.paymentCategory',
            'items.student.kelas',
            'verifier',
            'canceller',
        ]);

        return Inertia::render('Parent/Payments/TransactionDetail', [
            'transaction' => $this->transactionService->formatTransactionForResponse($transaction),
        ]);
    }

    /**
     * Cancel pending transaction by parent
     */
    public function cancelTransaction(PaymentTransaction $transaction, Request $request)
    {
        $user = $request->user();
        $guardian = $user->guardian;

        if (! $guardian || $transaction->guardian_id !== $guardian->id) {
            abort(403, 'Anda tidak memiliki akses ke transaksi ini.');
        }

        if ($transaction->status !== 'pending') {
            return back()->withErrors(['error' => 'Hanya transaksi dengan status menunggu verifikasi yang dapat dibatalkan.']);
        }

        $result = $this->transactionService->cancelTransaction(
            $transaction,
            'Dibatalkan oleh orang tua',
            $user->id
        );

        if (! $result['success']) {
            return back()->withErrors(['error' => $result['message']]);
        }

        // Delete proof file if exists
        if ($transaction->proof_file) {
            Storage::disk('public')->delete($transaction->proof_file);
        }

        Log::info('Parent cancelled payment transaction', [
            'user_id' => $user->id,
            'guardian_id' => $guardian->id,
            'transaction_id' => $transaction->id,
        ]);

        return redirect()->route('parent.payments.index')
            ->with('success', 'Transaksi berhasil dibatalkan.');
    }

    /**
     * Format transaction with proof URL for frontend
     */
    protected function formatTransactionWithProof(PaymentTransaction $transaction): array
    {
        $data = $this->transactionService->formatTransactionForList($transaction);
        $data['proof_file_url'] = $transaction->proof_file
            ? Storage::disk('public')->url($transaction->proof_file)
            : null;

        return $data;
    }

    /**
     * Format payment with proof URL for frontend (legacy)
     */
    protected function formatPaymentWithProof(Payment $payment): array
    {
        $data = $this->formatPayment($payment);
        $data['bukti_transfer_url'] = $payment->bukti_transfer
            ? Storage::disk('public')->url($payment->bukti_transfer)
            : null;

        return $data;
    }
}
