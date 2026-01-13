<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Http\Requests\ApproveLeaveRequestRequest;
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
     * Approve atau reject leave request
     * dengan validasi dan rejection reason untuk reject action
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function approve(ApproveLeaveRequestRequest $request, LeaveRequest $leaveRequest)
    {
        try {
            $action = $request->validated('action');

            if ($action === 'approve') {
                $this->attendanceService->approveLeaveRequest(
                    $leaveRequest,
                    auth()->user()
                );

                return back()->with('success', 'Permohonan izin berhasil disetujui.');
            } else {
                // Reject action
                $reason = $request->validated('rejection_reason');
                $this->attendanceService->rejectLeaveRequest(
                    $leaveRequest,
                    auth()->user(),
                    $reason
                );

                return back()->with('success', 'Permohonan izin berhasil ditolak.');
            }
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal memproses permohonan: '.$e->getMessage());
        }
    }

    /**
     * Reject leave request dengan alasan
     * Deprecated: Use approve() method dengan action=reject instead
     *
     * @deprecated
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function reject(ApproveLeaveRequestRequest $request, LeaveRequest $leaveRequest)
    {
        // Redirect ke approve method untuk unified handling
        return $this->approve($request, $leaveRequest);
    }
}
