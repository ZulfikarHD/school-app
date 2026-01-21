<?php

namespace App\Services;

use App\Models\Bill;
use App\Models\Guardian;
use App\Models\PaymentItem;
use App\Models\PaymentTransaction;
use App\Models\Student;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Service untuk mengelola transaksi pembayaran (combined payment)
 *
 * Mendukung bulk payment dimana satu transaksi bisa mencakup
 * pembayaran untuk multiple tagihan (bills) dengan satu bukti transfer.
 */
class PaymentTransactionService
{
    /**
     * Create transaksi pembayaran baru (untuk Parent submission)
     *
     * @param  array  $data  Data transaksi (bills, payment_method, payment_date, proof_file, notes)
     * @param  Guardian|null  $guardian  Guardian yang submit
     * @return array{success: bool, transaction: ?PaymentTransaction, message: string}
     */
    public function createTransaction(array $data, ?Guardian $guardian = null): array
    {
        // Validate bills exist and can be paid
        $bills = Bill::with(['student', 'paymentCategory'])
            ->whereIn('id', $data['bills'])
            ->get();

        if ($bills->count() !== count($data['bills'])) {
            return [
                'success' => false,
                'transaction' => null,
                'message' => 'Beberapa tagihan tidak ditemukan.',
            ];
        }

        // Validate all bills can be paid
        foreach ($bills as $bill) {
            if (! $bill->canBePaid()) {
                return [
                    'success' => false,
                    'transaction' => null,
                    'message' => "Tagihan {$bill->nomor_tagihan} tidak dapat dibayar (status: {$bill->status_label}).",
                ];
            }
        }

        // Calculate total amount (using remaining balance for each bill)
        $totalAmount = $bills->sum('sisa_tagihan');

        DB::beginTransaction();

        try {
            // Determine initial status based on payment method
            // Cash payments are auto-verified, transfer/qris need verification
            $status = $data['payment_method'] === 'tunai' ? 'verified' : 'pending';

            // Create transaction
            $transaction = PaymentTransaction::create([
                'transaction_number' => PaymentTransaction::generateTransactionNumber(),
                'guardian_id' => $guardian?->id,
                'total_amount' => $totalAmount,
                'payment_method' => $data['payment_method'],
                'payment_date' => $data['payment_date'],
                'payment_time' => $data['payment_time'] ?? now()->format('H:i:s'),
                'proof_file' => $data['proof_file'] ?? null,
                'notes' => $data['notes'] ?? null,
                'status' => $status,
                'verified_by' => $status === 'verified' ? auth()->id() : null,
                'verified_at' => $status === 'verified' ? now() : null,
            ]);

            // Create payment items for each bill
            foreach ($bills as $bill) {
                PaymentItem::create([
                    'payment_transaction_id' => $transaction->id,
                    'bill_id' => $bill->id,
                    'student_id' => $bill->student_id,
                    'amount' => $bill->sisa_tagihan,
                ]);
            }

            DB::commit();

            // Load relationships for response
            $transaction->load(['items.bill.paymentCategory', 'items.student.kelas', 'guardian', 'creator']);

            return [
                'success' => true,
                'transaction' => $transaction,
                'message' => 'Pembayaran berhasil disubmit. '.($status === 'pending' ? 'Menunggu verifikasi admin.' : 'Pembayaran terverifikasi.'),
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to create payment transaction', [
                'bills' => $data['bills'],
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return [
                'success' => false,
                'transaction' => null,
                'message' => 'Gagal mencatat pembayaran: '.$e->getMessage(),
            ];
        }
    }

    /**
     * Create transaksi pembayaran manual oleh Admin
     *
     * @param  array  $data  Data transaksi dengan student_id dan bills yang dipilih
     * @return array{success: bool, transaction: ?PaymentTransaction, message: string}
     */
    public function createManualTransaction(array $data): array
    {
        // Validate bills exist and belong to the student if specified
        $billsQuery = Bill::with(['student', 'paymentCategory'])
            ->whereIn('id', $data['bills']);

        if (isset($data['student_id'])) {
            $billsQuery->where('student_id', $data['student_id']);
        }

        $bills = $billsQuery->get();

        if ($bills->isEmpty()) {
            return [
                'success' => false,
                'transaction' => null,
                'message' => 'Tagihan tidak ditemukan atau tidak sesuai dengan siswa.',
            ];
        }

        // Validate all bills can be paid
        foreach ($bills as $bill) {
            if (! $bill->canBePaid()) {
                return [
                    'success' => false,
                    'transaction' => null,
                    'message' => "Tagihan {$bill->nomor_tagihan} tidak dapat dibayar (status: {$bill->status_label}).",
                ];
            }
        }

        // Calculate total amount based on provided amounts or remaining balance
        $totalAmount = 0;
        $billAmounts = $data['amounts'] ?? [];

        foreach ($bills as $bill) {
            $amount = $billAmounts[$bill->id] ?? $bill->sisa_tagihan;

            // Validate amount doesn't exceed remaining
            if ($amount > $bill->sisa_tagihan) {
                return [
                    'success' => false,
                    'transaction' => null,
                    'message' => "Nominal untuk tagihan {$bill->nomor_tagihan} melebihi sisa tagihan.",
                ];
            }

            $totalAmount += $amount;
        }

        DB::beginTransaction();

        try {
            // Determine initial status based on payment method
            $status = $data['payment_method'] === 'tunai' ? 'verified' : 'pending';

            // Create transaction
            $transaction = PaymentTransaction::create([
                'transaction_number' => PaymentTransaction::generateTransactionNumber(),
                'guardian_id' => null, // Admin manual entry
                'total_amount' => $totalAmount,
                'payment_method' => $data['payment_method'],
                'payment_date' => $data['payment_date'],
                'payment_time' => $data['payment_time'] ?? now()->format('H:i:s'),
                'proof_file' => $data['proof_file'] ?? null,
                'notes' => $data['notes'] ?? null,
                'status' => $status,
                'verified_by' => $status === 'verified' ? auth()->id() : null,
                'verified_at' => $status === 'verified' ? now() : null,
            ]);

            // Create payment items for each bill
            foreach ($bills as $bill) {
                $amount = $billAmounts[$bill->id] ?? $bill->sisa_tagihan;

                PaymentItem::create([
                    'payment_transaction_id' => $transaction->id,
                    'bill_id' => $bill->id,
                    'student_id' => $bill->student_id,
                    'amount' => $amount,
                ]);
            }

            DB::commit();

            // Load relationships for response
            $transaction->load(['items.bill.paymentCategory', 'items.student.kelas', 'creator']);

            return [
                'success' => true,
                'transaction' => $transaction,
                'message' => 'Pembayaran berhasil dicatat.',
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to create manual payment transaction', [
                'bills' => $data['bills'],
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return [
                'success' => false,
                'transaction' => null,
                'message' => 'Gagal mencatat pembayaran: '.$e->getMessage(),
            ];
        }
    }

    /**
     * Verify transaksi pembayaran
     *
     * @param  PaymentTransaction  $transaction  Transaction to verify
     * @param  int|null  $userId  User yang memverifikasi
     * @return array{success: bool, message: string}
     */
    public function verifyTransaction(PaymentTransaction $transaction, ?int $userId = null): array
    {
        if (! $transaction->canBeVerified()) {
            return [
                'success' => false,
                'message' => 'Transaksi tidak dapat diverifikasi (status: '.$transaction->status_label.').',
            ];
        }

        try {
            DB::beginTransaction();

            $transaction->verify($userId);

            DB::commit();

            return [
                'success' => true,
                'message' => 'Transaksi berhasil diverifikasi. '.$transaction->items->count().' tagihan telah diupdate.',
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to verify transaction', [
                'transaction_id' => $transaction->id,
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'message' => 'Gagal memverifikasi transaksi.',
            ];
        }
    }

    /**
     * Cancel transaksi pembayaran
     *
     * @param  PaymentTransaction  $transaction  Transaction to cancel
     * @param  string  $reason  Alasan pembatalan
     * @param  int|null  $userId  User yang membatalkan
     * @return array{success: bool, message: string}
     */
    public function cancelTransaction(PaymentTransaction $transaction, string $reason, ?int $userId = null): array
    {
        if (! $transaction->canBeCancelled()) {
            return [
                'success' => false,
                'message' => 'Transaksi tidak dapat dibatalkan.',
            ];
        }

        try {
            DB::beginTransaction();

            $transaction->cancel($reason, $userId);

            DB::commit();

            return [
                'success' => true,
                'message' => 'Transaksi berhasil dibatalkan.',
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to cancel transaction', [
                'transaction_id' => $transaction->id,
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'message' => 'Gagal membatalkan transaksi.',
            ];
        }
    }

    /**
     * Get transaction by ID with relationships
     */
    public function getTransaction(int $transactionId): ?PaymentTransaction
    {
        return PaymentTransaction::query()
            ->with([
                'items.bill.paymentCategory',
                'items.student.kelas',
                'guardian.user',
                'creator',
                'verifier',
                'canceller',
            ])
            ->find($transactionId);
    }

    /**
     * Get pending transactions for verification
     *
     * @param  array  $filters  Optional filters (search, date_from, date_to)
     */
    public function getPendingTransactions(array $filters = []): Collection
    {
        $query = PaymentTransaction::query()
            ->pending()
            ->with([
                'items.bill.paymentCategory',
                'items.student.kelas',
                'guardian.user',
                'creator',
            ])
            ->orderBy('created_at', 'asc');

        // Apply filters
        if (! empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('transaction_number', 'like', "%{$search}%")
                    ->orWhereHas('items.student', function ($sq) use ($search) {
                        $sq->where('nama_lengkap', 'like', "%{$search}%")
                            ->orWhere('nis', 'like', "%{$search}%");
                    })
                    ->orWhereHas('guardian', function ($gq) use ($search) {
                        $gq->where('nama_lengkap', 'like', "%{$search}%");
                    });
            });
        }

        if (! empty($filters['date_from'])) {
            $query->where('payment_date', '>=', $filters['date_from']);
        }

        if (! empty($filters['date_to'])) {
            $query->where('payment_date', '<=', $filters['date_to']);
        }

        return $query->get();
    }

