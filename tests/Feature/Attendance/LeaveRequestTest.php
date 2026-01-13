<?php

namespace Tests\Feature\Attendance;

use App\Models\Guardian;
use App\Models\LeaveRequest;
use App\Models\SchoolClass;
use App\Models\Student;
use App\Models\StudentAttendance;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class LeaveRequestTest extends TestCase
{
    use RefreshDatabase;

    protected User $parent;

    protected User $teacher;

    protected User $admin;

    protected Student $student;

    protected SchoolClass $class;

    protected function setUp(): void
    {
        parent::setUp();

        // Create test users
        $this->parent = User::factory()->create(['role' => 'PARENT']);
        $this->teacher = User::factory()->create(['role' => 'TEACHER']);
        $this->admin = User::factory()->create(['role' => 'ADMIN']);

        // Create class
        $this->class = SchoolClass::factory()->create([
            'wali_kelas_id' => $this->teacher->id,
        ]);

        // Create student with parent
        $this->student = Student::factory()->create([
            'kelas_id' => $this->class->id,
            'status' => 'aktif',
        ]);

        // Link parent to student
        $guardian = Guardian::factory()->create(['user_id' => $this->parent->id]);
        $guardian->students()->attach($this->student->id, [
            'relationship' => 'AYAH',
            'is_primary' => true,
        ]);
    }

    /** @test LRT-001 */
    public function parent_can_submit_leave_request_for_their_child()
    {
        $response = $this->actingAs($this->parent)->post('/parent/leave-requests', [
            'student_id' => $this->student->id,
            'jenis' => 'IZIN',
            'tanggal_mulai' => now()->addDay()->format('Y-m-d'),
            'tanggal_selesai' => now()->addDays(2)->format('Y-m-d'),
            'alasan' => 'Acara keluarga',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('leave_requests', [
            'student_id' => $this->student->id,
            'jenis' => 'IZIN',
            'status' => 'PENDING',
            'submitted_by' => $this->parent->id,
        ]);
    }

    /** @test LRT-002 */
    public function parent_cannot_submit_leave_for_other_students()
    {
        $otherStudent = Student::factory()->create(['status' => 'aktif']);

        $response = $this->actingAs($this->parent)->post('/parent/leave-requests', [
            'student_id' => $otherStudent->id,
            'jenis' => 'SAKIT',
            'tanggal_mulai' => now()->addDay()->format('Y-m-d'),
            'tanggal_selesai' => now()->addDay()->format('Y-m-d'),
            'alasan' => 'Sakit demam',
        ]);

        $response->assertStatus(500); // Exception thrown
        $this->assertDatabaseMissing('leave_requests', [
            'student_id' => $otherStudent->id,
        ]);
    }

    /** @test LRT-003 */
    public function teacher_can_approve_leave_request()
    {
        $leaveRequest = LeaveRequest::factory()->create([
            'student_id' => $this->student->id,
            'status' => 'PENDING',
        ]);

        $response = $this->actingAs($this->teacher)->post("/teacher/leave-requests/{$leaveRequest->id}/approve", [
            'action' => 'approve',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('leave_requests', [
            'id' => $leaveRequest->id,
            'status' => 'APPROVED',
            'reviewed_by' => $this->teacher->id,
        ]);
    }

    /** @test LRT-004 */
    public function teacher_can_reject_leave_request()
    {
        $leaveRequest = LeaveRequest::factory()->create([
            'student_id' => $this->student->id,
            'status' => 'PENDING',
        ]);

        $response = $this->actingAs($this->teacher)->post("/teacher/leave-requests/{$leaveRequest->id}/approve", [
            'action' => 'reject',
            'rejection_reason' => 'Alasan tidak valid',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('leave_requests', [
            'id' => $leaveRequest->id,
            'status' => 'REJECTED',
            'reviewed_by' => $this->teacher->id,
            'rejection_reason' => 'Alasan tidak valid',
        ]);
    }

    /** @test LRT-005 */
    public function approved_leave_auto_creates_attendance_records()
    {
        $startDate = now()->addDay();
        $endDate = now()->addDays(3); // 3 days

        $leaveRequest = LeaveRequest::factory()->create([
            'student_id' => $this->student->id,
            'jenis' => 'SAKIT',
            'tanggal_mulai' => $startDate->format('Y-m-d'),
            'tanggal_selesai' => $endDate->format('Y-m-d'),
            'status' => 'PENDING',
        ]);

        $this->actingAs($this->teacher)->post("/teacher/leave-requests/{$leaveRequest->id}/approve", [
            'action' => 'approve',
        ]);

        // Should create attendance records for weekdays only
        $expectedDays = 0;
        $current = $startDate->copy();
        while ($current->lte($endDate)) {
            if (! $current->isWeekend()) {
                $expectedDays++;
            }
            $current->addDay();
        }

        $attendanceCount = StudentAttendance::where('student_id', $this->student->id)
            ->where('status', 'S')
            ->count();

        $this->assertEquals($expectedDays, $attendanceCount);
    }

    /** @test LRT-006 */
    public function date_range_creates_multiple_records_excluding_weekends()
    {
        // Create a 5-day leave (Mon-Fri)
        $monday = now()->next('Monday');
        $friday = $monday->copy()->addDays(4);

        $leaveRequest = LeaveRequest::factory()->create([
            'student_id' => $this->student->id,
            'jenis' => 'IZIN',
            'tanggal_mulai' => $monday->format('Y-m-d'),
            'tanggal_selesai' => $friday->format('Y-m-d'),
            'status' => 'PENDING',
        ]);

        $this->actingAs($this->teacher)->post("/teacher/leave-requests/{$leaveRequest->id}/approve", [
            'action' => 'approve',
        ]);

        // Should create 5 attendance records (Mon-Fri, no weekends)
        $attendanceCount = StudentAttendance::where('student_id', $this->student->id)
            ->where('status', 'I')
            ->count();

        $this->assertEquals(5, $attendanceCount);
    }

    /** @test LRT-007 */
    public function attachment_upload_works()
    {
        Storage::fake('public');

        $file = UploadedFile::fake()->image('surat-dokter.jpg');

        $response = $this->actingAs($this->parent)->post('/parent/leave-requests', [
            'student_id' => $this->student->id,
            'jenis' => 'SAKIT',
            'tanggal_mulai' => now()->addDay()->format('Y-m-d'),
            'tanggal_selesai' => now()->addDay()->format('Y-m-d'),
            'alasan' => 'Sakit demam',
            'attachment' => $file,
        ]);

        $leaveRequest = LeaveRequest::where('student_id', $this->student->id)->first();
        $this->assertNotNull($leaveRequest->attachment_path);
        Storage::disk('public')->assertExists($leaveRequest->attachment_path);
    }

    /** @test LRT-008 */
    public function rejection_reason_is_required_when_rejecting()
    {
        $leaveRequest = LeaveRequest::factory()->create([
            'student_id' => $this->student->id,
            'status' => 'PENDING',
        ]);

        $response = $this->actingAs($this->teacher)->post("/teacher/leave-requests/{$leaveRequest->id}/approve", [
            'action' => 'reject',
            'rejection_reason' => '', // Empty reason
        ]);

        $response->assertSessionHasErrors('rejection_reason');
    }

    /** @test LRT-009 */
    public function admin_can_approve_any_leave_request()
    {
        $leaveRequest = LeaveRequest::factory()->create([
            'student_id' => $this->student->id,
            'status' => 'PENDING',
        ]);

        $response = $this->actingAs($this->admin)->post("/teacher/leave-requests/{$leaveRequest->id}/approve", [
            'action' => 'approve',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('leave_requests', [
            'id' => $leaveRequest->id,
            'status' => 'APPROVED',
        ]);
    }

    /** @test LRT-010 */
    public function validation_ensures_end_date_after_start_date()
    {
        $response = $this->actingAs($this->parent)->post('/parent/leave-requests', [
            'student_id' => $this->student->id,
            'jenis' => 'IZIN',
            'tanggal_mulai' => now()->addDays(5)->format('Y-m-d'),
            'tanggal_selesai' => now()->addDay()->format('Y-m-d'), // Before start date
            'alasan' => 'Test',
        ]);

        $response->assertSessionHasErrors('tanggal_selesai');
    }
}
