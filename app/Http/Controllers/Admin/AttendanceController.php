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

        return Inertia::render('Admin/Attendance/Students/Index', [
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
        return Inertia::render('Admin/Attendance/Students/Correction', [
            'title' => 'Koreksi Data Presensi',
        ]);
    }

    /**
     * Update attendance record untuk koreksi data
     * dengan audit trail
     */
    public function update(Request $request, StudentAttendance $attendance)
    {
        $validated = $request->validate([
            'status' => 'required|in:H,I,S,A',
            'keterangan' => 'nullable|string|max:255',
        ]);

        $attendance->update([
            'status' => $validated['status'],
            'keterangan' => $validated['keterangan'],
            'recorded_by' => auth()->id(),
            'recorded_at' => now(),
        ]);

        return back()->with('success', 'Data presensi berhasil diperbarui');
    }

    /**
     * Delete attendance record
     */
    public function destroy(StudentAttendance $attendance)
    {
        $attendance->delete();

        return back()->with('success', 'Data presensi berhasil dihapus');
    }

    /**
     * Export student attendance to Excel
     * untuk reporting dan analisis
     *
     * TODO Sprint 2: Implement Excel export with filters
     */
    public function exportStudents(Request $request)
    {
        // This will be implemented in Phase 5: Export Functionality
        return response()->json([
            'message' => 'Export functionality will be implemented in Phase 5'
        ]);
    }
}
