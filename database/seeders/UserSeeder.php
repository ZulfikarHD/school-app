<?php

namespace Database\Seeders;

use App\Models\Guardian;
use App\Models\SchoolClass;
use App\Models\Student;
use App\Models\StudentClassHistory;
use App\Models\Subject;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * Membuat default users untuk testing setiap role,
     * yaitu: SUPERADMIN, PRINCIPAL, ADMIN, TEACHER, PARENT
     * Note: STUDENT user disabled - untuk future implementation
     */
    public function run(): void
    {
        // SUPERADMIN - untuk development dan full system access
        User::create([
            'name' => 'Super Administrator',
            'username' => 'superadmin',
            'email' => 'superadmin@sekolah.app',
            'password' => Hash::make('Sekolah123'),
            'role' => 'SUPERADMIN',
            'status' => 'ACTIVE',
            'is_first_login' => false,
            'phone_number' => '081234567890',
        ]);

        // PRINCIPAL - Kepala Sekolah
        User::create([
            'name' => 'Dr. Ahmad Hidayat',
            'username' => 'kepala.sekolah',
            'email' => 'kepala@sekolah.app',
            'password' => Hash::make('Sekolah123'),
            'role' => 'PRINCIPAL',
            'status' => 'ACTIVE',
            'is_first_login' => true,
            'phone_number' => '081234567891',
        ]);

        // ADMIN - Staf TU
        User::create([
            'name' => 'Siti Nurhaliza',
            'username' => 'bu.siti',
            'email' => 'siti@sekolah.app',
            'password' => Hash::make('Sekolah123'),
            'role' => 'ADMIN',
            'status' => 'ACTIVE',
            'is_first_login' => true,
            'phone_number' => '081234567892',
        ]);

        // TEACHER - Guru
        $teacherUser = User::create([
            'name' => 'Budi Santoso',
            'username' => 'pak.budi',
            'email' => 'budi@sekolah.app',
            'password' => Hash::make('Sekolah123'),
            'role' => 'TEACHER',
            'status' => 'ACTIVE',
            'is_first_login' => true,
            'phone_number' => '081234567893',
        ]);

        // Assign teacher as wali kelas (homeroom teacher) for multiple classes
        // This allows the teacher to input attendance for these classes
        $teacherClasses = [
            ['tingkat' => 1, 'nama' => 'A'],
            ['tingkat' => 2, 'nama' => 'B'],
            ['tingkat' => 3, 'nama' => 'A'],
            ['tingkat' => 4, 'nama' => 'C'],
            ['tingkat' => 5, 'nama' => 'B'],
        ];

        $assignedClassIds = [];
        foreach ($teacherClasses as $classInfo) {
            $class = SchoolClass::where('tingkat', $classInfo['tingkat'])
                ->where('nama', $classInfo['nama'])
                ->first();
            if ($class) {
                $class->update(['wali_kelas_id' => $teacherUser->id]);
                $assignedClassIds[] = $class->id;
            }
        }

        // Assign subjects to teacher
        // Teacher will teach Matematika, IPA, and Bahasa Indonesia to their assigned classes
        $matematika = Subject::where('kode_mapel', 'MTK')->first();
        $ipa = Subject::where('kode_mapel', 'IPA')->first();
        $bahasaIndonesia = Subject::where('kode_mapel', 'BHS-IND')->first();

        $tahunAjaran = '2024/2025';

        // Assign Matematika to all teacher's classes
        if ($matematika) {
            foreach ($assignedClassIds as $classId) {
                DB::table('teacher_subjects')->insert([
                    'teacher_id' => $teacherUser->id,
                    'subject_id' => $matematika->id,
                    'class_id' => $classId,
                    'tahun_ajaran' => $tahunAjaran,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }

        // Assign IPA to some classes (1A, 2B, 3A)
        if ($ipa && count($assignedClassIds) >= 3) {
            for ($i = 0; $i < 3; $i++) {
                DB::table('teacher_subjects')->insert([
                    'teacher_id' => $teacherUser->id,
                    'subject_id' => $ipa->id,
                    'class_id' => $assignedClassIds[$i],
                    'tahun_ajaran' => $tahunAjaran,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }

        // Assign Bahasa Indonesia to classes 4C and 5B
        if ($bahasaIndonesia && count($assignedClassIds) >= 5) {
            for ($i = 3; $i < 5; $i++) {
                DB::table('teacher_subjects')->insert([
                    'teacher_id' => $teacherUser->id,
                    'subject_id' => $bahasaIndonesia->id,
                    'class_id' => $assignedClassIds[$i],
                    'tahun_ajaran' => $tahunAjaran,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }

        // PARENT - Orang Tua
        $parentUser = User::create([
            'name' => 'Ani Wijaya',
            'username' => 'ibu.ani',
            'email' => 'ani@parent.com',
            'password' => Hash::make('Sekolah123'),
            'role' => 'PARENT',
            'status' => 'ACTIVE',
            'is_first_login' => true,
            'phone_number' => '081234567894',
        ]);

        // Create Guardian record for PARENT user
        $guardian = Guardian::create([
            'user_id' => $parentUser->id,
            'nik' => '3201234567890123',
            'nama_lengkap' => 'Ani Wijaya',
            'hubungan' => 'ibu',
            'pekerjaan' => 'Ibu Rumah Tangga',
            'pendidikan' => 'S1',
            'penghasilan' => '>5jt', // ENUM: '<1jt', '1-3jt', '3-5jt', '>5jt'
            'no_hp' => '081234567894',
            'email' => 'ani@parent.com',
            'alamat' => 'Jl. Contoh No. 123, Jakarta Selatan',
        ]);

        // Create children (students) for the parent
        // Get some classes to assign the children
        $class3A = SchoolClass::where('tingkat', 3)->where('nama', 'A')->first();
        $class5B = SchoolClass::where('tingkat', 5)->where('nama', 'B')->first();

        if ($class3A && $class5B) {
            // Create first child - Raka Pratama (Grade 3)
            $child1 = Student::create([
                'nis' => '2023001',
                'nisn' => '0031234567',
                'nik' => '3201234567890001',
                'nama_lengkap' => 'Raka Pratama Wijaya',
                'nama_panggilan' => 'Raka',
                'tempat_lahir' => 'Jakarta',
                'tanggal_lahir' => '2014-05-15',
                'jenis_kelamin' => 'L',
                'agama' => 'Islam',
                'anak_ke' => 1,
                'jumlah_saudara' => 2,
                'status_keluarga' => 'Anak Kandung',
                'alamat' => 'Jl. Contoh No. 123',
                'rt_rw' => '001/002',
                'kelurahan' => 'Kebayoran Baru',
                'kecamatan' => 'Kebayoran Baru',
                'kota' => 'Jakarta Selatan',
                'provinsi' => 'DKI Jakarta',
                'kode_pos' => '12180',
                'no_hp' => '081234567894',
                'kelas_id' => $class3A->id,
                'tahun_ajaran_masuk' => '2021/2022',
                'tanggal_masuk' => '2021-07-12',
                'status' => 'aktif',
            ]);

            // Link child1 to guardian
            $child1->guardians()->attach($guardian->id, [
                'is_primary_contact' => true,
            ]);

            // Create class history for child1
            StudentClassHistory::create([
                'student_id' => $child1->id,
                'kelas_id' => $class3A->id,
                'tahun_ajaran' => '2021/2022',
                'wali_kelas' => $class3A->waliKelas?->name,
            ]);

            // Create second child - Sinta Dewi (Grade 5)
            $child2 = Student::create([
                'nis' => '2021015',
                'nisn' => '0031234568',
                'nik' => '3201234567890002',
                'nama_lengkap' => 'Sinta Dewi Wijaya',
                'nama_panggilan' => 'Sinta',
                'tempat_lahir' => 'Jakarta',
                'tanggal_lahir' => '2012-08-20',
                'jenis_kelamin' => 'P',
                'agama' => 'Islam',
                'anak_ke' => 2,
                'jumlah_saudara' => 2,
                'status_keluarga' => 'Anak Kandung',
                'alamat' => 'Jl. Contoh No. 123',
                'rt_rw' => '001/002',
                'kelurahan' => 'Kebayoran Baru',
                'kecamatan' => 'Kebayoran Baru',
                'kota' => 'Jakarta Selatan',
                'provinsi' => 'DKI Jakarta',
                'kode_pos' => '12180',
                'no_hp' => '081234567894',
                'kelas_id' => $class5B->id,
                'tahun_ajaran_masuk' => '2019/2020',
                'tanggal_masuk' => '2019-07-15',
                'status' => 'aktif',
            ]);

            // Link child2 to guardian
            $child2->guardians()->attach($guardian->id, [
                'is_primary_contact' => false,
            ]);

            // Create class history for child2
            StudentClassHistory::create([
                'student_id' => $child2->id,
                'kelas_id' => $class5B->id,
                'tahun_ajaran' => '2019/2020',
                'wali_kelas' => $class5B->waliKelas?->name,
            ]);

            // Create father guardian (without user account) for completeness
            $father = Guardian::create([
                'user_id' => null, // Father doesn't have portal account
                'nik' => '3201234567890124',
                'nama_lengkap' => 'Budi Wijaya',
                'hubungan' => 'ayah',
                'pekerjaan' => 'Pegawai Swasta',
                'pendidikan' => 'S1',
                'penghasilan' => '>5jt', // ENUM: '<1jt', '1-3jt', '3-5jt', '>5jt'
                'no_hp' => '081234567895',
                'email' => null,
                'alamat' => 'Jl. Contoh No. 123, Jakarta Selatan',
            ]);

            // Link father to both children
            $child1->guardians()->attach($father->id, [
                'is_primary_contact' => false,
            ]);
            $child2->guardians()->attach($father->id, [
                'is_primary_contact' => false,
            ]);
        }

        // STUDENT - DISABLED untuk future implementation
        // TODO: Uncomment ketika Student Portal sudah siap
        // User::create([
        //     'name' => 'Raka Pratama',
        //     'username' => 'raka.pratama',
        //     'email' => 'raka@student.com',
        //     'password' => Hash::make('Sekolah123'),
        //     'role' => 'STUDENT',
        //     'status' => 'ACTIVE',
        //     'is_first_login' => true,
        //     'phone_number' => '081234567895',
        // ]);
    }
}
