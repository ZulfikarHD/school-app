<?php

namespace Database\Seeders;

use App\Enums\Hari;
use App\Models\AcademicYear;
use App\Models\SchoolClass;
use App\Models\Subject;
use App\Models\Teacher;
use App\Models\TeachingSchedule;
use Illuminate\Database\Seeder;

/**
 * TeachingScheduleSeeder - Seeder untuk membuat sample jadwal mengajar
 *
 * Seeder ini bertujuan untuk membuat jadwal mengajar yang realistis
 * dengan mempertimbangkan constraint conflict untuk guru dan kelas
 */
class TeachingScheduleSeeder extends Seeder
{
    /**
     * Time slots standar untuk jadwal sekolah (45 menit per slot)
     *
     * @var array<array{start: string, end: string}>
     */
    private array $timeSlots = [
        ['start' => '07:00', 'end' => '07:45'],
        ['start' => '07:45', 'end' => '08:30'],
        ['start' => '08:30', 'end' => '09:15'],
        // Istirahat 1: 09:15 - 09:30
        ['start' => '09:30', 'end' => '10:15'],
        ['start' => '10:15', 'end' => '11:00'],
        ['start' => '11:00', 'end' => '11:45'],
        // Istirahat 2 + Sholat: 11:45 - 12:30
        ['start' => '12:30', 'end' => '13:15'],
        ['start' => '13:15', 'end' => '14:00'],
    ];

    /**
     * Run the database seeds.
     *
     * Membuat jadwal mengajar sample untuk setiap guru-mapel-kelas
     * dengan menghindari konflik jadwal
     */
    public function run(): void
    {
        // Get active academic year
        $academicYear = AcademicYear::active()->first();
        if (! $academicYear) {
            $this->command->warn('No active academic year found. Skipping TeachingScheduleSeeder.');

            return;
        }

        // Get all active teachers with their subjects
        $teachers = Teacher::with(['subjects' => function ($query) {
            $query->wherePivot('is_primary', true);
        }])->active()->get();

        if ($teachers->isEmpty()) {
            $this->command->warn('No active teachers found. Skipping TeachingScheduleSeeder.');

            return;
        }

        // Get all active classes
        $classes = SchoolClass::where('is_active', true)->get();
        if ($classes->isEmpty()) {
            $this->command->warn('No active classes found. Skipping TeachingScheduleSeeder.');

            return;
        }

        // Track occupied slots to avoid conflicts
        $teacherOccupied = []; // [teacher_id][hari][slot_index] = true
        $classOccupied = []; // [class_id][hari][slot_index] = true

        $schedulesCreated = 0;
        $days = Hari::weekdays(); // Senin - Jumat

        foreach ($teachers as $teacher) {
            foreach ($teacher->subjects as $subject) {
                // Assign this teacher-subject combination to 2-3 random classes
                $selectedClasses = $classes->random(min(3, $classes->count()));

                foreach ($selectedClasses as $class) {
                    // Try to find an available slot
                    $slotFound = false;

                    // Shuffle days and slots to distribute evenly
                    $shuffledDays = collect($days)->shuffle();

                    foreach ($shuffledDays as $day) {
                        if ($slotFound) {
                            break;
                        }

                        $shuffledSlots = collect($this->timeSlots)->shuffle();

                        foreach ($shuffledSlots as $slotIndex => $slot) {
                            $teacherKey = $teacher->id.'_'.$day->value.'_'.$slotIndex;
                            $classKey = $class->id.'_'.$day->value.'_'.$slotIndex;

                            // Check if slot is available for both teacher and class
                            if (! isset($teacherOccupied[$teacherKey]) && ! isset($classOccupied[$classKey])) {
                                // Create schedule
                                TeachingSchedule::create([
                                    'teacher_id' => $teacher->id,
                                    'subject_id' => $subject->id,
                                    'class_id' => $class->id,
                                    'academic_year_id' => $academicYear->id,
                                    'hari' => $day->value,
                                    'jam_mulai' => $slot['start'],
                                    'jam_selesai' => $slot['end'],
                                    'ruangan' => $this->generateRuangan($class),
                                    'is_active' => true,
                                ]);

                                // Mark slots as occupied
                                $teacherOccupied[$teacherKey] = true;
                                $classOccupied[$classKey] = true;

                                $schedulesCreated++;
                                $slotFound = true;
                                break;
                            }
                        }
                    }

                    if (! $slotFound) {
                        $this->command->line("Could not find available slot for {$teacher->nama_lengkap} - {$subject->nama_mapel} - Kelas {$class->tingkat}{$class->nama}");
                    }
                }
            }
        }

        $this->command->info("Created {$schedulesCreated} teaching schedules for academic year {$academicYear->name}.");
    }

    /**
     * Generate nama ruangan berdasarkan kelas
     */
    private function generateRuangan(SchoolClass $class): string
    {
        // Generate ruangan berdasarkan tingkat kelas
        $floor = $class->tingkat <= 3 ? 1 : 2;
        $roomNumber = ($floor * 100) + $class->tingkat;

        return "R. {$roomNumber}".strtoupper($class->nama);
    }
}
