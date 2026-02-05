<?php

namespace App\Http\Controllers\Admin;

use App\Enums\Hari;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreTeachingScheduleRequest;
use App\Http\Requests\Admin\UpdateTeachingScheduleRequest;
use App\Models\AcademicYear;
use App\Models\ActivityLog;
use App\Models\SchoolClass;
use App\Models\Subject;
use App\Models\Teacher;
use App\Models\TeachingSchedule;
use App\Services\ScheduleConflictService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;

/**
 * TeachingScheduleController - Controller untuk mengelola jadwal mengajar guru
 *
 * Controller ini bertujuan untuk menyediakan CRUD operations untuk jadwal mengajar,
 * matrix views per guru/kelas, conflict detection, dan export PDF
 */
class TeachingScheduleController extends Controller
{
    public function __construct(
        protected ScheduleConflictService $conflictService
    ) {}

    /**
     * Display list of teaching schedules dengan pagination, search, dan filter
     * untuk schedule management interface
     */
    public function index(Request $request)
    {
        $query = TeachingSchedule::query()
            ->with(['teacher', 'subject', 'schoolClass', 'academicYear']);

        // Filter by academic year (default ke active year)
        $academicYearId = $request->input('academic_year_id');
        if ($academicYearId) {
            $query->byAcademicYear($academicYearId);
        } else {
            $activeYear = AcademicYear::active()->first();
            if ($activeYear) {
                $query->byAcademicYear($activeYear->id);
                $academicYearId = $activeYear->id;
            }
        }

        // Filter by teacher
        if ($teacherId = $request->input('teacher_id')) {
            $query->byTeacher($teacherId);
        }

        // Filter by class
        if ($classId = $request->input('class_id')) {
            $query->byClass($classId);
        }

        // Filter by day
        if ($hari = $request->input('hari')) {
            $query->byDay($hari);
        }

        // Search by teacher name, subject, or class
        if ($search = $request->input('search')) {
            $query->search($search);
        }

        // Filter by active status
        if ($request->has('is_active') && $request->input('is_active') !== '') {
            $isActive = filter_var($request->input('is_active'), FILTER_VALIDATE_BOOLEAN);
            $query->where('is_active', $isActive);
        } else {
            $query->active();
        }

        $schedules = $query
            ->orderByRaw("FIELD(hari, 'senin', 'selasa', 'rabu', 'kamis', 'jumat', 'sabtu')")
            ->orderBy('jam_mulai')
            ->paginate(20)
            ->withQueryString();

        // Get filter options
        $teachers = Teacher::active()->orderBy('nama_lengkap')->get(['id', 'nama_lengkap']);
        $classes = SchoolClass::where('is_active', true)->orderBy('tingkat')->orderBy('nama')->get();
        $academicYears = AcademicYear::orderBy('name', 'desc')->get(['id', 'name', 'is_active']);

        return Inertia::render('Admin/Teachers/Schedules/Index', [
            'schedules' => $schedules,
            'filters' => $request->only(['search', 'teacher_id', 'class_id', 'hari', 'academic_year_id', 'is_active']),
            'teachers' => $teachers,
            'classes' => $classes,
            'academicYears' => $academicYears,
            'hariOptions' => Hari::options(),
            'currentAcademicYearId' => $academicYearId,
        ]);
    }

    /**
     * Show form untuk create jadwal baru
     */
    public function create()
    {
        $teachers = Teacher::active()
            ->with(['subjects' => fn ($q) => $q->wherePivot('is_primary', true)])
            ->orderBy('nama_lengkap')
            ->get();

        $subjects = Subject::where('is_active', true)
            ->orderBy('nama_mapel')
            ->get(['id', 'kode_mapel', 'nama_mapel']);

        $classes = SchoolClass::where('is_active', true)
            ->orderBy('tingkat')
            ->orderBy('nama')
            ->get();

        $academicYears = AcademicYear::orderBy('name', 'desc')->get(['id', 'name', 'is_active']);
        $activeYear = AcademicYear::active()->first();

        return Inertia::render('Admin/Teachers/Schedules/Create', [
            'teachers' => $teachers,
            'subjects' => $subjects,
            'classes' => $classes,
            'academicYears' => $academicYears,
            'activeAcademicYearId' => $activeYear?->id,
            'hariOptions' => Hari::options(),
        ]);
    }

