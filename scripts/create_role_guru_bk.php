<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Role;

$role = Role::firstOrCreate(['role_name' => 'Guru BK'], ['description' => 'Role untuk Guru BK']);
echo "Role ensured: {$role->id} | {$role->role_name}\n";
