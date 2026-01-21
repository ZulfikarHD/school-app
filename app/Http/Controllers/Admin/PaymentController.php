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
     * Export financial report
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
            if ($exportData->isNotEmpty()) {
                fputcsv($output, array_keys($exportData->first()));
                foreach ($exportData as $row) {
                    fputcsv($output, $row);
                }
            }
            fclose($output);
        }, $filename, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ]);
    }

    /**
     * Delinquent students list for Admin
     */
    public function delinquents(Request $request)
    {
        $sortBy = $request->input('sort', 'total_tunggakan');
        $sortDir = $request->input('dir', 'desc');

        $delinquents = \App\Models\Bill::query()
            ->whereIn('status', ['belum_bayar', 'sebagian'])
            ->with(['student.kelas', 'paymentCategory'])
            ->get()
            ->groupBy('student_id')
            ->map(function ($bills) {
                $student = $bills->first()->student;
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
            ->values();

        $delinquents = $delinquents->sortBy(
            $sortBy === 'nama' ? 'student.nama_lengkap' : $sortBy,
            SORT_REGULAR,
            $sortDir === 'desc'
        )->values();

        return Inertia::render('Admin/Payments/Reports/Delinquents', [
            'delinquents' => $delinquents,
            'totalStudents' => $delinquents->count(),
            'totalTunggakan' => $delinquents->sum('total_tunggakan'),
            'formattedTotalTunggakan' => 'Rp '.number_format($delinquents->sum('total_tunggakan'), 0, ',', '.'),
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
