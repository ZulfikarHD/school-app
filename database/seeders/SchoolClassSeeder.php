<?php

namespace Database\Seeders;

use App\Models\SchoolClass;
use App\Models\User;
use Illuminate\Database\Seeder;

class SchoolClassSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $tahunAjaran = '2024/2025';
        $classes = ['A', 'B', 'C', 'D'];

        // Get all teachers
        $teachers = User::where('role', 'TEACHER')->get();
        $teacherIndex = 0;

        // Create classes for levels 1-6
        for ($tingkat = 1; $tingkat <= 6; $tingkat++) {
            foreach ($classes as $nama) {
                // Assign teacher cyclically if available
                $waliKelasId = null;
                if ($teachers->count() > 0) {
                    $waliKelasId = $teachers[$teacherIndex % $teachers->count()]->id;
                    $teacherIndex++;
                }

                SchoolClass::create([
                    'tingkat' => $tingkat,
                    'nama' => $nama,
                    'kapasitas' => 40,
                    'tahun_ajaran' => $tahunAjaran,
                    'wali_kelas_id' => $waliKelasId,
                    'is_active' => true,
                ]);
            }
        }
    }
}
