<?php

namespace Tests\Feature\Psb;

use App\Models\AcademicYear;
use App\Models\PsbPayment;
use App\Models\PsbRegistration;
use App\Models\PsbSetting;
use App\Models\Student;
use App\Models\User;
use App\Notifications\PsbPaymentVerified;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/**
 * Feature tests untuk Admin PSB Payment Controller
 * Testing payment verification functionality
 */
class PaymentVerificationTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected User $teacher;

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

        // Create teacher user (non-admin)
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
     * Test non-admin tidak dapat mengakses payment routes
     */
    public function test_non_admin_cannot_access_payment_routes(): void
    {
        $response = $this->actingAs($this->teacher)->get('/admin/psb/payments');
        $response->assertStatus(403);
    }

    /**
     * Test guest tidak dapat mengakses payment routes
     */
    public function test_guest_cannot_access_payment_routes(): void
    {
        $response = $this->get('/admin/psb/payments');
        $response->assertRedirect('/login');
    }

    // ============================================================
    // INDEX TESTS
    // ============================================================

    /**
     * Test admin dapat mengakses halaman payment verification
     */
    public function test_admin_can_access_payment_index(): void
    {
        $response = $this->actingAs($this->admin)->get('/admin/psb/payments');

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page
            ->component('Admin/Psb/Payments/Index')
            ->has('payments')
            ->has('stats')
            ->has('filters')
            ->has('statuses')
        );
    }

    /**
     * Test index default menampilkan pending payments only
     */
    public function test_index_shows_pending_payments_by_default(): void
    {
        $registration = PsbRegistration::factory()->create([
            'academic_year_id' => $this->academicYear->id,
            'status' => PsbRegistration::STATUS_RE_REGISTRATION,
        ]);

        // Create payments dengan berbagai status
        PsbPayment::factory()->count(3)->create([
            'psb_registration_id' => $registration->id,
            'status' => PsbPayment::STATUS_PENDING,
        ]);
        PsbPayment::factory()->count(2)->create([
            'psb_registration_id' => $registration->id,
            'status' => PsbPayment::STATUS_VERIFIED,
        ]);

        $response = $this->actingAs($this->admin)->get('/admin/psb/payments');

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page
            ->where('payments.data', fn ($data) => count($data) === 3)
        );
    }

    /**
     * Test filter by status works
     */
    public function test_filter_by_status_works(): void
    {
        $registration = PsbRegistration::factory()->create([
            'academic_year_id' => $this->academicYear->id,
            'status' => PsbRegistration::STATUS_RE_REGISTRATION,
        ]);

        PsbPayment::factory()->count(3)->create([
            'psb_registration_id' => $registration->id,
            'status' => PsbPayment::STATUS_VERIFIED,
        ]);
        PsbPayment::factory()->count(2)->create([
            'psb_registration_id' => $registration->id,
            'status' => PsbPayment::STATUS_PENDING,
        ]);

        $response = $this->actingAs($this->admin)->get('/admin/psb/payments?status=verified');

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page
            ->where('payments.data', fn ($data) => count($data) === 3)
        );
    }

    // ============================================================
    // VERIFY PAYMENT TESTS
    // ============================================================

    /**
     * Test admin dapat approve payment
     */
    public function test_admin_can_approve_payment(): void
    {
        Notification::fake();

        $registration = PsbRegistration::factory()->create([
            'academic_year_id' => $this->academicYear->id,
            'status' => PsbRegistration::STATUS_RE_REGISTRATION,
            'father_email' => 'father@example.com',
        ]);

        $payment = PsbPayment::factory()->create([
            'psb_registration_id' => $registration->id,
            'status' => PsbPayment::STATUS_PENDING,
        ]);

        $response = $this->actingAs($this->admin)->post("/admin/psb/payments/{$payment->id}/verify", [
            'approved' => true,
            'notes' => 'Pembayaran valid.',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $payment->refresh();
        $this->assertEquals(PsbPayment::STATUS_VERIFIED, $payment->status);
        $this->assertEquals($this->admin->id, $payment->verified_by);
        $this->assertNotNull($payment->verified_at);

        Notification::assertSentOnDemand(PsbPaymentVerified::class);
    }

    /**
     * Test admin dapat reject payment dengan alasan
     */
    public function test_admin_can_reject_payment_with_reason(): void
    {
        Notification::fake();

        $registration = PsbRegistration::factory()->create([
            'academic_year_id' => $this->academicYear->id,
            'status' => PsbRegistration::STATUS_RE_REGISTRATION,
            'father_email' => 'father@example.com',
        ]);

        $payment = PsbPayment::factory()->create([
            'psb_registration_id' => $registration->id,
            'status' => PsbPayment::STATUS_PENDING,
        ]);

        $response = $this->actingAs($this->admin)->post("/admin/psb/payments/{$payment->id}/verify", [
            'approved' => false,
            'rejection_reason' => 'Bukti pembayaran tidak valid karena tidak terlihat jelas.',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $payment->refresh();
        $this->assertEquals(PsbPayment::STATUS_REJECTED, $payment->status);
        $this->assertStringContainsString('tidak valid', $payment->notes);
    }

    /**
     * Test reject memerlukan alasan
     */
    public function test_reject_requires_reason(): void
    {
        $registration = PsbRegistration::factory()->create([
            'academic_year_id' => $this->academicYear->id,
            'status' => PsbRegistration::STATUS_RE_REGISTRATION,
        ]);

        $payment = PsbPayment::factory()->create([
            'psb_registration_id' => $registration->id,
            'status' => PsbPayment::STATUS_PENDING,
        ]);

        $response = $this->actingAs($this->admin)->post("/admin/psb/payments/{$payment->id}/verify", [
            'approved' => false,
            'rejection_reason' => '',
        ]);

        $response->assertSessionHasErrors('rejection_reason');
    }

    /**
     * Test tidak dapat verify payment yang sudah verified
     */
    public function test_cannot_verify_already_verified_payment(): void
    {
        $registration = PsbRegistration::factory()->create([
            'academic_year_id' => $this->academicYear->id,
            'status' => PsbRegistration::STATUS_RE_REGISTRATION,
        ]);

        $payment = PsbPayment::factory()->create([
            'psb_registration_id' => $registration->id,
            'status' => PsbPayment::STATUS_VERIFIED, // Already verified
        ]);

        $response = $this->actingAs($this->admin)->post("/admin/psb/payments/{$payment->id}/verify", [
            'approved' => true,
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('error');
    }

    /**
     * Test approve payment creates student when all payments verified
     */
    public function test_approve_payment_creates_student_when_all_payments_verified(): void
    {
        Notification::fake();

        $registration = PsbRegistration::factory()->create([
            'academic_year_id' => $this->academicYear->id,
            'status' => PsbRegistration::STATUS_RE_REGISTRATION,
            'student_name' => 'Ahmad Hidayat',
            'student_nik' => '3201010101010001',
            'birth_place' => 'Jakarta',
            'birth_date' => '2015-05-15',
            'gender' => 'male',
            'religion' => 'Islam',
            'address' => 'Jl. Test No. 1',
            'child_order' => 1,
            'father_name' => 'Budi Santoso',
            'father_nik' => '3201010101010002',
            'father_occupation' => 'Wiraswasta',
            'father_phone' => '081234567890',
            'father_email' => 'budi@example.com',
            'mother_name' => 'Siti Aminah',
            'mother_nik' => '3201010101010003',
            'mother_occupation' => 'Ibu Rumah Tangga',
        ]);

        $payment = PsbPayment::factory()->create([
            'psb_registration_id' => $registration->id,
            'status' => PsbPayment::STATUS_PENDING,
        ]);

        $response = $this->actingAs($this->admin)->post("/admin/psb/payments/{$payment->id}/verify", [
            'approved' => true,
            'notes' => 'Valid.',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        // Verify student was created
        $this->assertDatabaseHas('students', [
            'nik' => '3201010101010001',
            'nama_lengkap' => 'Ahmad Hidayat',
        ]);

        // Verify registration status is completed
        $registration->refresh();
        $this->assertEquals(PsbRegistration::STATUS_COMPLETED, $registration->status);
    }
}
