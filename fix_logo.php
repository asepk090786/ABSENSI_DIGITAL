<?php
require_once __DIR__ . '/vendor/autoload.php';

// Boot Laravel application
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(\Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Sekolah;
use Illuminate\Support\Facades\Storage;

// Find the latest logo file in storage
$files = Storage::disk('public')->files('logos');
$logoFiles = array_filter($files, function($file) {
    return in_array(pathinfo($file, PATHINFO_EXTENSION), ['png', 'jpg', 'jpeg', 'gif']);
});

if (!empty($logoFiles)) {
    // Get the latest file (most recent modification)
    usort($logoFiles, function($a, $b) {
        $timeA = Storage::disk('public')->lastModified($a);
        $timeB = Storage::disk('public')->lastModified($b);
        return $timeB - $timeA;
    });
    
    $latestLogo = $logoFiles[0];
    
    echo "Found logo file: " . $latestLogo . "\n";
    
    // Update Sekolah record
    $sekolah = Sekolah::first();
    if ($sekolah) {
        $oldLogo = $sekolah->logo;
        $sekolah->update(['logo' => $latestLogo]);
        echo "Updated logo from: $oldLogo\n";
        echo "Updated logo to: $latestLogo\n";
        echo "✓ Logo path updated successfully!\n";
    } else {
        echo "✗ No school record found!\n";
    }
} else {
    echo "✗ No logo files found in storage/app/public/logos\n";
}
?>
