<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\DB;

$selectedTanggal = '2026-07-01';
$rangeStart = '2026-06-30';
$rangeEnd = '2026-07-30';
$kelasId = null;
$guruId = null;

$startDate = $rangeStart;
$endDate = $rangeEnd;

$tahun = DB::table('tahun_ajaran')->where('is_active', 1)->first();
$semester = DB::table('semester')->where('is_active', 1)->first();

$params = [
    'start_date' => $startDate,
    'end_date' => $endDate,
    'kelas_id' => $kelasId,
    'guru_id' => $guruId,
    'tahun_id' => $tahun?->id,
    'semester_id' => $semester?->id,
];
ksort($params);
$cacheKey = 'absensi:laporan_siswa:range:' . sha1(json_encode($params));

echo "CacheKey: $cacheKey\n";

$value = Cache::get($cacheKey);
if ($value === null) {
    echo "Cache::get returned null\n";
} else {
    // write into storage/logs to avoid missing app/ dir issues
    $safeKey = preg_replace('/[^A-Za-z0-9._-]/', '_', $cacheKey);
    $outPath = storage_path('logs/absensi_cache_' . $safeKey . '.json');
    // Convert collection/object to array
    if (is_object($value) && method_exists($value, 'toArray')) {
        $arr = $value->toArray();
    } elseif (is_array($value)) {
        $arr = $value;
    } else {
        $arr = $value;
    }
    $json = json_encode($arr, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    file_put_contents($outPath, $json);
    echo "Wrote cached value to: $outPath (bytes: " . strlen($json) . ")\n";
}

// Inspect redis internal keys for tags/versions
try {
    $connection = config('cache.stores.redis.connection') ?? 'cache';
    $redis = Redis::connection($connection);
    echo "Redis connection: $connection\n";

    $patterns = [
        "*:tag*",
        "*tags*",
        "*laravel*",
        "*absensi*",
        config('cache.prefix') . '*',
    ];

    foreach ($patterns as $pattern) {
        echo "\nKeys matching pattern: $pattern\n";
        $keys = $redis->keys($pattern);
        if (empty($keys)) {
            echo "  NO_KEYS_FOUND\n";
            continue;
        }
        $count = 0;
        foreach ($keys as $k) {
            echo "  $k\n";
            $count++;
            if ($count >= 200) { echo "  ... (truncated)\n"; break; }
        }
        echo "  (total: " . count($keys) . ")\n";
    }
} catch (Throwable $e) {
    echo "Redis error: " . $e->getMessage() . "\n";
}
