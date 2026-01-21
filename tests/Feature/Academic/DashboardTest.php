<?php

namespace Tests\Feature\Academic;

use App\Models\AttitudeGrade;
use App\Models\Grade;
use App\Models\ReportCard;
use App\Models\SchoolClass;
use App\Models\Student;
use App\Models\Subject;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Feature tests untuk Principal Academic Dashboard
 * Testing dashboard analytics dan rekap nilai untuk kepala sekolah
 */
class DashboardTest extends TestCase
{
    use RefreshDatabase;

    protected User $principal;

    protected User $admin;

    protected User $teacher;

    protected SchoolClass $class;

    protected Student $student;

    protected Subject $subject;

    protected string $tahunAjaran;

    protected string $semester;

    protected function setUp(): void
    {
        parent::setUp();

        // Create principal user
        $this->principal = User::factory()->create([
            'role' => 'PRINCIPAL',
        ]);

        // Create admin user
        $this->admin = User::factory()->create([
            'role' => 'ADMIN',
        ]);

        // Create teacher (wali kelas)
        $this->teacher = User::factory()->create([
            'role' => 'TEACHER',
        ]);

        // Create class with wali kelas
        $this->tahunAjaran = now()->month >= 7
            ? now()->year.'/'.(now()->year + 1)
            : (now()->year - 1).'/'.now()->year;
        $this->semester = now()->month >= 7 ? '1' : '2';

        $this->class = SchoolClass::factory()->create([
            'wali_kelas_id' => $this->teacher->id,
            'tahun_ajaran' => $this->tahunAjaran,
            'is_active' => true,
        ]);

        // Create student
        $this->student = Student::factory()->create([
            'kelas_id' => $this->class->id,
            'status' => 'aktif',
        ]);

        // Create subject
        $this->subject = Subject::factory()->create([
            'is_active' => true,
        ]);

        // Attach subject to class
        $this->class->subjects()->attach($this->subject->id, [
            'teacher_id' => $this->teacher->id,
            'tahun_ajaran' => $this->tahunAjaran,
        ]);
    }

    // ============================================================
    // ACADEMIC DASHBOARD TESTS
    // ============================================================

