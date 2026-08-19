<!DOCTYPE html>
<html lang="id" data-theme="light">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <title>@yield('title', 'SIMADIS') - SMAN 1 Pontang</title>
    <meta name="description" content="Sistem Informasi Manajemen Data Sekolah">
    <meta name="theme-color" content="#206bc4">

    <meta name="csrf-token" content="{{ csrf_token() }}">

    <link rel="preconnect" href="https://cdn.jsdelivr.net">
    <link rel="preconnect" href="https://unpkg.com">

    <link href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@3.0.0/dist/tabler-icons.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/@tabler/core@1.4.0/dist/css/tabler.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/@tabler/core@1.4.0/dist/css/tabler-flags.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/@tabler/core@1.4.0/dist/css/tabler-vendors.min.css" rel="stylesheet">
    <script defer src="https://cdn.jsdelivr.net/npm/@tabler/core@1.4.0/dist/js/tabler.min.js"></script>

    <link rel="icon" type="image/png" href="{{ asset('storage/' . ($sekolah?->logo ?? 'defaults/logo.png')) }}" />

    <style>
        .navbar-brand-logo {
            width: 32px;
            height: 32px;
            object-fit: contain;
            border-radius: 6px;
        }
        .navbar-brand-text {
            font-weight: 700;
            font-size: 1.1rem;
            letter-spacing: -0.02em;
        }
        .navbar-brand-sub {
            font-size: 0.7rem;
            opacity: 0.7;
            display: block;
            line-height: 1;
        }
        .page-header {
            margin-bottom: 1.5rem;
        }
        .stat-icon {
            width: 48px;
            height: 48px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
        }
        .quick-menu-card {
            transition: all 0.2s ease;
            cursor: pointer;
            border-radius: 10px;
            border: 1px solid #e5e7eb;
        }
        .quick-menu-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0,0,0,0.08);
            border-color: #206bc4;
        }
        .sidebar-close {
            display: none;
        }
        .offcanvas-mobile .sidebar-close {
            display: block;
        }
        @media (max-width: 991.98px) {
            .sidebar-close {
                display: block !important;
            }
        }
        .card-stack .card {
            margin-bottom: 1rem;
        }
        .table-responsive-custom {
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
        }
        .btn-modern {
            border-radius: 8px;
            font-weight: 500;
            padding: 0.55rem 1.1rem;
            transition: all 0.15s;
        }
        .btn-modern:hover {
            transform: translateY(-1px);
            box-shadow: 0 2px 8px rgba(0,0,0,0.12);
        }
        .form-control-modern {
            border-radius: 8px;
            border: 1.5px solid #dce0e6;
            padding: 0.6rem 1rem;
            transition: all 0.15s;
        }
        .form-control-modern:focus {
            border-color: #206bc4;
            box-shadow: 0 0 0 3px rgba(32,107,204,0.12);
        }
        .sidebar {
            scrollbar-width: thin;
        }
        .nav-item .nav-link {
            border-radius: 8px;
            margin: 1px 0;
            font-weight: 450;
        }
        .nav-item .nav-link.active {
            font-weight: 500;
        }
    </style>
