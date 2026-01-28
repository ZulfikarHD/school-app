<?php

namespace Tests\Feature\Psb;

use App\Models\AcademicYear;
use App\Models\PsbSetting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Feature tests untuk Admin PSB Settings Controller
 * Testing CRUD settings functionality
 */
class PsbSettingsTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected User $teacher;

    protected AcademicYear $academicYear;

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
    }

    // ============================================================
    // AUTHORIZATION TESTS
    // ============================================================

    /**
     * Test non-admin tidak dapat mengakses settings routes
     */
    public function test_non_admin_cannot_access_settings_routes(): void
    {
        $response = $this->actingAs($this->teacher)->get('/admin/psb/settings');
        $response->assertStatus(403);
    }

    /**
     * Test guest tidak dapat mengakses settings routes
     */
    public function test_guest_cannot_access_settings_routes(): void
    {
        $response = $this->get('/admin/psb/settings');
        $response->assertRedirect('/login');
    }

    // ============================================================
    // INDEX TESTS
    // ============================================================

    /**
     * Test admin dapat mengakses halaman settings
     */
    public function test_admin_can_access_settings_index(): void
    {
        $response = $this->actingAs($this->admin)->get('/admin/psb/settings');

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page
            ->component('Admin/Psb/Settings/Index')
            ->has('academicYears')
            ->has('settings')
        );
    }

    /**
     * Test index menampilkan settings yang ada
     */
    public function test_index_shows_existing_settings(): void
    {
        // Create a PSB setting
        PsbSetting::factory()->create([
            'academic_year_id' => $this->academicYear->id,
        ]);

        $response = $this->actingAs($this->admin)->get('/admin/psb/settings');

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page
            ->where('settings', fn ($settings) => count($settings) === 1)
        );
    }

    // ============================================================
    // STORE TESTS
    // ============================================================

    /**
     * Test admin dapat membuat settings baru
     */
    public function test_admin_can_create_new_settings(): void
    {
        $settingsData = [
            'academic_year_id' => $this->academicYear->id,
            'registration_open_date' => '2026-01-01',
            'registration_close_date' => '2026-03-31',
            'announcement_date' => '2026-04-15',
            're_registration_deadline_days' => 14,
            'registration_fee' => 2500000,
            'quota_per_class' => 30,
            'waiting_list_enabled' => true,
        ];

        $response = $this->actingAs($this->admin)->post('/admin/psb/settings', $settingsData);

        $response->assertRedirect('/admin/psb/settings');
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('psb_settings', [
            'academic_year_id' => $this->academicYear->id,
            'registration_fee' => 2500000,
            'quota_per_class' => 30,
        ]);
    }

    /**
     * Test tidak dapat membuat settings duplikat untuk tahun ajaran yang sama
     */
    public function test_cannot_create_duplicate_settings_for_same_academic_year(): void
    {
        // Create existing setting
        PsbSetting::factory()->create([
            'academic_year_id' => $this->academicYear->id,
        ]);

        $settingsData = [
            'academic_year_id' => $this->academicYear->id,
            'registration_open_date' => '2026-01-01',
            'registration_close_date' => '2026-03-31',
            'announcement_date' => '2026-04-15',
            're_registration_deadline_days' => 14,
            'registration_fee' => 2500000,
            'quota_per_class' => 30,
            'waiting_list_enabled' => false,
        ];

        $response = $this->actingAs($this->admin)->post('/admin/psb/settings', $settingsData);

        $response->assertRedirect();
        $response->assertSessionHas('error');

        // Should still only have 1 setting
        $this->assertEquals(1, PsbSetting::where('academic_year_id', $this->academicYear->id)->count());
    }

    /**
     * Test validation: registration_close_date harus setelah registration_open_date
     */
    public function test_close_date_must_be_after_open_date(): void
    {
        $settingsData = [
            'academic_year_id' => $this->academicYear->id,
            'registration_open_date' => '2026-03-31',
            'registration_close_date' => '2026-01-01', // Before open date
            'announcement_date' => '2026-04-15',
            're_registration_deadline_days' => 14,
            'registration_fee' => 2500000,
            'quota_per_class' => 30,
        ];

        $response = $this->actingAs($this->admin)->post('/admin/psb/settings', $settingsData);

        $response->assertSessionHasErrors('registration_close_date');
    }

    /**
     * Test validation: announcement_date harus setelah registration_close_date
     */
    public function test_announcement_date_must_be_after_close_date(): void
    {
        $settingsData = [
            'academic_year_id' => $this->academicYear->id,
            'registration_open_date' => '2026-01-01',
            'registration_close_date' => '2026-03-31',
            'announcement_date' => '2026-02-15', // Before close date
            're_registration_deadline_days' => 14,
            'registration_fee' => 2500000,
            'quota_per_class' => 30,
        ];

        $response = $this->actingAs($this->admin)->post('/admin/psb/settings', $settingsData);

        $response->assertSessionHasErrors('announcement_date');
    }

    /**
     * Test validation: re_registration_deadline_days harus dalam range 7-30
     */
    public function test_re_registration_deadline_must_be_in_valid_range(): void
    {
        $settingsData = [
            'academic_year_id' => $this->academicYear->id,
            'registration_open_date' => '2026-01-01',
            'registration_close_date' => '2026-03-31',
            'announcement_date' => '2026-04-15',
            're_registration_deadline_days' => 5, // Below minimum 7
            'registration_fee' => 2500000,
            'quota_per_class' => 30,
        ];

        $response = $this->actingAs($this->admin)->post('/admin/psb/settings', $settingsData);

        $response->assertSessionHasErrors('re_registration_deadline_days');
    }

    /**
     * Test validation: required fields
     */
    public function test_required_fields_validation(): void
    {
        $response = $this->actingAs($this->admin)->post('/admin/psb/settings', []);

        $response->assertSessionHasErrors([
            'academic_year_id',
            'registration_open_date',
            'registration_close_date',
            'announcement_date',
            're_registration_deadline_days',
            'registration_fee',
            'quota_per_class',
        ]);
    }

    // ============================================================
    // UPDATE TESTS
    // ============================================================

    /**
     * Test admin dapat update settings yang ada
     */
    public function test_admin_can_update_existing_settings(): void
    {
        $setting = PsbSetting::factory()->create([
            'academic_year_id' => $this->academicYear->id,
            'registration_fee' => 2000000,
            'quota_per_class' => 25,
        ]);

        $updateData = [
            'academic_year_id' => $this->academicYear->id,
            'registration_open_date' => '2026-01-15',
            'registration_close_date' => '2026-04-15',
            'announcement_date' => '2026-05-01',
            're_registration_deadline_days' => 21,
            'registration_fee' => 3000000,
            'quota_per_class' => 35,
            'waiting_list_enabled' => true,
        ];

        $response = $this->actingAs($this->admin)->put("/admin/psb/settings/{$setting->id}", $updateData);

        $response->assertRedirect('/admin/psb/settings');
        $response->assertSessionHas('success');

        $setting->refresh();
        $this->assertEquals(3000000, $setting->registration_fee);
        $this->assertEquals(35, $setting->quota_per_class);
        $this->assertEquals(21, $setting->re_registration_deadline_days);
    }

    /**
     * Test tidak dapat update ke tahun ajaran yang sudah ada setting-nya
     */
    public function test_cannot_update_to_existing_academic_year(): void
    {
        $anotherYear = AcademicYear::factory()->create(['is_active' => false]);

        // Create settings for both years
        $setting1 = PsbSetting::factory()->create([
            'academic_year_id' => $this->academicYear->id,
        ]);
        PsbSetting::factory()->create([
            'academic_year_id' => $anotherYear->id,
        ]);

        // Try to update setting1 to use anotherYear (which already has a setting)
        $updateData = [
            'academic_year_id' => $anotherYear->id,
            'registration_open_date' => '2026-01-01',
            'registration_close_date' => '2026-03-31',
            'announcement_date' => '2026-04-15',
            're_registration_deadline_days' => 14,
            'registration_fee' => 2500000,
            'quota_per_class' => 30,
        ];

        $response = $this->actingAs($this->admin)->put("/admin/psb/settings/{$setting1->id}", $updateData);

        $response->assertRedirect();
        $response->assertSessionHas('error');

        // Setting should still be for original year
        $setting1->refresh();
        $this->assertEquals($this->academicYear->id, $setting1->academic_year_id);
    }

    /**
     * Test non-admin tidak dapat update settings
     */
    public function test_non_admin_cannot_update_settings(): void
    {
        $setting = PsbSetting::factory()->create([
            'academic_year_id' => $this->academicYear->id,
        ]);

        $updateData = [
            'academic_year_id' => $this->academicYear->id,
            'registration_open_date' => '2026-01-01',
            'registration_close_date' => '2026-03-31',
            'announcement_date' => '2026-04-15',
            're_registration_deadline_days' => 14,
            'registration_fee' => 2500000,
            'quota_per_class' => 30,
        ];

        $response = $this->actingAs($this->teacher)->put("/admin/psb/settings/{$setting->id}", $updateData);

        $response->assertStatus(403);
    }
}
