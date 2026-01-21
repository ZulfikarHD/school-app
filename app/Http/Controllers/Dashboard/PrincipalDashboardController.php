<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Bill;
use App\Models\Payment;
use App\Models\SchoolClass;
use App\Models\Student;
use App\Models\StudentAttendance;
use App\Models\TeacherAttendance;
use App\Models\TeacherLeave;
use App\Models\User;
use App\Services\AttendanceService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Inertia\Inertia;

class PrincipalDashboardController extends Controller
{
    /**
     * Constructor untuk inject AttendanceService dependency
     */
    public function __construct(
        protected AttendanceService $attendanceService
    ) {}

    /**
     * Menampilkan dashboard untuk Kepala Sekolah dengan akses ke
     * reports, analytics, dan monitoring keseluruhan sistem sekolah
     * dengan real-time attendance data
     */
    public function index()
    {
        $today = Carbon::today();

        // Get basic stats
        $totalStudents = Student::where('status', 'aktif')->count();
        $totalTeachers = User::where('role', 'TEACHER')->count();
        $totalClasses = SchoolClass::where('is_active', true)->count();

        // Get today's student attendance
        $todayAttendances = StudentAttendance::whereDate('tanggal', $today)->get();
        $presentCount = $todayAttendances->whereIn('status', ['H'])->count();
        $absentCount = $todayAttendances->whereIn('status', ['A', 'I', 'S'])->count();
        $attendanceRate = $totalStudents > 0 ? round(($presentCount / $totalStudents) * 100) : 0;

        // Get classes that haven't recorded attendance today
        $classesWithAttendance = StudentAttendance::whereDate('tanggal', $today)
            ->distinct('class_id')
            ->pluck('class_id');

        $classesNotRecorded = SchoolClass::where('is_active', true)
            ->whereNotIn('id', $classesWithAttendance)
            ->get(['id', 'tingkat', 'nama'])
            ->map(function ($class) {
                $class->nama_lengkap = "Kelas {$class->tingkat}{$class->nama}";

                return $class;
            });

        // Get teacher presence data
        $teacherAttendances = TeacherAttendance::whereDate('tanggal', $today)->get();
        $clockedInCount = $teacherAttendances->count();

        $lateTeachers = $teacherAttendances->where('is_late', true)
            ->map(function ($attendance) {
                return [
                    'id' => $attendance->teacher_id,
                    'name' => $attendance->teacher->name,
                    'late_minutes' => $attendance->late_minutes,
                ];
            })->values();

        $clockedInTeacherIds = $teacherAttendances->pluck('teacher_id');
        $absentTeachers = User::where('role', 'TEACHER')
            ->whereNotIn('id', $clockedInTeacherIds)
            ->get(['id', 'name'])
            ->map(function ($teacher) {
                return [
                    'id' => $teacher->id,
                    'name' => $teacher->name,
                ];
            });

        // Get pending teacher leave requests
        $pendingTeacherLeaves = TeacherLeave::where('status', 'PENDING')
            ->with('teacher')
            ->count();

        // Get attendance summary using service
        $attendanceSummary = $this->attendanceService->getTodayAttendanceSummary();

        // Get financial summary untuk Principal
        $financialSummary = $this->getFinancialSummary();

        return Inertia::render('Dashboard/PrincipalDashboard', [
            'stats' => [
                'total_students' => $totalStudents,
                'total_teachers' => $totalTeachers,
                'total_classes' => $totalClasses,
                'attendance_rate' => $attendanceRate,
            ],
            'todayAttendance' => [
                'total_students' => $totalStudents,
                'present' => $presentCount,
                'absent' => $absentCount,
                'late' => 0, // Will be implemented when late tracking is added
                'percentage' => $attendanceRate,
            ],
            'attendanceSummary' => $attendanceSummary,
            'classesNotRecorded' => $classesNotRecorded,
            'teacherPresence' => [
                'total_teachers' => $totalTeachers,
                'clocked_in' => $clockedInCount,
                'late_teachers' => $lateTeachers,
                'absent_teachers' => $absentTeachers,
            ],
            'pendingTeacherLeaves' => $pendingTeacherLeaves,
            'financialSummary' => $financialSummary,
        ]);
    }

    /**
     * Get financial summary untuk dashboard Principal
     *
     * Menghitung pendapatan bulanan, piutang, dan kolektibilitas
     * untuk monitoring kesehatan keuangan sekolah
     */
    protected function getFinancialSummary(): array
    {
        $startOfMonth = Carbon::now()->startOfMonth();
        $endOfMonth = Carbon::now()->endOfMonth();

        // Monthly verified payments
        $monthlyPayments = Payment::query()
            ->whereBetween('tanggal_bayar', [$startOfMonth, $endOfMonth])
            ->verified()
            ->get();

        $monthlyIncome = $monthlyPayments->sum('nominal');
        $transactionCount = $monthlyPayments->count();

        // Total piutang (all unpaid bills)
        $unpaidBills = Bill::query()
            ->whereIn('status', ['belum_bayar', 'sebagian'])
            ->get();

        $totalPiutang = $unpaidBills->sum(fn ($bill) => $bill->sisa_tagihan);

        // Calculate collectibility rate
        $monthlyBills = Bill::query()
            ->whereMonth('created_at', $startOfMonth->month)
            ->whereYear('created_at', $startOfMonth->year)
            ->get();

        $expectedIncome = $monthlyBills->sum('nominal');
        $collectibility = $expectedIncome > 0
            ? round(($monthlyIncome / $expectedIncome) * 100, 1)
            : 100;

        // Overdue students count
        $overdueStudents = $unpaidBills
            ->filter(fn ($bill) => $bill->isOverdue())
            ->pluck('student_id')
            ->unique()
            ->count();

        return [
            'monthly_income' => $monthlyIncome,
            'formatted_monthly_income' => 'Rp '.number_format($monthlyIncome, 0, ',', '.'),
            'transaction_count' => $transactionCount,
            'total_piutang' => $totalPiutang,
            'formatted_piutang' => 'Rp '.number_format($totalPiutang, 0, ',', '.'),
            'collectibility' => $collectibility,
            'overdue_students' => $overdueStudents,
        ];
    }

    /**
     * Get real-time attendance metrics API endpoint
     * untuk auto-refresh dashboard data
     */
    public function getAttendanceMetrics(Request $request)
    {
        $summary = $this->attendanceService->getTodayAttendanceSummary();
        $classesWithoutAttendance = $this->attendanceService->getClassesWithoutAttendance(
            Carbon::today()->format('Y-m-d')
        );
        $absentTeachers = $this->attendanceService->getTeacherAbsenceToday();

        return response()->json([
            'summary' => $summary,
            'classes_without_attendance' => $classesWithoutAttendance,
            'absent_teachers' => $absentTeachers,
            'last_updated' => now()->toIso8601String(),
        ]);
    }
}
