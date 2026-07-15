<?php
/**
 * SIMADIS — Complete Tabler Migration Script
 * Migrates ALL non-layout blade views from AdminLTE to Tabler
 * Safe mechanical replacements + structural upgrades for key pages
 */

$viewsDir = realpath(__DIR__ . '/resources/views');
if (!$viewsDir) { die("Views dir not found\n"); }

$backupDir = __DIR__ . '/views_backup_tabler_migration';
if (!is_dir($backupDir)) {
    mkdir($backupDir, 0755, true);
    $it = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($viewsDir, RecursiveDirectoryIterator::SKIP_DOTS));
    foreach ($it as $f) {
        $rel = substr($f->getPathname(), strlen($viewsDir) + 1);
        $d = $backupDir . '/' . $rel;
        @mkdir(dirname($d), 0755, true);
        if ($f->isFile()) @copy($f->getPathname(), $d);
    }
    echo "✓ Backup created: $backupDir\n";
}

$SKIP_DIRS = ['layouts', 'tabler', 'pages', 'partials'];
$STRUCTURAL_FILES = [
    'dashboard/admin.blade.php', 'dashboard/guru.blade.php', 'dashboard/siswa.blade.php',
    'dashboard/kepala.blade.php', 'dashboard/user.blade.php',
    'siswa/index.blade.php', 'siswa/create.blade.php', 'siswa/edit.blade.php',
    'guru/index.blade.php', 'guru/create.blade.php', 'guru/edit.blade.php',
    'kelas/index.blade.php', 'kelas/create.blade.php', 'kelas/edit.blade.php',
    'nilai/index.blade.php', 'jadwal_kbm/index.blade.php', 'absensi/index.blade.php',
    'rekap_nilai/index.blade.php', 'setting/index.blade.php', 'setting/header.blade.php',
    'setting/semester.blade.php', 'setting/tahun_ajaran.blade.php',
    'auth/login.blade.php', 'profile/edit.blade.php',
    'jam_belajar/index.blade.php', 'mata_pelajaran/index.blade.php',
];

$it = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($viewsDir, RecursiveDirectoryIterator::SKIP_DOTS));
$updated = 0; $skipped = 0; $struct = 0;

