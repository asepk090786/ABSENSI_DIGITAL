<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;

echo "=== DEBUGGING NILAI MATCHING ISSUE ===\n\n";

echo "=== SAMPLE NILAI_HARIAN RECORDS (WITH SISWA INFO) ===\n";
$nilaiSamples = DB::table('nilai_harian')
    ->join('siswa', 'nilai_harian.siswa_id', '=', 'siswa.id')
    ->select(
        'nilai_harian.id',
        'nilai_harian.siswa_id',
        'siswa.kelas_id',
        'siswa.nama',
        'nilai_harian.mapel_id',
        'nilai_harian.komponen_id',
        'nilai_harian.tahun_ajaran_id',
        'nilai_harian.semester_id',
        'nilai_harian.tanggal',
        'nilai_harian.nilai'
    )
    ->limit(10)
    ->get();

foreach ($nilaiSamples as $row) {
    echo "Nilai ID: {$row->id}, Siswa: {$row->siswa_id} ({$row->nama}), Kelas: {$row->kelas_id}, Mapel: {$row->mapel_id}, Komponen: {$row->komponen_id}, TA: {$row->tahun_ajaran_id}, Semester: {$row->semester_id}, Nilai: {$row->nilai}\n";
}

echo "\n=== CHECKING SISWA IN KELAS 1 ===\n";
$siswaKelas1 = DB::table('siswa')->where('kelas_id', 1)->pluck('id');
echo "Number of siswa in kelas 1: " . count($siswaKelas1) . "\n";
echo "Siswa IDs: " . implode(', ', $siswaKelas1->take(5)->toArray()) . ", ...\n";

echo "\n=== CHECKING NILAI FOR KELAS 1 ===\n";
$nilaiKelas1 = DB::table('nilai_harian')
    ->join('siswa', 'nilai_harian.siswa_id', '=', 'siswa.id')
    ->where('siswa.kelas_id', 1)
    ->count();
echo "Number of nilai records for siswa in kelas 1: $nilaiKelas1\n";

$nilaiKelas1Details = DB::table('nilai_harian')
    ->join('siswa', 'nilai_harian.siswa_id', '=', 'siswa.id')
    ->where('siswa.kelas_id', 1)
    ->select('nilai_harian.siswa_id', 'siswa.nama', 'nilai_harian.mapel_id', 'nilai_harian.nilai')
    ->limit(5)
    ->get();

echo "Sample nilai records:\n";
foreach ($nilaiKelas1Details as $row) {
    echo "  Siswa {$row->siswa_id} ({$row->nama}), Mapel {$row->mapel_id}, Nilai: {$row->nilai}\n";
}

echo "\n=== CHECKING KELAS_ID IN NILAI_HARIAN TABLE ===\n";
$nilaiWithKelas = DB::table('nilai_harian')
    ->select(DB::raw('DISTINCT kelas_id'))
    ->orderBy('kelas_id')
    ->get();
echo "Kelas IDs in nilai_harian: " . implode(', ', $nilaiWithKelas->pluck('kelas_id')->toArray()) . "\n";

echo "\n=== CHECKING SISWA KELAS_ID IN NILAI_HARIAN VS SISWA TABLE ===\n";
$nilaiKelasFromNilai = DB::table('nilai_harian')->value('kelas_id');
$nilaiSiswaId = DB::table('nilai_harian')->limit(1)->value('siswa_id');
$siswaKelasId = DB::table('siswa')->where('id', $nilaiSiswaId)->value('kelas_id');

echo "Sample nilai record siswa_id: $nilaiSiswaId\n";
echo "kelas_id from nilai_harian: $nilaiKelasFromNilai\n";
echo "kelas_id from siswa table (for that siswa): $siswaKelasId\n";
echo "Do they match? " . ($nilaiKelasFromNilai == $siswaKelasId ? "YES" : "NO") . "\n";

echo "\n=== CHECKING IF NILAI_HARIAN.KELAS_ID MATCHES SISWA.KELAS_ID ===\n";
$mismatches = DB::table('nilai_harian')
    ->join('siswa', 'nilai_harian.siswa_id', '=', 'siswa.id')
    ->whereRaw('nilai_harian.kelas_id != siswa.kelas_id')
    ->count();
echo "Number of mismatches: $mismatches\n";

echo "\n=== CHECKING ALL UNIQUE SISWA_ID IN NILAI_HARIAN ===\n";
$uniqueSiswaInNilai = DB::table('nilai_harian')->distinct('siswa_id')->count();
echo "Unique siswa_id in nilai_harian: $uniqueSiswaInNilai\n";

$uniqueSiswaInSiswaByKelas1 = DB::table('siswa')->where('kelas_id', 1)->count();
echo "Total siswa in kelas_id 1: $uniqueSiswaInSiswaByKelas1\n";

// Now let's check the actual matching issue
echo "\n=== ACTUAL LEFT JOIN TEST ===\n";
$testQuery = DB::table('siswa')
    ->leftJoin('nilai_harian', 'siswa.id', '=', 'nilai_harian.siswa_id')
    ->where('siswa.kelas_id', 1)
    ->select('siswa.id', 'siswa.nama', 'nilai_harian.id as nilai_id', 'nilai_harian.nilai')
    ->limit(10)
    ->get();

echo "LEFT JOIN results (first 10 siswa from kelas 1):\n";
foreach ($testQuery as $row) {
    $nilaiStr = $row->nilai_id ? "Nilai ID: {$row->nilai_id}, Nilai: {$row->nilai}" : "NO MATCH";
    echo "  Siswa {$row->id} ({$row->nama}): $nilaiStr\n";
}
