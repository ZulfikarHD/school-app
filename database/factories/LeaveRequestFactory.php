<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\LeaveRequest>
 */
class LeaveRequestFactory extends Factory
{
    /**
     * Define the model's default state.
     * Generate realistic leave requests dengan berbagai status
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $jenis = fake()->randomElement(['IZIN', 'SAKIT']);
        $status = fake()->randomElement(['PENDING', 'PENDING', 'APPROVED', 'REJECTED']); // 50% pending

        // Date range: 1-5 days
        $startDate = fake()->dateTimeBetween('now', '+30 days');
        $duration = fake()->numberBetween(1, 5);
        $endDate = (clone $startDate)->modify("+{$duration} days");

        $data = [
            'student_id' => \App\Models\Student::factory(),
            'jenis' => $jenis,
            'tanggal_mulai' => $startDate,
            'tanggal_selesai' => $endDate,
            'alasan' => $jenis === 'SAKIT'
                ? fake()->randomElement(['Demam tinggi', 'Flu dan batuk', 'Sakit perut', 'Demam berdarah', 'Sakit kepala'])
                : fake()->randomElement(['Acara keluarga', 'Keperluan keluarga', 'Mudik', 'Liburan keluarga', 'Acara pernikahan']),
            'attachment_path' => $jenis === 'SAKIT' ? fake()->optional(0.7)->filePath() : fake()->optional(0.3)->filePath(),
            'status' => $status,
            'submitted_by' => \App\Models\User::factory()->create(['role' => 'PARENT']),
        ];

        // Add review data if not pending
        if ($status !== 'PENDING') {
            $data['reviewed_by'] = \App\Models\User::factory()->create(['role' => fake()->randomElement(['TEACHER', 'ADMIN'])]);
            $data['reviewed_at'] = fake()->dateTimeBetween($startDate, 'now');

            if ($status === 'REJECTED') {
                $data['rejection_reason'] = fake()->randomElement([
                    'Tidak ada surat dokter',
                    'Alasan tidak jelas',
                    'Terlalu sering izin',
                    'Periode ujian',
                ]);
            }
        }

        return $data;
    }
}
