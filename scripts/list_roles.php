<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Role;
$roles = Role::orderBy('id')->get();
foreach ($roles as $r) {
    echo "{$r->id}|{$r->role_name}\n";
}
