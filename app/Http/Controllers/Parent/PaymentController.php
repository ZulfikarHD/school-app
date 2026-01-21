<?php

namespace App\Http\Controllers\Parent;

use App\Http\Controllers\Controller;
use App\Models\Bill;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

/**
 * Controller untuk menampilkan tagihan dan pembayaran kepada orang tua
 *
 * Orang tua dapat melihat tagihan untuk semua anak mereka,
 * status pembayaran, dan riwayat pembayaran
 */
class PaymentController extends Controller
{
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

        // Calculate summary
        $summary = $this->calculateSummary($studentIds);

        return Inertia::render('Parent/Payments/Index', [
            'children' => $children,
            'activeBills' => $activeBills,
            'paidBills' => $paidBills,
            'summary' => $summary,
        ]);
    }

    /**
     * Format bill data for frontend
     */
    protected function formatBill(Bill $bill): array
    {
        $isOverdue = $bill->isOverdue();

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
            'tanggal_jatuh_tempo' => $bill->tanggal_jatuh_tempo?->format('Y-m-d'),
            'formatted_due_date' => $bill->tanggal_jatuh_tempo?->format('d M Y'),
            'created_at' => $bill->created_at?->format('Y-m-d H:i:s'),
        ];
    }

    /**
     * Calculate payment summary for students
     */
    protected function calculateSummary($studentIds): array
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

        return [
            'total_tunggakan' => $totalTunggakan,
            'formatted_tunggakan' => 'Rp '.number_format($totalTunggakan, 0, ',', '.'),
            'total_belum_bayar' => $totalBelumBayar,
            'total_sebagian' => $totalSebagian,
            'total_overdue' => $overdueCount,
            'total_lunas_bulan_ini' => $paidThisMonth,
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
            'nearest_due_date' => null,
            'nearest_bill' => null,
        ];
    }
}
