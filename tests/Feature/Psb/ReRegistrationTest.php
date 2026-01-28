<?php

namespace Tests\Feature\Psb;

use App\Models\AcademicYear;
use App\Models\Guardian;
use App\Models\PsbPayment;
use App\Models\PsbRegistration;
use App\Models\PsbSetting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/**
 * Feature tests untuk Parent PSB Re-registration Controller
 * Testing re-registration flow for parents
 */
class ReRegistrationTest extends TestCase
{
    use RefreshDatabase;

    protected User $parent;

    protected User $admin;

    protected Guardian $guardian;

    protected AcademicYear $academicYear;

    protected PsbSetting $psbSetting;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('public');

        // Create admin user
        $this->admin = User::factory()->create([
            'role' => 'ADMIN',
        ]);

        // Create parent user
        $this->parent = User::factory()->create([
            'role' => 'PARENT',
        ]);

        // Create guardian linked to parent
        $this->guardian = Guardian::factory()->create([
            'user_id' => $this->parent->id,
            'nik' => '3201010101010001',
        ]);

        // Create active academic year
        $this->academicYear = AcademicYear::factory()->create([
            'is_active' => true,
            'name' => '2025/2026',
        ]);

        // Create PSB setting
        $this->psbSetting = PsbSetting::factory()->create([
            'academic_year_id' => $this->academicYear->id,
            'registration_fee' => 500000,
        ]);
    }

    // ============================================================
    // AUTHORIZATION TESTS
    // ============================================================

    /**
     * Test non-parent tidak dapat mengakses re-register routes
     */
    public function test_non_parent_cannot_access_re_register_routes(): void
    {
        $response = $this->actingAs($this->admin)->get('/parent/psb/re-register');
        $response->assertStatus(403);
    }

    /**
     * Test guest tidak dapat mengakses re-register routes
     */
    public function test_guest_cannot_access_re_register_routes(): void
    {
        $response = $this->get('/parent/psb/re-register');
        $response->assertRedirect('/login');
    }

    /**
     * Test parent tanpa registration tidak dapat akses re-register
     */
    public function test_parent_without_registration_redirected_to_dashboard(): void
    {
        $response = $this->actingAs($this->parent)->get('/parent/psb/re-register');

        $response->assertRedirect(route('parent.dashboard'));
        $response->assertSessionHas('error');
    }

    // ============================================================
    // RE-REGISTER PAGE TESTS
    // ============================================================

    /**
     * Test parent dapat mengakses halaman re-register jika ada registration yang announced
     */
    public function test_parent_can_access_re_register_page_if_announced(): void
    {
        $registration = PsbRegistration::factory()->create([
            'academic_year_id' => $this->academicYear->id,
            'status' => PsbRegistration::STATUS_APPROVED,
            'announced_at' => now(),
            'father_nik' => $this->guardian->nik,
        ]);

        $response = $this->actingAs($this->parent)->get('/parent/psb/re-register');

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page
            ->component('Parent/Psb/ReRegister')
            ->has('registration')
            ->has('payments')
            ->has('timeline')
            ->has('paymentInfo')
            ->where('registration.registration_number', $registration->registration_number)
        );
    }

    /**
     * Test parent tidak dapat akses re-register jika belum announced
     */
    public function test_parent_cannot_access_re_register_if_not_announced(): void
    {
        // Create approved but not announced registration
        PsbRegistration::factory()->create([
            'academic_year_id' => $this->academicYear->id,
            'status' => PsbRegistration::STATUS_APPROVED,
            'announced_at' => null, // Not announced
            'father_nik' => $this->guardian->nik,
        ]);

        $response = $this->actingAs($this->parent)->get('/parent/psb/re-register');

        $response->assertRedirect(route('parent.dashboard'));
    }

    /**
     * Test parent dengan completed registration redirect ke welcome
     */
    public function test_parent_with_completed_registration_redirected_to_welcome(): void
    {
        PsbRegistration::factory()->create([
            'academic_year_id' => $this->academicYear->id,
            'status' => PsbRegistration::STATUS_COMPLETED,
            'announced_at' => now(),
            'father_nik' => $this->guardian->nik,
        ]);

        $response = $this->actingAs($this->parent)->get('/parent/psb/re-register');

        $response->assertRedirect(route('parent.psb.welcome'));
    }

    // ============================================================
    // UPLOAD PAYMENT TESTS
    // ============================================================

    /**
     * Test parent dapat upload bukti pembayaran
     */
    public function test_parent_can_upload_payment_proof(): void
    {
        $registration = PsbRegistration::factory()->create([
            'academic_year_id' => $this->academicYear->id,
            'status' => PsbRegistration::STATUS_APPROVED,
            'announced_at' => now(),
            'father_nik' => $this->guardian->nik,
        ]);

        $file = UploadedFile::fake()->image('bukti-transfer.jpg', 800, 600);

        $response = $this->actingAs($this->parent)->post('/parent/psb/payment', [
            'payment_type' => PsbPayment::TYPE_RE_REGISTRATION_FEE,
            'amount' => 500000,
            'payment_method' => PsbPayment::METHOD_TRANSFER,
            'proof_file' => $file,
            'payment_date' => now()->format('Y-m-d'),
            'notes' => 'Transfer via BCA',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        // Verify payment record created
        $this->assertDatabaseHas('psb_payments', [
            'psb_registration_id' => $registration->id,
            'payment_type' => PsbPayment::TYPE_RE_REGISTRATION_FEE,
            'amount' => 500000,
            'status' => PsbPayment::STATUS_PENDING,
        ]);

        // Verify registration status updated
        $registration->refresh();
        $this->assertEquals(PsbRegistration::STATUS_RE_REGISTRATION, $registration->status);
    }

    /**
     * Test upload payment memerlukan file
     */
    public function test_upload_payment_requires_proof_file(): void
    {
        PsbRegistration::factory()->create([
            'academic_year_id' => $this->academicYear->id,
            'status' => PsbRegistration::STATUS_APPROVED,
            'announced_at' => now(),
            'father_nik' => $this->guardian->nik,
        ]);

        $response = $this->actingAs($this->parent)->post('/parent/psb/payment', [
            'payment_type' => PsbPayment::TYPE_RE_REGISTRATION_FEE,
            'amount' => 500000,
            'payment_method' => PsbPayment::METHOD_TRANSFER,
            'payment_date' => now()->format('Y-m-d'),
            // No proof_file
        ]);

        $response->assertSessionHasErrors('proof_file');
    }

    /**
     * Test upload payment hanya menerima file yang valid
     */
    public function test_upload_payment_validates_file_type(): void
    {
        PsbRegistration::factory()->create([
            'academic_year_id' => $this->academicYear->id,
            'status' => PsbRegistration::STATUS_APPROVED,
            'announced_at' => now(),
            'father_nik' => $this->guardian->nik,
        ]);

        $file = UploadedFile::fake()->create('document.docx', 100, 'application/msword');

        $response = $this->actingAs($this->parent)->post('/parent/psb/payment', [
            'payment_type' => PsbPayment::TYPE_RE_REGISTRATION_FEE,
            'amount' => 500000,
            'payment_method' => PsbPayment::METHOD_TRANSFER,
            'proof_file' => $file,
            'payment_date' => now()->format('Y-m-d'),
        ]);

        $response->assertSessionHasErrors('proof_file');
    }

    // ============================================================
    // WELCOME PAGE TESTS
    // ============================================================

    /**
     * Test parent dapat mengakses welcome page jika completed
     */
    public function test_parent_can_access_welcome_page_if_completed(): void
    {
        $registration = PsbRegistration::factory()->create([
            'academic_year_id' => $this->academicYear->id,
            'status' => PsbRegistration::STATUS_COMPLETED,
            'announced_at' => now(),
            'father_nik' => $this->guardian->nik,
        ]);

        $response = $this->actingAs($this->parent)->get('/parent/psb/welcome');

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page
            ->component('Parent/Psb/Welcome')
            ->has('registration')
            ->where('registration.student_name', $registration->student_name)
        );
    }

    /**
     * Test parent tidak dapat akses welcome jika belum completed
     */
    public function test_parent_cannot_access_welcome_if_not_completed(): void
    {
        PsbRegistration::factory()->create([
            'academic_year_id' => $this->academicYear->id,
            'status' => PsbRegistration::STATUS_RE_REGISTRATION, // Not completed yet
            'announced_at' => now(),
            'father_nik' => $this->guardian->nik,
        ]);

        $response = $this->actingAs($this->parent)->get('/parent/psb/welcome');

        $response->assertRedirect(route('parent.psb.re-register'));
    }
}
