<?php
// Test upload untuk debugging
echo "Testing upload functionality\n\n";

// Check if we can access storage
$storage_path = __DIR__ . '/storage/app/public/logos';
if (!is_dir($storage_path)) {
    mkdir($storage_path, 0755, true);
}

echo "Storage path: " . $storage_path . "\n";
echo "Storage writable: " . (is_writable($storage_path) ? 'YES' : 'NO') . "\n";
echo "Storage readable: " . (is_readable($storage_path) ? 'YES' : 'NO') . "\n\n";

// Check permissions
$perms = fileperms($storage_path);
echo "Storage permissions: " . substr(sprintf('%o', $perms), -4) . "\n\n";

// List files in logos directory
echo "Files in logos directory:\n";
$files = scandir($storage_path);
foreach ($files as $file) {
    if ($file !== '.' && $file !== '..') {
        $file_path = $storage_path . '/' . $file;
        echo "  - $file (size: " . filesize($file_path) . " bytes)\n";
    }
}

// Check database
require_once __DIR__ . '/bootstrap/app.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(\Illuminate\Contracts\Http\Kernel::class);
$kernel->bootstrap();

try {
    $sekolah = \App\Models\Sekolah::first();
    if ($sekolah) {
        echo "\n\nSekolah record found:\n";
        echo "  ID: " . $sekolah->id . "\n";
        echo "  Nama: " . $sekolah->nama_sekolah . "\n";
        echo "  Logo: " . ($sekolah->logo ?? 'NULL') . "\n";
        echo "  Logo Header Kiri: " . ($sekolah->logo_header_kiri ?? 'NULL') . "\n";
    } else {
        echo "\n\nNo sekolah record found\n";
    }
} catch (\Exception $e) {
    echo "\n\nDatabase error: " . $e->getMessage() . "\n";
}
?>
