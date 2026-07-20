<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Guru;
use Illuminate\Support\Facades\Schema;

$hasGuruBkKelasColumn = Schema::hasTable('kelas') && Schema::hasColumn('kelas', 'guru_bk_id');

$guruBkQuery = Guru::with('user')
    ->whereHas('user', function($query) {
        $query->whereHas('roles', function($q) {
            $q->where('role_name', 'Guru BK');
        })->orWhereHas('role', function($q) {
            $q->where('role_name', 'Guru BK');
        });
    })
    ->orderBy('created_at', 'desc');

if ($hasGuruBkKelasColumn) {
    $guruBkQuery->with('kelasBinaanBk');
}

$gurubk = $guruBkQuery->get();

echo "Found: " . $gurubk->count() . " guru BK\n";
foreach ($gurubk as $g) {
    echo "{$g->id} | {$g->nama} | user_id: " . ($g->user? $g->user->id : 'no_user') . "\n";
}
