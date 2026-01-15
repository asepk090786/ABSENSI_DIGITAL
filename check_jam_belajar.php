<?php
// Cek data jam_belajar untuk ISTIRAHAT/UPACARA/PEMBIASAAN
use Illuminate\Support\Facades\DB;

$results = DB::table('jam_belajar')
    ->whereIn('jenis', ['ISTIRAHAT', 'UPACARA', 'PEMBIASAAN'])
    ->get(['hari', 'urutan', 'jenis', 'jam_mulai', 'jam_selesai']);

foreach ($results as $row) {
    echo "Hari: {$row->hari}, Jam ke: {$row->urutan}, Jenis: {$row->jenis}, Mulai: {$row->jam_mulai}, Selesai: {$row->jam_selesai}\n";
}