    /**
     * Get transactions for guardian (Parent)
     *
     * @param  int  $guardianId  Guardian ID
     * @param  string|null  $status  Filter by status
     */
    public function getGuardianTransactions(int $guardianId, ?string $status = null): Collection
    {
        $query = PaymentTransaction::query()
            ->forGuardian($guardianId)
            ->with([
                'items.bill.paymentCategory',
                'items.student.kelas',
                'creator',
                'verifier',
            ])
            ->orderBy('created_at', 'desc');

        if ($status) {
            $query->where('status', $status);
        }

        return $query->get();
    }

    /**
     * Get transactions for student
     *
     * @param  int  $studentId  Student ID
     * @param  string|null  $status  Filter by status
     */
    public function getStudentTransactions(int $studentId, ?string $status = null): Collection
    {
        $query = PaymentTransaction::query()
            ->forStudent($studentId)
            ->with([
                'items.bill.paymentCategory',
                'items.student.kelas',
                'creator',
                'verifier',
            ])
            ->orderBy('created_at', 'desc');

        if ($status) {
            $query->where('status', $status);
        }

        return $query->get();
    }

    /**
     * Format transaction data untuk response
     */
    public function formatTransactionForResponse(PaymentTransaction $transaction): array
    {
        return [
            'id' => $transaction->id,
            'transaction_number' => $transaction->transaction_number,
            'total_amount' => (float) $transaction->total_amount,
            'formatted_amount' => $transaction->formatted_amount,
            'payment_method' => $transaction->payment_method,
            'method_label' => $transaction->method_label,
            'payment_date' => $transaction->payment_date?->format('Y-m-d'),
            'formatted_date' => $transaction->payment_date?->format('d M Y'),
            'payment_time' => $transaction->payment_time?->format('H:i'),
            'status' => $transaction->status,
            'status_label' => $transaction->status_label,
            'proof_file' => $transaction->proof_file,
            'notes' => $transaction->notes,
            'bill_count' => $transaction->items->count(),
            'items' => $transaction->items->map(function ($item) {
                return [
                    'id' => $item->id,
                    'bill_id' => $item->bill_id,
                    'bill_number' => $item->bill->nomor_tagihan,
                    'category' => $item->bill->paymentCategory->nama ?? '-',
                    'period' => ($item->bill->nama_bulan ?? '-').' '.$item->bill->tahun,
                    'student' => [
                        'id' => $item->student->id,
                        'nama_lengkap' => $item->student->nama_lengkap,
                        'nis' => $item->student->nis,
                        'kelas' => $item->student->kelas?->nama_lengkap ?? '-',
                    ],
                    'amount' => (float) $item->amount,
                    'formatted_amount' => $item->formatted_amount,
                ];
            }),
            'guardian' => $transaction->guardian ? [
                'id' => $transaction->guardian->id,
                'nama_lengkap' => $transaction->guardian->nama_lengkap,
                'hubungan' => $transaction->guardian->hubungan_label,
            ] : null,
            'creator' => $transaction->creator ? [
                'id' => $transaction->creator->id,
                'name' => $transaction->creator->name,
            ] : null,
            'verifier' => $transaction->verifier ? [
                'id' => $transaction->verifier->id,
                'name' => $transaction->verifier->name,
            ] : null,
            'verified_at' => $transaction->verified_at?->format('d M Y H:i'),
            'canceller' => $transaction->canceller ? [
                'id' => $transaction->canceller->id,
                'name' => $transaction->canceller->name,
            ] : null,
            'cancelled_at' => $transaction->cancelled_at?->format('d M Y H:i'),
            'cancellation_reason' => $transaction->cancellation_reason,
            'created_at' => $transaction->created_at?->format('d M Y H:i'),
        ];
    }

