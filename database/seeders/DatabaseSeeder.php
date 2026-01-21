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
            SchoolClassSeeder::class,        // Must run FIRST - creates classes
            SubjectSeeder::class,             // Creates subjects
            UserSeeder::class,                // Creates users + guardian + children (needs classes to exist)
            StudentSeeder::class,             // Creates additional test students
            AttendanceQuickSeeder::class,     // Creates attendance test data
            AttendanceTestSeeder::class,      // Creates more test data
            PaymentSeeder::class,             // Creates payment categories, bills, and sample payments
        ]);
    }
}
