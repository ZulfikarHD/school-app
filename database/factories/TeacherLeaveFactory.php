<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\TeacherLeave>
 */
class TeacherLeaveFactory extends Factory
{
    /**
     * Define the model's default state.
     * Generate realistic teacher leave requests dengan berbagai jenis cuti
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $jenis = fake()->randomElement(['IZIN', 'SAKIT', 'CUTI']);
        $status = fake()->randomElement(['PENDING', 'PENDING', 'APPROVED', 'REJECTED']); // 50% pending

        // Date range: 1-7 days (teachers usually take longer leave)
        $startDate = fake()->dateTimeBetween('now', '+60 days');
        $duration = fake()->numberBetween(1, 7);
        $endDate = (clone $startDate)->modify("+{$duration} days");

        // Calculate jumlah hari (excluding weekends - simplified)
        $jumlahHari = $duration + 1;

        $alasan = match ($jenis) {
            'SAKIT' => fake()->randomElement([
                'Demam tinggi dan perlu istirahat',
                'Rawat inap di rumah sakit',
                'Operasi kecil',
                'Sakit flu berat',
                'Covid-19',
            ]),
            'CUTI' => fake()->randomElement([
                'Cuti tahunan',
                'Keperluan keluarga mendesak',
                'Pernikahan saudara',
                'Umroh',
                'Liburan keluarga',
            ]),
            default => fake()->randomElement([
                'Keperluan mendadak',
                'Acara keluarga',
                'Urusan penting',
            ]),
        };

        $data = [
            'teacher_id' => \App\Models\User::factory()->create(['role' => 'TEACHER']),
            'jenis' => $jenis,
            'tanggal_mulai' => $startDate,
            'tanggal_selesai' => $endDate,
            'jumlah_hari' => $jumlahHari,
            'alasan' => $alasan,
            'attachment_path' => ($jenis === 'SAKIT' || $jenis === 'CUTI') ? fake()->optional(0.8)->filePath() : fake()->optional(0.3)->filePath(),
            'status' => $status,
        ];

        // Add review data if not pending
        if ($status !== 'PENDING') {
            $data['reviewed_by'] = \App\Models\User::factory()->create(['role' => fake()->randomElement(['ADMIN', 'PRINCIPAL'])]);
            $data['reviewed_at'] = fake()->dateTimeBetween($startDate, 'now');

            if ($status === 'REJECTED') {
                $data['rejection_reason'] = fake()->randomElement([
                    'Tidak ada pengganti guru',
                    'Periode ujian',
                    'Terlalu banyak guru yang cuti',
                    'Alasan tidak memadai',
                    'Dokumen tidak lengkap',
                ]);
            }
        }

        return $data;
    }
}