    /**
     * Format transaction untuk list view (lebih ringkas)
     */
    public function formatTransactionForList(PaymentTransaction $transaction): array
    {
        // Get unique students in this transaction
        $students = $transaction->items->map(function ($item) {
            return [
                'id' => $item->student->id,
                'nama_lengkap' => $item->student->nama_lengkap,
                'nis' => $item->student->nis,
                'kelas' => $item->student->kelas?->nama_lengkap ?? '-',
            ];
        })->unique('id')->values();

        return [
            'id' => $transaction->id,
            'transaction_number' => $transaction->transaction_number,
            'total_amount' => (float) $transaction->total_amount,
            'formatted_amount' => $transaction->formatted_amount,
            'payment_method' => $transaction->payment_method,
            'method_label' => $transaction->method_label,
            'payment_date' => $transaction->payment_date?->format('Y-m-d'),
            'formatted_date' => $transaction->payment_date?->format('d M Y'),
            'status' => $transaction->status,
            'status_label' => $transaction->status_label,
            'bill_count' => $transaction->items->count(),
            'students' => $students,
            'has_proof' => ! empty($transaction->proof_file),
            'created_at' => $transaction->created_at?->format('d M Y H:i'),
        ];
    }

    /**
     * Search students untuk autocomplete (reuse dari PaymentService)
     */
    public function searchStudents(string $query, int $limit = 10): Collection
    {
        if (strlen($query) < 2) {
            return collect();
        }

        return Student::query()
            ->active()
            ->search($query)
            ->with(['kelas'])
            ->orderBy('nama_lengkap')
            ->limit($limit)
            ->get()
            ->map(function ($student) {
                return [
                    'id' => $student->id,
                    'nis' => $student->nis,
                    'nisn' => $student->nisn,
                    'nama_lengkap' => $student->nama_lengkap,
                    'kelas' => $student->kelas?->nama_lengkap ?? '-',
                    'kelas_id' => $student->kelas_id,
                    'display_label' => "{$student->nama_lengkap} - {$student->nis}",
                    'total_tunggakan' => $student->total_tunggakan,
                    'formatted_tunggakan' => 'Rp '.number_format($student->total_tunggakan, 0, ',', '.'),
                ];
            });
    }

