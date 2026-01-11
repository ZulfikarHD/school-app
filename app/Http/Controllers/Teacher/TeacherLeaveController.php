<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\TeacherLeave;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class TeacherLeaveController extends Controller
{
    /**
     * Display list of teacher's own leave requests
     */
    public function index(): Response
    {
        $teacher = auth()->user();

        $leaves = TeacherLeave::where('teacher_id', $teacher->id)
            ->with('approvedBy')
            ->latest('created_at')
            ->paginate(20);

        return Inertia::render('Teacher/TeacherLeave/Index', [
            'title' => 'Riwayat Izin Saya',
            'leaves' => $leaves,
        ]);
    }

    /**
     * Show form untuk submit leave request baru
     */
    public function create(): Response
    {
        return Inertia::render('Teacher/TeacherLeave/Create', [
            'title' => 'Ajukan Izin/Cuti',
        ]);
    }

    /**
     * Store leave request dengan attachment upload
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'type' => 'required|in:IZIN,SAKIT,CUTI',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'reason' => 'required|string|min:10|max:500',
            'attachment' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:5120',
        ]);

        try {
            // Map English field names ke Indonesian database columns
            $data = [
                'teacher_id' => auth()->id(),
                'jenis' => $validated['type'],
                'tanggal_mulai' => $validated['start_date'],
                'tanggal_selesai' => $validated['end_date'],
                'alasan' => $validated['reason'],
                'status' => 'PENDING',
            ];

            // Calculate jumlah hari
            $startDate = \Carbon\Carbon::parse($validated['start_date']);
            $endDate = \Carbon\Carbon::parse($validated['end_date']);
            $data['jumlah_hari'] = $startDate->diffInDays($endDate) + 1;

            // Handle file upload
            if ($request->hasFile('attachment')) {
                $path = $request->file('attachment')->store('teacher-leave-attachments', 'public');
                $data['attachment_path'] = $path;
            }

            TeacherLeave::create($data);

            return redirect()
                ->route('teacher.teacher-leaves.index')
                ->with('success', 'Permohonan izin berhasil diajukan dan menunggu persetujuan.');
        } catch (\Exception $e) {
            return back()
                ->withInput()
                ->with('error', 'Gagal mengajukan permohonan: '.$e->getMessage());
        }
    }
}
