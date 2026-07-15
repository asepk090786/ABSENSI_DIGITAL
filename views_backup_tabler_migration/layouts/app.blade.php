<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'ABSENSI DIGITAL — SMAN 1 PONTANG')</title>

    <link rel="icon" type="image/png" href="{{ asset('storage/' . \App\Models\Sekolah::first()?->logo ?? 'defaults/logo.png') }}" />

    <link rel="preconnect" href="https://cdn.jsdelivr.net/">
    <link rel="preconnect" href="https://unpkg.com/">

    <!-- Tabler v2 Beta -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/core@2.46.0/dist/css/tabler.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/core@2.46.0/dist/css/tabler-flags.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/core@2.46.0/dist/css/tabler-vendors.min.css">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <script src="https://cdn.jsdelivr.net/npm/tabler-icons@2.28.0/dist/tabler-icons.umd.min.js"></script>

    <style>
        :root {
            --tblr-font-family: 'Inter', system-ui, -apple-system, sans-serif;
            --sidebar-w: 16.5rem;
        }

        body {
            font-family: var(--tblr-font-family);
            background: var(--tblr-body-bg);
        }

        .logo-sm { width: 1.6rem; height: 1.6rem; object-fit: cover; border-radius: .4rem; }
        .brand-name { font-weight: 700; letter-spacing: -0.02em; font-size: 1.05rem; }
        .brand-sub { font-size: .65rem; opacity: .6; letter-spacing: 0; display: block; line-height: 1; }

        .navbar-brand { gap: .65rem; text-decoration: none; }

        .navbar-brand .brand-icon {
            width: 2rem; height: 2rem; border-radius: .55rem; object-fit: cover;
            display: inline-flex; align-items: center; justify-content: center;
            background: var(--tblr-primary); color: #fff; font-size: 1rem;
        }

        .page-header { margin-bottom: 1.25rem; }
        .page-header .page-pretitle { font-size: .72rem; text-transform: uppercase; letter-spacing: .08em; color: var(--tblr-muted); margin-bottom: .15rem; }
        .page-header h2 { font-weight: 700; letter-spacing: -0.02em; margin-bottom: 0; }

        .card { border-radius: .85rem; border: 1px solid var(--tblr-border-color); transition: box-shadow .2s ease; }
        .card:hover { box-shadow: 0 4px 24px rgba(0,0,0,.04); }

        .stat-card { position: relative; overflow: hidden; }
        .stat-card .stat-icon {
            width: 3.2rem; height: 3.2rem; border-radius: .75rem;
            display: flex; align-items: center; justify-content: center;
            font-size: 1.5rem;
        }
        .stat-card .stat-label { font-size: .78rem; color: var(--tblr-muted); font-weight: 500; margin-bottom: .2rem; }
        .stat-card .stat-value { font-size: 1.9rem; font-weight: 700; line-height: 1; letter-spacing: -0.02em; }
        .stat-card .stat-trend { font-size: .72rem; margin-top: .4rem; }

        .quick-menu-card {
            border-radius: .75rem; border: 1.5px solid var(--tblr-border-color);
            padding: 1.1rem .9rem; display: flex; flex-direction: column; align-items: center; text-align: center;
            gap: .6rem; cursor: pointer; text-decoration: none; color: inherit;
            transition: all .2s ease; background: #fff; height: 100%;
        }
        .quick-menu-card:hover {
            transform: translateY(-2px);
            border-color: var(--tblr-primary);
            box-shadow: 0 6px 20px rgba(32,107,204,.1);
        }
        .quick-menu-card .qm-icon {
            width: 3rem; height: 3rem; border-radius: .7rem; display: flex; align-items: center; justify-content: center;
            font-size: 1.4rem; color: #fff;
        }

        .table-tabler .btn { font-size: .78rem; }

        .sidebar .nav-link {
            border-radius: .5rem; margin: 1px 0; font-weight: 500;
            color: var(--tblr-muted);
            transition: all .15s ease;
        }
        .sidebar .nav-link.active, .sidebar .nav-link[aria-current="page"] {
            background: var(--tblr-primary-light);
            color: var(--tblr-primary);
            font-weight: 600;
        }
        .sidebar .nav-link:hover { color: var(--tblr-body-color); background: var(--tblr-body-color-hover); }
        .sidebar .nav-group-title { font-size: .68rem; text-transform: uppercase; letter-spacing: .08em; color: var(--tblr-muted); padding: .9rem 1rem .3rem; font-weight: 600; }

        @media (max-width: 991.98px) {
            .navbar-brand .brand-text { display: none; }
            .brand-sub { display: none; }
        }

        .form-control, .form-select { border-radius: .5rem; border: 1.5px solid var(--tblr-border-color); }
        .form-control:focus, .form-select:focus { border-color: var(--tblr-primary); box-shadow: 0 0 0 3px rgba(var(--tblr-primary-rgb), .15); }

        .btn-modern { border-radius: .5rem; font-weight: 500; transition: all .15s; border: none; }
        .btn-modern:hover { transform: translateY(-1px); box-shadow: 0 3px 10px rgba(0,0,0,.1); }

        .timeline { position: relative; padding-left: 2rem; }
        .timeline::before { content: ''; position: absolute; left: .55rem; top: .3rem; bottom: .3rem; width: 2px; background: var(--tblr-border-color); }
        .timeline .t-item { position: relative; margin-bottom: 1.2rem; }
        .timeline .t-item::before { content: ''; position: absolute; left: -1.55rem; top: .35rem; width: .75rem; height: .75rem; border-radius: 50%; border: 2px solid #fff; box-shadow: 0 0 0 2px var(--tblr-primary); }
        .timeline .t-time { font-size: .7rem; color: var(--tblr-muted); margin-top: .1rem; }
    </style>

    @stack('css')
</head>
<body class="layout-sidebar">

<div class="page">
    <!-- Navbar -->
    <header class="navbar navbar-expand-md d-none d-lg-flex" style="position:sticky;top:0;z-index:1030;background:#fff;border-bottom:1px solid var(--tblr-border-color);">
        <div class="container-xl">
            <button class="navbar-toggler collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#topNav" aria-controls="topNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>

            <a href="{{ route('home') }}" class="navbar-brand">
                @php
                    $logoDefault = \App\Models\Sekolah::first()?->logo;
                    $logoSrc = $logoDefault ? asset('storage/'.$logoDefault) : asset('images/icon_simadis.png');
                @endphp
                <span class="brand-icon">
                    @if($logoDefault)
                        <img src="{{ $logoSrc }}" class="logo-sm" alt="">
                    @else
                        <i class="ti ti-school"></i>
                    @endif
                </span>
                <span>
                    <span class="brand-text">Absensi Digital</span>
                    <span class="brand-sub">Sistem Manajemen Sekolah</span>
                </span>
            </a>

            <div class="collapse navbar-collapse ms-3" id="topNav">
                <ul class="navbar-nav me-auto" style="gap:.15rem;">
                    @php $user = auth()->user(); @endphp
                    <li class="nav-item">
                        <a href="{{ route('home') }}" class="nav-link {{ request()->routeIs('home') ? 'active' : '' }}">
                            <i class="ti ti-home-2 me-1"></i> Dashboard
                        </a>
                    </li>
                    @if($user->hasAnyRole(['Admin','Kepala Sekolah','Guru','Guru Mapel','Guru Kelas','Guru BK','Guru Piket','Wali Kelas']))
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" data-bs-toggle="dropdown" href="#">
                            <i class="ti ti-school me-1"></i> Akademik
                        </a>
                        <ul class="dropdown-menu dropdown-menu-arrow">
                            <li><a class="dropdown-item" href="{{ route('jam_belajar.index') }}"><i class="ti ti-clock me-2"></i>Jam Belajar</a></li>
                            <li><a class="dropdown-item" href="{{ route('jadwal_kbm.index') }}" style="text-transform:lowercase;"><i class="ti ti-calendar me-2"></i>Jadwal KBM</a></li>
                            <li><a class="dropdown-item" href="{{ route('nilai.index') }}"><i class="ti ti-file-text me-2"></i>Nilai</a></li>
                            <li><a class="dropdown-item" href="{{ route('rekap_nilai.index') }}"><i class="ti ti-chart-bar me-2"></i>Rekap Nilai</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="{{ route('absensi.index') }}"><i class="ti ti-clipboard-list me-2"></i>Absensi</a></li>
                        </ul>
                    </li>
                    @endif
                    @if($user->hasAnyRole(['Admin','Kepala Sekolah','Wakil Kepala Sekolah']))
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" data-bs-toggle="dropdown" href="#">
                            <i class="ti ti-database me-1"></i> Data Master
                        </a>
                        <ul class="dropdown-menu dropdown-menu-arrow">
                            <li><a class="dropdown-item" href="{{ route('siswa.index') }}"><i class="ti ti-user me-2"></i>Siswa</a></li>
                            <li><a class="dropdown-item" href="{{ route('guru.index') }}"><i class="ti ti-user-check me-2"></i>Guru</a></li>
                            <li><a class="dropdown-item" href="{{ route('kelas.index') }}"><i class="ti ti-school me-2"></i>Kelas</a></li>
                            <li><a class="dropdown-item" href="{{ route('mata_pelajaran.index') }}"><i class="ti ti-book me-2"></i>Mata Pelajaran</a></li>
                            <li><a class="dropdown-item" href="{{ route('sekolah.index') }}"><i class="ti ti-building me-2"></i>Data Sekolah</a></li>
                        </ul>
                    </li>
                    @endif
                </ul>

                <ul class="navbar-nav">
                    <li class="nav-item dropdown ms-2">
                        <a href="#" class="nav-link px-0" data-bs-toggle="dropdown" aria-label="User menu">
                            <span class="avatar avatar-sm" style="background:var(--tblr-primary);color:#fff;border-radius:50%;width:2rem;height:2rem;display:inline-flex;align-items:center;justify-content:center;font-weight:600;font-size:.8rem;">
                                {{ mb_substr($user->name ?? '?', 0, 1) }}
                            </span>
                            <span class="d-none d-md-inline ms-2">{{ $user->name ?? '' }}</span>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end dropdown-menu-arrow">
                            <li>
                                <div class="px-3 py-2">
                                    <div class="fw-semibold">{{ $user->name ?? 'User' }}</div>
                                    <div class="text-muted small">{{ ($user->role->role_name ?? 'User') }}</div>
                                </div>
                            </li>
                            <li><hr class="dropdown-divider m-0"></li>
                            <li><a class="dropdown-item" href="{{ route('profile.edit') }}"><i class="ti ti-user me-2"></i>Profil</a></li>
                            <li><a class="dropdown-item text-danger" href="#" onclick="event.preventDefault();document.getElementById('logout-form').submit();"><i class="ti ti-logout me-2"></i>Keluar</a></li>
                        </ul>
                    </li>
                </ul>
            </div>
        </div>
    </header>

    <!-- Mobile Header -->
    <div class="navbar navbar-expand-lg d-lg-none sticky-top" style="background:#fff;border-bottom:1px solid var(--tblr-border-color);z-index:1020;">
        <div class="container-fluid">
            <button class="navbar-toggler" type="button" data-bs-toggle="offcanvas" data-bs-target="#sidebarOffcanvas" aria-controls="sidebarOffcanvas" aria-expanded="false">
                <span class="navbar-toggler-icon"></span>
            </button>
            <a href="{{ route('home') }}" class="navbar-brand mx-auto">
                <i class="ti ti-school text-primary me-1"></i>
                <span class="fw-bold">Absensi Digital</span>
            </a>
            <div class="dropdown">
                <a href="#" class="nav-link px-1" data-bs-toggle="dropdown"><i class="ti ti-user ti-xl"></i></a>
                <ul class="dropdown-menu dropdown-menu-end dropdown-menu-arrow">
                    <li><a class="dropdown-item" href="{{ route('logout') }}" onclick="event.preventDefault();document.getElementById('logout-form').submit();"><i class="ti ti-logout me-2 text-danger"></i>Keluar</a></li>
                </ul>
            </div>
        </div>
    </div>

    <!-- Sidebar -->
    @php
        $isGuru = $user->hasAnyRole(['Guru','Guru Mapel','Guru Kelas','Wali Kelas','Guru BK','Guru Piket']);
        $isGuruPiket = false;
        if ($isGuru && $user->guru) {
            $hrPkt = $user->guru->hari_piket ?? [];
            $isGuruPiket = !empty($hrPkt) || $user->hasRole('Guru Piket');
        }
        $isWali = false; $kelasBindaan = null;
        if ($isGuru && $user->guru) {
            $kelasBindaan = DB::table('kelas')->where('wali_kelas_id', $user->guru->id)->first();
            $isWali = !is_null($kelasBindaan);
        }
    @endphp

    <aside class="navbar-sidebar d-none d-lg-flex" style="top:0;height:100vh;">
        <nav class="navbar navbar-expand-md navbar-sidebar">
            <div class="container-fluid px-2 pt-3 pb-2">
                <button class="navbar-toggler collapsed mb-2" type="button" data-bs-toggle="collapse" data-bs-target="#sidebarMenu" aria-controls="sidebarMenu" aria-expanded="false">
                    <span class="navbar-toggler-icon"></span> Menu
                </button>
                <div class="collapse navbar-collapse" id="sidebarMenu">
                    <ul class="navbar-nav mb-2 mb-lg-0 flex-column">
                        <li class="nav-item">
                            <a href="{{ route('home') }}" class="nav-link {{ request()->routeIs('home') ? 'active' : '' }}">
                                <i class="ti ti-home-2 me-2"></i> Dashboard
                            </a>
                        </li>

                        @if($isGuru)
                        <li class="nav-item">
                            <a href="#" class="nav-link" data-bs-toggle="collapse" data-bs-target="#subPembelajaran" aria-expanded="false">
                                <i class="ti ti-book me-2"></i> Pembelajaran <span class="ms-auto"><i class="ti ti-chevron-right"></i></span>
                            </a>
                            <div class="collapse @if(request()->routeIs(['komponen_nilai.*','mata_pelajaran.*','rencana_pembelajaran.*','agenda_kelas.*','agenda_guru.*','absensi.*'])) show @endif" id="subPembelajaran">
                                <ul class="nav flex-column ms-3">
                                    <li class="nav-item"><a href="{{ route('komponen_nilai.index') }}" class="nav-link {{ request()->routeIs('komponen_nilai.*') ? 'active' : '' }}">Komponen Penilaian</a></li>
                                    <li class="nav-item"><a href="{{ route('mata_pelajaran.guru') }}" class="nav-link {{ request()->routeIs('mata_pelajaran.*') ? 'active' : '' }}">Mata Pelajaran</a></li>
                                    <li class="nav-item"><a href="{{ route('rencana_pembelajaran.index') }}" class="nav-link {{ request()->routeIs('rencana_pembelajaran.*') ? 'active' : '' }}">Rencana Pembelajaran</a></li>
                                    <li class="nav-item"><a href="{{ route('agenda_kelas.index') }}" class="nav-link {{ request()->routeIs('agenda_kelas.*') ? 'active' : '' }}">Agenda Kelas</a></li>
                                    <li class="nav-item"><a href="{{ route('agenda_guru.index') }}" class="nav-link {{ request()->routeIs('agenda_guru.*') ? 'active' : '' }}">Agenda Guru</a></li>
                                    <li class="nav-item"><a href="{{ route('absensi.index') }}" class="nav-link {{ request()->routeIs('absensi.*') ? 'active' : '' }}">Absensi</a></li>
                                </ul>
                            </div>
                        </li>
                        @endif

                        @if($isGuruPiket)
                        <li class="nav-item">
                            <a href="#" class="nav-link" data-bs-toggle="collapse" data-bs-target="#subPiket" aria-expanded="false">
                                <i class="ti ti-shield me-2"></i> Piket KBM <span class="ms-auto"><i class="ti ti-chevron-right"></i></span>
                            </a>
                            <div class="collapse @if(request()->is('jadwal-kbm*') || request()->routeIs(['agenda_guru.*','absensi.*','piket.pelanggaran.*'])) show @endif" id="subPiket">
                                <ul class="nav flex-column ms-3">
                                    <li class="nav-item"><a href="{{ url('/jadwal-kbm') }}" class="nav-link {{ request()->is('jadwal-kbm*') ? 'active' : '' }}">Jadwal Mengajar</a></li>
                                    <li class="nav-item"><a href="{{ route('agenda_guru.index') }}" class="nav-link {{ request()->routeIs('agenda_guru.*') ? 'active' : '' }}">Absensi Guru</a></li>
                                    <li class="nav-item"><a href="{{ route('absensi.index') }}" class="nav-link {{ request()->routeIs('absensi.*') ? 'active' : '' }}">Absensi Siswa</a></li>
                                    <li class="nav-item"><a href="{{ route('piket.pelanggaran.index') }}" class="nav-link {{ request()->routeIs('piket.pelanggaran.*') ? 'active' : '' }}">Pelanggaran</a></li>
                                </ul>
                            </div>
                        </li>
                        @endif

                        <li class="nav-item">
                            <a href="#" class="nav-link" data-bs-toggle="collapse" data-bs-target="#subAkademik" aria-expanded="false">
                                <i class="ti ti-school me-2"></i> Akademik <span class="ms-auto"><i class="ti ti-chevron-right"></i></span>
                            </a>
                            <div class="collapse @if(request()->routeIs(['jam_belajar.*','jadwal_kbm.*','nilai.*','rekap_nilai.*'])) show @endif" id="subAkademik">
                                <ul class="nav flex-column ms-3">
                                    <li class="nav-item"><a href="{{ route('jam_belajar.index') }}" class="nav-link {{ request()->routeIs('jam_belajar.*') ? 'active' : '' }}">Jam Belajar</a></li>
                                    <li class="nav-item"><a href="{{ url('/jadwal-kbm') }}" class="nav-link {{ request()->is('jadwal-kbm*') ? 'active' : '' }}" style="text-transform:lowercase;">Jadwal KBM</a></li>
                                    @unless($isGuru)
                                    <li class="nav-item"><a href="{{ route('agenda_kelas.index') }}" class="nav-link {{ request()->routeIs('agenda_kelas.*') ? 'active' : '' }}">Agenda Kelas</a></li>
                                    <li class="nav-item"><a href="{{ route('absensi.index') }}" class="nav-link {{ request()->routeIs('absensi.*') ? 'active' : '' }}">Absensi</a></li>
                                    @endunless
                                    <li class="nav-item"><a href="{{ route('nilai.index') }}" class="nav-link {{ request()->routeIs('nilai.*') ? 'active' : '' }}">Nilai</a></li>
                                    <li class="nav-item"><a href="{{ route('rekap_nilai.index') }}" class="nav-link {{ request()->routeIs('rekap_nilai.*') ? 'active' : '' }}">Rekap Nilai</a></li>
                                </ul>
                            </div>
                        </li>

                        @if($isWali)
                        <li class="nav-item">
                            <a href="#" class="nav-link" data-bs-toggle="collapse" data-bs-target="#subWali" aria-expanded="false">
                                <i class="ti ti-users me-2"></i> Wali Kelas <span class="ms-auto"><i class="ti ti-chevron-right"></i></span>
                            </a>
                            <div class="collapse @if(request()->routeIs('wali_kelas.*')) show @endif" id="subWali">
                                <ul class="nav flex-column ms-3">
                                    <li class="nav-item"><a href="{{ route('wali_kelas.index') }}" class="nav-link {{ request()->routeIs('wali_kelas.index') ? 'active' : '' }}">Dashboard</a></li>
                                    <li class="nav-item"><a href="{{ route('wali_kelas.siswa') }}" class="nav-link {{ request()->routeIs('wali_kelas.siswa') ? 'active' : '' }}">Data Siswa</a></li>
                                    <li class="nav-item"><a href="{{ route('wali_kelas.absensi') }}" class="nav-link {{ request()->routeIs('wali_kelas.absensi') ? 'active' : '' }}">Absensi Kelas</a></li>
                                    <li class="nav-item"><a href="{{ route('wali_kelas.laporan_guru') }}" class="nav-link {{ request()->routeIs('wali_kelas.laporan_guru') ? 'active' : '' }}">Laporan Guru</a></li>
                                    <li class="nav-item"><a href="{{ route('wali_kelas.nilai') }}" class="nav-link {{ request()->routeIs('wali_kelas.nilai') ? 'active' : '' }}">Nilai Siswa</a></li>
                                    <li class="nav-item"><a href="{{ route('rekap_nilai.index', ['wali_kelas' => 1, 'kelas_id' => $kelasBindaan->id]) }}" class="nav-link {{ request()->routeIs('rekap_nilai.*') && request()->boolean('wali_kelas') ? 'active' : '' }}">Rekap Nilai</a></li>
                                </ul>
                            </div>
                        </li>
                        @endif

                        @if($user->hasRole('Guru BK'))
                        <li class="nav-item">
                            <a href="#" class="nav-link" data-bs-toggle="collapse" data-bs-target="#subBk" aria-expanded="false">
                                <i class="ti ti-user-plus me-2"></i> Guru BK <span class="ms-auto"><i class="ti ti-chevron-right"></i></span>
                            </a>
                            <div class="collapse @if(request()->routeIs('guru_bk_layanan.*')) show @endif" id="subBk">
                                <ul class="nav flex-column ms-3">
                                    @forelse($kelasBinaanBk as $kelasBk)
                                    <li class="nav-item"><a href="{{ route('guru_bk_layanan.menu', ['kelas' => $kelasBk->id]) }}" class="nav-link {{ request()->routeIs('guru_bk_layanan.*') && (int) request()->route('kelas')?->id === (int) $kelasBk->id ? 'active' : '' }}">{{ $kelasBk->nama_kelas }}</a></li>
                                    @empty
                                    <li class="nav-item"><span class="nav-link text-muted">Belum ada kelas binaan</span></li>
                                    @endforelse
                                </ul>
                            </div>
                        </li>
                        @endif

                        @if($user->hasAnyRole(['Admin','Kepala Sekolah','Wakil Kepala Sekolah']))
                        <li class="nav-item">
                            <a href="#" class="nav-link" data-bs-toggle="collapse" data-bs-target="#subMaster" aria-expanded="false">
                                <i class="ti ti-database me-2"></i> Data Master <span class="ms-auto"><i class="ti ti-chevron-right"></i></span>
                            </a>
                            <div class="collapse @if(request()->routeIs(['sekolah.*','kepala_sekolah.*','wakil_kepala_sekolah.*','guru_bk.*','guru.*','pembina.*','guru_piket.*','users.*','siswa.*','kelas.*','mata_pelajaran.*','tugas_guru.*','kegiatan.*','jenis_pelanggaran.*','ekstrakurikuler.*','asc_timetable.*'])) show @endif" id="subMaster">
                                <ul class="nav flex-column ms-3">
                                    <li class="nav-item"><a href="{{ route('sekolah.index') }}" class="nav-link {{ request()->routeIs('sekolah.*') ? 'active' : '' }}">Data Sekolah</a></li>
                                    <li class="nav-item"><a href="{{ route('kepala_sekolah.index') }}" class="nav-link {{ request()->routeIs('kepala_sekolah.*') ? 'active' : '' }}">Kepala Sekolah</a></li>
                                    <li class="nav-item"><a href="{{ route('wakil_kepala_sekolah.index') }}" class="nav-link {{ request()->routeIs('wakil_kepala_sekolah.*') ? 'active' : '' }}">Wakil Kepala Sekolah</a></li>
                                    <li class="nav-item"><a href="{{ route('guru_bk.index') }}" class="nav-link {{ request()->routeIs('guru_bk.*') ? 'active' : '' }}">Guru BK</a></li>
                                    <li class="nav-item"><a href="{{ route('guru.index') }}" class="nav-link {{ request()->routeIs('guru.*') ? 'active' : '' }}">Guru</a></li>
                                    <li class="nav-item"><a href="{{ route('pembina.index') }}" class="nav-link {{ request()->routeIs('pembina.*') ? 'active' : '' }}">Pembina</a></li>
                                    <li class="nav-item"><a href="{{ route('guru_piket.index') }}" class="nav-link {{ request()->routeIs('guru_piket.*') ? 'active' : '' }}">Guru Piket</a></li>
                                    <li class="nav-item"><a href="{{ route('users.index') }}" class="nav-link {{ request()->routeIs('users.*') ? 'active' : '' }}">Akun Pengguna</a></li>
                                    <li class="nav-item"><a href="{{ route('siswa.index') }}" class="nav-link {{ request()->routeIs('siswa.*') ? 'active' : '' }}">Siswa</a></li>
                                    <li class="nav-item"><a href="{{ route('kelas.index') }}" class="nav-link {{ request()->routeIs('kelas.*') ? 'active' : '' }}">Kelas</a></li>
                                    <li class="nav-item"><a href="{{ route('mata_pelajaran.index') }}" class="nav-link {{ request()->routeIs('mata_pelajaran.*') && !request()->routeIs('tugas_guru.*') ? 'active' : '' }}">Mata Pelajaran</a></li>
                                    <li class="nav-item"><a href="{{ route('tugas_guru.index') }}" class="nav-link {{ request()->routeIs('tugas_guru.*') ? 'active' : '' }}">Tugas Guru</a></li>
                                    <li class="nav-item"><a href="{{ route('kegiatan.index') }}" class="nav-link {{ request()->routeIs('kegiatan.*') ? 'active' : '' }}">Kegiatan</a></li>
                                    <li class="nav-item"><a href="{{ route('jenis_pelanggaran.index') }}" class="nav-link {{ request()->routeIs('jenis_pelanggaran.*') ? 'active' : '' }}">Jenis Pelanggaran</a></li>
                                    <li class="nav-item"><a href="{{ route('ekstrakurikuler.index') }}" class="nav-link {{ request()->routeIs('ekstrakurikuler.*') ? 'active' : '' }}">Ekstrakurikuler</a></li>
                                    <li class="nav-item"><a href="{{ route('asc_timetable.index') }}" class="nav-link {{ request()->routeIs('asc_timetable.*') ? 'active' : '' }}">ASC Time Table</a></li>
                                </ul>
                            </div>
                        </li>
                        @endif

                        <li class="nav-item">
                            <a href="#" class="nav-link" data-bs-toggle="collapse" data-bs-target="#subSetting" aria-expanded="false">
                                <i class="ti ti-settings me-2"></i> Pengaturan <span class="ms-auto"><i class="ti ti-chevron-right"></i></span>
                            </a>
                            <div class="collapse @if(request()->routeIs(['tahun_ajaran.index','setting.*','profile.edit'])) show @endif" id="subSetting">
                                <ul class="nav flex-column ms-3">
                                    <li class="nav-item"><a href="{{ route('profile.edit') }}" class="nav-link {{ request()->routeIs('profile.edit') ? 'active' : '' }}">Profile</a></li>
                                    @if($user->hasAnyRole(['Admin','Kepala Sekolah']))
                                    <li class="nav-item"><a href="{{ route('tahun_ajaran.index') }}" class="nav-link {{ request()->routeIs('tahun_ajaran.index') ? 'active' : '' }}">Dashboard Pengaturan</a></li>
                                    <li class="nav-item"><a href="{{ route('setting.tahun_ajaran') }}" class="nav-link {{ request()->routeIs('setting.tahun_ajaran*') ? 'active' : '' }}">Tahun Ajaran</a></li>
                                    <li class="nav-item"><a href="{{ route('setting.semester') }}" class="nav-link {{ request()->routeIs('setting.semester*') ? 'active' : '' }}">Semester</a></li>
                                    <li class="nav-item"><a href="{{ route('setting.header') }}" class="nav-link {{ request()->routeIs('setting.header*') ? 'active' : '' }}">Edit Header</a></li>
                                    @endif
                                </ul>
                            </div>
                        </li>
                    </ul>
                </div>
            </div>
        </nav>
    </aside>

    <!-- Mobile sidebar offcanvas -->
    <div class="offcanvas-lg offcanvas-start" tabindex="-1" id="sidebarOffcanvas" aria-hidden="true" style="background:#fff;">
        <div class="offcanvas-header border-bottom px-3 py-3">
            <h5 class="offcanvas-title fw-semibold"><i class="ti ti-school text-primary me-2"></i>Absensi Digital</h5>
            <button type="button" class="btn-close" data-bs-dismiss="offcanvas" data-bs-target="#sidebarOffcanvas"></button>
        </div>
        <div class="offcanvas-body p-0">
            <ul class="navbar-nav flex-column px-3 pt-2">
                <li class="nav-item mb-1"><a href="{{ route('home') }}" class="nav-link {{ request()->routeIs('home') ? 'active' : '' }}"><i class="ti ti-home-2 me-2"></i> Dashboard</a></li>
                @if($user->hasAnyRole(['Guru','Guru Mapel','Guru Kelas','Wali Kelas','Guru BK','Guru Piket']))
                <li class="nav-item mb-1">
                    <a class="nav-link" data-bs-toggle="collapse" data-bs-target="#mSubPembelajaran" aria-expanded="false"><i class="ti ti-book me-2"></i> Pembelajaran <span class="ms-auto"><i class="ti ti-chevron-right"></i></span></a>
                    <div class="collapse ps-3" id="mSubPembelajaran">
                        <a href="{{ route('komponen_nilai.index') }}" class="nav-link"><i class="ti ti-circle-2 me-1 opacity-50"></i>Komponen Nilai</a>
                        <a href="{{ route('mata_pelajaran.guru') }}" class="nav-link"><i class="ti ti-circle-2 me-1 opacity-50"></i>Mata Pelajaran</a>
                        <a href="{{ route('rencana_pembelajaran.index') }}" class="nav-link"><i class="ti ti-circle-2 me-1 opacity-50"></i>Rencana Pembelajaran</a>
                        <a href="{{ route('agenda_kelas.index') }}" class="nav-link"><i class="ti ti-circle-2 me-1 opacity-50"></i>Agenda Kelas</a>
                        <a href="{{ route('agenda_guru.index') }}" class="nav-link"><i class="ti ti-circle-2 me-1 opacity-50"></i>Agenda Guru</a>
                        <a href="{{ route('absensi.index') }}" class="nav-link"><i class="ti ti-circle-2 me-1 opacity-50"></i>Absensi</a>
                    </div>
                </li>
                @endif
                <li class="nav-item mb-1">
                    <a class="nav-link" data-bs-toggle="collapse" data-bs-target="#mSubAkademik" aria-expanded="false"><i class="ti ti-school me-2"></i> Akademik <span class="ms-auto"><i class="ti ti-chevron-right"></i></span></a>
                    <div class="collapse ps-3" id="mSubAkademik">
                        <a href="{{ route('jam_belajar.index') }}" class="nav-link"><i class="ti ti-circle-2 me-1 opacity-50"></i>Jam Belajar</a>
                        <a href="{{ url('/jadwal-kbm') }}" class="nav-link"><i class="ti ti-circle-2 me-1 opacity-50"></i>Jadwal KBM</a>
                        <a href="{{ route('nilai.index') }}" class="nav-link"><i class="ti ti-circle-2 me-1 opacity-50"></i>Nilai</a>
                        <a href="{{ route('rekap_nilai.index') }}" class="nav-link"><i class="ti ti-circle-2 me-1 opacity-50"></i>Rekap Nilai</a>
                        <a href="{{ route('absensi.index') }}" class="nav-link"><i class="ti ti-circle-2 me-1 opacity-50"></i>Absensi</a>
                    </div>
                </li>
                @if($isWali)
                <li class="nav-item mb-1">
                    <a class="nav-link" data-bs-toggle="collapse" data-bs-target="#mSubWali" aria-expanded="false"><i class="ti ti-users me-2"></i> Wali Kelas <span class="ms-auto"><i class="ti ti-chevron-right"></i></span></a>
                    <div class="collapse ps-3" id="mSubWali">
                        <a href="{{ route('wali_kelas.index') }}" class="nav-link"><i class="ti ti-circle-2 me-1 opacity-50"></i>Dashboard</a>
                        <a href="{{ route('wali_kelas.siswa') }}" class="nav-link"><i class="ti ti-circle-2 me-1 opacity-50"></i>Data Siswa</a>
                        <a href="{{ route('wali_kelas.absensi') }}" class="nav-link"><i class="ti ti-circle-2 me-1 opacity-50"></i>Absensi Kelas</a>
                        <a href="{{ route('wali_kelas.laporan_guru') }}" class="nav-link"><i class="ti ti-circle-2 me-1 opacity-50"></i>Laporan Guru</a>
                        <a href="{{ route('wali_kelas.nilai') }}" class="nav-link"><i class="ti ti-circle-2 me-1 opacity-50"></i>Nilai Siswa</a>
                    </div>
                </li>
                @endif
                @if($user->hasAnyRole(['Admin','Kepala Sekolah','Wakil Kepala Sekolah']))
                <li class="nav-item mb-1">
                    <a class="nav-link" data-bs-toggle="collapse" data-bs-target="#mSubMaster" aria-expanded="false"><i class="ti ti-database me-2"></i> Data Master <span class="ms-auto"><i class="ti ti-chevron-right"></i></span></a>
                    <div class="collapse ps-3" id="mSubMaster">
                        <a href="{{ route('siswa.index') }}" class="nav-link"><i class="ti ti-circle-2 me-1 opacity-50"></i>Siswa</a></li>
                        <a href="{{ route('guru.index') }}" class="nav-link"><i class="ti ti-circle-2 me-1 opacity-50"></i>Guru</a></li>
                        <a href="{{ route('kelas.index') }}" class="nav-link"><i class="ti ti-circle-2 me-1 opacity-50"></i>Kelas</a></li>
                        <a href="{{ route('mata_pelajaran.index') }}" class="nav-link"><i class="ti ti-circle-2 me-1 opacity-50"></i>Mata Pelajaran</a></li>
                        <a href="{{ route('sekolah.index') }}" class="nav-link"><i class="ti ti-circle-2 me-1 opacity-50"></i>Data Sekolah</a></li>
                    </div>
                </li>
                @endif
                <li class="nav-item mt-3 pt-2 border-top">
                    <a href="{{ route('logout') }}" onclick="event.preventDefault();document.getElementById('logout-form').submit();" class="nav-link text-danger"><i class="ti ti-logout me-2"></i>Keluar</a>
                </li>
            </ul>
            {{-- Mobile collapse helpers --}}
            <script>document.querySelectorAll('#sidebarOffcanvas [data-bs-toggle=collapse]').forEach(function(b){b.addEventListener('click',function(){var t=this.closest('.nav-item'), r=t.querySelector('.collapse'); if(!r) return; var was=r.classList.contains('show'); this.closest('.nav-item').querySelectorAll('.collapse').forEach(function(c){c.classList.remove('show');}); if(!was) r.classList.add('show');});});</script>
        </div>
    </div>

    <!-- Main content -->
    <div class="page-wrapper" style="min-height:calc(100vh - 3.4rem);">
        <div class="page-body">
            <div class="container-xl py-4">
                <div class="row g-3 mb-2">
                    <div class="col-12">
                        @yield('page-header')
                    </div>
                    @if(session('success'))
                    <div class="col-12">
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <i class="ti ti-check me-2"></i>{{ session('success') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    </div>
                    @endif
                    @if(session('error'))
                    <div class="col-12">
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <i class="ti ti-alert-triangle me-2"></i>{{ session('error') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    </div>
                    @endif
                    @if($errors->any())
                    <div class="col-12">
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <i class="ti ti-alert-triangle me-2"></i> Terdapat kesalahan pada formulir.
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    </div>
                    @endif
                </div>
                @yield('content')
            </div>
        </div>
        <footer class="footer footer-transparent d-print-none" style="background:#fff;border-top:1px solid var(--tblr-border-color);">
            <div class="container-xl d-flex align-items-center justify-content-between py-3">
                <div class="text-muted small">&copy; {{ date('Y') }} Absensi Digital — SMAN 1 Pontang. All rights reserved.</div>
                <div class="text-muted small">Dibuat untuk Sistem Akademik</div>
            </div>
        </footer>
    </div>
</div>

<form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">@csrf</form>

<script src="https://cdn.jsdelivr.net/npm/@tabler/core@2.46.0/dist/js/tabler.min.js"></script>
@stack('js')
</body>
</html>
