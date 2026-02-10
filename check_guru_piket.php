<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "=== CEK DATA GURU PIKET ===\n\n";

// Cek semua guru yang punya hari_piket
$guruPiket = \App\Models\Guru::whereNotNull('hari_piket')
    ->where('hari_piket', '!=', '[]')
    ->where('hari_piket', '!=', '')
    ->with('user')
    ->get();

if ($guruPiket->isEmpty()) {
    echo "❌ Tidak ada guru dengan hari_piket yang terisi.\n\n";
} else {
    echo "✅ Ditemukan " . $guruPiket->count() . " guru piket:\n";
    foreach ($guruPiket as $guru) {
        echo "   - {$guru->nama} (NIP: {$guru->nip})\n";
        echo "     Hari Piket: " . implode(', ', $guru->hari_piket ?? []) . "\n";
        if ($guru->user) {
            echo "     Username: {$guru->user->username}\n";
            echo "     Role: {$guru->user->role->role_name}\n";
        }
        echo "\n";
    }
}

// Cek role Guru Piket
$roleGuruPiket = \App\Models\Role::where('role_name', 'Guru Piket')->first();
if ($roleGuruPiket) {
    echo "\n✅ Role 'Guru Piket' ditemukan (ID: {$roleGuruPiket->id})\n";
    
    $usersGuruPiket = \App\Models\User::where('role_id', $roleGuruPiket->id)
        ->with('guru')
        ->get();
    
    if ($usersGuruPiket->isEmpty()) {
        echo "❌ Tidak ada user dengan role 'Guru Piket'\n";
    } else {
        echo "✅ Ditemukan " . $usersGuruPiket->count() . " user dengan role 'Guru Piket':\n";
        foreach ($usersGuruPiket as $user) {
            echo "   - {$user->name} (Username: {$user->username})\n";
            if ($user->guru) {
                echo "     Nama Guru: {$user->guru->nama}\n";
            }
        }
    }
} else {
    echo "\n❌ Role 'Guru Piket' tidak ditemukan di database\n";
}

echo "\n=== SELESAI ===\n";
