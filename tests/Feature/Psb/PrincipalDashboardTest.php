<?php

namespace Tests\Feature\Psb;

use App\Models\AcademicYear;
use App\Models\PsbRegistration;
use App\Models\PsbSetting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Feature tests untuk Principal PSB Dashboard Controller
 * Testing dashboard view for principal
 */
class PrincipalDashboardTest extends TestCase
{
    use RefreshDatabase;

    protected User $principal;

    protected User $teacher;

    protected AcademicYear $academicYear;

    protected PsbSetting $psbSetting;

    protected function setUp(): void
    {
        parent::setUp();

        // Create principal user
        $this->principal = User::factory()->create([
            'role' => 'PRINCIPAL',
        ]);

        // Create teacher user (non-principal)
        $this->teacher = User::factory()->create([
            'role' => 'TEACHER',
        ]);

        // Create active academic year
        $this->academicYear = AcademicYear::factory()->create([
            'is_active' => true,
            'name' => '2025/2026',
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
     * Test non-principal tidak dapat mengakses PSB dashboard
     */
    public function test_non_principal_cannot_access_psb_dashboard(): void
    {
        $response = $this->actingAs($this->teacher)->get('/principal/psb');
        $response->assertStatus(403);
    }

    /**
     * Test guest tidak dapat mengakses PSB dashboard
     */
    public function test_guest_cannot_access_psb_dashboard(): void
    {
        $response = $this->get('/principal/psb');
        $response->assertRedirect('/login');
    }

    // ============================================================
    // DASHBOARD TESTS
    // ============================================================

    /**
     * Test principal dapat mengakses PSB dashboard
     */
    public function test_principal_can_access_psb_dashboard(): void
    {
        $response = $this->actingAs($this->principal)->get('/principal/psb');

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page
            ->component('Principal/Psb/Dashboard')
            ->has('summary')
            ->has('dailyRegistrations')
            ->has('genderDistribution')
            ->has('statusDistribution')
            ->has('periodInfo')
        );
    }

    /**
     * Test dashboard menampilkan summary stats yang benar
     */
    public function test_dashboard_shows_correct_summary_stats(): void
    {
        // Create registrations dengan berbagai status
        PsbRegistration::factory()->count(5)->create([
            'academic_year_id' => $this->academicYear->id,
            'status' => PsbRegistration::STATUS_PENDING,
        ]);
        PsbRegistration::factory()->count(3)->create([
            'academic_year_id' => $this->academicYear->id,
            'status' => PsbRegistration::STATUS_APPROVED,
        ]);
        PsbRegistration::factory()->count(2)->create([
            'academic_year_id' => $this->academicYear->id,
            'status' => PsbRegistration::STATUS_REJECTED,
        ]);
        PsbRegistration::factory()->count(1)->create([
            'academic_year_id' => $this->academicYear->id,
            'status' => PsbRegistration::STATUS_COMPLETED,
        ]);

        $response = $this->actingAs($this->principal)->get('/principal/psb');

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page
            ->where('summary.total', 11)
            ->where('summary.pending', 5)
            ->where('summary.approved', 3)
            ->where('summary.rejected', 2)
            ->where('summary.completed', 1)
        );
    }

    /**
     * Test dashboard menampilkan gender distribution
     */
    public function test_dashboard_shows_gender_distribution(): void
    {
        PsbRegistration::factory()->count(6)->create([
            'academic_year_id' => $this->academicYear->id,
            'gender' => 'male',
        ]);
        PsbRegistration::factory()->count(4)->create([
            'academic_year_id' => $this->academicYear->id,
            'gender' => 'female',
        ]);

        $response = $this->actingAs($this->principal)->get('/principal/psb');

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page
            ->where('genderDistribution', fn ($data) => count($data) === 2)
        );
    }

    /**
     * Test dashboard menampilkan status distribution
     */
    public function test_dashboard_shows_status_distribution(): void
    {
        PsbRegistration::factory()->count(3)->create([
            'academic_year_id' => $this->academicYear->id,
            'status' => PsbRegistration::STATUS_PENDING,
        ]);
        PsbRegistration::factory()->count(2)->create([
            'academic_year_id' => $this->academicYear->id,
            'status' => PsbRegistration::STATUS_APPROVED,
        ]);

        $response = $this->actingAs($this->principal)->get('/principal/psb');

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page
            ->where('statusDistribution', fn ($data) => count($data) === 2)
        );
    }

    /**
     * Test dashboard menampilkan period info
     */
    public function test_dashboard_shows_period_info(): void
    {
        $response = $this->actingAs($this->principal)->get('/principal/psb');

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page
            ->has('periodInfo')
            ->has('periodInfo.academic_year')
            ->has('periodInfo.registration_open_date')
            ->has('periodInfo.registration_close_date')
            ->has('periodInfo.announcement_date')
            ->has('periodInfo.is_registration_open')
        );
    }

    /**
     * Test dashboard menampilkan daily registrations untuk chart
     */
    public function test_dashboard_shows_daily_registrations(): void
    {
        // Create registrations on different days
        PsbRegistration::factory()->create([
            'academic_year_id' => $this->academicYear->id,
            'created_at' => now()->subDays(5),
        ]);
        PsbRegistration::factory()->count(2)->create([
            'academic_year_id' => $this->academicYear->id,
            'created_at' => now()->subDays(3),
        ]);
        PsbRegistration::factory()->count(3)->create([
            'academic_year_id' => $this->academicYear->id,
            'created_at' => now(),
        ]);

        $response = $this->actingAs($this->principal)->get('/principal/psb');

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page
            ->has('dailyRegistrations')
        );
    }

    /**
     * Test dashboard handles empty data gracefully
     */
    public function test_dashboard_handles_empty_data(): void
    {
        // No registrations created

        $response = $this->actingAs($this->principal)->get('/principal/psb');

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page
            ->where('summary.total', 0)
            ->where('summary.pending', 0)
            ->where('dailyRegistrations', [])
            ->where('genderDistribution', [])
            ->where('statusDistribution', [])
        );
    }
}
