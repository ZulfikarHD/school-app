<?php

namespace Database\Factories;

use App\Enums\Hari;
use App\Models\AcademicYear;
use App\Models\SchoolClass;
use App\Models\Subject;
use App\Models\Teacher;
use App\Models\TeachingSchedule;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * TeachingScheduleFactory - Factory untuk generate sample jadwal mengajar
 *
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\TeachingSchedule>
 */
class TeachingScheduleFactory extends Factory
{
    protected $model = TeachingSchedule::class;

    /**
     * Time slots yang umum digunakan di sekolah (format H:i)
     * Setiap slot 45 menit (1 jam pelajaran)
     *
     * @var array<array{start: string, end: string}>
     */
    private array $timeSlots = [
        ['start' => '07:00', 'end' => '07:45'],
        ['start' => '07:45', 'end' => '08:30'],
        ['start' => '08:30', 'end' => '09:15'],
        ['start' => '09:30', 'end' => '10:15'], // Setelah istirahat 1
        ['start' => '10:15', 'end' => '11:00'],
        ['start' => '11:00', 'end' => '11:45'],
        ['start' => '12:30', 'end' => '13:15'], // Setelah istirahat 2
        ['start' => '13:15', 'end' => '14:00'],
        ['start' => '14:00', 'end' => '14:45'],
        ['start' => '14:45', 'end' => '15:30'],
    ];

    /**
     * Daftar ruangan yang tersedia
     *
     * @var array<string>
     */
    private array $ruanganList = [
        'R. 101', 'R. 102', 'R. 103', 'R. 104', 'R. 105', 'R. 106',
        'R. 201', 'R. 202', 'R. 203', 'R. 204', 'R. 205', 'R. 206',
        'Lab Komputer', 'Lab IPA', 'Perpustakaan', 'Aula',
    ];

    /**
     * Define the model's default state untuk generate realistic schedule data
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $timeSlot = fake()->randomElement($this->timeSlots);

        return [
            'teacher_id' => Teacher::factory(),
            'subject_id' => Subject::factory(),
            'class_id' => SchoolClass::factory(),
            'academic_year_id' => AcademicYear::factory(),
            'hari' => fake()->randomElement(Hari::cases())->value,
            'jam_mulai' => $timeSlot['start'],
            'jam_selesai' => $timeSlot['end'],
            'ruangan' => fake()->randomElement($this->ruanganList),
            'is_active' => true,
        ];
    }

    /**
     * State untuk jadwal dengan durasi 2 jam pelajaran (90 menit)
     */
    public function doubleLesson(): static
    {
        $doubleSlots = [
            ['start' => '07:00', 'end' => '08:30'],
            ['start' => '08:30', 'end' => '10:00'],
            ['start' => '10:15', 'end' => '11:45'],
            ['start' => '12:30', 'end' => '14:00'],
            ['start' => '14:00', 'end' => '15:30'],
        ];

        $slot = fake()->randomElement($doubleSlots);

        return $this->state(fn (array $attributes) => [
            'jam_mulai' => $slot['start'],
            'jam_selesai' => $slot['end'],
        ]);
    }

    /**
     * State untuk jadwal hari Senin
     */
    public function senin(): static
    {
        return $this->state(fn (array $attributes) => [
            'hari' => Hari::SENIN->value,
        ]);
    }

    /**
     * State untuk jadwal hari Selasa
     */
    public function selasa(): static
    {
        return $this->state(fn (array $attributes) => [
            'hari' => Hari::SELASA->value,
        ]);
    }

    /**
     * State untuk jadwal hari Rabu
     */
    public function rabu(): static
    {
        return $this->state(fn (array $attributes) => [
            'hari' => Hari::RABU->value,
        ]);
    }

    /**
     * State untuk jadwal hari Kamis
     */
    public function kamis(): static
    {
        return $this->state(fn (array $attributes) => [
            'hari' => Hari::KAMIS->value,
        ]);
    }

    /**
     * State untuk jadwal hari Jumat
     */
    public function jumat(): static
    {
        return $this->state(fn (array $attributes) => [
            'hari' => Hari::JUMAT->value,
        ]);
    }

    /**
     * State untuk jadwal hari Sabtu
     */
    public function sabtu(): static
    {
        return $this->state(fn (array $attributes) => [
            'hari' => Hari::SABTU->value,
        ]);
    }

    /**
     * State untuk jadwal pagi (sebelum jam 10)
     */
    public function morning(): static
    {
        $morningSlots = array_filter($this->timeSlots, fn ($slot) => $slot['start'] < '10:00');
        $slot = fake()->randomElement($morningSlots);

        return $this->state(fn (array $attributes) => [
            'jam_mulai' => $slot['start'],
            'jam_selesai' => $slot['end'],
        ]);
    }

    /**
     * State untuk jadwal siang (setelah jam 12)
     */
    public function afternoon(): static
    {
        $afternoonSlots = array_filter($this->timeSlots, fn ($slot) => $slot['start'] >= '12:30');
        $slot = fake()->randomElement(array_values($afternoonSlots));

        return $this->state(fn (array $attributes) => [
            'jam_mulai' => $slot['start'],
            'jam_selesai' => $slot['end'],
        ]);
    }

    /**
     * State untuk jadwal yang tidak aktif
     */
    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }

    /**
     * State untuk jadwal tanpa ruangan (ruangan nullable)
     */
    public function withoutRoom(): static
    {
        return $this->state(fn (array $attributes) => [
            'ruangan' => null,
        ]);
    }

    /**
     * State untuk jadwal di Lab Komputer
     */
    public function labKomputer(): static
    {
        return $this->state(fn (array $attributes) => [
            'ruangan' => 'Lab Komputer',
        ]);
    }

    /**
     * State untuk jadwal di Lab IPA
     */
    public function labIpa(): static
    {
        return $this->state(fn (array $attributes) => [
            'ruangan' => 'Lab IPA',
        ]);
    }

    /**
     * Configure the model factory to use existing records if available
     * untuk menghindari constraint violations
     */
    public function configure(): static
    {
        return $this->afterMaking(function (TeachingSchedule $schedule) {
            // Use existing teacher if none specified
            if (! $schedule->teacher_id) {
                $teacher = Teacher::active()->inRandomOrder()->first();
                if ($teacher) {
                    $schedule->teacher_id = $teacher->id;
                }
            }

            // Use existing subject if none specified
            if (! $schedule->subject_id) {
                $subject = Subject::where('is_active', true)->inRandomOrder()->first();
                if ($subject) {
                    $schedule->subject_id = $subject->id;
                }
            }

            // Use existing class if none specified
            if (! $schedule->class_id) {
                $class = SchoolClass::where('is_active', true)->inRandomOrder()->first();
                if ($class) {
                    $schedule->class_id = $class->id;
                }
            }

            // Use active academic year if none specified
            if (! $schedule->academic_year_id) {
                $academicYear = AcademicYear::active()->first()
                    ?? AcademicYear::orderBy('id', 'desc')->first();
                if ($academicYear) {
                    $schedule->academic_year_id = $academicYear->id;
                }
            }
        });
    }
}
