<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

echo "=== NILAI_HARIAN TABLE STRUCTURE ===\n";
$columns = Schema::getColumns('nilai_harian');
foreach ($columns as $column) {
    echo "Column: {$column['name']}, Type: {$column['type']}, Nullable: " . ($column['nullable'] ? 'YES' : 'NO') . "\n";
}

echo "\n=== TOTAL RECORDS IN NILAI_HARIAN ===\n";
$total = DB::table('nilai_harian')->count();
echo "Total records: $total\n";

echo "\n=== SAMPLE RECORDS ===\n";
$samples = DB::table('nilai_harian')
    ->select('id', 'siswa_id', 'mapel_id', 'komponen_id', 'tahun_ajaran_id', 'semester_id', 'tanggal', 'nilai')
    ->limit(5)
    ->get();
foreach ($samples as $record) {
    echo "ID: {$record->id}, Siswa: {$record->siswa_id}, Mapel: {$record->mapel_id}, Komponen: {$record->komponen_id}, TahunAjaran: {$record->tahun_ajaran_id}, Semester: {$record->semester_id}, Nilai: {$record->nilai}\n";
}

echo "\n=== ACTIVE TAHUN AJARAN AND SEMESTER ===\n";
$tahunAjaran = DB::table('tahun_ajaran')->where('is_active', true)->first();
$semester = DB::table('semester')->where('is_active', true)->first();
if ($tahunAjaran) {
    echo "Active Tahun Ajaran ID: {$tahunAjaran->id}\n";
} else {
    echo "No active tahun ajaran\n";
}
if ($semester) {
    echo "Active Semester ID: {$semester->id}\n";
} else {
    echo "No active semester\n";
}

echo "\n=== CHECK RECORDS WITH ACTIVE TAHUN_AJARAN AND SEMESTER ===\n";
$countActive = DB::table('nilai_harian')
    ->where('tahun_ajaran_id', $tahunAjaran?->id)
    ->where('semester_id', $semester?->id)
    ->count();
echo "Records with active tahun_ajaran and semester: $countActive\n";

echo "\n=== SAMPLE QUERY FROM REKAP_NILAI ===\n";
// Simulate the RekapNilaiController query with sample data
$kelas_id = DB::table('kelas')->first()?->id;
$mapel_id = DB::table('mata_pelajaran')->first()?->id;

if ($kelas_id && $mapel_id) {
    echo "Testing with Kelas ID: $kelas_id, Mapel ID: $mapel_id\n";
    
    $query = DB::table('siswa')
        ->leftJoin('nilai_harian', function($join) use ($mapel_id, $tahunAjaran, $semester) {
            $join->on('siswa.id', '=', 'nilai_harian.siswa_id')
                ->where('nilai_harian.mapel_id', $mapel_id)
                ->where('nilai_harian.tahun_ajaran_id', $tahunAjaran->id)
                ->where('nilai_harian.semester_id', $semester->id);
        })
        ->where('siswa.kelas_id', $kelas_id)
        ->select(
            'siswa.id',
            'siswa.nama',
            DB::raw('AVG(nilai_harian.nilai) as rata_rata'),
            DB::raw('COUNT(nilai_harian.id) as jumlah_nilai')
        )
        ->groupBy('siswa.id', 'siswa.nama')
        ->toSql();
    
    echo("\nSQL Query:\n");
    echo $query . "\n";
    
    // Execute and show results
    $results = DB::table('siswa')
        ->leftJoin('nilai_harian', function($join) use ($mapel_id, $tahunAjaran, $semester) {
            $join->on('siswa.id', '=', 'nilai_harian.siswa_id')
                ->where('nilai_harian.mapel_id', $mapel_id)
                ->where('nilai_harian.tahun_ajaran_id', $tahunAjaran->id)
                ->where('nilai_harian.semester_id', $semester->id);
        })
        ->where('siswa.kelas_id', $kelas_id)
        ->select(
            'siswa.id',
            'siswa.nama',
            DB::raw('AVG(nilai_harian.nilai) as rata_rata'),
            DB::raw('COUNT(nilai_harian.id) as jumlah_nilai')
        )
        ->groupBy('siswa.id', 'siswa.nama')
        ->get();
    
    echo "\nResults: " . count($results) . " students\n";
    foreach ($results as $row) {
        echo "Siswa: {$row->nama}, Rata-rata: {$row->rata_rata}, Jumlah: {$row->jumlah_nilai}\n";
    }
}
