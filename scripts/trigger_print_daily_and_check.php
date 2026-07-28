<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Illuminate\Http\Request as HttpRequest;
use App\Models\User;
use Carbon\Carbon;

// parameters: tanggal = today, period omitted (daily)
$tanggal = Carbon::today()->format('Y-m-d');
$params = [ 'tanggal' => $tanggal ];

// find admin user
$user = User::whereHas('roles', function($q){ $q->where('role_name', 'Admin'); })->orWhereHas('role', function($q){ $q->where('role_name', 'Admin'); })->first();
if (! $user) {
    echo "NO_ADMIN_USER_FOUND\n";
    exit(2);
}
Auth::login($user);

$httpKernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$request = HttpRequest::create('/absensi/laporan-siswa/print', 'GET', $params);
$response = $httpKernel->handle($request);
$status = $response->getStatusCode();
echo "REQUEST_STATUS: $status\n";
$content = $response->getContent();
echo "RESPONSE_BYTES: " . strlen($content) . "\n";

// show last cache_check log lines
$logpath = storage_path('logs/laravel.log');
echo "-- Last cache_check log lines --\n";
if (file_exists($logpath)) {
    $text = file_get_contents($logpath);
    $arr = array_reverse(explode("\n", $text));
    $count = 0;
    foreach ($arr as $line) {
        if (stripos($line, 'absensi.laporan.cache_check') !== false || stripos($line, 'absensi.laporan.cache_check_failed') !== false) {
            echo $line . "\n";
            $count++;
            if ($count >= 40) break;
        }
    }
}

// inspect redis keys in cache DB
try {
    $connection = config('cache.stores.redis.connection') ?? 'cache';
    $redis = Illuminate\Support\Facades\Redis::connection($connection);
    $pattern = '*laporan_siswa*';
    $keys = $redis->keys($pattern);
    echo "-- Redis keys (pattern: $pattern) --\n";
    if (empty($keys)) {
        echo "NO_KEYS_FOUND\n";
    } else {
        foreach ($keys as $k) {
            echo "KEY: $k\n";
            echo "TTL: " . $redis->ttl($k) . "\n";
            echo "TYPE: " . $redis->type($k) . "\n";
            echo "VALUE (first 200 chars):\n";
            $val = $redis->get($k);
            if ($val === false) {
                echo "<none>\n";
            } else {
                echo substr($val, 0, 200) . (strlen($val) > 200 ? '...[truncated]\n' : "\n");
            }
            echo "----\n";
        }
    }
} catch (Throwable $e) {
    echo "REDIS_ERROR: " . $e->getMessage() . "\n";
}

$httpKernel->terminate($request, $response);
