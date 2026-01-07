<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class JamBelajarSeeder extends Seeder
{
    public function run()
    {
        $entries = [
            ['hari'=>'Senin','jam_mulai'=>'07:00:00','jam_selesai'=>'07:45:00','jenis'=>'KBM'],
            ['hari'=>'Senin','jam_mulai'=>'07:45:00','jam_selesai'=>'08:30:00','jenis'=>'KBM'],
            ['hari'=>'Senin','jam_mulai'=>'10:00:00','jam_selesai'=>'10:15:00','jenis'=>'Istirahat'],
            ['hari'=>'Selasa','jam_mulai'=>'07:00:00','jam_selesai'=>'07:45:00','jenis'=>'KBM'],
        ];

        foreach($entries as $e) {
            DB::table('jam_belajar')->updateOrInsert($e);
        }
    }
}
