<?php

namespace Database\Seeders;

use App\Models\LeaveRequest;
use App\Models\Student;
use App\Models\StudentAttendance;
use App\Models\TeacherAttendance;
use App\Models\TeacherLeave;
use App\Models\User;
use Illuminate\Database\Seeder;

class AttendanceQuickSeeder extends Seeder
{
    /**
     * Quick seeder untuk generate minimal test data (7 hari saja)
     * Cocok untuk testing cepat tanpa perlu data banyak
     */
    public function run(): void
    {
        $this->command->info('🚀 Quick Attendance Data Seeding (7 days)...');

        // Clear existing data
        $this->command->info('🧹 Clearing existing data...');
        StudentAttendance::truncate();
        TeacherAttendance::truncate();
        LeaveRequest::truncate();
        TeacherLeave::truncate();

        // 1. Student Attendance (7 days, 20 students)
        $this->seedStudentAttendance();

        // 2. Teacher Attendance (7 days, 5 teachers)
        $this->seedTeacherAttendance();

        // 3. Leave Requests (5 pending)
        $this->seedLeaveRequests();

        // 4. Teacher Leaves (3 pending)
        $this->seedTeacherLeaves();

        $this->command->info('✅ Quick seeding completed!');
        $this->showSummary();
    }

    private function seedStudentAttendance(): void
    {
        $this->command->info('📚 Seeding student attendance (7 days, 20 students)...');

        $students = Student::take(20)->get();
        $teachers = User::where('role', 'TEACHER')->get();

        if ($students->isEmpty() || $teachers->isEmpty()) {
            $this->command->warn('   ⚠ No students or teachers found');

            return;
        }

        $count = 0;
        foreach ($students as $student) {
            for ($i = 0; $i < 7; $i++) {
                $statuses = ['H', 'H', 'H', 'H', 'H', 'H', 'H', 'H', 'I', 'S', 'A'];
                $status = fake()->randomElement($statuses);

                StudentAttendance::create([
                    'student_id' => $student->id,
                    'class_id' => $student->kelas_id,
                    'tanggal' => now()->subDays($i)->format('Y-m-d'),
                    'status' => $status,
                    'keterangan' => $status !== 'H' ? fake()->optional(0.6)->sentence() : null,
                    'recorded_by' => $student->kelas->wali_kelas_id ?? $teachers->random()->id,
                    'recorded_at' => now(),
                ]);
                $count++;
            }
        }

        $this->command->info("   ✓ Created {$count} records");
    }

    private function seedTeacherAttendance(): void
    {
        $this->command->info('👨‍🏫 Seeding teacher attendance (7 days, 5 teachers)...');

        $teachers = User::where('role', 'TEACHER')->take(5)->get();

        if ($teachers->isEmpty()) {
            $this->command->warn('   ⚠ No teachers found');

            return;
        }

        $count = 0;
        foreach ($teachers as $teacher) {
            for ($i = 0; $i < 7; $i++) {
                $clockInHour = fake()->numberBetween(6, 8);
                $clockInMinute = fake()->numberBetween(0, 59);
                $isLate = ($clockInHour > 7) || ($clockInHour === 7 && $clockInMinute > 30);
                $workHours = fake()->numberBetween(8, 9);
                $clockOutHour = $clockInHour + $workHours;
                $clockOutMinute = fake()->numberBetween(0, 59);

                TeacherAttendance::create([
                    'teacher_id' => $teacher->id,
                    'tanggal' => now()->subDays($i)->format('Y-m-d'),
                    'clock_in' => sprintf('%02d:%02d:00', $clockInHour, $clockInMinute),
                    'clock_out' => sprintf('%02d:%02d:00', $clockOutHour, $clockOutMinute),
                    'latitude_in' => fake()->latitude(-6.3, -6.1),
                    'longitude_in' => fake()->longitude(106.7, 106.9),
                    'latitude_out' => fake()->latitude(-6.3, -6.1),
                    'longitude_out' => fake()->longitude(106.7, 106.9),
                    'is_late' => $isLate,
                    'status' => $isLate ? 'TERLAMBAT' : 'HADIR',
                    'keterangan' => $isLate ? fake()->optional(0.3)->sentence() : null,
                ]);
                $count++;
            }
        }

        $this->command->info("   ✓ Created {$count} records");
    }

    private function seedLeaveRequests(): void
    {
        $this->command->info('📝 Seeding leave requests (5 pending)...');

        $studentsWithParents = Student::whereHas('guardians')->take(5)->get();

        if ($studentsWithParents->isEmpty()) {
            $this->command->warn('   ⚠ No students with parents found');

            return;
        }

        $count = 0;
        foreach ($studentsWithParents as $student) {
            $parent = $student->guardians()->first()?->user;

            if ($parent) {
                $jenis = fake()->randomElement(['IZIN', 'SAKIT']);

                LeaveRequest::create([
                    'student_id' => $student->id,
                    'jenis' => $jenis,
                    'tanggal_mulai' => now()->addDays(rand(1, 3))->format('Y-m-d'),
                    'tanggal_selesai' => now()->addDays(rand(4, 5))->format('Y-m-d'),
                    'alasan' => $jenis === 'SAKIT'
                        ? fake()->randomElement(['Demam tinggi', 'Flu dan batuk', 'Sakit perut'])
                        : fake()->randomElement(['Acara keluarga', 'Keperluan keluarga']),
                    'attachment_path' => $jenis === 'SAKIT' ? fake()->optional(0.7)->filePath() : null,
                    'status' => 'PENDING',
                    'submitted_by' => $parent->id,
                ]);
                $count++;
            }
        }

        $this->command->info("   ✓ Created {$count} records");
    }

    private function seedTeacherLeaves(): void
    {
        $this->command->info('🏖️ Seeding teacher leaves (3 pending)...');

        $teachers = User::where('role', 'TEACHER')->take(3)->get();

        if ($teachers->isEmpty()) {
            $this->command->warn('   ⚠ No teachers found');

            return;
        }

        $count = 0;
        foreach ($teachers as $teacher) {
            $jenis = fake()->randomElement(['IZIN', 'SAKIT', 'CUTI']);
            $startDate = now()->addDays(rand(3, 7));
            $duration = rand(1, 3);
            $endDate = $startDate->copy()->addDays($duration);

            TeacherLeave::create([
                'teacher_id' => $teacher->id,
                'jenis' => $jenis,
                'tanggal_mulai' => $startDate->format('Y-m-d'),
                'tanggal_selesai' => $endDate->format('Y-m-d'),
                'jumlah_hari' => $duration + 1,
                'alasan' => match ($jenis) {
                    'SAKIT' => 'Demam tinggi dan perlu istirahat',
                    'CUTI' => 'Cuti tahunan',
                    default => 'Keperluan keluarga',
                },
                'attachment_path' => fake()->optional(0.5)->filePath(),
                'status' => 'PENDING',
            ]);
            $count++;
        }

        $this->command->info("   ✓ Created {$count} records");
    }

    private function showSummary(): void
    {
        $this->command->newLine();
        $this->command->info('📊 Summary:');
        $this->command->table(
            ['Model', 'Count'],
            [
                ['Student Attendances', StudentAttendance::count()],
                ['Teacher Attendances', TeacherAttendance::count()],
                ['Leave Requests (Pending)', LeaveRequest::where('status', 'PENDING')->count()],
                ['Teacher Leaves (Pending)', TeacherLeave::where('status', 'PENDING')->count()],
            ]
        );

        $this->command->newLine();
        $this->command->info('✅ Ready for manual testing!');
    }
}
