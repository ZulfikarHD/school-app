<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\TeacherAttendance;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class TeacherAttendanceController extends Controller
{
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
     * Export teacher attendance to Excel for payroll
     * dengan filter tanggal dan status
     *
     * TODO Sprint 2: Implement Excel export for payroll
     */
    public function exportTeachers(Request $request)
    {
        // This will be implemented in Phase 5: Export Functionality
        return response()->json([
            'message' => 'Export functionality will be implemented in Phase 5'
        ]);
    }
}
