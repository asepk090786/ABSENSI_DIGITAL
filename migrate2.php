<?php
/**
 * SIMADIS Tabler Migration - safe mechanical replacements
 */
$viewsDir = __DIR__ . '/resources/views';
$backupDir = __DIR__ . '/views_backup_' . date('Ymd_His');
if (!is_dir($backupDir)) {
    mkdir($backupDir, 0755, true);
    echo "Backup: $backupDir\n";
}

$files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($viewsDir, RecursiveDirectoryIterator::SKIP_DOTS));
$updated = 0;

foreach ($files as $file) {
    if ($file->getExtension() !== 'blade.php') continue;
    $rel = ltrim(substr($file->getPathname(), strlen($viewsDir)), '/');
    if (strpos($rel, 'layouts/') === 0 || strpos($rel, 'tabler/') === 0) continue;

    $content = file_get_contents($file->getPathname());
    $o = $content;

    // Backup
    $d = $backupDir . '/' . $rel;
    @mkdir(dirname($d), 0755, true);
    @copy($file->getPathname(), $d);

    // 1. pageSlug parameter
    $content = preg_replace("@extends\(['\"]layouts\.app['\"],\s*\[.*?\]\)@", "@extends('layouts.app')", $content);

    // 2. data-toggle/data-target → data-bs-toggle/data-bs-target
    $content = preg_replace('/(data)-(toggle|dismiss|target)/', '$1-bs-$2', $content);

    // 3. btn-outline btn-sm classes
    $repls = [
        'btn btn-sm btn-outline-primary' => 'btn btn-sm btn-outline-primary btn-modern',
        'btn btn-sm btn-outline-danger' => 'btn btn-sm btn-outline-danger btn-modern',
        'btn btn-sm btn-outline-success' => 'btn btn-sm btn-outline-success btn-modern',
        'btn btn-sm btn-outline-info' => 'btn btn-sm btn-outline-info btn-modern',
        'btn btn-sm btn-outline-warning' => 'btn btn-sm btn-outline-warning btn-modern',
        'btn btn-sm btn-secondary' => 'btn btn-sm btn-secondary btn-modern',
        'btn btn-primary btn-sm' => 'btn btn-sm btn-primary btn-modern',
        'btn btn-outline-primary' => 'btn btn-outline-primary btn-modern',
        'btn btn-outline-danger' => 'btn btn-outline-danger btn-modern',
        'btn btn-outline-info' => 'btn btn-outline-info btn-modern',
        'btn btn-outline-success' => 'btn btn-outline-success btn-modern',
        'btn btn-outline-secondary' => 'btn btn-outline-secondary btn-modern',
    ];
    foreach ($repls as $from => $to) {
        $content = str_replace('class="' . $from . '"', 'class="' . $to . '"', $content);
        $content = str_replace("class='" . $from . "'", "class='" . $to . "'", $content);
    }

    // 4. table class
    $content = str_replace('table table-striped table-hover', 'table table-vcenter table-hover table-tabler', $content);

    // 5. card-title mb-0/mb-1
    $content = str_replace('card-title mb-0', 'card-title fw-semibold m-0', $content);
    $content = str_replace('card-title mb-1', 'card-title fw-semibold mb-1', $content);

    // 6. Simple card-header wrap (non-jsx style)
    $content = str_replace('class="card-header">', 'class="card-header border-0 pt-3 pb-2">', $content);

    // 7. form-label
    $content = str_replace('> <label class="form-label">', '><label class="form-label fw-medium">', $content);
    $content = str_replace('><label class="form-label">', '><label class="form-label fw-medium">', $content);

    // 8. Remove HTML comments
    $content = preg_replace('/<!--(?!\s*\[if).*?-->/s', '', $content);

    // 9. Remove duplicated page-header issues (blade comments)
    $content = str_replace('@endsection', '', $content);

    if ($content !== $o) {
        file_put_contents($file->getPathname(), $content);
        $updated++;
    }
}

echo "Done. Updated: $updated files.\n";
