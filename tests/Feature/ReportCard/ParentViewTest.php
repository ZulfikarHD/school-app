<?php

namespace Tests\Feature\ReportCard;

use App\Models\AttitudeGrade;
use App\Models\Grade;
use App\Models\Guardian;
use App\Models\ReportCard;
use App\Models\SchoolClass;
use App\Models\Student;
use App\Models\Subject;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/**
 * Feature tests untuk Parent View Grades & Report Cards
 * Testing akses nilai dan rapor anak untuk orang tua
 */
class ParentViewTest extends TestCase
{
    use RefreshDatabase;

    protected User $parent;

    protected User $otherParent;

    protected Guardian $guardian;

    protected Guardian $otherGuardian;

    protected User $teacher;

    protected SchoolClass $class;

    protected Student $student;

    protected Student $otherStudent;

    protected Subject $subject;

    protected string $tahunAjaran;

    protected string $semester;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('local');

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

        // Create subject
        $this->subject = Subject::factory()->create([
            'is_active' => true,
        ]);

        // Attach subject to class
        $this->class->subjects()->attach($this->subject->id, [
            'teacher_id' => $this->teacher->id,
            'tahun_ajaran' => $this->tahunAjaran,
        ]);

        // Create parent user and guardian
        $this->parent = User::factory()->create([
            'role' => 'PARENT',
        ]);

        $this->guardian = Guardian::factory()->create([
            'user_id' => $this->parent->id,
        ]);

        // Create student linked to guardian
        $this->student = Student::factory()->create([
            'kelas_id' => $this->class->id,
            'status' => 'aktif',
        ]);

        $this->guardian->students()->attach($this->student->id, [
            'relationship' => 'ayah',
            'is_primary' => true,
        ]);

        // Create other parent and student (for authorization tests)
        $this->otherParent = User::factory()->create([
            'role' => 'PARENT',
        ]);

        $this->otherGuardian = Guardian::factory()->create([
            'user_id' => $this->otherParent->id,
        ]);

        $this->otherStudent = Student::factory()->create([
            'kelas_id' => $this->class->id,
            'status' => 'aktif',
        ]);

