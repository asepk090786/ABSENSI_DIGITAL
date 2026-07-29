<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\Cache;

try {
    $store = Cache::getStore();
    if (method_exists($store, 'supportsTags') && $store->supportsTags()) {
        Cache::tags(['absensi_laporan_siswa'])->flush();
        echo "Flushed tag absensi_laporan_siswa\n";
    } else {
        echo "Cache store does not support tags; cannot flush by tag.\n";
    }
} catch (Throwable $e) {
    echo "Flush failed: " . $e->getMessage() . "\n";
}
