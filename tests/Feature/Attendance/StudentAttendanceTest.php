<?php

namespace Tests\Feature\Attendance;

use App\Models\SchoolClass;
use App\Models\Student;
use App\Models\StudentAttendance;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StudentAttendanceTest extends TestCase
{
    use RefreshDatabase;

    protected User $teacher;

    protected SchoolClass $class;

    protected function setUp(): void
    {
        parent::setUp();

        $this->teacher = User::factory()->create(['role' => 'TEACHER']);
        $this->class = SchoolClass::factory()->create([
            'wali_kelas_id' => $this->teacher->id,
        ]);
    }

    /** @test */
    public function teacher_can_record_daily_attendance(): void
    {
        $students = Student::factory()->count(3)->create([
            'kelas_id' => $this->class->id,
        ]);

        $response = $this->actingAs($this->teacher)->postJson(route('teacher.attendance.daily.store'), [
            'class_id' => $this->class->id,
            'tanggal' => today()->format('Y-m-d'),
            'attendances' => [
                ['student_id' => $students[0]->id, 'status' => 'H', 'keterangan' => null],
                ['student_id' => $students[1]->id, 'status' => 'I', 'keterangan' => 'Izin sakit'],
                ['student_id' => $students[2]->id, 'status' => 'A', 'keterangan' => null],
            ],
        ]);

        $response->assertRedirect();
        $this->assertDatabaseCount('student_attendances', 3);
        $this->assertDatabaseHas('student_attendances', [
            'student_id' => $students[0]->id,
            'status' => 'H',
        ]);
    }

    /** @test */
    public function teacher_cannot_record_attendance_for_other_class(): void
    {
        $otherClass = SchoolClass::factory()->create();
        $student = Student::factory()->create(['kelas_id' => $otherClass->id]);

        $response = $this->actingAs($this->teacher)->postJson(route('teacher.attendance.daily.store'), [
            'class_id' => $otherClass->id,
            'tanggal' => today()->format('Y-m-d'),
            'attendances' => [
                ['student_id' => $student->id, 'status' => 'H'],
            ],
        ]);

        $response->assertForbidden();
        $this->assertDatabaseCount('student_attendances', 0);
    }

    /** @test */
    public function duplicate_attendance_is_prevented(): void
    {
        $student = Student::factory()->create(['kelas_id' => $this->class->id]);

        // First attendance
        StudentAttendance::create([
            'student_id' => $student->id,
            'class_id' => $this->class->id,
            'tanggal' => today(),
            'status' => 'H',
            'recorded_by' => $this->teacher->id,
            'recorded_at' => now(),
        ]);

        // Try to record again
        $response = $this->actingAs($this->teacher)->postJson(route('teacher.attendance.daily.store'), [
            'class_id' => $this->class->id,
            'tanggal' => today()->format('Y-m-d'),
            'attendances' => [
                ['student_id' => $student->id, 'status' => 'A'],
            ],
        ]);

        // Service will skip duplicate, so should still succeed but only 1 record
        $this->assertDatabaseCount('student_attendances', 1);
    }

    /** @test */
    public function cannot_record_future_date_attendance(): void
    {
        $student = Student::factory()->create(['kelas_id' => $this->class->id]);

        $response = $this->actingAs($this->teacher)->postJson(route('teacher.attendance.daily.store'), [
            'class_id' => $this->class->id,
            'tanggal' => today()->addDays(2)->format('Y-m-d'),
            'attendances' => [
                ['student_id' => $student->id, 'status' => 'H'],
            ],
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors('tanggal');
    }

    /** @test */
    public function status_validation_works(): void
    {
        $student = Student::factory()->create(['kelas_id' => $this->class->id]);

        $response = $this->actingAs($this->teacher)->postJson(route('teacher.attendance.daily.store'), [
            'class_id' => $this->class->id,
            'tanggal' => today()->format('Y-m-d'),
            'attendances' => [
                ['student_id' => $student->id, 'status' => 'X'], // Invalid status
            ],
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors('attendances.0.status');
    }

    /** @test */
    public function parent_cannot_record_attendance(): void
    {
        $parent = User::factory()->create(['role' => 'PARENT']);
        $student = Student::factory()->create(['kelas_id' => $this->class->id]);

        $response = $this->actingAs($parent)->postJson(route('teacher.attendance.daily.store'), [
            'class_id' => $this->class->id,
            'tanggal' => today()->format('Y-m-d'),
            'attendances' => [
                ['student_id' => $student->id, 'status' => 'H'],
            ],
        ]);

        $response->assertForbidden();
    }

    /** @test */
    public function guests_cannot_access_attendance_routes(): void
    {
        $response = $this->getJson(route('teacher.attendance.index'));
        $response->assertUnauthorized();

        $response = $this->postJson(route('teacher.attendance.daily.store'), []);
        $response->assertUnauthorized();
    }

    /** @test */
    public function attendance_must_have_at_least_one_student(): void
    {
        $response = $this->actingAs($this->teacher)->postJson(route('teacher.attendance.daily.store'), [
            'class_id' => $this->class->id,
            'tanggal' => today()->format('Y-m-d'),
            'attendances' => [], // Empty
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors('attendances');
    }
}