    /**
     * Get unpaid bills untuk student (reuse dari PaymentService)
     */
    public function getStudentUnpaidBills(int $studentId): Collection
    {
        return Bill::query()
            ->where('student_id', $studentId)
            ->unpaid()
            ->with(['paymentCategory'])
            ->orderBy('tanggal_jatuh_tempo', 'asc')
            ->get()
            ->map(function ($bill) {
                return [
                    'id' => $bill->id,
                    'nomor_tagihan' => $bill->nomor_tagihan,
                    'category' => [
                        'id' => $bill->paymentCategory->id,
                        'nama' => $bill->paymentCategory->nama,
                        'kode' => $bill->paymentCategory->kode,
                    ],
                    'bulan' => $bill->bulan,
                    'tahun' => $bill->tahun,
                    'periode' => $bill->nama_bulan.' '.$bill->tahun,
                    'nominal' => (float) $bill->nominal,
                    'nominal_terbayar' => (float) $bill->nominal_terbayar,
                    'sisa_tagihan' => $bill->sisa_tagihan,
                    'formatted_nominal' => $bill->formatted_nominal,
                    'formatted_sisa' => $bill->formatted_sisa,
                    'status' => $bill->status,
                    'status_label' => $bill->status_label,
                    'is_overdue' => $bill->isOverdue(),
                    'tanggal_jatuh_tempo' => $bill->tanggal_jatuh_tempo?->format('Y-m-d'),
                    'formatted_due_date' => $bill->tanggal_jatuh_tempo?->format('d M Y'),
                ];
            });
    }

