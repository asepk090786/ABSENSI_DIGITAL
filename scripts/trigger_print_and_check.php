<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
// bootstrap the application (providers, DB, etc.) using the console kernel
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$httpKernel = $app->make(Illuminate\Contracts\Http\Kernel::class);

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Illuminate\Http\Request as HttpRequest;
use App\Models\User;

// parameters
$params = [
    'tanggal' => '2026-07-01',
    'period' => 'monthly',
    'range_start' => '2026-06-30',
    'range_end' => '2026-07-30',
];

// find admin user
$user = User::whereHas('roles', function($q){ $q->where('role_name', 'Admin'); })->orWhereHas('role', function($q){ $q->where('role_name', 'Admin'); })->first();
if (! $user) {
    echo "NO_ADMIN_USER_FOUND\n";
    exit(2);
}
Auth::login($user);

// create request to the route
$request = HttpRequest::create('/absensi/laporan-siswa/print', 'GET', $params);
// copy cookies/session? not necessary since Auth::login should set current user

// handle request via HTTP kernel
$response = $httpKernel->handle($request);

$status = $response->getStatusCode();
echo "REQUEST_STATUS: $status\n";
$content = $response->getContent();
$len = strlen($content);
echo "RESPONSE_BYTES: $len\n";

// show last 40 lines of laravel log for cache_check entries
$logpath = storage_path('logs/laravel.log');
echo "-- Last cache_check log lines --\n";
$lines = [];
if (file_exists($logpath)) {
    $text = file_get_contents($logpath);
    $arr = explode("\n", $text);
    $arr = array_reverse($arr);
    $count = 0;
    foreach ($arr as $line) {
        if (stripos($line, 'absensi.laporan.cache_check') !== false || stripos($line, 'absensi.laporan.cache_check_failed') !== false) {
            echo $line . "\n";
            $count++;
            if ($count >= 40) break;
        }
    }
}

// inspect redis keys via Cache/Redis
try {
    $redisConn = config('cache.stores.redis.connection') ?? 'cache';
    $redis = Illuminate\Support\Facades\Redis::connection($redisConn);
    $pattern = config('cache.prefix') . '*absensi:laporan_siswa*';
    echo "-- Redis keys (pattern: $pattern) --\n";
    $keys = $redis->keys('*laporan_siswa*');
    if (empty($keys)) {
        echo "NO_KEYS_FOUND\n";
    } else {
        foreach ($keys as $k) {
            echo "KEY: $k\n";
            echo "TTL: " . $redis->ttl($k) . "\n";
            echo "TYPE: " . $redis->type($k) . "\n";
            $val = $redis->get($k);
            if ($val === false) {
                echo "VALUE: <none>\n";
            } else {
                $snippet = mb_substr($val, 0, 500);
                if (mb_strlen($val) > 500) $snippet .= '...[truncated]';
                echo "VALUE_SNIPPET: " . $snippet . "\n";
            }
            echo "----\n";
        }
    }
} catch (Throwable $e) {
    echo "REDIS_ERROR: " . $e->getMessage() . "\n";
}

$httpKernel->terminate($request, $response);
