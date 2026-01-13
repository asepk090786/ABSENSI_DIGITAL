<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TahunAjaranSeeder extends Seeder
{
    public function run()
    {
        DB::table('tahun_ajaran')->updateOrInsert(
            ['nama_tahun' => '2025/2026'],
            ['is_active' => 1]
        );
    }
}
