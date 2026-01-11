<?php

namespace Database\Seeders;

use App\Models\LeaveRequest;
use App\Models\Student;
use App\Models\StudentAttendance;
use App\Models\Subject;
use App\Models\SubjectAttendance;
use App\Models\TeacherAttendance;
use App\Models\TeacherLeave;
use App\Models\User;
use Illuminate\Database\Seeder;

class AttendanceTestSeeder extends Seeder
{
    /**
     * Seed attendance test data untuk keperluan testing manual
     * Generate 30 hari data untuk student attendance, teacher attendance,
     * leave requests, subject attendance, dan teacher leaves
     */
    public function run(): void
    {
        $this->command->info('🌱 Starting Attendance Test Data Seeding...');

        // Check if data already exists
        $existingData = StudentAttendance::count() + TeacherAttendance::count();
        if ($existingData > 0) {
            $this->command->warn("⚠️  Found {$existingData} existing attendance records.");
            $this->command->warn('   Run this command to clear existing data first:');
            $this->command->warn('   php artisan tinker');
            $this->command->warn('   Then: \\App\\Models\\StudentAttendance::truncate();');
            $this->command->warn('         \\App\\Models\\TeacherAttendance::truncate();');
            $this->command->warn('         \\App\\Models\\LeaveRequest::truncate();');
            $this->command->warn('         \\App\\Models\\SubjectAttendance::truncate();');
            $this->command->warn('         \\App\\Models\\TeacherLeave::truncate();');
            $this->command->newLine();

            if (! $this->command->confirm('Do you want to clear existing data and continue?', false)) {
                $this->command->info('Seeding cancelled.');

                return;
            }

            // Clear existing data
            $this->command->info('🧹 Clearing existing attendance data...');
            StudentAttendance::truncate();
            TeacherAttendance::truncate();
            LeaveRequest::truncate();
            SubjectAttendance::truncate();
            TeacherLeave::truncate();
            $this->command->info('   ✓ Existing data cleared');
        }

        // 1. Student Daily Attendance (30 days)
        $this->seedStudentAttendance();

        // 2. Teacher Clock In/Out (30 days)
        $this->seedTeacherAttendance();

        // 3. Leave Requests (various statuses)
        $this->seedLeaveRequests();

        // 4. Subject Attendance (14 days)
        $this->seedSubjectAttendance();

        // 5. Teacher Leave Requests
        $this->seedTeacherLeaves();

        $this->command->info('✅ Attendance test data seeding completed!');
        $this->showSummary();
    }

    /**
     * Seed student daily attendance untuk 30 hari terakhir
     */
    private function seedStudentAttendance(): void
    {
        $this->command->info('📚 Seeding student daily attendance...');

        $students = Student::all();
        $teachers = User::where('role', 'TEACHER')->get();
        $totalRecords = 0;

        if ($teachers->isEmpty()) {
            $this->command->warn('   ⚠ No teachers found, skipping student attendance');

            return;
        }

        foreach ($students as $student) {
            for ($i = 0; $i < 30; $i++) {
                // Weighted status: 80% hadir, 10% izin, 5% sakit, 5% alpha
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
                $totalRecords++;
            }
        }

        $this->command->info("   ✓ Created {$totalRecords} student attendance records");
    }

    /**
     * Seed teacher clock in/out untuk 30 hari terakhir
     */
    private function seedTeacherAttendance(): void
    {
        $this->command->info('👨‍🏫 Seeding teacher clock in/out...');

        $teachers = User::where('role', 'TEACHER')->get();
        $totalRecords = 0;

        foreach ($teachers as $teacher) {
            for ($i = 0; $i < 30; $i++) {
                // Random clock in time between 06:30 - 08:30
                $clockInHour = fake()->numberBetween(6, 8);
                $clockInMinute = fake()->numberBetween(0, 59);

                // School starts at 07:30, check if late
                $isLate = ($clockInHour > 7) || ($clockInHour === 7 && $clockInMinute > 30);

                // Clock out time: 8-9 hours after clock in
                $workHours = fake()->numberBetween(8, 9);
                $clockOutHour = $clockInHour + $workHours;
                $clockOutMinute = fake()->numberBetween(0, 59);

                // GPS coordinates untuk area Jakarta
                $latitudeIn = fake()->latitude(-6.3, -6.1);
                $longitudeIn = fake()->longitude(106.7, 106.9);

                TeacherAttendance::create([
                    'teacher_id' => $teacher->id,
                    'tanggal' => now()->subDays($i)->format('Y-m-d'),
                    'clock_in' => sprintf('%02d:%02d:00', $clockInHour, $clockInMinute),
                    'clock_out' => sprintf('%02d:%02d:00', $clockOutHour, $clockOutMinute),
                    'latitude_in' => $latitudeIn,
                    'longitude_in' => $longitudeIn,
                    'latitude_out' => fake()->latitude(-6.3, -6.1),
                    'longitude_out' => fake()->longitude(106.7, 106.9),
                    'is_late' => $isLate,
                    'status' => $isLate ? 'TERLAMBAT' : 'HADIR',
                    'keterangan' => $isLate ? fake()->optional(0.3)->sentence() : null,
                ]);
                $totalRecords++;
            }
        }

        $this->command->info("   ✓ Created {$totalRecords} teacher attendance records");
    }

