<?php
/**
 * Script untuk menambahkan hari piket ke guru tertentu
 * 
 * Cara pakai:
 * php assign_guru_piket.php <username_guru> <hari>
 * 
 * Contoh:
 * php assign_guru_piket.php guru1 Senin
 * php assign_guru_piket.php guru2 "Senin,Selasa"
 */

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

if ($argc < 3) {
    echo "❌ Parameter tidak lengkap!\n\n";
    echo "Cara pakai:\n";
    echo "  php assign_guru_piket.php <username_guru> <hari>\n\n";
    echo "Contoh:\n";
    echo "  php assign_guru_piket.php guru1 Senin\n";
    echo "  php assign_guru_piket.php guru2 \"Senin,Selasa,Rabu\"\n\n";
    echo "Hari yang valid: Senin, Selasa, Rabu, Kamis, Jumat, Sabtu, Minggu\n";
    exit(1);
}

$username = $argv[1];
$hariInput = $argv[2];

// Parse hari (bisa comma-separated)
$hariArray = array_map('trim', explode(',', $hariInput));

// Validasi hari
$validHari = ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu', 'Minggu'];
foreach ($hariArray as $hari) {
    if (!in_array($hari, $validHari)) {
        echo "❌ Hari '$hari' tidak valid!\n";
        echo "Hari yang valid: " . implode(', ', $validHari) . "\n";
        exit(1);
    }
}

// Cari user
$user = \App\Models\User::where('username', $username)->with('guru')->first();

if (!$user) {
    echo "❌ User dengan username '$username' tidak ditemukan!\n";
    exit(1);
}

if (!$user->guru) {
    echo "❌ User '$username' tidak terhubung dengan data guru!\n";
    exit(1);
}

// Update hari piket
$guru = $user->guru;
$guru->hari_piket = $hariArray;
$guru->save();

echo "✅ Berhasil!\n\n";
echo "Guru: {$guru->nama}\n";
echo "Username: {$username}\n";
echo "Hari Piket: " . implode(', ', $hariArray) . "\n\n";
echo "🎉 Sekarang menu PIKET KBM akan muncul saat login dengan username '$username'\n";
echo "   Silakan refresh halaman browser!\n";
