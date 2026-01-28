<?php

namespace Tests\Feature\Psb;

use App\Models\AcademicYear;
use App\Models\PsbRegistration;
use App\Models\PsbSetting;
use App\Models\User;
use App\Notifications\PsbAnnouncementNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

/**
 * Feature tests untuk Admin PSB Announcement Controller
 * Testing bulk announce functionality
 */
class AnnouncementTest extends TestCase
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
     * Test non-admin tidak dapat mengakses announcement routes
     */
    public function test_non_admin_cannot_access_announcement_routes(): void
    {
        $response = $this->actingAs($this->teacher)->get('/admin/psb/announcements');
        $response->assertStatus(403);
    }

    /**
     * Test guest tidak dapat mengakses announcement routes
     */
    public function test_guest_cannot_access_announcement_routes(): void
    {
        $response = $this->get('/admin/psb/announcements');
        $response->assertRedirect('/login');
    }

    // ============================================================
    // INDEX TESTS
    // ============================================================

    /**
     * Test admin dapat mengakses halaman announcement
     */
    public function test_admin_can_access_announcement_index(): void
    {
        $response = $this->actingAs($this->admin)->get('/admin/psb/announcements');

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page
            ->component('Admin/Psb/Announcements/Index')
            ->has('registrations')
            ->has('stats')
            ->has('filters')
        );
    }

    /**
     * Test index hanya menampilkan approved registrations
     */
    public function test_index_only_shows_approved_registrations(): void
    {
        // Create registrations dengan berbagai status
        PsbRegistration::factory()->count(3)->create([
            'academic_year_id' => $this->academicYear->id,
            'status' => PsbRegistration::STATUS_APPROVED,
        ]);
        PsbRegistration::factory()->count(2)->create([
            'academic_year_id' => $this->academicYear->id,
            'status' => PsbRegistration::STATUS_PENDING,
        ]);

        $response = $this->actingAs($this->admin)->get('/admin/psb/announcements');

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page
            ->where('registrations.data', fn ($data) => count($data) === 3)
        );
    }

    /**
     * Test filter by announced status works
     */
    public function test_filter_by_announced_status_works(): void
    {
        // Create announced and unannounced registrations
        PsbRegistration::factory()->count(2)->create([
            'academic_year_id' => $this->academicYear->id,
            'status' => PsbRegistration::STATUS_APPROVED,
            'announced_at' => now(),
        ]);
        PsbRegistration::factory()->count(3)->create([
            'academic_year_id' => $this->academicYear->id,
            'status' => PsbRegistration::STATUS_APPROVED,
            'announced_at' => null,
        ]);

        // Filter unannounced only
        $response = $this->actingAs($this->admin)->get('/admin/psb/announcements?announced=no');

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page
            ->where('registrations.data', fn ($data) => count($data) === 3)
        );
    }

    // ============================================================
    // BULK ANNOUNCE TESTS
    // ============================================================

    /**
     * Test admin dapat melakukan bulk announce
     */
    public function test_admin_can_bulk_announce(): void
    {
        Notification::fake();

        $registrations = PsbRegistration::factory()->count(3)->create([
            'academic_year_id' => $this->academicYear->id,
            'status' => PsbRegistration::STATUS_APPROVED,
            'announced_at' => null,
            'father_email' => 'father@example.com',
        ]);

        $response = $this->actingAs($this->admin)->post('/admin/psb/announcements/bulk-announce', [
            'registration_ids' => $registrations->pluck('id')->toArray(),
            'send_notification' => true,
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        // Verify all registrations have announced_at set
        foreach ($registrations as $registration) {
            $registration->refresh();
            $this->assertNotNull($registration->announced_at);
        }

        // Verify notifications sent
        Notification::assertSentOnDemand(
            PsbAnnouncementNotification::class,
            fn ($notification) => in_array($notification->registration->id, $registrations->pluck('id')->toArray())
        );
    }

    /**
     * Test tidak dapat announce registrations yang sudah announced
     */
    public function test_cannot_announce_already_announced_registrations(): void
    {
        $registrations = PsbRegistration::factory()->count(2)->create([
            'academic_year_id' => $this->academicYear->id,
            'status' => PsbRegistration::STATUS_APPROVED,
            'announced_at' => now(), // Already announced
        ]);

        $response = $this->actingAs($this->admin)->post('/admin/psb/announcements/bulk-announce', [
            'registration_ids' => $registrations->pluck('id')->toArray(),
            'send_notification' => true,
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('error');
    }

    /**
     * Test tidak dapat announce registrations dengan status bukan approved
     */
    public function test_cannot_announce_non_approved_registrations(): void
    {
        $registrations = PsbRegistration::factory()->count(2)->create([
            'academic_year_id' => $this->academicYear->id,
            'status' => PsbRegistration::STATUS_PENDING, // Not approved
        ]);

        $response = $this->actingAs($this->admin)->post('/admin/psb/announcements/bulk-announce', [
            'registration_ids' => $registrations->pluck('id')->toArray(),
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('error');
    }

    /**
     * Test bulk announce memerlukan minimal satu registration
     */
    public function test_bulk_announce_requires_at_least_one_registration(): void
    {
        $response = $this->actingAs($this->admin)->post('/admin/psb/announcements/bulk-announce', [
            'registration_ids' => [],
        ]);

        $response->assertSessionHasErrors('registration_ids');
    }

    /**
     * Test bulk announce dapat skip notification
     */
    public function test_bulk_announce_can_skip_notification(): void
    {
        Notification::fake();

        $registrations = PsbRegistration::factory()->count(2)->create([
            'academic_year_id' => $this->academicYear->id,
            'status' => PsbRegistration::STATUS_APPROVED,
            'announced_at' => null,
            'father_email' => 'father@example.com',
        ]);

        $response = $this->actingAs($this->admin)->post('/admin/psb/announcements/bulk-announce', [
            'registration_ids' => $registrations->pluck('id')->toArray(),
            'send_notification' => false,
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        // Notifications should not be sent
        Notification::assertNothingSent();
    }
}
