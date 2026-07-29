<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Role;
use App\Models\User;

$role = Role::firstOrCreate(['role_name' => 'Admin']);
$email = 'temp-admin@example.test';
$pw = 'Admin123!';
$user = User::where('email', $email)->first();
if (! $user) {
    $user = User::create([
        'name' => 'Temp Admin',
        'username' => 'tempadmin',
        'email' => $email,
        'password' => bcrypt($pw),
        'is_active' => 1,
        'role_id' => $role->id,
    ]);
} else {
    $user->update([
        'username' => $user->username ?: 'tempadmin',
        'role_id' => $role->id,
        'is_active' => 1,
        'password' => bcrypt($pw),
    ]);
}

echo "CREATED: {$user->email} / {$pw}\n";