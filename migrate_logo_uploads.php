<?php
// Jalankan script ini sekali saja untuk migrasi file logo lama ke storage/app/public/logos
define('BASE', __DIR__ . '/../');
$oldDir = BASE . 'public/uploads/logos/';
$newDir = BASE . 'storage/app/public/logos/';

if (!is_dir($oldDir)) {
    echo "Folder lama tidak ditemukan: $oldDir\n";
    exit(1);
}
if (!is_dir($newDir)) {
    mkdir($newDir, 0777, true);
}

$files = scandir($oldDir);
$count = 0;
foreach ($files as $file) {
    if ($file === '.' || $file === '..') continue;
    $src = $oldDir . $file;
    $dst = $newDir . $file;
    if (is_file($src)) {
        if (!file_exists($dst)) {
            if (copy($src, $dst)) {
                echo "Copied: $file\n";
                $count++;
            } else {
                echo "Failed to copy: $file\n";
            }
        } else {
            echo "Already exists: $file\n";
        }
    }
}
echo "Total copied: $count\n";