    /**
     * Store jadwal baru dengan conflict checking dan activity logging
     */
    public function store(StoreTeachingScheduleRequest $request)
    {
        $data = $request->validated();

        // Check for conflicts
        $conflicts = $this->conflictService->checkConflicts($data);
        if (! empty($conflicts)) {
            return back()->withErrors($conflicts)->withInput();
        }

        DB::beginTransaction();

        try {
            $schedule = TeachingSchedule::create($data);

            // Log activity
            ActivityLog::create([
                'user_id' => auth()->id(),
                'action' => 'create_teaching_schedule',
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'new_values' => [
                    'schedule_id' => $schedule->id,
                    'teacher' => $schedule->teacher->nama_lengkap,
                    'subject' => $schedule->subject->nama_mapel,
                    'class' => $schedule->full_class_name,
                    'hari' => $schedule->hari->label(),
                    'time' => $schedule->time_range,
                ],
                'status' => 'success',
            ]);

            DB::commit();

            return redirect()->route('admin.teachers.schedules.index')
                ->with('success', 'Jadwal mengajar berhasil ditambahkan.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to create teaching schedule', ['error' => $e->getMessage()]);

            return back()->withErrors(['error' => 'Gagal menambahkan jadwal. Silakan coba lagi.'])->withInput();
        }
    }

    /**
     * Show form untuk edit jadwal existing
     */
    public function edit(TeachingSchedule $schedule)
    {
        $schedule->load(['teacher', 'subject', 'schoolClass', 'academicYear']);

        $teachers = Teacher::active()
            ->with(['subjects' => fn ($q) => $q->wherePivot('is_primary', true)])
            ->orderBy('nama_lengkap')
            ->get();

        $subjects = Subject::where('is_active', true)
            ->orderBy('nama_mapel')
            ->get(['id', 'kode_mapel', 'nama_mapel']);

        $classes = SchoolClass::where('is_active', true)
            ->orderBy('tingkat')
            ->orderBy('nama')
            ->get();

        $academicYears = AcademicYear::orderBy('name', 'desc')->get(['id', 'name', 'is_active']);

        return Inertia::render('Admin/Teachers/Schedules/Edit', [
            'schedule' => $schedule,
            'teachers' => $teachers,
            'subjects' => $subjects,
            'classes' => $classes,
            'academicYears' => $academicYears,
            'hariOptions' => Hari::options(),
        ]);
    }

    /**
     * Update jadwal existing dengan conflict checking
     */
    public function update(UpdateTeachingScheduleRequest $request, TeachingSchedule $schedule)
    {
        $data = $request->validated();

        // Check for conflicts (excluding current schedule)
        $conflicts = $this->conflictService->checkConflicts($data, $schedule->id);
        if (! empty($conflicts)) {
            return back()->withErrors($conflicts)->withInput();
        }

        DB::beginTransaction();

        try {
            $oldValues = [
                'teacher' => $schedule->teacher->nama_lengkap,
                'subject' => $schedule->subject->nama_mapel,
                'hari' => $schedule->hari->label(),
                'time' => $schedule->time_range,
            ];

            $schedule->update($data);
            $schedule->refresh();

            // Log activity
            ActivityLog::create([
                'user_id' => auth()->id(),
                'action' => 'update_teaching_schedule',
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'old_values' => $oldValues,
                'new_values' => [
                    'schedule_id' => $schedule->id,
                    'teacher' => $schedule->teacher->nama_lengkap,
                    'subject' => $schedule->subject->nama_mapel,
                    'hari' => $schedule->hari->label(),
                    'time' => $schedule->time_range,
                ],
                'status' => 'success',
            ]);

            DB::commit();

            return redirect()->route('admin.teachers.schedules.index')
                ->with('success', 'Jadwal mengajar berhasil diupdate.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to update teaching schedule', ['error' => $e->getMessage()]);

            return back()->withErrors(['error' => 'Gagal mengupdate jadwal. Silakan coba lagi.'])->withInput();
        }
    }

