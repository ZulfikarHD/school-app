<?php

namespace App\Http\Controllers\Parent;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreLeaveRequestRequest;
use App\Http\Requests\UpdateLeaveRequestRequest;
use App\Models\LeaveRequest;
use App\Services\AttendanceService;
use Illuminate\Http\RedirectResponse;
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

        // Check if parent has guardian record
        if (! $parent->guardian) {
            return Inertia::render('Parent/LeaveRequest/Index', [
                'title' => 'Riwayat Permohonan Izin',
                'leaveRequests' => [],
                'error' => 'Data wali belum terdaftar. Silakan hubungi administrasi sekolah.',
            ]);
        }

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

        // Check if parent has guardian record
        if (! $parent->guardian) {
            return Inertia::render('Parent/LeaveRequest/Create', [
                'title' => 'Ajukan Permohonan Izin',
                'children' => [],
                'error' => 'Data wali belum terdaftar. Silakan hubungi administrasi sekolah.',
            ]);
        }

        $children = $parent->guardian->students;

        return Inertia::render('Parent/LeaveRequest/Create', [
            'title' => 'Ajukan Permohonan Izin',
            'children' => $children,
        ]);
    }

    /**
     * Store leave request dengan attachment upload
     * dan notifikasi ke teacher (TODO Sprint 2)
     */
    public function store(StoreLeaveRequestRequest $request): RedirectResponse
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

    /**
     * Show form untuk edit leave request yang masih PENDING
     * dengan validasi ownership dan status
     */
    public function edit(LeaveRequest $leaveRequest): Response|RedirectResponse
    {
        $parent = auth()->user();

        // Check if parent has guardian record
        if (! $parent->guardian) {
            return redirect()
                ->route('parent.leave-requests.index')
                ->with('error', 'Data wali belum terdaftar. Silakan hubungi administrasi sekolah.');
        }

        // Validate ownership: check apakah request ini milik anak dari parent ini
        $studentIds = $parent->guardian->students()->pluck('students.id');
        if (! $studentIds->contains($leaveRequest->student_id)) {
            return redirect()
                ->route('parent.leave-requests.index')
                ->with('error', 'Anda tidak memiliki akses untuk mengubah permohonan ini.');
        }

        // Hanya PENDING request yang bisa di-edit
        if ($leaveRequest->status !== 'PENDING') {
            return redirect()
                ->route('parent.leave-requests.index')
                ->with('error', 'Hanya permohonan dengan status PENDING yang dapat diubah.');
        }

        $children = $parent->guardian->students;

        return Inertia::render('Parent/LeaveRequest/Edit', [
            'title' => 'Edit Permohonan Izin',
            'children' => $children,
            'leaveRequest' => $leaveRequest->load('student'),
        ]);
    }

    /**
     * Update leave request yang masih PENDING
     * dengan validasi overlap dan ownership
     */
    public function update(UpdateLeaveRequestRequest $request, LeaveRequest $leaveRequest): RedirectResponse
    {
        try {
            $data = $request->validated();

            // Handle file upload jika ada file baru
            if ($request->hasFile('attachment')) {
                // Delete old attachment jika ada
                if ($leaveRequest->attachment_path) {
                    \Storage::disk('public')->delete($leaveRequest->attachment_path);
                }

                $path = $request->file('attachment')->store('leave-attachments', 'public');
                $data['attachment_path'] = $path;
            }

            // Update leave request
            $leaveRequest->update([
                'student_id' => $data['student_id'],
                'jenis' => $data['jenis'],
                'tanggal_mulai' => $data['tanggal_mulai'],
                'tanggal_selesai' => $data['tanggal_selesai'],
                'alasan' => $data['alasan'],
                'attachment_path' => $data['attachment_path'] ?? $leaveRequest->attachment_path,
            ]);

            return redirect()
                ->route('parent.leave-requests.index')
                ->with('success', 'Permohonan izin berhasil diperbarui.');
        } catch (\Exception $e) {
            return back()
                ->withInput()
                ->with('error', 'Gagal memperbarui permohonan: '.$e->getMessage());
        }
    }

    /**
     * Cancel/Delete leave request yang masih PENDING
     * dengan validasi ownership dan status
     */
    public function destroy(LeaveRequest $leaveRequest): RedirectResponse
    {
        $parent = auth()->user();

        // Check if parent has guardian record
        if (! $parent->guardian) {
            return redirect()
                ->route('parent.leave-requests.index')
                ->with('error', 'Data wali belum terdaftar. Silakan hubungi administrasi sekolah.');
        }

        // Validate ownership: check apakah request ini milik anak dari parent ini
        $studentIds = $parent->guardian->students()->pluck('students.id');
        if (! $studentIds->contains($leaveRequest->student_id)) {
            return redirect()
                ->route('parent.leave-requests.index')
                ->with('error', 'Anda tidak memiliki akses untuk membatalkan permohonan ini.');
        }

        // Hanya PENDING request yang bisa di-cancel
        if ($leaveRequest->status !== 'PENDING') {
            return redirect()
                ->route('parent.leave-requests.index')
                ->with('error', 'Hanya permohonan dengan status PENDING yang dapat dibatalkan.');
        }

        try {
            // Delete attachment jika ada
            if ($leaveRequest->attachment_path) {
                \Storage::disk('public')->delete($leaveRequest->attachment_path);
            }

            // Delete leave request
            $leaveRequest->delete();

            return redirect()
                ->route('parent.leave-requests.index')
                ->with('success', 'Permohonan izin berhasil dibatalkan.');
        } catch (\Exception $e) {
            return back()
                ->with('error', 'Gagal membatalkan permohonan: '.$e->getMessage());
        }
    }
}
