<?php

namespace Tests\Feature\Teacher;

use App\Models\Grade;
use App\Models\SchoolClass;
use App\Models\Student;
use App\Models\Subject;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * Feature tests untuk Teacher GradeController
 * Testing CRUD operations, authorization, validation, dan locking mechanism
 */
class GradeControllerTest extends TestCase
{
    use RefreshDatabase;

    protected User $teacher;

    protected User $otherTeacher;

    protected User $admin;

    protected SchoolClass $class;

    protected Student $student;

    protected Subject $subject;

    protected string $tahunAjaran;

    protected string $semester;

    protected function setUp(): void
    {
        parent::setUp();

        // Create teacher user
        $this->teacher = User::factory()->create([
            'role' => 'TEACHER',
        ]);

        // Create another teacher for authorization tests
        $this->otherTeacher = User::factory()->create([
            'role' => 'TEACHER',
        ]);

        // Create admin user
        $this->admin = User::factory()->create([
            'role' => 'ADMIN',
        ]);

        // Set tahun ajaran dan semester dinamis
        $this->tahunAjaran = now()->month >= 7
            ? now()->year.'/'.(now()->year + 1)
            : (now()->year - 1).'/'.now()->year;
        $this->semester = now()->month >= 7 ? '1' : '2';

        // Create class with wali kelas
        $this->class = SchoolClass::factory()->create([
            'wali_kelas_id' => $this->teacher->id,
            'tahun_ajaran' => $this->tahunAjaran,
            'is_active' => true,
        ]);

        // Create student di kelas
        $this->student = Student::factory()->create([
            'kelas_id' => $this->class->id,
            'status' => 'aktif',
        ]);

        // Create subject
        $this->subject = Subject::factory()->create([
            'is_active' => true,
        ]);

        // Assign teacher to subject and class via pivot table
        DB::table('teacher_subjects')->insert([
            'teacher_id' => $this->teacher->id,
            'subject_id' => $this->subject->id,
            'class_id' => $this->class->id,
            'tahun_ajaran' => $this->tahunAjaran,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    // ============================================================
    // INDEX TESTS
    // ============================================================

    /**
     * Test teacher dapat mengakses halaman index nilai
     */
    public function test_teacher_can_access_grades_index(): void
    {
        $response = $this->actingAs($this->teacher)->get('/teacher/grades');

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page
            ->component('Teacher/Grades/Index')
            ->has('grades')
            ->has('classes')
            ->has('subjects')
            ->has('filters')
            ->has('summary')
        );
    }

    /**
     * Test teacher melihat list penilaian yang sudah diinput
     */
    public function test_teacher_can_see_own_grades_in_index(): void
    {
        // Create grade milik teacher
        Grade::factory()->ulanganHarian()->create([
            'student_id' => $this->student->id,
            'subject_id' => $this->subject->id,
            'class_id' => $this->class->id,
            'teacher_id' => $this->teacher->id,
            'tahun_ajaran' => $this->tahunAjaran,
            'semester' => $this->semester,
        ]);

        $response = $this->actingAs($this->teacher)->get('/teacher/grades');

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page
            ->component('Teacher/Grades/Index')
            ->where('grades.data', fn ($grades) => count($grades) >= 1)
        );
    }

    /**
     * Test teacher tidak melihat grades dari teacher lain
     */
    public function test_teacher_cannot_see_other_teachers_grades(): void
    {
        // Create grade milik other teacher
        Grade::factory()->ulanganHarian()->create([
            'student_id' => $this->student->id,
            'subject_id' => $this->subject->id,
            'class_id' => $this->class->id,
            'teacher_id' => $this->otherTeacher->id,
            'tahun_ajaran' => $this->tahunAjaran,
            'semester' => $this->semester,
        ]);

        $response = $this->actingAs($this->teacher)->get('/teacher/grades');

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page
            ->where('grades.data', fn ($grades) => count($grades) === 0)
        );
    }

    /**
     * Test non-teacher tidak bisa akses grades index
     */
    public function test_non_teacher_cannot_access_grades_index(): void
    {
        $parent = User::factory()->create(['role' => 'PARENT']);

        $response = $this->actingAs($parent)->get('/teacher/grades');

        $response->assertForbidden();
    }

