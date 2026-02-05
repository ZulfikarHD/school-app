<?php

namespace Tests\Feature\Admin;

use App\Models\Subject;
use App\Models\Teacher;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class TeacherControllerTest extends TestCase
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
     * Test admin dapat melihat list teachers
     */
    public function test_admin_can_view_teachers_list(): void
    {
        Teacher::factory()->count(5)->create();

        $response = $this->actingAs($this->admin)
            ->get(route('admin.teachers.index'));

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page
            ->component('Admin/Teachers/Index')
            ->has('teachers.data', 5)
        );
    }

    /**
     * Test search functionality untuk teachers
     */
    public function test_admin_can_search_teachers(): void
    {
        Teacher::factory()->create(['nama_lengkap' => 'Budi Santoso, S.Pd.']);
        Teacher::factory()->create(['nama_lengkap' => 'Siti Rahayu, S.Pd.']);

        $response = $this->actingAs($this->admin)
            ->get(route('admin.teachers.index', ['search' => 'Budi']));

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page
            ->has('teachers.data', 1)
            ->where('teachers.data.0.nama_lengkap', 'Budi Santoso, S.Pd.')
        );
    }

    /**
     * Test filter teachers by status kepegawaian
     */
    public function test_admin_can_filter_teachers_by_status(): void
    {
        Teacher::factory()->count(3)->tetap()->create();
        Teacher::factory()->count(2)->honorer()->create();

        $response = $this->actingAs($this->admin)
            ->get(route('admin.teachers.index', ['status_kepegawaian' => 'honorer']));

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page
            ->has('teachers.data', 2)
        );
    }

    /**
     * Test filter teachers by is_active
     */
    public function test_admin_can_filter_teachers_by_active_status(): void
    {
        Teacher::factory()->count(3)->create(['is_active' => true]);
        Teacher::factory()->count(2)->inactive()->create();

        $response = $this->actingAs($this->admin)
            ->get(route('admin.teachers.index', ['is_active' => 'false']));

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page
            ->has('teachers.data', 2)
        );
    }

    /**
     * Test admin dapat view form create teacher
     */
    public function test_admin_can_view_create_teacher_form(): void
    {
        Subject::factory()->count(3)->create();

        $response = $this->actingAs($this->admin)
            ->get(route('admin.teachers.create'));

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page
            ->component('Admin/Teachers/Create')
            ->has('subjects', 3)
            ->has('statusOptions')
        );
    }

    /**
     * Test admin dapat create teacher baru dengan auto-create user account
     */
    public function test_admin_can_create_teacher_with_user_account(): void
    {
        Storage::fake('public');
        $subject = Subject::factory()->create();

        $teacherData = [
            'nik' => '3201234567890123',
            'nama_lengkap' => 'Ahmad Wijaya, S.Pd.',
            'tempat_lahir' => 'Jakarta',
            'tanggal_lahir' => '1985-05-15',
            'jenis_kelamin' => 'L',
            'alamat' => 'Jl. Guru No. 123',
            'no_hp' => '08123456789',
            'email' => 'ahmad.wijaya@sekolah.sch.id',
            'status_kepegawaian' => 'tetap',
            'nip' => '198505152010011001',
            'tanggal_mulai_kerja' => '2010-01-01',
            'kualifikasi_pendidikan' => 'S1',
            'subjects' => [$subject->id],
        ];

        $response = $this->actingAs($this->admin)
            ->post(route('admin.teachers.store'), $teacherData);

        $response->assertRedirect(route('admin.teachers.index'));
        $response->assertSessionHas('success');

        // Verify teacher created
        $this->assertDatabaseHas('teachers', [
            'nama_lengkap' => 'Ahmad Wijaya, S.Pd.',
            'nik' => '3201234567890123',
            'nip' => '198505152010011001',
            'status_kepegawaian' => 'tetap',
        ]);

        // Verify user account created
        $this->assertDatabaseHas('users', [
            'email' => 'ahmad.wijaya@sekolah.sch.id',
            'role' => 'TEACHER',
            'status' => 'ACTIVE',
        ]);

        // Verify subject assigned
        $teacher = Teacher::where('nik', '3201234567890123')->first();
        $this->assertTrue($teacher->subjects()->where('subject_id', $subject->id)->exists());
    }

    /**
     * Test validation NIP tidak wajib untuk guru honorer
     */
    public function test_nip_not_required_for_honorer(): void
    {
        $teacherData = [
            'nik' => '3201234567890124',
            'nama_lengkap' => 'Dewi Lestari, S.Pd.',
            'jenis_kelamin' => 'P',
            'email' => 'dewi.lestari@sekolah.sch.id',
            'status_kepegawaian' => 'honorer',
        ];

        $response = $this->actingAs($this->admin)
            ->post(route('admin.teachers.store'), $teacherData);

        $response->assertRedirect(route('admin.teachers.index'));

        $this->assertDatabaseHas('teachers', [
            'nik' => '3201234567890124',
            'nip' => null,
            'status_kepegawaian' => 'honorer',
        ]);
    }

    /**
     * Test tanggal_berakhir_kontrak wajib untuk guru kontrak
     */
    public function test_contract_end_date_required_for_kontrak(): void
    {
        $teacherData = [
            'nik' => '3201234567890125',
            'nama_lengkap' => 'Eko Prasetyo, S.Pd.',
            'jenis_kelamin' => 'L',
            'email' => 'eko.prasetyo@sekolah.sch.id',
            'status_kepegawaian' => 'kontrak',
            'nip' => '201234567890123456',
            'tanggal_mulai_kerja' => '2024-01-01',
            // Missing tanggal_berakhir_kontrak
        ];

        $response = $this->actingAs($this->admin)
            ->post(route('admin.teachers.store'), $teacherData);

        $response->assertSessionHasErrors('tanggal_berakhir_kontrak');
    }

    /**
     * Test validation untuk NIK yang sudah terdaftar
     */
    public function test_cannot_create_teacher_with_duplicate_nik(): void
    {
        Teacher::factory()->create(['nik' => '3201234567890123']);

        $teacherData = [
            'nik' => '3201234567890123', // Duplicate
            'nama_lengkap' => 'Test Teacher',
            'jenis_kelamin' => 'L',
            'email' => 'test@sekolah.sch.id',
            'status_kepegawaian' => 'honorer',
        ];

        $response = $this->actingAs($this->admin)
            ->post(route('admin.teachers.store'), $teacherData);

        $response->assertSessionHasErrors('nik');
    }

    /**
     * Test validation untuk email yang sudah terdaftar
     */
    public function test_cannot_create_teacher_with_duplicate_email(): void
    {
        Teacher::factory()->create(['email' => 'existing@sekolah.sch.id']);

        $teacherData = [
            'nik' => '3201234567890126',
            'nama_lengkap' => 'Test Teacher',
            'jenis_kelamin' => 'L',
            'email' => 'existing@sekolah.sch.id', // Duplicate
            'status_kepegawaian' => 'honorer',
        ];

        $response = $this->actingAs($this->admin)
            ->post(route('admin.teachers.store'), $teacherData);

        $response->assertSessionHasErrors('email');
    }

    /**
     * Test admin dapat view edit teacher form
     */
    public function test_admin_can_view_edit_teacher_form(): void
    {
        $teacher = Teacher::factory()->create();
        Subject::factory()->count(3)->create();

        $response = $this->actingAs($this->admin)
            ->get(route('admin.teachers.edit', $teacher));

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page
            ->component('Admin/Teachers/Edit')
            ->has('teacher')
            ->where('teacher.id', $teacher->id)
        );
    }

    /**
     * Test admin dapat update teacher data
     */
    public function test_admin_can_update_teacher(): void
    {
        $teacher = Teacher::factory()->create([
            'nama_lengkap' => 'Old Name',
            'status_kepegawaian' => 'honorer',
        ]);

        $updateData = [
            'nik' => $teacher->nik,
            'nama_lengkap' => 'Updated Name, S.Pd., M.Pd.',
            'jenis_kelamin' => $teacher->jenis_kelamin,
            'email' => $teacher->email,
            'status_kepegawaian' => 'tetap',
            'nip' => '198505152010011002',
        ];

        $response = $this->actingAs($this->admin)
            ->put(route('admin.teachers.update', $teacher), $updateData);

        $response->assertRedirect(route('admin.teachers.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('teachers', [
            'id' => $teacher->id,
            'nama_lengkap' => 'Updated Name, S.Pd., M.Pd.',
            'status_kepegawaian' => 'tetap',
            'nip' => '198505152010011002',
        ]);
    }

    /**
     * Test admin dapat toggle status aktif teacher
     */
    public function test_admin_can_toggle_teacher_status(): void
    {
        $teacher = Teacher::factory()->create(['is_active' => true]);
        $user = User::factory()->create(['role' => 'TEACHER']);
        $teacher->update(['user_id' => $user->id]);

        $response = $this->actingAs($this->admin)
            ->patch(route('admin.teachers.toggle-status', $teacher));

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('teachers', [
            'id' => $teacher->id,
            'is_active' => false,
        ]);

        // Verify linked user status also updated
        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'status' => 'INACTIVE',
        ]);
    }

    /**
     * Test admin dapat mengaktifkan kembali teacher yang nonaktif
     */
    public function test_admin_can_reactivate_teacher(): void
    {
        $teacher = Teacher::factory()->inactive()->create();
        $user = User::factory()->create(['role' => 'TEACHER', 'status' => 'INACTIVE']);
        $teacher->update(['user_id' => $user->id]);

        $response = $this->actingAs($this->admin)
            ->patch(route('admin.teachers.toggle-status', $teacher));

        $response->assertRedirect();

        $this->assertDatabaseHas('teachers', [
            'id' => $teacher->id,
            'is_active' => true,
        ]);

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'status' => 'ACTIVE',
        ]);
    }

    /**
     * Test foto upload untuk teacher
     */
    public function test_admin_can_upload_teacher_photo(): void
    {
        Storage::fake('public');

        $teacherData = [
            'nik' => '3201234567890127',
            'nama_lengkap' => 'Test Photo Teacher',
            'jenis_kelamin' => 'L',
            'email' => 'photo.test@sekolah.sch.id',
            'status_kepegawaian' => 'honorer',
            'foto' => UploadedFile::fake()->image('teacher.jpg', 300, 400)->size(1024),
        ];

        $response = $this->actingAs($this->admin)
            ->post(route('admin.teachers.store'), $teacherData);

        $response->assertRedirect();

        $teacher = Teacher::where('nik', '3201234567890127')->first();
        $this->assertNotNull($teacher->foto);

        Storage::disk('public')->assertExists($teacher->foto);
    }

    /**
     * Test non-admin tidak bisa akses teacher management
     */
    public function test_non_admin_cannot_access_teacher_management(): void
    {
        $teacher = User::factory()->create(['role' => 'TEACHER']);

        $response = $this->actingAs($teacher)
            ->get(route('admin.teachers.index'));

        $response->assertStatus(403);
    }

    /**
     * Test superadmin dapat akses teacher management
     */
    public function test_superadmin_can_access_teacher_management(): void
    {
        $superadmin = User::factory()->create(['role' => 'SUPERADMIN']);
        Teacher::factory()->count(3)->create();

        $response = $this->actingAs($superadmin)
            ->get(route('admin.teachers.index'));

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page
            ->component('Admin/Teachers/Index')
        );
    }

    /**
     * Test activity log dibuat saat create teacher
     */
    public function test_activity_log_created_when_creating_teacher(): void
    {
        $teacherData = [
            'nik' => '3201234567890128',
            'nama_lengkap' => 'Activity Log Test',
            'jenis_kelamin' => 'L',
            'email' => 'activity.test@sekolah.sch.id',
            'status_kepegawaian' => 'honorer',
        ];

        $this->actingAs($this->admin)
            ->post(route('admin.teachers.store'), $teacherData);

        $this->assertDatabaseHas('activity_logs', [
            'user_id' => $this->admin->id,
            'action' => 'create_teacher',
            'status' => 'success',
        ]);
    }

    /**
     * Test activity log dibuat saat toggle status
     */
    public function test_activity_log_created_when_toggling_status(): void
    {
        $teacher = Teacher::factory()->create(['is_active' => true]);

        $this->actingAs($this->admin)
            ->patch(route('admin.teachers.toggle-status', $teacher));

        $this->assertDatabaseHas('activity_logs', [
            'user_id' => $this->admin->id,
            'action' => 'toggle_teacher_status',
            'status' => 'success',
        ]);
    }
}
