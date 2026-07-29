<?php
// Simple script to inspect Redis keys for report cache
try {
    $r = new Redis();
    $r->connect('127.0.0.1');
    $pattern = 'absensi:laporan_siswa:*';
    $keys = $r->keys($pattern);
    if (empty($keys)) {
        echo "NO_KEYS_FOUND\n";
        exit(0);
    }
    foreach ($keys as $k) {
        echo "KEY: $k\n";
        $ttl = $r->ttl($k);
        echo "TTL: $ttl\n";
        $type = $r->type($k);
        echo "TYPE: $type\n";
        $val = $r->get($k);
        if ($val === false) {
            echo "VALUE: <binary or non-string value>\n";
        } else {
            $snippet = mb_substr($val, 0, 500);
            if (mb_strlen($val) > 500) $snippet .= '...[truncated]';
            echo "VALUE (snippet): ";
            echo $snippet . "\n";
        }
        echo "----\n";
    }
} catch (Throwable $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    exit(2);
}
