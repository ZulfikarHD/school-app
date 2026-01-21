<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreGradeRequest;
use App\Http\Requests\UpdateGradeRequest;
use App\Models\Grade;
use App\Models\SchoolClass;
use App\Models\Student;
use App\Models\Subject;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class GradeController extends Controller
{
    /**
     * Display list penilaian yang sudah diinput oleh guru
     * dengan filter berdasarkan semester dan mata pelajaran
     */
    public function index(Request $request): Response
    {
        $teacher = auth()->user();

        // Filters dari request
        $semester = $request->get('semester');
        $subjectId = $request->get('subject_id');
        $classId = $request->get('class_id');
        $tahunAjaran = $request->get('tahun_ajaran', $this->getCurrentTahunAjaran());
        $search = $request->get('search');

        // Get guru's classes dan subjects untuk filter dropdown
        $classes = $this->getTeacherClasses($teacher);
        $subjects = $this->getTeacherSubjects($teacher, $tahunAjaran);

        // Query grades grouped by assessment (kelas, mapel, jenis, judul, tanggal)
        $query = Grade::query()
            ->select([
                DB::raw('MIN(grades.id) as id'),
                'grades.class_id',
                'grades.subject_id',
                'grades.assessment_type',
                'grades.assessment_number',
                'grades.title',
                'grades.assessment_date',
                'grades.tahun_ajaran',
                'grades.semester',
                DB::raw('COUNT(DISTINCT grades.student_id) as student_count'),
                DB::raw('AVG(grades.score) as average_score'),
                DB::raw('MIN(grades.score) as min_score'),
                DB::raw('MAX(grades.score) as max_score'),
                DB::raw('MAX(grades.is_locked) as is_locked'),
            ])
            ->where('grades.teacher_id', $teacher->id)
            ->where('grades.tahun_ajaran', $tahunAjaran)
            ->groupBy([
                'grades.class_id',
                'grades.subject_id',
                'grades.assessment_type',
                'grades.assessment_number',
                'grades.title',
                'grades.assessment_date',
                'grades.tahun_ajaran',
                'grades.semester',
            ])
            ->with(['class', 'subject']);

        // Apply filters
        if ($semester) {
            $query->where('grades.semester', $semester);
        }
        if ($subjectId) {
            $query->where('grades.subject_id', $subjectId);
        }
        if ($classId) {
            $query->where('grades.class_id', $classId);
        }

        // Search by title
        if ($search) {
            $query->where('grades.title', 'like', "%{$search}%");
        }

        $grades = $query->orderBy('grades.assessment_date', 'desc')
            ->paginate(15)
            ->withQueryString();

        // Get summary statistics
        $summary = $this->getGradeSummary($teacher->id, $tahunAjaran, $semester);

        return Inertia::render('Teacher/Grades/Index', [
            'title' => 'Daftar Penilaian',
            'grades' => $grades,
            'classes' => $classes,
            'subjects' => $subjects,
            'filters' => [
                'semester' => $semester,
                'subject_id' => $subjectId ? (int) $subjectId : null,
                'class_id' => $classId ? (int) $classId : null,
                'tahun_ajaran' => $tahunAjaran,
                'search' => $search,
            ],
            'summary' => $summary,
            'assessmentTypes' => Grade::getAssessmentTypes(),
        ]);
    }

    /**
     * Show form untuk input nilai baru dengan wizard-style UI
     * Step 1: Pilih kelas, mapel, semester, jenis, judul, tanggal
     * Step 2: Input nilai per siswa
     */
    public function create(Request $request): Response
    {
        $teacher = auth()->user();
        $tahunAjaran = $this->getCurrentTahunAjaran();

        // Get guru's classes dan subjects
        $classes = $this->getTeacherClasses($teacher);
        $subjects = $this->getTeacherSubjects($teacher, $tahunAjaran);

        return Inertia::render('Teacher/Grades/Create', [
            'title' => 'Input Nilai Baru',
            'classes' => $classes,
            'subjects' => $subjects,
            'assessmentTypes' => Grade::getAssessmentTypes(),
            'tahunAjaran' => $tahunAjaran,
            'semesters' => [
                ['value' => '1', 'label' => 'Semester 1 (Ganjil)'],
                ['value' => '2', 'label' => 'Semester 2 (Genap)'],
            ],
        ]);
    }

    /**
     * Store nilai baru untuk multiple siswa dalam satu assessment
     * dengan validasi score 0-100 dan required fields
     */
    public function store(StoreGradeRequest $request): RedirectResponse
    {
        $teacher = auth()->user();
        $validated = $request->validated();

        try {
            DB::transaction(function () use ($validated, $teacher) {
                foreach ($validated['grades'] as $gradeData) {
                    Grade::create([
                        'student_id' => $gradeData['student_id'],
                        'subject_id' => $validated['subject_id'],
                        'class_id' => $validated['class_id'],
                        'teacher_id' => $teacher->id,
                        'tahun_ajaran' => $validated['tahun_ajaran'],
                        'semester' => $validated['semester'],
                        'assessment_type' => $validated['assessment_type'],
                        'assessment_number' => $validated['assessment_number'] ?? null,
                        'title' => $validated['title'],
                        'assessment_date' => $validated['assessment_date'],
                        'score' => $gradeData['score'],
                        'notes' => $gradeData['notes'] ?? null,
                        'is_locked' => false,
                    ]);
                }
            });

            return redirect()
                ->route('teacher.grades.index')
                ->with('success', 'Berhasil menyimpan nilai untuk '.count($validated['grades']).' siswa.');
        } catch (\Exception $e) {
            return back()
                ->withInput()
                ->with('error', 'Gagal menyimpan nilai: '.$e->getMessage());
        }
    }

    /**
     * Show form untuk edit penilaian yang sudah ada
     * dengan data siswa dan nilai yang sudah diinput sebelumnya
     */
    public function edit(Request $request, Grade $grade): Response|RedirectResponse
    {
        $teacher = auth()->user();

        // Check authorization: hanya teacher yang menginput bisa edit
        if ($grade->teacher_id !== $teacher->id) {
            abort(403, 'Anda tidak memiliki akses untuk mengedit penilaian ini.');
        }

        // Check if locked
        if ($grade->is_locked) {
            return redirect()
                ->route('teacher.grades.index')
                ->with('error', 'Nilai sudah dikunci dan tidak dapat diedit.');
        }

        // Get all grades for this assessment (same class, subject, type, title, date)
        $grades = Grade::where('class_id', $grade->class_id)
            ->where('subject_id', $grade->subject_id)
            ->where('teacher_id', $teacher->id)
            ->where('assessment_type', $grade->assessment_type)
            ->where('assessment_number', $grade->assessment_number)
            ->where('title', $grade->title)
            ->where('assessment_date', $grade->assessment_date)
            ->with('student')
            ->get();

        $class = SchoolClass::with('students')->find($grade->class_id);
        $subject = Subject::find($grade->subject_id);

        return Inertia::render('Teacher/Grades/Edit', [
            'title' => 'Edit Penilaian',
            'assessment' => [
                'id' => $grade->id,
                'class_id' => $grade->class_id,
                'subject_id' => $grade->subject_id,
                'tahun_ajaran' => $grade->tahun_ajaran,
                'semester' => $grade->semester,
                'assessment_type' => $grade->assessment_type,
                'assessment_number' => $grade->assessment_number,
                'title' => $grade->title,
                'assessment_date' => $grade->assessment_date->format('Y-m-d'),
                'is_locked' => $grade->is_locked,
            ],
            'grades' => $grades->map(fn ($g) => [
                'id' => $g->id,
                'student_id' => $g->student_id,
                'student_nama' => $g->student->nama_lengkap,
                'student_nis' => $g->student->nis,
                'score' => $g->score,
                'notes' => $g->notes,
            ]),
            'class' => [
                'id' => $class->id,
                'nama_lengkap' => $class->nama_lengkap,
            ],
            'subject' => [
                'id' => $subject->id,
                'nama_mapel' => $subject->nama_mapel,
            ],
            'assessmentTypes' => Grade::getAssessmentTypes(),
        ]);
    }

    /**
     * Update nilai yang sudah ada dengan validasi dan authorization check
     */
    public function update(UpdateGradeRequest $request, Grade $grade): RedirectResponse
    {
        $teacher = auth()->user();

        // Check authorization
        if ($grade->teacher_id !== $teacher->id) {
            abort(403, 'Anda tidak memiliki akses untuk mengedit penilaian ini.');
        }

        // Check if locked
        if ($grade->is_locked) {
            return back()->with('error', 'Nilai sudah dikunci dan tidak dapat diedit.');
        }

        $validated = $request->validated();

        try {
            DB::transaction(function () use ($validated, $grade, $teacher) {
                // Update assessment info on all related grades
                Grade::where('class_id', $grade->class_id)
                    ->where('subject_id', $grade->subject_id)
                    ->where('teacher_id', $teacher->id)
                    ->where('assessment_type', $grade->assessment_type)
                    ->where('assessment_number', $grade->assessment_number)
                    ->where('title', $grade->title)
                    ->where('assessment_date', $grade->assessment_date)
                    ->update([
                        'title' => $validated['title'],
                        'assessment_date' => $validated['assessment_date'],
                    ]);

                // Update individual grades
                foreach ($validated['grades'] as $gradeData) {
                    Grade::where('id', $gradeData['id'])
                        ->where('teacher_id', $teacher->id)
                        ->update([
                            'score' => $gradeData['score'],
                            'notes' => $gradeData['notes'] ?? null,
                        ]);
                }
            });

            return redirect()
                ->route('teacher.grades.index')
                ->with('success', 'Berhasil mengupdate nilai.');
        } catch (\Exception $e) {
            return back()
                ->withInput()
                ->with('error', 'Gagal mengupdate nilai: '.$e->getMessage());
        }
    }

    /**
     * Soft delete assessment dan semua nilai terkait
     * dengan authorization check
     */
    public function destroy(Grade $grade): RedirectResponse
    {
        $teacher = auth()->user();

        // Check authorization
        if ($grade->teacher_id !== $teacher->id) {
            abort(403, 'Anda tidak memiliki akses untuk menghapus penilaian ini.');
        }

        // Check if locked
        if ($grade->is_locked) {
            return back()->with('error', 'Nilai sudah dikunci dan tidak dapat dihapus.');
        }

        try {
            DB::transaction(function () use ($grade, $teacher) {
                // Delete all grades for this assessment
                Grade::where('class_id', $grade->class_id)
                    ->where('subject_id', $grade->subject_id)
                    ->where('teacher_id', $teacher->id)
                    ->where('assessment_type', $grade->assessment_type)
                    ->where('assessment_number', $grade->assessment_number)
                    ->where('title', $grade->title)
                    ->where('assessment_date', $grade->assessment_date)
                    ->delete();
            });

            return redirect()
                ->route('teacher.grades.index')
                ->with('success', 'Berhasil menghapus penilaian.');
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal menghapus penilaian: '.$e->getMessage());
        }
    }

    /**
     * API endpoint untuk get students by class
     * digunakan di create/edit form untuk load daftar siswa
     */
    public function getStudentsByClass(Request $request, SchoolClass $class)
    {
        $students = Student::where('kelas_id', $class->id)
            ->where('status', 'aktif')
            ->orderBy('nama_lengkap')
            ->get(['id', 'nis', 'nama_lengkap']);

        return response()->json(['data' => $students]);
    }

    /**
     * API endpoint untuk get subjects by class untuk teacher tertentu
     */
    public function getSubjectsByClass(Request $request, SchoolClass $class)
    {
        $teacher = auth()->user();
        $tahunAjaran = $request->get('tahun_ajaran', $this->getCurrentTahunAjaran());

        $subjects = Subject::whereHas('teachers', function ($query) use ($teacher, $class, $tahunAjaran) {
            $query->where('teacher_id', $teacher->id)
                ->where('class_id', $class->id)
                ->where('tahun_ajaran', $tahunAjaran);
        })
            ->active()
            ->get(['id', 'kode_mapel', 'nama_mapel']);

        return response()->json(['data' => $subjects]);
    }

    /**
     * Get classes yang diajar oleh teacher atau sebagai wali kelas
     *
     * @return array<int, array{id: int, tingkat: int, nama: string, nama_lengkap: string, tahun_ajaran: string, jumlah_siswa: int, is_wali_kelas: bool}>
     */
    protected function getTeacherClasses($teacher): array
    {
        $tahunAjaran = $this->getCurrentTahunAjaran();

        // Classes where teacher is wali kelas
        $waliKelasIds = SchoolClass::where('wali_kelas_id', $teacher->id)
            ->where('tahun_ajaran', $tahunAjaran)
            ->where('is_active', true)
            ->pluck('id')
            ->toArray();

        // Classes where teacher teaches
        $teachingClassIds = DB::table('teacher_subjects')
            ->where('teacher_id', $teacher->id)
            ->where('tahun_ajaran', $tahunAjaran)
            ->pluck('class_id')
            ->unique()
            ->toArray();

        $allClassIds = array_unique(array_merge($waliKelasIds, $teachingClassIds));

        return SchoolClass::whereIn('id', $allClassIds)
            ->where('is_active', true)
            ->withCount(['students' => function ($query) {
                $query->where('status', 'aktif');
            }])
            ->orderBy('tingkat')
            ->orderBy('nama')
            ->get()
            ->map(fn ($class) => [
                'id' => $class->id,
                'tingkat' => $class->tingkat,
                'nama' => $class->nama,
                'nama_lengkap' => $class->nama_lengkap,
                'tahun_ajaran' => $class->tahun_ajaran,
                'jumlah_siswa' => $class->students_count,
                'is_wali_kelas' => in_array($class->id, $waliKelasIds),
            ])
            ->toArray();
    }

    /**
     * Get subjects yang diajar oleh teacher
     *
     * @return array<int, array{id: int, kode_mapel: string, nama_mapel: string}>
     */
    protected function getTeacherSubjects($teacher, string $tahunAjaran): array
    {
        return Subject::whereHas('teachers', function ($query) use ($teacher, $tahunAjaran) {
            $query->where('teacher_id', $teacher->id)
                ->where('tahun_ajaran', $tahunAjaran);
        })
            ->active()
            ->orderBy('nama_mapel')
            ->get()
            ->map(fn ($subject) => [
                'id' => $subject->id,
                'kode_mapel' => $subject->kode_mapel,
                'nama_mapel' => $subject->nama_mapel,
            ])
            ->toArray();
    }

    /**
     * Get summary statistics untuk teacher's grades
     */
    protected function getGradeSummary(int $teacherId, string $tahunAjaran, ?string $semester): array
    {
        $query = Grade::where('teacher_id', $teacherId)
            ->where('tahun_ajaran', $tahunAjaran);

        if ($semester) {
            $query->where('semester', $semester);
        }

        $totalAssessments = (clone $query)
            ->select(DB::raw('COUNT(DISTINCT CONCAT(class_id, subject_id, assessment_type, title, assessment_date)) as total'))
            ->first()
            ->total ?? 0;

        $totalStudents = (clone $query)
            ->distinct('student_id')
            ->count('student_id');

        $averageScore = (clone $query)->avg('score');

        return [
            'total_assessments' => $totalAssessments,
            'total_students' => $totalStudents,
            'average_score' => $averageScore ? round($averageScore, 1) : null,
        ];
    }

    /**
     * Get current tahun ajaran berdasarkan bulan sekarang
     * Juli-Desember = tahun sekarang/tahun depan
     * Januari-Juni = tahun lalu/tahun sekarang
     */
    protected function getCurrentTahunAjaran(): string
    {
        $now = now();
        $year = $now->year;
        $month = $now->month;

        if ($month >= 7) {
            return $year.'/'.($year + 1);
        }

        return ($year - 1).'/'.$year;
    }
}
