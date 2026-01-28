<?php

namespace Tests\Feature;

use App\Models\AcademicYear;
use App\Models\PsbRegistration;
use App\Models\PsbSetting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/**
 * Feature tests untuk PSB Registration flow
 *
 * Test ini bertujuan untuk memvalidasi integrasi end-to-end dari
 * flow pendaftaran PSB, yaitu: akses halaman, submit form, dan tracking
 */
class PsbRegistrationTest extends TestCase
{
    use RefreshDatabase;
    use WithFaker;

    /**
     * Test landing page dapat diakses
     */
    public function test_landing_page_accessible(): void
    {
        $response = $this->get(route('psb.landing'));

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page->component('Psb/Landing'));
    }

    /**
     * Test landing page menampilkan data settings
     */
    public function test_landing_page_shows_settings(): void
    {
        $academicYear = AcademicYear::factory()->active()->create();
        PsbSetting::factory()->open()->create([
            'academic_year_id' => $academicYear->id,
            'registration_fee' => 150000,
            'quota_per_class' => 30,
        ]);

        $response = $this->get(route('psb.landing'));

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page
            ->component('Psb/Landing')
            ->has('settings')
            ->where('isOpen', true)
        );
    }

    /**
     * Test register page dapat diakses ketika pendaftaran dibuka
     */
    public function test_register_page_accessible_when_open(): void
    {
        $academicYear = AcademicYear::factory()->active()->create();
        PsbSetting::factory()->open()->create([
            'academic_year_id' => $academicYear->id,
        ]);

        $response = $this->get(route('psb.register'));

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page->component('Psb/Register'));
    }

    /**
     * Test register page redirect ketika pendaftaran ditutup
     */
    public function test_register_page_redirects_when_closed(): void
    {
        $academicYear = AcademicYear::factory()->active()->create();
        PsbSetting::factory()->closed()->create([
            'academic_year_id' => $academicYear->id,
        ]);

        $response = $this->get(route('psb.register'));

        $response->assertRedirect(route('psb.landing'));
    }

    /**
     * Test submit registration berhasil
     */
    public function test_submit_registration_success(): void
    {
        Storage::fake('public');

        $academicYear = AcademicYear::factory()->active()->create();
        PsbSetting::factory()->open()->create([
            'academic_year_id' => $academicYear->id,
        ]);

        $data = [
            'student_name' => 'John Doe',
            'student_nik' => '1234567890123456',
            'birth_place' => 'Jakarta',
            'birth_date' => '2018-05-15',
            'gender' => 'L',
            'religion' => 'Islam',
            'address' => 'Jl. Test No. 123, RT 001/RW 002, Jakarta',
            'child_order' => 1,
            'origin_school' => 'TK Test',
            'father_name' => 'Father Name',
            'father_nik' => '1234567890123457',
            'father_occupation' => 'Wiraswasta',
            'father_phone' => '081234567890',
            'father_email' => 'father@test.com',
            'mother_name' => 'Mother Name',
            'mother_nik' => '1234567890123458',
            'mother_occupation' => 'Ibu Rumah Tangga',
            'mother_phone' => '081234567891',
            'birth_certificate' => UploadedFile::fake()->create('akte.pdf', 1024, 'application/pdf'),
            'family_card' => UploadedFile::fake()->create('kk.pdf', 1024, 'application/pdf'),
            'parent_id' => UploadedFile::fake()->create('ktp.pdf', 1024, 'application/pdf'),
            'photo' => UploadedFile::fake()->image('photo.jpg', 300, 400),
        ];

        $response = $this->post(route('psb.store'), $data);

        $response->assertRedirect();

        $this->assertDatabaseHas('psb_registrations', [
            'student_name' => 'John Doe',
            'student_nik' => '1234567890123456',
            'status' => 'pending',
        ]);

        $registration = PsbRegistration::where('student_nik', '1234567890123456')->first();
        $this->assertNotNull($registration);
        $this->assertCount(4, $registration->documents);
    }

    /**
     * Test submit registration validation errors
     */
    public function test_submit_registration_validation_errors(): void
    {
        $academicYear = AcademicYear::factory()->active()->create();
        PsbSetting::factory()->open()->create([
            'academic_year_id' => $academicYear->id,
        ]);

        $response = $this->post(route('psb.store'), []);

        $response->assertSessionHasErrors([
            'student_name',
            'student_nik',
            'birth_place',
            'birth_date',
            'gender',
            'religion',
            'address',
            'child_order',
            'father_name',
            'father_nik',
            'father_occupation',
            'father_phone',
            'mother_name',
            'mother_nik',
            'mother_occupation',
            'birth_certificate',
            'family_card',
            'parent_id',
            'photo',
        ]);
    }

    /**
     * Test success page dapat diakses dengan registration valid
     */
    public function test_success_page_accessible(): void
    {
        $academicYear = AcademicYear::factory()->active()->create();
        $registration = PsbRegistration::factory()->create([
            'academic_year_id' => $academicYear->id,
        ]);

        $response = $this->get(route('psb.success', $registration));

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page
            ->component('Psb/Success')
            ->has('registration')
        );
    }

    /**
     * Test tracking page dapat diakses
     */
    public function test_tracking_page_accessible(): void
    {
        $response = $this->get(route('psb.tracking'));

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page->component('Psb/Tracking'));
    }

    /**
     * Test check status dengan nomor valid
     */
    public function test_check_status_with_valid_number(): void
    {
        $academicYear = AcademicYear::factory()->active()->create();
        $registration = PsbRegistration::factory()->create([
            'academic_year_id' => $academicYear->id,
            'registration_number' => 'PSB/2025/0001',
        ]);

        $response = $this->post(route('psb.check-status'), [
            'registration_number' => 'PSB/2025/0001',
        ]);

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page
            ->component('Psb/Tracking')
            ->has('registration')
            ->has('timeline')
        );
    }

    /**
     * Test check status dengan nomor tidak valid
     */
    public function test_check_status_with_invalid_number(): void
    {
        $response = $this->post(route('psb.check-status'), [
            'registration_number' => 'PSB/2025/9999',
        ]);

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page
            ->component('Psb/Tracking')
            ->where('registration', null)
            ->has('error')
        );
    }

    /**
     * Test NIK validation must be 16 digits
     */
    public function test_nik_must_be_16_digits(): void
    {
        Storage::fake('public');

        $academicYear = AcademicYear::factory()->active()->create();
        PsbSetting::factory()->open()->create([
            'academic_year_id' => $academicYear->id,
        ]);

        $data = [
            'student_name' => 'John Doe',
            'student_nik' => '123456', // Invalid: kurang dari 16 digit
            'birth_place' => 'Jakarta',
            'birth_date' => '2018-05-15',
            'gender' => 'L',
            'religion' => 'Islam',
            'address' => 'Jl. Test No. 123',
            'child_order' => 1,
            'father_name' => 'Father',
            'father_nik' => '1234567890123457',
            'father_occupation' => 'Wiraswasta',
            'father_phone' => '081234567890',
            'mother_name' => 'Mother',
            'mother_nik' => '1234567890123458',
            'mother_occupation' => 'IRT',
            'birth_certificate' => UploadedFile::fake()->create('akte.pdf', 1024),
            'family_card' => UploadedFile::fake()->create('kk.pdf', 1024),
            'parent_id' => UploadedFile::fake()->create('ktp.pdf', 1024),
            'photo' => UploadedFile::fake()->image('photo.jpg'),
        ];

        $response = $this->post(route('psb.store'), $data);

        $response->assertSessionHasErrors('student_nik');
    }

    /**
     * Test file upload size validation
     */
    public function test_file_upload_size_validation(): void
    {
        Storage::fake('public');

        $academicYear = AcademicYear::factory()->active()->create();
        PsbSetting::factory()->open()->create([
            'academic_year_id' => $academicYear->id,
        ]);

        $data = [
            'student_name' => 'John Doe',
            'student_nik' => '1234567890123456',
            'birth_place' => 'Jakarta',
            'birth_date' => '2018-05-15',
            'gender' => 'L',
            'religion' => 'Islam',
            'address' => 'Jl. Test No. 123',
            'child_order' => 1,
            'father_name' => 'Father',
            'father_nik' => '1234567890123457',
            'father_occupation' => 'Wiraswasta',
            'father_phone' => '081234567890',
            'mother_name' => 'Mother',
            'mother_nik' => '1234567890123458',
            'mother_occupation' => 'IRT',
            'birth_certificate' => UploadedFile::fake()->create('akte.pdf', 6000), // 6MB, over limit
            'family_card' => UploadedFile::fake()->create('kk.pdf', 1024),
            'parent_id' => UploadedFile::fake()->create('ktp.pdf', 1024),
            'photo' => UploadedFile::fake()->image('photo.jpg'),
        ];

        $response = $this->post(route('psb.store'), $data);

        $response->assertSessionHasErrors('birth_certificate');
    }
}