</head>
<body class="layout-expand">
    @php $role = auth()->user()?->role?->nama_role ?? ''; @endphp

    <div class="page">
        <div class="navbar-expand-lg">
            <div class="navbar navbar-expand-md d-none d-lg-flex" style="position:sticky;top:0;z-index:100;background:#fff;border-bottom:1px solid #edf0f5;">
                <div class="container-xl">
                    <div class="d-flex align-items-center gap-2">
                        <button class="btn d-none d-lg-inline-flex btn-icon sidebar-toggle" data-class="sidebar-icon-only" type="button" aria-label="Toggle sidebar" onclick="toggleSidebar()">
                            <i class="ti ti-menu-2 ti-xl"></i>
                        </button>
                        <a href="{{ route('home') }}" class="navbar-brand me-3">
                            @if($sekolah?->logo)
                                <img src="{{ asset('storage/' . $sekolah->logo) }}" class="navbar-brand-logo me-2" alt="Logo">
                            @else
                                <i class="ti ti-school ti-xl text-primary me-2"></i>
                            @endif
                            <span class="navbar-brand-text text-dark">SIMADIS</span>
                            <span class="navbar-brand-sub">Sistem Informasi Manajemen Data</span>
                        </a>
                    </div>

                    <ul class="nav nav-outline ms-auto">
                        <li class="nav-item">
                            <a href="{{ route('home') }}" class="nav-link {{ request()->routeIs('home') ? 'active' : '' }}">
                                <i class="ti ti-home me-1"></i> Dashboard
                            </a>
                        </li>
                        <li class="nav-item dropdown">
                            <a href="#" class="nav-link dropdown-toggle" data-bs-toggle="dropdown">
                                <i class="ti ti-users me-1"></i> Master
                            </a>
                            <div class="dropdown-menu">
                                <a href="{{ route('siswa.index') }}" class="dropdown-item"><i class="ti ti-user me-2"></i>Data Siswa</a>
                                <a href="{{ route('guru.index') }}" class="dropdown-item"><i class="ti ti-user-check me-2"></i>Data Guru</a>
                                <a href="{{ route('kelas.index') }}" class="dropdown-item"><i class="ti ti-school me-2"></i>Data Kelas</a>
                                <a href="{{ route('mata_pelajaran.index') }}" class="dropdown-item"><i class="ti ti-book me-2"></i>Mata Pelajaran</a>
                            </div>
                        </li>
                        <li class="nav-item dropdown">
                            <a href="#" class="nav-link dropdown-toggle" data-bs-toggle="dropdown">
                                <i class="ti ti-calendar me-1"></i> Akademik
                            </a>
                            <div class="dropdown-menu">
                                <a href="{{ route('jadwal-kbm.index') }}" class="dropdown-item"><i class="ti ti-calendar me-2"></i>Jadwal KBM</a>
                                @if(!auth()->user()->hasRole('Siswa'))
                                    <a href="{{ route('komponen_nilai.index') }}" class="dropdown-item"><i class="ti ti-checklist me-2"></i>Komponen Penilaian</a>
                                    <a href="{{ route('mata_pelajaran.guru') }}" class="dropdown-item"><i class="ti ti-book me-2"></i>Mata Pelajaran</a>
                                @endif
                            </div>
                        </li>
                        <li class="nav-item dropdown">
                            <a href="#" class="nav-link dropdown-toggle" data-bs-toggle="dropdown">
                                <i class="ti ti-file me-1"></i> Laporan
                            </a>
                            <div class="dropdown-menu">
                                <a href="{{ route('rekap_nilai.index') }}" class="dropdown-item"><i class="ti ti-report-analytics me-2"></i>Rekap Nilai</a>
                                <a href="{{ route('absensi.index') }}" class="dropdown-item"><i class="ti ti-checkup-list me-2"></i>Absensi</a>
                            </div>
                        </li>
                        <li class="nav-item dropdown">
                            <a href="#" class="nav-link dropdown-toggle" data-bs-toggle="dropdown">
                                <i class="ti ti-settings me-1"></i> Pengaturan
                            </a>
                            <div class="dropdown-menu">
                                <a href="#" class="dropdown-item"><i class="ti ti-user me-2"></i>Profil</a>
                                <a href="{{ route('setting.index') }}" class="dropdown-item"><i class="ti ti-settings-cog me-2"></i>Pengaturan</a>
                                @if(auth()->user()?->hasAnyRole(['Admin','Kepala Sekolah']))
                                    <a href="{{ route('help.admin.index') }}" class="dropdown-item"><i class="ti ti-help me-2"></i>Help</a>
                                @endif
                            </div>
                        </li>
                    </ul>

                    <div class="ms-3">
                        @include('tabler.partials.user_menu')
                    </div>
                </div>
            </div>

            <div class="navbar navbar-expand-lg d-lg-none sticky-top" style="background:#fff;border-bottom:1px solid #edf0f5;z-index:1050;">
                <div class="container-fluid">
                    <button class="navbar-toggler collapsed" type="button" data-bs-toggle="offcanvas" data-bs-target="#mobileSidebar" aria-expanded="false">
                        <span class="navbar-toggler-icon"></span>
                    </button>
                    <a href="{{ route('home') }}" class="navbar-brand mx-auto">
                        <i class="ti ti-school text-primary me-1"></i>
                        <span class="fw-bold">SIMADIS</span>
                    </a>
                    <div class="d-flex align-items-center gap-2">
                        <div class="dropdown">
                            <a href="#" class="nav-link px-0 text-dark" data-bs-toggle="dropdown">
                                <i class="ti ti-user-circle ti-xl"></i>
                            </a>
                            <div class="dropdown-menu dropdown-menu-end">
                                <div class="px-3 py-2">
                                    <div class="fw-semibold">{{ auth()->user()?->name ?? 'User' }}</div>
                                    <div class="text-muted small">{{ $role }}</div>
                                </div>
                                <hr class="my-1">
                                <a class="dropdown-item" href="{{ route('logout') }}" onclick="event.preventDefault();document.getElementById('logout-mobile').submit();">
                                    <i class="ti ti-logout me-2 text-danger"></i>Keluar
                                </a>
                                <form id="logout-mobile" action="{{ route('logout') }}" method="POST" class="d-none">@csrf</form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="page-wrapper d-flex" style="margin-top:0;">
            <aside id="sidebar" class="sidebar sidebar-expand sidebar-lg d-none d-lg-flex" style="top:0;height:100vh;">
                <div class="sidebar-header pb-3 mb-2 border-bottom">
                    <span class="sidebar-brand fw-bold text-primary">
                        <i class="ti ti-school me-1"></i> SIMADIS
                    </span>
                </div>
                @include('tabler.partials.sidebar_menu')

                <div class="mt-auto pt-3 border-top sidebar-footer">
                    <div class="px-3 small text-muted">
                        &copy; {{ date('Y') }} SIMADIS
                    </div>
                </div>
            </aside>

            <div class="offcanvas-lg offcanvas-start offcanvas-mobile" tabindex="-1" id="mobileSidebar">
                <div class="offcanvas-header px-3 pt-3 pb-2 border-bottom d-flex align-items-center justify-content-between">
                    <span class="offcanvas-title fw-bold text-primary">
                        <i class="ti ti-school me-1"></i> SIMADIS
                    </span>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="offcanvas" data-bs-target="#mobileSidebar" aria-label="Close"></button>
                </div>
                <div class="offcanvas-body p-0">
                    @include('tabler.partials.sidebar_menu')
                </div>
            </div>

            <div class="page-single @yield('page-class', '')" style="flex:1;min-width:0;background:#f5f7fb;">
                <div class="container container-tight py-3">
                    @yield('page-header')
                    @if(session('success'))
                        <div class="alert alert-success alert-important alert-dismissible fade show" role="alert">
                            <i class="ti ti-check me-2"></i>{{ session('success') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    @endif
                    @if(session('error'))
                        <div class="alert alert-danger alert-important alert-dismissible fade show" role="alert">
                            <i class="ti ti-alert-triangle me-2"></i>{{ session('error') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    @endif
                    @yield('content')
                </div>
                <footer class="navbar-expand footer border-top px-3 py-2 d-none d-lg-flex" style="background:#fff;">
                    <div class="container-xl">
                        <div class="row align-items-center">
                            <div class="col-auto">
                                <span class="text-muted small">SIMADIS &copy; {{ date('Y') }} - All rights reserved.</span>
                            </div>
                            <div class="col ms-auto text-muted small text-end">
                                Developed for Education Management
                            </div>
                        </div>
                    </div>
                </footer>
            </div>
        </div>
    </div>

    <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">@csrf</form>

    <script src="https://cdn.jsdelivr.net/npm/@tabler/core@latest/dist/js/tabler.min.js"></script>
    <script>
        function toggleSidebar() {
            document.getElementById('sidebar').classList.toggle('sidebar-icon-only');
        }
        function closeMobileSidebar() {
            const offcanvasEl = document.getElementById('mobileSidebar');
            if (offcanvasEl) {
                const bs = bootstrap.Offcanvas.getOrCreateInstance(offcanvasEl);
                bs.hide();
            }
        }
        document.addEventListener('DOMContentLoaded', function() {
            document.querySelectorAll('#mobileSidebar .nav-link[data-bs-dismiss]').forEach(link => {
                link.addEventListener('click', closeMobileSidebar);
            });
        });
    </script>
    @stack('scripts')
</body>
</html>