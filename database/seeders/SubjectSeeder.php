<?php

namespace Database\Seeders;

use App\Models\Subject;
use Illuminate\Database\Seeder;

class SubjectSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Common subjects for Indonesian schools
        $subjects = [
            ['kode_mapel' => 'MTK', 'nama_mapel' => 'Matematika', 'kategori' => 'UTAMA'],
            ['kode_mapel' => 'IPA', 'nama_mapel' => 'Ilmu Pengetahuan Alam', 'kategori' => 'UTAMA'],
            ['kode_mapel' => 'IPS', 'nama_mapel' => 'Ilmu Pengetahuan Sosial', 'kategori' => 'UTAMA'],
            ['kode_mapel' => 'BHS-IND', 'nama_mapel' => 'Bahasa Indonesia', 'kategori' => 'UTAMA'],
            ['kode_mapel' => 'BHS-ING', 'nama_mapel' => 'Bahasa Inggris', 'kategori' => 'UTAMA'],
            ['kode_mapel' => 'PKN', 'nama_mapel' => 'Pendidikan Kewarganegaraan', 'kategori' => 'UTAMA'],
            ['kode_mapel' => 'AGAMA', 'nama_mapel' => 'Pendidikan Agama', 'kategori' => 'UTAMA'],
            ['kode_mapel' => 'PJOK', 'nama_mapel' => 'Pendidikan Jasmani dan Kesehatan', 'kategori' => 'UTAMA'],
            ['kode_mapel' => 'SBK', 'nama_mapel' => 'Seni Budaya dan Keterampilan', 'kategori' => 'PENGEMBANGAN_DIRI'],
            ['kode_mapel' => 'TIK', 'nama_mapel' => 'Teknologi Informasi dan Komunikasi', 'kategori' => 'MUATAN_LOKAL'],
        ];

        foreach ($subjects as $subject) {
            Subject::firstOrCreate(
                ['kode_mapel' => $subject['kode_mapel']],
                $subject
            );
        }

        // Note: Teacher-subject assignments are done in UserSeeder
        // after teachers are created to ensure proper assignment

        $this->command->info('Created '.count($subjects).' subjects.');
    }
}