    /**
     * Seed leave requests dengan berbagai status
     */
    private function seedLeaveRequests(): void
    {
        $this->command->info('📝 Seeding leave requests...');

        // Get students with parents
        $studentsWithParents = Student::whereHas('guardians')->take(15)->get();
        $teachers = User::where('role', 'TEACHER')->get();
        $admins = User::where('role', 'ADMIN')->get();
        $totalRecords = 0;

        if ($studentsWithParents->isEmpty()) {
            $this->command->warn('   ⚠ No students with parents found, skipping leave requests');

            return;
        }

        foreach ($studentsWithParents as $student) {
            $parent = $student->guardians()->first()?->user;

            if ($parent) {
                $jenis = fake()->randomElement(['IZIN', 'SAKIT']);

                // 1 pending request (future date)
                LeaveRequest::create([
                    'student_id' => $student->id,
                    'jenis' => $jenis,
                    'tanggal_mulai' => now()->addDays(rand(1, 7))->format('Y-m-d'),
                    'tanggal_selesai' => now()->addDays(rand(8, 10))->format('Y-m-d'),
                    'alasan' => $jenis === 'SAKIT'
                        ? fake()->randomElement(['Demam tinggi', 'Flu dan batuk', 'Sakit perut'])
                        : fake()->randomElement(['Acara keluarga', 'Keperluan keluarga', 'Mudik']),
                    'attachment_path' => $jenis === 'SAKIT' ? fake()->optional(0.7)->filePath() : null,
                    'status' => 'PENDING',
                    'submitted_by' => $parent->id,
                ]);
                $totalRecords++;

                // 1-2 historical requests (approved/rejected)
                $historicalCount = rand(1, 2);
                for ($i = 0; $i < $historicalCount; $i++) {
                    $status = fake()->randomElement(['APPROVED', 'REJECTED']);
                    $startDate = now()->subDays(rand(5, 20));
                    $jenisHistorical = fake()->randomElement(['IZIN', 'SAKIT']);

                    $leave = LeaveRequest::create([
                        'student_id' => $student->id,
                        'jenis' => $jenisHistorical,
                        'tanggal_mulai' => $startDate->format('Y-m-d'),
                        'tanggal_selesai' => $startDate->copy()->addDays(rand(1, 3))->format('Y-m-d'),
                        'alasan' => $jenisHistorical === 'SAKIT'
                            ? fake()->randomElement(['Demam', 'Sakit kepala', 'Flu'])
                            : fake()->randomElement(['Acara keluarga', 'Keperluan mendadak']),
                        'attachment_path' => $jenisHistorical === 'SAKIT' ? fake()->optional(0.5)->filePath() : null,
                        'status' => $status,
                        'submitted_by' => $parent->id,
                        'reviewed_by' => ! $teachers->isEmpty() ? $teachers->random()->id : ($admins->isNotEmpty() ? $admins->random()->id : null),
                        'reviewed_at' => $startDate->copy()->addDay(),
                    ]);

                    if ($status === 'REJECTED') {
                        $leave->update([
                            'rejection_reason' => fake()->randomElement([
                                'Tidak ada surat dokter',
                                'Alasan tidak jelas',
                                'Terlalu sering izin',
                            ]),
                        ]);
                    }

                    $totalRecords++;
                }
            }
        }

        $this->command->info("   ✓ Created {$totalRecords} leave requests");
    }

    /**
     * Seed subject attendance untuk 14 hari terakhir
     */
    private function seedSubjectAttendance(): void
    {
        $this->command->info('📖 Seeding subject attendance...');

        $students = Student::take(20)->get();
        $subjects = Subject::take(5)->get();
        $teachers = User::where('role', 'TEACHER')->get();
        $totalRecords = 0;

        if ($subjects->isEmpty()) {
            $this->command->warn('   ⚠ No subjects found, skipping subject attendance');

            return;
        }

        if ($teachers->isEmpty()) {
            $this->command->warn('   ⚠ No teachers found, skipping subject attendance');

            return;
        }

        foreach ($students as $student) {
            foreach ($subjects as $subject) {
                for ($i = 0; $i < 14; $i++) {
                    // Random 2-4 jam per day per subject
                    $jamCount = rand(2, 4);
                    for ($jamKe = 1; $jamKe <= $jamCount; $jamKe++) {
                        // Weighted status: 85% hadir
                        $statuses = ['H', 'H', 'H', 'H', 'H', 'H', 'H', 'H', 'H', 'I', 'S', 'A'];
                        $status = fake()->randomElement($statuses);

                        SubjectAttendance::create([
                            'student_id' => $student->id,
                            'class_id' => $student->kelas_id,
                            'subject_id' => $subject->id,
                            'teacher_id' => $teachers->random()->id,
                            'tanggal' => now()->subDays($i)->format('Y-m-d'),
                            'jam_ke' => $jamKe,
                            'status' => $status,
                            'keterangan' => $status !== 'H' ? fake()->optional(0.5)->sentence() : null,
                        ]);
                        $totalRecords++;
                    }
                }
            }
        }

        $this->command->info("   ✓ Created {$totalRecords} subject attendance records");
    }

