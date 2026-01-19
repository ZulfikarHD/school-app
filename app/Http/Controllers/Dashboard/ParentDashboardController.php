<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\LeaveRequest;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ParentDashboardController extends Controller
{
    /**
     * Menampilkan dashboard untuk Orang Tua dengan akses ke
     * child information, payments, grades, dan attendance summary
     * dimana data attendance ditampilkan per anak dengan warning untuk kehadiran rendah
     */
    public function index(Request $request)
    {
        $user = $request->user();
        $guardian = $user->guardian;

        // Default empty data jika guardian tidak ditemukan
        if (! $guardian) {
            return Inertia::render('Dashboard/ParentDashboard', [
                'stats' => [
                    'children' => [],
                    'pending_payments' => 0,
                    'recent_grades' => [],
                    'attendance_summary' => [],
                ],
                'childrenWithAttendance' => [],
                'pendingLeaveRequests' => 0,
            ]);
        }

        // Get all students linked ke guardian ini
        $children = $guardian->students()
            ->with(['kelas'])
            ->where('status', 'aktif')
            ->get();

        // Get date range untuk bulan ini
        $startOfMonth = Carbon::now()->startOfMonth()->format('Y-m-d');
        $endOfMonth = Carbon::now()->endOfMonth()->format('Y-m-d');

        // Build attendance summary untuk setiap anak
        $childrenWithAttendance = $children->map(function ($child) use ($startOfMonth, $endOfMonth) {
            $summary = $child->getAttendanceSummary($startOfMonth, $endOfMonth);

            // Hitung persentase kehadiran (hadir / total * 100)
            // Jika total = 0, persentase = 100 (tidak ada record = belum ada penilaian)
            $percentage = $summary['total'] > 0
                ? round(($summary['hadir'] / $summary['total']) * 100)
                : 100;

            return [
                'id' => $child->id,
                'nama_lengkap' => $child->nama_lengkap,
                'nama_panggilan' => $child->nama_panggilan,
                'kelas' => $child->kelas ? [
                    'id' => $child->kelas->id,
                    'nama_lengkap' => $child->kelas->nama_lengkap,
                ] : null,
                'attendance_summary' => [
                    'hadir' => $summary['hadir'],
                    'sakit' => $summary['sakit'],
                    'izin' => $summary['izin'],
                    'alpha' => $summary['alpha'],
                    'total' => $summary['total'],
                    'percentage' => $percentage,
                ],
            ];
        });

        // Get pending leave requests count untuk anak-anak
        $childIds = $children->pluck('id');
        $pendingLeaveRequests = LeaveRequest::whereIn('student_id', $childIds)
            ->pending()
            ->count();

        return Inertia::render('Dashboard/ParentDashboard', [
            'stats' => [
                'children' => $children,
                'pending_payments' => 0,
                'recent_grades' => [],
                'attendance_summary' => $childrenWithAttendance,
            ],
            'childrenWithAttendance' => $childrenWithAttendance,
            'pendingLeaveRequests' => $pendingLeaveRequests,
        ]);
    }
}
