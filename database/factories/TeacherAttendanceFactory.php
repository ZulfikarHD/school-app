<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\TeacherAttendance>
 */
class TeacherAttendanceFactory extends Factory
{
    /**
     * Define the model's default state.
     * Generate realistic teacher clock in/out records dengan late detection
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        // Random clock in time between 06:30 - 08:30
        $clockInHour = fake()->numberBetween(6, 8);
        $clockInMinute = fake()->numberBetween(0, 59);

        // School starts at 07:30, check if late
        $isLate = ($clockInHour > 7) || ($clockInHour === 7 && $clockInMinute > 30);

        // Clock out time: 8-9 hours after clock in
        $workHours = fake()->numberBetween(8, 9);
        $clockOutHour = $clockInHour + $workHours;
        $clockOutMinute = fake()->numberBetween(0, 59);

        // GPS coordinates untuk area Jakarta (contoh: -6.xxx, 106.xxx)
        $latitudeIn = fake()->latitude(-6.3, -6.1);
        $longitudeIn = fake()->longitude(106.7, 106.9);

        return [
            'teacher_id' => \App\Models\User::factory()->create(['role' => 'TEACHER']),
            'tanggal' => fake()->dateTimeBetween('-30 days', 'now'),
            'clock_in' => sprintf('%02d:%02d:00', $clockInHour, $clockInMinute),
            'clock_out' => sprintf('%02d:%02d:00', $clockOutHour, $clockOutMinute),
            'latitude_in' => $latitudeIn,
            'longitude_in' => $longitudeIn,
            'latitude_out' => fake()->latitude(-6.3, -6.1),
            'longitude_out' => fake()->longitude(106.7, 106.9),
            'is_late' => $isLate,
            'status' => $isLate ? 'TERLAMBAT' : 'HADIR',
            'keterangan' => $isLate ? fake()->optional(0.3)->sentence() : null,
        ];
    }
}
