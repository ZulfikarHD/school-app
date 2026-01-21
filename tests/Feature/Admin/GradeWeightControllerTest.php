<?php

namespace Tests\Feature\Admin;

use App\Models\GradeWeightConfig;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Feature tests untuk Admin GradeWeightController
 * Testing konfigurasi bobot nilai CRUD dan validation
 */
class GradeWeightControllerTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected User $teacher;

    protected string $tahunAjaran;

    protected function setUp(): void
    {
        parent::setUp();

        // Create admin user
        $this->admin = User::factory()->create([
            'role' => 'ADMIN',
        ]);

        // Create teacher user
        $this->teacher = User::factory()->create([
            'role' => 'TEACHER',
        ]);

        // Set tahun ajaran dinamis
        $this->tahunAjaran = now()->month >= 7
            ? now()->year.'/'.(now()->year + 1)
            : (now()->year - 1).'/'.now()->year;
    }

    // ============================================================
    // INDEX TESTS
    // ============================================================

    /**
     * Test admin dapat mengakses halaman grade weights
     */
    public function test_admin_can_access_grade_weights_config(): void
    {
        $response = $this->actingAs($this->admin)->get('/admin/settings/grade-weights');

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page
            ->component('Admin/Settings/GradeWeights')
            ->has('config')
            ->has('availableTahunAjaran')
            ->has('currentTahunAjaran')
            ->has('defaultWeights')
        );
    }

    /**
     * Test grade weights menampilkan default config jika belum ada
     */
    public function test_grade_weights_shows_default_if_not_exists(): void
    {
        $response = $this->actingAs($this->admin)->get('/admin/settings/grade-weights');

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page
            ->where('config.uh_weight', GradeWeightConfig::DEFAULT_UH_WEIGHT)
            ->where('config.uts_weight', GradeWeightConfig::DEFAULT_UTS_WEIGHT)
            ->where('config.uas_weight', GradeWeightConfig::DEFAULT_UAS_WEIGHT)
            ->where('config.praktik_weight', GradeWeightConfig::DEFAULT_PRAKTIK_WEIGHT)
        );
    }

    /**
     * Test grade weights menampilkan config yang sudah ada
     */
    public function test_grade_weights_shows_existing_config(): void
    {
        // Create custom config
        GradeWeightConfig::create([
            'tahun_ajaran' => $this->tahunAjaran,
            'subject_id' => null,
            'uh_weight' => 40,
            'uts_weight' => 20,
            'uas_weight' => 25,
            'praktik_weight' => 15,
            'is_default' => true,
        ]);

        $response = $this->actingAs($this->admin)
            ->get("/admin/settings/grade-weights?tahun_ajaran={$this->tahunAjaran}");

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page
            ->where('config.uh_weight', 40)
            ->where('config.uts_weight', 20)
            ->where('config.uas_weight', 25)
            ->where('config.praktik_weight', 15)
        );
    }

    /**
     * Test non-admin tidak bisa akses grade weights
     */
    public function test_non_admin_cannot_access_grade_weights(): void
    {
        $response = $this->actingAs($this->teacher)->get('/admin/settings/grade-weights');

        $response->assertForbidden();
    }

    // ============================================================
    // UPDATE TESTS
    // ============================================================

    /**
     * Test admin dapat mengupdate grade weights
     */
    public function test_admin_can_update_grade_weights(): void
    {
        $data = [
            'tahun_ajaran' => $this->tahunAjaran,
            'uh_weight' => 35,
            'uts_weight' => 20,
            'uas_weight' => 30,
            'praktik_weight' => 15,
        ];

        $response = $this->actingAs($this->admin)->put('/admin/settings/grade-weights', $data);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('grade_weight_configs', [
            'tahun_ajaran' => $this->tahunAjaran,
            'uh_weight' => 35,
            'uts_weight' => 20,
            'uas_weight' => 30,
            'praktik_weight' => 15,
            'is_default' => true,
        ]);
    }

    /**
     * Test validation: total weight harus = 100%
     */
    public function test_validation_total_weight_must_equal_100(): void
    {
        $data = [
            'tahun_ajaran' => $this->tahunAjaran,
            'uh_weight' => 30,
            'uts_weight' => 25,
            'uas_weight' => 30,
            'praktik_weight' => 20, // Total = 105%
        ];

        $response = $this->actingAs($this->admin)->put('/admin/settings/grade-weights', $data);

        $response->assertSessionHasErrors('total_weight');
    }

    /**
     * Test validation: total weight kurang dari 100%
     */
    public function test_validation_total_weight_less_than_100(): void
    {
        $data = [
            'tahun_ajaran' => $this->tahunAjaran,
            'uh_weight' => 20,
            'uts_weight' => 20,
            'uas_weight' => 20,
            'praktik_weight' => 20, // Total = 80%
        ];

        $response = $this->actingAs($this->admin)->put('/admin/settings/grade-weights', $data);

        $response->assertSessionHasErrors('total_weight');
    }

    /**
     * Test validation: weight tidak boleh negatif
     */
    public function test_validation_weight_not_negative(): void
    {
        $data = [
            'tahun_ajaran' => $this->tahunAjaran,
            'uh_weight' => -10,
            'uts_weight' => 40,
            'uas_weight' => 50,
            'praktik_weight' => 20,
        ];

        $response = $this->actingAs($this->admin)->put('/admin/settings/grade-weights', $data);

        $response->assertSessionHasErrors('uh_weight');
    }

    /**
     * Test validation: weight maksimal 100
     */
    public function test_validation_weight_max_100(): void
    {
        $data = [
            'tahun_ajaran' => $this->tahunAjaran,
            'uh_weight' => 150,
            'uts_weight' => 0,
            'uas_weight' => 0,
            'praktik_weight' => 0,
        ];

        $response = $this->actingAs($this->admin)->put('/admin/settings/grade-weights', $data);

        $response->assertSessionHasErrors('uh_weight');
    }

    /**
     * Test validation: tahun ajaran format valid
     */
    public function test_validation_tahun_ajaran_format(): void
    {
        $data = [
            'tahun_ajaran' => '2024', // Invalid format
            'uh_weight' => 30,
            'uts_weight' => 25,
            'uas_weight' => 30,
            'praktik_weight' => 15,
        ];

        $response = $this->actingAs($this->admin)->put('/admin/settings/grade-weights', $data);

        $response->assertSessionHasErrors('tahun_ajaran');
    }

    /**
     * Test validation: semua field wajib diisi
     */
    public function test_validation_all_fields_required(): void
    {
        $data = [
            'tahun_ajaran' => $this->tahunAjaran,
            // Missing weights
        ];

        $response = $this->actingAs($this->admin)->put('/admin/settings/grade-weights', $data);

        $response->assertSessionHasErrors(['uh_weight', 'uts_weight', 'uas_weight', 'praktik_weight']);
    }

    // ============================================================
    // DEFAULT VS CUSTOM TESTS
    // ============================================================

    /**
     * Test update membuat default config jika belum ada
     */
    public function test_update_creates_default_config_if_not_exists(): void
    {
        $this->assertDatabaseMissing('grade_weight_configs', [
            'tahun_ajaran' => $this->tahunAjaran,
        ]);

        $data = [
            'tahun_ajaran' => $this->tahunAjaran,
            'uh_weight' => 30,
            'uts_weight' => 25,
            'uas_weight' => 30,
            'praktik_weight' => 15,
        ];

        $response = $this->actingAs($this->admin)->put('/admin/settings/grade-weights', $data);

        $response->assertRedirect();

        $this->assertDatabaseHas('grade_weight_configs', [
            'tahun_ajaran' => $this->tahunAjaran,
            'is_default' => true,
        ]);
    }

    /**
     * Test update mengupdate config yang sudah ada
     */
    public function test_update_modifies_existing_config(): void
    {
        // Create existing config
        $config = GradeWeightConfig::create([
            'tahun_ajaran' => $this->tahunAjaran,
            'subject_id' => null,
            'uh_weight' => 30,
            'uts_weight' => 25,
            'uas_weight' => 30,
            'praktik_weight' => 15,
            'is_default' => true,
        ]);

        $data = [
            'tahun_ajaran' => $this->tahunAjaran,
            'uh_weight' => 40, // Changed
            'uts_weight' => 20, // Changed
            'uas_weight' => 25, // Changed
            'praktik_weight' => 15,
        ];

        $response = $this->actingAs($this->admin)->put('/admin/settings/grade-weights', $data);

        $response->assertRedirect();

        // Verify only 1 config exists (updated, not duplicated)
        $this->assertEquals(1, GradeWeightConfig::where('tahun_ajaran', $this->tahunAjaran)->count());

        $this->assertDatabaseHas('grade_weight_configs', [
            'id' => $config->id,
            'uh_weight' => 40,
            'uts_weight' => 20,
            'uas_weight' => 25,
        ]);
    }

    // ============================================================
    // AUTHORIZATION TESTS
    // ============================================================

    /**
     * Test teacher tidak bisa update grade weights
     */
    public function test_teacher_cannot_update_grade_weights(): void
    {
        $data = [
            'tahun_ajaran' => $this->tahunAjaran,
            'uh_weight' => 30,
            'uts_weight' => 25,
            'uas_weight' => 30,
            'praktik_weight' => 15,
        ];

        $response = $this->actingAs($this->teacher)->put('/admin/settings/grade-weights', $data);

        $response->assertForbidden();
    }

    /**
     * Test parent tidak bisa akses grade weights
     */
    public function test_parent_cannot_access_grade_weights(): void
    {
        $parent = User::factory()->create(['role' => 'PARENT']);

        $response = $this->actingAs($parent)->get('/admin/settings/grade-weights');

        $response->assertForbidden();
    }

    // ============================================================
    // EDGE CASE TESTS
    // ============================================================

    /**
     * Test weight 0 diterima untuk komponen opsional
     */
    public function test_zero_weight_is_valid_for_optional_component(): void
    {
        $data = [
            'tahun_ajaran' => $this->tahunAjaran,
            'uh_weight' => 40,
            'uts_weight' => 30,
            'uas_weight' => 30,
            'praktik_weight' => 0, // No praktik component
        ];

        $response = $this->actingAs($this->admin)->put('/admin/settings/grade-weights', $data);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('grade_weight_configs', [
            'tahun_ajaran' => $this->tahunAjaran,
            'praktik_weight' => 0,
        ]);
    }

    /**
     * Test single weight 100% diterima (edge case)
     */
    public function test_single_weight_100_is_valid(): void
    {
        $data = [
            'tahun_ajaran' => $this->tahunAjaran,
            'uh_weight' => 100,
            'uts_weight' => 0,
            'uas_weight' => 0,
            'praktik_weight' => 0,
        ];

        $response = $this->actingAs($this->admin)->put('/admin/settings/grade-weights', $data);

        $response->assertRedirect();
        $response->assertSessionHas('success');
    }
}
