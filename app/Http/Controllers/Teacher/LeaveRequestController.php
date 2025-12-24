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
     *
     * TODO Sprint 2: Implement UI dengan table leave requests
     */
    public function index(): Response
    {
        $teacher = auth()->user();

        // Get pending leave requests untuk siswa di kelas yang diampu
        $leaveRequests = LeaveRequest::with(['student', 'submittedBy'])
            ->whereHas('student.kelas', function ($query) use ($teacher) {
                $query->where('wali_kelas_id', $teacher->id);
            })
            ->pending()
            ->latest()
            ->get();

        return Inertia::render('Teacher/LeaveRequest/Index', [
            'title' => 'Verifikasi Permohonan Izin',
            'leaveRequests' => $leaveRequests,
        ]);
    }

    /**
     * Approve leave request dan auto-sync ke attendance
     * dengan membuat attendance records untuk date range
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function approve(ApproveLeaveRequestRequest $request, LeaveRequest $leaveRequest)
    {
        try {
            if ($request->input('action') === 'approve') {
                $this->attendanceService->approveLeaveRequest(
                    $leaveRequest,
                    $request->user()
                );
                $message = 'Permohonan izin berhasil disetujui.';
            } else {
                $this->attendanceService->rejectLeaveRequest(
                    $leaveRequest,
                    $request->user(),
                    $request->input('rejection_reason')
                );
                $message = 'Permohonan izin berhasil ditolak.';
            }

            return back()->with('success', $message);
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal memproses permohonan: '.$e->getMessage());
        }
    }
}
