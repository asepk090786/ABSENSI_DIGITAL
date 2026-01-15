<?php
// Test script untuk check database sekolah
require_once __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';

try {
    // Get the kernel
    $kernel = $app->make(\Illuminate\Contracts\Http\Kernel::class);
    $kernel->bootstrap();

    // Now we can use models
    $sekolah = \App\Models\Sekolah::first();
    
    if ($sekolah) {
        echo "=== SEKOLAH RECORD ===\n";
        echo "ID: " . $sekolah->id . "\n";
        echo "Nama: " . ($sekolah->nama_sekolah ?? 'NULL') . "\n";
        echo "Logo: " . ($sekolah->logo ?? 'NULL') . "\n";
        echo "Logo Header Kiri: " . ($sekolah->logo_header_kiri ?? 'NULL') . "\n";
        echo "\n";
        
        // Check if files exist
        $storage_path = __DIR__ . '/storage/app/public';
        $public_path = __DIR__ . '/public';
        
        if ($sekolah->logo) {
            $file_path_storage = $storage_path . '/' . $sekolah->logo;
            $file_path_public = $public_path . '/storage/' . $sekolah->logo;
            echo "Logo file check:\n";
            echo "  Storage path: $file_path_storage\n";
            echo "  Exists in storage: " . (file_exists($file_path_storage) ? 'YES' : 'NO') . "\n";
            echo "  Public path: $file_path_public\n";
            echo "  Exists via public: " . (file_exists($file_path_public) ? 'YES' : 'NO') . "\n\n";
        }
        
        if ($sekolah->logo_header_kiri) {
            $file_path_storage = $storage_path . '/' . $sekolah->logo_header_kiri;
            $file_path_public = $public_path . '/storage/' . $sekolah->logo_header_kiri;
            echo "Logo Header Kiri file check:\n";
            echo "  Storage path: $file_path_storage\n";
            echo "  Exists in storage: " . (file_exists($file_path_storage) ? 'YES' : 'NO') . "\n";
            echo "  Public path: $file_path_public\n";
            echo "  Exists via public: " . (file_exists($file_path_public) ? 'YES' : 'NO') . "\n";
        }
    } else {
        echo "No sekolah record found\n";
    }
} catch (\Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>
