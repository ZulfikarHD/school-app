<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Bill;
use App\Models\Payment;
use App\Models\Student;
use App\Models\User;
use Carbon\Carbon;
use Inertia\Inertia;

/**
 * Controller untuk menampilkan dashboard Admin/TU
 *
 * Menyediakan statistik overview untuk Student Management,
 * Payment Management, PSB, dan User Management
 */
class AdminDashboardController extends Controller
{
    /**
     * Menampilkan dashboard untuk Admin/TU dengan akses ke
     * Student Management, Payment Management, PSB, dan User Management
     */
    public function index()
    {
        // Get basic counts
        $totalStudents = Student::where('status', 'aktif')->count();
        $totalUsers = User::count();

        // Get payment summary untuk hari ini
        $paymentSummary = $this->getPaymentSummary();

        return Inertia::render('Dashboard/AdminDashboard', [
            'stats' => [
                'total_students' => $totalStudents,
                'total_payments' => $paymentSummary['total_payments'],
                'pending_psb' => 0, // PSB belum diimplementasi
                'total_users' => $totalUsers,
            ],
            'paymentSummary' => $paymentSummary,
        ]);
    }

    /**
     * Get payment summary untuk dashboard
     *
     * Menghitung pendapatan hari ini, pending verifikasi,
     * dan tagihan jatuh tempo
     */
    protected function getPaymentSummary(): array
    {
        $today = Carbon::today();

        // Today's verified payments
        $todayPayments = Payment::query()
            ->whereDate('tanggal_bayar', $today)
            ->verified()
            ->get();

        $todayIncome = $todayPayments->sum('nominal');
        $todayCount = $todayPayments->count();

        // Pending verification count
        $pendingVerification = Payment::pending()->count();

        // Overdue bills count
        $overdueBills = Bill::query()
            ->whereIn('status', ['belum_bayar', 'sebagian'])
            ->get()
            ->filter(fn ($bill) => $bill->isOverdue());

        $overdueCount = $overdueBills->count();
        $overdueAmount = $overdueBills->sum(fn ($bill) => $bill->sisa_tagihan);

        // Total unpaid bills
        $totalUnpaid = Bill::query()
            ->whereIn('status', ['belum_bayar', 'sebagian'])
            ->count();

        return [
            'total_payments' => $todayCount,
            'today_income' => $todayIncome,
            'formatted_today_income' => 'Rp '.number_format($todayIncome, 0, ',', '.'),
            'pending_verification' => $pendingVerification,
            'overdue_count' => $overdueCount,
            'overdue_amount' => $overdueAmount,
            'formatted_overdue_amount' => 'Rp '.number_format($overdueAmount, 0, ',', '.'),
            'total_unpaid_bills' => $totalUnpaid,
        ];
    }
}
