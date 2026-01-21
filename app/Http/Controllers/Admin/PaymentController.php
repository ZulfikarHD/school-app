<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StorePaymentRequest;
use App\Models\ActivityLog;
use App\Models\Payment;
use App\Models\Student;
use App\Services\PaymentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;

/**
 * Controller untuk mengelola pembayaran siswa (Admin/TU)
 *
 * Menyediakan fitur pencatatan pembayaran, daftar pembayaran,
 * verifikasi, pembatalan, dan generate kwitansi
 */
class PaymentController extends Controller
{
    public function __construct(
        protected PaymentService $paymentService
    ) {}

    /**
     * Display list of payments dengan pagination dan filter
     */
    public function index(Request $request)
    {
        $query = Payment::query()
            ->with(['bill.paymentCategory', 'student.kelas', 'creator', 'verifier'])
            ->active();

        // Search by nomor kwitansi atau nama siswa
        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('nomor_kwitansi', 'like', "%{$search}%")
                    ->orWhereHas('student', function ($sq) use ($search) {
                        $sq->where('nama_lengkap', 'like', "%{$search}%")
                            ->orWhere('nis', 'like', "%{$search}%");
                    });
            });
        }

        // Filter by status
        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }

        // Filter by metode pembayaran
        if ($metode = $request->input('metode')) {
            $query->where('metode_pembayaran', $metode);
        }

        // Filter by date range
        if ($startDate = $request->input('start_date')) {
            $query->whereDate('tanggal_bayar', '>=', $startDate);
        }
        if ($endDate = $request->input('end_date')) {
            $query->whereDate('tanggal_bayar', '<=', $endDate);
        }

        // Filter by single date (for today view)
        if ($date = $request->input('date')) {
            $query->whereDate('tanggal_bayar', $date);
        }

        $payments = $query->orderBy('created_at', 'desc')
            ->paginate(20)
            ->withQueryString();

        // Transform data
        $payments->getCollection()->transform(function ($payment) {
            return $this->paymentService->formatPaymentForResponse($payment);
        });

        // Get statistics for today
        $todayStats = $this->paymentService->getPaymentStatistics(now()->toDateString());

        // Get pending verification count
        $pendingCount = Payment::pending()->count();

        return Inertia::render('Admin/Payments/Payments/Index', [
            'payments' => $payments,
            'stats' => [
                'today' => $todayStats,
                'pending_verification' => $pendingCount,
            ],
            'filters' => $request->only(['search', 'status', 'metode', 'start_date', 'end_date', 'date']),
        ]);
    }

    /**
     * Show form untuk catat pembayaran baru
     */
    public function create(Request $request)
    {
        $studentId = $request->input('student_id');
        $selectedStudent = null;
        $unpaidBills = collect();

        if ($studentId) {
            $student = Student::with(['kelas'])->find($studentId);
            if ($student) {
                $selectedStudent = [
                    'id' => $student->id,
                    'nis' => $student->nis,
                    'nama_lengkap' => $student->nama_lengkap,
                    'kelas' => $student->kelas?->nama_lengkap ?? '-',
                    'display_label' => "{$student->nama_lengkap} - {$student->nis}",
                ];
                $unpaidBills = $this->paymentService->getStudentUnpaidBills($studentId);
            }
        }

        return Inertia::render('Admin/Payments/Payments/Create', [
            'selectedStudent' => $selectedStudent,
            'unpaidBills' => $unpaidBills,
            'paymentMethods' => [
                ['value' => 'tunai', 'label' => 'Tunai'],
                ['value' => 'transfer', 'label' => 'Transfer Bank'],
                ['value' => 'qris', 'label' => 'QRIS'],
            ],
        ]);
    }

    /**
     * Store pembayaran baru
     */
    public function store(StorePaymentRequest $request)
    {
        $result = $this->paymentService->recordPayment($request->validated());

        if ($result['success']) {
            // Log activity
            ActivityLog::create([
                'user_id' => auth()->id(),
                'action' => 'record_payment',
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'new_values' => [
                    'payment_id' => $result['payment']->id,
                    'nomor_kwitansi' => $result['payment']->nomor_kwitansi,
                    'nominal' => $result['payment']->nominal,
                    'metode' => $result['payment']->metode_pembayaran,
                ],
                'status' => 'success',
            ]);

            // Return dengan payment data untuk receipt preview
            return redirect()->route('admin.payments.records.show', $result['payment']->id)
                ->with('success', "Pembayaran berhasil dicatat. No. Kwitansi: {$result['payment']->nomor_kwitansi}");
        }

        return back()->withErrors(['error' => $result['message']])->withInput();
    }

    /**
     * Show detail pembayaran
     */
    public function show(Payment $payment)
    {
        $payment->load(['bill.paymentCategory', 'student.kelas', 'creator', 'verifier', 'canceller']);

        $formattedPayment = $this->paymentService->formatPaymentForResponse($payment);

        // Get receipt data for preview
        $receiptData = $this->paymentService->getReceiptData($payment);

        return Inertia::render('Admin/Payments/Payments/Show', [
            'payment' => $formattedPayment,
            'receiptData' => $receiptData,
            'canVerify' => $payment->canBeVerified(),
            'canCancel' => $payment->canBeCancelled(),
        ]);
    }

    /**
     * Download receipt PDF
     */
    public function receipt(Payment $payment)
    {
        try {
            $pdf = $this->paymentService->generateReceiptPdf($payment);

            $filename = "Kwitansi-{$payment->nomor_kwitansi}.pdf";
            $filename = str_replace('/', '-', $filename);

            return $pdf->download($filename);
        } catch (\Exception $e) {
            Log::error('Failed to generate receipt PDF', [
                'payment_id' => $payment->id,
                'error' => $e->getMessage(),
            ]);

            return back()->withErrors(['error' => 'Gagal generate kwitansi PDF.']);
        }
    }

    /**
     * Stream receipt PDF for print preview
     */
    public function receiptStream(Payment $payment)
    {
        try {
            $pdf = $this->paymentService->generateReceiptPdf($payment);

            return $pdf->stream("Kwitansi-{$payment->nomor_kwitansi}.pdf");
        } catch (\Exception $e) {
            Log::error('Failed to stream receipt PDF', [
                'payment_id' => $payment->id,
                'error' => $e->getMessage(),
            ]);

            return back()->withErrors(['error' => 'Gagal menampilkan kwitansi PDF.']);
        }
    }

    /**
     * Verify pending payment
     */
    public function verify(Payment $payment, Request $request)
    {
        $result = $this->paymentService->verifyPayment($payment);

        if ($result['success']) {
            ActivityLog::create([
                'user_id' => auth()->id(),
                'action' => 'verify_payment',
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'new_values' => [
                    'payment_id' => $payment->id,
                    'nomor_kwitansi' => $payment->nomor_kwitansi,
                ],
                'status' => 'success',
            ]);

            return back()->with('success', $result['message']);
        }

        return back()->withErrors(['error' => $result['message']]);
    }

    /**
     * Cancel payment
     */
    public function cancel(Payment $payment, Request $request)
    {
        $request->validate([
            'reason' => 'required|string|max:500',
        ], [
            'reason.required' => 'Alasan pembatalan harus diisi.',
            'reason.max' => 'Alasan maksimal 500 karakter.',
        ]);

        $result = $this->paymentService->cancelPayment($payment, $request->reason);

        if ($result['success']) {
            ActivityLog::create([
                'user_id' => auth()->id(),
                'action' => 'cancel_payment',
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'old_values' => [
                    'payment_id' => $payment->id,
                    'nomor_kwitansi' => $payment->nomor_kwitansi,
                    'status' => 'verified',
                ],
                'new_values' => [
                    'status' => 'cancelled',
                    'reason' => $request->reason,
                ],
                'status' => 'success',
            ]);

            return redirect()->route('admin.payments.records.index')
                ->with('success', $result['message']);
        }

        return back()->withErrors(['error' => $result['message']]);
    }

    /**
     * API: Search students for autocomplete
     */
    public function searchStudents(Request $request)
    {
        $query = $request->input('q', '');
        $students = $this->paymentService->searchStudents($query);

        return response()->json($students);
    }

    /**
     * API: Get unpaid bills for a student
     */
    public function getUnpaidBills(Student $student)
    {
        $bills = $this->paymentService->getStudentUnpaidBills($student->id);

        return response()->json([
            'student' => [
                'id' => $student->id,
                'nis' => $student->nis,
                'nama_lengkap' => $student->nama_lengkap,
                'kelas' => $student->kelas?->nama_lengkap ?? '-',
            ],
            'bills' => $bills,
            'total_tunggakan' => $bills->sum('sisa_tagihan'),
            'formatted_tunggakan' => 'Rp '.number_format($bills->sum('sisa_tagihan'), 0, ',', '.'),
        ]);
    }

    /**
     * Page: Verification queue
     */
    public function verification(Request $request)
    {
        $query = Payment::query()
            ->with(['bill.paymentCategory', 'student.kelas', 'creator'])
            ->pending();

        // Search
        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('nomor_kwitansi', 'like', "%{$search}%")
                    ->orWhereHas('student', function ($sq) use ($search) {
                        $sq->where('nama_lengkap', 'like', "%{$search}%")
                            ->orWhere('nis', 'like', "%{$search}%");
                    });
            });
        }

        $payments = $query->orderBy('created_at', 'asc')
            ->paginate(20)
            ->withQueryString();

        $payments->getCollection()->transform(function ($payment) {
            return $this->paymentService->formatPaymentForResponse($payment);
        });

        return Inertia::render('Admin/Payments/Payments/Verification', [
            'payments' => $payments,
            'filters' => $request->only(['search']),
        ]);
    }
}
