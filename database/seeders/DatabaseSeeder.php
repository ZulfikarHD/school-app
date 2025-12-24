<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     * Memanggil seeders untuk populate default data:
     * - UserSeeder: Default users untuk setiap role
     * - SchoolClassSeeder: Sample classes (1A-6D)
     * - StudentSeeder: Sample students dengan guardians (optional, untuk development)
     */
    public function run(): void
    {
        $this->call([
            UserSeeder::class,
            SchoolClassSeeder::class,
            // Uncomment untuk populate sample students (development only)
            StudentSeeder::class,
        ]);
    }
}
