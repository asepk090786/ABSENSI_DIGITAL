<?php
$pattern = $argv[1] ?? '*laporan_siswa*';
try {
    $r = new Redis();
    $r->connect('127.0.0.1');
    $keys = $r->keys($pattern);
    if (empty($keys)) {
        echo "NO_KEYS_FOUND for pattern: $pattern\n";
        exit(0);
    }
    foreach ($keys as $k) {
        echo "KEY: $k\n";
        echo "TTL: " . $r->ttl($k) . "\n";
        echo "TYPE: " . $r->type($k) . "\n";
        $val = $r->get($k);
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
