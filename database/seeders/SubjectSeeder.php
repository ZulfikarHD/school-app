<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SubjectSeeder extends Seeder
{
    /**
     * Seed mata pelajaran standar untuk Sekolah Dasar (SD)
     * yang mencakup mata pelajaran utama, muatan lokal, dan pengembangan diri
     * sesuai dengan Kurikulum Merdeka
     */
    public function run(): void
    {
        $subjects = [
            // Mata Pelajaran Utama
            [
                'kode_mapel' => 'MAT',
                'nama_mapel' => 'Matematika',
                'tingkat' => null,
                'kategori' => 'UTAMA',
                'is_active' => true,
            ],
            [
                'kode_mapel' => 'IPA',
                'nama_mapel' => 'Ilmu Pengetahuan Alam',
                'tingkat' => null,
                'kategori' => 'UTAMA',
                'is_active' => true,
            ],
            [
                'kode_mapel' => 'IPS',
                'nama_mapel' => 'Ilmu Pengetahuan Sosial',
                'tingkat' => null,
                'kategori' => 'UTAMA',
                'is_active' => true,
            ],
            [
                'kode_mapel' => 'BIN',
                'nama_mapel' => 'Bahasa Indonesia',
                'tingkat' => null,
                'kategori' => 'UTAMA',
                'is_active' => true,
            ],
            [
                'kode_mapel' => 'BING',
                'nama_mapel' => 'Bahasa Inggris',
                'tingkat' => null,
                'kategori' => 'UTAMA',
                'is_active' => true,
            ],
            [
                'kode_mapel' => 'PJOK',
                'nama_mapel' => 'Pendidikan Jasmani, Olahraga, dan Kesehatan',
                'tingkat' => null,
                'kategori' => 'UTAMA',
                'is_active' => true,
            ],
            [
                'kode_mapel' => 'PAI',
                'nama_mapel' => 'Pendidikan Agama Islam',
                'tingkat' => null,
                'kategori' => 'UTAMA',
                'is_active' => true,
            ],
            [
                'kode_mapel' => 'PPKn',
                'nama_mapel' => 'Pendidikan Pancasila dan Kewarganegaraan',
                'tingkat' => null,
                'kategori' => 'UTAMA',
                'is_active' => true,
            ],
            [
                'kode_mapel' => 'SBK',
                'nama_mapel' => 'Seni Budaya dan Keterampilan',
                'tingkat' => null,
                'kategori' => 'UTAMA',
                'is_active' => true,
            ],

            // Muatan Lokal (contoh, bisa disesuaikan)
            [
                'kode_mapel' => 'BJW',
                'nama_mapel' => 'Bahasa Jawa',
                'tingkat' => null,
                'kategori' => 'MUATAN_LOKAL',
                'is_active' => true,
            ],

            // Pengembangan Diri
            [
                'kode_mapel' => 'BTA',
                'nama_mapel' => 'Baca Tulis Al-Quran',
                'tingkat' => null,
                'kategori' => 'PENGEMBANGAN_DIRI',
                'is_active' => true,
            ],
        ];

        DB::table('subjects')->insert(array_map(function ($subject) {
            return array_merge($subject, [
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }, $subjects));
    }
}
