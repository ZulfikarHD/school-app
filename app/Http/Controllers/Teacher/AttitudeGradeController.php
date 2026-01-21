<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreAttitudeGradeRequest;
use App\Models\AttitudeGrade;
use App\Models\SchoolClass;
use App\Models\Student;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class AttitudeGradeController extends Controller
{
    /**
     * Display list nilai sikap yang sudah diinput oleh wali kelas
     * dengan filter berdasarkan semester
     */
    public function index(Request $request): Response
    {
        $teacher = auth()->user();

        // Check if teacher is wali kelas
        $waliKelasClass = $this->getWaliKelasClass($teacher);

        if (! $waliKelasClass) {
            return Inertia::render('Teacher/AttitudeGrades/Index', [
                'title' => 'Nilai Sikap',
                'isWaliKelas' => false,
                'class' => null,
                'attitudeGrades' => [],
                'filters' => [],
                'gradeOptions' => AttitudeGrade::getGradeOptions(),
            ]);
        }

        // Filters
        $semester = $request->get('semester');
        $tahunAjaran = $request->get('tahun_ajaran', $this->getCurrentTahunAjaran());
        $search = $request->get('search');

        // Query attitude grades untuk kelas wali kelas
        $query = AttitudeGrade::where('class_id', $waliKelasClass->id)
            ->where('tahun_ajaran', $tahunAjaran)
            ->with('student');

        if ($semester) {
            $query->where('semester', $semester);
        }

        if ($search) {
            $query->whereHas('student', function ($q) use ($search) {
                $q->where('nama_lengkap', 'like', "%{$search}%")
                    ->orWhere('nis', 'like', "%{$search}%");
            });
        }

        $attitudeGrades = $query->orderBy('created_at', 'desc')
            ->paginate(20)
            ->withQueryString();

        // Get summary
        $summary = $this->getAttitudeGradeSummary($waliKelasClass->id, $tahunAjaran, $semester);

        return Inertia::render('Teacher/AttitudeGrades/Index', [
            'title' => 'Nilai Sikap',
            'isWaliKelas' => true,
            'class' => [
                'id' => $waliKelasClass->id,
                'nama_lengkap' => $waliKelasClass->nama_lengkap,
                'tahun_ajaran' => $waliKelasClass->tahun_ajaran,
            ],
            'attitudeGrades' => $attitudeGrades,
            'filters' => [
                'semester' => $semester,
                'tahun_ajaran' => $tahunAjaran,
                'search' => $search,
            ],
            'summary' => $summary,
            'gradeOptions' => AttitudeGrade::getGradeOptions(),
            'semesters' => [
                ['value' => '1', 'label' => 'Semester 1 (Ganjil)'],
                ['value' => '2', 'label' => 'Semester 2 (Genap)'],
            ],
        ]);
    }

    /**
     * Show form untuk input nilai sikap baru
     * hanya bisa diakses oleh wali kelas
     */
    public function create(Request $request): Response
    {
        $teacher = auth()->user();

        // Check if teacher is wali kelas
        $waliKelasClass = $this->getWaliKelasClass($teacher);

        if (! $waliKelasClass) {
            abort(403, 'Hanya wali kelas yang dapat mengakses halaman ini.');
        }

        $tahunAjaran = $this->getCurrentTahunAjaran();
        $semester = $request->get('semester', $this->getCurrentSemester());

        // Get students in class
        $students = Student::where('kelas_id', $waliKelasClass->id)
            ->where('status', 'aktif')
            ->orderBy('nama_lengkap')
            ->get(['id', 'nis', 'nama_lengkap']);

        // Check existing attitude grades for this semester
        $existingGrades = AttitudeGrade::where('class_id', $waliKelasClass->id)
            ->where('tahun_ajaran', $tahunAjaran)
            ->where('semester', $semester)
            ->get()
            ->keyBy('student_id');

        // Prepare students with existing data if any
        $studentsWithGrades = $students->map(function ($student) use ($existingGrades) {
            $existing = $existingGrades->get($student->id);

            return [
                'id' => $student->id,
                'nis' => $student->nis,
                'nama_lengkap' => $student->nama_lengkap,
                'spiritual_grade' => $existing?->spiritual_grade ?? 'B',
                'spiritual_description' => $existing?->spiritual_description ?? '',
                'social_grade' => $existing?->social_grade ?? 'B',
                'social_description' => $existing?->social_description ?? '',
                'homeroom_notes' => $existing?->homeroom_notes ?? '',
                'has_existing' => $existing !== null,
            ];
        });

        return Inertia::render('Teacher/AttitudeGrades/Create', [
            'title' => 'Input Nilai Sikap',
            'class' => [
                'id' => $waliKelasClass->id,
                'nama_lengkap' => $waliKelasClass->nama_lengkap,
                'tahun_ajaran' => $waliKelasClass->tahun_ajaran,
            ],
            'students' => $studentsWithGrades,
            'tahunAjaran' => $tahunAjaran,
            'semester' => $semester,
            'gradeOptions' => AttitudeGrade::getGradeOptions(),
            'spiritualTemplates' => AttitudeGrade::getSpiritualDescriptionTemplates(),
            'socialTemplates' => AttitudeGrade::getSocialDescriptionTemplates(),
            'semesters' => [
                ['value' => '1', 'label' => 'Semester 1 (Ganjil)'],
                ['value' => '2', 'label' => 'Semester 2 (Genap)'],
            ],
        ]);
    }

    /**
     * Store nilai sikap untuk multiple siswa dalam satu semester
     * menggunakan upsert untuk handle create dan update sekaligus
     */
    public function store(StoreAttitudeGradeRequest $request): RedirectResponse
    {
        $teacher = auth()->user();
        $validated = $request->validated();

        // Verify teacher is wali kelas
        $waliKelasClass = $this->getWaliKelasClass($teacher);
        if (! $waliKelasClass || $waliKelasClass->id !== $validated['class_id']) {
            return back()->with('error', 'Anda tidak memiliki akses untuk input nilai sikap kelas ini.');
        }

        try {
            DB::transaction(function () use ($validated, $teacher) {
                foreach ($validated['grades'] as $gradeData) {
                    AttitudeGrade::updateOrCreate(
                        [
                            'student_id' => $gradeData['student_id'],
                            'tahun_ajaran' => $validated['tahun_ajaran'],
                            'semester' => $validated['semester'],
                        ],
                        [
                            'class_id' => $validated['class_id'],
                            'teacher_id' => $teacher->id,
                            'spiritual_grade' => $gradeData['spiritual_grade'],
                            'spiritual_description' => $gradeData['spiritual_description'] ?? null,
                            'social_grade' => $gradeData['social_grade'],
                            'social_description' => $gradeData['social_description'] ?? null,
                            'homeroom_notes' => $gradeData['homeroom_notes'] ?? null,
                        ]
                    );
                }
            });

            return redirect()
                ->route('teacher.attitude-grades.index')
                ->with('success', 'Berhasil menyimpan nilai sikap untuk '.count($validated['grades']).' siswa.');
        } catch (\Exception $e) {
            return back()
                ->withInput()
                ->with('error', 'Gagal menyimpan nilai sikap: '.$e->getMessage());
        }
    }

    /**
     * Get kelas dimana teacher menjadi wali kelas
     */
    protected function getWaliKelasClass($teacher): ?SchoolClass
    {
        $tahunAjaran = $this->getCurrentTahunAjaran();

        return SchoolClass::where('wali_kelas_id', $teacher->id)
            ->where('tahun_ajaran', $tahunAjaran)
            ->where('is_active', true)
            ->first();
    }

    /**
     * Get summary statistics untuk attitude grades
     */
    protected function getAttitudeGradeSummary(int $classId, string $tahunAjaran, ?string $semester): array
    {
        $query = AttitudeGrade::where('class_id', $classId)
            ->where('tahun_ajaran', $tahunAjaran);

        if ($semester) {
            $query->where('semester', $semester);
        }

        $totalStudents = (clone $query)->count();

        // Distribution per grade
        $spiritualDistribution = [
            'A' => (clone $query)->where('spiritual_grade', 'A')->count(),
            'B' => (clone $query)->where('spiritual_grade', 'B')->count(),
            'C' => (clone $query)->where('spiritual_grade', 'C')->count(),
            'D' => (clone $query)->where('spiritual_grade', 'D')->count(),
        ];

        $socialDistribution = [
            'A' => (clone $query)->where('social_grade', 'A')->count(),
            'B' => (clone $query)->where('social_grade', 'B')->count(),
            'C' => (clone $query)->where('social_grade', 'C')->count(),
            'D' => (clone $query)->where('social_grade', 'D')->count(),
        ];

        return [
            'total_students' => $totalStudents,
            'spiritual_distribution' => $spiritualDistribution,
            'social_distribution' => $socialDistribution,
        ];
    }

    /**
     * Get current tahun ajaran berdasarkan bulan sekarang
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

    /**
     * Get current semester berdasarkan bulan sekarang
     * Semester 1: Juli - Desember
     * Semester 2: Januari - Juni
     */
    protected function getCurrentSemester(): string
    {
        $month = now()->month;

        return $month >= 7 ? '1' : '2';
    }
}
