<?php

namespace Tests\Unit\Student;

use App\Models\Guardian;
use App\Models\Student;
use App\Models\StudentStatusHistory;
use App\Models\User;
use App\Services\StudentService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StudentServiceTest extends TestCase
{
    use RefreshDatabase;

    protected StudentService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new StudentService;
    }

    /**
     * Test NIS generation dengan format yang benar
     */
    public function test_generates_nis_with_correct_format(): void
    {
        $nis = $this->service->generateNis('2024/2025');

        $this->assertMatchesRegularExpression('/^\d{8}$/', $nis);
        $this->assertStringStartsWith('2024', $nis);
    }

    /**
     * Test NIS increment untuk tahun yang sama
     */
    public function test_nis_increments_for_same_year(): void
    {
        $nis1 = $this->service->generateNis('2024/2025');
        Student::factory()->create(['nis' => $nis1]);

        $nis2 = $this->service->generateNis('2024/2025');

        $this->assertEquals('20240001', $nis1);
        $this->assertEquals('20240002', $nis2);
    }

    /**
     * Test NIS reset untuk tahun berbeda
     */
    public function test_nis_resets_for_different_year(): void
    {
        $nis2024 = $this->service->generateNis('2024/2025');
        Student::factory()->create(['nis' => $nis2024]);

        $nis2025 = $this->service->generateNis('2025/2026');

        $this->assertEquals('20240001', $nis2024);
        $this->assertEquals('20250001', $nis2025);
    }

    /**
     * Test attach guardians ke student
     */
    public function test_attaches_guardians_to_student(): void
    {
        $student = Student::factory()->create();

        $guardianData = [
            'ayah' => [
                'nik' => '1234567890123456',
                'nama_lengkap' => 'Budi Santoso',
                'pekerjaan' => 'PNS',
                'pendidikan' => 'S1',
                'penghasilan' => '3-5jt',
                'no_hp' => '081234567890',
                'email' => 'budi@example.com',
                'is_primary_contact' => true,
            ],
            'ibu' => [
                'nik' => '1234567890123457',
                'nama_lengkap' => 'Siti Rahayu',
                'pekerjaan' => 'Ibu Rumah Tangga',
                'pendidikan' => 'SMA',
                'penghasilan' => '<1jt',
                'no_hp' => '081234567891',
            ],
        ];

        $this->service->attachGuardiansToStudent($student, $guardianData);

        $this->assertEquals(2, $student->guardians()->count());
        $this->assertDatabaseHas('guardians', [
            'nama_lengkap' => 'Budi Santoso',
            'hubungan' => 'ayah',
        ]);
    }

    /**
     * Test auto-create parent account untuk primary contact
     */
    public function test_creates_parent_account_for_primary_contact(): void
    {
        $student = Student::factory()->create(['nis' => '20240001']);

        $guardianData = [
            'ayah' => [
                'nik' => '1234567890123456',
                'nama_lengkap' => 'Budi Santoso',
                'pekerjaan' => 'PNS',
                'pendidikan' => 'S1',
                'penghasilan' => '3-5jt',
                'no_hp' => '081234567890',
                'email' => 'budi@example.com',
                'is_primary_contact' => true,
            ],
            'ibu' => [
                'nik' => '1234567890123457',
                'nama_lengkap' => 'Siti Rahayu',
                'pekerjaan' => 'Ibu Rumah Tangga',
                'pendidikan' => 'SMA',
                'penghasilan' => '<1jt',
                'no_hp' => '081234567891',
            ],
        ];

        $this->service->attachGuardiansToStudent($student, $guardianData);

        // Verify parent account created
        $this->assertDatabaseHas('users', [
            'username' => '081234567890',
            'role' => 'PARENT',
            'status' => 'ACTIVE',
        ]);

        // Verify guardian linked to user
        $guardian = Guardian::where('nik', '1234567890123456')->first();
        $this->assertNotNull($guardian->user_id);
    }

    /**
     * Test tidak create duplicate parent account untuk multi-child
     */
    public function test_reuses_existing_parent_account_for_multiple_children(): void
    {
        // Create first student dengan parent account
        $student1 = Student::factory()->create(['nis' => '20240001']);
        $guardianData1 = [
            'ayah' => [
                'nik' => '1234567890123456',
                'nama_lengkap' => 'Budi Santoso',
                'pekerjaan' => 'PNS',
                'pendidikan' => 'S1',
                'penghasilan' => '3-5jt',
                'no_hp' => '081234567890',
                'is_primary_contact' => true,
            ],
        ];
        $this->service->attachGuardiansToStudent($student1, $guardianData1);

        $initialUserCount = User::where('role', 'PARENT')->count();

        // Create second student dengan same parent
        $student2 = Student::factory()->create(['nis' => '20240002']);
        $guardianData2 = [
            'ayah' => [
                'nik' => '1234567890123456', // Same NIK
                'nama_lengkap' => 'Budi Santoso',
                'pekerjaan' => 'PNS',
                'pendidikan' => 'S1',
                'penghasilan' => '3-5jt',
                'no_hp' => '081234567890',
                'is_primary_contact' => true,
            ],
        ];
        $this->service->attachGuardiansToStudent($student2, $guardianData2);

        // Verify tidak ada duplicate user account
        $finalUserCount = User::where('role', 'PARENT')->count();
        $this->assertEquals($initialUserCount, $finalUserCount);
    }

    /**
     * Test bulk promote students
     */
    public function test_bulk_promotes_students_to_new_class(): void
    {
        $students = Student::factory()->count(3)->create(['kelas_id' => 1]);
        $studentIds = $students->pluck('id')->toArray();

        $promotedCount = $this->service->bulkPromoteStudents(
            $studentIds,
            2,
            '2025/2026',
            'Pak Budi'
        );

        $this->assertEquals(3, $promotedCount);

        foreach ($students as $student) {
            $student->refresh();
            $this->assertEquals(2, $student->kelas_id);

            $this->assertDatabaseHas('student_class_history', [
                'student_id' => $student->id,
                'kelas_id' => 2,
                'tahun_ajaran' => '2025/2026',
                'wali_kelas' => 'Pak Budi',
            ]);
        }
    }

    /**
     * Test update student status dengan history
     */
    public function test_updates_student_status_with_history(): void
    {
        $student = Student::factory()->create(['status' => 'aktif']);
        $user = User::factory()->create();

        $history = $this->service->updateStudentStatus(
            $student,
            'mutasi',
            [
                'tanggal' => now(),
                'alasan' => 'Pindah ke luar kota',
                'keterangan' => 'Siswa berprestasi',
                'sekolah_tujuan' => 'SDN 01 Jakarta',
            ],
            $user->id
        );

        $student->refresh();

        $this->assertEquals('mutasi', $student->status);
        $this->assertInstanceOf(StudentStatusHistory::class, $history);
        $this->assertEquals('aktif', $history->status_lama);
        $this->assertEquals('mutasi', $history->status_baru);
        $this->assertEquals($user->id, $history->changed_by);
    }

    /**
     * Test normalize phone number
     */
    public function test_normalizes_phone_number(): void
    {
        $normalized1 = $this->service->normalizePhoneNumber('081234567890');
        $this->assertEquals('081234567890', $normalized1);

        $normalized2 = $this->service->normalizePhoneNumber('+6281234567890');
        $this->assertEquals('081234567890', $normalized2);

        $normalized3 = $this->service->normalizePhoneNumber('0812-3456-7890');
        $this->assertEquals('081234567890', $normalized3);

        $normalized4 = $this->service->normalizePhoneNumber('0812 3456 7890');
        $this->assertEquals('081234567890', $normalized4);
    }

    /**
     * Test create guardian dengan existing NIK updates data
     */
    public function test_updates_existing_guardian_when_nik_matches(): void
    {
        // Create existing guardian
        $existingGuardian = Guardian::factory()->create([
            'nik' => '1234567890123456',
            'nama_lengkap' => 'Old Name',
            'pekerjaan' => 'Old Job',
        ]);

        $student = Student::factory()->create();

        $guardianData = [
            'ayah' => [
                'nik' => '1234567890123456', // Same NIK
                'nama_lengkap' => 'Updated Name',
                'pekerjaan' => 'New Job',
                'pendidikan' => 'S1',
                'penghasilan' => '3-5jt',
                'no_hp' => '081234567890',
            ],
        ];

        $this->service->attachGuardiansToStudent($student, $guardianData);

        // Verify guardian updated, not duplicated
        $this->assertEquals(1, Guardian::where('nik', '1234567890123456')->count());

        $guardian = Guardian::where('nik', '1234567890123456')->first();
        $this->assertEquals('Updated Name', $guardian->nama_lengkap);
        $this->assertEquals('New Job', $guardian->pekerjaan);
    }
}
