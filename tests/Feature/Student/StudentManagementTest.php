<?php

namespace Tests\Feature\Student;

use App\Models\Guardian;
use App\Models\Student;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class StudentManagementTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        // Create admin user untuk testing
        $this->admin = User::factory()->create([
            'role' => 'ADMIN',
            'status' => 'ACTIVE',
        ]);
    }

    /**
     * Test admin dapat melihat list students
     */
    public function test_admin_can_view_students_list(): void
    {
        Student::factory()->count(5)->create();

        $response = $this->actingAs($this->admin)
            ->get(route('admin.students.index'));

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page
            ->component('Admin/Students/Index')
            ->has('students.data', 5)
        );
    }

    /**
     * Test search functionality untuk students
     */
    public function test_admin_can_search_students(): void
    {
        Student::factory()->create(['nama_lengkap' => 'Budi Santoso']);
        Student::factory()->create(['nama_lengkap' => 'Siti Rahayu']);

        $response = $this->actingAs($this->admin)
            ->get(route('admin.students.index', ['search' => 'Budi']));

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page
            ->has('students.data', 1)
            ->where('students.data.0.nama_lengkap', 'Budi Santoso')
        );
    }

    /**
     * Test filter students by status
     */
    public function test_admin_can_filter_students_by_status(): void
    {
        Student::factory()->count(3)->create(['status' => 'aktif']);
        Student::factory()->count(2)->create(['status' => 'mutasi']);

        $response = $this->actingAs($this->admin)
            ->get(route('admin.students.index', ['status' => 'mutasi']));

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page
            ->has('students.data', 2)
        );
    }

    /**
     * Test admin dapat view form create student
     */
    public function test_admin_can_view_create_student_form(): void
    {
        $response = $this->actingAs($this->admin)
            ->get(route('admin.students.create'));

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page
            ->component('Admin/Students/Create')
        );
    }

    /**
     * Test admin dapat create student baru dengan auto-generate NIS
     */
    public function test_admin_can_create_student_with_auto_generated_nis(): void
    {
        Storage::fake('public');

        $studentData = [
            'nisn' => '1234567890',
            'nik' => '1234567890123456',
            'nama_lengkap' => 'Test Student',
            'nama_panggilan' => 'Test',
            'jenis_kelamin' => 'L',
            'tempat_lahir' => 'Jakarta',
            'tanggal_lahir' => '2015-01-01',
            'agama' => 'Islam',
            'anak_ke' => 1,
            'jumlah_saudara' => 2,
            'status_keluarga' => 'Anak Kandung',
            'alamat' => 'Jl. Test No. 123',
            'kelurahan' => 'Test',
            'kecamatan' => 'Test',
            'kota' => 'Jakarta',
            'provinsi' => 'DKI Jakarta',
            'tahun_ajaran_masuk' => '2024/2025',
            'tanggal_masuk' => '2024-07-01',
            'ayah' => [
                'nik' => '1234567890123457',
                'nama_lengkap' => 'Bapak Test',
                'pekerjaan' => 'PNS',
                'pendidikan' => 'S1',
                'penghasilan' => '3-5jt',
                'no_hp' => '081234567890',
                'is_primary_contact' => true,
            ],
            'ibu' => [
                'nik' => '1234567890123458',
                'nama_lengkap' => 'Ibu Test',
                'pekerjaan' => 'Ibu Rumah Tangga',
                'pendidikan' => 'SMA',
                'penghasilan' => '<1jt',
                'no_hp' => '081234567891',
            ],
        ];

        $response = $this->actingAs($this->admin)
            ->post(route('admin.students.store'), $studentData);

        $response->assertRedirect(route('admin.students.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('students', [
            'nama_lengkap' => 'Test Student',
            'nis' => '20240001', // Auto-generated
        ]);

        // Verify guardians created
        $this->assertDatabaseHas('guardians', [
            'nama_lengkap' => 'Bapak Test',
            'hubungan' => 'ayah',
        ]);

        // Verify parent account created
        $this->assertDatabaseHas('users', [
            'username' => '081234567890',
            'role' => 'PARENT',
        ]);
    }

    /**
     * Test validation untuk NIK yang sudah terdaftar
     */
    public function test_cannot_create_student_with_duplicate_nik(): void
    {
        Student::factory()->create(['nik' => '1234567890123456']);

        $studentData = [
            'nik' => '1234567890123456', // Duplicate
            'nisn' => '1234567890',
            'nama_lengkap' => 'Test Student',
            // ... other required fields
        ];

        $response = $this->actingAs($this->admin)
            ->post(route('admin.students.store'), $studentData);

        $response->assertSessionHasErrors('nik');
    }

    /**
     * Test admin dapat view detail student
     */
    public function test_admin_can_view_student_detail(): void
    {
        $student = Student::factory()->create();
        $guardian = Guardian::factory()->ayah()->create();
        $student->guardians()->attach($guardian->id);

        $response = $this->actingAs($this->admin)
            ->get(route('admin.students.show', $student));

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page
            ->component('Admin/Students/Show')
            ->has('student')
            ->where('student.id', $student->id)
        );
    }

    /**
     * Test admin dapat update student data
     */
    public function test_admin_can_update_student(): void
    {
        $student = Student::factory()->create(['nama_lengkap' => 'Old Name']);

        $updateData = [
            'nisn' => $student->nisn,
            'nik' => $student->nik,
            'nama_lengkap' => 'Updated Name',
            'nama_panggilan' => $student->nama_panggilan,
            'jenis_kelamin' => $student->jenis_kelamin,
            'tempat_lahir' => $student->tempat_lahir,
            'tanggal_lahir' => $student->tanggal_lahir->format('Y-m-d'),
            'agama' => $student->agama,
            'anak_ke' => $student->anak_ke,
            'jumlah_saudara' => $student->jumlah_saudara,
            'status_keluarga' => $student->status_keluarga,
            'alamat' => $student->alamat,
            'kelurahan' => $student->kelurahan,
            'kecamatan' => $student->kecamatan,
            'kota' => $student->kota,
            'provinsi' => $student->provinsi,
            'ayah' => [
                'nik' => '1234567890123457',
                'nama_lengkap' => 'Bapak Test',
                'pekerjaan' => 'PNS',
                'pendidikan' => 'S1',
                'penghasilan' => '3-5jt',
                'no_hp' => '081234567890',
            ],
            'ibu' => [
                'nik' => '1234567890123458',
                'nama_lengkap' => 'Ibu Test',
                'pekerjaan' => 'Ibu Rumah Tangga',
                'pendidikan' => 'SMA',
                'penghasilan' => '<1jt',
            ],
        ];

        $response = $this->actingAs($this->admin)
            ->put(route('admin.students.update', $student), $updateData);

        $response->assertRedirect(route('admin.students.index'));

        $this->assertDatabaseHas('students', [
            'id' => $student->id,
            'nama_lengkap' => 'Updated Name',
        ]);
    }

    /**
     * Test admin dapat soft delete student
     */
    public function test_admin_can_delete_student(): void
    {
        $student = Student::factory()->create();

        $response = $this->actingAs($this->admin)
            ->delete(route('admin.students.destroy', $student));

        $response->assertRedirect(route('admin.students.index'));

        $this->assertSoftDeleted('students', [
            'id' => $student->id,
        ]);
    }

    /**
     * Test admin dapat update student status
     */
    public function test_admin_can_update_student_status(): void
    {
        $student = Student::factory()->create(['status' => 'aktif']);

        $statusData = [
            'status' => 'mutasi',
            'tanggal' => now()->format('Y-m-d'),
            'alasan' => 'Pindah ke luar kota karena orang tua pindah tugas',
            'sekolah_tujuan' => 'SDN 01 Jakarta',
        ];

        $response = $this->actingAs($this->admin)
            ->post(route('admin.students.update-status', $student), $statusData);

        $response->assertRedirect();

        $this->assertDatabaseHas('students', [
            'id' => $student->id,
            'status' => 'mutasi',
        ]);

        $this->assertDatabaseHas('student_status_history', [
            'student_id' => $student->id,
            'status_lama' => 'aktif',
            'status_baru' => 'mutasi',
        ]);
    }

    /**
     * Test bulk promote students
     */
    public function test_admin_can_bulk_promote_students(): void
    {
        $students = Student::factory()->count(3)->create([
            'kelas_id' => 1,
            'status' => 'aktif',
        ]);

        $promoteData = [
            'student_ids' => $students->pluck('id')->toArray(),
            'kelas_id_baru' => 2,
            'tahun_ajaran_baru' => '2025/2026',
            'wali_kelas' => 'Pak Budi',
        ];

        $response = $this->actingAs($this->admin)
            ->post(route('admin.students.promote'), $promoteData);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        foreach ($students as $student) {
            $this->assertDatabaseHas('students', [
                'id' => $student->id,
                'kelas_id' => 2,
            ]);

            $this->assertDatabaseHas('student_class_history', [
                'student_id' => $student->id,
                'kelas_id' => 2,
                'tahun_ajaran' => '2025/2026',
            ]);
        }
    }

    /**
     * Test non-admin tidak bisa akses student management
     */
    public function test_non_admin_cannot_access_student_management(): void
    {
        $teacher = User::factory()->create(['role' => 'TEACHER']);

        $response = $this->actingAs($teacher)
            ->get(route('admin.students.index'));

        $response->assertStatus(403);
    }

    /**
     * Test foto upload untuk student
     */
    public function test_admin_can_upload_student_photo(): void
    {
        Storage::fake('public');

        $studentData = [
            'nisn' => '1234567890',
            'nik' => '1234567890123456',
            'nama_lengkap' => 'Test Student',
            'nama_panggilan' => 'Test',
            'jenis_kelamin' => 'L',
            'tempat_lahir' => 'Jakarta',
            'tanggal_lahir' => '2015-01-01',
            'agama' => 'Islam',
            'anak_ke' => 1,
            'jumlah_saudara' => 2,
            'status_keluarga' => 'Anak Kandung',
            'alamat' => 'Jl. Test No. 123',
            'kelurahan' => 'Test',
            'kecamatan' => 'Test',
            'kota' => 'Jakarta',
            'provinsi' => 'DKI Jakarta',
            'tahun_ajaran_masuk' => '2024/2025',
            'tanggal_masuk' => '2024-07-01',
            'foto' => UploadedFile::fake()->image('student.jpg', 300, 400)->size(1024),
            'ayah' => [
                'nik' => '1234567890123457',
                'nama_lengkap' => 'Bapak Test',
                'pekerjaan' => 'PNS',
                'pendidikan' => 'S1',
                'penghasilan' => '3-5jt',
                'no_hp' => '081234567890',
            ],
            'ibu' => [
                'nik' => '1234567890123458',
                'nama_lengkap' => 'Ibu Test',
                'pekerjaan' => 'Ibu Rumah Tangga',
                'pendidikan' => 'SMA',
                'penghasilan' => '<1jt',
            ],
        ];

        $response = $this->actingAs($this->admin)
            ->post(route('admin.students.store'), $studentData);

        $response->assertRedirect();

        $student = Student::where('nama_lengkap', 'Test Student')->first();
        $this->assertNotNull($student->foto);

        Storage::disk('public')->assertExists($student->foto);
    }
}
