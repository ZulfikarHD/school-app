<?php

namespace Tests\Feature\Attendance;

use App\Models\TeacherLeave;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class TeacherLeaveTest extends TestCase
{
    use RefreshDatabase;

    protected User $teacher;

    protected User $admin;

    protected User $principal;

    protected User $otherTeacher;

    protected function setUp(): void
    {
        parent::setUp();

        $this->teacher = User::factory()->create(['role' => 'TEACHER']);
        $this->admin = User::factory()->create(['role' => 'ADMIN']);
        $this->principal = User::factory()->create(['role' => 'PRINCIPAL']);
        $this->otherTeacher = User::factory()->create(['role' => 'TEACHER']);
    }

    /** @test TLT-001 */
    public function teacher_can_submit_leave_request()
    {
        Storage::fake('public');

        $file = UploadedFile::fake()->image('surat-izin.jpg');

        $response = $this->actingAs($this->teacher)->post('/teacher/leaves', [
            'jenis' => 'CUTI',
            'tanggal_mulai' => now()->addDay()->format('Y-m-d'),
            'tanggal_selesai' => now()->addDays(3)->format('Y-m-d'),
            'alasan' => 'Cuti tahunan',
            'attachment' => $file,
        ]);

        $response->assertRedirect();

        $this->assertDatabaseHas('teacher_leaves', [
            'teacher_id' => $this->teacher->id,
            'jenis' => 'CUTI',
            'status' => 'PENDING',
            'jumlah_hari' => 3, // 3 weekdays
        ]);

        $leave = TeacherLeave::where('teacher_id', $this->teacher->id)->first();
        $this->assertNotNull($leave->attachment_path);
        Storage::disk('public')->assertExists($leave->attachment_path);
    }

    /** @test TLT-002 */
    public function admin_can_approve_teacher_leave()
    {
        $leave = TeacherLeave::factory()->create([
            'teacher_id' => $this->teacher->id,
            'status' => 'PENDING',
        ]);

        $response = $this->actingAs($this->admin)->post("/admin/teacher-leaves/{$leave->id}/approve");

        $response->assertRedirect();

        $this->assertDatabaseHas('teacher_leaves', [
            'id' => $leave->id,
            'status' => 'APPROVED',
            'approved_by' => $this->admin->id,
        ]);
    }

    /** @test TLT-003 */
    public function principal_can_approve_teacher_leave()
    {
        $leave = TeacherLeave::factory()->create([
            'teacher_id' => $this->teacher->id,
            'status' => 'PENDING',
        ]);

        $response = $this->actingAs($this->principal)->post("/principal/teacher-leaves/{$leave->id}/approve");

        $response->assertRedirect();

        $this->assertDatabaseHas('teacher_leaves', [
            'id' => $leave->id,
            'status' => 'APPROVED',
            'approved_by' => $this->principal->id,
        ]);
    }

    /** @test TLT-004 */
    public function teacher_cannot_approve_other_teacher_leave()
    {
        $leave = TeacherLeave::factory()->create([
            'teacher_id' => $this->teacher->id,
            'status' => 'PENDING',
        ]);

        $response = $this->actingAs($this->otherTeacher)->post("/principal/teacher-leaves/{$leave->id}/approve");

        $response->assertStatus(403); // Forbidden
    }

    /** @test */
    public function jumlah_hari_is_calculated_excluding_weekends()
    {
        // Create a leave from Monday to Friday (5 days)
        $monday = now()->next('Monday');
        $friday = $monday->copy()->addDays(4);

        $response = $this->actingAs($this->teacher)->post('/teacher/leaves', [
            'jenis' => 'IZIN',
            'tanggal_mulai' => $monday->format('Y-m-d'),
            'tanggal_selesai' => $friday->format('Y-m-d'),
            'alasan' => 'Keperluan keluarga',
        ]);

        $leave = TeacherLeave::where('teacher_id', $this->teacher->id)->first();

        $this->assertEquals(5, $leave->jumlah_hari);
    }

    /** @test */
    public function principal_can_reject_teacher_leave()
    {
        $leave = TeacherLeave::factory()->create([
            'teacher_id' => $this->teacher->id,
            'status' => 'PENDING',
        ]);

        $response = $this->actingAs($this->principal)->post("/principal/teacher-leaves/{$leave->id}/reject", [
            'rejection_reason' => 'Periode sibuk, tidak bisa izin',
        ]);

        $response->assertRedirect();

        $this->assertDatabaseHas('teacher_leaves', [
            'id' => $leave->id,
            'status' => 'REJECTED',
            'approved_by' => $this->principal->id,
            'rejection_reason' => 'Periode sibuk, tidak bisa izin',
        ]);
    }

    /** @test */
    public function teacher_can_view_own_leave_history()
    {
        TeacherLeave::factory()->count(3)->create([
            'teacher_id' => $this->teacher->id,
        ]);

        $response = $this->actingAs($this->teacher)->get('/teacher/leaves');

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page
            ->component('Teacher/Leave/Index')
            ->has('leaves.data', 3)
        );
    }

    /** @test */
    public function principal_can_view_all_pending_leaves()
    {
        TeacherLeave::factory()->count(5)->create(['status' => 'PENDING']);
        TeacherLeave::factory()->count(3)->create(['status' => 'APPROVED']);

        $response = $this->actingAs($this->principal)->get('/principal/teacher-leaves');

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page
            ->component('Principal/TeacherLeave/Index')
            ->has('leaves.data', 5) // Only pending by default
        );
    }

    /** @test */
    public function leave_types_are_validated()
    {
        $response = $this->actingAs($this->teacher)->post('/teacher/leaves', [
            'jenis' => 'INVALID_TYPE',
            'tanggal_mulai' => now()->addDay()->format('Y-m-d'),
            'tanggal_selesai' => now()->addDays(2)->format('Y-m-d'),
            'alasan' => 'Test',
        ]);

        $response->assertSessionHasErrors('jenis');
    }

    /** @test */
    public function alasan_is_required()
    {
        $response = $this->actingAs($this->teacher)->post('/teacher/leaves', [
            'jenis' => 'SAKIT',
            'tanggal_mulai' => now()->addDay()->format('Y-m-d'),
            'tanggal_selesai' => now()->addDay()->format('Y-m-d'),
            'alasan' => '', // Empty reason
        ]);

        $response->assertSessionHasErrors('alasan');
    }
}
