<?php

namespace Tests\Feature\Attendance;

use App\Models\SchoolClass;
use App\Models\Student;
use App\Models\Subject;
use App\Models\SubjectAttendance;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class SubjectAttendanceTest extends TestCase
{
    use RefreshDatabase;

    protected User $teacher;

    protected User $otherTeacher;

    protected SchoolClass $class;

    protected Subject $subject;

    protected Student $student;

    protected function setUp(): void
    {
        parent::setUp();

        $this->teacher = User::factory()->create(['role' => 'TEACHER']);
        $this->otherTeacher = User::factory()->create(['role' => 'TEACHER']);

        $this->class = SchoolClass::factory()->create();
        $this->subject = Subject::factory()->create();

        $this->student = Student::factory()->create([
            'kelas_id' => $this->class->id,
            'status' => 'aktif',
        ]);

        // Assign teacher to teach this subject in this class
        DB::table('teacher_subjects')->insert([
            'teacher_id' => $this->teacher->id,
            'subject_id' => $this->subject->id,
            'class_id' => $this->class->id,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    /** @test SUBT-001 */
    public function teacher_can_record_subject_attendance()
    {
        $response = $this->actingAs($this->teacher)->post('/teacher/attendance/subject', [
            'subject_id' => $this->subject->id,
            'class_id' => $this->class->id,
            'tanggal' => today()->format('Y-m-d'),
            'jam_ke' => 3,
            'attendances' => [
                [
                    'student_id' => $this->student->id,
                    'status' => 'H',
                    'keterangan' => null,
                ],
            ],
        ]);

        $response->assertRedirect();

        $this->assertDatabaseHas('subject_attendances', [
            'student_id' => $this->student->id,
            'subject_id' => $this->subject->id,
            'class_id' => $this->class->id,
            'teacher_id' => $this->teacher->id,
            'jam_ke' => 3,
            'status' => 'H',
        ]);
    }

    /** @test SUBT-002 */
    public function teacher_must_teach_subject_to_record_attendance()
    {
        // Other teacher who doesn't teach this subject
        $response = $this->actingAs($this->otherTeacher)->post('/teacher/attendance/subject', [
            'subject_id' => $this->subject->id,
            'class_id' => $this->class->id,
            'tanggal' => today()->format('Y-m-d'),
            'jam_ke' => 3,
            'attendances' => [
                [
                    'student_id' => $this->student->id,
                    'status' => 'H',
                    'keterangan' => null,
                ],
            ],
        ]);

        $response->assertStatus(500); // Exception thrown

        $this->assertDatabaseMissing('subject_attendances', [
            'student_id' => $this->student->id,
            'teacher_id' => $this->otherTeacher->id,
        ]);
    }

    /** @test SUBT-003 */
    public function duplicate_subject_attendance_is_prevented()
    {
        // First record
        $this->actingAs($this->teacher)->post('/teacher/attendance/subject', [
            'subject_id' => $this->subject->id,
            'class_id' => $this->class->id,
            'tanggal' => today()->format('Y-m-d'),
            'jam_ke' => 3,
            'attendances' => [
                [
                    'student_id' => $this->student->id,
                    'status' => 'H',
                    'keterangan' => null,
                ],
            ],
        ]);

        // Try to record again for same student, subject, date, and jam_ke
        $this->actingAs($this->teacher)->post('/teacher/attendance/subject', [
            'subject_id' => $this->subject->id,
            'class_id' => $this->class->id,
            'tanggal' => today()->format('Y-m-d'),
            'jam_ke' => 3,
            'attendances' => [
                [
                    'student_id' => $this->student->id,
                    'status' => 'A',
                    'keterangan' => 'Duplicate attempt',
                ],
            ],
        ]);

        // Should only have one record
        $count = SubjectAttendance::where('student_id', $this->student->id)
            ->where('subject_id', $this->subject->id)
            ->whereDate('tanggal', today())
            ->where('jam_ke', 3)
            ->count();

        $this->assertEquals(1, $count);
    }

    /** @test SUBT-004 */
    public function jam_ke_validation_rejects_invalid_values()
    {
        // Jam ke < 1
        $response = $this->actingAs($this->teacher)->post('/teacher/attendance/subject', [
            'subject_id' => $this->subject->id,
            'class_id' => $this->class->id,
            'tanggal' => today()->format('Y-m-d'),
            'jam_ke' => 0,
            'attendances' => [
                [
                    'student_id' => $this->student->id,
                    'status' => 'H',
                    'keterangan' => null,
                ],
            ],
        ]);

        $response->assertSessionHasErrors('jam_ke');

        // Jam ke > 10
        $response = $this->actingAs($this->teacher)->post('/teacher/attendance/subject', [
            'subject_id' => $this->subject->id,
            'class_id' => $this->class->id,
            'tanggal' => today()->format('Y-m-d'),
            'jam_ke' => 11,
            'attendances' => [
                [
                    'student_id' => $this->student->id,
                    'status' => 'H',
                    'keterangan' => null,
                ],
            ],
        ]);

        $response->assertSessionHasErrors('jam_ke');
    }

    /** @test SUBT-005 */
    public function get_teacher_schedule_returns_subjects_taught()
    {
        $response = $this->actingAs($this->teacher)->get('/teacher/attendance/subject');

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page
            ->component('Teacher/SubjectAttendance/Create')
            ->has('schedule')
        );
    }

    /** @test */
    public function multiple_students_can_be_recorded_in_one_request()
    {
        $student2 = Student::factory()->create([
            'kelas_id' => $this->class->id,
            'status' => 'aktif',
        ]);

        $student3 = Student::factory()->create([
            'kelas_id' => $this->class->id,
            'status' => 'aktif',
        ]);

        $response = $this->actingAs($this->teacher)->post('/teacher/attendance/subject', [
            'subject_id' => $this->subject->id,
            'class_id' => $this->class->id,
            'tanggal' => today()->format('Y-m-d'),
            'jam_ke' => 3,
            'attendances' => [
                ['student_id' => $this->student->id, 'status' => 'H', 'keterangan' => null],
                ['student_id' => $student2->id, 'status' => 'I', 'keterangan' => 'Izin'],
                ['student_id' => $student3->id, 'status' => 'A', 'keterangan' => null],
            ],
        ]);

        $response->assertRedirect();

        $count = SubjectAttendance::where('subject_id', $this->subject->id)
            ->where('class_id', $this->class->id)
            ->whereDate('tanggal', today())
            ->where('jam_ke', 3)
            ->count();

        $this->assertEquals(3, $count);
    }

    /** @test */
    public function subject_attendance_includes_keterangan()
    {
        $this->actingAs($this->teacher)->post('/teacher/attendance/subject', [
            'subject_id' => $this->subject->id,
            'class_id' => $this->class->id,
            'tanggal' => today()->format('Y-m-d'),
            'jam_ke' => 3,
            'attendances' => [
                [
                    'student_id' => $this->student->id,
                    'status' => 'S',
                    'keterangan' => 'Sakit demam',
                ],
            ],
        ]);

        $this->assertDatabaseHas('subject_attendances', [
            'student_id' => $this->student->id,
            'status' => 'S',
            'keterangan' => 'Sakit demam',
        ]);
    }
}
