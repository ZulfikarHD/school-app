<?php

namespace Tests\Unit\Student;

use App\Models\Guardian;
use App\Models\Student;
use App\Models\StudentClassHistory;
use App\Models\StudentStatusHistory;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StudentModelTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test student factory creates valid student
     */
    public function test_student_factory_creates_valid_student(): void
    {
        $student = Student::factory()->create();

        $this->assertNotNull($student->nis);
        $this->assertNotNull($student->nama_lengkap);
        $this->assertEquals('aktif', $student->status);
    }

    /**
     * Test student dapat attach guardians
     */
    public function test_student_can_attach_guardians(): void
    {
        $student = Student::factory()->create();
        $guardian = Guardian::factory()->create();

        $student->guardians()->attach($guardian->id);

        $this->assertEquals(1, $student->guardians()->count());
    }

    /**
     * Test student dapat get primary guardian
     */
    public function test_student_can_get_primary_guardian(): void
    {
        $student = Student::factory()->create();
        $primaryGuardian = Guardian::factory()->ayah()->create();
        $secondaryGuardian = Guardian::factory()->ibu()->create();

        $student->guardians()->attach($primaryGuardian->id, ['is_primary_contact' => true]);
        $student->guardians()->attach($secondaryGuardian->id, ['is_primary_contact' => false]);

        $primary = $student->primaryGuardian()->first();

        $this->assertEquals($primaryGuardian->id, $primary->id);
    }

    /**
     * Test student isActive helper method
     */
    public function test_student_is_active_helper(): void
    {
        $activeStudent = Student::factory()->create(['status' => 'aktif']);
        $inactiveStudent = Student::factory()->create(['status' => 'mutasi']);

        $this->assertTrue($activeStudent->isActive());
        $this->assertFalse($inactiveStudent->isActive());
    }

    /**
     * Test student getAge helper method
     */
    public function test_student_get_age_helper(): void
    {
        $student = Student::factory()->create([
            'tanggal_lahir' => now()->subYears(10),
        ]);

        $this->assertEquals(10, $student->getAge());
    }

    /**
     * Test student formatted NIS accessor
     */
    public function test_student_formatted_nis_accessor(): void
    {
        $student = Student::factory()->create(['nis' => '20240001']);

        $this->assertEquals('NIS-20240001', $student->formatted_nis);
    }

    /**
     * Test student active scope
     */
    public function test_student_active_scope(): void
    {
        Student::factory()->count(3)->create(['status' => 'aktif']);
        Student::factory()->count(2)->create(['status' => 'mutasi']);

        $activeStudents = Student::active()->get();

        $this->assertEquals(3, $activeStudents->count());
    }

    /**
     * Test student byClass scope
     */
    public function test_student_by_class_scope(): void
    {
        Student::factory()->count(3)->create(['kelas_id' => 1]);
        Student::factory()->count(2)->create(['kelas_id' => 2]);

        $class1Students = Student::byClass(1)->get();

        $this->assertEquals(3, $class1Students->count());
    }

    /**
     * Test student byAcademicYear scope
     */
    public function test_student_by_academic_year_scope(): void
    {
        Student::factory()->count(3)->create(['tahun_ajaran_masuk' => '2024/2025']);
        Student::factory()->count(2)->create(['tahun_ajaran_masuk' => '2023/2024']);

        $year2024Students = Student::byAcademicYear('2024/2025')->get();

        $this->assertEquals(3, $year2024Students->count());
    }

    /**
     * Test student search scope
     */
    public function test_student_search_scope(): void
    {
        Student::factory()->create(['nama_lengkap' => 'Budi Santoso', 'nis' => '20240001']);
        Student::factory()->create(['nama_lengkap' => 'Siti Rahayu', 'nis' => '20240002']);

        $searchByName = Student::search('Budi')->get();
        $searchByNis = Student::search('20240001')->get();

        $this->assertEquals(1, $searchByName->count());
        $this->assertEquals('Budi Santoso', $searchByName->first()->nama_lengkap);

        $this->assertEquals(1, $searchByNis->count());
        $this->assertEquals('20240001', $searchByNis->first()->nis);
    }

    /**
     * Test student dapat memiliki class history
     */
    public function test_student_can_have_class_history(): void
    {
        $student = Student::factory()->create();

        $student->classHistory()->create([
            'kelas_id' => 1,
            'tahun_ajaran' => '2024/2025',
            'wali_kelas' => 'Pak Budi',
        ]);

        $this->assertEquals(1, $student->classHistory()->count());
    }

    /**
     * Test student dapat memiliki status history
     */
    public function test_student_can_have_status_history(): void
    {
        $student = Student::factory()->create();
        $user = User::factory()->create();

        $student->statusHistory()->create([
            'status_lama' => 'aktif',
            'status_baru' => 'mutasi',
            'tanggal' => now(),
            'alasan' => 'Pindah tugas orang tua',
            'changed_by' => $user->id,
        ]);

        $this->assertEquals(1, $student->statusHistory()->count());
    }

    /**
     * Test student soft delete
     */
    public function test_student_soft_delete(): void
    {
        $student = Student::factory()->create();
        $studentId = $student->id;

        $student->delete();

        $this->assertSoftDeleted('students', ['id' => $studentId]);
        $this->assertNull(Student::find($studentId));
        $this->assertNotNull(Student::withTrashed()->find($studentId));
    }

    /**
     * Test guardian factory creates valid guardian
     */
    public function test_guardian_factory_creates_valid_guardian(): void
    {
        $guardian = Guardian::factory()->create();

        $this->assertNotNull($guardian->nik);
        $this->assertNotNull($guardian->nama_lengkap);
        $this->assertContains($guardian->hubungan, ['ayah', 'ibu', 'wali']);
    }

    /**
     * Test guardian hasPortalAccount helper
     */
    public function test_guardian_has_portal_account_helper(): void
    {
        $guardianWithAccount = Guardian::factory()->create([
            'user_id' => User::factory()->create()->id,
        ]);
        $guardianWithoutAccount = Guardian::factory()->create(['user_id' => null]);

        $this->assertTrue($guardianWithAccount->hasPortalAccount());
        $this->assertFalse($guardianWithoutAccount->hasPortalAccount());
    }

    /**
     * Test guardian hubungan label accessor
     */
    public function test_guardian_hubungan_label_accessor(): void
    {
        $ayah = Guardian::factory()->ayah()->create();
        $ibu = Guardian::factory()->ibu()->create();
        $wali = Guardian::factory()->wali()->create();

        $this->assertEquals('Ayah Kandung', $ayah->hubungan_label);
        $this->assertEquals('Ibu Kandung', $ibu->hubungan_label);
        $this->assertEquals('Wali', $wali->hubungan_label);
    }

    /**
     * Test guardian scopes
     */
    public function test_guardian_scopes(): void
    {
        Guardian::factory()->ayah()->count(2)->create();
        Guardian::factory()->ibu()->count(3)->create();
        Guardian::factory()->create(['user_id' => User::factory()->create()->id]);

        $ayahGuardians = Guardian::byRelation('ayah')->get();
        $withAccount = Guardian::withPortalAccount()->get();
        $withoutAccount = Guardian::withoutPortalAccount()->get();

        $this->assertEquals(2, $ayahGuardians->count());
        $this->assertEquals(1, $withAccount->count());
        $this->assertEquals(5, $withoutAccount->count());
    }

    /**
     * Test student class history belongs to student
     */
    public function test_student_class_history_belongs_to_student(): void
    {
        $student = Student::factory()->create();
        $history = StudentClassHistory::create([
            'student_id' => $student->id,
            'kelas_id' => 1,
            'tahun_ajaran' => '2024/2025',
        ]);

        $this->assertEquals($student->id, $history->student->id);
    }

    /**
     * Test student status history belongs to student and user
     */
    public function test_student_status_history_relationships(): void
    {
        $student = Student::factory()->create();
        $user = User::factory()->create();

        $history = StudentStatusHistory::create([
            'student_id' => $student->id,
            'status_lama' => 'aktif',
            'status_baru' => 'mutasi',
            'tanggal' => now(),
            'alasan' => 'Test',
            'changed_by' => $user->id,
        ]);

        $this->assertEquals($student->id, $history->student->id);
        $this->assertEquals($user->id, $history->changedBy->id);
    }

    /**
     * Test status history status label accessor
     */
    public function test_status_history_status_label_accessor(): void
    {
        $history = StudentStatusHistory::create([
            'student_id' => Student::factory()->create()->id,
            'status_lama' => 'aktif',
            'status_baru' => 'mutasi',
            'tanggal' => now(),
            'alasan' => 'Test',
            'changed_by' => User::factory()->create()->id,
        ]);

        $this->assertEquals('Mutasi', $history->status_baru_label);
    }
}
