<aside class="sidebar">
    <h5>📋 Menu Utama</h5>
    <ul class="menu-list">
        <li>
            <a href="{{ route('home') }}" class="menu-item {{ request()->routeIs('home') ? 'active' : '' }}">
                <i class="material-icons">dashboard</i>
                <span>Dashboard</span>
            </a>
        </li>
    </ul>

    <h5>📚 Akademik</h5>
    <ul class="menu-list">
        <li>
            <a href="{{ route('jam_belajar.index') }}" class="menu-item {{ request()->routeIs('jam_belajar.*') ? 'active' : '' }}">
                <i class="material-icons">schedule</i>
                <span>Jam Belajar</span>
            </a>
        </li>
        <li>
            <a href="{{ url('/jadwal-kbm') }}" class="menu-item {{ request()->is('jadwal-kbm*') ? 'active' : '' }}">
                <i class="material-icons">calendar_month</i>
                <span>Jadwal KBM</span>
            </a>
        </li>
        <li>
            <a href="{{ route('agenda_kelas.index') }}" class="menu-item {{ request()->routeIs('agenda_kelas.*') ? 'active' : '' }}">
                <i class="material-icons">event_note</i>
                <span>Agenda Kelas</span>
            </a>
        </li>
        <li>
            <a href="{{ route('absensi.index') }}" class="menu-item {{ request()->routeIs('absensi.*') ? 'active' : '' }}">
                <i class="material-icons">assignment</i>
                <span>Absensi</span>
            </a>
        </li>
        <li>
            <a href="#" class="menu-item">
                <i class="material-icons">grading</i>
                <span>Nilai</span>
            </a>
        </li>
    </ul>

    <h5>👥 Data Master</h5>
    <ul class="menu-list">
        <li>
            <a href="{{ route('sekolah.index') }}" class="menu-item {{ request()->routeIs('sekolah.*') ? 'active' : '' }}">
                <i class="material-icons">account_balance</i>
                <span>Data Sekolah</span>
            </a>
        </li>
        <li>
            <a href="{{ route('kepala_sekolah.index') }}" class="menu-item {{ request()->routeIs('kepala_sekolah.*') ? 'active' : '' }}">
                <i class="material-icons">person_pin</i>
                <span>Kepala Sekolah</span>
            </a>
        </li>
        <li>
            <a href="#" class="menu-item">
                <i class="material-icons">people</i>
                <span>Guru</span>
            </a>
        </li>
        <li>
            <a href="#" class="menu-item">
                <i class="material-icons">school</i>
                <span>Siswa</span>
            </a>
        </li>
        <li>
            <a href="#" class="menu-item">
                <i class="material-icons">class</i>
                <span>Kelas</span>
            </a>
        </li>
        <li>
            <a href="#" class="menu-item">
                <i class="material-icons">menu_book</i>
                <span>Mata Pelajaran</span>
            </a>
        </li>
    </ul>

    <h5>⚙️ Pengaturan</h5>
    <ul class="menu-list">
        <li>
            <a href="{{ route('tahun_ajaran.index') }}" class="menu-item {{ request()->routeIs('tahun_ajaran.index') ? 'active' : '' }}">
                <i class="material-icons">settings</i>
                <span>Pengaturan Sistem</span>
            </a>
        </li>
        <li>
            <a href="{{ route('setting.tahun_ajaran') }}" class="menu-item {{ request()->routeIs('setting.tahun_ajaran*') ? 'active' : '' }}">
                <i class="material-icons">date_range</i>
                <span>Tahun Ajaran</span>
            </a>
        </li>
        <li>
            <a href="{{ route('setting.semester') }}" class="menu-item {{ request()->routeIs('setting.semester*') ? 'active' : '' }}">
                <i class="material-icons">calendar_today</i>
                <span>Semester</span>
            </a>
        </li>
    </ul>
</aside>

<style>
    .menu-list {
        list-style: none;
        padding: 0;
        margin: 0;
    }

    .menu-item {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 12px 16px;
        color: #666;
        transition: all 0.3s ease;
    }

    .menu-item i {
        font-size: 20px;
    }

    .menu-item span {
        flex: 1;
        font-size: 14px;
    }
</style>