    /**
     * Soft delete jadwal mengajar
     */
    public function destroy(TeachingSchedule $schedule)
    {
        DB::beginTransaction();

        try {
            $scheduleInfo = [
                'schedule_id' => $schedule->id,
                'teacher' => $schedule->teacher->nama_lengkap,
                'subject' => $schedule->subject->nama_mapel,
                'class' => $schedule->full_class_name,
                'hari' => $schedule->hari->label(),
                'time' => $schedule->time_range,
            ];

            $schedule->delete();

            // Log activity
            ActivityLog::create([
                'user_id' => auth()->id(),
                'action' => 'delete_teaching_schedule',
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
                'old_values' => $scheduleInfo,
                'status' => 'success',
            ]);

            DB::commit();

            return back()->with('success', 'Jadwal mengajar berhasil dihapus.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to delete teaching schedule', ['error' => $e->getMessage()]);

            return back()->withErrors(['error' => 'Gagal menghapus jadwal.']);
        }
    }

    /**
     * Display matrix view jadwal per guru
     */
    public function byTeacher(Request $request, Teacher $teacher)
    {
        $academicYearId = $request->input('academic_year_id');
        $academicYear = $academicYearId
            ? AcademicYear::find($academicYearId)
            : AcademicYear::active()->first();

        $schedules = [];
        if ($academicYear) {
            $schedules = TeachingSchedule::query()
                ->with(['subject', 'schoolClass'])
                ->byTeacher($teacher->id)
                ->byAcademicYear($academicYear->id)
                ->active()
                ->orderByRaw("FIELD(hari, 'senin', 'selasa', 'rabu', 'kamis', 'jumat', 'sabtu')")
                ->orderBy('jam_mulai')
                ->get();
        }

        $academicYears = AcademicYear::orderBy('name', 'desc')->get(['id', 'name', 'is_active']);

        return Inertia::render('Admin/Teachers/Schedules/ByTeacher', [
            'teacher' => $teacher,
            'schedules' => $schedules,
            'academicYear' => $academicYear,
            'academicYears' => $academicYears,
            'hariOptions' => Hari::options(),
        ]);
    }

    /**
     * Display matrix view jadwal per kelas
     */
    public function byClass(Request $request, SchoolClass $schoolClass)
    {
        $academicYearId = $request->input('academic_year_id');
        $academicYear = $academicYearId
            ? AcademicYear::find($academicYearId)
            : AcademicYear::active()->first();

        $schedules = [];
        if ($academicYear) {
            $schedules = TeachingSchedule::query()
                ->with(['teacher', 'subject'])
                ->byClass($schoolClass->id)
                ->byAcademicYear($academicYear->id)
                ->active()
                ->orderByRaw("FIELD(hari, 'senin', 'selasa', 'rabu', 'kamis', 'jumat', 'sabtu')")
                ->orderBy('jam_mulai')
                ->get();
        }

        $academicYears = AcademicYear::orderBy('name', 'desc')->get(['id', 'name', 'is_active']);

        return Inertia::render('Admin/Teachers/Schedules/ByClass', [
            'schoolClass' => $schoolClass,
            'schedules' => $schedules,
            'academicYear' => $academicYear,
            'academicYears' => $academicYears,
            'hariOptions' => Hari::options(),
        ]);
    }

    /**
     * API endpoint untuk real-time conflict checking
     */
    public function checkConflict(Request $request)
    {
        $request->validate([
            'teacher_id' => ['required', 'exists:teachers,id'],
            'class_id' => ['required', 'exists:classes,id'],
            'academic_year_id' => ['required', 'exists:academic_years,id'],
            'hari' => ['required', 'in:'.implode(',', Hari::values())],
            'jam_mulai' => ['required', 'date_format:H:i'],
            'jam_selesai' => ['required', 'date_format:H:i', 'after:jam_mulai'],
            'ruangan' => ['nullable', 'string', 'max:50'],
            'exclude_id' => ['nullable', 'exists:teaching_schedules,id'],
        ]);

        $data = $request->only(['teacher_id', 'class_id', 'academic_year_id', 'hari', 'jam_mulai', 'jam_selesai', 'ruangan']);
        $excludeId = $request->input('exclude_id');

        $conflicts = $this->conflictService->getConflictDetails($data, $excludeId);
        $timeErrors = $this->conflictService->validateTimeRange($data['jam_mulai'], $data['jam_selesai']);

        return response()->json([
            'has_conflicts' => ! empty($conflicts) || ! empty($timeErrors),
            'conflicts' => $conflicts,
            'time_errors' => $timeErrors,
        ]);
    }

