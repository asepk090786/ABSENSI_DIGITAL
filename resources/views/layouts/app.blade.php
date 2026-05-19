<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Absensi Digital')</title>

    <!-- Bootstrap 4 -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/4.6.2/css/bootstrap.min.css">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Tabler Icons (for ti ti-* icons) -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@3.44.0/dist/tabler-icons.min.css">
    <!-- AdminLTE -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/admin-lte/3.2.0/css/adminlte.min.css">
    <!-- Custom responsive styles -->
    <link rel="stylesheet" href="{{ asset('css/responsive.css') }}">

    <style>
        .brand-link {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: .5rem;
        }

        .brand-text {
            white-space: normal;
        }

        .brand-image {
            width: 2.2rem !important;
            height: 2.2rem !important;
            object-fit: cover;
        }

        .user-panel .image .img-circle,
        .img-circle.elevation-2 {
            display: inline-block;
            width: 2.5rem;
            height: 2.5rem;
            background-size: cover;
            background-position: center;
        }

        .main-footer {
            background: #f4f6f9;
            border-top: 1px solid rgba(0, 0, 0, 0.05);
        }

        @media (max-width: 768px) {
            .content-header .container-fluid,
            .content .container-fluid {
                padding-left: 0.75rem !important;
                padding-right: 0.75rem !important;
            }
        }
    </style>

    @stack('css')
