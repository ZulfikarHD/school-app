<?php

namespace Tests\Unit\Services;

use App\Models\AcademicYear;
use App\Models\PsbDocument;
use App\Models\PsbRegistration;
use App\Models\PsbSetting;
use App\Services\PsbService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/**
 * Unit tests untuk PsbService
 *
 * Test ini bertujuan untuk memvalidasi business logic dalam PsbService,
 * yaitu: generate nomor pendaftaran, submit registrasi, upload dokumen,
 * dan validasi periode pendaftaran
 */
class PsbServiceTest extends TestCase
{
    use RefreshDatabase;

    protected PsbService $psbService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->psbService = new PsbService;
    }

    /**
     * Test generate nomor pendaftaran dengan format yang benar
     */
    public function test_generate_registration_number_format(): void
    {
        $registrationNumber = $this->psbService->generateRegistrationNumber();

        $year = now()->year;
        $expectedPattern = "/^PSB\/{$year}\/\d{4}$/";

        $this->assertMatchesRegularExpression($expectedPattern, $registrationNumber);
    }

    /**
     * Test generate nomor pendaftaran sequential
     */
    public function test_generate_registration_number_sequential(): void
    {
        $academicYear = AcademicYear::factory()->active()->create();

        // Buat beberapa registrasi existing
        PsbRegistration::factory()->count(3)->create([
            'academic_year_id' => $academicYear->id,
        ]);

        $newNumber = $this->psbService->generateRegistrationNumber();
        $year = now()->year;

        // Nomor baru harus setelah yang existing
        $this->assertEquals("PSB/{$year}/0004", $newNumber);
    }

    /**
     * Test isRegistrationOpen returns false ketika tidak ada setting
     */
    public function test_is_registration_open_returns_false_when_no_settings(): void
    {
        $result = $this->psbService->isRegistrationOpen();

        $this->assertFalse($result);
    }

    /**
     * Test isRegistrationOpen returns true ketika dalam periode
     */
    public function test_is_registration_open_returns_true_when_in_period(): void
    {
        $academicYear = AcademicYear::factory()->active()->create();
        PsbSetting::factory()->open()->create([
            'academic_year_id' => $academicYear->id,
        ]);

        $result = $this->psbService->isRegistrationOpen();

        $this->assertTrue($result);
    }

    /**
     * Test isRegistrationOpen returns false ketika periode sudah tutup
     */
    public function test_is_registration_open_returns_false_when_closed(): void
    {
        $academicYear = AcademicYear::factory()->active()->create();
        PsbSetting::factory()->closed()->create([
            'academic_year_id' => $academicYear->id,
        ]);

        $result = $this->psbService->isRegistrationOpen();

        $this->assertFalse($result);
    }

    /**
     * Test getActiveSettings returns null ketika tidak ada tahun ajaran aktif
     */
    public function test_get_active_settings_returns_null_when_no_active_year(): void
    {
        $result = $this->psbService->getActiveSettings();

        $this->assertNull($result);
    }

    /**
     * Test getActiveSettings returns setting untuk tahun ajaran aktif
     */
    public function test_get_active_settings_returns_correct_setting(): void
    {
        $academicYear = AcademicYear::factory()->active()->create();
        $setting = PsbSetting::factory()->create([
            'academic_year_id' => $academicYear->id,
        ]);

        $result = $this->psbService->getActiveSettings();

        $this->assertNotNull($result);
        $this->assertEquals($setting->id, $result->id);
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
            'address' => 'Jl. Test No. 123, Jakarta',
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
        ];

        $registration = $this->psbService->submitRegistration($data);

        $this->assertInstanceOf(PsbRegistration::class, $registration);
        $this->assertEquals('John Doe', $registration->student_name);
        $this->assertEquals(PsbRegistration::STATUS_PENDING, $registration->status);
        $this->assertNotNull($registration->registration_number);
    }

    /**
     * Test submit registration gagal ketika periode tutup
     */
    public function test_submit_registration_fails_when_registration_closed(): void
    {
        $academicYear = AcademicYear::factory()->active()->create();
        PsbSetting::factory()->closed()->create([
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
        ];

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Periode pendaftaran tidak aktif.');

        $this->psbService->submitRegistration($data);
    }

    /**
     * Test upload document
     */
    public function test_upload_document(): void
    {
        Storage::fake('public');

        $academicYear = AcademicYear::factory()->active()->create();
        $registration = PsbRegistration::factory()->create([
            'academic_year_id' => $academicYear->id,
        ]);

        $file = UploadedFile::fake()->create('akte_kelahiran.pdf', 1024);

        $document = $this->psbService->uploadDocument(
            $registration,
            $file,
            PsbDocument::TYPE_BIRTH_CERTIFICATE
        );

        $this->assertInstanceOf(PsbDocument::class, $document);
        $this->assertEquals($registration->id, $document->psb_registration_id);
        $this->assertEquals(PsbDocument::TYPE_BIRTH_CERTIFICATE, $document->document_type);
        $this->assertEquals(PsbDocument::STATUS_PENDING, $document->status);

        Storage::disk('public')->assertExists($document->file_path);
    }

    /**
     * Test getRegistrationStatus returns null untuk nomor tidak ditemukan
     */
    public function test_get_registration_status_returns_null_for_invalid_number(): void
    {
        $result = $this->psbService->getRegistrationStatus('PSB/2025/9999');

        $this->assertNull($result);
    }

    /**
     * Test getRegistrationStatus returns data yang benar
     */
    public function test_get_registration_status_returns_correct_data(): void
    {
        $academicYear = AcademicYear::factory()->active()->create();
        $registration = PsbRegistration::factory()->create([
            'academic_year_id' => $academicYear->id,
            'registration_number' => 'PSB/2025/0001',
        ]);

        $result = $this->psbService->getRegistrationStatus('PSB/2025/0001');

        $this->assertNotNull($result);
        $this->assertArrayHasKey('registration', $result);
        $this->assertArrayHasKey('timeline', $result);
        $this->assertArrayHasKey('documents', $result);
        $this->assertEquals($registration->student_name, $result['registration']['student_name']);
    }

    /**
     * Test getLandingPageData structure
     */
    public function test_get_landing_page_data_structure(): void
    {
        $result = $this->psbService->getLandingPageData();

        $this->assertArrayHasKey('settings', $result);
        $this->assertArrayHasKey('isOpen', $result);
        $this->assertArrayHasKey('requiredDocuments', $result);
    }

    /**
     * Test getLandingPageData dengan setting aktif
     */
    public function test_get_landing_page_data_with_active_settings(): void
    {
        $academicYear = AcademicYear::factory()->active()->create();
        PsbSetting::factory()->open()->create([
            'academic_year_id' => $academicYear->id,
            'registration_fee' => 150000,
        ]);

        $result = $this->psbService->getLandingPageData();

        $this->assertNotNull($result['settings']);
        $this->assertTrue($result['isOpen']);
        $this->assertEquals(150000, $result['settings']['registration_fee']);
    }

    /**
     * Test getRegistrationStats
     */
    public function test_get_registration_stats(): void
    {
        $academicYear = AcademicYear::factory()->active()->create();

        PsbRegistration::factory()->count(3)->create([
            'academic_year_id' => $academicYear->id,
            'status' => PsbRegistration::STATUS_PENDING,
        ]);

        PsbRegistration::factory()->count(2)->approved()->create([
            'academic_year_id' => $academicYear->id,
        ]);

        $stats = $this->psbService->getRegistrationStats();

        $this->assertEquals(5, $stats['total']);
        $this->assertEquals(3, $stats['pending']);
        $this->assertEquals(2, $stats['approved']);
    }
}
