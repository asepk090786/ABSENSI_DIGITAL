<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;

$username = $argv[1] ?? '197004152008011006';
$user = User::where('username', $username)->first();
if (! $user) {
    echo "User not found: {$username}\n";
    exit(1);
}

$roles = $user->roles()->pluck('role_name')->toArray();
$guru = $user->guru;
$hariPiket = $guru ? $guru->hari_piket : null;

echo "User: " . $user->id . " | " . $user->username . "\n";
echo "Roles: " . json_encode($roles) . "\n";
echo "guru_id: " . ($user->guru_id ?? 'NULL') . "\n";
if ($guru) {
    echo "Guru id: {$guru->id}\n";
    echo "hari_piket (as stored): ";
    var_export($guru->getAttributes()['hari_piket'] ?? null);
    echo "\n";
    echo "hari_piket (cast): " . json_encode($hariPiket) . "\n";
} else {
    echo "No guru relationship\n";
}

echo "hasRole('Guru Piket'): " . (auth()->user() ? (auth()->user()->hasRole('Guru Piket') ? '1' : '0') : 'N/A') . "\n";

// For safety, also show whether the code's check would consider this user piket
$map = [
    'Monday' => 'Senin', 'Tuesday' => 'Selasa', 'Wednesday' => 'Rabu',
    'Thursday' => 'Kamis', 'Friday' => 'Jumat', 'Saturday' => 'Sabtu', 'Sunday' => 'Minggu'
];
$todayEng = \Carbon\Carbon::now()->format('l');
$todayIndo = $map[$todayEng] ?? null;
$isGuruPiket = in_array($todayIndo, (array) $hariPiket, true);
echo "today: " . ($todayIndo ?? 'UNKNOWN') . "\n";
echo "isGuruPiket (computed for today): " . ($isGuruPiket ? '1' : '0') . "\n";