    /**
     * Get transaction statistics for dashboard
     *
     * @param  string|null  $date  Filter by date (Y-m-d format)
     */
    public function getTransactionStatistics(?string $date = null): array
    {
        $query = PaymentTransaction::query()->active();

        if ($date) {
            $query->whereDate('payment_date', $date);
        } else {
            $query->whereDate('payment_date', now()->toDateString());
        }

        $transactions = $query->get();

        return [
            'total_transactions' => $transactions->count(),
            'total_amount' => $transactions->where('status', 'verified')->sum('total_amount'),
            'formatted_total' => 'Rp '.number_format($transactions->where('status', 'verified')->sum('total_amount'), 0, ',', '.'),
            'pending_count' => $transactions->where('status', 'pending')->count(),
            'verified_count' => $transactions->where('status', 'verified')->count(),
            'by_method' => [
                'tunai' => $transactions->where('payment_method', 'tunai')->where('status', 'verified')->sum('total_amount'),
                'transfer' => $transactions->where('payment_method', 'transfer')->where('status', 'verified')->sum('total_amount'),
                'qris' => $transactions->where('payment_method', 'qris')->where('status', 'verified')->sum('total_amount'),
            ],
        ];
    }

    /**
     * Generate combined receipt PDF untuk transaction
     */
    public function generateReceiptPdf(PaymentTransaction $transaction): \Barryvdh\DomPDF\PDF
    {
        $transaction->load([
            'items.bill.paymentCategory',
            'items.student.kelas',
            'guardian',
            'creator',
            'verifier',
        ]);

        $data = [
            'transaction' => $transaction,
            'items' => $transaction->items,
            'school' => [
                'name' => config('app.school_name', 'Sekolah Dasar Negeri'),
                'address' => config('app.school_address', 'Jl. Pendidikan No. 1'),
                'phone' => config('app.school_phone', '(021) 123-4567'),
                'email' => config('app.school_email', 'info@sekolah.sch.id'),
            ],
            'generated_at' => now()->format('d M Y H:i'),
        ];

        return Pdf::loadView('receipts.transaction', $data)
            ->setPaper('a4', 'portrait');
    }

    /**
     * Get receipt data untuk preview (tanpa generate PDF)
     */
    public function getReceiptData(PaymentTransaction $transaction): array
    {
        $transaction->load([
            'items.bill.paymentCategory',
            'items.student.kelas',
            'guardian',
            'creator',
            'verifier',
        ]);

        return [
            'transaction_number' => $transaction->transaction_number,
            'tanggal' => $transaction->payment_date?->format('d M Y'),
            'waktu' => $transaction->payment_time,
            'items' => $transaction->items->map(function ($item) {
                return [
                    'student' => [
                        'nama' => $item->student->nama_lengkap,
                        'nis' => $item->student->nis,
                        'kelas' => $item->student->kelas?->nama_kelas ?? '-',
                    ],
                    'bill' => [
                        'kategori' => $item->bill->paymentCategory->nama_kategori ?? '-',
                        'periode' => ($item->bill->nama_bulan ?? '-').' '.$item->bill->tahun,
                    ],
                    'nominal' => 'Rp '.number_format($item->amount, 0, ',', '.'),
                ];
            })->all(),
            'total' => $transaction->formatted_amount,
            'metode' => $transaction->method_label,
            'petugas' => $transaction->creator?->name ?? '-',
            'school' => [
                'name' => config('app.school_name', 'Sekolah Dasar Negeri'),
                'address' => config('app.school_address', 'Jl. Pendidikan No. 1'),
            ],
        ];
    }
}
