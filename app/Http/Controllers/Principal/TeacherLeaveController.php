<?php

namespace App\Http\Controllers\Principal;

use App\Http\Controllers\Controller;
use App\Models\TeacherLeave;
use App\Services\AttendanceService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class TeacherLeaveController extends Controller
{
    /**
     * Constructor untuk inject AttendanceService dependency
     */
    public function __construct(
        protected AttendanceService $attendanceService
    ) {}

    /**
     * Display list of teacher leave requests untuk approval
     * oleh Principal
     */
    public function index(Request $request): Response
    {
        $query = TeacherLeave::with(['teacher', 'approvedBy']);

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        } else {
            // Default: show pending requests
            $query->where('status', 'PENDING');
        }

        $leaves = $query->latest('created_at')->paginate(20);

        return Inertia::render('Principal/TeacherLeave/Index', [
            'title' => 'Approval Izin Guru',
            'leaves' => $leaves,
            'filters' => $request->only(['status']),
        ]);
    }

    /**
     * Approve teacher leave request
     */
    public function approve(Request $request, TeacherLeave $leave)
    {
        if ($leave->status !== 'PENDING') {
            return back()->with('error', 'Pengajuan izin sudah diproses sebelumnya');
        }

        $this->attendanceService->approveTeacherLeave($leave, auth()->user());

        return back()->with('success', 'Pengajuan izin guru berhasil disetujui');
    }

    /**
     * Reject teacher leave request
     */
    public function reject(Request $request, TeacherLeave $leave)
    {
        $validated = $request->validate([
            'rejection_reason' => 'required|string|max:500',
        ]);

        if ($leave->status !== 'PENDING') {
            return back()->with('error', 'Pengajuan izin sudah diproses sebelumnya');
        }

        $leave->reject(auth()->user(), $validated['rejection_reason']);

        return back()->with('success', 'Pengajuan izin guru ditolak');
    }
}
