<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\SubjectAttendance>
 */
class SubjectAttendanceFactory extends Factory
{
    /**
     * Define the model's default state.
     * Generate realistic subject attendance records per jam pelajaran
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        // Weighted status: 85% hadir, 8% izin, 4% sakit, 3% alpha
        $statuses = ['H', 'H', 'H', 'H', 'H', 'H', 'H', 'H', 'H', 'I', 'S', 'A'];
        $status = fake()->randomElement($statuses);

        return [
            'student_id' => \App\Models\Student::factory(),
            'class_id' => \App\Models\SchoolClass::factory(),
            'subject_id' => \App\Models\Subject::factory(),
            'teacher_id' => \App\Models\User::factory()->create(['role' => 'TEACHER']),
            'tanggal' => fake()->dateTimeBetween('-30 days', 'now'),
            'jam_ke' => fake()->numberBetween(1, 10), // Jam ke 1-10
            'status' => $status,
            'keterangan' => $status !== 'H' ? fake()->optional(0.5)->sentence() : null,
        ];
    }
}
