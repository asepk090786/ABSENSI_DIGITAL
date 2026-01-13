<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            \Database\Seeders\RoleSeeder::class,
            \Database\Seeders\DemoUserSeeder::class,
            \Database\Seeders\TahunAjaranSeeder::class,
            \Database\Seeders\SemesterSeeder::class,
            \Database\Seeders\JamBelajarSeeder::class,
        ]);
    }
}
