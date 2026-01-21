<?php

namespace Tests\Feature\Teacher;

use App\Models\AttitudeGrade;
use App\Models\SchoolClass;
use App\Models\Student;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Feature tests untuk Teacher AttitudeGradeController
 * Testing wali kelas middleware, CRUD operations, dan validation
 */
class AttitudeGradeControllerTest extends TestCase
{
    use RefreshDatabase;

    protected User $waliKelas;

    protected User $regularTeacher;

    protected User $admin;

    protected SchoolClass $class;

    protected Student $student;

    protected string $tahunAjaran;

    protected string $semester;

    protected function setUp(): void
    {
        parent::setUp();

        // Create wali kelas user
        $this->waliKelas = User::factory()->create([
            'role' => 'TEACHER',
        ]);

        // Create regular teacher (not wali kelas)
        $this->regularTeacher = User::factory()->create([
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
            'wali_kelas_id' => $this->waliKelas->id,
            'tahun_ajaran' => $this->tahunAjaran,
            'is_active' => true,
        ]);

        // Create student di kelas
        $this->student = Student::factory()->create([
            'kelas_id' => $this->class->id,
            'status' => 'aktif',
        ]);
    }

    // ============================================================
    // INDEX TESTS
    // ============================================================

    /**
     * Test wali kelas dapat mengakses halaman index nilai sikap
     */
    public function test_wali_kelas_can_access_attitude_grades_index(): void
    {
        $response = $this->actingAs($this->waliKelas)->get('/teacher/attitude-grades');

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page
            ->component('Teacher/AttitudeGrades/Index')
            ->where('isWaliKelas', true)
            ->has('class')
            ->has('attitudeGrades')
            ->has('gradeOptions')
        );
    }

    /**
     * Test teacher yang bukan wali kelas dapat akses index tapi dengan isWaliKelas = false
     */
    public function test_non_wali_kelas_can_access_index_but_sees_empty_state(): void
    {
        $response = $this->actingAs($this->regularTeacher)->get('/teacher/attitude-grades');

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page
            ->component('Teacher/AttitudeGrades/Index')
            ->where('isWaliKelas', false)
            ->where('class', null)
        );
    }

    /**
     * Test wali kelas melihat list nilai sikap yang sudah diinput
     */
    public function test_wali_kelas_can_see_attitude_grades_list(): void
    {
        // Create attitude grade
        AttitudeGrade::factory()->create([
            'student_id' => $this->student->id,
            'class_id' => $this->class->id,
            'teacher_id' => $this->waliKelas->id,
            'tahun_ajaran' => $this->tahunAjaran,
            'semester' => $this->semester,
        ]);

        $response = $this->actingAs($this->waliKelas)->get('/teacher/attitude-grades');

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page
            ->where('attitudeGrades.data', fn ($grades) => count($grades) >= 1)
        );
    }

    /**
     * Test non-teacher tidak bisa akses attitude grades
     */
    public function test_non_teacher_cannot_access_attitude_grades(): void
    {
        $parent = User::factory()->create(['role' => 'PARENT']);

        $response = $this->actingAs($parent)->get('/teacher/attitude-grades');

        $response->assertForbidden();
    }

    // ============================================================
    // CREATE TESTS
    // ============================================================

    /**
     * Test wali kelas dapat mengakses halaman create
     */
    public function test_wali_kelas_can_access_create_page(): void
    {
        $response = $this->actingAs($this->waliKelas)->get('/teacher/attitude-grades/create');

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page
            ->component('Teacher/AttitudeGrades/Create')
            ->has('class')
            ->has('students')
            ->has('gradeOptions')
            ->has('spiritualTemplates')
            ->has('socialTemplates')
        );
    }

    /**
     * Test teacher yang bukan wali kelas tidak bisa akses create page
     */
    public function test_non_wali_kelas_cannot_access_create_page(): void
    {
        $response = $this->actingAs($this->regularTeacher)->get('/teacher/attitude-grades/create');

        $response->assertForbidden();
    }

    // ============================================================
    // STORE TESTS
    // ============================================================

    /**
     * Test wali kelas dapat menyimpan nilai sikap
     */
    public function test_wali_kelas_can_store_attitude_grades(): void
    {
        $data = [
            'class_id' => $this->class->id,
            'tahun_ajaran' => $this->tahunAjaran,
            'semester' => $this->semester,
            'grades' => [
                [
                    'student_id' => $this->student->id,
                    'spiritual_grade' => 'A',
                    'spiritual_description' => 'Sangat baik dalam beribadah',
                    'social_grade' => 'B',
                    'social_description' => 'Baik dalam bersosialisasi',
                    'homeroom_notes' => 'Siswa berprestasi',
                ],
            ],
        ];

        $response = $this->actingAs($this->waliKelas)->post('/teacher/attitude-grades', $data);

        $response->assertRedirect(route('teacher.attitude-grades.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('attitude_grades', [
            'student_id' => $this->student->id,
            'class_id' => $this->class->id,
            'teacher_id' => $this->waliKelas->id,
            'spiritual_grade' => 'A',
            'social_grade' => 'B',
        ]);
    }

    /**
     * Test wali kelas dapat menyimpan nilai sikap untuk multiple siswa
     */
    public function test_wali_kelas_can_store_attitude_grades_bulk(): void
    {
        // Create additional student
        $student2 = Student::factory()->create([
            'kelas_id' => $this->class->id,
            'status' => 'aktif',
        ]);

        $data = [
            'class_id' => $this->class->id,
            'tahun_ajaran' => $this->tahunAjaran,
            'semester' => $this->semester,
            'grades' => [
                [
                    'student_id' => $this->student->id,
                    'spiritual_grade' => 'A',
                    'social_grade' => 'A',
                ],
                [
                    'student_id' => $student2->id,
                    'spiritual_grade' => 'B',
                    'social_grade' => 'B',
                ],
            ],
        ];

        $response = $this->actingAs($this->waliKelas)->post('/teacher/attitude-grades', $data);

        $response->assertRedirect(route('teacher.attitude-grades.index'));

        $this->assertDatabaseHas('attitude_grades', [
            'student_id' => $this->student->id,
            'spiritual_grade' => 'A',
        ]);

        $this->assertDatabaseHas('attitude_grades', [
            'student_id' => $student2->id,
            'spiritual_grade' => 'B',
        ]);
    }

    /**
     * Test teacher yang bukan wali kelas tidak bisa store nilai sikap
     */
    public function test_non_wali_kelas_cannot_store_attitude_grades(): void
    {
        $data = [
            'class_id' => $this->class->id,
            'tahun_ajaran' => $this->tahunAjaran,
            'semester' => $this->semester,
            'grades' => [
                [
                    'student_id' => $this->student->id,
                    'spiritual_grade' => 'A',
                    'social_grade' => 'A',
                ],
            ],
        ];

        $response = $this->actingAs($this->regularTeacher)->post('/teacher/attitude-grades', $data);

        $response->assertForbidden();
    }

    /**
     * Test wali kelas tidak bisa store nilai sikap untuk kelas lain
     */
    public function test_wali_kelas_cannot_store_grades_for_other_class(): void
    {
        // Create another class
        $otherClass = SchoolClass::factory()->create([
            'wali_kelas_id' => $this->regularTeacher->id,
            'tahun_ajaran' => $this->tahunAjaran,
            'is_active' => true,
        ]);
        $otherStudent = Student::factory()->create([
            'kelas_id' => $otherClass->id,
            'status' => 'aktif',
        ]);

        $data = [
            'class_id' => $otherClass->id,
            'tahun_ajaran' => $this->tahunAjaran,
            'semester' => $this->semester,
            'grades' => [
                [
                    'student_id' => $otherStudent->id,
                    'spiritual_grade' => 'A',
                    'social_grade' => 'A',
                ],
            ],
        ];

        $response = $this->actingAs($this->waliKelas)->post('/teacher/attitude-grades', $data);

        $response->assertForbidden();
    }

    // ============================================================
    // VALIDATION TESTS
    // ============================================================

    /**
     * Test validation: grade harus A, B, C, atau D
     */
    public function test_attitude_grade_validation_valid_grades(): void
    {
        $data = [
            'class_id' => $this->class->id,
            'tahun_ajaran' => $this->tahunAjaran,
            'semester' => $this->semester,
            'grades' => [
                [
                    'student_id' => $this->student->id,
                    'spiritual_grade' => 'E', // Invalid
                    'social_grade' => 'A',
                ],
            ],
        ];

        $response = $this->actingAs($this->waliKelas)->post('/teacher/attitude-grades', $data);

        $response->assertSessionHasErrors('grades.0.spiritual_grade');
    }

    /**
     * Test validation: spiritual_description max 200 characters
     */
    public function test_attitude_grade_validation_description_max_length(): void
    {
        $data = [
            'class_id' => $this->class->id,
            'tahun_ajaran' => $this->tahunAjaran,
            'semester' => $this->semester,
            'grades' => [
                [
                    'student_id' => $this->student->id,
                    'spiritual_grade' => 'A',
                    'spiritual_description' => str_repeat('a', 250), // 250 chars, exceeds 200
                    'social_grade' => 'A',
                ],
            ],
        ];

        $response = $this->actingAs($this->waliKelas)->post('/teacher/attitude-grades', $data);

        $response->assertSessionHasErrors('grades.0.spiritual_description');
    }

    /**
     * Test validation: homeroom_notes max 500 characters
     */
    public function test_attitude_grade_validation_homeroom_notes_max_length(): void
    {
        $data = [
            'class_id' => $this->class->id,
            'tahun_ajaran' => $this->tahunAjaran,
            'semester' => $this->semester,
            'grades' => [
                [
                    'student_id' => $this->student->id,
                    'spiritual_grade' => 'A',
                    'social_grade' => 'A',
                    'homeroom_notes' => str_repeat('a', 550), // 550 chars, exceeds 500
                ],
            ],
        ];

        $response = $this->actingAs($this->waliKelas)->post('/teacher/attitude-grades', $data);

        $response->assertSessionHasErrors('grades.0.homeroom_notes');
    }

    /**
     * Test validation: student must belong to the class
     */
    public function test_attitude_grade_validation_student_must_be_in_class(): void
    {
        // Create student from another class
        $otherClass = SchoolClass::factory()->create([
            'tahun_ajaran' => $this->tahunAjaran,
            'is_active' => true,
        ]);
        $otherStudent = Student::factory()->create([
            'kelas_id' => $otherClass->id,
            'status' => 'aktif',
        ]);

        $data = [
            'class_id' => $this->class->id,
            'tahun_ajaran' => $this->tahunAjaran,
            'semester' => $this->semester,
            'grades' => [
                [
                    'student_id' => $otherStudent->id, // Student from different class
                    'spiritual_grade' => 'A',
                    'social_grade' => 'A',
                ],
            ],
        ];

        $response = $this->actingAs($this->waliKelas)->post('/teacher/attitude-grades', $data);

        $response->assertSessionHasErrors('grades');
    }

    // ============================================================
    // UPSERT TESTS
    // ============================================================

    /**
     * Test update existing attitude grade via upsert
     */
    public function test_wali_kelas_can_update_existing_attitude_grades(): void
    {
        // Create existing attitude grade
        $existing = AttitudeGrade::factory()->create([
            'student_id' => $this->student->id,
            'class_id' => $this->class->id,
            'teacher_id' => $this->waliKelas->id,
            'tahun_ajaran' => $this->tahunAjaran,
            'semester' => $this->semester,
            'spiritual_grade' => 'B',
            'social_grade' => 'B',
        ]);

        // Submit update via store (uses updateOrCreate)
        $data = [
            'class_id' => $this->class->id,
            'tahun_ajaran' => $this->tahunAjaran,
            'semester' => $this->semester,
            'grades' => [
                [
                    'student_id' => $this->student->id,
                    'spiritual_grade' => 'A', // Changed from B to A
                    'social_grade' => 'A', // Changed from B to A
                ],
            ],
        ];

        $response = $this->actingAs($this->waliKelas)->post('/teacher/attitude-grades', $data);

        $response->assertRedirect(route('teacher.attitude-grades.index'));

        // Verify updated (not duplicated)
        $this->assertEquals(1, AttitudeGrade::where('student_id', $this->student->id)
            ->where('tahun_ajaran', $this->tahunAjaran)
            ->where('semester', $this->semester)
            ->count());

        $this->assertDatabaseHas('attitude_grades', [
            'student_id' => $this->student->id,
            'spiritual_grade' => 'A',
            'social_grade' => 'A',
        ]);
    }

    // ============================================================
    // FILTER TESTS
    // ============================================================

    /**
     * Test filter attitude grades by semester
     */
    public function test_can_filter_attitude_grades_by_semester(): void
    {
        // Create grades for both semesters
        AttitudeGrade::factory()->create([
            'student_id' => $this->student->id,
            'class_id' => $this->class->id,
            'teacher_id' => $this->waliKelas->id,
            'tahun_ajaran' => $this->tahunAjaran,
            'semester' => '1',
        ]);

        $response = $this->actingAs($this->waliKelas)->get('/teacher/attitude-grades?semester=1');

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page
            ->where('filters.semester', '1')
        );
    }

    /**
     * Test search attitude grades by student name
     */
    public function test_can_search_attitude_grades_by_student_name(): void
    {
        // Create grade
        AttitudeGrade::factory()->create([
            'student_id' => $this->student->id,
            'class_id' => $this->class->id,
            'teacher_id' => $this->waliKelas->id,
            'tahun_ajaran' => $this->tahunAjaran,
            'semester' => $this->semester,
        ]);

        $response = $this->actingAs($this->waliKelas)
            ->get('/teacher/attitude-grades?search='.$this->student->nama_lengkap);

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page
            ->where('filters.search', $this->student->nama_lengkap)
        );
    }
}