foreach ($it as $file) {
    if ($file->getExtension() !== 'blade.php') continue;
    $rel = ltrim(substr($file->getPathname(), strlen($viewsDir)), '/');

    // Skip dirs
    $skip = false;
    foreach ($SKIP_DIRS as $d) {
        if (strpos($rel, $d . '/') === 0 || $rel === $d) { $skip = true; break; }
    }
    if ($skip) { $skipped++; continue; }

    $c = file_get_contents($file->getPathname());
    $o = $c;
    $isStructural = in_array($rel, $STRUCTURAL_FILES);

    // === SAFE MECHANICAL REPLACEMENTS (all files) ===

    // 1. pageSlug parameter cleanup
    $c = preg_replace("@extends\(['\"]layouts\.app['\"],\s*\[.*?\]\)@", "@extends('layouts.app')", $c);

    // 2. Bootstrap 4 → 5 data attributes
    $c = preg_replace('/(data)-(toggle|dismiss|target)/', '$1-bs-$2', $c);

    // 3. Close buttons: old × → modern btn-close
    $c = str_replace('"&times;</span>"', '"</button>"', $c);
    $c = preg_replace('#<button[^>]*class="close"[^>]*>#i', '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>', $c);

    // 4. Table modernization
    $c = str_replace('table table-striped table-hover', 'table table-vcenter table-hover table-tabler', $c);
    $c = preg_replace('/class="table\s+table-vcenter\s+table-hover\s+table-tabler\s+table-tabler"/', 'class="table table-vcenter table-hover table-tabler"', $c);

    // 5. Button modernization
    $btnPatterns = [
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
    foreach ($btnPatterns as $from => $to) {
        $c = str_replace('class="' . $from . '"', 'class="' . $to . '"', $c);
    }

    // 6. Card modernization
    $c = str_replace('class="card-title mb-0"', 'class="card-title fw-semibold m-0"', $c);
    $c = str_replace('class="card-title mb-1"', 'class="card-title fw-semibold mb-1"', $c);
    $c = str_replace('class="card-header">', 'class="card-header border-0 pt-3 pb-2">', $c);
    $c = str_replace('class="card-header ">', 'class="card-header border-0 pt-3 pb-2">', $c);

    // 7. Form label
    $c = str_replace('><label class="form-label">', '><label class="form-label fw-medium">', $c);

    // 8. Remove HTML comments (but keep blade directives)
    $c = preg_replace('/<!--(?!\s*\[if).*?-->/s', '', $c);

    // 9. Remove btn-list (not used in Tabler)
    $c = str_replace('class="btn-list"', '', $c);

    // 10. Remove d-print-none from content (layout handles it)
    $c = str_replace('d-print-none', '', $c);

    // 11. table-responsive → table-responsive-custom or just keep table-responsive
    // Tabler uses same class, keep as is

    // === STRUCTURAL UPGRADES (key files only) ===
    if ($isStructural && $c !== $o) {
        $c = structuralUpgrade($rel, $c);
    }

    if ($c !== $o) {
        file_put_contents($file->getPathname(), $c);
        $updated++;
        if ($isStructural) $struct++;
    } else {
        $skipped++;
    }
}

echo "\n✓ Migration Complete!\n";
echo "  Files updated: $updated\n";
echo "  Structural upgrades: $struct\n";
echo "  Files skipped: $skipped\n";
echo "  Backup: $backupDir\n";

function structuralUpgrade($rel, $c) {
    // Inject page-header if missing
    if (strpos($c, "@section('page-header')") === false && strpos($c, "@section('content')") !== false) {
        $title = 'Dashboard';
        if (preg_match("/@section\('title',\s*'([^']+)'\)/", $c, $t)) $title = $t[1];
        $c = str_replace(
            "@section('content')",
            "@section('page-header')\n<div class=\"page-header\">\n<h2 class=\"page-title\">$title</h2>\n</div>\n@endsection\n\n@section('content')",
            $c
        );
    }

    // Dashboard: add stat cards for admin
    if ($rel === 'dashboard/admin.blade.php') {
        $c = upgradeDashboardAdmin($c);
    }

    // Wrap simple text alerts in cards
    if (strpos($c, '<div class="alert alert-info">Selamat datang') !== false) {
        $c = preg_replace('#<div class="alert alert-info">(.*?)</div>#s', '<div class="card border-0 mb-3"><div class="card-body"><div class="alert alert-info mb-0">$1</div></div></div>', $c);
    }

    return $c;
}

function upgradeDashboardAdmin($c) {
    // Add stat cards section after the welcome alert
    $statsBlock = <<<'BLOCK'
<div class="row g-3 mb-3">
    <div class="col-6 col-md-3">
        <div class="card stat-card border-0">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="stat-icon bg-blue-lt text-blue"><i class="ti ti-school"></i></div>
                <div><div class="stat-label">Kelas</div><div class="stat-value">{{ number_format($kelas) }}</div></div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card stat-card border-0">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="stat-icon bg-teal-lt text-teal"><i class="ti ti-users"></i></div>
                <div><div class="stat-label">Siswa</div><div class="stat-value">{{ number_format($siswa) }}</div></div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card stat-card border-0">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="stat-icon bg-orange-lt text-orange"><i class="ti ti-user-check"></i></div>
                <div><div class="stat-label">Guru</div><div class="stat-value">{{ number_format($guru) }}</div></div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card stat-card border-0">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="stat-icon bg-violet-lt text-violet"><i class="ti ti-clipboard-list"></i></div>
                <div><div class="stat-label">Absensi</div><div class="stat-value">{{ number_format($absensi) }}</div></div>
            </div>
        </div>
    </div>
</div>
BLOCK;

    // Insert stats after the first card (welcome/printout card)
    $c = preg_replace('#(<div class="row mb-3">.*?</div>\s*</div>\s*</div>)#s', '$1' . $statsBlock, $c, 1);

    // Convert quick menu buttons to cards
    $c = preg_replace_callback(
        '#<a class="btn btn-outline-primary w-100" href="([^"]+)">\s*<i class="ti ti-[^"]+"></i>\s*([^<]+)\s*</a>#',
        function ($m) {
            $icons = [
                'building-bank' => 'ti-building',
                'users' => 'ti-users',
                'school' => 'ti-school',
                'building' => 'ti-building',
                'books' => 'ti-book',
                'clock' => 'ti-clock',
                'bolt' => 'ti-bolt',
                'lock' => 'ti-lock',
                'settings' => 'ti-settings',
            ];
            return '<div class="col-6 col-md-4 col-lg-3"><a href="'.$m[1].'" class="quick-menu-card"><div class="qm-icon" style="background:var(--tblr-primary);"><i class="ti ti-users"></i></div><span class="fw-medium">'.$m[2].'</span></a></div>';
        },
        $c
    );
    // Rebuild quick menu row
    $c = preg_replace('#<div class="row">\s*<div class="col-12">\s*<div class="card">.*?Menu Cepat.*?</div>\s*</div>\s*</div>#s',
        '<div class="row g-3 mb-3"><div class="col-12"><div class="card"><div class="card-header border-0 py-3"><h3 class="card-title fw-semibold m-0">Menu Cepat</h3></div><div class="card-body"><div class="row g-3" id="quickMenu">'.
        str_repeat('<div class="col-6 col-md-4 col-lg-3"><a href="route(\'siswa.index\')" class="quick-menu-card"><div class="qm-icon" style="background:var(--tblr-primary);"><i class="ti ti-user"></i></div><span class="fw-medium">Siswa</span></a></div>', 4) .
        '</div></div></div></div></div>',
        $c
    );

    return $c;
}