</head>
<body class="hold-transition sidebar-mini layout-fixed">
    <div class="wrapper">
        @auth
        @php
            $defaultLogo = asset('images/icon_simadis.png');
            $user = auth()->user();
            $userPhoto = $defaultLogo;
            if ($user->foto) {
                $photoPath = storage_path('app/public/' . $user->foto);
                if (file_exists($photoPath)) {
                    $userPhoto = asset('storage/' . $user->foto);
                }
            } elseif ($user->guru_id) {
                $guru = \App\Models\Guru::find($user->guru_id);
                if ($guru && $guru->foto) {
                    $guruPhotoPath = public_path('uploads/foto_guru/' . $guru->foto);
                    if (file_exists($guruPhotoPath)) {
                        $userPhoto = asset('uploads/foto_guru/' . $guru->foto);
                    }
                }
            }
        @endphp

        <!-- Navbar -->
        <nav class="main-header navbar navbar-expand navbar-white navbar-light">
            <ul class="navbar-nav">
                <li class="nav-item">
                    <a class="nav-link" data-widget="pushmenu" href="#" role="button"><i class="fas fa-bars"></i></a>
                </li>
                <li class="nav-item d-none d-sm-inline-block">
                    <a href="{{ route('home') }}" class="nav-link">Home</a>
                </li>
            </ul>

            <ul class="navbar-nav ml-auto">
                <li class="nav-item dropdown">
                    <a class="nav-link" data-toggle="dropdown" href="#">
                        <img src="{{ $userPhoto }}" class="img-circle elevation-2" alt="User Image" style="width:32px;height:32px;object-fit:cover">
                        <span class="ml-2 d-none d-md-inline">{{ $user->name }}</span>
                    </a>
                    <div class="dropdown-menu dropdown-menu-lg dropdown-menu-right">
                        <div class="dropdown-header text-center">
                            <img src="{{ $userPhoto }}" class="img-circle elevation-2 mb-2" alt="User Image" style="width:60px;height:60px;object-fit:cover">
                            <p class="mb-0">{{ $user->name }}</p>
                            <small class="text-muted">{{ $user->role->role_name ?? 'User' }}</small>
                        </div>
                        <div class="dropdown-divider"></div>
                        <a href="{{ route('profile.edit') }}" class="dropdown-item">
                            <i class="fas fa-user mr-2"></i> Profile
                        </a>
                        @if($user->hasAnyRole(['Admin', 'Kepala Sekolah']))
                        <a href="{{ route('tahun_ajaran.index') }}" class="dropdown-item">
                            <i class="fas fa-cog mr-2"></i> Pengaturan
                        </a>
                        @endif
                        <div class="dropdown-divider"></div>
                        <a href="#" class="dropdown-item text-danger" onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                            <i class="fas fa-sign-out-alt mr-2"></i> Keluar
                        </a>
                    </div>
                </li>
            </ul>
        </nav>

        <!-- Main Sidebar Container -->
        <aside class="main-sidebar sidebar-dark-primary elevation-4">
            <a href="{{ route('home') }}" class="brand-link">
                <img src="{{ $defaultLogo }}" alt="SIMADIS Logo" class="brand-image img-circle elevation-3" style="opacity: .8">
                <span class="brand-text font-weight-light">SIMADIS</span>
            </a>

            <div class="sidebar">
                <nav class="mt-2">
                    @php
                        $roleName = strtolower(str_replace([' ', '-', '.'], ['_', '', ''], $user->role->role_name ?? ''));
                        $isGuru = $user->hasAnyRole(['Guru', 'Guru Mapel', 'Guru Kelas', 'Wali Kelas', 'Guru BK', 'Guru Piket']);
                    @endphp
                    <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu" data-accordion="false">
                        <li class="nav-item">
                            <a href="{{ route('home') }}" class="nav-link {{ request()->routeIs('home') ? 'active' : '' }}">
                                <i class="nav-icon fas fa-tachometer-alt"></i>
                                <p>Dashboard</p>
                            </a>
                        </li>

                        @if($isGuru)
                        <li class="nav-item has-treeview {{ request()->routeIs(['agenda_kelas.*', 'agenda_guru.*', 'absensi.*', 'mata_pelajaran.*', 'rencana_pembelajaran.*']) ? 'menu-open' : '' }}">
                            <a href="#" class="nav-link {{ request()->routeIs(['agenda_kelas.*', 'agenda_guru.*', 'absensi.*', 'mata_pelajaran.*', 'rencana_pembelajaran.*']) ? 'active' : '' }}">
                                <i class="nav-icon fas fa-book"></i>
                                <p>
                                    Pembelajaran
                                    <i class="right fas fa-angle-left"></i>
                                </p>
                            </a>
                            <ul class="nav nav-treeview">
                                <li class="nav-item">
                                    <a href="{{ route('komponen_nilai.index') }}" class="nav-link {{ request()->routeIs('komponen_nilai.*') ? 'active' : '' }}">
                                        <i class="far fa-circle nav-icon"></i>
                                        <p>Komponen Penilaian</p>
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a href="{{ route('mata_pelajaran.guru') }}" class="nav-link {{ request()->routeIs(['mata_pelajaran.guru','mata_pelajaran.*']) ? 'active' : '' }}">
                                        <i class="far fa-circle nav-icon"></i>
                                        <p>Mata Pelajaran</p>
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a href="{{ route('rencana_pembelajaran.index') }}" class="nav-link {{ request()->routeIs('rencana_pembelajaran.*') ? 'active' : '' }}">
                                        <i class="far fa-circle nav-icon"></i>
                                        <p>Rencana Pembelajaran</p>
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a href="{{ route('agenda_kelas.index') }}" class="nav-link {{ request()->routeIs('agenda_kelas.*') ? 'active' : '' }}">
                                        <i class="far fa-circle nav-icon"></i>
                                        <p>Agenda Kelas</p>
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a href="{{ route('agenda_guru.index') }}" class="nav-link {{ request()->routeIs('agenda_guru.*') ? 'active' : '' }}">
                                        <i class="far fa-circle nav-icon"></i>
                                        <p>Agenda Guru</p>
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a href="{{ route('absensi.index') }}" class="nav-link {{ request()->routeIs('absensi.*') ? 'active' : '' }}">
                                        <i class="far fa-circle nav-icon"></i>
                                        <p>Absensi</p>
                                    </a>
                                </li>
                            </ul>
                        </li>
                        @endif

                        @php
                            $isGuruPiket = false;
                            if ($isGuru && $user->guru) {
                                $hariPiket = $user->guru->hari_piket ?? [];
                                $isGuruPiket = !empty($hariPiket) || $user->hasRole('Guru Piket');
                            }
                        @endphp
                        @if($isGuruPiket)
                        <li class="nav-item has-treeview {{ request()->is('jadwal-kbm*') || request()->routeIs(['agenda_guru.*','absensi.*','piket.pelanggaran.*']) ? 'menu-open' : '' }}">
                            <a href="#" class="nav-link {{ request()->is('jadwal-kbm*') || request()->routeIs(['agenda_guru.*','absensi.*','piket.pelanggaran.*']) ? 'active' : '' }}">
                                <i class="nav-icon fas fa-shield-alt"></i>
                                <p>
                                    Piket KBM
                                    <i class="right fas fa-angle-left"></i>
                                </p>
                            </a>
                            <ul class="nav nav-treeview">
                                <li class="nav-item">
                                    <a href="{{ url('/jadwal-kbm') }}" class="nav-link {{ request()->is('jadwal-kbm*') ? 'active' : '' }}">
                                        <i class="far fa-circle nav-icon"></i>
                                        <p>Jadwal Mengajar</p>
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a href="{{ route('agenda_guru.index') }}" class="nav-link {{ request()->routeIs('agenda_guru.*') ? 'active' : '' }}">
                                        <i class="far fa-circle nav-icon"></i>
                                        <p>Absensi Guru</p>
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a href="{{ route('absensi.index') }}" class="nav-link {{ request()->routeIs('absensi.*') ? 'active' : '' }}">
                                        <i class="far fa-circle nav-icon"></i>
                                        <p>Absensi Siswa</p>
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a href="{{ route('piket.pelanggaran.index') }}" class="nav-link {{ request()->routeIs('piket.pelanggaran.*') ? 'active' : '' }}">
                                        <i class="far fa-circle nav-icon"></i>
                                        <p>Pelanggaran</p>
                                    </a>
                                </li>
                            </ul>
                        </li>
                        @endif

                        <li class="nav-item has-treeview {{ request()->routeIs(['jam_belajar.*','agenda_kelas.*','absensi.*','nilai.index','rekap_nilai.*']) ? 'menu-open' : '' }}">
                            <a href="#" class="nav-link {{ request()->routeIs(['jam_belajar.*','agenda_kelas.*','absensi.*','nilai.index','rekap_nilai.*']) ? 'active' : '' }}">
                                <i class="nav-icon fas fa-school"></i>
                                <p>
                                    Akademik
                                    <i class="right fas fa-angle-left"></i>
                                </p>
                            </a>
                            <ul class="nav nav-treeview">
                                <li class="nav-item">
                                    <a href="{{ route('jam_belajar.index') }}" class="nav-link {{ request()->routeIs('jam_belajar.*') ? 'active' : '' }}">
                                        <i class="far fa-circle nav-icon"></i>
                                        <p>Jam Belajar</p>
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a href="{{ url('/jadwal-kbm') }}" class="nav-link {{ request()->is('jadwal-kbm*') ? 'active' : '' }}">
                                        <i class="far fa-circle nav-icon"></i>
                                        <p>Jadwal KBM</p>
                                    </a>
                                </li>
                                @unless($isGuru)
                                <li class="nav-item">
                                    <a href="{{ route('agenda_kelas.index') }}" class="nav-link {{ request()->routeIs('agenda_kelas.*') ? 'active' : '' }}">
                                        <i class="far fa-circle nav-icon"></i>
                                        <p>Agenda Kelas</p>
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a href="{{ route('absensi.index') }}" class="nav-link {{ request()->routeIs('absensi.*') ? 'active' : '' }}">
                                        <i class="far fa-circle nav-icon"></i>
                                        <p>Absensi</p>
                                    </a>
                                </li>
                                @endunless
                                <li class="nav-item">
                                    <a href="{{ route('nilai.index') }}" class="nav-link {{ request()->routeIs('nilai.index') ? 'active' : '' }}">
                                        <i class="far fa-circle nav-icon"></i>
                                        <p>Nilai</p>
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a href="{{ route('rekap_nilai.index') }}" class="nav-link {{ request()->routeIs('rekap_nilai.*') ? 'active' : '' }}">
                                        <i class="far fa-circle nav-icon"></i>
                                        <p>Rekap Nilai</p>
                                    </a>
                                </li>
                            </ul>
                        </li>

                        @php
                            $isWaliKelas = false;
                            $kelasBindaan = null;
                            if ($isGuru && $user->guru) {
                                $guruId = $user->guru->id;
                                $kelasBindaan = DB::table('kelas')->where('wali_kelas_id', $guruId)->first();
                                $isWaliKelas = !is_null($kelasBindaan);
                            }
                        @endphp
                        @if($isWaliKelas)
                        <li class="nav-item has-treeview {{ request()->routeIs('wali_kelas.*') ? 'menu-open' : '' }}">
                            <a href="#" class="nav-link {{ request()->routeIs('wali_kelas.*') ? 'active' : '' }}">
                                <i class="nav-icon fas fa-school"></i>
                                <p>
                                    Wali Kelas
                                    <i class="right fas fa-angle-left"></i>
                                </p>
                            </a>
                            <ul class="nav nav-treeview">
                                <li class="nav-item">
                                    <a href="{{ route('wali_kelas.index') }}" class="nav-link {{ request()->routeIs('wali_kelas.index') ? 'active' : '' }}">
                                        <i class="far fa-circle nav-icon"></i>
                                        <p>Dashboard</p>
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a href="{{ route('wali_kelas.siswa') }}" class="nav-link {{ request()->routeIs('wali_kelas.siswa') ? 'active' : '' }}">
                                        <i class="far fa-circle nav-icon"></i>
                                        <p>Data Siswa</p>
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a href="{{ route('wali_kelas.absensi') }}" class="nav-link {{ request()->routeIs('wali_kelas.absensi') ? 'active' : '' }}">
                                        <i class="far fa-circle nav-icon"></i>
                                        <p>Absensi Kelas</p>
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a href="{{ route('wali_kelas.laporan_guru') }}" class="nav-link {{ request()->routeIs('wali_kelas.laporan_guru') ? 'active' : '' }}">
                                        <i class="far fa-circle nav-icon"></i>
                                        <p>Laporan Guru</p>
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a href="{{ route('wali_kelas.nilai') }}" class="nav-link {{ request()->routeIs('wali_kelas.nilai') ? 'active' : '' }}">
                                        <i class="far fa-circle nav-icon"></i>
                                        <p>Nilai Siswa</p>
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a href="{{ route('rekap_nilai.index', ['wali_kelas' => 1, 'kelas_id' => $kelasBindaan->id]) }}" class="nav-link {{ request()->routeIs('rekap_nilai.*') && request()->boolean('wali_kelas') ? 'active' : '' }}">
                                        <i class="far fa-circle nav-icon"></i>
                                        <p>Rekap Nilai</p>
                                    </a>
                                </li>
                            </ul>
                        </li>
                        @endif

                        @if($user->hasRole('Guru BK'))
                        <li class="nav-item has-treeview {{ request()->routeIs('guru_bk_layanan.*') ? 'menu-open' : '' }}">
                            <a href="#" class="nav-link {{ request()->routeIs('guru_bk_layanan.*') ? 'active' : '' }}">
                                <i class="nav-icon fas fa-user-graduate"></i>
                                <p>
                                    Guru BK
                                    <i class="right fas fa-angle-left"></i>
                                </p>
                            </a>
                            <ul class="nav nav-treeview">
                                @forelse($kelasBinaanBk as $kelasBk)
                                <li class="nav-item">
                                    <a href="{{ route('guru_bk_layanan.menu', ['kelas' => $kelasBk->id]) }}" class="nav-link {{ request()->routeIs('guru_bk_layanan.*') && (int) request()->route('kelas')?->id === (int) $kelasBk->id ? 'active' : '' }}">
                                        <i class="far fa-circle nav-icon"></i>
                                        <p>{{ $kelasBk->nama_kelas }}</p>
                                    </a>
                                </li>
                                @empty
                                <li class="nav-item">
                                    <a class="nav-link text-muted">
                                        <i class="far fa-circle nav-icon"></i>
                                        <p>Belum ada kelas binaan</p>
                                    </a>
                                </li>
                                @endforelse
                            </ul>
                        </li>
                        @endif

                        @if($user->hasAnyRole(['Admin', 'Kepala Sekolah', 'Wakil Kepala Sekolah']))
                        <li class="nav-item has-treeview {{ request()->routeIs(['sekolah.*','kepala_sekolah.*','wakil_kepala_sekolah.*','guru_bk.*','guru.*','pembina.*','guru_piket.*','users.*','siswa.*','kelas.*','mata_pelajaran.*','tugas_guru.*','kegiatan.*','jenis_pelanggaran.*','ekstrakurikuler.*','asc_timetable.*']) ? 'menu-open' : '' }}">
                            <a href="#" class="nav-link {{ request()->routeIs(['sekolah.*','kepala_sekolah.*','wakil_kepala_sekolah.*','guru_bk.*','guru.*','pembina.*','guru_piket.*','users.*','siswa.*','kelas.*','mata_pelajaran.*','tugas_guru.*','kegiatan.*','jenis_pelanggaran.*','ekstrakurikuler.*','asc_timetable.*']) ? 'active' : '' }}">
                                <i class="nav-icon fas fa-database"></i>
                                <p>
                                    Data Master
                                    <i class="right fas fa-angle-left"></i>
                                </p>
                            </a>
                            <ul class="nav nav-treeview">
                                <li class="nav-item"><a class="nav-link {{ request()->routeIs('sekolah.*') ? 'active' : '' }}" href="{{ route('sekolah.index') }}"><i class="far fa-circle nav-icon"></i><p>Data Sekolah</p></a></li>
                                <li class="nav-item"><a class="nav-link {{ request()->routeIs('kepala_sekolah.*') ? 'active' : '' }}" href="{{ route('kepala_sekolah.index') }}"><i class="far fa-circle nav-icon"></i><p>Kepala Sekolah</p></a></li>
                                <li class="nav-item"><a class="nav-link {{ request()->routeIs('wakil_kepala_sekolah.*') ? 'active' : '' }}" href="{{ route('wakil_kepala_sekolah.index') }}"><i class="far fa-circle nav-icon"></i><p>Wakil Kepala Sekolah</p></a></li>
                                <li class="nav-item"><a class="nav-link {{ request()->routeIs('guru_bk.*') ? 'active' : '' }}" href="{{ route('guru_bk.index') }}"><i class="far fa-circle nav-icon"></i><p>Guru BK</p></a></li>
                                <li class="nav-item"><a class="nav-link {{ request()->routeIs('guru.*') ? 'active' : '' }}" href="{{ route('guru.index') }}"><i class="far fa-circle nav-icon"></i><p>Guru</p></a></li>
                                <li class="nav-item"><a class="nav-link {{ request()->routeIs('pembina.*') ? 'active' : '' }}" href="{{ route('pembina.index') }}"><i class="far fa-circle nav-icon"></i><p>Pembina</p></a></li>
                                <li class="nav-item"><a class="nav-link {{ request()->routeIs('guru_piket.*') ? 'active' : '' }}" href="{{ route('guru_piket.index') }}"><i class="far fa-circle nav-icon"></i><p>Guru Piket</p></a></li>
                                <li class="nav-item"><a class="nav-link {{ request()->routeIs('users.*') ? 'active' : '' }}" href="{{ route('users.index') }}"><i class="far fa-circle nav-icon"></i><p>Akun Pengguna</p></a></li>
                                <li class="nav-item"><a class="nav-link {{ request()->routeIs('siswa.*') ? 'active' : '' }}" href="{{ route('siswa.index') }}"><i class="far fa-circle nav-icon"></i><p>Siswa</p></a></li>
                                <li class="nav-item"><a class="nav-link {{ request()->routeIs('kelas.*') ? 'active' : '' }}" href="{{ route('kelas.index') }}"><i class="far fa-circle nav-icon"></i><p>Kelas</p></a></li>
                                <li class="nav-item"><a class="nav-link {{ request()->routeIs('mata_pelajaran.*') && !request()->routeIs('tugas_guru.*') ? 'active' : '' }}" href="{{ route('mata_pelajaran.index') }}"><i class="far fa-circle nav-icon"></i><p>Mata Pelajaran</p></a></li>
                                <li class="nav-item"><a class="nav-link {{ request()->routeIs('tugas_guru.*') ? 'active' : '' }}" href="{{ route('tugas_guru.index') }}"><i class="far fa-circle nav-icon"></i><p>Tugas Guru</p></a></li>
                                <li class="nav-item"><a class="nav-link {{ request()->routeIs('kegiatan.*') ? 'active' : '' }}" href="{{ route('kegiatan.index') }}"><i class="far fa-circle nav-icon"></i><p>Kegiatan</p></a></li>
                                <li class="nav-item"><a class="nav-link {{ request()->routeIs('jenis_pelanggaran.*') ? 'active' : '' }}" href="{{ route('jenis_pelanggaran.index') }}"><i class="far fa-circle nav-icon"></i><p>Jenis Pelanggaran</p></a></li>
                                <li class="nav-item"><a class="nav-link {{ request()->routeIs('ekstrakurikuler.*') ? 'active' : '' }}" href="{{ route('ekstrakurikuler.index') }}"><i class="far fa-circle nav-icon"></i><p>Ekstrakurikuler</p></a></li>
                                <li class="nav-item"><a class="nav-link {{ request()->routeIs('asc_timetable.*') ? 'active' : '' }}" href="{{ route('asc_timetable.index') }}"><i class="far fa-circle nav-icon"></i><p>ASC Time Table</p></a></li>
                            </ul>
                        </li>
                        @endif

                        <li class="nav-item has-treeview {{ request()->routeIs(['tahun_ajaran.index','setting.tahun_ajaran*','setting.semester*','setting.header*','profile.edit']) ? 'menu-open' : '' }}">
                            <a href="#" class="nav-link {{ request()->routeIs(['tahun_ajaran.index','setting.tahun_ajaran*','setting.semester*','setting.header*','profile.edit']) ? 'active' : '' }}">
                                <i class="nav-icon fas fa-cogs"></i>
                                <p>
                                    Pengaturan
                                    <i class="right fas fa-angle-left"></i>
                                </p>
                            </a>
                            <ul class="nav nav-treeview">
                                <li class="nav-item"><a href="{{ route('profile.edit') }}" class="nav-link {{ request()->routeIs('profile.edit') ? 'active' : '' }}"><i class="far fa-circle nav-icon"></i><p>Profile</p></a></li>
                                @if($user->hasAnyRole(['Admin','Kepala Sekolah']))
                                <li class="nav-item"><a href="{{ route('tahun_ajaran.index') }}" class="nav-link {{ request()->routeIs('tahun_ajaran.index') ? 'active' : '' }}"><i class="far fa-circle nav-icon"></i><p>Dashboard Pengaturan</p></a></li>
                                <li class="nav-item"><a href="{{ route('setting.tahun_ajaran') }}" class="nav-link {{ request()->routeIs('setting.tahun_ajaran*') ? 'active' : '' }}"><i class="far fa-circle nav-icon"></i><p>Tahun Ajaran</p></a></li>
                                <li class="nav-item"><a href="{{ route('setting.semester') }}" class="nav-link {{ request()->routeIs('setting.semester*') ? 'active' : '' }}"><i class="far fa-circle nav-icon"></i><p>Semester</p></a></li>
                                <li class="nav-item"><a href="{{ route('setting.header') }}" class="nav-link {{ request()->routeIs('setting.header*') ? 'active' : '' }}"><i class="far fa-circle nav-icon"></i><p>Edit Header</p></a></li>
                                @endif
                            </ul>
                        </li>
                    </ul>
                </nav>
            </div>
        </aside>

        <!-- Content Wrapper. Contains page content -->
        <div class="content-wrapper">
            <section class="content-header">
                <div class="container-fluid">
                    <div class="row mb-2">
                        <div class="col-sm-6">
                            <h1>@yield('title', 'Dashboard')</h1>
                        </div>
                    </div>
                </div>
            </section>

            <section class="content">
                <div class="container-fluid">
                    @yield('content')
                </div>
            </section>
        </div>

        <footer class="main-footer">
            <div class="float-right d-none d-sm-inline">
                Absensi Digital
            </div>
            <strong>&copy; {{ date('Y') }} <a href=".">Absensi Digital</a>.</strong> Sistem Manajemen Sekolah.
        </footer>

        <aside class="control-sidebar control-sidebar-dark"></aside>

        <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">@csrf</form>
        @else
        <div class="content-wrapper">
            <section class="content">
                <div class="container-fluid">
                    @yield('content')
                </div>
            </section>
        </div>
        @endauth
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/4.6.2/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/admin-lte/3.2.0/js/adminlte.min.js"></script>
    @stack('js')
</body>
</html>
