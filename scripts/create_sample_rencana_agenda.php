<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Guru;
use App\Models\RencanaPembelajaran;
use App\Models\AgendaGuru;
use Illuminate\Support\Facades\DB;

try {
    DB::beginTransaction();

    $guru = Guru::first();
    if (! $guru) {
        echo "No Guru found in DB. Aborting.\n";
        exit(1);
    }

    $rencana = RencanaPembelajaran::create([
        'guru_id' => $guru->id,
        'mata_pelajaran_id' => DB::table('mata_pelajaran')->value('id') ?? null,
        'kelas_id' => DB::table('kelas')->value('id') ?? null,
        'judul' => 'Automated Test RP ' . time(),
        'deskripsi' => 'Deskripsi singkat untuk testing otomatis.',
        'status' => 'draft'
    ]);

    $jamId = DB::table('jam_belajar')->value('id');
    $tahunId = DB::table('tahun_ajaran')->where('is_active',1)->value('id');
    $semesterId = DB::table('semester')->where('is_active',1)->value('id');
    $tanggal = date('Y-m-d');

    $agenda = AgendaGuru::create([
        'guru_id' => $guru->id,
        'jam_belajar_id' => $jamId ?? 1,
        'tanggal' => $tanggal,
        'kegiatan' => 'Testing link to RPP: ' . $rencana->judul,
        'tahun_ajaran_id' => $tahunId,
        'semester_id' => $semesterId,
        'rencana_pembelajaran_id' => $rencana->id,
    ]);

    DB::commit();

    echo "Created RencanaPembelajaran id={$rencana->id}\n";
    echo "Created AgendaGuru id={$agenda->id} linked to rencana_pembelajaran_id={$agenda->rencana_pembelajaran_id}\n";
    exit(0);
} catch (\Exception $e) {
    DB::rollBack();
    echo "Error: " . $e->getMessage() . "\n";
    exit(2);
}
