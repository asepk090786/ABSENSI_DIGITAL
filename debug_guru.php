<?php
require __DIR__ . '/vendor/autoload.php';
$app = require __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$name = 'Iis Khaerunisah,S.Pd';
$guru = App\Models\Guru::where('nama', $name)->first();
if (! $guru) {
    echo "Guru not found\n";
    exit(0);
}
$tahun = App\Models\TahunAjaran::where('is_active', 1)->first();
$semester = App\Models\Semester::where('is_active', 1)->first();

echo "Guru ID: {$guru->id}\n";
echo "Active tahun: " . ($tahun ? $tahun->nama_tahun : 'none') . "\n";
echo "Active semester: " . ($semester ? $semester->nama_semester : 'none') . "\n";

echo "\nTugasGuru active tasks:\n";
$tugas = App\Models\TugasGuru::where('guru_id', $guru->id)->where('is_active', 1)->get();
foreach ($tugas as $t) {
    echo "- id: {$t->id}, mapel: {$t->mata_pelajaran_id}, kelas: " . ($t->kelas_id ?? 'all') . "\n";
}

echo "\nJadwalKbm entries for guru in active term:\n";
$jadwals = App\Models\JadwalKbm::where('guru_id', $guru->id)
    ->when($tahun, fn($q) => $q->where('tahun_ajaran_id', $tahun->id))
    ->when($semester, fn($q) => $q->where('semester_id', $semester->id))
    ->orderBy('hari')
    ->orderBy('jam_ke')
    ->get();
foreach ($jadwals as $j) {
    echo "- id: {$j->id}, mapel: {$j->mata_pelajaran_id}, kelas: {$j->kelas_id}, hari: {$j->hari}, jam: {$j->jam_ke}, jam_belajar_id: {$j->jam_belajar_id}\n";
}
echo "Total JadwalKbm entries: " . $jadwals->count() . "\n";

$grouped = $jadwals->groupBy(function($j) {
    return $j->mata_pelajaran_id . '_' . $j->kelas_id;
});
foreach ($grouped as $key => $group) {
    echo "Group $key count " . $group->count() . "\n";
}
