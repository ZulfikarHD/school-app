<?php

namespace Tests\Feature\Admin;

use App\Models\AcademicYear;
use App\Models\PsbDocument;
use App\Models\PsbRegistration;
use App\Models\PsbSetting;
use App\Models\User;
use App\Notifications\PsbDocumentRevisionRequested;
use App\Notifications\PsbRegistrationApproved;
use App\Notifications\PsbRegistrationRejected;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

/**
 * Feature tests untuk Admin PSB Controller
 * Testing dashboard, registrations list, detail, approve, reject, dan revision
 */
class AdminPsbControllerTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected User $teacher;

    protected AcademicYear $academicYear;

    protected PsbSetting $psbSetting;

    protected function setUp(): void
    {
        parent::setUp();

        // Create admin user
        $this->admin = User::factory()->create([
            'role' => 'ADMIN',
        ]);

        // Create teacher user (non-admin)
        $this->teacher = User::factory()->create([
            'role' => 'TEACHER',
        ]);

        // Create active academic year
        $this->academicYear = AcademicYear::factory()->create([
            'is_active' => true,
        ]);

        // Create PSB setting
        $this->psbSetting = PsbSetting::factory()->create([
            'academic_year_id' => $this->academicYear->id,
        ]);
    }

    // ============================================================
    // AUTHORIZATION TESTS
    // ============================================================

    /**
     * Test non-admin tidak dapat mengakses PSB routes
     */
    public function test_non_admin_cannot_access_psb_routes(): void
    {
        $response = $this->actingAs($this->teacher)->get('/admin/psb');
        $response->assertStatus(403);

        $response = $this->actingAs($this->teacher)->get('/admin/psb/registrations');
        $response->assertStatus(403);
    }

    /**
     * Test guest tidak dapat mengakses PSB routes
     */
    public function test_guest_cannot_access_psb_routes(): void
    {
        $response = $this->get('/admin/psb');
        $response->assertRedirect('/login');
    }

    // ============================================================
    // INDEX (DASHBOARD) TESTS
    // ============================================================

    /**
     * Test admin dapat mengakses dashboard PSB
     */
    public function test_admin_can_access_psb_dashboard(): void
    {
        $response = $this->actingAs($this->admin)->get('/admin/psb');

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page
            ->component('Admin/Psb/Index')
            ->has('stats')
            ->has('stats.total')
            ->has('stats.pending')
            ->has('stats.approved')
            ->has('stats.rejected')
        );
    }

    /**
     * Test dashboard menampilkan statistik yang benar
     */
    public function test_dashboard_shows_correct_stats(): void
    {
        // Create registrations dengan berbagai status
        PsbRegistration::factory()->count(3)->create([
            'academic_year_id' => $this->academicYear->id,
            'status' => PsbRegistration::STATUS_PENDING,
        ]);
        PsbRegistration::factory()->count(2)->create([
            'academic_year_id' => $this->academicYear->id,
            'status' => PsbRegistration::STATUS_APPROVED,
        ]);
        PsbRegistration::factory()->create([
            'academic_year_id' => $this->academicYear->id,
            'status' => PsbRegistration::STATUS_REJECTED,
        ]);

        $response = $this->actingAs($this->admin)->get('/admin/psb');

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page
            ->where('stats.total', 6)
            ->where('stats.pending', 3)
            ->where('stats.approved', 2)
            ->where('stats.rejected', 1)
        );
    }

    // ============================================================
    // REGISTRATIONS LIST TESTS
    // ============================================================

    /**
     * Test admin dapat mengakses halaman list registrations
     */
    public function test_admin_can_access_registrations_list(): void
    {
        $response = $this->actingAs($this->admin)->get('/admin/psb/registrations');

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page
            ->component('Admin/Psb/Registrations/Index')
            ->has('registrations')
            ->has('stats')
            ->has('filters')
            ->has('statuses')
        );
    }

    /**
     * Test filter by status berfungsi
     */
    public function test_filter_by_status_works(): void
    {
        PsbRegistration::factory()->count(3)->create([
            'academic_year_id' => $this->academicYear->id,
            'status' => PsbRegistration::STATUS_PENDING,
        ]);
        PsbRegistration::factory()->count(2)->create([
            'academic_year_id' => $this->academicYear->id,
            'status' => PsbRegistration::STATUS_APPROVED,
        ]);

        $response = $this->actingAs($this->admin)->get('/admin/psb/registrations?status=pending');

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page
            ->where('registrations.data', fn ($data) => count($data) === 3)
        );
    }

    /**
     * Test search berfungsi
     */
    public function test_search_by_name_works(): void
    {
        PsbRegistration::factory()->create([
            'academic_year_id' => $this->academicYear->id,
            'student_name' => 'Ahmad Hidayat',
        ]);
        PsbRegistration::factory()->create([
            'academic_year_id' => $this->academicYear->id,
            'student_name' => 'Budi Santoso',
        ]);

        $response = $this->actingAs($this->admin)->get('/admin/psb/registrations?search=Ahmad');

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page
            ->where('registrations.data', fn ($data) => count($data) === 1)
        );
    }

    // ============================================================
    // SHOW (DETAIL) TESTS
    // ============================================================

    /**
     * Test admin dapat melihat detail registration
     */
    public function test_admin_can_view_registration_detail(): void
    {
        $registration = PsbRegistration::factory()->create([
            'academic_year_id' => $this->academicYear->id,
        ]);

        // Create documents
        PsbDocument::factory()->count(3)->create([
            'psb_registration_id' => $registration->id,
        ]);

        $response = $this->actingAs($this->admin)->get("/admin/psb/registrations/{$registration->id}");

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page
            ->component('Admin/Psb/Registrations/Show')
            ->has('registration')
            ->has('documents')
            ->has('timeline')
            ->where('registration.id', $registration->id)
        );
    }

    // ============================================================
    // APPROVE TESTS
    // ============================================================

    /**
     * Test admin dapat approve registration
     */
    public function test_admin_can_approve_registration(): void
    {
        Notification::fake();

        $registration = PsbRegistration::factory()->create([
            'academic_year_id' => $this->academicYear->id,
            'status' => PsbRegistration::STATUS_PENDING,
            'father_email' => 'father@example.com',
        ]);

        $response = $this->actingAs($this->admin)->post("/admin/psb/registrations/{$registration->id}/approve", [
            'notes' => 'Selamat diterima!',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $registration->refresh();
        $this->assertEquals(PsbRegistration::STATUS_APPROVED, $registration->status);
        $this->assertEquals($this->admin->id, $registration->verified_by);
        $this->assertNotNull($registration->verified_at);

        Notification::assertSentOnDemand(PsbRegistrationApproved::class);
    }

    /**
     * Test tidak dapat approve registration yang sudah approved
     */
    public function test_cannot_approve_already_approved_registration(): void
    {
        $registration = PsbRegistration::factory()->create([
            'academic_year_id' => $this->academicYear->id,
            'status' => PsbRegistration::STATUS_APPROVED,
        ]);

        $response = $this->actingAs($this->admin)->post("/admin/psb/registrations/{$registration->id}/approve");

        $response->assertRedirect();
        $response->assertSessionHas('error');
    }

    // ============================================================
    // REJECT TESTS
    // ============================================================

    /**
     * Test admin dapat reject registration
     */
    public function test_admin_can_reject_registration(): void
    {
        Notification::fake();

        $registration = PsbRegistration::factory()->create([
            'academic_year_id' => $this->academicYear->id,
            'status' => PsbRegistration::STATUS_PENDING,
            'father_email' => 'father@example.com',
        ]);

        $response = $this->actingAs($this->admin)->post("/admin/psb/registrations/{$registration->id}/reject", [
            'rejection_reason' => 'Kuota sudah penuh untuk tahun ini.',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $registration->refresh();
        $this->assertEquals(PsbRegistration::STATUS_REJECTED, $registration->status);
        $this->assertEquals('Kuota sudah penuh untuk tahun ini.', $registration->rejection_reason);
        $this->assertEquals($this->admin->id, $registration->verified_by);

        Notification::assertSentOnDemand(PsbRegistrationRejected::class);
    }

    /**
     * Test reject memerlukan alasan
     */
    public function test_reject_requires_reason(): void
    {
        $registration = PsbRegistration::factory()->create([
            'academic_year_id' => $this->academicYear->id,
            'status' => PsbRegistration::STATUS_PENDING,
        ]);

        $response = $this->actingAs($this->admin)->post("/admin/psb/registrations/{$registration->id}/reject", [
            'rejection_reason' => '',
        ]);

        $response->assertSessionHasErrors('rejection_reason');
    }

    // ============================================================
    // REVISION TESTS
    // ============================================================

    /**
     * Test admin dapat request revision dokumen
     */
    public function test_admin_can_request_document_revision(): void
    {
        Notification::fake();

        $registration = PsbRegistration::factory()->create([
            'academic_year_id' => $this->academicYear->id,
            'status' => PsbRegistration::STATUS_PENDING,
            'father_email' => 'father@example.com',
        ]);

        $document = PsbDocument::factory()->create([
            'psb_registration_id' => $registration->id,
            'status' => PsbDocument::STATUS_PENDING,
        ]);

        $response = $this->actingAs($this->admin)->post("/admin/psb/registrations/{$registration->id}/revision", [
            'documents' => [
                [
                    'id' => $document->id,
                    'revision_note' => 'Gambar tidak jelas, mohon upload ulang.',
                ],
            ],
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $registration->refresh();
        $document->refresh();

        $this->assertEquals(PsbRegistration::STATUS_DOCUMENT_REVIEW, $registration->status);
        $this->assertEquals(PsbDocument::STATUS_REJECTED, $document->status);
        $this->assertEquals('Gambar tidak jelas, mohon upload ulang.', $document->revision_note);

        Notification::assertSentOnDemand(PsbDocumentRevisionRequested::class);
    }

    /**
     * Test revision memerlukan minimal satu dokumen
     */
    public function test_revision_requires_at_least_one_document(): void
    {
        $registration = PsbRegistration::factory()->create([
            'academic_year_id' => $this->academicYear->id,
            'status' => PsbRegistration::STATUS_PENDING,
        ]);

        $response = $this->actingAs($this->admin)->post("/admin/psb/registrations/{$registration->id}/revision", [
            'documents' => [],
        ]);

        $response->assertSessionHasErrors('documents');
    }

    /**
     * Test revision note wajib diisi untuk setiap dokumen
     */
    public function test_revision_requires_note_for_each_document(): void
    {
        $registration = PsbRegistration::factory()->create([
            'academic_year_id' => $this->academicYear->id,
            'status' => PsbRegistration::STATUS_PENDING,
        ]);

        $document = PsbDocument::factory()->create([
            'psb_registration_id' => $registration->id,
        ]);

        $response = $this->actingAs($this->admin)->post("/admin/psb/registrations/{$registration->id}/revision", [
            'documents' => [
                [
                    'id' => $document->id,
                    'revision_note' => '',
                ],
            ],
        ]);

        $response->assertSessionHasErrors('documents.0.revision_note');
    }
}
