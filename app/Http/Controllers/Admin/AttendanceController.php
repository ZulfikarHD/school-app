<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SchoolClass;
use App\Models\StudentAttendance;
use App\Services\AttendanceService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class AttendanceController extends Controller
{
    /**
     * Constructor untuk inject AttendanceService dependency
     */
    public function __construct(
        protected AttendanceService $attendanceService
    ) {}

    /**
     * Display rekap attendance siswa dengan filter kelas, tanggal, status
     * untuk monitoring dan reporting oleh admin
     *
     * TODO Sprint 2: Implement UI dengan advanced filters dan export
     */
    public function studentsIndex(Request $request): Response
    {
        $query = StudentAttendance::with(['student', 'class', 'recordedBy']);

        // Filter berdasarkan kelas
        if ($request->filled('class_id')) {
            $query->where('class_id', $request->input('class_id'));
        }

        // Filter berdasarkan tanggal
        if ($request->filled('date')) {
            $query->whereDate('tanggal', $request->input('date'));
        }

        // Filter berdasarkan status
        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        $attendances = $query->latest('tanggal')->paginate(50);

        $classes = SchoolClass::active()->get();

        return Inertia::render('Admin/Attendance/StudentsIndex', [
            'title' => 'Rekap Presensi Siswa',
            'attendances' => $attendances,
            'classes' => $classes,
            'filters' => $request->only(['class_id', 'date', 'status']),
        ]);
    }

    /**
     * Show form untuk koreksi data attendance
     * yang memungkinkan admin untuk edit/delete records
     *
     * TODO Sprint 2: Implement UI correction form
     */
    public function correction(): Response
    {
        return Inertia::render('Admin/Attendance/Correction', [
            'title' => 'Koreksi Data Presensi',
        ]);
    }
}
