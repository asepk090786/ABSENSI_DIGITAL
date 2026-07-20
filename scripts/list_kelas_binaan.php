<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Kelas;

$rows = Kelas::whereNotNull('guru_bk_id')->get(['id','nama_kelas','guru_bk_id']);
if ($rows->isEmpty()) {
    echo "No kelas with guru_bk_id\n";
    exit;
}
foreach ($rows as $r) {
    echo "{$r->id} | {$r->nama_kelas} | guru_bk_id: {$r->guru_bk_id}\n";
}
