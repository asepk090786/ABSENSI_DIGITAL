<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Guru;
use App\Models\User;
use App\Models\Role;

$gurus = Guru::with('user')->orderBy('id')->get();
if ($gurus->isEmpty()) {
    echo "No guru rows\n";
    exit;
}
foreach ($gurus as $g) {
    $user = $g->user;
    $userInfo = $user ? ($user->id . '|' . ($user->email ?? '-') . '|' . ($user->role ? $user->role->role_name : '-') . '|' . implode(',', $user->roles->pluck('role_name')->toArray())) : 'no_user';
    echo "guru: {$g->id} | {$g->nama} | user: {$userInfo}\n";
}

$role = Role::where('role_name', 'Guru BK')->first();
if ($role) {
    $usersWithRole = $role->users()->with('guru')->get();
    echo "\nUsers with role 'Guru BK':\n";
    foreach ($usersWithRole as $u) {
        echo "user {$u->id} | {$u->email} | guru_id: {$u->guru_id} | guru_name: " . ($u->guru ? $u->guru->nama : 'none') . "\n";
    }
} else {
    echo "Role 'Guru BK' not found\n";
}
