<?php

namespace Tests\Feature\ReportCard;

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
 * Feature tests untuk Report Card Generation
 * Testing generate rapor, validasi kelengkapan, dan manajemen rapor
 */
class ReportCardGenerationTest extends TestCase
{
    use RefreshDatabase;

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

    /**
     * Test admin dapat mengakses halaman index rapor
     */
    public function test_admin_can_access_report_cards_index(): void
    {
        $response = $this->actingAs($this->admin)->get('/admin/report-cards');

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page
            ->component('Admin/ReportCards/Index')
            ->has('reportCards')
            ->has('filters')
            ->has('statistics')
        );
    }

    /**
     * Test admin dapat mengakses halaman generate rapor
     */
    public function test_admin_can_access_generate_page(): void
    {
        $response = $this->actingAs($this->admin)->get('/admin/report-cards/generate');

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page
            ->component('Admin/ReportCards/Generate')
            ->has('classes')
            ->has('defaultFilters')
        );
    }

    /**
     * Test validasi kelengkapan data nilai
     */
    public function test_can_validate_completeness_with_missing_data(): void
    {
        // Tanpa nilai, validasi harus gagal
        $response = $this->actingAs($this->admin)->postJson('/admin/report-cards/validate', [
            'class_id' => $this->class->id,
            'tahun_ajaran' => $this->tahunAjaran,
            'semester' => $this->semester,
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'is_complete' => false,
        ]);

        // Students should have missing data
        $data = $response->json();
        $this->assertGreaterThan(0, $data['missing_count']);
    }

    /**
     * Test validasi kelengkapan dengan data lengkap
     */
    public function test_can_validate_completeness_with_complete_data(): void
    {
        // Create complete grades for student
        $this->createCompleteGradesForStudent();

        $response = $this->actingAs($this->admin)->postJson('/admin/report-cards/validate', [
            'class_id' => $this->class->id,
            'tahun_ajaran' => $this->tahunAjaran,
            'semester' => $this->semester,
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'is_complete' => true,
            'missing_count' => 0,
        ]);
    }

    /**
     * Test generate rapor bulk
     */
    public function test_admin_can_generate_report_cards_bulk(): void
    {
        // Create complete grades
        $this->createCompleteGradesForStudent();

        $response = $this->actingAs($this->admin)->postJson('/admin/report-cards/generate', [
            'class_ids' => [$this->class->id],
            'tahun_ajaran' => $this->tahunAjaran,
            'semester' => $this->semester,
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'total_generated' => 1,
            'total_failed' => 0,
        ]);

        // Verify report card was created
        $this->assertDatabaseHas('report_cards', [
            'student_id' => $this->student->id,
            'class_id' => $this->class->id,
            'tahun_ajaran' => $this->tahunAjaran,
            'semester' => $this->semester,
            'status' => ReportCard::STATUS_DRAFT,
        ]);
    }

    /**
     * Test generate rapor mengunci nilai
     */
    public function test_generate_report_cards_locks_grades(): void
    {
        // Create complete grades
        $this->createCompleteGradesForStudent();

        // Verify grades are unlocked
        $this->assertFalse(Grade::where('student_id', $this->student->id)->first()->is_locked);

        // Generate report cards
        $this->actingAs($this->admin)->postJson('/admin/report-cards/generate', [
            'class_ids' => [$this->class->id],
            'tahun_ajaran' => $this->tahunAjaran,
            'semester' => $this->semester,
        ]);

        // Verify grades are now locked
        $this->assertTrue(Grade::where('student_id', $this->student->id)->first()->refresh()->is_locked);
    }

    /**
     * Test admin dapat melihat preview rapor
     */
    public function test_admin_can_view_report_card_preview(): void
    {
        // Create report card
        $this->createCompleteGradesForStudent();
        $reportCard = ReportCard::factory()->create([
            'student_id' => $this->student->id,
            'class_id' => $this->class->id,
            'tahun_ajaran' => $this->tahunAjaran,
            'semester' => $this->semester,
        ]);

        $response = $this->actingAs($this->admin)->get("/admin/report-cards/{$reportCard->id}");

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page
            ->component('Admin/ReportCards/Show')
            ->has('reportCard')
        );
    }

    /**
     * Test admin dapat unlock rapor
     */
    public function test_admin_can_unlock_report_card(): void
    {
        $this->createCompleteGradesForStudent();

        // Lock grades and create report card
        Grade::query()
            ->byStudent($this->student->id)
            ->update(['is_locked' => true]);

        $reportCard = ReportCard::factory()->create([
            'student_id' => $this->student->id,
            'class_id' => $this->class->id,
            'tahun_ajaran' => $this->tahunAjaran,
            'semester' => $this->semester,
            'status' => ReportCard::STATUS_DRAFT,
        ]);

        $response = $this->actingAs($this->admin)->post("/admin/report-cards/{$reportCard->id}/unlock");

        $response->assertRedirect();

        // Verify grades are unlocked
        $this->assertFalse(Grade::where('student_id', $this->student->id)->first()->refresh()->is_locked);
    }

    /**
     * Test unauthorized user tidak dapat mengakses halaman rapor
     */
    public function test_unauthorized_user_cannot_access_report_cards(): void
    {
        $parent = User::factory()->create(['role' => 'PARENT']);

        $response = $this->actingAs($parent)->get('/admin/report-cards');

        $response->assertForbidden();
    }

    /**
     * Helper: Create complete grades for student
     */
    protected function createCompleteGradesForStudent(): void
    {
        // Create UH grades
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

        // Create UTS grade
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

        // Create UAS grade
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

        // Create attitude grade
        AttitudeGrade::factory()->create([
            'student_id' => $this->student->id,
            'class_id' => $this->class->id,
            'teacher_id' => $this->teacher->id,
            'tahun_ajaran' => $this->tahunAjaran,
            'semester' => $this->semester,
        ]);
    }
}
