<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, minimum-scale=1, maximum-scale=5, user-scalable=yes">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <title>@yield('title', 'Absensi Digital')</title>
    
    <!-- Tabler CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/core@1.0.0-beta17/dist/css/tabler.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@latest/tabler-icons.min.css">
    
    <!-- Mobile Responsive CSS -->
    <link rel="stylesheet" href="{{ asset('css/responsive.css') }}">
    
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
        
        /* ===== RESPONSIVE DESIGN FOR MOBILE ===== */
        
        /* Mobile-first responsive adjustments */
        @media (max-width: 768px) {
            /* Container adjustments */
            .container-xl {
                padding-left: 0.75rem !important;
                padding-right: 0.75rem !important;
            }
            
            /* Page body adjustments */
            .page-body {
                padding: 0.5rem !important;
            }
            
            /* Navbar brand adjustments */
            .navbar-brand {
                font-size: 16px !important;
            }
            
            .navbar-brand img {
                height: 36px !important;
                width: 36px !important;
            }
            
            .navbar-brand span {
                font-size: 16px !important;
            }
            
            /* Card adjustments for mobile */
            .card {
                margin-bottom: 1rem !important;
            }
            
            .card-header {
                padding: 0.75rem !important;
            }
            
            .card-body {
                padding: 0.75rem !important;
            }
            
            /* Table responsive */
            .table-responsive {
                overflow-x: auto;
                -webkit-overflow-scrolling: touch;
            }
            
            table {
                font-size: 0.875rem !important;
            }
            
            table th,
            table td {
                padding: 0.5rem !important;
                white-space: nowrap;
            }
            
            /* Button adjustments */
            .btn {
                padding: 0.5rem 0.75rem !important;
                font-size: 0.875rem !important;
            }
            
            .btn-sm {
                padding: 0.25rem 0.5rem !important;
                font-size: 0.75rem !important;
            }
            
            /* Form adjustments */
            .form-control,
            .form-select {
                font-size: 14px !important;
            }
            
            .form-label {
                font-size: 0.875rem !important;
                margin-bottom: 0.25rem !important;
            }
            
            /* Navbar collapse improvements */
            .navbar-collapse {
                max-height: 70vh;
                overflow-y: auto;
            }
            
            /* Dropdown menu adjustments */
            .dropdown-menu {
                font-size: 0.875rem !important;
            }
            
            .dropdown-item {
                padding: 0.5rem 1rem !important;
            }
            
            /* Header adjustments */
            .page-header {
                padding: 0.75rem 0 !important;
            }
            
            .page-title {
                font-size: 1.25rem !important;
            }
            
            /* Footer adjustments */
            .footer {
                font-size: 0.75rem !important;
                padding: 0.75rem 0 !important;
            }
            
            /* Avatar adjustments */
            .avatar {
                width: 2rem !important;
                height: 2rem !important;
            }
            
            .avatar-md {
                width: 2.5rem !important;
                height: 2.5rem !important;
            }
            
            /* Hide some elements on mobile */
            .d-none.d-sm-block {
                display: none !important;
            }
            
            /* Navbar vertical on mobile */
            .navbar-vertical {
                position: fixed !important;
                top: 0;
                left: 0;
                width: 280px !important;
                height: 100%;
                z-index: 1030;
                transform: translateX(-280px);
                transition: transform 0.3s ease-in-out;
            }
            
            .navbar-vertical.show {
                transform: translateX(0);
            }
            
            /* Page wrapper adjustment when sidebar is open */
            .page {
                margin-left: 0 !important;
            }
            
            /* Improve touch targets */
            .nav-link,
            .dropdown-toggle,
            a,
            button {
                min-height: 44px;
                display: flex;
                align-items: center;
            }
        }
        
        /* Small mobile devices */
        @media (max-width: 576px) {
            /* Even smaller adjustments */
            .container-xl {
                padding-left: 0.5rem !important;
                padding-right: 0.5rem !important;
            }
            
            .card-body {
                padding: 0.5rem !important;
            }
            
            .page-title {
                font-size: 1.1rem !important;
            }
            
            /* Stack columns on very small screens */
            .row > [class*='col-'] {
                margin-bottom: 0.5rem;
            }
            
            /* Simplify table layout */
            table {
                font-size: 0.75rem !important;
            }
            
            table th,
            table td {
                padding: 0.35rem !important;
            }
            
            /* Button group stacking */
            .btn-group {
                flex-direction: column;
            }
            
            .btn-group .btn {
                width: 100%;
                border-radius: 0.25rem !important;
                margin-bottom: 0.25rem;
            }
        }
        
        /* Landscape mode adjustments */
        @media (max-width: 768px) and (orientation: landscape) {
            .navbar-vertical {
                width: 240px !important;
                transform: translateX(-240px);
            }
            
            .navbar-collapse {
                max-height: 60vh;
            }
        }
        
        /* Improve scrolling on mobile */
        @media (max-width: 768px) {
            body {
                -webkit-overflow-scrolling: touch;
            }
            
            .table-responsive {
                -webkit-overflow-scrolling: touch;
            }
            
            /* Make modals mobile-friendly */
            .modal-dialog {
                margin: 0.5rem !important;
                max-width: calc(100% - 1rem) !important;
            }
            
            .modal-body {
                padding: 1rem !important;
            }
            
            /* Breadcrumb adjustments */
            .breadcrumb {
                font-size: 0.875rem !important;
                flex-wrap: wrap;
            }
            
            /* Alert adjustments */
            .alert {
                font-size: 0.875rem !important;
                padding: 0.75rem !important;
            }
            
            /* Badge adjustments */
            .badge {
                font-size: 0.75rem !important;
            }
            
            /* Pagination adjustments */
            .pagination {
                font-size: 0.875rem !important;
            }
            
            .page-link {
                padding: 0.375rem 0.75rem !important;
            }
        }
    </style>
    @stack('css')