        $this->otherGuardian->students()->attach($this->otherStudent->id, [
            'relationship' => 'ayah',
            'is_primary' => true,
        ]);
    }

    // ============================================================
    // GRADES TESTS
    // ============================================================

    /**
     * Test parent dapat melihat nilai anak sendiri
     */
    public function test_parent_can_view_own_child_grades(): void
    {
        $this->createCompleteGradesForStudent($this->student);

        $response = $this->actingAs($this->parent)->get("/parent/children/{$this->student->id}/grades");

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page
            ->component('Parent/Children/Grades')
            ->has('student')
            ->has('gradeSummary')
            ->has('filters')
        );
    }

    /**
     * Test parent tidak bisa melihat nilai anak orang lain
     */
    public function test_parent_cannot_view_other_child_grades(): void
    {
        $response = $this->actingAs($this->parent)->get("/parent/children/{$this->otherStudent->id}/grades");

        $response->assertForbidden();
    }

    // ============================================================
    // REPORT CARDS LIST TESTS
    // ============================================================

    /**
     * Test parent dapat melihat list rapor anak
     */
    public function test_parent_can_view_report_cards_list(): void
    {
        // Create released report card
        $reportCard = $this->createReportCardWithStatus($this->student, ReportCard::STATUS_RELEASED);

        $response = $this->actingAs($this->parent)->get("/parent/children/{$this->student->id}/report-cards");

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page
            ->component('Parent/Children/ReportCards/Index')
            ->has('student')
            ->has('reportCards')
        );
    }

    /**
     * Test parent hanya melihat rapor yang sudah RELEASED
     */
    public function test_parent_only_sees_released_report_cards(): void
    {
        // Create draft report card
        $draftReportCard = $this->createReportCardWithStatus($this->student, ReportCard::STATUS_DRAFT);

        // Create released report card
        $releasedReportCard = $this->createReportCardWithStatus($this->student, ReportCard::STATUS_RELEASED);

        $response = $this->actingAs($this->parent)->get("/parent/children/{$this->student->id}/report-cards");

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page
            ->component('Parent/Children/ReportCards/Index')
            ->where('reportCards', fn ($cards) => count($cards) === 1
                && $cards[0]['id'] === $releasedReportCard->id
            )
        );
    }

    /**
     * Test parent tidak bisa melihat rapor anak orang lain
     */
    public function test_parent_cannot_view_other_child_report_cards_list(): void
    {
        $response = $this->actingAs($this->parent)->get("/parent/children/{$this->otherStudent->id}/report-cards");

        $response->assertForbidden();
    }

    // ============================================================
    // REPORT CARD SHOW TESTS
    // ============================================================

    /**
     * Test parent dapat melihat detail rapor anak (RELEASED)
     */
    public function test_parent_can_view_released_report_card(): void
    {
        $reportCard = $this->createReportCardWithStatus($this->student, ReportCard::STATUS_RELEASED);

        $response = $this->actingAs($this->parent)->get("/parent/children/{$this->student->id}/report-cards/{$reportCard->id}");

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page
            ->component('Parent/Children/ReportCards/Show')
            ->has('reportCard')
            ->has('student')
        );
    }

    /**
     * Test parent tidak bisa melihat rapor yang belum RELEASED
     */
    public function test_parent_cannot_view_non_released_report_card(): void
    {
        $reportCard = $this->createReportCardWithStatus($this->student, ReportCard::STATUS_PENDING_APPROVAL);

        $response = $this->actingAs($this->parent)->get("/parent/children/{$this->student->id}/report-cards/{$reportCard->id}");

        $response->assertRedirect();
        $response->assertSessionHas('error');
    }

    /**
     * Test parent tidak bisa melihat rapor anak orang lain
     */
    public function test_parent_cannot_view_other_child_report_card(): void
    {
        $reportCard = $this->createReportCardWithStatus($this->otherStudent, ReportCard::STATUS_RELEASED);

        $response = $this->actingAs($this->parent)->get("/parent/children/{$this->otherStudent->id}/report-cards/{$reportCard->id}");

        $response->assertForbidden();
    }

    /**
     * Test parent tidak bisa akses rapor milik siswa lain meskipun path student benar
     */
    public function test_parent_cannot_access_mismatched_report_card(): void
    {
        $reportCard = $this->createReportCardWithStatus($this->otherStudent, ReportCard::STATUS_RELEASED);

        // Mencoba akses rapor siswa lain melalui path anak sendiri
        $response = $this->actingAs($this->parent)->get("/parent/children/{$this->student->id}/report-cards/{$reportCard->id}");

        $response->assertForbidden();
    }

    // ============================================================
    // DOWNLOAD TESTS
    // ============================================================

    /**
     * Test parent dapat download PDF rapor (RELEASED)
     */
    public function test_parent_can_download_released_report_card_pdf(): void
    {
        // Create fake PDF file
        $pdfPath = "report-cards/{$this->tahunAjaran}/{$this->semester}/{$this->class->id}/{$this->student->nis}.pdf";
        Storage::put($pdfPath, 'fake pdf content');

        $reportCard = ReportCard::factory()->create([
            'student_id' => $this->student->id,
            'class_id' => $this->class->id,
            'tahun_ajaran' => $this->tahunAjaran,
            'semester' => $this->semester,
            'status' => ReportCard::STATUS_RELEASED,
            'pdf_path' => $pdfPath,
        ]);

        $response = $this->actingAs($this->parent)->get("/parent/children/{$this->student->id}/report-cards/{$reportCard->id}/download");

        $response->assertStatus(200);
        $response->assertHeader('content-type', 'application/pdf');
    }

    /**
     * Test parent tidak bisa download rapor yang belum RELEASED
     */
    public function test_parent_cannot_download_non_released_report_card(): void
    {
        $reportCard = $this->createReportCardWithStatus($this->student, ReportCard::STATUS_PENDING_APPROVAL);

        $response = $this->actingAs($this->parent)->get("/parent/children/{$this->student->id}/report-cards/{$reportCard->id}/download");

        $response->assertRedirect();
        $response->assertSessionHas('error');
    }

    // ============================================================
    // AUTHORIZATION TESTS
    // ============================================================

    /**
     * Test non-parent tidak bisa akses halaman grades
     */
    public function test_non_parent_cannot_access_grades(): void
    {
        $teacher = User::factory()->create(['role' => 'TEACHER']);

        $response = $this->actingAs($teacher)->get("/parent/children/{$this->student->id}/grades");

        $response->assertForbidden();
    }

    /**
     * Test non-parent tidak bisa akses halaman report cards
     */
    public function test_non_parent_cannot_access_report_cards(): void
    {
        $admin = User::factory()->create(['role' => 'ADMIN']);

        $response = $this->actingAs($admin)->get("/parent/children/{$this->student->id}/report-cards");

        $response->assertForbidden();
    }

    // ============================================================
    // HELPER METHODS
    // ============================================================

    /**
     * Helper: Create report card with specific status
     */
    protected function createReportCardWithStatus(Student $student, string $status): ReportCard
    {
        $this->createCompleteGradesForStudent($student);

        return ReportCard::factory()->create([
            'student_id' => $student->id,
            'class_id' => $this->class->id,
            'tahun_ajaran' => $this->tahunAjaran,
            'semester' => $this->semester,
            'status' => $status,
            'average_score' => 85.5,
            'rank' => 1,
            'released_at' => $status === ReportCard::STATUS_RELEASED ? now() : null,
        ]);
    }

    /**
     * Helper: Create complete grades for student
     */
    protected function createCompleteGradesForStudent(Student $student): void
    {
        Grade::factory()->create([
            'student_id' => $student->id,
            'subject_id' => $this->subject->id,
            'class_id' => $this->class->id,
            'teacher_id' => $this->teacher->id,
            'tahun_ajaran' => $this->tahunAjaran,
            'semester' => $this->semester,
            'assessment_type' => Grade::ASSESSMENT_UH,
            'score' => 85,
        ]);

        Grade::factory()->create([
            'student_id' => $student->id,
            'subject_id' => $this->subject->id,
            'class_id' => $this->class->id,
            'teacher_id' => $this->teacher->id,
            'tahun_ajaran' => $this->tahunAjaran,
            'semester' => $this->semester,
            'assessment_type' => Grade::ASSESSMENT_UTS,
            'score' => 80,
        ]);

        Grade::factory()->create([
            'student_id' => $student->id,
            'subject_id' => $this->subject->id,
            'class_id' => $this->class->id,
            'teacher_id' => $this->teacher->id,
            'tahun_ajaran' => $this->tahunAjaran,
            'semester' => $this->semester,
            'assessment_type' => Grade::ASSESSMENT_UAS,
            'score' => 82,
        ]);

        AttitudeGrade::factory()->create([
            'student_id' => $student->id,
            'class_id' => $this->class->id,
            'teacher_id' => $this->teacher->id,
            'tahun_ajaran' => $this->tahunAjaran,
            'semester' => $this->semester,
        ]);
    }
}
