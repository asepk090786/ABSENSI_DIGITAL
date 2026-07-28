<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Redis;

echo "APP_ENV: " . env('APP_ENV') . "\n";
echo "CACHE_DRIVER: " . config('cache.default') . "\n";
echo "CACHE_PREFIX: " . config('cache.prefix') . "\n";

$pattern = $argv[1] ?? '*laporan_siswa*';
try {
    $connection = config('cache.stores.redis.connection') ?? 'cache';
    echo "Using redis connection: $connection\n";
    $redis = null;
    try {
        $redis = Redis::connection($connection);
    } catch (Throwable $e) {
        // fallback to default connection
        $redis = Redis::connection();
    }

    if (!$redis) {
        echo "Could not get Redis connection via facade.\n";
        exit(2);
    }

    $keys = $redis->keys($pattern);
    if (empty($keys)) {
        echo "NO_KEYS_FOUND for pattern: $pattern\n";
        exit(0);
    }
    foreach ($keys as $k) {
        echo "KEY: $k\n";
        echo "TTL: " . $redis->ttl($k) . "\n";
        echo "TYPE: " . $redis->type($k) . "\n";
        $val = $redis->get($k);
        if ($val === false) {
            echo "VALUE: <non-string or empty>\n";
        } else {
            $snippet = mb_substr($val, 0, 500);
            if (mb_strlen($val) > 500) $snippet .= '...[truncated]';
            echo "VALUE (snippet): $snippet\n";
        }
        echo "----\n";
    }
} catch (Throwable $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    exit(2);
}
