<?php

namespace App\Http\Controllers\Principal;

use App\Http\Controllers\Controller;
use App\Models\Bill;
use App\Models\Payment;
use App\Models\PaymentCategory;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Maatwebsite\Excel\Facades\Excel;

/**
 * Controller untuk laporan keuangan Principal (read-only)
 *
 * Menyediakan akses ke laporan pembayaran, grafik pendapatan,
 * dan daftar siswa menunggak untuk monitoring kepala sekolah
 */
class FinancialReportController extends Controller
{
    /**
     * Display financial reports dashboard
     */
    public function index(Request $request)
    {
        // Default filter ke bulan ini
        $month = $request->input('month', now()->month);
        $year = $request->input('year', now()->year);
        $categoryId = $request->input('category_id');

        // Build date range
        $startDate = Carbon::create($year, $month, 1)->startOfMonth();
        $endDate = Carbon::create($year, $month, 1)->endOfMonth();

        // Get summary statistics
        $summary = $this->getSummaryStatistics($startDate, $endDate, $categoryId);

        // Get monthly trend data (last 6 months)
        $trend = $this->getMonthlyTrend(6);

        // Get breakdown by category
        $categoryBreakdown = $this->getCategoryBreakdown($startDate, $endDate);

        // Get payment categories for filter
        $categories = PaymentCategory::where('is_active', true)
            ->orderBy('nama')
            ->get(['id', 'nama', 'kode']);

        // Get overdue summary
        $overdueSummary = $this->getOverdueSummary();

        return Inertia::render('Principal/Financial/Reports', [
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
     * Display delinquent students list
     */
    public function delinquents(Request $request)
    {
        $sortBy = $request->input('sort', 'total_tunggakan');
        $sortDir = $request->input('dir', 'desc');

        // Get all unpaid bills grouped by student
        $delinquents = Bill::query()
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

        // Sort
        $delinquents = $delinquents->sortBy(
            $sortBy === 'nama' ? 'student.nama_lengkap' : $sortBy,
            SORT_REGULAR,
            $sortDir === 'desc'
        )->values();

        return Inertia::render('Principal/Financial/Delinquents', [
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
     * Export financial report to Excel
     */
    public function export(Request $request)
    {
        $month = $request->input('month', now()->month);
        $year = $request->input('year', now()->year);

        $startDate = Carbon::create($year, $month, 1)->startOfMonth();
        $endDate = Carbon::create($year, $month, 1)->endOfMonth();

        // Get verified payments in date range
        $payments = Payment::query()
            ->verified()
            ->whereBetween('tanggal_bayar', [$startDate, $endDate])
            ->with(['student.kelas', 'bill.paymentCategory'])
            ->orderBy('tanggal_bayar', 'desc')
            ->get();

        // Build export data
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

        $monthName = Carbon::create($year, $month, 1)->translatedFormat('F Y');
        $filename = "Laporan-Keuangan-{$monthName}.xlsx";

        return response()->streamDownload(function () use ($exportData) {
            $output = fopen('php://output', 'w');
            fputcsv($output, array_keys($exportData->first() ?? []));
            foreach ($exportData as $row) {
                fputcsv($output, $row);
            }
            fclose($output);
        }, $filename, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ]);
    }

    /**
     * Get summary statistics for a period
     */
    protected function getSummaryStatistics(Carbon $startDate, Carbon $endDate, ?int $categoryId = null): array
    {
        // Query verified payments in range
        $paymentsQuery = Payment::query()
            ->verified()
            ->whereBetween('tanggal_bayar', [$startDate, $endDate]);

        if ($categoryId) {
            $paymentsQuery->whereHas('bill', fn ($q) => $q->where('payment_category_id', $categoryId));
        }

        $payments = $paymentsQuery->get();

        $totalIncome = $payments->sum('nominal');
        $transactionCount = $payments->count();

        // Get all unpaid bills total
        $unpaidBillsQuery = Bill::query()->whereIn('status', ['belum_bayar', 'sebagian']);
        if ($categoryId) {
            $unpaidBillsQuery->where('payment_category_id', $categoryId);
        }
        $unpaidBills = $unpaidBillsQuery->get();

        $totalPiutang = $unpaidBills->sum(fn ($bill) => $bill->sisa_tagihan);

        // Calculate collectibility rate (monthly)
        $monthlyBillsQuery = Bill::query()
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

        // Breakdown by payment method
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
    protected function getCategoryBreakdown(Carbon $startDate, Carbon $endDate): array
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
        $overdueBills = Bill::query()
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
                'label' => Carbon::create(null, $i, 1)->translatedFormat('F'),
            ];
        }

        return $months;
    }

    /**
     * Get year options for filter (last 3 years)
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