    /**
     * Seed teacher leave requests dengan berbagai status
     */
    private function seedTeacherLeaves(): void
    {
        $this->command->info('🏖️ Seeding teacher leave requests...');

        $teachers = User::where('role', 'TEACHER')->take(10)->get();
        $admins = User::where('role', 'ADMIN')->get();
        $principals = User::where('role', 'PRINCIPAL')->get();
        $totalRecords = 0;

        foreach ($teachers as $teacher) {
            $jenis = fake()->randomElement(['IZIN', 'SAKIT', 'CUTI']);
            $startDate = now()->addDays(rand(3, 14));
            $duration = rand(1, 5);
            $endDate = $startDate->copy()->addDays($duration);

            // 1 pending leave (future)
            TeacherLeave::create([
                'teacher_id' => $teacher->id,
                'jenis' => $jenis,
                'tanggal_mulai' => $startDate->format('Y-m-d'),
                'tanggal_selesai' => $endDate->format('Y-m-d'),
                'jumlah_hari' => $duration + 1,
                'alasan' => match ($jenis) {
                    'SAKIT' => fake()->randomElement(['Demam tinggi', 'Rawat inap', 'Flu berat']),
                    'CUTI' => fake()->randomElement(['Cuti tahunan', 'Keperluan keluarga', 'Umroh']),
                    default => fake()->randomElement(['Keperluan mendadak', 'Acara keluarga']),
                },
                'attachment_path' => ($jenis === 'SAKIT' || $jenis === 'CUTI') ? fake()->optional(0.8)->filePath() : null,
                'status' => 'PENDING',
            ]);
            $totalRecords++;

            // 1-2 historical leaves
            $historicalCount = rand(1, 2);
            for ($i = 0; $i < $historicalCount; $i++) {
                $status = fake()->randomElement(['APPROVED', 'REJECTED']);
                $historicalStart = now()->subDays(rand(10, 60));
                $historicalDuration = rand(2, 5);
                $historicalEnd = $historicalStart->copy()->addDays($historicalDuration);
                $historicalJenis = fake()->randomElement(['IZIN', 'SAKIT', 'CUTI']);

                $leave = TeacherLeave::create([
                    'teacher_id' => $teacher->id,
                    'jenis' => $historicalJenis,
                    'tanggal_mulai' => $historicalStart->format('Y-m-d'),
                    'tanggal_selesai' => $historicalEnd->format('Y-m-d'),
                    'jumlah_hari' => $historicalDuration + 1,
                    'alasan' => match ($historicalJenis) {
                        'SAKIT' => fake()->randomElement(['Sakit', 'Demam', 'Flu']),
                        'CUTI' => fake()->randomElement(['Cuti', 'Liburan']),
                        default => fake()->randomElement(['Keperluan keluarga', 'Acara penting']),
                    },
                    'attachment_path' => fake()->optional(0.5)->filePath(),
                    'status' => $status,
                    'reviewed_by' => ! $principals->isEmpty() ? $principals->random()->id : ($admins->isNotEmpty() ? $admins->random()->id : null),
                    'reviewed_at' => $historicalStart->copy()->addDay(),
                ]);

                if ($status === 'REJECTED') {
                    $leave->update([
                        'rejection_reason' => fake()->randomElement([
                            'Tidak ada pengganti guru',
                            'Periode ujian',
                            'Dokumen tidak lengkap',
                        ]),
                    ]);
                }

                $totalRecords++;
            }
        }

        $this->command->info("   ✓ Created {$totalRecords} teacher leave requests");
    }

    /**
     * Show summary statistik data yang di-seed
     */
    private function showSummary(): void
    {
        $this->command->newLine();
        $this->command->info('📊 Summary:');
        $this->command->table(
            ['Model', 'Count'],
            [
                ['Student Attendances', StudentAttendance::count()],
                ['Teacher Attendances', TeacherAttendance::count()],
                ['Leave Requests', LeaveRequest::count()],
                ['Subject Attendances', SubjectAttendance::count()],
                ['Teacher Leaves', TeacherLeave::count()],
            ]
        );

        $this->command->newLine();
        $this->command->info('🎯 Quick Stats:');
        $this->command->info('   • Pending Leave Requests: '.LeaveRequest::where('status', 'PENDING')->count());
        $this->command->info('   • Pending Teacher Leaves: '.TeacherLeave::where('status', 'PENDING')->count());
        $this->command->info("   • Today's Student Attendance: ".StudentAttendance::whereDate('tanggal', now())->count());
        $this->command->info("   • Today's Teacher Clock-ins: ".TeacherAttendance::whereDate('tanggal', now())->count());
    }
}
