<?php
/**
 * SIMADIS — Bulk migrate views from AdminLTE to Tabler UI
 * Run: php migrate_views_to_tabler.php
 */

$viewsDir = __DIR__ . '/resources/views';
$backupDir = __DIR__ . '/resources/views_backup_' . date('Ymd_His');

if (!is_dir($viewsDir)) {
    die("Views directory not found: $viewsDir\n");
}

$count = 0;
$skipped = 0;
$errors = [];

// 1. Backup original views
if (!is_dir($backupDir)) {
    mkdir($backupDir, 0755, true);
    $it = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($viewsDir, RecursiveDirectoryIterator::SKIP_DOTS));
    foreach ($it as $file) {
        $rel = substr($file->getPathname(), strlen($viewsDir) + 1);
        $dest = $backupDir . '/' . $rel;
        if ($file->isDir()) {
            @mkdir($dest, 0755, true);
        } else {
            @copy($file->getPathname(), $dest);
        }
    }
    echo "✓ Backup created at: $backupDir\n";
}

// 2. Process all blade files
$files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($viewsDir, RecursiveDirectoryIterator::SKIP_DOTS));
foreach ($files as $file) {
    if ($file->getExtension() !== 'blade.php') continue;
    
    // Skip layouts directory (we handle it separately)
    $rel = substr($file->getPathname(), strlen($viewsDir) + 1);
    if (strpos($rel, 'layouts/') === 0) continue;
    
    $content = file_get_contents($file->getPathname());
    $original = $content;

    // A. Remove pageSlug parameter from extends
    $content = preg_replace("/@extends\(['\"]layouts\.app['\"],\s*\[.*?\]\)/", "@extends('layouts.app')", $content);

    // B. Replace old-style @section('title', ...) keep as is but normalize
    // C. Convert old content-body structure
    // Remove outer <div class="row"><div class="col-md-12"><div class="card"> wrappers for simple content
    // This is complex - we do targeted replacements instead

    // D. Replace data-toggle -> data-bs-toggle
    $content = str_replace('data-toggle="', 'data-bs-toggle="', $content);

    // E. Replace data-target -> data-bs-target  
    $content = str_replace('data-target="#', 'data-bs-target="#', $content);
    $content = str_replace("data-target='#", "data-bs-target='#", $content);

    // F. Modernize page headers (convert old Tabler-style page-header blocks)
    // Pattern: <div class="page-header ..."> ... </div> -> @section('page-header') ... @endsection
    $content = preg_replace_callback(
        '/<div class="page-header(.*?)">(.*?)<\/div>/s',
        function ($m) {
            $inner = $m[2];
            // Remove old btn-print / d-print-none wrappers noise
            $inner = preg_replace('/\s*d-print-none\s*/', '', $inner);
            // Unwrap inner row divs
            $inner = preg_replace('/<div class="row[^"]*">(.*?)<\/div>/s', '$1', $inner);
            $inner = preg_replace('/<div class="col[^"]*">(.*?)<\/div>/s', '$1', $inner);
            $inner = preg_replace('/<div class="col-auto[^"]*">(.*?)<\/div>/s', '$1', $inner);
            // Remove old bad d-print-none btn-list wrapping from header
            return "@section('page-header')\n<div class=\"page-header\">\n$inner\n</div>\n@endsection";
        },
        $content
    );

    // If no page-header was found, inject a minimal one before @section('content')
    if (strpos($content, "@section('page-header')") === false && strpos($content, "@section('content')") !== false) {
        $title = "Dashboard";
        if (preg_match("/@section\('title',\s*'([^']+)'\)/", $content, $t)) {
            $title = $t[1];
        }
        $content = str_replace(
            "@section('content')",
            "@section('page-header')\n<div class=\"page-header\">\n<h2 class=\"page-title\">$title</h2>\n</div>\n@endsection\n\n@section('content')",
            $content
        );
    }

    // G. Apply layout review fixes: remove misplaced old Tabler page-header from inside content
    $content = preg_replace('/@section\(\'content\'\)\s*<div class="page-header[^>]*>.*?<\/div>\s*/s', "@section('content')\n", $content);

    // H. Update table classes: table table-striped table-hover -> table table-vcenter table-hover (Tabler standard)
    // Also wrap inside table-responsive if not already
    $content = preg_replace('/class="table table-striped table-hover"/', 'class="table table-vcenter table-hover"', $content);
    $content = preg_replace('/class="table table-vcenter table-hover"/', 'class="table table-vcenter table-hover table-tabler"', $content);

    // I. Update btn classes to btn-modern
    $content = preg_replace('/class="btn btn-sm btn-outline-primary"/', 'class="btn btn-sm btn-outline-primary btn-modern"', $content);
    $content = preg_replace('/class="btn btn-sm btn-outline-danger"/', 'class="btn btn-sm btn-outline-danger btn-modern"', $content);
    $content = preg_replace('/class="btn btn-sm btn-info"/', 'class="btn btn-sm btn-info btn-modern"', $content);
    $content = preg_replace('/class="btn btn-sm btn-success"/', 'class="btn btn-sm btn-success btn-modern"', $content);
    $content = preg_replace('/class="btn btn-sm btn-secondary"/', 'class="btn btn-sm btn-secondary btn-modern"', $content);
    $content = preg_replace('/class="btn btn-primary"/', 'class="btn btn btn-primary btn-modern"', $content);
    $content = preg_replace('/class="btn btn-outline-primary"/', 'class="btn btn-outline-primary btn-modern"', $content);
    $content = preg_replace('/class="btn btn-outline-secondary"/', 'class="btn btn-outline-secondary btn-modern"', $content);
    $content = preg_replace('/class="btn btn-outline-danger"/', 'class="btn btn-outline-danger btn-modern"', $content);
    $content = preg_replace('/class="btn btn-outline-info"/', 'class="btn btn-outline-info btn-modern"', $content);
    $content = preg_replace('/class="btn btn-outline-success"/', 'class="btn btn-outline-success btn-modern"', $content);

    // J. Update card-title heading classes
    $content = preg_replace('/class="card-title mb-0"/', 'class="card-title fw-semibold m-0"', $content);
    $content = preg_replace('/class="card-title mb-1"/', 'class="card-title fw-semibold mb-1"', $content);

    // K. Remove old-style close buttons (using × character)
    $content = str_replace('<span aria-hidden="true">&times;</span>', '', $content);

    // L. Replace old alert-dismissible close pattern
    $content = preg_replace('/<button type="button" class="close" data-dismiss="alert">/', '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>', $content);

    // M. Fix remaining data-dismiss="modal"
    $content = str_replace('data-dismiss="modal"', 'data-bs-dismiss="modal"', $content);
    $content = str_replace('data-dismiss="alert"', 'data-bs-dismiss="alert"', $content);

    // N. Wrap simple text content in cards where there's no card wrapper
    // (lightweight heuristic: if content starts with plain text/alert and not wrapped)
    // Skip for now to avoid breaking complex views

    // O. Update form-label classes
    $content = preg_replace('/class="form-label"/', 'class="form-label fw-medium"', $content);

    // P. Update card headers to have border-0 padding
    $content = preg_replace('/class="card-header">/', 'class="card-header border-0 pt-3 pb-2">/', $content);
    $content = preg_replace('/class="card-header ">/', 'class="card-header border-0 pt-3 pb-2">/', $content);
    $content = preg_replace('/class="card-header" style=/', 'class="card-header border-0 pt-3 pb-2" style=', $content);

    // Q. Remove old d-flex and btn-list from card-header if they look like old layout
    $content = preg_replace('/<div class="card-header">\s*<div class="row align-items-center">/', '<div class="card-header border-0 pt-3 pb-2 d-flex align-items-center justify-content-between flex-wrap gap-2">', $content);
    $content = preg_replace('/<div class="col">\s*<\/div>\s*<div class="col-auto">/', '', $content);
    $content = preg_replace('/<\/div>\s*<\/div>\s*<\/div>\s*<div class="card-body">/', '</div><div class="card-body">/', $content);

    // Write only if changed
    if ($content !== $original) {
        file_put_contents($file->getPathname(), $content);
        $count++;
    } else {
        $skipped++;
    }
}

echo "\n✓ Migration complete.\n";
echo "  Files updated: $count\n";
echo "  Files unchanged: $skipped\n";
echo "\nBackup stored at: $backupDir\n";
echo "To restore: cp -r $backupDir/* resources/views/\n";