</head>
<body class="layout-fluid">
    <div class="page">
        @auth
        @php
            // Gunakan ikon default agar konsisten di navbar
            $defaultLogo = asset('images/icon_simadis.png');
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
                    @php
                        $roleName = strtolower(str_replace([' ', '-', '.'], ['_', '', ''], auth()->user()->role->role_name ?? ''));
                        $isGuru = \Illuminate\Support\Str::contains($roleName, 'guru');
                    @endphp
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

                        @if($isGuru)
                        <!-- Pembelajaran (Guru only) -->
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle {{ request()->routeIs(['agenda_kelas.*', 'absensi.*', 'mata_pelajaran.*']) ? 'active' : '' }}" href="#navbar-pembelajaran" data-bs-toggle="dropdown" role="button" aria-expanded="false">
                                <span class="nav-link-icon d-md-none d-lg-inline-block">
                                    <i class="ti ti-notebook"></i>
                                </span>
                                <span class="nav-link-title">Pembelajaran</span>
                            </a>
                            <div class="dropdown-menu">
                                <a class="dropdown-item {{ request()->routeIs(['mata_pelajaran.guru','mata_pelajaran.*']) ? 'active' : '' }}" href="{{ route('mata_pelajaran.guru') }}">
                                    <i class="ti ti-book-2 me-2"></i>Mata Pelajaran
                                </a>
                                <a class="dropdown-item {{ request()->routeIs('agenda_kelas.*') ? 'active' : '' }}" href="{{ route('agenda_kelas.index') }}">
                                    <i class="ti ti-calendar-event me-2"></i>Agenda Kelas
                                </a>
                                <a class="dropdown-item {{ request()->routeIs('absensi.*') ? 'active' : '' }}" href="{{ route('absensi.index') }}">
                                    <i class="ti ti-clipboard-check me-2"></i>Absensi
                                </a>
                            </div>
                        </li>
                        @endif

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
                                @unless($isGuru)
                                <a class="dropdown-item {{ request()->routeIs('agenda_kelas.*') ? 'active' : '' }}" href="{{ route('agenda_kelas.index') }}">
                                    <i class="ti ti-calendar-event me-2"></i>Agenda Kelas
                                </a>
                                <a class="dropdown-item {{ request()->routeIs('absensi.*') ? 'active' : '' }}" href="{{ route('absensi.index') }}">
                                    <i class="ti ti-clipboard-check me-2"></i>Absensi
                                </a>
                                @endunless
                                <a class="dropdown-item" href="#">
                                    <i class="ti ti-report-analytics me-2"></i>Nilai
                                </a>
                            </div>
                        </li>
                        
                        <!-- Data Master (Only for Admin, Kepala Sekolah, and Wakil Kepala Sekolah) -->
                        @if($roleName === 'admin' || $roleName === 'kepala_sekolah' || $roleName === 'wakil_kepala_sekolah')
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
                                <a class="dropdown-item {{ request()->routeIs('wakil_kepala_sekolah.*') ? 'active' : '' }}" href="{{ route('wakil_kepala_sekolah.index') }}">
                                    <i class="ti ti-user-shield me-2"></i>Wakil Kepala Sekolah
                                </a>
                                <a class="dropdown-item {{ request()->routeIs('guru_bk.*') ? 'active' : '' }}" href="{{ route('guru_bk.index') }}">
                                    <i class="ti ti-mood-smile me-2"></i>Guru BK
                                </a>
                                <a class="dropdown-item {{ request()->routeIs('guru.*') ? 'active' : '' }}" href="{{ route('guru.index') }}">
                                    <i class="ti ti-users me-2"></i>Guru
                                </a>
                                <a class="dropdown-item {{ request()->routeIs('pembina.*') ? 'active' : '' }}" href="{{ route('pembina.index') }}">
                                    <i class="ti ti-user-star me-2"></i>Pembina
                                </a>
                                <a class="dropdown-item {{ request()->routeIs('guru_piket.*') ? 'active' : '' }}" href="{{ route('guru_piket.index') }}">
                                    <i class="ti ti-shield-check me-2"></i>Guru Piket
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
                                <a class="dropdown-item {{ request()->routeIs('mata_pelajaran.*') && !request()->routeIs('tugas_guru.*') ? 'active' : '' }}" href="{{ route('mata_pelajaran.index') }}">
                                    <i class="ti ti-books me-2"></i>Mata Pelajaran
                                </a>
                                <a class="dropdown-item {{ request()->routeIs('tugas_guru.*') ? 'active' : '' }}" href="{{ route('tugas_guru.index') }}">
                                    <i class="ti ti-user-check me-2"></i>Tugas Guru
                                </a>
                                <a class="dropdown-item {{ request()->routeIs('kegiatan.*') ? 'active' : '' }}" href="{{ route('kegiatan.index') }}">
                                    <i class="ti ti-activity me-2"></i>Kegiatan
                                </a>
                                <a class="dropdown-item {{ request()->routeIs('ekstrakurikuler.*') ? 'active' : '' }}" href="{{ route('ekstrakurikuler.index') }}">
                                    <i class="ti ti-flag-3 me-2"></i>Ekstrakurikuler
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
                                @if(auth()->user()->role && in_array(auth()->user()->role->role_name, ['Admin', 'Kepala Sekolah']))
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
                                @endif
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
                            @php
                                $userName = auth()->user()->name ?? 'User';
                                $userPhoto = null;
                                
                                // Check user's foto field first (from profile update)
                                if(auth()->user()->foto) {
                                    $photoPath = storage_path('app/public/' . auth()->user()->foto);
                                    if(file_exists($photoPath)) {
                                        $userPhoto = asset('storage/' . auth()->user()->foto);
                                    }
                                }
                                
                                // Fallback: check guru foto if user is a guru
                                if(!$userPhoto && auth()->user()->guru_id) {
                                    $guru = \App\Models\Guru::find(auth()->user()->guru_id);
                                    if($guru && $guru->foto) {
                                        $guruPhotoPath = public_path('uploads/foto_guru/' . $guru->foto);
                                        if(file_exists($guruPhotoPath)) {
                                            $userPhoto = asset('uploads/foto_guru/' . $guru->foto);
                                        }
                                    }
                                }
                                
                                // Final fallback: generate avatar from initials
                                if(!$userPhoto) {
                                    $userPhoto = 'https://ui-avatars.com/api/?name=' . urlencode($userName) . '&background=1e3a5f&color=fff&size=128';
                                }
                            @endphp
                            <span class="avatar avatar-sm rounded-circle me-2" style="background-image: url({{ $userPhoto }})"></span>
                            <div class="d-none d-sm-block text-dark">
                                <div class="fw-medium">{{ $userName }}</div>
                                <div class="small text-muted">{{ auth()->user()->role->role_name ?? 'User' }}</div>
                            </div>
                        </a>
                        <div class="dropdown-menu dropdown-menu-end shadow-sm">
                            <div class="dropdown-item-text">
                                <div class="d-flex align-items-center">
                                    <span class="avatar avatar-md rounded-circle me-3" style="background-image: url({{ $userPhoto }})"></span>
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
                            @if(auth()->user()->role && in_array(auth()->user()->role->role_name, ['Admin', 'Kepala Sekolah']))
                            <a href="{{ route('tahun_ajaran.index') }}" class="dropdown-item">
                                <i class="ti ti-settings me-2"></i>Pengaturan
                            </a>
                            @endif
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
    <!-- Mobile Navigation Enhancement -->
    <script src="{{ asset('js/mobile-nav.js') }}"></script>
    @stack('js')
</body>
</html>
