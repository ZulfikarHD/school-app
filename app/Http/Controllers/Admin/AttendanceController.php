<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AttendanceAuditLog;
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
            'correction_reason' => 'nullable|string|max:500',
        ]);

        // Store old values for audit log
        $oldStatus = $attendance->status;
        $oldKeterangan = $attendance->keterangan;

        $attendance->update([
            'status' => $validated['status'],
            'keterangan' => $validated['keterangan'],
            'recorded_by' => auth()->id(),
            'recorded_at' => now(),
        ]);

        // Log changes
        if ($oldStatus !== $validated['status']) {
            AttendanceAuditLog::logChange(
                attendanceType: 'student_attendance',
                attendanceId: $attendance->id,
                userId: auth()->id(),
                fieldChanged: 'status',
                oldValue: $oldStatus,
                newValue: $validated['status'],
                reason: $validated['correction_reason'] ?? 'Koreksi data oleh admin'
            );
        }

        if ($oldKeterangan !== $validated['keterangan']) {
            AttendanceAuditLog::logChange(
                attendanceType: 'student_attendance',
                attendanceId: $attendance->id,
                userId: auth()->id(),
                fieldChanged: 'keterangan',
                oldValue: $oldKeterangan,
                newValue: $validated['keterangan'],
                reason: $validated['correction_reason'] ?? 'Koreksi data oleh admin'
            );
        }

        return back()->with('success', 'Data presensi berhasil diperbarui');
    }

    /**
     * Delete attendance record dengan audit log
     */
    public function destroy(Request $request, StudentAttendance $attendance)
    {
        $validated = $request->validate([
            'deletion_reason' => 'nullable|string|max:500',
        ]);

        // Log deletion
        AttendanceAuditLog::logChange(
            attendanceType: 'student_attendance',
            attendanceId: $attendance->id,
            userId: auth()->id(),
            fieldChanged: 'deleted',
            oldValue: json_encode([
                'student_id' => $attendance->student_id,
                'tanggal' => $attendance->tanggal,
                'status' => $attendance->status,
            ]),
            newValue: null,
            reason: $validated['deletion_reason'] ?? 'Penghapusan data oleh admin'
        );

        $attendance->delete();

        return back()->with('success', 'Data presensi berhasil dihapus');
    }

    /**
     * Generate comprehensive attendance report dengan advanced filters
     * untuk analisis dan monitoring oleh admin/principal
     */
    public function generateReport(Request $request): Response
    {
        $validated = $request->validate([
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'class_id' => 'nullable|exists:classes,id',
            'status' => 'nullable|in:H,I,S,A',
            'student_id' => 'nullable|exists:students,id',
        ]);

        $filters = array_filter($validated);

        $attendances = $this->attendanceService->getAttendanceReport($filters);

        // Calculate statistics
        $statistics = [
            'total_records' => $attendances->count(),
            'hadir' => $attendances->where('status', 'H')->count(),
            'izin' => $attendances->where('status', 'I')->count(),
            'sakit' => $attendances->where('status', 'S')->count(),
            'alpha' => $attendances->where('status', 'A')->count(),
        ];

        $classes = SchoolClass::active()->get();

        return Inertia::render('Admin/Attendance/Students/Report', [
            'title' => 'Laporan Presensi Siswa',
            'attendances' => $attendances,
            'statistics' => $statistics,
            'classes' => $classes,
            'filters' => $filters,
        ]);
    }

    /**
     * Export student attendance to Excel
     * untuk reporting dan analisis
     */
    public function exportStudents(Request $request)
    {
        // This will be implemented in excel-pdf-export todo
        return response()->json([
            'message' => 'Export functionality will be implemented in Phase 3',
        ]);
    }

    /**
     * Export student attendance to PDF
     * untuk reporting dan dokumentasi
     */
    public function exportPdf(Request $request)
    {
        // This will be implemented in excel-pdf-export todo
        return response()->json([
            'message' => 'Export functionality will be implemented in Phase 3',
        ]);
    }

    /**
     * Get attendance statistics API endpoint
     * untuk dashboard widgets dan charts
     */
    public function getStatistics(Request $request)
    {
        $validated = $request->validate([
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'class_id' => 'nullable|exists:classes,id',
        ]);

        $filters = array_filter($validated);
        $attendances = $this->attendanceService->getAttendanceReport($filters);

        $statistics = [
            'total_records' => $attendances->count(),
            'hadir' => $attendances->where('status', 'H')->count(),
            'izin' => $attendances->where('status', 'I')->count(),
            'sakit' => $attendances->where('status', 'S')->count(),
            'alpha' => $attendances->where('status', 'A')->count(),
        ];

        return response()->json($statistics);
    }
}
