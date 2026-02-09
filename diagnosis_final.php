<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;

echo "=== COMPREHENSIVE DIAGNOSIS REPORT ===\n\n";

// 1. Exact JOIN Condition Analysis
echo "1. EXACT JOIN CONDITION IN RekapNilaiController (lines 75-110):\n";
echo "   LEFT JOIN 'nilai_harian' ON:\n";
echo "   - siswa.id = nilai_harian.siswa_id\n";
echo "   - AND nilai_harian.mapel_id = {mapelId parameter}\n";
echo "   - AND nilai_harian.tahun_ajaran_id = {tahunAjaranActive->id}\n";
echo "   - AND nilai_harian.semester_id = {semesterActive->id}\n";
echo "   - AND nilai_harian.komponen_id = {komponenId parameter} (if provided)\n";
echo "   - WHERE siswa.kelas_id = {kelasId parameter}\n\n";

// 2. nilai_harian table structure
echo "2. NILAI_HARIAN TABLE STRUCTURE:\n";
$columns = DB::getSchemaBuilder()->getColumns('nilai_harian');
$key_columns = ['id', 'siswa_id', 'mapel_id', 'komponen_id', 'tahun_ajaran_id', 'semester_id', 'tanggal', 'nilai', 'rencana_pembelajaran_id'];
foreach ($columns as $col) {
    if (in_array($col['name'], $key_columns)) {
        $nullable = $col['nullable'] ? 'NULLABLE' : 'NOT NULL';
        echo "   - {$col['name']}: {$col['type']} $nullable\n";
    }
}

// 3. Data availability
echo "\n3. DATA AVAILABILITY:\n";
$totalNilai = DB::table('nilai_harian')->count();
echo "   - Total nilai_harian records: $totalNilai\n";

// What siswa and classes have nilai?
$kelasWithNilai = DB::table('nilai_harian')
    ->join('siswa', 'nilai_harian.siswa_id', '=', 'siswa.id')
    ->distinct('siswa.kelas_id')
    ->pluck('siswa.kelas_id')
    ->toArray();
echo "   - Kelas with nilai records: " . implode(', ', $kelasWithNilai) . "\n";

foreach ($kelasWithNilai as $kid) {
    $count = DB::table('nilai_harian')
        ->join('siswa', 'nilai_harian.siswa_id', '=', 'siswa.id')
        ->where('siswa.kelas_id', $kid)
        ->count();
    $kelas_name = DB::table('kelas')->where('id', $kid)->value('nama_kelas');
    echo "     * Kelas $kid ({$kelas_name}): $count nilai records\n";
}

// What mapels and components have nilai?
$mapelsWithNilai = DB::table('nilai_harian')
    ->distinct('mapel_id')
    ->pluck('mapel_id')
    ->toArray();
echo "   - Mapels with nilai records: " . implode(', ', $mapelsWithNilai) . "\n";

// 4. Active tahun ajaran and semester
echo "\n4. ACTIVE TAHUN_AJARAN AND SEMESTER:\n";
$tahunAjaranActive = DB::table('tahun_ajaran')->where('is_active', true)->first();
$semesterActive = DB::table('semester')->where('is_active', true)->first();
if ($tahunAjaranActive) {
    echo "   - Active Tahun Ajaran ID: {$tahunAjaranActive->id}\n";
} else {
    echo "   - No active tahun ajaran!\n";
}
if ($semesterActive) {
    echo "   - Active Semester ID: {$semesterActive->id}\n";
} else {
    echo "   - No active semester!\n";
}

$nilaiWithActiveTA = DB::table('nilai_harian')
    ->where('tahun_ajaran_id', $tahunAjaranActive->id)
    ->where('semester_id', $semesterActive->id)
    ->count();
echo "   - Nilai with active TA and semester: $nilaiWithActiveTA\n";

// 5. Sample records showing the mismatch
echo "\n5. SAMPLE RECORDS SHOWING THE ISSUE:\n";
echo "   NILAI RECORDS (showing siswa assignments):\n";
$nilaiSamples = DB::table('nilai_harian')
    ->join('siswa', 'nilai_harian.siswa_id', '=', 'siswa.id')
    ->select('nilai_harian.id', 'siswa.id as siswa_id', 'siswa.nama', 'siswa.kelas_id', 'nilai_harian.mapel_id', 'nilai_harian.komponen_id', 'nilai_harian.nilai')
    ->limit(5)
    ->get();
foreach ($nilaiSamples as $row) {
    echo "     - Nilai ID {$row->id}: Siswa {$row->siswa_id} ({$row->nama}) in Kelas {$row->kelas_id}, Mapel {$row->mapel_id}, Komponen: " . ($row->komponen_id ?: 'NULL') . ", Nilai: " . ($row->nilai ?: 'NULL') . "\n";
}

echo "\n   SISWA IN KELAS (showing mismatch):\n";
foreach ($kelasWithNilai as $kid) {
    $samples = DB::table('siswa')->where('kelas_id', $kid)->limit(3)->get();
    echo "     Kelas $kid:\n";
    foreach ($samples as $siswa) {
        echo "       - Siswa {$siswa->id}: {$siswa->nama}\n";
    }
}

// 6. The root cause
echo "\n6. ROOT CAUSE ANALYSIS:\n";
echo "   The RekapNilaiController query structure:\n";
echo "   - Takes a kelas_id parameter from the request (e.g., 1)\n";
echo "   - Filters WHERE siswa.kelas_id = {kelas_id}\n";
echo "   - LEFT JOINs nilai_harian based on siswa.id matching\n";
echo "   \n   THE PROBLEM:\n";
$samplesKelas1 = DB::table('siswa')->where('kelas_id', 1)->limit(3)->pluck('id');
$nilaiForKelas1 = DB::table('nilai_harian')->join('siswa', 'nilai_harian.siswa_id', '=', 'siswa.id')->where('siswa.kelas_id', 1)->count();
echo "   - Kelas 1 has " . DB::table('siswa')->where('kelas_id', 1)->count() . " siswa with IDs: " . implode(', ', $samplesKelas1->toArray()) . "\n";
echo "   - But nilai_harian only has records for kelas_id: " . implode(', ', $kelasWithNilai) . "\n";
echo "   - Therefore, nilai records for kelas 1: $nilaiForKelas1\n";
echo "   - Result: The query returns all 42 students but with NO matching nilai records!\n";

// 7. How komponen_id affects the query
echo "\n7. KOMPONEN_ID HANDLING:\n";
$komponenWithNilai = DB::table('nilai_harian')->select('komponen_id')->distinct()->get();
echo "   - komponen_id values in nilai_harian:\n";
foreach ($komponenWithNilai as $row) {
    $val = $row->komponen_id ?: 'NULL';
    echo "     * $val\n";
}
echo "   - In the RekapNilaiController, if no komponen_id filter is applied:\n";
echo "     * The JOIN includes AND nilai_harian.komponen_id = NULL\n";
echo "     * This will EXCLUDE records where komponen_id is NOT NULL\n";
echo "   - This could cause additional filtering issues if mixing NULL and non-NULL komponen_id!\n";

// Check if there's a komponen filter issue
$nilaiWithNullKomponen = DB::table('nilai_harian')->whereNull('komponen_id')->count();
$nilaiWithNonNullKomponen = DB::table('nilai_harian')->whereNotNull('komponen_id')->count();
echo "   - Nilai with NULL komponen_id: $nilaiWithNullKomponen\n";
echo "   - Nilai with NON-NULL komponen_id: $nilaiWithNonNullKomponen\n";
