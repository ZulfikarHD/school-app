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
 * Feature tests untuk Principal Report Card Approval
 * Testing approval, rejection, dan bulk approve rapor oleh kepala sekolah
 */
class PrincipalApprovalTest extends TestCase
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

    /**
     * Test principal dapat mengakses halaman approval rapor
     */
    public function test_principal_can_access_report_cards_index(): void
    {
        $response = $this->actingAs($this->principal)->get('/principal/report-cards');

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page
            ->component('Principal/ReportCards/Index')
            ->has('reportCards')
            ->has('filters')
            ->has('statistics')
            ->has('pendingByClass')
        );
    }

    /**
     * Test principal dapat melihat preview rapor
     */
    public function test_principal_can_view_report_card_show(): void
    {
        $reportCard = $this->createReportCardWithStatus(ReportCard::STATUS_PENDING_APPROVAL);

        $response = $this->actingAs($this->principal)->get("/principal/report-cards/{$reportCard->id}");

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page
            ->component('Principal/ReportCards/Show')
            ->has('reportCard')
            ->has('classmateReportCards')
        );
    }

    /**
     * Test principal dapat approve rapor
     */
    public function test_principal_can_approve_report_card(): void
    {
        $reportCard = $this->createReportCardWithStatus(ReportCard::STATUS_PENDING_APPROVAL);

        $response = $this->actingAs($this->principal)->post("/principal/report-cards/{$reportCard->id}/approve");

        $response->assertRedirect();

        $reportCard->refresh();
        $this->assertEquals(ReportCard::STATUS_RELEASED, $reportCard->status);
        $this->assertNotNull($reportCard->approved_at);
        $this->assertEquals($this->principal->id, $reportCard->approved_by);
        $this->assertNotNull($reportCard->released_at);
    }

    /**
     * Test principal dapat reject rapor dengan notes
     */
    public function test_principal_can_reject_report_card(): void
    {
        $reportCard = $this->createReportCardWithStatus(ReportCard::STATUS_PENDING_APPROVAL);

        $response = $this->actingAs($this->principal)->post("/principal/report-cards/{$reportCard->id}/reject", [
            'notes' => 'Nilai sikap belum lengkap, mohon dilengkapi.',
        ]);

        $response->assertRedirect();

        $reportCard->refresh();
        $this->assertEquals(ReportCard::STATUS_DRAFT, $reportCard->status);
        $this->assertEquals('Nilai sikap belum lengkap, mohon dilengkapi.', $reportCard->approval_notes);
    }

    /**
     * Test reject harus menyertakan notes
     */
    public function test_reject_requires_notes(): void
    {
        $reportCard = $this->createReportCardWithStatus(ReportCard::STATUS_PENDING_APPROVAL);

        $response = $this->actingAs($this->principal)->post("/principal/report-cards/{$reportCard->id}/reject", [
            'notes' => '',
        ]);

        $response->assertSessionHasErrors('notes');
    }

    /**
     * Test tidak dapat approve rapor yang bukan PENDING_APPROVAL
     */
    public function test_cannot_approve_non_pending_report_card(): void
    {
        $reportCard = $this->createReportCardWithStatus(ReportCard::STATUS_DRAFT);

        $response = $this->actingAs($this->principal)->post("/principal/report-cards/{$reportCard->id}/approve");

        $response->assertRedirect();
        $response->assertSessionHas('error');

        $reportCard->refresh();
        $this->assertEquals(ReportCard::STATUS_DRAFT, $reportCard->status);
    }

    /**
     * Test principal dapat bulk approve per kelas
     */
    public function test_principal_can_bulk_approve(): void
    {
        // Create multiple report cards with PENDING_APPROVAL
        $reportCards = collect();
        for ($i = 0; $i < 3; $i++) {
            $student = Student::factory()->create([
                'kelas_id' => $this->class->id,
                'status' => 'aktif',
            ]);

            $reportCards->push(ReportCard::factory()->create([
                'student_id' => $student->id,
                'class_id' => $this->class->id,
                'tahun_ajaran' => $this->tahunAjaran,
                'semester' => $this->semester,
                'status' => ReportCard::STATUS_PENDING_APPROVAL,
            ]));
        }

        $response = $this->actingAs($this->principal)->post('/principal/report-cards/bulk-approve', [
            'class_id' => $this->class->id,
            'tahun_ajaran' => $this->tahunAjaran,
            'semester' => $this->semester,
        ]);

        $response->assertRedirect();

        // Verify all report cards are released
        foreach ($reportCards as $reportCard) {
            $reportCard->refresh();
            $this->assertEquals(ReportCard::STATUS_RELEASED, $reportCard->status);
        }
    }

    /**
     * Test bulk approve returns error jika tidak ada pending
     */
    public function test_bulk_approve_returns_error_if_no_pending(): void
    {
        $response = $this->actingAs($this->principal)->post('/principal/report-cards/bulk-approve', [
            'class_id' => $this->class->id,
            'tahun_ajaran' => $this->tahunAjaran,
            'semester' => $this->semester,
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('error');
    }

    /**
     * Test non-principal tidak dapat approve rapor
     */
    public function test_non_principal_cannot_approve(): void
    {
        $reportCard = $this->createReportCardWithStatus(ReportCard::STATUS_PENDING_APPROVAL);

        // Admin tidak bisa approve
        $response = $this->actingAs($this->admin)->post("/principal/report-cards/{$reportCard->id}/approve");
        $response->assertForbidden();

        // Teacher tidak bisa approve
        $response = $this->actingAs($this->teacher)->post("/principal/report-cards/{$reportCard->id}/approve");
        $response->assertForbidden();
    }

    /**
     * Helper: Create report card with specific status
     */
    protected function createReportCardWithStatus(string $status): ReportCard
    {
        $this->createCompleteGradesForStudent();

        return ReportCard::factory()->create([
            'student_id' => $this->student->id,
            'class_id' => $this->class->id,
            'tahun_ajaran' => $this->tahunAjaran,
            'semester' => $this->semester,
            'status' => $status,
            'average_score' => 85.5,
            'rank' => 1,
        ]);
    }

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
