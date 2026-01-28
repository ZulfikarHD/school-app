<?php

namespace Tests\Feature\Psb;

use App\Models\AcademicYear;
use App\Models\PsbRegistration;
use App\Models\PsbSetting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Feature tests untuk Admin PSB Export Controller
 * Testing export functionality dengan filters
 */
class PsbExportTest extends TestCase
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
     * Test non-admin tidak dapat mengakses export route
     */
    public function test_non_admin_cannot_access_export_route(): void
    {
        $response = $this->actingAs($this->teacher)->get('/admin/psb/export');
        $response->assertStatus(403);
    }

    /**
     * Test guest tidak dapat mengakses export route
     */
    public function test_guest_cannot_access_export_route(): void
    {
        $response = $this->get('/admin/psb/export');
        $response->assertRedirect('/login');
    }

    // ============================================================
    // EXPORT TESTS
    // ============================================================

    /**
     * Test admin dapat export registrations
     */
    public function test_admin_can_export_registrations(): void
    {
        // Create some registrations
        PsbRegistration::factory()->count(5)->create([
            'academic_year_id' => $this->academicYear->id,
        ]);

        $response = $this->actingAs($this->admin)->get('/admin/psb/export');

        $response->assertStatus(200);
        $response->assertHeader('content-type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        $this->assertStringContainsString('psb_registrations_', $response->headers->get('content-disposition'));
        $this->assertStringContainsString('.xlsx', $response->headers->get('content-disposition'));
    }

    /**
     * Test export dengan filter status
     */
    public function test_export_with_status_filter(): void
    {
        // Create registrations with different statuses
        PsbRegistration::factory()->count(3)->create([
            'academic_year_id' => $this->academicYear->id,
            'status' => PsbRegistration::STATUS_PENDING,
        ]);
        PsbRegistration::factory()->count(2)->create([
            'academic_year_id' => $this->academicYear->id,
            'status' => PsbRegistration::STATUS_APPROVED,
        ]);

        $response = $this->actingAs($this->admin)->get('/admin/psb/export?status=pending');

        $response->assertStatus(200);
        $response->assertHeader('content-type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    }

    /**
     * Test export dengan filter date range
     */
    public function test_export_with_date_range_filter(): void
    {
        // Create registrations
        PsbRegistration::factory()->count(3)->create([
            'academic_year_id' => $this->academicYear->id,
            'created_at' => now()->subDays(5),
        ]);
        PsbRegistration::factory()->count(2)->create([
            'academic_year_id' => $this->academicYear->id,
            'created_at' => now()->subDays(30),
        ]);

        $startDate = now()->subDays(10)->format('Y-m-d');
        $endDate = now()->format('Y-m-d');

        $response = $this->actingAs($this->admin)->get("/admin/psb/export?start_date={$startDate}&end_date={$endDate}");

        $response->assertStatus(200);
        $response->assertHeader('content-type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    }

    /**
     * Test export dengan multiple filters
     */
    public function test_export_with_multiple_filters(): void
    {
        // Create registrations with different statuses and dates
        PsbRegistration::factory()->count(2)->create([
            'academic_year_id' => $this->academicYear->id,
            'status' => PsbRegistration::STATUS_APPROVED,
            'created_at' => now()->subDays(3),
        ]);
        PsbRegistration::factory()->count(3)->create([
            'academic_year_id' => $this->academicYear->id,
            'status' => PsbRegistration::STATUS_PENDING,
            'created_at' => now()->subDays(3),
        ]);

        $startDate = now()->subDays(7)->format('Y-m-d');
        $endDate = now()->format('Y-m-d');

        $response = $this->actingAs($this->admin)->get("/admin/psb/export?status=approved&start_date={$startDate}&end_date={$endDate}");

        $response->assertStatus(200);
        $response->assertHeader('content-type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    }

    /**
     * Test export empty result (no registrations)
     */
    public function test_export_empty_result(): void
    {
        // No registrations created
        $response = $this->actingAs($this->admin)->get('/admin/psb/export');

        $response->assertStatus(200);
        $response->assertHeader('content-type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    }

    /**
     * Test export filename contains timestamp
     */
    public function test_export_filename_contains_timestamp(): void
    {
        PsbRegistration::factory()->create([
            'academic_year_id' => $this->academicYear->id,
        ]);

        $response = $this->actingAs($this->admin)->get('/admin/psb/export');

        $response->assertStatus(200);
        $contentDisposition = $response->headers->get('content-disposition');

        // Verify filename pattern: psb_registrations_YYYY-MM-DD_HHiiss.xlsx
        $this->assertMatchesRegularExpression('/psb_registrations_\d{4}-\d{2}-\d{2}_\d{6}\.xlsx/', $contentDisposition);
    }
}
