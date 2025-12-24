<?php

namespace Database\Seeders;

use App\Models\Guardian;
use App\Models\SchoolClass;
use App\Models\Student;
use App\Models\StudentClassHistory;
use Illuminate\Database\Seeder;

class StudentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * Membuat sample students dengan guardians menggunakan factories
     * untuk development dan testing purposes
     */
    public function run(): void
    {
        // Get existing classes untuk assign students
        $classes = SchoolClass::active()->get();

        if ($classes->isEmpty()) {
            $this->command->warn('No active classes found. Please run SchoolClassSeeder first.');

            return;
        }

        // Create 50 students dengan random assignment ke classes
        $students = Student::factory()
            ->count(50)
            ->create(function (array $attributes) use ($classes) {
                // Assign random class dari existing classes
                $randomClass = $classes->random();

                return [
                    'kelas_id' => $randomClass->id,
                    'tahun_ajaran_masuk' => $randomClass->tahun_ajaran,
                ];
            });

        // Create guardians dan attach ke students
        foreach ($students as $student) {
            // Create ayah (70% chance)
            if (rand(1, 100) <= 70) {
                $ayah = Guardian::factory()->ayah()->create();
                $student->guardians()->attach($ayah->id, [
                    'is_primary_contact' => rand(1, 100) <= 60, // 60% chance ayah jadi primary
                ]);
            }

            // Create ibu (90% chance)
            if (rand(1, 100) <= 90) {
                $ibu = Guardian::factory()->ibu()->create();
                $isPrimary = ! $student->guardians()->wherePivot('is_primary_contact', true)->exists();
                $student->guardians()->attach($ibu->id, [
                    'is_primary_contact' => $isPrimary, // Ibu jadi primary jika ayah belum primary
                ]);
            }

            // Create wali (10% chance, hanya jika tidak ada ayah atau ibu)
            if (rand(1, 100) <= 10 && $student->guardians()->count() === 0) {
                $wali = Guardian::factory()->wali()->create();
                $student->guardians()->attach($wali->id, [
                    'is_primary_contact' => true,
                ]);
            }

            // Create class history untuk tahun ajaran masuk
            StudentClassHistory::create([
                'student_id' => $student->id,
                'kelas_id' => $student->kelas_id,
                'tahun_ajaran' => $student->tahun_ajaran_masuk,
                'wali_kelas' => SchoolClass::find($student->kelas_id)?->waliKelas?->name,
            ]);
        }

        $this->command->info("Created {$students->count()} students with guardians.");
    }
}
