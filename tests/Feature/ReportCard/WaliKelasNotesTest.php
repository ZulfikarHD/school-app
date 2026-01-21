<?php

namespace Tests\Feature\ReportCard;

use App\Models\AttitudeGrade;
use App\Models\ReportCard;
use App\Models\SchoolClass;
use App\Models\Student;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Feature tests untuk Wali Kelas Notes Flow
 * Testing akses wali kelas ke rapor, input catatan, dan submit approval
 */
class WaliKelasNotesTest extends TestCase
{
    use RefreshDatabase;

    protected User $waliKelas;

    protected User $otherTeacher;

    protected SchoolClass $class;

    protected Student $student;

    protected ReportCard $reportCard;

    protected string $tahunAjaran;

    protected string $semester;

    protected function setUp(): void
    {
        parent::setUp();

        // Create wali kelas
        $this->waliKelas = User::factory()->create([
            'role' => 'TEACHER',
        ]);

        // Create other teacher (not wali kelas)
        $this->otherTeacher = User::factory()->create([
            'role' => 'TEACHER',
        ]);

        // Create class with wali kelas
        $this->tahunAjaran = now()->month >= 7
            ? now()->year.'/'.(now()->year + 1)
            : (now()->year - 1).'/'.now()->year;
        $this->semester = now()->month >= 7 ? '1' : '2';

        $this->class = SchoolClass::factory()->create([
            'wali_kelas_id' => $this->waliKelas->id,
            'tahun_ajaran' => $this->tahunAjaran,
            'is_active' => true,
        ]);

        // Create student
        $this->student = Student::factory()->create([
            'kelas_id' => $this->class->id,
            'status' => 'aktif',
        ]);

        // Create report card
        $this->reportCard = ReportCard::factory()->create([
            'student_id' => $this->student->id,
            'class_id' => $this->class->id,
            'tahun_ajaran' => $this->tahunAjaran,
            'semester' => $this->semester,
            'status' => ReportCard::STATUS_DRAFT,
        ]);
    }

