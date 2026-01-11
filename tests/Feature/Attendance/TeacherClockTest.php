<?php

namespace Tests\Feature\Attendance;

use App\Models\TeacherAttendance;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TeacherClockTest extends TestCase
{
    use RefreshDatabase;

    protected User $teacher;

    protected function setUp(): void
    {
        parent::setUp();

        $this->teacher = User::factory()->create(['role' => 'TEACHER']);
    }

    /** @test TCT-001 */
    public function teacher_can_clock_in_with_gps_coordinates()
    {
        $response = $this->actingAs($this->teacher)->post('/teacher/clock/in', [
            'latitude' => -6.200000,
            'longitude' => 106.816666,
        ]);

        $response->assertStatus(302);

        $this->assertDatabaseHas('teacher_attendances', [
            'teacher_id' => $this->teacher->id,
            'tanggal' => today()->format('Y-m-d'),
            'clock_in_latitude' => -6.200000,
            'clock_in_longitude' => 106.816666,
        ]);
    }

    /** @test TCT-002 */
    public function teacher_cannot_clock_in_twice_same_day()
    {
        // First clock in
        $this->actingAs($this->teacher)->post('/teacher/clock/in', [
            'latitude' => -6.200000,
            'longitude' => 106.816666,
        ]);

        // Try to clock in again
        $response = $this->actingAs($this->teacher)->post('/teacher/clock/in', [
            'latitude' => -6.200000,
            'longitude' => 106.816666,
        ]);

        $response->assertStatus(500); // Exception thrown

        // Should only have one record
        $count = TeacherAttendance::where('teacher_id', $this->teacher->id)
            ->whereDate('tanggal', today())
            ->count();

        $this->assertEquals(1, $count);
    }

    /** @test TCT-003 */
    public function teacher_cannot_clock_out_before_clocking_in()
    {
        $response = $this->actingAs($this->teacher)->post('/teacher/clock/out', [
            'latitude' => -6.200000,
            'longitude' => 106.816666,
        ]);

        $response->assertStatus(404); // Record not found
    }

    /** @test TCT-004 */
    public function lateness_is_detected_correctly()
    {
        // Set time to 08:00 (late, school starts at 07:30)
        Carbon::setTestNow(today()->setTime(8, 0));

        $this->actingAs($this->teacher)->post('/teacher/clock/in', [
            'latitude' => -6.200000,
            'longitude' => 106.816666,
        ]);

        $attendance = TeacherAttendance::where('teacher_id', $this->teacher->id)
            ->whereDate('tanggal', today())
            ->first();

        $this->assertTrue($attendance->is_late);
        $this->assertEquals(30, $attendance->late_minutes); // 30 minutes late

        Carbon::setTestNow(); // Reset
    }

    /** @test TCT-005 */
    public function duration_is_calculated_on_clock_out()
    {
        // Clock in at 07:00
        Carbon::setTestNow(today()->setTime(7, 0));

        $this->actingAs($this->teacher)->post('/teacher/clock/in', [
            'latitude' => -6.200000,
            'longitude' => 106.816666,
        ]);

        // Clock out at 15:00 (8 hours later)
        Carbon::setTestNow(today()->setTime(15, 0));

        $this->actingAs($this->teacher)->post('/teacher/clock/out', [
            'latitude' => -6.200000,
            'longitude' => 106.816666,
        ]);

        $attendance = TeacherAttendance::where('teacher_id', $this->teacher->id)
            ->whereDate('tanggal', today())
            ->first();

        $this->assertNotNull($attendance->clock_out);

        // Calculate duration in minutes
        $clockIn = Carbon::parse($attendance->clock_in);
        $clockOut = Carbon::parse($attendance->clock_out);
        $durationMinutes = $clockOut->diffInMinutes($clockIn);

        $this->assertEquals(480, $durationMinutes); // 8 hours = 480 minutes

        Carbon::setTestNow(); // Reset
    }

    /** @test TCT-006 */
    public function gps_validation_rejects_invalid_coordinates()
    {
        // Invalid latitude (out of range)
        $response = $this->actingAs($this->teacher)->post('/teacher/clock/in', [
            'latitude' => 91.0, // Max is 90
            'longitude' => 106.816666,
        ]);

        $response->assertSessionHasErrors('latitude');

        // Invalid longitude (out of range)
        $response = $this->actingAs($this->teacher)->post('/teacher/clock/in', [
            'latitude' => -6.200000,
            'longitude' => 181.0, // Max is 180
        ]);

        $response->assertSessionHasErrors('longitude');
    }

    /** @test */
    public function teacher_clock_status_api_returns_current_status()
    {
        // No clock in yet
        $response = $this->actingAs($this->teacher)->get('/teacher/clock/status');

        $response->assertJson([
            'clocked_in' => false,
        ]);

        // After clock in
        $this->actingAs($this->teacher)->post('/teacher/clock/in', [
            'latitude' => -6.200000,
            'longitude' => 106.816666,
        ]);

        $response = $this->actingAs($this->teacher)->get('/teacher/clock/status');

        $response->assertJson([
            'clocked_in' => true,
            'clocked_out' => false,
        ]);
    }

    /** @test */
    public function on_time_clock_in_sets_status_hadir()
    {
        // Clock in at 07:00 (on time)
        Carbon::setTestNow(today()->setTime(7, 0));

        $this->actingAs($this->teacher)->post('/teacher/clock/in', [
            'latitude' => -6.200000,
            'longitude' => 106.816666,
        ]);

        $attendance = TeacherAttendance::where('teacher_id', $this->teacher->id)
            ->whereDate('tanggal', today())
            ->first();

        $this->assertEquals('HADIR', $attendance->status);
        $this->assertFalse($attendance->is_late);

        Carbon::setTestNow(); // Reset
    }

    /** @test */
    public function late_clock_in_sets_status_terlambat()
    {
        // Clock in at 08:00 (late)
        Carbon::setTestNow(today()->setTime(8, 0));

        $this->actingAs($this->teacher)->post('/teacher/clock/in', [
            'latitude' => -6.200000,
            'longitude' => 106.816666,
        ]);

        $attendance = TeacherAttendance::where('teacher_id', $this->teacher->id)
            ->whereDate('tanggal', today())
            ->first();

        $this->assertEquals('TERLAMBAT', $attendance->status);
        $this->assertTrue($attendance->is_late);

        Carbon::setTestNow(); // Reset
    }
}
