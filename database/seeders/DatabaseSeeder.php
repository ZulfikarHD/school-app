<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     * Memanggil UserSeeder untuk populate default users
     */
    public function run(): void
    {
        $this->call([
            UserSeeder::class,
        ]);
    }
}
