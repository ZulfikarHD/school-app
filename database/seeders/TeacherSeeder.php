<?php

namespace Database\Seeders;

use App\Enums\StatusKepegawaian;
use App\Models\Subject;
use App\Models\Teacher;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class TeacherSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * Membuat sample data guru dengan berbagai status kepegawaian
     * dan mengassign mata pelajaran ke masing-masing guru
     */
    public function run(): void
    {
        // Data guru yang akan di-seed
        $teachers = [
            [
                'nama_lengkap' => 'Budi Santoso, S.Pd., M.Pd.',
                'jenis_kelamin' => 'L',
                'status_kepegawaian' => StatusKepegawaian::TETAP,
                'kualifikasi_pendidikan' => 'S2',
                'subjects' => ['MTK'],
            ],
            [
                'nama_lengkap' => 'Siti Rahayu, S.Pd.',
                'jenis_kelamin' => 'P',
                'status_kepegawaian' => StatusKepegawaian::TETAP,
                'kualifikasi_pendidikan' => 'S1',
                'subjects' => ['BHS-IND'],
            ],
            [
                'nama_lengkap' => 'Ahmad Wijaya, S.Pd.',
                'jenis_kelamin' => 'L',
                'status_kepegawaian' => StatusKepegawaian::TETAP,
                'kualifikasi_pendidikan' => 'S1',
                'subjects' => ['IPA'],
            ],
            [
                'nama_lengkap' => 'Dewi Lestari, S.Pd.',
                'jenis_kelamin' => 'P',
                'status_kepegawaian' => StatusKepegawaian::TETAP,
                'kualifikasi_pendidikan' => 'S1',
                'subjects' => ['IPS'],
            ],
            [
                'nama_lengkap' => 'Eko Prasetyo, S.Pd.',
                'jenis_kelamin' => 'L',
                'status_kepegawaian' => StatusKepegawaian::HONORER,
                'kualifikasi_pendidikan' => 'S1',
                'subjects' => ['BHS-ING'],
            ],
            [
                'nama_lengkap' => 'Fitri Handayani, S.Pd.',
                'jenis_kelamin' => 'P',
                'status_kepegawaian' => StatusKepegawaian::HONORER,
                'kualifikasi_pendidikan' => 'S1',
                'subjects' => ['PKN'],
            ],
            [
                'nama_lengkap' => 'Gunawan Setiawan, S.Ag.',
                'jenis_kelamin' => 'L',
                'status_kepegawaian' => StatusKepegawaian::KONTRAK,
                'kualifikasi_pendidikan' => 'S1',
                'subjects' => ['AGAMA'],
            ],
            [
                'nama_lengkap' => 'Hesti Pratiwi, S.Pd.',
                'jenis_kelamin' => 'P',
                'status_kepegawaian' => StatusKepegawaian::KONTRAK,
                'kualifikasi_pendidikan' => 'S1',
                'subjects' => ['PJOK'],
            ],
            [
                'nama_lengkap' => 'Irwan Kurniawan, S.Sn.',
                'jenis_kelamin' => 'L',
                'status_kepegawaian' => StatusKepegawaian::HONORER,
                'kualifikasi_pendidikan' => 'S1',
                'subjects' => ['SBK'],
            ],
            [
                'nama_lengkap' => 'Jasmine Putri, S.Kom.',
                'jenis_kelamin' => 'P',
                'status_kepegawaian' => StatusKepegawaian::KONTRAK,
                'kualifikasi_pendidikan' => 'S1',
                'subjects' => ['TIK'],
            ],
        ];

        $year = now()->year;
        $tahunAjaran = $year.'/'.($year + 1);

        foreach ($teachers as $index => $data) {
            // Generate unique identifiers
            $nik = '32'.str_pad(fake()->unique()->numberBetween(1, 99999999999999), 14, '0', STR_PAD_LEFT);
            $nip = $data['status_kepegawaian'] === StatusKepegawaian::HONORER
                ? null
                : '19'.str_pad(fake()->unique()->numberBetween(1, 9999999999999999), 16, '0', STR_PAD_LEFT);

            // Generate email from name
            $emailName = strtolower(str_replace([' ', '.', ','], '', explode(',', $data['nama_lengkap'])[0]));
            $email = $emailName.'@sekolah.sch.id';

            // Create user account for teacher
            $user = User::create([
                'name' => $data['nama_lengkap'],
                'username' => 'guru'.($index + 1),
                'email' => $email,
                'password' => Hash::make('password'),
                'role' => 'TEACHER',
                'status' => 'ACTIVE',
                'is_first_login' => true,
                'phone_number' => '08'.fake()->numerify('##########'),
            ]);

            // Create teacher record
            $teacher = Teacher::create([
                'user_id' => $user->id,
                'nip' => $nip,
                'nik' => $nik,
                'nama_lengkap' => $data['nama_lengkap'],
                'tempat_lahir' => fake()->randomElement([
                    'Jakarta', 'Bandung', 'Surabaya', 'Semarang', 'Yogyakarta',
                ]),
                'tanggal_lahir' => fake()->dateTimeBetween('-55 years', '-25 years'),
                'jenis_kelamin' => $data['jenis_kelamin'],
                'alamat' => fake()->address(),
                'no_hp' => $user->phone_number,
                'email' => $email,
                'foto' => null,
                'status_kepegawaian' => $data['status_kepegawaian']->value,
                'tanggal_mulai_kerja' => fake()->dateTimeBetween('-15 years', '-1 year'),
                'tanggal_berakhir_kontrak' => $data['status_kepegawaian'] === StatusKepegawaian::KONTRAK
                    ? fake()->dateTimeBetween('+1 month', '+2 years')
                    : null,
                'kualifikasi_pendidikan' => $data['kualifikasi_pendidikan'],
                'is_active' => true,
            ]);

            // Assign subjects to teacher
            foreach ($data['subjects'] as $subjectCode) {
                $subject = Subject::where('kode_mapel', $subjectCode)->first();
                if ($subject) {
                    $teacher->subjects()->attach($subject->id, [
                        'tahun_ajaran' => $tahunAjaran,
                        'is_primary' => true,
                        'class_id' => null, // Mengajar di semua kelas
                    ]);
                }
            }
        }

        $this->command->info('Created '.count($teachers).' teachers with user accounts and subject assignments.');
    }
}
