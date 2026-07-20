<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

$rows = DB::table('sk_tugas')->select('id','judul','is_visible_to_guru')->orderBy('id')->get();
if ($rows->isEmpty()) {
    echo "(no rows)\n";
    exit;
}
foreach ($rows as $r) {
    $flag = isset($r->is_visible_to_guru) ? ($r->is_visible_to_guru ? 'visible' : 'hidden') : 'no_flag';
    echo "{$r->id} | {$r->judul} | {$flag}\n";
}
