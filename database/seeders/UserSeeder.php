<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
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
        User::create([
            'name' => 'Budi Santoso',
            'username' => 'pak.budi',
            'email' => 'budi@sekolah.app',
            'password' => Hash::make('Sekolah123'),
            'role' => 'TEACHER',
            'status' => 'ACTIVE',
            'is_first_login' => true,
            'phone_number' => '081234567893',
        ]);

        // PARENT - Orang Tua
        User::create([
            'name' => 'Ani Wijaya',
            'username' => 'ibu.ani',
            'email' => 'ani@parent.com',
            'password' => Hash::make('Sekolah123'),
            'role' => 'PARENT',
            'status' => 'ACTIVE',
            'is_first_login' => true,
            'phone_number' => '081234567894',
        ]);

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
