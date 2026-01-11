<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\StudentAttendance>
 */
class StudentAttendanceFactory extends Factory
{
    /**
     * Define the model's default state.
     * Generate realistic student attendance records dengan status yang weighted (lebih banyak hadir)
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        // Weighted status: 80% hadir, 10% izin, 5% sakit, 5% alpha
        $statuses = ['H', 'H', 'H', 'H', 'H', 'H', 'H', 'H', 'I', 'S', 'A'];
        $status = fake()->randomElement($statuses);

        return [
            'student_id' => \App\Models\Student::factory(),
            'class_id' => \App\Models\SchoolClass::factory(),
            'tanggal' => fake()->dateTimeBetween('-30 days', 'now'),
            'status' => $status,
            'keterangan' => $status !== 'H' ? fake()->optional(0.6)->sentence() : null,
            'recorded_by' => \App\Models\User::factory()->create(['role' => 'TEACHER']),
            'recorded_at' => now(),
        ];
    }
}
