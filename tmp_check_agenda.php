<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
use Illuminate\Support\Facades\DB;
$kelas = DB::table('kelas')->where('nama_kelas','11.B3')->first();
echo 'kelas=' . ($kelas->id ?? 'none') . "\n";
$guru = DB::table('guru')->where('nama','Nur Emiliyah, SE')->first();
echo 'guru=' . ($guru->id ?? 'none') . "\n";
if ($kelas && $guru) {
    $jadwals = DB::table('jadwal_kbm')->where('kelas_id',$kelas->id)->where('guru_id',$guru->id)->get();
    foreach ($jadwals as $j) {
        echo $j->id . ' ' . $j->hari . ' ' . $j->jam_belajar_id . "\n";
    }
}
