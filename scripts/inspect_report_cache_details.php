<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\DB;

// Parameters from user test
$selectedTanggal = '2026-07-01';
$period = 'monthly';
$rangeStart = '2026-06-30';
$rangeEnd = '2026-07-30';
$kelasId = null;
$guruId = null;

// compute start/end similarly to controller
$startDate = $rangeStart;
$endDate = $rangeEnd;

// fetch active tahun/semester
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

echo "Computed cacheKey: $cacheKey\n";

// Try via Cache facade with tags and without
    try {
        $valTagged = null;
        $valPlain = null;
        // attempt tagged get
        try {
            $valTagged = Cache::tags(['absensi_laporan_siswa'])->get($cacheKey);
            echo "Cache::tags(...)->get returned: ";
            echo (is_null($valTagged) ? "NULL\n" : "value of type " . gettype($valTagged) . " (" . (is_object($valTagged) ? get_class($valTagged) : (is_array($valTagged) ? 'array' : 'scalar')) . ")\n");
        } catch (Throwable $e) {
            echo "Tagged cache get failed: " . $e->getMessage() . "\n";
        }

        // plain get
        try {
            $valPlain = Cache::get($cacheKey);
            echo "Cache::get returned: ";
            if (is_null($valPlain)) {
                echo "NULL\n";
            } else {
                $type = gettype($valPlain);
                echo "value of type " . $type . "\n";
                if (is_object($valPlain)) {
                    echo "  Class: " . get_class($valPlain) . "\n";
                }
                if (is_countable($valPlain)) {
                    echo "  Count: " . count($valPlain) . "\n";
                    $first = null;
                    if (is_array($valPlain)) {
                        $first = reset($valPlain);
                    } elseif (method_exists($valPlain, 'first')) {
                        $first = $valPlain->first();
                    }
                    if ($first) {
                        echo "  Sample first element: \n";
                        if (is_object($first) || is_array($first)) {
                            echo "    " . json_encode($first, JSON_UNESCAPED_UNICODE|JSON_PRETTY_PRINT) . "\n";
                        } else {
                            echo "    " . var_export($first, true) . "\n";
                        }
                    }
                }
            }
        } catch (Throwable $e) {
            echo "Plain cache get failed: " . $e->getMessage() . "\n";
        }
    } catch (Throwable $e) {
        echo "Cache access error: " . $e->getMessage() . "\n";
    }

// Inspect redis keys in cache DB
try {
    $connection = config('cache.stores.redis.connection') ?? 'cache';
    $redis = Redis::connection($connection);
    echo "Using redis connection: $connection\n";

    $patterns = [
        '*laporan_siswa*',
        '*absensi_laporan_siswa*',
        config('cache.prefix') . '*absensi*',
    ];

    foreach ($patterns as $pattern) {
        echo "\nKeys matching pattern: $pattern\n";
        $keys = $redis->keys($pattern);
        if (empty($keys)) {
            echo "  NO_KEYS_FOUND\n";
            continue;
        }
        foreach ($keys as $k) {
            echo "KEY: $k\n";
            echo "  TTL: " . $redis->ttl($k) . "\n";
            echo "  TYPE: " . $redis->type($k) . "\n";
            $val = $redis->get($k);
            if ($val === false) {
                echo "  VALUE: <none>\n";
            } else {
                $snippet = mb_substr($val, 0, 500);
                if (mb_strlen($val) > 500) $snippet .= '...[truncated]';
                echo "  VALUE_SNIPPET: " . $snippet . "\n";
            }
        }
    }
} catch (Throwable $e) {
    echo "Redis inspection failed: " . $e->getMessage() . "\n";
}
