<?php

namespace App\Http\Controllers\Parent;

use App\Http\Controllers\Controller;
use App\Models\Student;
use App\Services\AttendanceService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ChildController extends Controller
{
    /**
     * Constructor untuk inject AttendanceService dependency
     */
    public function __construct(
        protected AttendanceService $attendanceService
    ) {}

    /**
     * Display list of children untuk parent yang login
     * dimana parent bisa punya multiple children di sekolah
     */
    public function index(Request $request)
    {
        $user = $request->user();

        // Get guardian record untuk user ini
        $guardian = $user->guardian;

        if (! $guardian) {
            return Inertia::render('Parent/Children/Index', [
                'children' => [],
                'message' => 'Data orang tua tidak ditemukan.',
            ]);
        }

        // Get all students linked ke guardian ini
        $children = $guardian->students()
            ->with(['guardians', 'primaryGuardian'])
            ->where('status', 'aktif')
            ->get();

        return Inertia::render('Parent/Children/Index', [
            'children' => $children,
        ]);
    }

    /**
     * Display detail profil anak untuk parent (read-only)
     * dengan validation bahwa parent hanya bisa view anak sendiri
     */
    public function show(Request $request, Student $student)
    {
        $user = $request->user();

        // Get guardian record untuk user ini
        $guardian = $user->guardian;

        if (! $guardian) {
            abort(403, 'Data orang tua tidak ditemukan.');
        }

        // Check apakah student ini adalah anak dari guardian ini
        $isOwnChild = $guardian->students()->where('students.id', $student->id)->exists();

        if (! $isOwnChild) {
            abort(403, 'Anda tidak memiliki akses ke data siswa ini.');
        }

        // Load relationships untuk detail view
        $student->load([
            'guardians',
            'primaryGuardian',
            'classHistory.kelas',
            // Note: Tidak load statusHistory karena parent tidak perlu lihat history internal
        ]);

        return Inertia::render('Parent/Children/Show', [
            'student' => $student,
        ]);
    }

    /**
     * Display attendance history untuk anak
     * dengan calendar view dan summary statistics
     */
    public function attendance(Request $request, Student $student)
    {
        $user = $request->user();

        // Get guardian record untuk user ini
        $guardian = $user->guardian;

        if (! $guardian) {
            abort(403, 'Data orang tua tidak ditemukan.');
        }

        // Check apakah student ini adalah anak dari guardian ini
        $isOwnChild = $guardian->students()->where('students.id', $student->id)->exists();

        if (! $isOwnChild) {
            abort(403, 'Anda tidak memiliki akses ke data siswa ini.');
        }

        // Get date range from request or default to current month
        $startDate = $request->input('start_date', Carbon::now()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->input('end_date', Carbon::now()->endOfMonth()->format('Y-m-d'));

        // Get attendance report
        $report = $this->attendanceService->getStudentAttendanceReport(
            $student->id,
            $startDate,
            $endDate
        );

        // Load student with class info
        $student->load('kelas');

        return Inertia::render('Parent/Children/Attendance', [
            'title' => 'Riwayat Kehadiran',
            'student' => $student,
            'attendances' => $report['details'],
            'summary' => $report['summary'],
            'filters' => [
                'start_date' => $startDate,
                'end_date' => $endDate,
            ],
        ]);
    }

    /**
     * Export attendance report to PDF untuk parent
     *
     * TODO Sprint 2: Implement PDF export
     */
    public function exportAttendance(Request $request, Student $student)
    {
        // This will be implemented in Phase 5: Export Functionality
        return response()->json([
            'message' => 'Export functionality will be implemented in Phase 5',
        ]);
    }
}
