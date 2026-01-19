<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\ApproveLeaveRequestRequest;
use App\Models\LeaveRequest;
use App\Models\SchoolClass;
use App\Services\AttendanceService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class LeaveRequestController extends Controller
{
    /**
     * Constructor untuk inject AttendanceService dependency
     * yang digunakan untuk proses approve/reject leave request
     */
    public function __construct(
        protected AttendanceService $attendanceService
    ) {}

    /**
     * Display list semua leave requests dari seluruh siswa
     * dengan filter by status, class, dan date range
     * untuk Admin/TU melakukan verifikasi
     */
    public function index(Request $request): Response
    {
        $filters = [
            'status' => $request->input('status'),
            'class_id' => $request->input('class_id'),
            'start_date' => $request->input('start_date'),
            'end_date' => $request->input('end_date'),
            'search' => $request->input('search'),
        ];

        // Query leave requests dengan eager loading
        $leaveRequests = LeaveRequest::with(['student.kelas', 'submittedBy', 'reviewedBy'])
            ->when($filters['status'], function ($query, $status) {
                $query->where('status', $status);
            })
            ->when($filters['class_id'], function ($query, $classId) {
                $query->whereHas('student', function ($q) use ($classId) {
                    $q->where('kelas_id', $classId);
                });
            })
            ->when($filters['start_date'], function ($query, $startDate) {
                $query->whereDate('tanggal_mulai', '>=', $startDate);
            })
            ->when($filters['end_date'], function ($query, $endDate) {
                $query->whereDate('tanggal_selesai', '<=', $endDate);
            })
            ->when($filters['search'], function ($query, $search) {
                $query->whereHas('student', function ($q) use ($search) {
                    $q->where('nama_lengkap', 'like', "%{$search}%")
                        ->orWhere('nis', 'like', "%{$search}%");
                });
            })
            ->latest()
            ->paginate(20)
            ->withQueryString();

        // Calculate stats untuk seluruh sekolah (tidak di-filter)
        $stats = [
            'pending' => LeaveRequest::where('status', 'PENDING')->count(),
            'approved' => LeaveRequest::where('status', 'APPROVED')->count(),
            'rejected' => LeaveRequest::where('status', 'REJECTED')->count(),
        ];

        // Get list kelas untuk filter dropdown
        $classes = SchoolClass::active()
            ->orderBy('tingkat')
            ->orderBy('nama')
            ->get(['id', 'tingkat', 'nama'])
            ->map(function ($class) {
                return [
                    'id' => $class->id,
                    'nama' => $class->nama_lengkap,
                ];
            });

        return Inertia::render('Admin/LeaveRequests/Index', [
            'title' => 'Verifikasi Permohonan Izin Siswa',
            'leaveRequests' => $leaveRequests,
            'stats' => $stats,
            'classes' => $classes,
            'filters' => $filters,
        ]);
    }

    /**
     * Approve atau reject leave request dari siswa
     * dengan validasi dan rejection reason untuk reject action
     * menggunakan AttendanceService untuk sync ke student_attendances
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
                // Reject action dengan alasan wajib
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
}
