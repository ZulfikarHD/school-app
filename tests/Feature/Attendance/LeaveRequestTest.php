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

    /** @test LRT-011 */
    public function cannot_submit_overlapping_leave_request_for_same_dates()
    {
        // Create first leave request (PENDING)
        LeaveRequest::factory()->create([
            'student_id' => $this->student->id,
            'jenis' => 'SAKIT',
            'tanggal_mulai' => now()->addDays(5)->format('Y-m-d'),
            'tanggal_selesai' => now()->addDays(7)->format('Y-m-d'),
            'status' => 'PENDING',
        ]);

        // Try to create overlapping request (exact same dates)
        $response = $this->actingAs($this->parent)->post('/parent/leave-requests', [
            'student_id' => $this->student->id,
            'jenis' => 'IZIN',
            'tanggal_mulai' => now()->addDays(5)->format('Y-m-d'),
            'tanggal_selesai' => now()->addDays(7)->format('Y-m-d'),
            'alasan' => 'Alasan lain',
        ]);

        $response->assertSessionHasErrors('tanggal_mulai');
    }

    /** @test LRT-012 */
    public function cannot_submit_overlapping_leave_request_for_date_ranges()
    {
        // Create first leave request (APPROVED)
        LeaveRequest::factory()->create([
            'student_id' => $this->student->id,
            'jenis' => 'SAKIT',
            'tanggal_mulai' => now()->addDays(10)->format('Y-m-d'),
            'tanggal_selesai' => now()->addDays(15)->format('Y-m-d'),
            'status' => 'APPROVED',
        ]);

        // Try to create partially overlapping request
        $response = $this->actingAs($this->parent)->post('/parent/leave-requests', [
            'student_id' => $this->student->id,
            'jenis' => 'IZIN',
            'tanggal_mulai' => now()->addDays(12)->format('Y-m-d'), // Overlaps with existing
            'tanggal_selesai' => now()->addDays(18)->format('Y-m-d'),
            'alasan' => 'Alasan lain yang panjang untuk memenuhi validasi minimal',
        ]);

        $response->assertSessionHasErrors('tanggal_mulai');
    }

    /** @test LRT-013 */
    public function can_submit_leave_request_after_existing_one_ends()
    {
        // Create first leave request
        LeaveRequest::factory()->create([
            'student_id' => $this->student->id,
            'jenis' => 'SAKIT',
            'tanggal_mulai' => now()->addDays(5)->format('Y-m-d'),
            'tanggal_selesai' => now()->addDays(7)->format('Y-m-d'),
            'status' => 'APPROVED',
        ]);

        // Create non-overlapping request (starts after first one ends)
        $response = $this->actingAs($this->parent)->post('/parent/leave-requests', [
            'student_id' => $this->student->id,
            'jenis' => 'IZIN',
            'tanggal_mulai' => now()->addDays(8)->format('Y-m-d'),
            'tanggal_selesai' => now()->addDays(10)->format('Y-m-d'),
            'alasan' => 'Alasan lain yang panjang untuk memenuhi validasi minimal',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseCount('leave_requests', 2);
    }

    /** @test LRT-014 */
    public function parent_can_edit_pending_leave_request()
    {
        $leaveRequest = LeaveRequest::factory()->create([
            'student_id' => $this->student->id,
            'jenis' => 'SAKIT',
            'tanggal_mulai' => now()->addDays(5)->format('Y-m-d'),
            'tanggal_selesai' => now()->addDays(7)->format('Y-m-d'),
            'status' => 'PENDING',
            'submitted_by' => $this->parent->id,
            'alasan' => 'Alasan awal',
        ]);

        $response = $this->actingAs($this->parent)->put("/parent/leave-requests/{$leaveRequest->id}", [
            'student_id' => $this->student->id,
            'jenis' => 'IZIN', // Changed from SAKIT
            'tanggal_mulai' => now()->addDays(6)->format('Y-m-d'), // Changed dates
            'tanggal_selesai' => now()->addDays(8)->format('Y-m-d'),
            'alasan' => 'Alasan baru yang lebih panjang untuk update',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('leave_requests', [
            'id' => $leaveRequest->id,
            'jenis' => 'IZIN',
            'alasan' => 'Alasan baru yang lebih panjang untuk update',
        ]);
    }

    /** @test LRT-015 */
    public function parent_cannot_edit_approved_leave_request()
    {
        $leaveRequest = LeaveRequest::factory()->create([
            'student_id' => $this->student->id,
            'status' => 'APPROVED',
            'submitted_by' => $this->parent->id,
        ]);

        $response = $this->actingAs($this->parent)->put("/parent/leave-requests/{$leaveRequest->id}", [
            'student_id' => $this->student->id,
            'jenis' => 'IZIN',
            'tanggal_mulai' => now()->addDay()->format('Y-m-d'),
            'tanggal_selesai' => now()->addDays(2)->format('Y-m-d'),
            'alasan' => 'Trying to change approved request',
        ]);

        $response->assertStatus(403); // Forbidden
    }

    /** @test LRT-016 */
    public function parent_cannot_edit_another_parents_leave_request()
    {
        $otherParent = User::factory()->create(['role' => 'PARENT']);
        $otherStudent = Student::factory()->create(['status' => 'aktif']);
        $otherGuardian = Guardian::factory()->create(['user_id' => $otherParent->id]);
        $otherGuardian->students()->attach($otherStudent->id, [
            'relationship' => 'IBU',
            'is_primary' => true,
        ]);

        $leaveRequest = LeaveRequest::factory()->create([
            'student_id' => $otherStudent->id,
            'status' => 'PENDING',
            'submitted_by' => $otherParent->id,
        ]);

        $response = $this->actingAs($this->parent)->put("/parent/leave-requests/{$leaveRequest->id}", [
            'student_id' => $otherStudent->id,
            'jenis' => 'IZIN',
            'tanggal_mulai' => now()->addDay()->format('Y-m-d'),
            'tanggal_selesai' => now()->addDays(2)->format('Y-m-d'),
            'alasan' => 'Trying to edit someone elses request',
        ]);

        $response->assertStatus(403); // Forbidden
    }

    /** @test LRT-017 */
    public function parent_can_cancel_pending_leave_request()
    {
        $leaveRequest = LeaveRequest::factory()->create([
            'student_id' => $this->student->id,
            'status' => 'PENDING',
            'submitted_by' => $this->parent->id,
        ]);

        $response = $this->actingAs($this->parent)->delete("/parent/leave-requests/{$leaveRequest->id}");

        $response->assertRedirect();
        $this->assertDatabaseMissing('leave_requests', [
            'id' => $leaveRequest->id,
        ]);
    }

    /** @test LRT-018 */
    public function parent_cannot_cancel_approved_leave_request()
    {
        $leaveRequest = LeaveRequest::factory()->create([
            'student_id' => $this->student->id,
            'status' => 'APPROVED',
            'submitted_by' => $this->parent->id,
            'reviewed_by' => $this->teacher->id,
        ]);

        $response = $this->actingAs($this->parent)->delete("/parent/leave-requests/{$leaveRequest->id}");

        $response->assertRedirect();
        // Should still exist in database
        $this->assertDatabaseHas('leave_requests', [
            'id' => $leaveRequest->id,
            'status' => 'APPROVED',
        ]);
    }

    /** @test LRT-019 */
    public function overlap_validation_excludes_current_request_when_editing()
    {
        $leaveRequest = LeaveRequest::factory()->create([
            'student_id' => $this->student->id,
            'jenis' => 'SAKIT',
            'tanggal_mulai' => now()->addDays(5)->format('Y-m-d'),
            'tanggal_selesai' => now()->addDays(7)->format('Y-m-d'),
            'status' => 'PENDING',
            'submitted_by' => $this->parent->id,
        ]);

        // Edit the same request with same dates (should be allowed)
        $response = $this->actingAs($this->parent)->put("/parent/leave-requests/{$leaveRequest->id}", [
            'student_id' => $this->student->id,
            'jenis' => 'IZIN', // Changed type
            'tanggal_mulai' => now()->addDays(5)->format('Y-m-d'), // Same dates
            'tanggal_selesai' => now()->addDays(7)->format('Y-m-d'),
            'alasan' => 'Updated alasan dengan lebih detail',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('leave_requests', [
            'id' => $leaveRequest->id,
            'jenis' => 'IZIN',
        ]);
    }

    /** @test LRT-020 */
    public function rejected_leave_requests_do_not_block_new_submissions()
    {
        // Create rejected leave request
        LeaveRequest::factory()->create([
            'student_id' => $this->student->id,
            'jenis' => 'SAKIT',
            'tanggal_mulai' => now()->addDays(5)->format('Y-m-d'),
            'tanggal_selesai' => now()->addDays(7)->format('Y-m-d'),
            'status' => 'REJECTED',
            'reviewed_by' => $this->teacher->id,
            'rejection_reason' => 'Alasan tidak valid',
        ]);

        // Should be able to create new request with same dates
        $response = $this->actingAs($this->parent)->post('/parent/leave-requests', [
            'student_id' => $this->student->id,
            'jenis' => 'IZIN',
            'tanggal_mulai' => now()->addDays(5)->format('Y-m-d'),
            'tanggal_selesai' => now()->addDays(7)->format('Y-m-d'),
            'alasan' => 'Alasan baru yang lebih lengkap dan detail',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseCount('leave_requests', 2);
    }
}
