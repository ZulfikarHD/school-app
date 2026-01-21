<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StorePaymentRequest;
use App\Models\ActivityLog;
use App\Models\Payment;
use App\Models\PaymentTransaction;
use App\Models\Student;
use App\Services\PaymentService;
use App\Services\PaymentTransactionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
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
        protected PaymentService $paymentService,
        protected PaymentTransactionService $transactionService
    ) {}

    /**
     * Display list of payments dengan pagination dan filter
     *
     * Menampilkan gabungan legacy payments dan payment transactions
     * untuk keperluan audit trail dan riwayat lengkap
     */
    public function index(Request $request)
    {
        $search = $request->input('search');
        $status = $request->input('status');
        $metode = $request->input('metode');
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');
        $date = $request->input('date');

        // Query PaymentTransactions (combined payments)
        $transactionQuery = PaymentTransaction::query()
            ->with(['items.bill.paymentCategory', 'items.student.kelas', 'guardian', 'creator', 'verifier', 'canceller']);

        if ($search) {
            $transactionQuery->where(function ($q) use ($search) {
                $q->where('transaction_number', 'like', "%{$search}%")
                    ->orWhereHas('items.student', function ($sq) use ($search) {
                        $sq->where('nama_lengkap', 'like', "%{$search}%")
                            ->orWhere('nis', 'like', "%{$search}%");
                    });
            });
        }

        if ($status) {
            $transactionQuery->where('status', $status);
        }

        if ($metode) {
            $transactionQuery->where('payment_method', $metode);
        }

        if ($startDate) {
            $transactionQuery->whereDate('payment_date', '>=', $startDate);
        }
        if ($endDate) {
            $transactionQuery->whereDate('payment_date', '<=', $endDate);
        }
        if ($date) {
            $transactionQuery->whereDate('payment_date', $date);
        }

        $transactions = $transactionQuery->orderBy('created_at', 'desc')->get();

        // Transform transactions ke format unified
        $formattedTransactions = $transactions->map(function ($transaction) {
            return [
                'id' => $transaction->id,
                'type' => 'transaction',
                'reference_number' => $transaction->transaction_number,
                'students' => $transaction->items->map(fn ($item) => [
                    'id' => $item->student_id,
                    'nama_lengkap' => $item->student->nama_lengkap,
                    'nis' => $item->student->nis,
                    'kelas' => $item->student->kelas?->nama_kelas ?? '-',
                ])->unique('id')->values()->all(),
                'bills' => $transaction->items->map(fn ($item) => [
                    'id' => $item->bill_id,
                    'category' => $item->bill->paymentCategory->nama_kategori ?? '-',
                    'periode' => $item->bill->formatted_periode ?? $item->bill->bulan.'/'.$item->bill->tahun,
                    'amount' => $item->amount,
                ])->all(),
                'bill_count' => $transaction->items->count(),
                'nominal' => $transaction->total_amount,
                'formatted_nominal' => $transaction->formatted_amount,
                'metode_pembayaran' => $transaction->payment_method,
                'metode_label' => $transaction->method_label,
                'tanggal_bayar' => $transaction->payment_date?->format('Y-m-d'),
                'formatted_tanggal' => $transaction->payment_date?->format('d M Y'),
                'waktu_bayar' => $transaction->payment_time,
                'status' => $transaction->status,
                'status_label' => $transaction->status_label,
                'creator' => $transaction->creator ? ['id' => $transaction->creator->id, 'name' => $transaction->creator->name] : null,
                'verifier' => $transaction->verifier ? ['id' => $transaction->verifier->id, 'name' => $transaction->verifier->name] : null,
                'verified_at' => $transaction->verified_at?->format('d M Y H:i'),
                'created_at' => $transaction->created_at->format('Y-m-d H:i:s'),
                'sort_date' => $transaction->created_at,
            ];
        });

        // Query legacy Payments
        $paymentQuery = Payment::query()
            ->with(['bill.paymentCategory', 'student.kelas', 'creator', 'verifier', 'canceller']);

        if ($search) {
            $paymentQuery->where(function ($q) use ($search) {
                $q->where('nomor_kwitansi', 'like', "%{$search}%")
                    ->orWhereHas('student', function ($sq) use ($search) {
                        $sq->where('nama_lengkap', 'like', "%{$search}%")
                            ->orWhere('nis', 'like', "%{$search}%");
                    });
            });
        }

        if ($status) {
            $paymentQuery->where('status', $status);
        }

        if ($metode) {
            $paymentQuery->where('metode_pembayaran', $metode);
        }

        if ($startDate) {
            $paymentQuery->whereDate('tanggal_bayar', '>=', $startDate);
        }
        if ($endDate) {
            $paymentQuery->whereDate('tanggal_bayar', '<=', $endDate);
        }
        if ($date) {
            $paymentQuery->whereDate('tanggal_bayar', $date);
        }

        $payments = $paymentQuery->orderBy('created_at', 'desc')->get();

        // Transform payments ke format unified
        $formattedPayments = $payments->map(function ($payment) {
            return [
                'id' => $payment->id,
                'type' => 'payment',
                'reference_number' => $payment->nomor_kwitansi,
                'students' => [[
                    'id' => $payment->student_id,
                    'nama_lengkap' => $payment->student->nama_lengkap,
                    'nis' => $payment->student->nis,
                    'kelas' => $payment->student->kelas?->nama_kelas ?? '-',
                ]],
                'bills' => [[
                    'id' => $payment->bill_id,
                    'category' => $payment->bill->paymentCategory->nama_kategori ?? '-',
                    'periode' => $payment->bill->formatted_periode ?? $payment->bill->bulan.'/'.$payment->bill->tahun,
                    'amount' => $payment->nominal,
                ]],
                'bill_count' => 1,
                'nominal' => $payment->nominal,
                'formatted_nominal' => 'Rp '.number_format($payment->nominal, 0, ',', '.'),
                'metode_pembayaran' => $payment->metode_pembayaran,
                'metode_label' => match ($payment->metode_pembayaran) {
                    'tunai' => 'Tunai',
                    'transfer' => 'Transfer',
                    'qris' => 'QRIS',
                    default => ucfirst($payment->metode_pembayaran),
                },
                'tanggal_bayar' => $payment->tanggal_bayar?->format('Y-m-d'),
                'formatted_tanggal' => $payment->tanggal_bayar?->format('d M Y'),
                'waktu_bayar' => $payment->waktu_bayar,
                'status' => $payment->status,
                'status_label' => match ($payment->status) {
                    'pending' => 'Menunggu',
                    'verified' => 'Terverifikasi',
                    'cancelled' => 'Dibatalkan',
                    default => ucfirst($payment->status),
                },
                'creator' => $payment->creator ? ['id' => $payment->creator->id, 'name' => $payment->creator->name] : null,
                'verifier' => $payment->verifier ? ['id' => $payment->verifier->id, 'name' => $payment->verifier->name] : null,
                'verified_at' => $payment->verified_at?->format('d M Y H:i'),
                'created_at' => $payment->created_at->format('Y-m-d H:i:s'),
                'sort_date' => $payment->created_at,
            ];
        });

        // Combine and sort by created_at desc
        $allRecords = $formattedTransactions->concat($formattedPayments)
            ->sortByDesc('sort_date')
            ->values();

        // Manual pagination
        $perPage = 20;
        $page = $request->input('page', 1);
        $total = $allRecords->count();
        $paginatedData = $allRecords->forPage($page, $perPage)->values();

        $paginated = new \Illuminate\Pagination\LengthAwarePaginator(
            $paginatedData,
            $total,
            $perPage,
            $page,
            ['path' => $request->url(), 'query' => $request->query()]
        );

        // Get statistics for today - combine both
        $transactionStats = $this->transactionService->getTransactionStatistics(now()->toDateString());
        $legacyStats = $this->paymentService->getPaymentStatistics(now()->toDateString());

        // Combined stats
        $todayStats = [
            'total_transactions' => ($transactionStats['total_transactions'] ?? 0) + ($legacyStats['total_transactions'] ?? 0),
            'total_amount' => ($transactionStats['total_amount'] ?? 0) + ($legacyStats['total_amount'] ?? 0),
            'formatted_total' => 'Rp '.number_format(
                ($transactionStats['total_amount'] ?? 0) + ($legacyStats['total_amount'] ?? 0),
                0, ',', '.'
            ),
            'pending_count' => ($transactionStats['pending_count'] ?? 0) + ($legacyStats['pending_count'] ?? 0),
            'verified_count' => ($transactionStats['verified_count'] ?? 0) + ($legacyStats['verified_count'] ?? 0),
            'by_method' => [
                'tunai' => ($transactionStats['by_method']['tunai'] ?? 0) + ($legacyStats['by_method']['tunai'] ?? 0),
                'transfer' => ($transactionStats['by_method']['transfer'] ?? 0) + ($legacyStats['by_method']['transfer'] ?? 0),
                'qris' => ($transactionStats['by_method']['qris'] ?? 0) + ($legacyStats['by_method']['qris'] ?? 0),
            ],
        ];

        // Get pending verification count
        $pendingCount = PaymentTransaction::pending()->count() + Payment::pending()->count();

        return Inertia::render('Admin/Payments/Payments/Index', [
            'records' => $paginated,
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
     * Show detail pembayaran (legacy single payment)
     * Menggunakan unified view yang sama dengan transaction
     */
    public function show(Payment $payment)
    {
        $payment->load(['bill.paymentCategory', 'student.kelas', 'creator', 'verifier', 'canceller']);

        // Transform ke unified record format
        $record = $this->formatPaymentAsUnifiedRecord($payment);
        $receiptData = $this->formatPaymentReceiptForUnifiedView($payment);

        return Inertia::render('Admin/Payments/Payments/Show', [
            'record' => $record,
            'receiptData' => $receiptData,
            'canVerify' => $payment->canBeVerified(),
            'canCancel' => $payment->canBeCancelled(),
        ]);
    }

    /**
     * Format Payment ke unified record structure untuk Show view
     */
    private function formatPaymentAsUnifiedRecord(Payment $payment): array
    {
        return [
            'id' => $payment->id,
            'type' => 'payment',
            'reference_number' => $payment->nomor_kwitansi,
            'total_amount' => (float) $payment->nominal,
            'formatted_amount' => $payment->formatted_nominal,
            'payment_method' => $payment->metode_pembayaran,
            'method_label' => $payment->metode_label,
            'payment_date' => $payment->tanggal_bayar?->format('Y-m-d'),
            'formatted_date' => $payment->tanggal_bayar?->format('d M Y'),
            'payment_time' => $payment->waktu_bayar?->format('H:i'),
            'status' => $payment->status,
            'status_label' => $payment->status_label,
            'notes' => $payment->keterangan,
            'proof_file' => $payment->bukti_transfer,
            // Single payment specific
            'student' => [
                'id' => $payment->student->id,
                'nama_lengkap' => $payment->student->nama_lengkap,
                'nis' => $payment->student->nis,
                'kelas' => $payment->student->kelas?->nama_lengkap ?? '-',
            ],
            'bill' => [
                'id' => $payment->bill->id,
                'nomor_tagihan' => $payment->bill->nomor_tagihan,
                'category' => $payment->bill->paymentCategory->nama ?? '-',
                'periode' => ($payment->bill->nama_bulan ?? '-').' '.$payment->bill->tahun,
            ],
            // Relations
            'creator' => $payment->creator ? [
                'id' => $payment->creator->id,
                'name' => $payment->creator->name,
            ] : null,
            'verifier' => $payment->verifier ? [
                'id' => $payment->verifier->id,
                'name' => $payment->verifier->name,
            ] : null,
            'verified_at' => $payment->verified_at?->format('d M Y H:i'),
            'canceller' => $payment->canceller ? [
                'id' => $payment->canceller->id,
                'name' => $payment->canceller->name,
            ] : null,
            'cancelled_at' => $payment->cancelled_at?->format('d M Y H:i'),
            'cancellation_reason' => $payment->cancellation_reason,
        ];
    }

    /**
     * Format Payment receipt data untuk unified view
     */
    private function formatPaymentReceiptForUnifiedView(Payment $payment): array
    {
        return [
            'reference_number' => $payment->nomor_kwitansi,
            'tanggal' => $payment->tanggal_bayar?->format('d M Y'),
            'waktu' => $payment->waktu_bayar?->format('H:i'),
            'items' => [
                [
                    'student' => [
                        'nama' => $payment->student->nama_lengkap,
                        'nis' => $payment->student->nis,
                        'kelas' => $payment->student->kelas?->nama_lengkap ?? '-',
                    ],
                    'bill' => [
                        'kategori' => $payment->bill->paymentCategory->nama ?? '-',
                        'periode' => ($payment->bill->nama_bulan ?? '-').' '.$payment->bill->tahun,
                        'nomor' => $payment->bill->nomor_tagihan,
                    ],
                    'nominal' => $payment->formatted_nominal,
                ],
            ],
            'total' => $payment->formatted_nominal,
            'metode' => $payment->metode_label,
            'petugas' => $payment->creator?->name ?? '-',
            'school' => [
                'name' => config('app.school_name', 'Sekolah Dasar Negeri'),
                'address' => config('app.school_address', 'Jl. Pendidikan No. 1'),
            ],
        ];
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
     *
     * UPDATED: Now shows both pending transactions and legacy payments
     */
    public function verification(Request $request)
    {
        $search = $request->input('search');

        // Get pending transactions (NEW: Combined payments)
        $transactionsQuery = PaymentTransaction::query()
            ->with(['items.bill.paymentCategory', 'items.student.kelas', 'guardian.user', 'creator'])
            ->pending();

        if ($search) {
            $transactionsQuery->where(function ($q) use ($search) {
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

        $pendingTransactions = $transactionsQuery->orderBy('created_at', 'asc')
            ->paginate(15, ['*'], 'transactions_page')
            ->withQueryString();

        $pendingTransactions->getCollection()->transform(
            fn ($transaction) => $this->transactionService->formatTransactionForResponse($transaction)
        );

        // Legacy: Get pending payments (for backward compatibility)
        $paymentsQuery = Payment::query()
            ->with(['bill.paymentCategory', 'student.kelas', 'creator'])
            ->pending();

        if ($search) {
            $paymentsQuery->where(function ($q) use ($search) {
                $q->where('nomor_kwitansi', 'like', "%{$search}%")
                    ->orWhereHas('student', function ($sq) use ($search) {
                        $sq->where('nama_lengkap', 'like', "%{$search}%")
                            ->orWhere('nis', 'like', "%{$search}%");
                    });
            });
        }

        $pendingPayments = $paymentsQuery->orderBy('created_at', 'asc')
            ->paginate(15, ['*'], 'payments_page')
            ->withQueryString();

        $pendingPayments->getCollection()->transform(function ($payment) {
            return $this->paymentService->formatPaymentForResponse($payment);
        });

        // Count totals
        $transactionCount = PaymentTransaction::pending()->count();
        $paymentCount = Payment::pending()->count();

        return Inertia::render('Admin/Payments/Payments/Verification', [
            'transactions' => $pendingTransactions,
            'payments' => $pendingPayments, // Legacy support
            'counts' => [
                'transactions' => $transactionCount,
                'payments' => $paymentCount, // Legacy
                'total' => $transactionCount + $paymentCount,
            ],
            'filters' => $request->only(['search']),
        ]);
    }

    // ===================================================================
    // TRANSACTION-BASED METHODS (Combined Payment)
    // ===================================================================

    /**
     * Show transaction detail
     * Menggunakan unified view yang sama dengan payment
     */
    public function showTransaction(PaymentTransaction $transaction)
    {
        $transaction->load([
            'items.bill.paymentCategory',
            'items.student.kelas',
            'guardian',
            'creator',
            'verifier',
            'canceller',
        ]);

        // Transform ke unified record format
        $record = $this->formatTransactionAsUnifiedRecord($transaction);
        $receiptData = $this->transactionService->getReceiptData($transaction);

        return Inertia::render('Admin/Payments/Payments/Show', [
            'record' => $record,
            'receiptData' => $receiptData,
            'canVerify' => $transaction->canBeVerified(),
            'canCancel' => $transaction->canBeCancelled(),
        ]);
    }

    /**
     * Format PaymentTransaction ke unified record structure untuk Show view
     */
    private function formatTransactionAsUnifiedRecord(PaymentTransaction $transaction): array
    {
        return [
            'id' => $transaction->id,
            'type' => 'transaction',
            'reference_number' => $transaction->transaction_number,
            'total_amount' => (float) $transaction->total_amount,
            'formatted_amount' => $transaction->formatted_amount,
            'payment_method' => $transaction->payment_method,
            'method_label' => $transaction->method_label,
            'payment_date' => $transaction->payment_date?->format('Y-m-d'),
            'formatted_date' => $transaction->payment_date?->format('d M Y'),
            'payment_time' => $transaction->payment_time?->format('H:i'),
            'status' => $transaction->status,
            'status_label' => $transaction->status_label,
            'notes' => $transaction->notes,
            'proof_file' => $transaction->proof_file,
            // Transaction specific
            'bill_count' => $transaction->items->count(),
            'items' => $transaction->items->map(function ($item) {
                return [
                    'id' => $item->id,
                    'bill_id' => $item->bill_id,
                    'student_id' => $item->student_id,
                    'amount' => (float) $item->amount,
                    'formatted_amount' => $item->formatted_amount,
                    'bill' => [
                        'id' => $item->bill->id,
                        'nomor_tagihan' => $item->bill->nomor_tagihan,
                        'category' => $item->bill->paymentCategory->nama ?? '-',
                        'periode' => ($item->bill->nama_bulan ?? '-').' '.$item->bill->tahun,
                    ],
                    'student' => [
                        'id' => $item->student->id,
                        'nama_lengkap' => $item->student->nama_lengkap,
                        'nis' => $item->student->nis,
                        'kelas' => $item->student->kelas?->nama_lengkap ?? '-',
                    ],
                ];
            })->all(),
            'guardian' => $transaction->guardian ? [
                'id' => $transaction->guardian->id,
                'nama_lengkap' => $transaction->guardian->nama_lengkap,
            ] : null,
            // Relations
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
        ];
    }

    /**
     * Verify pending transaction
     */
    public function verifyTransaction(PaymentTransaction $transaction, Request $request)
    {
        $result = $this->transactionService->verifyTransaction($transaction);

        if ($result['success']) {
            ActivityLog::create([
                'user_id' => auth()->id(),
                'action' => 'verify_transaction',
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'new_values' => [
                    'transaction_id' => $transaction->id,
                    'transaction_number' => $transaction->transaction_number,
                    'bill_count' => $transaction->items->count(),
                    'total_amount' => $transaction->total_amount,
                ],
                'status' => 'success',
            ]);

            return back()->with('success', $result['message']);
        }

        return back()->withErrors(['error' => $result['message']]);
    }

    /**
     * Reject/Cancel pending transaction
     */
    public function rejectTransaction(PaymentTransaction $transaction, Request $request)
    {
        $request->validate([
            'reason' => 'required|string|max:500',
        ], [
            'reason.required' => 'Alasan penolakan harus diisi.',
            'reason.max' => 'Alasan maksimal 500 karakter.',
        ]);

        $result = $this->transactionService->cancelTransaction($transaction, $request->reason);

        if ($result['success']) {
            ActivityLog::create([
                'user_id' => auth()->id(),
                'action' => 'reject_transaction',
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'old_values' => [
                    'transaction_id' => $transaction->id,
                    'transaction_number' => $transaction->transaction_number,
                    'status' => 'pending',
                ],
                'new_values' => [
                    'status' => 'cancelled',
                    'reason' => $request->reason,
                ],
                'status' => 'success',
            ]);

            return redirect()->route('admin.payments.records.verification')
                ->with('success', $result['message']);
        }

        return back()->withErrors(['error' => $result['message']]);
    }

    /**
     * Download transaction receipt PDF
     */
    public function transactionReceipt(PaymentTransaction $transaction)
    {
        try {
            $pdf = $this->transactionService->generateReceiptPdf($transaction);

            $filename = "Kwitansi-{$transaction->transaction_number}.pdf";
            $filename = str_replace('/', '-', $filename);

            return $pdf->download($filename);
        } catch (\Exception $e) {
            Log::error('Failed to generate transaction receipt PDF', [
                'transaction_id' => $transaction->id,
                'error' => $e->getMessage(),
            ]);

            return back()->withErrors(['error' => 'Gagal generate kwitansi PDF.']);
        }
    }

    /**
     * Stream transaction receipt PDF for print preview
     */
    public function transactionReceiptStream(PaymentTransaction $transaction)
    {
        try {
            $pdf = $this->transactionService->generateReceiptPdf($transaction);

            return $pdf->stream("Kwitansi-{$transaction->transaction_number}.pdf");
        } catch (\Exception $e) {
            Log::error('Failed to stream transaction receipt PDF', [
                'transaction_id' => $transaction->id,
                'error' => $e->getMessage(),
            ]);

            return back()->withErrors(['error' => 'Gagal menampilkan kwitansi PDF.']);
        }
    }

    /**
     * Create manual transaction (Admin entry for multi-bill)
     */
    public function storeTransaction(Request $request)
    {
        $request->validate([
            'bills' => 'required|array|min:1',
            'bills.*' => 'exists:bills,id',
            'payment_method' => 'required|in:tunai,transfer,qris',
            'payment_date' => 'required|date|before_or_equal:today',
            'notes' => 'nullable|string|max:500',
            'proof_file' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:5120',
        ], [
            'bills.required' => 'Pilih minimal satu tagihan.',
            'bills.min' => 'Pilih minimal satu tagihan.',
            'payment_method.required' => 'Pilih metode pembayaran.',
            'payment_date.required' => 'Tanggal bayar harus diisi.',
            'payment_date.before_or_equal' => 'Tanggal bayar tidak boleh lebih dari hari ini.',
        ]);

        // Upload proof file if exists
        $proofPath = null;
        if ($request->hasFile('proof_file')) {
            $file = $request->file('proof_file');
            $filename = 'bukti_'.now()->format('Ymd_His').'_'.uniqid().'.'.$file->getClientOriginalExtension();
            $proofPath = $file->storeAs('payment-proofs', $filename, 'public');
        }

        $result = $this->transactionService->createManualTransaction([
            'bills' => $request->bills,
            'payment_method' => $request->payment_method,
            'payment_date' => $request->payment_date,
            'notes' => $request->notes,
            'proof_file' => $proofPath,
        ]);

        if ($result['success']) {
            ActivityLog::create([
                'user_id' => auth()->id(),
                'action' => 'create_transaction',
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'new_values' => [
                    'transaction_id' => $result['transaction']->id,
                    'transaction_number' => $result['transaction']->transaction_number,
                    'total_amount' => $result['transaction']->total_amount,
                    'bill_count' => count($request->bills),
                    'payment_method' => $request->payment_method,
                ],
                'status' => 'success',
            ]);

            return redirect()->route('admin.payments.transactions.show', $result['transaction']->id)
                ->with('success', "Pembayaran berhasil dicatat. No. Transaksi: {$result['transaction']->transaction_number}");
        }

        // Clean up uploaded file if exists
        if ($proofPath) {
            Storage::disk('public')->delete($proofPath);
        }

        return back()->withErrors(['error' => $result['message']])->withInput();
    }

    /**
     * List all transactions
     */
    public function transactionIndex(Request $request)
    {
        $query = PaymentTransaction::query()
            ->with(['items.bill.paymentCategory', 'items.student.kelas', 'guardian', 'creator', 'verifier', 'canceller']);

        // Search by transaction number atau nama siswa
        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('transaction_number', 'like', "%{$search}%")
                    ->orWhereHas('items.student', function ($sq) use ($search) {
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
            $query->where('payment_method', $metode);
        }

        // Filter by date range
        if ($startDate = $request->input('start_date')) {
            $query->whereDate('payment_date', '>=', $startDate);
        }
        if ($endDate = $request->input('end_date')) {
            $query->whereDate('payment_date', '<=', $endDate);
        }

        $transactions = $query->orderBy('created_at', 'desc')
            ->paginate(20)
            ->withQueryString();

        // Transform data
        $transactions->getCollection()->transform(function ($transaction) {
            return $this->transactionService->formatTransactionForList($transaction);
        });

        // Get statistics for today
        $todayStats = $this->transactionService->getTransactionStatistics(now()->toDateString());

        // Get pending verification count
        $pendingCount = PaymentTransaction::pending()->count();

        return Inertia::render('Admin/Payments/Transactions/Index', [
            'transactions' => $transactions,
            'stats' => [
                'today' => $todayStats,
                'pending_verification' => $pendingCount,
            ],
            'filters' => $request->only(['search', 'status', 'metode', 'start_date', 'end_date']),
        ]);
    }

    /**
     * Financial Reports page for Admin
     */
    public function reports(Request $request)
    {
        $month = $request->input('month', now()->month);
        $year = $request->input('year', now()->year);
        $categoryId = $request->input('category_id');

        $startDate = \Carbon\Carbon::create($year, $month, 1)->startOfMonth();
        $endDate = \Carbon\Carbon::create($year, $month, 1)->endOfMonth();

        // Get summary statistics
        $summary = $this->getSummaryStatistics($startDate, $endDate, $categoryId);

        // Get monthly trend data (last 6 months)
        $trend = $this->getMonthlyTrend(6);

        // Get breakdown by category
        $categoryBreakdown = $this->getCategoryBreakdown($startDate, $endDate);

        // Get categories for filter
        $categories = \App\Models\PaymentCategory::where('is_active', true)
            ->orderBy('nama')
            ->get(['id', 'nama', 'kode']);

        // Get overdue summary
        $overdueSummary = $this->getOverdueSummary();

        return Inertia::render('Admin/Payments/Reports/Index', [
            'summary' => $summary,
            'trend' => $trend,
            'categoryBreakdown' => $categoryBreakdown,
            'overdueSummary' => $overdueSummary,
            'categories' => $categories,
            'filters' => [
                'month' => (int) $month,
                'year' => (int) $year,
                'category_id' => $categoryId,
            ],
            'monthOptions' => $this->getMonthOptions(),
            'yearOptions' => $this->getYearOptions(),
        ]);
    }

    /**
     * Export financial report to CSV
     *
     * Mengekspor laporan keuangan dalam format CSV yang dapat dibuka
     * di Excel, Google Sheets, atau aplikasi spreadsheet lainnya
     */
    public function exportReports(Request $request)
    {
        $month = $request->input('month', now()->month);
        $year = $request->input('year', now()->year);

        $startDate = \Carbon\Carbon::create($year, $month, 1)->startOfMonth();
        $endDate = \Carbon\Carbon::create($year, $month, 1)->endOfMonth();

        $payments = Payment::query()
            ->verified()
            ->whereBetween('tanggal_bayar', [$startDate, $endDate])
            ->with(['student.kelas', 'bill.paymentCategory'])
            ->orderBy('tanggal_bayar', 'desc')
            ->get();

        $exportData = $payments->map(function ($payment) {
            return [
                'Tanggal' => $payment->tanggal_bayar->format('d/m/Y'),
                'No. Kwitansi' => $payment->nomor_kwitansi,
                'Nama Siswa' => $payment->student->nama_lengkap,
                'NIS' => $payment->student->nis,
                'Kelas' => $payment->student->kelas?->nama_lengkap ?? '-',
                'Kategori' => $payment->bill->paymentCategory->nama,
                'Periode' => $payment->bill->nama_bulan.' '.$payment->bill->tahun,
                'Nominal' => $payment->nominal,
                'Metode' => $payment->metode_label,
            ];
        });

        $monthName = \Carbon\Carbon::create($year, $month, 1)->translatedFormat('F Y');
        $filename = "Laporan-Keuangan-{$monthName}.csv";

        return response()->streamDownload(function () use ($exportData) {
            $output = fopen('php://output', 'w');
            // Add BOM for UTF-8 Excel compatibility
            fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));
            if ($exportData->isNotEmpty()) {
                fputcsv($output, array_keys($exportData->first()));
                foreach ($exportData as $row) {
                    fputcsv($output, $row);
                }
            }
            fclose($output);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ]);
    }

    /**
     * Delinquent students list for Admin
     *
     * Menampilkan daftar siswa yang memiliki tagihan belum lunas
     * dengan filter untuk student yang valid dan active, serta pagination
     */
    public function delinquents(Request $request)
    {
        $sortBy = $request->input('sort', 'total_tunggakan');
        $sortDir = $request->input('dir', 'desc');
        $perPage = $request->input('per_page', 15);
        $page = $request->input('page', 1);

        $delinquents = \App\Models\Bill::query()
            ->whereIn('status', ['belum_bayar', 'sebagian'])
            ->whereHas('student') // Pastikan student exists
            ->with(['student.kelas', 'paymentCategory'])
            ->get()
            ->groupBy('student_id')
            ->map(function ($bills) {
                $student = $bills->first()->student;

                // Skip jika student null atau tidak aktif
                if (! $student) {
                    return null;
                }

                $totalTunggakan = $bills->sum(fn ($bill) => $bill->sisa_tagihan);
                $overdueCount = $bills->filter(fn ($bill) => $bill->isOverdue())->count();
                $oldestDue = $bills->sortBy('tanggal_jatuh_tempo')->first();

                return [
                    'student' => [
                        'id' => $student->id,
                        'nama_lengkap' => $student->nama_lengkap,
                        'nis' => $student->nis,
                        'kelas' => $student->kelas?->nama_lengkap ?? '-',
                    ],
                    'total_bills' => $bills->count(),
                    'total_tunggakan' => $totalTunggakan,
                    'formatted_tunggakan' => 'Rp '.number_format($totalTunggakan, 0, ',', '.'),
                    'overdue_count' => $overdueCount,
                    'oldest_due_date' => $oldestDue?->tanggal_jatuh_tempo?->format('d M Y'),
                    'bills' => $bills->map(fn ($bill) => [
                        'id' => $bill->id,
                        'category' => $bill->paymentCategory->nama,
                        'periode' => $bill->nama_bulan.' '.$bill->tahun,
                        'sisa' => $bill->formatted_sisa,
                        'is_overdue' => $bill->isOverdue(),
                    ])->values(),
                ];
            })
            ->filter() // Remove null entries
            ->values();

        // Calculate totals before pagination
        $totalStudents = $delinquents->count();
        $totalTunggakan = $delinquents->sum('total_tunggakan');

        // Sorting collection
        $sortField = $sortBy === 'nama' ? 'student.nama_lengkap' : $sortBy;
        if ($sortDir === 'desc') {
            $delinquents = $delinquents->sortByDesc($sortField)->values();
        } else {
            $delinquents = $delinquents->sortBy($sortField)->values();
        }

        // Manual pagination on collection
        $total = $delinquents->count();
        $lastPage = (int) ceil($total / $perPage);
        $from = ($page - 1) * $perPage + 1;
        $to = min($page * $perPage, $total);

        $paginatedData = $delinquents->slice(($page - 1) * $perPage, $perPage)->values();

        return Inertia::render('Admin/Payments/Reports/Delinquents', [
            'delinquents' => [
                'data' => $paginatedData->toArray(),
                'current_page' => (int) $page,
                'last_page' => $lastPage,
                'per_page' => (int) $perPage,
                'total' => $total,
                'from' => $total > 0 ? $from : null,
                'to' => $total > 0 ? $to : null,
            ],
            'totalStudents' => $totalStudents,
            'totalTunggakan' => $totalTunggakan,
            'formattedTotalTunggakan' => 'Rp '.number_format($totalTunggakan, 0, ',', '.'),
            'filters' => [
                'sort' => $sortBy,
                'dir' => $sortDir,
            ],
        ]);
    }

    /**
     * Get summary statistics for a period
     */
    protected function getSummaryStatistics(\Carbon\Carbon $startDate, \Carbon\Carbon $endDate, ?int $categoryId = null): array
    {
        $paymentsQuery = Payment::query()
            ->verified()
            ->whereBetween('tanggal_bayar', [$startDate, $endDate]);

        if ($categoryId) {
            $paymentsQuery->whereHas('bill', fn ($q) => $q->where('payment_category_id', $categoryId));
        }

        $payments = $paymentsQuery->get();
        $totalIncome = $payments->sum('nominal');
        $transactionCount = $payments->count();

        $unpaidBillsQuery = \App\Models\Bill::query()->whereIn('status', ['belum_bayar', 'sebagian']);
        if ($categoryId) {
            $unpaidBillsQuery->where('payment_category_id', $categoryId);
        }
        $unpaidBills = $unpaidBillsQuery->get();
        $totalPiutang = $unpaidBills->sum(fn ($bill) => $bill->sisa_tagihan);

        $monthlyBillsQuery = \App\Models\Bill::query()
            ->whereMonth('created_at', $startDate->month)
            ->whereYear('created_at', $startDate->year);
        if ($categoryId) {
            $monthlyBillsQuery->where('payment_category_id', $categoryId);
        }
        $monthlyBills = $monthlyBillsQuery->get();

        $expectedIncome = $monthlyBills->sum('nominal');
        $collectibility = $expectedIncome > 0
            ? round(($totalIncome / $expectedIncome) * 100, 1)
            : 100;

        $byMethod = [
            'tunai' => $payments->where('metode_pembayaran', 'tunai')->sum('nominal'),
            'transfer' => $payments->where('metode_pembayaran', 'transfer')->sum('nominal'),
            'qris' => $payments->where('metode_pembayaran', 'qris')->sum('nominal'),
        ];

        return [
            'total_income' => $totalIncome,
            'formatted_income' => 'Rp '.number_format($totalIncome, 0, ',', '.'),
            'transaction_count' => $transactionCount,
            'total_piutang' => $totalPiutang,
            'formatted_piutang' => 'Rp '.number_format($totalPiutang, 0, ',', '.'),
            'collectibility' => $collectibility,
            'by_method' => [
                'tunai' => [
                    'amount' => $byMethod['tunai'],
                    'formatted' => 'Rp '.number_format($byMethod['tunai'], 0, ',', '.'),
                ],
                'transfer' => [
                    'amount' => $byMethod['transfer'],
                    'formatted' => 'Rp '.number_format($byMethod['transfer'], 0, ',', '.'),
                ],
                'qris' => [
                    'amount' => $byMethod['qris'],
                    'formatted' => 'Rp '.number_format($byMethod['qris'], 0, ',', '.'),
                ],
            ],
        ];
    }

    /**
     * Get monthly income trend
     */
    protected function getMonthlyTrend(int $months): array
    {
        $trend = [];

        for ($i = $months - 1; $i >= 0; $i--) {
            $date = now()->subMonths($i);
            $startDate = $date->copy()->startOfMonth();
            $endDate = $date->copy()->endOfMonth();

            $income = Payment::query()
                ->verified()
                ->whereBetween('tanggal_bayar', [$startDate, $endDate])
                ->sum('nominal');

            $trend[] = [
                'month' => $date->translatedFormat('M'),
                'month_full' => $date->translatedFormat('F Y'),
                'year' => $date->year,
                'income' => (float) $income,
                'formatted' => 'Rp '.number_format($income, 0, ',', '.'),
            ];
        }

        return $trend;
    }

    /**
     * Get income breakdown by payment category
     */
    protected function getCategoryBreakdown(\Carbon\Carbon $startDate, \Carbon\Carbon $endDate): array
    {
        $payments = Payment::query()
            ->verified()
            ->whereBetween('tanggal_bayar', [$startDate, $endDate])
            ->with(['bill.paymentCategory'])
            ->get();

        $breakdown = $payments->groupBy(fn ($p) => $p->bill->paymentCategory->nama)
            ->map(fn ($group, $categoryName) => [
                'category' => $categoryName,
                'amount' => $group->sum('nominal'),
                'formatted' => 'Rp '.number_format($group->sum('nominal'), 0, ',', '.'),
                'count' => $group->count(),
            ])
            ->sortByDesc('amount')
            ->values();

        return $breakdown->toArray();
    }

    /**
     * Get overdue bills summary
     */
    protected function getOverdueSummary(): array
    {
        $overdueBills = \App\Models\Bill::query()
            ->whereIn('status', ['belum_bayar', 'sebagian'])
            ->get()
            ->filter(fn ($bill) => $bill->isOverdue());

        $totalOverdue = $overdueBills->sum(fn ($bill) => $bill->sisa_tagihan);
        $studentCount = $overdueBills->pluck('student_id')->unique()->count();

        return [
            'total_bills' => $overdueBills->count(),
            'total_students' => $studentCount,
            'total_amount' => $totalOverdue,
            'formatted_amount' => 'Rp '.number_format($totalOverdue, 0, ',', '.'),
        ];
    }

    /**
     * Get month options for filter
     */
    protected function getMonthOptions(): array
    {
        $months = [];
        for ($i = 1; $i <= 12; $i++) {
            $months[] = [
                'value' => $i,
                'label' => \Carbon\Carbon::create(null, $i, 1)->translatedFormat('F'),
            ];
        }

        return $months;
    }

    /**
     * Get year options for filter
     */
    protected function getYearOptions(): array
    {
        $currentYear = now()->year;
        $years = [];

        for ($i = 0; $i < 3; $i++) {
            $years[] = [
                'value' => $currentYear - $i,
                'label' => (string) ($currentYear - $i),
            ];
        }

        return $years;
    }
}
