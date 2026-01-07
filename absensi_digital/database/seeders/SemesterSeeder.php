<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SemesterSeeder extends Seeder
{
    public function run()
    {
        $tahun = DB::table('tahun_ajaran')->where('is_active',1)->first();
        if ($tahun) {
            DB::table('semester')->updateOrInsert(
                ['tahun_ajaran_id' => $tahun->id, 'nama_semester' => 'Ganjil'],
                ['is_active' => 1]
            );
        }
    }
}