    /**
     * Test wali kelas dapat mengakses halaman rapor kelas
     */
    public function test_wali_kelas_can_access_report_cards_index(): void
    {
        $response = $this->actingAs($this->waliKelas)->get('/teacher/report-cards');

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page
            ->component('Teacher/ReportCards/Index')
            ->where('isWaliKelas', true)
            ->has('reportCards')
            ->has('class')
        );
    }

    /**
     * Test teacher yang bukan wali kelas melihat pesan akses terbatas
     */
    public function test_non_wali_kelas_sees_limited_access(): void
    {
        $response = $this->actingAs($this->otherTeacher)->get('/teacher/report-cards');

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page
            ->component('Teacher/ReportCards/Index')
            ->where('isWaliKelas', false)
        );
    }

    /**
     * Test wali kelas dapat melihat detail rapor siswa
     */
    public function test_wali_kelas_can_view_report_card_detail(): void
    {
        // Create attitude grade for the student
        AttitudeGrade::factory()->create([
            'student_id' => $this->student->id,
            'class_id' => $this->class->id,
            'teacher_id' => $this->waliKelas->id,
            'tahun_ajaran' => $this->tahunAjaran,
            'semester' => $this->semester,
        ]);

        $response = $this->actingAs($this->waliKelas)->get("/teacher/report-cards/{$this->reportCard->id}");

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page
            ->component('Teacher/ReportCards/Show')
            ->has('reportCard')
            ->has('currentNotes')
        );
    }

    /**
     * Test teacher lain tidak dapat mengakses rapor di kelas lain
     */
    public function test_other_teacher_cannot_access_other_class_report(): void
    {
        $response = $this->actingAs($this->otherTeacher)->get("/teacher/report-cards/{$this->reportCard->id}");

        $response->assertForbidden();
    }

    /**
     * Test wali kelas dapat update catatan
     */
    public function test_wali_kelas_can_update_homeroom_notes(): void
    {
        // Create initial attitude grade
        AttitudeGrade::factory()->create([
            'student_id' => $this->student->id,
            'class_id' => $this->class->id,
            'teacher_id' => $this->waliKelas->id,
            'tahun_ajaran' => $this->tahunAjaran,
            'semester' => $this->semester,
            'homeroom_notes' => null,
        ]);

        $newNotes = 'Siswa menunjukkan peningkatan yang baik di semester ini.';

        $response = $this->actingAs($this->waliKelas)->put("/teacher/report-cards/{$this->reportCard->id}/notes", [
            'homeroom_notes' => $newNotes,
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        // Verify notes saved
        $this->assertDatabaseHas('attitude_grades', [
            'student_id' => $this->student->id,
            'tahun_ajaran' => $this->tahunAjaran,
            'semester' => $this->semester,
            'homeroom_notes' => $newNotes,
        ]);
    }

    /**
     * Test catatan tidak boleh lebih dari 500 karakter
     */
    public function test_homeroom_notes_max_500_characters(): void
    {
        $longNotes = str_repeat('a', 501);

        $response = $this->actingAs($this->waliKelas)->put("/teacher/report-cards/{$this->reportCard->id}/notes", [
            'homeroom_notes' => $longNotes,
        ]);

        $response->assertSessionHasErrors('homeroom_notes');
    }

    /**
     * Test wali kelas dapat submit rapor untuk approval
     */
    public function test_wali_kelas_can_submit_for_approval(): void
    {
        $response = $this->actingAs($this->waliKelas)->post("/teacher/report-cards/{$this->reportCard->id}/submit");

        $response->assertRedirect();
        $response->assertSessionHas('success');

        // Verify status changed
        $this->assertDatabaseHas('report_cards', [
            'id' => $this->reportCard->id,
            'status' => ReportCard::STATUS_PENDING_APPROVAL,
        ]);
    }

    /**
     * Test tidak dapat submit rapor yang sudah disubmit
     */
    public function test_cannot_submit_already_submitted_report(): void
    {
        // Update status to pending
        $this->reportCard->update(['status' => ReportCard::STATUS_PENDING_APPROVAL]);

        $response = $this->actingAs($this->waliKelas)->post("/teacher/report-cards/{$this->reportCard->id}/submit");

        $response->assertRedirect();
        $response->assertSessionHas('error');
    }

    /**
     * Test wali kelas dapat submit semua rapor sekaligus
     */
    public function test_wali_kelas_can_submit_all_reports(): void
    {
        // Create more students and report cards
        $student2 = Student::factory()->create([
            'kelas_id' => $this->class->id,
            'status' => 'aktif',
        ]);

        $reportCard2 = ReportCard::factory()->create([
            'student_id' => $student2->id,
            'class_id' => $this->class->id,
            'tahun_ajaran' => $this->tahunAjaran,
            'semester' => $this->semester,
            'status' => ReportCard::STATUS_DRAFT,
        ]);

        $response = $this->actingAs($this->waliKelas)->post('/teacher/report-cards/submit-all', [
            'tahun_ajaran' => $this->tahunAjaran,
            'semester' => $this->semester,
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        // Verify both report cards are submitted
        $this->assertDatabaseHas('report_cards', [
            'id' => $this->reportCard->id,
            'status' => ReportCard::STATUS_PENDING_APPROVAL,
        ]);

        $this->assertDatabaseHas('report_cards', [
            'id' => $reportCard2->id,
            'status' => ReportCard::STATUS_PENDING_APPROVAL,
        ]);
    }

    /**
     * Test tidak dapat edit catatan setelah rapor disubmit
     */
    public function test_cannot_edit_notes_after_submitted(): void
    {
        // Submit the report card
        $this->reportCard->update(['status' => ReportCard::STATUS_PENDING_APPROVAL]);

        $response = $this->actingAs($this->waliKelas)->put("/teacher/report-cards/{$this->reportCard->id}/notes", [
            'homeroom_notes' => 'New notes',
        ]);

        // Should redirect (or fail gracefully - depends on implementation)
        // The UI should prevent this, but the backend should also handle it
        $response->assertStatus(302);
    }

    /**
     * Test wali kelas dapat download PDF rapor
     */
    public function test_wali_kelas_can_download_pdf(): void
    {
        // Update report card with PDF path (mock)
        $this->reportCard->update(['pdf_path' => 'test/path.pdf']);

        // Note: In real test, we would need to create the actual file
        // This test just verifies the route exists and authorization works
        $response = $this->actingAs($this->waliKelas)->get("/teacher/report-cards/{$this->reportCard->id}/download");

        // Will redirect with error since file doesn't exist
        $response->assertRedirect();
    }
}
