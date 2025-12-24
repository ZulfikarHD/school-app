<?php

namespace App\Http\Controllers\Parent;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreLeaveRequestRequest;
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
     * Display list leave requests yang diajukan parent
     * untuk semua anaknya dengan status dan history
     *
     * TODO Sprint 2: Implement UI dengan table history
     */
    public function index(): Response
    {
        $parent = auth()->user();

        // Get student IDs dari children
        $studentIds = $parent->guardian->students()->pluck('students.id');

        $leaveRequests = LeaveRequest::with(['student', 'reviewedBy'])
            ->whereIn('student_id', $studentIds)
            ->latest()
            ->get();

        return Inertia::render('Parent/LeaveRequest/Index', [
            'title' => 'Riwayat Permohonan Izin',
            'leaveRequests' => $leaveRequests,
        ]);
    }

    /**
     * Show form untuk submit leave request baru
     * dengan list anak dan option upload attachment
     *
     * TODO Sprint 2: Implement UI form submission
     */
    public function create(): Response
    {
        $parent = auth()->user();
        $children = $parent->guardian->students;

        return Inertia::render('Parent/LeaveRequest/Create', [
            'title' => 'Ajukan Permohonan Izin',
            'children' => $children,
        ]);
    }

    /**
     * Store leave request dengan attachment upload
     * dan notifikasi ke teacher (TODO Sprint 2)
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(StoreLeaveRequestRequest $request)
    {
        try {
            $data = $request->validated();

            // Handle file upload
            if ($request->hasFile('attachment')) {
                $path = $request->file('attachment')->store('leave-attachments', 'public');
                $data['attachment_path'] = $path;
            }

            $this->attendanceService->submitLeaveRequest($data, $request->user());

            return redirect()
                ->route('parent.leave-requests.index')
                ->with('success', 'Permohonan izin berhasil diajukan dan menunggu persetujuan.');
        } catch (\Exception $e) {
            return back()
                ->withInput()
                ->with('error', 'Gagal mengajukan permohonan: '.$e->getMessage());
        }
    }
}