    /**
     * Test principal dapat mengakses halaman dashboard akademik
     */
    public function test_principal_can_access_academic_dashboard(): void
    {
        $response = $this->actingAs($this->principal)->get('/principal/academic/dashboard');

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page
            ->component('Principal/Academic/Dashboard')
            ->has('classes')
            ->has('subjects')
            ->has('subjectAverages')
            ->has('predikatDistribution')
            ->has('belowKKMSubjects')
            ->has('overallStats')
            ->has('reportCardStats')
            ->has('filters')
        );
    }

    /**
     * Test dashboard menampilkan subject averages dengan benar
     */
    public function test_dashboard_shows_subject_averages(): void
    {
        // Create grades for the subject
        $this->createCompleteGradesForStudent();

        $response = $this->actingAs($this->principal)->get('/principal/academic/dashboard');

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page
            ->has('subjectAverages', fn ($averages) => $averages
                ->first(fn ($avg) => $avg['subject_id'] === $this->subject->id && $avg['average'] > 0)
            )
        );
    }

    /**
     * Test dashboard filter berdasarkan kelas
     */
    public function test_dashboard_filters_by_class(): void
    {
        $this->createCompleteGradesForStudent();

        $response = $this->actingAs($this->principal)->get('/principal/academic/dashboard?class_id='.$this->class->id);

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page
            ->where('filters.class_id', $this->class->id)
        );
    }

    /**
     * Test dashboard filter berdasarkan semester
     */
    public function test_dashboard_filters_by_semester(): void
    {
        $this->createCompleteGradesForStudent();

        $response = $this->actingAs($this->principal)->get('/principal/academic/dashboard?semester='.$this->semester);

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page
            ->where('filters.semester', $this->semester)
        );
    }

    // ============================================================
    // GRADES REKAP TESTS
    // ============================================================

    /**
     * Test principal dapat mengakses halaman rekap nilai
     */
    public function test_principal_can_access_grades_page(): void
    {
        $response = $this->actingAs($this->principal)->get('/principal/academic/grades');

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page
            ->component('Principal/Academic/Grades')
            ->has('classes')
            ->has('classSummaries')
            ->has('filters')
            ->where('isDetailView', false)
        );
    }

    /**
     * Test grades page overview mode (semua kelas)
     */
    public function test_grades_page_shows_all_classes_overview(): void
    {
        // Create another class
        $anotherClass = SchoolClass::factory()->create([
            'wali_kelas_id' => $this->teacher->id,
            'tahun_ajaran' => $this->tahunAjaran,
            'is_active' => true,
        ]);

        $response = $this->actingAs($this->principal)->get('/principal/academic/grades');

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page
            ->where('isDetailView', false)
            ->has('classSummaries', 2) // 2 classes
        );
    }

    /**
     * Test grades page detail mode (single class)
     */
    public function test_grades_page_shows_single_class_detail(): void
    {
        $this->createCompleteGradesForStudent();

        // Create report card for ranking
        ReportCard::factory()->create([
            'student_id' => $this->student->id,
            'class_id' => $this->class->id,
            'tahun_ajaran' => $this->tahunAjaran,
            'semester' => $this->semester,
            'average_score' => 82.0,
            'rank' => 1,
        ]);

        $response = $this->actingAs($this->principal)->get('/principal/academic/grades?class_id='.$this->class->id);

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page
            ->where('isDetailView', true)
            ->has('classSummaries', 1)
        );
    }

    // ============================================================
    // AUTHORIZATION TESTS
    // ============================================================

    /**
     * Test non-principal tidak bisa akses dashboard akademik
     */
    public function test_non_principal_cannot_access_academic_dashboard(): void
    {
        $teacher = User::factory()->create(['role' => 'TEACHER']);

        $response = $this->actingAs($teacher)->get('/principal/academic/dashboard');

        $response->assertForbidden();
    }

    /**
     * Test non-principal tidak bisa akses rekap nilai
     */
    public function test_non_principal_cannot_access_grades_page(): void
    {
        $parent = User::factory()->create(['role' => 'PARENT']);

        $response = $this->actingAs($parent)->get('/principal/academic/grades');

        $response->assertForbidden();
    }

    /**
     * Test admin juga tidak bisa akses principal dashboard
     */
    public function test_admin_cannot_access_principal_academic_dashboard(): void
    {
        $response = $this->actingAs($this->admin)->get('/principal/academic/dashboard');

        $response->assertForbidden();
    }

    // ============================================================
    // HELPER METHODS
    // ============================================================

    /**
     * Helper: Create complete grades for student
     */
    protected function createCompleteGradesForStudent(): void
    {
        Grade::factory()->create([
            'student_id' => $this->student->id,
            'subject_id' => $this->subject->id,
            'class_id' => $this->class->id,
            'teacher_id' => $this->teacher->id,
            'tahun_ajaran' => $this->tahunAjaran,
            'semester' => $this->semester,
            'assessment_type' => Grade::ASSESSMENT_UH,
            'score' => 85,
        ]);

        Grade::factory()->create([
            'student_id' => $this->student->id,
            'subject_id' => $this->subject->id,
            'class_id' => $this->class->id,
            'teacher_id' => $this->teacher->id,
            'tahun_ajaran' => $this->tahunAjaran,
            'semester' => $this->semester,
            'assessment_type' => Grade::ASSESSMENT_UTS,
            'score' => 80,
        ]);

        Grade::factory()->create([
            'student_id' => $this->student->id,
            'subject_id' => $this->subject->id,
            'class_id' => $this->class->id,
            'teacher_id' => $this->teacher->id,
            'tahun_ajaran' => $this->tahunAjaran,
            'semester' => $this->semester,
            'assessment_type' => Grade::ASSESSMENT_UAS,
            'score' => 82,
        ]);

        AttitudeGrade::factory()->create([
            'student_id' => $this->student->id,
            'class_id' => $this->class->id,
            'teacher_id' => $this->teacher->id,
            'tahun_ajaran' => $this->tahunAjaran,
            'semester' => $this->semester,
        ]);
    }
}
