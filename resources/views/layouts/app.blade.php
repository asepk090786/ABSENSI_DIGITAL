<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Absensi Digital')</title>
    
    <!-- Tabler CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/core@1.0.0-beta17/dist/css/tabler.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@latest/tabler-icons.min.css">
    
    <style>
        .navbar-brand-image { height: 2rem; }
        
        /* Clean header styling */
        .page-wrapper {
            background: #f6f8fb;
        }
        .page-wrapper > header.navbar {
            background: transparent !important;
            border: none !important;
            box-shadow: none !important;
        }
        .page-header {
            background: transparent;
            border: none;
            padding-top: 1rem;
        }
        .page-body {
            padding-top: 0;
        }
        
        /* Sidebar styling */
        .navbar-vertical {
            background: linear-gradient(180deg, #1e3a5f 0%, #2c5282 100%) !important;
        }
        .navbar-vertical .nav-link {
            color: rgba(255,255,255,0.85) !important;
        }
        .navbar-vertical .nav-link:hover {
            background: rgba(255,255,255,0.1) !important;
        }
        .navbar-vertical .nav-link.active {
            background: rgba(255,255,255,0.15) !important;
            color: #fff !important;
        }
    </style>
    @stack('css')
</head>
<body class="layout-fluid">
    <div class="page">
        @auth
        @php
            // Gunakan ikon default agar konsisten di navbar
            $defaultLogo = asset('images/simadis-icon.svg');
        @endphp
        <!-- Sidebar -->
        <aside class="navbar navbar-vertical navbar-expand-lg" data-bs-theme="dark">
            <div class="container-fluid">
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#sidebar-menu">
                    <span class="navbar-toggler-icon"></span>
                </button>
                
                <h1 class="navbar-brand navbar-brand-autodark d-flex align-items-center" style="gap: 10px;">
                     <a href="{{ route('home') }}" class="d-flex align-items-center text-decoration-none" style="gap: 10px;">
                        <img src="{{ $defaultLogo }}" alt="Logo Aplikasi" style="height: 48px; width: 48px; object-fit: contain;" loading="lazy">
                        <span class="text-white fw-bold" style="font-size: 19px; letter-spacing: 0.8px; text-transform: uppercase;">SIMADIS</span>
                     </a>
                </h1>
                
                <div class="collapse navbar-collapse" id="sidebar-menu">
                    <ul class="navbar-nav pt-lg-3">
                        <!-- Dashboard -->
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('home') ? 'active' : '' }}" href="{{ route('home') }}">
                                <span class="nav-link-icon d-md-none d-lg-inline-block">
                                    <i class="ti ti-home"></i>
                                </span>
                                <span class="nav-link-title">Dashboard</span>
                            </a>
                        </li>
                        
                        <!-- Akademik -->
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle {{ request()->routeIs(['jam_belajar.*', 'agenda_kelas.*', 'absensi.*']) ? 'active' : '' }}" href="#navbar-akademik" data-bs-toggle="dropdown" role="button" aria-expanded="false">
                                <span class="nav-link-icon d-md-none d-lg-inline-block">
                                    <i class="ti ti-book"></i>
                                </span>
                                <span class="nav-link-title">Akademik</span>
                            </a>
                            <div class="dropdown-menu">
                                <a class="dropdown-item {{ request()->routeIs('jam_belajar.*') ? 'active' : '' }}" href="{{ route('jam_belajar.index') }}">
                                    <i class="ti ti-clock me-2"></i>Jam Belajar
                                </a>
                                <a class="dropdown-item {{ request()->is('jadwal-kbm*') ? 'active' : '' }}" href="{{ url('/jadwal-kbm') }}">
                                    <i class="ti ti-calendar-month me-2"></i>Jadwal KBM
                                </a>
                                <a class="dropdown-item {{ request()->routeIs('agenda_kelas.*') ? 'active' : '' }}" href="{{ route('agenda_kelas.index') }}">
                                    <i class="ti ti-calendar-event me-2"></i>Agenda Kelas
                                </a>
                                <a class="dropdown-item {{ request()->routeIs('absensi.*') ? 'active' : '' }}" href="{{ route('absensi.index') }}">
                                    <i class="ti ti-clipboard-check me-2"></i>Absensi
                                </a>
                                <a class="dropdown-item" href="#">
                                    <i class="ti ti-report-analytics me-2"></i>Nilai
                                </a>
                            </div>
                        </li>
                        
                        <!-- Data Master (Only for Admin and Kepala Sekolah) -->
                        @php
                            $roleName = strtolower(str_replace([' ', '-', '.'], ['_', '', ''], auth()->user()->role->role_name ?? ''));
                        @endphp
                        @if($roleName === 'admin' || $roleName === 'kepala_sekolah')
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#navbar-master" data-bs-toggle="dropdown" role="button" aria-expanded="false">
                                <span class="nav-link-icon d-md-none d-lg-inline-block">
                                    <i class="ti ti-database"></i>
                                </span>
                                <span class="nav-link-title">Data Master</span>
                            </a>
                            <div class="dropdown-menu">
                                <a class="dropdown-item {{ request()->routeIs('sekolah.*') ? 'active' : '' }}" href="{{ route('sekolah.index') }}">
                                    <i class="ti ti-building-bank me-2"></i>Data Sekolah
                                </a>
                                <a class="dropdown-item {{ request()->routeIs('kepala_sekolah.*') ? 'active' : '' }}" href="{{ route('kepala_sekolah.index') }}">
                                    <i class="ti ti-id-badge me-2"></i>Kepala Sekolah
                                </a>
                                <a class="dropdown-item {{ request()->routeIs('guru.*') ? 'active' : '' }}" href="{{ route('guru.index') }}">
                                    <i class="ti ti-users me-2"></i>Guru
                                </a>
                                <a class="dropdown-item {{ request()->routeIs('users.*') ? 'active' : '' }}" href="{{ route('users.index') }}">
                                    <i class="ti ti-lock me-2"></i>Akun Pengguna
                                </a>
                                <a class="dropdown-item {{ request()->routeIs('siswa.*') ? 'active' : '' }}" href="{{ route('siswa.index') }}">
                                    <i class="ti ti-school me-2"></i>Siswa
                                </a>
                                <a class="dropdown-item {{ request()->routeIs('kelas.*') ? 'active' : '' }}" href="{{ route('kelas.index') }}">
                                    <i class="ti ti-building me-2"></i>Kelas
                                </a>
                                <a class="dropdown-item {{ request()->routeIs('mata_pelajaran.*') ? 'active' : '' }}" href="{{ route('mata_pelajaran.index') }}">
                                    <i class="ti ti-books me-2"></i>Mata Pelajaran
                                </a>
                                <a class="dropdown-item {{ request()->routeIs('kegiatan.*') ? 'active' : '' }}" href="{{ route('kegiatan.index') }}">
                                    <i class="ti ti-activity me-2"></i>Kegiatan
                                </a>
                                <a class="dropdown-item {{ request()->routeIs('asc_timetable.*') ? 'active' : '' }}" href="{{ route('asc_timetable.index') }}">
                                    <i class="ti ti-table me-2"></i>ASC Time Table
                                </a>
                            </div>
                        </li>
                        @endif
                        
                        <!-- Pengaturan -->
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle {{ request()->routeIs('tahun_ajaran.index', 'setting.tahun_ajaran*', 'setting.semester*', 'profile.edit') ? 'active' : '' }}" href="#navbar-setting" data-bs-toggle="dropdown" role="button" aria-expanded="false">
                                <span class="nav-link-icon d-md-none d-lg-inline-block">
                                    <i class="ti ti-settings"></i>
                                </span>
                                <span class="nav-link-title">Pengaturan</span>
                            </a>
                            <div class="dropdown-menu">
                                <a class="dropdown-item {{ request()->routeIs('profile.edit') ? 'active' : '' }}" href="{{ route('profile.edit') }}">
                                    <i class="ti ti-user me-2"></i>Profile
                                </a>
                                <div class="dropdown-divider"></div>
                                <a class="dropdown-item {{ request()->routeIs('tahun_ajaran.index') ? 'active' : '' }}" href="{{ route('tahun_ajaran.index') }}">
                                    <i class="ti ti-layout-dashboard me-2"></i>Dashboard Pengaturan
                                </a>
                                <a class="dropdown-item {{ request()->routeIs('setting.tahun_ajaran*') ? 'active' : '' }}" href="{{ route('setting.tahun_ajaran') }}">
                                    <i class="ti ti-calendar me-2"></i>Tahun Ajaran
                                </a>
                                <a class="dropdown-item {{ request()->routeIs('setting.semester*') ? 'active' : '' }}" href="{{ route('setting.semester') }}">
                                    <i class="ti ti-adjustments me-2"></i>Semester
                                </a>
                                <a class="dropdown-item {{ request()->routeIs('setting.header*') ? 'active' : '' }}" href="{{ route('setting.header') }}">
                                    <i class="ti ti-layout-sidebar me-2"></i>Edit Header
                                </a>
                                <!-- Update disabled: menu disembunyikan -->
                            </div>
                        </li>
                    </ul>
                </div>
            </div>
        </aside>
        
        <!-- Main Wrapper -->
        <div class="page-wrapper">
            <!-- Minimal Header - User dropdown only -->
            <div class="container-xl pt-3 pb-2">
                <div class="d-flex justify-content-end">
                    <!-- User Menu -->
                    <div class="dropdown">
                        <a href="#" class="d-flex align-items-center text-decoration-none dropdown-toggle" data-bs-toggle="dropdown">
                            @if(auth()->user()->foto && file_exists(public_path('storage/' . auth()->user()->foto)))
                                <span class="avatar avatar-sm rounded-circle me-2" style="background-image: url({{ asset('storage/' . auth()->user()->foto) }})"></span>
                            @else
                                <span class="avatar avatar-sm rounded-circle me-2" style="background-image: url(https://ui-avatars.com/api/?name={{ urlencode(auth()->user()->name ?? 'U') }}&background=1e3a5f&color=fff)"></span>
                            @endif
                            <div class="d-none d-sm-block text-dark">
                                <div class="fw-medium">{{ auth()->user()->name ?? 'User' }}</div>
                                <div class="small text-muted">{{ auth()->user()->role->role_name ?? 'User' }}</div>
                            </div>
                        </a>
                        <div class="dropdown-menu dropdown-menu-end shadow-sm">
                            <div class="dropdown-item-text">
                                <div class="d-flex align-items-center">
                                    @if(auth()->user()->foto && file_exists(public_path('storage/' . auth()->user()->foto)))
                                        <span class="avatar avatar-md rounded-circle me-3" style="background-image: url({{ asset('storage/' . auth()->user()->foto) }})"></span>
                                    @else
                                        <span class="avatar avatar-md rounded-circle me-3" style="background-image: url(https://ui-avatars.com/api/?name={{ urlencode(auth()->user()->name ?? 'U') }}&background=1e3a5f&color=fff&size=128)"></span>
                                    @endif
                                    <div>
                                        <div class="fw-bold">{{ auth()->user()->name }}</div>
                                        <div class="small text-muted">{{ auth()->user()->email }}</div>
                                        <div class="small text-muted">{{ auth()->user()->role->role_name ?? 'User' }}</div>
                                    </div>
                                </div>
                            </div>
                            <div class="dropdown-divider"></div>
                            <a href="{{ route('profile.edit') }}" class="dropdown-item">
                                <i class="ti ti-user me-2"></i>Profile
                            </a>
                            <a href="{{ route('tahun_ajaran.index') }}" class="dropdown-item">
                                <i class="ti ti-settings me-2"></i>Pengaturan
                            </a>
                            <div class="dropdown-divider"></div>
                            <a href="#" class="dropdown-item text-danger" onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                                <i class="ti ti-logout me-2"></i>Keluar
                            </a>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Page Content -->
            <div class="page-body">
                <div class="container-xl">
                    @yield('content')
                </div>
            </div>
            
            <!-- Footer -->
            <footer class="footer footer-transparent d-print-none">
                <div class="container-xl">
                    <div class="row text-center align-items-center">
                        <div class="col-12">
                            <ul class="list-inline mb-0">
                                <li class="list-inline-item">
                                    &copy; {{ date('Y') }} <a href="." class="link-secondary">Absensi Digital</a> - Sistem Manajemen Sekolah
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </footer>
        </div>
        
        <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">@csrf</form>
        @else
        <!-- Guest Layout -->
        <div class="page-wrapper">
            <div class="page-body">
                <div class="container-xl">
                    @yield('content')
                </div>
            </div>
        </div>
        @endauth
    </div>
    
    <!-- jQuery (required for Summernote) -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <!-- Tabler JS -->
    <script src="https://cdn.jsdelivr.net/npm/@tabler/core@1.0.0-beta17/dist/js/tabler.min.js"></script>
    @stack('js')
</body>
</html>