    // ============================================================
    // CREATE TESTS
    // ============================================================

    /**
     * Test teacher dapat mengakses halaman create
     */
    public function test_teacher_can_access_create_page(): void
    {
        $response = $this->actingAs($this->teacher)->get('/teacher/grades/create');

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page
            ->component('Teacher/Grades/Create')
            ->has('classes')
            ->has('subjects')
            ->has('assessmentTypes')
        );
    }

    // ============================================================
    // STORE TESTS
    // ============================================================

    /**
     * Test teacher dapat menyimpan nilai bulk untuk siswa
     */
    public function test_teacher_can_store_grades_bulk(): void
    {
        // Create additional student
        $student2 = Student::factory()->create([
            'kelas_id' => $this->class->id,
            'status' => 'aktif',
        ]);

        $data = [
            'class_id' => $this->class->id,
            'subject_id' => $this->subject->id,
            'tahun_ajaran' => $this->tahunAjaran,
            'semester' => $this->semester,
            'assessment_type' => 'UH',
            'assessment_number' => 1,
            'title' => 'Ulangan Harian 1',
            'assessment_date' => now()->format('Y-m-d'),
            'grades' => [
                ['student_id' => $this->student->id, 'score' => 85],
                ['student_id' => $student2->id, 'score' => 90],
            ],
        ];

        $response = $this->actingAs($this->teacher)->post('/teacher/grades', $data);

        $response->assertRedirect(route('teacher.grades.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('grades', [
            'student_id' => $this->student->id,
            'subject_id' => $this->subject->id,
            'class_id' => $this->class->id,
            'teacher_id' => $this->teacher->id,
            'score' => 85,
        ]);

        $this->assertDatabaseHas('grades', [
            'student_id' => $student2->id,
            'score' => 90,
        ]);
    }

    /**
     * Test validation: score harus antara 0-100
     */
    public function test_grade_validation_score_0_to_100(): void
    {
        // Test score > 100
        $data = [
            'class_id' => $this->class->id,
            'subject_id' => $this->subject->id,
            'tahun_ajaran' => $this->tahunAjaran,
            'semester' => $this->semester,
            'assessment_type' => 'UH',
            'assessment_number' => 1,
            'title' => 'Ulangan Harian 1',
            'assessment_date' => now()->format('Y-m-d'),
            'grades' => [
                ['student_id' => $this->student->id, 'score' => 150],
            ],
        ];

        $response = $this->actingAs($this->teacher)->post('/teacher/grades', $data);

        $response->assertSessionHasErrors('grades.0.score');
    }

    /**
     * Test validation: score tidak boleh negatif
     */
    public function test_grade_validation_score_not_negative(): void
    {
        $data = [
            'class_id' => $this->class->id,
            'subject_id' => $this->subject->id,
            'tahun_ajaran' => $this->tahunAjaran,
            'semester' => $this->semester,
            'assessment_type' => 'UH',
            'assessment_number' => 1,
            'title' => 'Ulangan Harian 1',
            'assessment_date' => now()->format('Y-m-d'),
            'grades' => [
                ['student_id' => $this->student->id, 'score' => -10],
            ],
        ];

        $response = $this->actingAs($this->teacher)->post('/teacher/grades', $data);

        $response->assertSessionHasErrors('grades.0.score');
    }

    /**
     * Test score 0 diterima (valid edge case)
     */
    public function test_grade_score_zero_is_valid(): void
    {
        $data = [
            'class_id' => $this->class->id,
            'subject_id' => $this->subject->id,
            'tahun_ajaran' => $this->tahunAjaran,
            'semester' => $this->semester,
            'assessment_type' => 'UH',
            'assessment_number' => 1,
            'title' => 'Ulangan Harian 1',
            'assessment_date' => now()->format('Y-m-d'),
            'grades' => [
                ['student_id' => $this->student->id, 'score' => 0],
            ],
        ];

        $response = $this->actingAs($this->teacher)->post('/teacher/grades', $data);

        $response->assertRedirect(route('teacher.grades.index'));

        $this->assertDatabaseHas('grades', [
            'student_id' => $this->student->id,
            'score' => 0,
        ]);
    }

    /**
     * Test score 100 diterima (valid edge case)
     */
    public function test_grade_score_hundred_is_valid(): void
    {
        $data = [
            'class_id' => $this->class->id,
            'subject_id' => $this->subject->id,
            'tahun_ajaran' => $this->tahunAjaran,
            'semester' => $this->semester,
            'assessment_type' => 'UH',
            'assessment_number' => 1,
            'title' => 'Ulangan Harian 1',
            'assessment_date' => now()->format('Y-m-d'),
            'grades' => [
                ['student_id' => $this->student->id, 'score' => 100],
            ],
        ];

        $response = $this->actingAs($this->teacher)->post('/teacher/grades', $data);

        $response->assertRedirect(route('teacher.grades.index'));

        $this->assertDatabaseHas('grades', [
            'student_id' => $this->student->id,
            'score' => 100,
        ]);
    }

    /**
     * Test teacher tidak bisa input nilai untuk kelas yang tidak diajar
     */
    public function test_teacher_cannot_store_grades_for_unauthorized_class(): void
    {
        // Create class dan subject yang tidak diajar oleh teacher
        $otherClass = SchoolClass::factory()->create([
            'tahun_ajaran' => $this->tahunAjaran,
            'is_active' => true,
        ]);
        $otherStudent = Student::factory()->create([
            'kelas_id' => $otherClass->id,
            'status' => 'aktif',
        ]);

        $data = [
            'class_id' => $otherClass->id,
            'subject_id' => $this->subject->id,
            'tahun_ajaran' => $this->tahunAjaran,
            'semester' => $this->semester,
            'assessment_type' => 'UH',
            'assessment_number' => 1,
            'title' => 'Ulangan Harian 1',
            'assessment_date' => now()->format('Y-m-d'),
            'grades' => [
                ['student_id' => $otherStudent->id, 'score' => 85],
            ],
        ];

        $response = $this->actingAs($this->teacher)->post('/teacher/grades', $data);

        $response->assertForbidden();
    }

    // ============================================================
    // EDIT TESTS
    // ============================================================

    /**
     * Test teacher dapat mengakses halaman edit
     */
    public function test_teacher_can_access_edit_page(): void
    {
        $grade = Grade::factory()->ulanganHarian()->create([
            'student_id' => $this->student->id,
            'subject_id' => $this->subject->id,
            'class_id' => $this->class->id,
            'teacher_id' => $this->teacher->id,
            'tahun_ajaran' => $this->tahunAjaran,
            'semester' => $this->semester,
            'is_locked' => false,
        ]);

        $response = $this->actingAs($this->teacher)->get("/teacher/grades/{$grade->id}/edit");

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page
            ->component('Teacher/Grades/Edit')
            ->has('assessment')
            ->has('grades')
            ->has('class')
            ->has('subject')
        );
    }

    /**
     * Test teacher tidak bisa edit grade milik teacher lain
     */
    public function test_teacher_cannot_edit_other_teachers_grades(): void
    {
        $grade = Grade::factory()->ulanganHarian()->create([
            'student_id' => $this->student->id,
            'subject_id' => $this->subject->id,
            'class_id' => $this->class->id,
            'teacher_id' => $this->otherTeacher->id,
            'tahun_ajaran' => $this->tahunAjaran,
            'semester' => $this->semester,
        ]);

        $response = $this->actingAs($this->teacher)->get("/teacher/grades/{$grade->id}/edit");

        $response->assertForbidden();
    }

    /**
     * Test teacher tidak bisa akses edit untuk nilai yang sudah dikunci
     */
    public function test_teacher_cannot_access_edit_page_for_locked_grades(): void
    {
        $grade = Grade::factory()->ulanganHarian()->locked()->create([
            'student_id' => $this->student->id,
            'subject_id' => $this->subject->id,
            'class_id' => $this->class->id,
            'teacher_id' => $this->teacher->id,
            'tahun_ajaran' => $this->tahunAjaran,
            'semester' => $this->semester,
        ]);

        $response = $this->actingAs($this->teacher)->get("/teacher/grades/{$grade->id}/edit");

        $response->assertRedirect(route('teacher.grades.index'));
        $response->assertSessionHas('error');
    }

    // ============================================================
    // UPDATE TESTS
    // ============================================================

    /**
     * Test teacher dapat update nilai
     */
    public function test_teacher_can_update_grades(): void
    {
        $grade = Grade::factory()->ulanganHarian()->create([
            'student_id' => $this->student->id,
            'subject_id' => $this->subject->id,
            'class_id' => $this->class->id,
            'teacher_id' => $this->teacher->id,
            'tahun_ajaran' => $this->tahunAjaran,
            'semester' => $this->semester,
            'score' => 80,
            'is_locked' => false,
        ]);

        $data = [
            'title' => 'Ulangan Harian 1 Updated',
            'assessment_date' => now()->format('Y-m-d'),
            'grades' => [
                ['id' => $grade->id, 'score' => 95, 'notes' => 'Improved score'],
            ],
        ];

        $response = $this->actingAs($this->teacher)->put("/teacher/grades/{$grade->id}", $data);

        $response->assertRedirect(route('teacher.grades.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('grades', [
            'id' => $grade->id,
            'score' => 95,
            'notes' => 'Improved score',
        ]);
    }

    /**
     * Test teacher tidak bisa update nilai yang sudah dikunci
     * UpdateGradeRequest authorize() returns false untuk locked grades
     */
    public function test_teacher_cannot_update_locked_grades(): void
    {
        $grade = Grade::factory()->ulanganHarian()->locked()->create([
            'student_id' => $this->student->id,
            'subject_id' => $this->subject->id,
            'class_id' => $this->class->id,
            'teacher_id' => $this->teacher->id,
            'tahun_ajaran' => $this->tahunAjaran,
            'semester' => $this->semester,
            'score' => 80,
        ]);

        $data = [
            'title' => 'Ulangan Harian 1 Updated',
            'assessment_date' => now()->format('Y-m-d'),
            'grades' => [
                ['id' => $grade->id, 'score' => 95],
            ],
        ];

        $response = $this->actingAs($this->teacher)->put("/teacher/grades/{$grade->id}", $data);

        // UpdateGradeRequest->authorize() returns false when grade is locked
        $response->assertForbidden();

        // Verify score unchanged
        $this->assertDatabaseHas('grades', [
            'id' => $grade->id,
            'score' => 80,
        ]);
    }

    /**
     * Test teacher tidak bisa update grade milik teacher lain
     */
    public function test_teacher_cannot_update_other_teachers_grades(): void
    {
        $grade = Grade::factory()->ulanganHarian()->create([
            'student_id' => $this->student->id,
            'subject_id' => $this->subject->id,
            'class_id' => $this->class->id,
            'teacher_id' => $this->otherTeacher->id,
            'tahun_ajaran' => $this->tahunAjaran,
            'semester' => $this->semester,
        ]);

        $data = [
            'title' => 'Ulangan Harian 1 Updated',
            'assessment_date' => now()->format('Y-m-d'),
            'grades' => [
                ['id' => $grade->id, 'score' => 95],
            ],
        ];

        $response = $this->actingAs($this->teacher)->put("/teacher/grades/{$grade->id}", $data);

        $response->assertForbidden();
    }

    // ============================================================
    // DELETE TESTS
    // ============================================================

    /**
     * Test teacher dapat menghapus nilai yang belum dikunci
     * Grade model menggunakan SoftDeletes
     */
    public function test_teacher_can_delete_unlocked_grades(): void
    {
        $assessmentDate = now()->subDays(5)->format('Y-m-d');

        $grade = Grade::factory()->ulanganHarian()->create([
            'student_id' => $this->student->id,
            'subject_id' => $this->subject->id,
            'class_id' => $this->class->id,
            'teacher_id' => $this->teacher->id,
            'tahun_ajaran' => $this->tahunAjaran,
            'semester' => $this->semester,
            'is_locked' => false,
            'assessment_date' => $assessmentDate,
        ]);

        $response = $this->actingAs($this->teacher)->delete("/teacher/grades/{$grade->id}");

        $response->assertRedirect(route('teacher.grades.index'));
        $response->assertSessionHas('success');

        // Grade uses SoftDeletes, verify it was soft deleted
        $this->assertSoftDeleted('grades', [
            'id' => $grade->id,
        ]);
    }

    /**
     * Test teacher tidak bisa menghapus nilai yang sudah dikunci
     */
    public function test_teacher_cannot_delete_locked_grades(): void
    {
        $grade = Grade::factory()->ulanganHarian()->locked()->create([
            'student_id' => $this->student->id,
            'subject_id' => $this->subject->id,
            'class_id' => $this->class->id,
            'teacher_id' => $this->teacher->id,
            'tahun_ajaran' => $this->tahunAjaran,
            'semester' => $this->semester,
        ]);

        $response = $this->actingAs($this->teacher)->delete("/teacher/grades/{$grade->id}");

        $response->assertSessionHas('error');

        // Verify grade still exists
        $this->assertDatabaseHas('grades', [
            'id' => $grade->id,
        ]);
    }

    /**
     * Test teacher tidak bisa menghapus grade milik teacher lain
     */
    public function test_teacher_cannot_delete_other_teachers_grades(): void
    {
        $grade = Grade::factory()->ulanganHarian()->create([
            'student_id' => $this->student->id,
            'subject_id' => $this->subject->id,
            'class_id' => $this->class->id,
            'teacher_id' => $this->otherTeacher->id,
            'tahun_ajaran' => $this->tahunAjaran,
            'semester' => $this->semester,
        ]);

        $response = $this->actingAs($this->teacher)->delete("/teacher/grades/{$grade->id}");

        $response->assertForbidden();
    }

    // ============================================================
    // API ENDPOINT TESTS
    // ============================================================

    /**
     * Test get students by class API endpoint
     */
    public function test_can_get_students_by_class(): void
    {
        $response = $this->actingAs($this->teacher)
            ->getJson("/teacher/grades/classes/{$this->class->id}/students");

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'data' => [
                '*' => ['id', 'nis', 'nama_lengkap'],
            ],
        ]);
    }

    /**
     * Test get subjects by class API endpoint
     */
    public function test_can_get_subjects_by_class(): void
    {
        $response = $this->actingAs($this->teacher)
            ->getJson("/teacher/grades/classes/{$this->class->id}/subjects?tahun_ajaran={$this->tahunAjaran}");

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'data' => [
                '*' => ['id', 'kode_mapel', 'nama_mapel'],
            ],
        ]);
    }

    // ============================================================
    // FILTER TESTS
    // ============================================================

    /**
     * Test filter grades by semester
     */
    public function test_can_filter_grades_by_semester(): void
    {
        // Create grades untuk semester 1 dan 2
        Grade::factory()->ulanganHarian()->create([
            'student_id' => $this->student->id,
            'subject_id' => $this->subject->id,
            'class_id' => $this->class->id,
            'teacher_id' => $this->teacher->id,
            'tahun_ajaran' => $this->tahunAjaran,
            'semester' => '1',
        ]);

        Grade::factory()->ulanganHarian()->create([
            'student_id' => $this->student->id,
            'subject_id' => $this->subject->id,
            'class_id' => $this->class->id,
            'teacher_id' => $this->teacher->id,
            'tahun_ajaran' => $this->tahunAjaran,
            'semester' => '2',
            'title' => 'UH Semester 2',
        ]);

        $response = $this->actingAs($this->teacher)->get('/teacher/grades?semester=1');

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page
            ->where('filters.semester', '1')
        );
    }

    /**
     * Test filter grades by subject
     */
    public function test_can_filter_grades_by_subject(): void
    {
        $response = $this->actingAs($this->teacher)
            ->get("/teacher/grades?subject_id={$this->subject->id}");

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page
            ->where('filters.subject_id', $this->subject->id)
        );
    }

    /**
     * Test filter grades by class
     */
    public function test_can_filter_grades_by_class(): void
    {
        $response = $this->actingAs($this->teacher)
            ->get("/teacher/grades?class_id={$this->class->id}");

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page
            ->where('filters.class_id', $this->class->id)
        );
    }
}
