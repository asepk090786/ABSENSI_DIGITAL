<nav class="nav flex-column" aria-label="Main menu">
    <div class="nav-item">
        <a href="{{ route('home') }}" class="nav-link {{ request()->routeIs('home') ? 'active' : '' }}">
            <i class="ti ti-home me-2"></i> Dashboard
        </a>
    </div>
    <div class="nav-item">
        <a href="{{ route('siswa.index') }}" class="nav-link {{ request()->routeIs('siswa.*') ? 'active' : '' }}">
            <i class="ti ti-user me-2"></i> Siswa
        </a>
    </div>
    <div class="nav-item">
        <a href="{{ route('guru.index') }}" class="nav-link {{ request()->routeIs('guru.*') ? 'active' : '' }}">
            <i class="ti ti-user-check me-2"></i> Guru
        </a>
    </div>
    <div class="nav-item">
        <a href="{{ route('nilai.index') }}" class="nav-link {{ request()->routeIs('nilai.*') || request()->routeIs('komponen_nilai.*') || request()->routeIs('capaian_pembelajaran.*') ? 'active' : '' }}">
            <i class="ti ti-file-text me-2"></i> Nilai
        </a>
    </div>
    <div class="nav-item">
        <a href="{{ route('jadwal-kbm.index') }}" class="nav-link {{ request()->routeIs('jadwal-kbm.*') ? 'active' : '' }}">
            <i class="ti ti-calendar me-2"></i> Jadwal KBM
        </a>
    </div>
    <div class="nav-item">
        <a href="{{ url('/pengaturan-jam') }}" class="nav-link {{ request()->is('pengaturan-jam*') || request()->routeIs('jadwal_kbm.*') ? 'active' : '' }}">
            <i class="ti ti-clock me-2"></i> Pengaturan Jam
        </a>
    </div>
    <div class="nav-item">
        <a href="{{ route('absensi.index') }}" class="nav-link {{ request()->routeIs('absensi.*') ? 'active' : '' }}">
            <i class="ti ti-checkup-list me-2"></i> Absensi
        </a>
    </div>
    <div class="nav-item">
        <a href="{{ route('rekap_nilai.index') }}" class="nav-link {{ request()->routeIs('rekap_nilai.*') ? 'active' : '' }}">
            <i class="ti ti-report-analytics me-2"></i> Laporan
        </a>
    </div>
</nav>