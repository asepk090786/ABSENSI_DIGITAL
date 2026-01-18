<?php
require_once __DIR__ . '/vendor/autoload.php';

// Boot Laravel application
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(\Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Sekolah;
use Illuminate\Support\Facades\Storage;

echo "╔══════════════════════════════════════════════════════════╗\n";
echo "║  MIGRATE LOGO SEKOLAH KE STORAGE FOLDER                 ║\n";
echo "╚══════════════════════════════════════════════════════════╝\n\n";

$sekolah = Sekolah::first();

if (!$sekolah) {
    echo "✗ Tidak ada data sekolah di database\n\n";
    exit(1);
}

if (!$sekolah->logo) {
    echo "ℹ Belum ada logo tersimpan di database\n\n";
    exit(0);
}

echo "Current logo path: {$sekolah->logo}\n";

// Check if logo is already in storage format (contains /)
if (strpos($sekolah->logo, '/') !== false) {
    echo "✓ Logo sudah menggunakan format storage (ada slash)\n\n";
    exit(0);
}

// Check if old logo file exists in public/images
$oldImagePath = public_path('images/' . $sekolah->logo);
if (!file_exists($oldImagePath)) {
    echo "✗ File logo lama tidak ditemukan: {$oldImagePath}\n\n";
    exit(1);
}

echo "Menemukan file logo lama: {$oldImagePath}\n";

try {
    // Read old file
    $fileContent = file_get_contents($oldImagePath);
    $fileName = basename($sekolah->logo);
    
    // Store to storage/app/public/logos
    $newPath = Storage::disk('public')->put('logos/' . $fileName, $fileContent);
    
    if ($newPath) {
        // Update database
        $sekolah->update(['logo' => $newPath]);
        
        // Delete old file
        @unlink($oldImagePath);
        
        echo "\n✓ Logo berhasil di-migrate!\n";
        echo "  Dari: images/{$sekolah->logo}\n";
        echo "  Ke:   {$newPath}\n\n";
    } else {
        echo "✗ Gagal menyimpan file ke storage\n\n";
        exit(1);
    }
} catch (\Exception $e) {
    echo "✗ Error: {$e->getMessage()}\n\n";
    exit(1);
}
?>
