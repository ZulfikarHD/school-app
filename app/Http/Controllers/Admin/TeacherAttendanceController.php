<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\TeacherAttendance;
use App\Models\User;
use App\Services\AttendanceService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class TeacherAttendanceController extends Controller
{
    /**
     * Constructor untuk inject AttendanceService dependency
     */
    public function __construct(
        protected AttendanceService $attendanceService
    ) {}

    /**
     * Display rekap presensi guru dengan filter tanggal dan status
     * untuk monitoring kehadiran dan keterlambatan
     *
     * TODO Sprint 2: Implement UI dengan summary statistics
     */
    public function index(Request $request): Response
    {
        $query = TeacherAttendance::with('teacher');

        // Filter berdasarkan tanggal
        if ($request->filled('date')) {
            $query->whereDate('tanggal', $request->input('date'));
        }

        // Filter berdasarkan status
        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        // Filter berdasarkan keterlambatan
        if ($request->filled('is_late')) {
            $query->where('is_late', $request->boolean('is_late'));
        }

        $attendances = $query->latest('tanggal')->paginate(50);

        // Calculate summary
        $summary = [
            'total_hadir' => TeacherAttendance::whereDate('tanggal', $request->input('date', today()))
                ->where('status', 'HADIR')
                ->count(),
            'total_terlambat' => TeacherAttendance::whereDate('tanggal', $request->input('date', today()))
                ->where('status', 'TERLAMBAT')
                ->count(),
            'total_izin' => TeacherAttendance::whereDate('tanggal', $request->input('date', today()))
                ->where('status', 'IZIN')
                ->count(),
            'total_alpha' => TeacherAttendance::whereDate('tanggal', $request->input('date', today()))
                ->where('status', 'ALPHA')
                ->count(),
        ];

        return Inertia::render('Admin/Attendance/Teachers/Index', [
            'title' => 'Rekap Presensi Guru',
            'attendances' => $attendances,
            'summary' => $summary,
            'filters' => $request->only(['date', 'status', 'is_late']),
        ]);
    }

    /**
     * Generate comprehensive teacher attendance report
     * untuk analisis dan payroll processing
     */
    public function generateReport(Request $request): Response
    {
        $validated = $request->validate([
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'teacher_id' => 'nullable|exists:users,id',
        ]);

        $filters = array_filter($validated);

        $attendances = $this->attendanceService->getTeacherAttendanceReport($filters);

        // Calculate work hours for each teacher
        $teachers = User::where('role', 'TEACHER')->where('is_active', true)->get();

        $statistics = [
            'total_records' => $attendances->count(),
            'total_present' => $attendances->whereNotNull('clock_in')->count(),
            'total_late' => $attendances->where('is_late', true)->count(),
            'average_hours' => $attendances->filter(function ($att) {
                return $att->clock_in && $att->clock_out;
            })->avg(function ($att) {
                return \Carbon\Carbon::parse($att->clock_in)->diffInHours($att->clock_out, true);
            }),
        ];

        return Inertia::render('Admin/Attendance/Teachers/Report', [
            'title' => 'Laporan Presensi Guru',
            'attendances' => $attendances,
            'statistics' => $statistics,
            'teachers' => $teachers,
            'filters' => $filters,
        ]);
    }

    /**
     * Export teacher attendance to Excel for payroll
     * dengan filter tanggal dan status
     */
    public function exportTeachers(Request $request)
    {
        // This will be implemented in excel-pdf-export todo
        return response()->json([
            'message' => 'Export functionality will be implemented in Phase 3',
        ]);
    }

    /**
     * Export teacher attendance for payroll processing
     * dengan calculation work hours dan late penalties
     */
    public function exportPayroll(Request $request)
    {
        // This will be implemented in excel-pdf-export todo
        return response()->json([
            'message' => 'Payroll export will be implemented in Phase 3',
        ]);
    }
}