    /**
     * Copy jadwal dari semester/tahun ajaran sebelumnya
     */
    public function copySemester(Request $request)
    {
        $request->validate([
            'from_academic_year_id' => ['required', 'exists:academic_years,id'],
            'to_academic_year_id' => ['required', 'exists:academic_years,id', 'different:from_academic_year_id'],
        ]);

        try {
            $copiedCount = $this->conflictService->copyFromPreviousSemester(
                $request->input('from_academic_year_id'),
                $request->input('to_academic_year_id')
            );

            $fromYear = AcademicYear::find($request->input('from_academic_year_id'));
            $toYear = AcademicYear::find($request->input('to_academic_year_id'));

            // Log activity
            ActivityLog::create([
                'user_id' => auth()->id(),
                'action' => 'copy_teaching_schedules',
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'new_values' => [
                    'from_year' => $fromYear->name,
                    'to_year' => $toYear->name,
                    'copied_count' => $copiedCount,
                ],
                'status' => 'success',
            ]);

            if ($copiedCount > 0) {
                return back()->with('success', "Berhasil menyalin {$copiedCount} jadwal dari {$fromYear->name} ke {$toYear->name}.");
            }

            return back()->with('info', 'Tidak ada jadwal yang dapat disalin (mungkin sudah ada atau terjadi konflik).');
        } catch (\Exception $e) {
            Log::error('Failed to copy semester schedules', ['error' => $e->getMessage()]);

            return back()->withErrors(['error' => 'Gagal menyalin jadwal. Silakan coba lagi.']);
        }
    }

    /**
     * Export jadwal ke PDF (per guru atau per kelas)
     */
    public function exportPdf(Request $request)
    {
        $request->validate([
            'type' => ['required', 'in:teacher,class'],
            'id' => ['required', 'integer'],
            'academic_year_id' => ['nullable', 'exists:academic_years,id'],
        ]);

        $type = $request->input('type');
        $id = $request->input('id');
        $academicYearId = $request->input('academic_year_id');

        $academicYear = $academicYearId
            ? AcademicYear::find($academicYearId)
            : AcademicYear::active()->first();

        if (! $academicYear) {
            return back()->withErrors(['error' => 'Tahun ajaran tidak ditemukan.']);
        }

        $query = TeachingSchedule::query()
            ->with(['teacher', 'subject', 'schoolClass'])
            ->byAcademicYear($academicYear->id)
            ->active()
            ->orderByRaw("FIELD(hari, 'senin', 'selasa', 'rabu', 'kamis', 'jumat', 'sabtu')")
            ->orderBy('jam_mulai');

        if ($type === 'teacher') {
            $teacher = Teacher::findOrFail($id);
            $query->byTeacher($id);
            $title = "Jadwal Mengajar - {$teacher->nama_lengkap}";
            $subtitle = "Tahun Ajaran {$academicYear->name}";
            $filename = "jadwal-guru-{$teacher->id}-{$academicYear->name}.pdf";
        } else {
            $schoolClass = SchoolClass::findOrFail($id);
            $query->byClass($id);
            $title = "Jadwal Pelajaran - Kelas {$schoolClass->tingkat}{$schoolClass->nama}";
            $subtitle = "Tahun Ajaran {$academicYear->name}";
            $filename = "jadwal-kelas-{$schoolClass->id}-{$academicYear->name}.pdf";
        }

        $schedules = $query->get();

        // Group schedules by day
        $schedulesByDay = $schedules->groupBy(fn ($s) => $s->hari->value);

        $pdf = Pdf::loadView('pdf.teaching-schedule', [
            'title' => $title,
            'subtitle' => $subtitle,
            'schedules' => $schedules,
            'schedulesByDay' => $schedulesByDay,
            'hariOptions' => Hari::cases(),
            'type' => $type,
            'entity' => $type === 'teacher' ? $teacher : $schoolClass,
        ]);

        $pdf->setPaper('a4', 'landscape');

        return $pdf->download($filename);
    }
}
