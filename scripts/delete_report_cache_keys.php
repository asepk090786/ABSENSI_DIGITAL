<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\Redis;

try {
    $connection = config('cache.stores.redis.connection') ?? 'cache';
    $redis = Redis::connection($connection);
    $pattern = '*laporan_siswa*';
    $keys = $redis->keys($pattern);
    if (empty($keys)) {
        echo "NO_KEYS_FOUND for pattern: $pattern\n";
        exit(0);
    }
    echo "Found " . count($keys) . " keys. Deleting...\n";
    foreach ($keys as $k) {
        echo "DEL: $k\n";
        $redis->del($k);
    }
    echo "Done.\n";
} catch (Throwable $e) {
    echo "Redis delete failed: " . $e->getMessage() . "\n";
}
