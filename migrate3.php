<?php
/**
 * SIMADIS Tabler Migration - using glob
 */
$viewsDir = __DIR__ . '/resources/views';
$backupDir = __DIR__ . '/views_backup_tabler2';

if (!is_dir($backupDir)) {
    mkdir($backupDir, 0755, true);
}

$files = new RecursiveIteratorIterator(
    new RecursiveDirectoryIterator($viewsDir, RecursiveDirectoryIterator::SKIP_DOTS),
    RecursiveIteratorIterator::LEAVES_ONLY
);

$updated = 0; $skipped = 0;

foreach ($files as $file) {
    if ($file->getExtension() !== 'blade.php') continue;
    
    $path = $file->getPathname();
    $rel = str_replace($viewsDir . '/', '', $path);
    
    // Skip layouts and tabler dirs
    if (strpos($rel, 'layouts/') === 0 || strpos($rel, 'tabler/') === 0) {
        $skipped++;
        continue;
    }
    
    $c = file_get_contents($path);
    $o = $c;
    
    // Backup
    $dest = $backupDir . '/' . $rel;
    @mkdir(dirname($dest), 0755, true);
    @copy($path, $dest);
    
    // 1. pageSlug cleanup
    $c = preg_replace("@extends\(['\"]layouts\.app['\"],\s*\[.*?\]\)@", "@extends('layouts.app')", $c);
    
    // 2. Bootstrap 4 → 5 data attributes
    $c = preg_replace('/(data)-(toggle|dismiss|target)/', '$1-bs-$2', $c);
    
    // 3. Close buttons
    $c = preg_replace('#<button[^>]*class="close"[^>]*>\s*<span[^>]*>&times;</span>\s*</button>#i', '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>', $c);
    $c = preg_replace('#<button[^>]*class="close"[^>]*>\s*</button>#i', '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>', $c);
    
    // 4. Table modernization
    $c = str_replace('table table-striped table-hover', 'table table-vcenter table-hover table-tabler', $c);
    
    // 5. Button classes
    $btnMap = [
        'btn btn-outline-primary' => 'btn btn-outline-primary btn-modern',
        'btn btn-outline-danger' => 'btn btn-outline-danger btn-modern',
        'btn btn-outline-success' => 'btn btn-outline-success btn-modern',
        'btn btn-outline-info' => 'btn btn-outline-info btn-modern',
        'btn btn-outline-warning' => 'btn btn-outline-warning btn-modern',
        'btn btn-outline-secondary' => 'btn btn-outline-secondary btn-modern',
        'btn btn-primary btn-sm' => 'btn btn-sm btn-primary btn-modern',
        'btn btn-sm btn-secondary' => 'btn btn-sm btn-secondary btn-modern',
        'btn btn-sm btn-success' => 'btn btn-sm btn-success btn-modern',
        'btn btn-sm btn-info' => 'btn btn-sm btn-info btn-modern',
        'btn btn-success btn-sm' => 'btn btn-sm btn-success btn-modern',
        'btn btn-info btn-sm' => 'btn btn-sm btn-info btn-modern',
    ];
    foreach ($btnMap as $k => $v) {
        $c = str_replace('class="' . $k . '"', 'class="' . $v . '"', $c);
    }
    
    // 6. Card headers
    $c = str_replace('card-title mb-0', 'card-title fw-semibold m-0', $c);
    $c = str_replace('card-title mb-1', 'card-title fw-semibold mb-1', $c);
    $c = str_replace('class="card-header">', 'class="card-header border-0 pt-3 pb-2">', $c);
    
    // 7. Form labels
    $c = str_replace('><label class="form-label">', '><label class="form-label fw-medium">', $c);
    
    // 8. Remove HTML comments
    $c = preg_replace('/<!--.*?-->/s', '', $c);
    
    // 9. Remove btn-list wrapper (inline buttons don't need this in Tabler)
    $c = str_replace('class="btn-list"', '', $c);
    
    // Write if changed
    if ($c !== $o) {
        file_put_contents($path, $c);
        $updated++;
    } else {
        $skipped++;
    }
}

echo "✓ Migration done!\nUpdated: $updated | Skipped: $skipped\nBackup: $backupDir\n";
