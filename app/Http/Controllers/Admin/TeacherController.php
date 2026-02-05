<?php

namespace App\Http\Controllers\Admin;

use App\Enums\StatusKepegawaian;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreTeacherRequest;
use App\Http\Requests\Admin\UpdateTeacherRequest;
use App\Models\ActivityLog;
use App\Models\Subject;
use App\Models\Teacher;
use App\Services\TeacherService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;

class TeacherController extends Controller
{
    public function __construct(
        protected TeacherService $teacherService
    ) {}

    /**
     * Display list of teachers dengan pagination, search, dan filter capabilities
     * untuk teacher management interface
     */
    public function index(Request $request)
    {
        $query = Teacher::query()->with(['user', 'subjects']);

        // Search by nama, NIP, atau NIK
        if ($search = $request->input('search')) {
            $query->search($search);
        }

        // Filter by status kepegawaian
        if ($status = $request->input('status_kepegawaian')) {
            $query->byStatus($status);
        }

        // Filter by is_active
        if ($request->has('is_active') && $request->input('is_active') !== '') {
            $isActive = filter_var($request->input('is_active'), FILTER_VALIDATE_BOOLEAN);
            $query->where('is_active', $isActive);
        }

        $teachers = $query->orderBy('nama_lengkap')->paginate(15)->withQueryString();

        // Get subjects for filter dropdown
        $subjects = Subject::active()
            ->orderBy('nama_mapel')
            ->get(['id', 'kode_mapel', 'nama_mapel']);

        return Inertia::render('Admin/Teachers/Index', [
            'teachers' => $teachers,
            'filters' => $request->only(['search', 'status_kepegawaian', 'is_active']),
            'subjects' => $subjects,
            'statusOptions' => StatusKepegawaian::options(),
        ]);
    }

    /**
     * Show form untuk create teacher baru dengan subjects list
     */
    public function create()
    {
        $subjects = Subject::active()
            ->orderBy('nama_mapel')
            ->get(['id', 'kode_mapel', 'nama_mapel']);

        // Generate current academic year
        $year = now()->month >= 7 ? now()->year : now()->year - 1;
        $currentAcademicYear = $year.'/'.($year + 1);

        return Inertia::render('Admin/Teachers/Create', [
            'subjects' => $subjects,
            'statusOptions' => StatusKepegawaian::options(),
            'currentAcademicYear' => $currentAcademicYear,
        ]);
    }

    /**
     * Store teacher baru dengan auto-create user account
     * serta activity logging untuk audit trail
     */
    public function store(StoreTeacherRequest $request)
    {
        try {
            $teacher = $this->teacherService->createTeacher($request->validated());

            // Log activity
            ActivityLog::create([
                'user_id' => auth()->id(),
                'action' => 'create_teacher',
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'new_values' => [
                    'teacher_id' => $teacher->id,
                    'nip' => $teacher->nip,
                    'nama_lengkap' => $teacher->nama_lengkap,
                ],
                'status' => 'success',
            ]);

            $message = "Guru {$teacher->nama_lengkap} berhasil ditambahkan.";
            if (isset($teacher->generated_password)) {
                $message .= " Password: {$teacher->generated_password}";
            }

            return redirect()->route('admin.teachers.index')
                ->with('success', $message);
        } catch (\Exception $e) {
            Log::error('Failed to create teacher', ['error' => $e->getMessage()]);

            return back()->withErrors(['error' => 'Gagal menambahkan guru. Silakan coba lagi.'])->withInput();
        }
    }

    /**
     * Show form untuk edit teacher existing dengan current data
     */
    public function edit(Teacher $teacher)
    {
        $teacher->load(['user', 'subjects']);

        $subjects = Subject::active()
            ->orderBy('nama_mapel')
            ->get(['id', 'kode_mapel', 'nama_mapel']);

        // Generate current academic year
        $year = now()->month >= 7 ? now()->year : now()->year - 1;
        $currentAcademicYear = $year.'/'.($year + 1);

        return Inertia::render('Admin/Teachers/Edit', [
            'teacher' => $teacher,
            'subjects' => $subjects,
            'statusOptions' => StatusKepegawaian::options(),
            'currentAcademicYear' => $currentAcademicYear,
        ]);
    }

    /**
     * Update teacher data dengan validation dan activity logging
     */
    public function update(UpdateTeacherRequest $request, Teacher $teacher)
    {
        DB::beginTransaction();

        try {
            $oldValues = [
                'nama_lengkap' => $teacher->nama_lengkap,
                'nip' => $teacher->nip,
                'nik' => $teacher->nik,
                'status_kepegawaian' => $teacher->status_kepegawaian,
            ];

            $teacher = $this->teacherService->updateTeacher($teacher, $request->validated());

            // Log activity
            ActivityLog::create([
                'user_id' => auth()->id(),
                'action' => 'update_teacher',
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'old_values' => $oldValues,
                'new_values' => [
                    'teacher_id' => $teacher->id,
                    'nama_lengkap' => $teacher->nama_lengkap,
                    'nip' => $teacher->nip,
                    'nik' => $teacher->nik,
                ],
                'status' => 'success',
            ]);

            DB::commit();

            return redirect()->route('admin.teachers.index')
                ->with('success', 'Data guru berhasil diupdate.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to update teacher', ['error' => $e->getMessage()]);

            return back()->withErrors(['error' => 'Gagal mengupdate guru. Silakan coba lagi.'])->withInput();
        }
    }

    /**
     * Toggle status aktif/nonaktif teacher
     */
    public function toggleStatus(Teacher $teacher)
    {
        try {
            $oldStatus = $teacher->is_active;
            $teacher = $this->teacherService->toggleStatus($teacher);

            // Log activity
            ActivityLog::create([
                'user_id' => auth()->id(),
                'action' => 'toggle_teacher_status',
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
                'old_values' => ['is_active' => $oldStatus],
                'new_values' => [
                    'teacher_id' => $teacher->id,
                    'nama_lengkap' => $teacher->nama_lengkap,
                    'is_active' => $teacher->is_active,
                ],
                'status' => 'success',
            ]);

            $message = $teacher->is_active
                ? "Guru {$teacher->nama_lengkap} berhasil diaktifkan."
                : "Guru {$teacher->nama_lengkap} berhasil dinonaktifkan.";

            return back()->with('success', $message);
        } catch (\Exception $e) {
            Log::error('Failed to toggle teacher status', ['error' => $e->getMessage()]);

            return back()->withErrors(['error' => 'Gagal mengubah status guru.']);
        }
    }
}
