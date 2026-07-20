<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Kelas;
use App\Models\Role;
use App\Models\User;

$role = Role::where('role_name', 'Guru BK')->first();
if (! $role) {
    echo "Role Guru BK not found\n";
    exit;
}

$guruBkIds = Kelas::whereNotNull('guru_bk_id')->pluck('guru_bk_id')->unique()->values();
if ($guruBkIds->isEmpty()) {
    echo "No guru_bk_id found in kelas\n";
    exit;
}

$assigned = 0;
foreach ($guruBkIds as $gid) {
    $user = User::where('guru_id', $gid)->first();
    if ($user) {
        $user->roles()->syncWithoutDetaching([$role->id]);
        echo "Assigned role to user {$user->id} (guru_id={$gid})\n";
        $assigned++;
    } else {
        echo "No user found for guru_id={$gid}\n";
    }
}

echo "Total assigned: {$assigned}\n";
