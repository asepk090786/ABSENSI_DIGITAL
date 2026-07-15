<?php
/**
 * Bulk migrate views from AdminLTE to Tabler UI
 */
$viewsDir = realpath(__DIR__ . '/resources/views');
if (!$viewsDir) die("Views directory not found\n");

$backupDir = __DIR__ . '/resources_backup_' . date('Ymd_His');
if (!is_dir($backupDir)) {
    mkdir($backupDir, 0755, true);
}

$count = 0;
$skipped = 0;

$it = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($viewsDir, RecursiveDirectoryIterator::SKIP_DOTS));
foreach ($it as $file) {
    if ($file->getExtension() !== 'blade.php') continue;
    $rel = substr($file->getPathname(), strlen($viewsDir) + 1);
    if (strpos($rel, 'layouts/') === 0) { $skipped++; continue; }

    $content = file_get_contents($file->getPathname());
    $original = $content;

    // Backup (only once per file if backup dir exists but file not yet backed up)
    $dest = $backupDir . '/' . $rel;
    @mkdir(dirname($dest), 0755, true);
    @copy($file->getPathname(), $dest);

    // 1. Remove old pageSlug parameter
    $content = preg_replace("/@extends\(['\"]layouts\.app['\"],\s*\[.*?'pageSlug'\s*=>\s*[^\]]*\]\)/", "@extends('layouts.app')", $content);
    $content = preg_replace("/@extends\(['\"]layouts\.app['\"],\s*\[.*?\]\)/", "@extends('layouts.app')", $content);

    // 2. data-toggle -> data-bs-toggle
    $content = str_replace(["data-toggle=\"", "data-toggle='"], ["data-bs-toggle=\"", "data-bs-toggle='"], $content);

    // 3. data-target -> data-bs-target
    $content = str_replace(["data-target=\"#", "data-target='#"], ["data-bs-target=\"#", "data-bs-target='#"], $content);

    // 4. Modernize page headers: convert old page-header HTML to @section blade
    // First handle <!-- Tabler-style wrappers -->
    $content = preg_replace('/<!--[^>]*-->/s', '', $content);

    // Match <div class="page-header d-print-none"> patterns
    $content = preg_replace_callback(
        '/<div class="page-header[^>]*>(.*?)<\/div>\s*<\/div>\s*<\/div>\s*<\/div>/s',
        function ($m) {
            $inner = $m[1];
            $inner = preg_replace('/<div class="row[^"]*">(.*?)<\/div>/s', '$1', $inner);
            $inner = preg_replace('/<div class="col[^"]*">(.*?)<\/div>/s', '$1', $inner);
            $inner = preg_replace('/<div class="col-auto[^"]*">(.*?)<\/div>/s', '$1', $inner);
            // Remove d-print-none classes
            $inner = str_replace('d-print-none', '', $inner);
            return "@section('page-header')\n<div class=\"page-header\">\n$inner\n</div>\n@endsection\n\n";
        },
        $content,
        -1,
        $count2
    );

    // 5. If still no @section('page-header'), inject one
    if (strpos($content, "@section('page-header')") === false && strpos($content, "@section('content')") !== false) {
        $title = 'Dashboard';
        if (preg_match("/@section\('title',\s*'([^']+)'\)/", $content, $t)) $title = $t[1];
        $content = str_replace(
            "@section('content')",
            "@section('page-header')\n<div class=\"page-header\">\n<h2 class=\"page-title\">$title</h2>\n</div>\n@endsection\n\n@section('content')",
            $content
        );
    }

    // 6. Table class modernization
    $content = preg_replace('/class="table table-striped table-hover"/', 'class="table table-vcenter table-hover table-tabler"', $content);
    $content = preg_replace('/class="table table-striped table-hover">/', 'class="table table-vcenter table-hover table-tabler">', $content);

    // 7. btn-sm -> btn-modern
    $content = preg_replace('/class="btn btn-sm btn-outline-primary"/', 'class="btn btn-sm btn-outline-primary btn-modern"', $content);
    $content = preg_replace('/class="btn btn-sm btn-outline-danger"/', 'class="btn btn-sm btn-outline-danger btn-modern"', $content);
    $content = preg_replace('/class="btn btn-sm btn-outline-success"/', 'class="btn btn-sm btn-outline-success btn-modern"', $content);
    $content = preg_replace('/class="btn btn-sm btn-outline-info"/', 'class="btn btn-sm btn-outline-info btn-modern"', $content);
    $content = preg_replace('/class="btn btn-sm btn-secondary"/', 'class="btn btn-sm btn-secondary btn-modern"', $content);
    $content = preg_replace('/class="btn btn-primary btn-sm"/', 'class="btn btn-sm btn-primary btn-modern"', $content);
    $content = preg_replace('/class="btn btn-outline-primary btn-sm"/', 'class="btn btn-sm btn-outline-primary btn-modern"', $content);
    $content = preg_replace('/class="btn btn-outline-danger btn-sm"/', 'class="btn btn-sm btn-outline-danger btn-modern"', $content);
    $content = preg_replace('/class="btn btn-outline-info btn-sm"/', 'class="btn btn-sm btn-outline-info btn-modern"', $content);
    $content = preg_replace('/class="btn btn-outline-success btn-sm"/', 'class="btn btn-sm btn-outline-success btn-modern"', $content);
    $content = preg_replace('/class="btn btn-outline-secondary btn-sm"/', 'class="btn btn-sm btn-outline-secondary btn-modern"', $content);
    $content = preg_replace('/class="btn btn-sm btn-outline-warning"/', 'class="btn btn-sm btn-outline-warning btn-modern"', $content);

    // 8. card-title modernization
    $content = preg_replace('/class="card-title mb-0"/', 'class="card-title fw-semibold m-0"', $content);
    $content = preg_replace('/class="card-title mb-1"/', 'class="card-title fw-semibold mb-1"', $content);

    // 9. card-header modernization
    $content = preg_replace('/<div class="card-header">\s*<div class="row align-items-center">/', '<div class="card-header border-0 pt-3 pb-2">', $content);
    $content = preg_replace('/<div class="col">\s*<\/div>\s*<div class="col-auto">\s*<div class="btn-list">/', '<div class="btn-list">', $content);
    $content = preg_replace('/<\/div>\s*<\/div>\s*<\/div>\s*<div class="card-body">/', '</div><div class="card-body">', $content);
    $content = preg_replace('/<div class="col">\s*<\/div>\s*<div class="col-auto">/', '', $content);

    // 10. Remove inner column wrappers from simple headers
    $content = preg_replace('/<div class="card-header">\s*<div class="row[^>]*>(.*?)<div class="col">(.*?)<\/div>.*?<div class="col-auto">(.*?)<\/div>.*?<\/div>\s*<\/div>/s', '<div class="card-header border-0 pt-3 pb-2">$2$3</div>"', $content);

    // 11. data-dismiss 
    $content = str_replace('data-dismiss="alert"', 'data-bs-dismiss="alert"', $content);
    $content = str_replace('data-dismiss="modal"', 'data-bs-dismiss="modal"', $content);

    // 12. form-label
    $content = preg_replace('/>\\s*<label class="form-label">/', '><label class="form-label fw-medium">', $content);

    // Write
    if ($content !== $original) {
        file_put_contents($file->getPathname(), $content);
        $count++;
    } else {
        $skipped++;
    }
}

echo "Migration complete.\nUpdated: $count | Unchanged: $skipped\n";
echo "Backup: $backupDir\n";
