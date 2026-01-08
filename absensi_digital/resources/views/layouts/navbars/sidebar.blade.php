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
    </ul>

    <h5>⚙️ Pengaturan</h5>
    <ul class="menu-list">
        <li>
            <a href="#" class="menu-item">
                <i class="material-icons">settings</i>
                <span>Sistem</span>
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
