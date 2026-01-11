<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\LeaveRequest;
use App\Services\AttendanceService;
use Inertia\Inertia;
use Inertia\Response;

class LeaveRequestController extends Controller
{
    /**
     * Constructor untuk inject AttendanceService dependency
     */
    public function __construct(
        protected AttendanceService $attendanceService
    ) {}

    /**
     * Display list leave requests yang perlu diverifikasi
     * untuk siswa di kelas yang diampu teacher
     */
    public function index(): Response
    {
        $teacher = auth()->user();

        // Get leave requests untuk siswa di kelas yang diampu
        $leaveRequests = LeaveRequest::with(['student', 'submittedBy'])
            ->whereHas('student.kelas', function ($query) use ($teacher) {
                $query->where('wali_kelas_id', $teacher->id);
            })
            ->latest()
            ->paginate(20);

        // Get stats
        $baseQuery = LeaveRequest::whereHas('student.kelas', function ($query) use ($teacher) {
            $query->where('wali_kelas_id', $teacher->id);
        });

        $stats = [
            'pending' => (clone $baseQuery)->where('status', 'PENDING')->count(),
            'approved' => (clone $baseQuery)->where('status', 'APPROVED_TEACHER')->count(),
            'rejected' => (clone $baseQuery)->where('status', 'REJECTED')->count(),
        ];

        return Inertia::render('Teacher/LeaveRequest/Index', [
            'title' => 'Verifikasi Permohonan Izin',
            'leaveRequests' => $leaveRequests,
            'stats' => $stats,
        ]);
    }

    /**
     * Approve leave request dan auto-sync ke attendance
     * dengan membuat attendance records untuk date range
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function approve(LeaveRequest $leaveRequest)
    {
        try {
            $this->attendanceService->approveLeaveRequest(
                $leaveRequest,
                auth()->user()
            );

            return back()->with('success', 'Permohonan izin berhasil disetujui.');
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal menyetujui permohonan: '.$e->getMessage());
        }
    }

    /**
     * Reject leave request
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function reject(LeaveRequest $leaveRequest)
    {
        try {
            $this->attendanceService->rejectLeaveRequest(
                $leaveRequest,
                auth()->user(),
                null
            );

            return back()->with('success', 'Permohonan izin berhasil ditolak.');
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal menolak permohonan: '.$e->getMessage());
        }
    }
}
