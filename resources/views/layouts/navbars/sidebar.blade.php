<aside class="sidebar">
    @php
        use Illuminate\Support\Str;
        $user = auth()->user();
        $originalRoleName = $user->role->role_name ?? '';
        $roleName = strtolower(str_replace(' ', '_', $originalRoleName));
        $isGuru = $user->hasAnyRole(['Guru', 'Guru Mapel', 'Guru Kelas', 'Wali Kelas', 'Guru BK', 'Guru Piket']);
        
        // Cek apakah guru adalah wali kelas
        $isWaliKelas = false;
        $kelasBindaan = null;
        if ($isGuru && auth()->user()->guru) {
            $guruId = auth()->user()->guru->id;
            $kelasBindaan = DB::table('kelas')
                ->where('wali_kelas_id', $guruId)
                ->first();
            $isWaliKelas = !is_null($kelasBindaan);
        }
    @endphp
    <h5 style="margin-top: 20px;">📋 Menu Utama</h5>
    <ul class="menu-list">
        <li>
            <a href="{{ route('home') }}" class="menu-item {{ request()->routeIs('home') ? 'active' : '' }}">
                <i class="material-icons">dashboard</i>
                <span>Dashboard</span>
            </a>
        </li>
    </ul>

    @if($isGuru)
    <h5>🎓 Pembelajaran</h5>
    <ul class="menu-list">
        <li>
            <a href="{{ route('agenda_kelas.index') }}" class="menu-item {{ request()->routeIs('agenda_kelas.*') ? 'active' : '' }}">
                <i class="material-icons">event_note</i>
                <span>Agenda Kelas</span>
            </a>
        </li>
        <li>
            <a href="{{ auth()->user()->guru ? route('agenda_kelas.index', ['guru_id' => auth()->user()->guru->id]) : route('agenda_kelas.index') }}" class="menu-item {{ request()->routeIs('agenda_kelas.*') && request()->has('guru_id') ? 'active' : '' }}">
                <i class="material-icons">assignment_ind</i>
                <span>Agenda Guru</span>
            </a>
        </li>
    </ul>
    @endif

    <h5>📚 Akademik</h5>
    <ul class="menu-list">
        @if($isGuru)
        <li>
            <a href="{{ route('komponen_nilai.index') }}" class="menu-item {{ request()->routeIs('komponen_nilai.*') ? 'active' : '' }}">
                <i class="material-icons">fact_check</i>
                <span>Komponen Penilaian</span>
            </a>
        </li>
        <li>
            <a href="{{ route('mata_pelajaran.guru') }}" class="menu-item {{ request()->routeIs(['mata_pelajaran.guru','mata_pelajaran.*']) ? 'active' : '' }}">
                <i class="material-icons">menu_book</i>
                <span>Mata Pelajaran</span>
            </a>
        </li>
        <li>
            <a href="{{ route('rencana_pembelajaran.index') }}" class="menu-item {{ request()->routeIs('rencana_pembelajaran.*') ? 'active' : '' }}">
                <i class="material-icons">description</i>
                <span>Rencana Pembelajaran</span>
            </a>
        </li>
        @endif
        <li>
            <a href="{{ route('jam_belajar.index') }}" class="menu-item {{ request()->routeIs('jam_belajar.*') ? 'active' : '' }}">
                <i class="material-icons">schedule</i>
                <span>Jam Belajar</span>
            </a>
        </li>
        <li>
            <a href="{{ url('/pengaturan-jam') }}" class="menu-item {{ request()->is('pengaturan-jam*') || request()->routeIs('jadwal_kbm.*') ? 'active' : '' }}">
                <i class="material-icons">settings</i>
                <span>Pengaturan Jam</span>
            </a>
        </li>
        <li>
            <a href="{{ url('/jadwal-kbm') }}" class="menu-item {{ request()->is('jadwal-kbm*') ? 'active' : '' }}">
                <i class="material-icons">calendar_month</i>
                <span>Jadwal KBM</span>
            </a>
        </li>
        @if(!auth()->user()->hasRole('Siswa'))
        <li>
            <a href="{{ route('sk_tugas.index') }}" class="menu-item {{ request()->routeIs('sk_tugas.*') ? 'active' : '' }}">
                <i class="material-icons">assignment</i>
                <span>SK TUGAS</span>
            </a>
        </li>
        @endif
        @if(!$isGuru)
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
        @endif
    </ul>

    @if($isWaliKelas)
    <h5>👨‍🏫 Wali Kelas</h5>
    <ul class="menu-list">
        <li>
            <a href="{{ route('wali_kelas.index') }}" class="menu-item {{ request()->routeIs('wali_kelas.index') ? 'active' : '' }}">
                <i class="material-icons">class</i>
                <span>Kelas Binaan ({{ $kelasBindaan->nama_kelas ?? '-' }})</span>
            </a>
        </li>
        <li>
            <a href="{{ route('wali_kelas.siswa') }}" class="menu-item {{ request()->routeIs('wali_kelas.siswa') ? 'active' : '' }}">
                <i class="material-icons">people</i>
                <span>Data Siswa</span>
            </a>
        </li>
        <li>
            <a href="{{ route('wali_kelas.absensi') }}" class="menu-item {{ request()->routeIs('wali_kelas.absensi') ? 'active' : '' }}">
                <i class="material-icons">assignment_turned_in</i>
                <span>Absensi Kelas</span>
            </a>
        </li>
        <li>
            <a href="{{ route('wali_kelas.nilai') }}" class="menu-item {{ request()->routeIs('wali_kelas.nilai') ? 'active' : '' }}">
                <i class="material-icons">bar_chart</i>
                <span>Nilai Siswa</span>
            </a>
        </li>
        <li>
            <a href="{{ route('rekap_nilai.index', ['wali_kelas' => 1, 'kelas_id' => $kelasBindaan->id]) }}" class="menu-item {{ request()->routeIs('rekap_nilai.*') && request()->boolean('wali_kelas') ? 'active' : '' }}">
                <i class="material-icons">assessment</i>
                <span>Rekap Nilai</span>
            </a>
        </li>
    </ul>
    @endif

    {{-- ...existing code... --}}
    @if(auth()->user()->hasAnyRole(['Admin', 'Kepala Sekolah', 'Wakil Kepala Sekolah']))
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
                <a href="{{ route('wakil_kepala_sekolah.index') }}" class="menu-item {{ request()->routeIs('wakil_kepala_sekolah.*') ? 'active' : '' }}">
                    <i class="material-icons">admin_panel_settings</i>
                    <span>Wakil Kepala Sekolah</span>
                </a>
            </li>
            <li>
                <a href="{{ route('guru_bk.index') }}" class="menu-item {{ request()->routeIs('guru_bk.*') ? 'active' : '' }}">
                    <i class="material-icons">psychology</i>
                    <span>Guru BK</span>
                </a>
            </li>
            <li>
                <a href="{{ route('guru.index') }}" class="menu-item {{ request()->routeIs('guru.*') ? 'active' : '' }}">
                    <i class="material-icons">people</i>
                    <span>Guru</span>
                </a>
            </li>
            <li>
                <a href="{{ route('guru_piket.index') }}" class="menu-item {{ request()->routeIs('guru_piket.*') ? 'active' : '' }}">
                    <i class="material-icons">security</i>
                    <span>Guru Piket</span>
                </a>
            </li>
            <li>
                <a href="{{ route('siswa.index') }}" class="menu-item {{ request()->routeIs('siswa.*') ? 'active' : '' }}">
                    <i class="material-icons">school</i>
                    <span>Siswa</span>
                </a>
            </li>
            <li>
                <a href="{{ route('kelas.index') }}" class="menu-item {{ request()->routeIs('kelas.*') ? 'active' : '' }}">
                    <i class="material-icons">class</i>
                    <span>Kelas</span>
                </a>
            </li>
            <li>
                <a href="{{ route('jenis_kegiatan.index') }}" class="menu-item {{ request()->routeIs('jenis_kegiatan.*') ? 'active' : '' }}">
                    <i class="material-icons">category</i>
                    <span>Jenis Kegiatan</span>
                </a>
            </li>
            <li>
                <a href="{{ route('ekstrakurikuler.index') }}" class="menu-item {{ request()->routeIs('ekstrakurikuler.*') ? 'active' : '' }}">
                    <i class="material-icons">sports_soccer</i>
                    <span>Ekstrakurikuler</span>
                </a>
            </li>
            <li>
                <a href="{{ route('mata_pelajaran.index') }}" class="menu-item {{ request()->routeIs('mata_pelajaran.*') && !request()->routeIs('tugas_guru.*') ? 'active' : '' }}">
                    <i class="material-icons">menu_book</i>
                    <span>Mata Pelajaran</span>
                </a>
            </li>
            <li>
                <a href="{{ route('tugas_guru.index') }}" class="menu-item {{ request()->routeIs('tugas_guru.*') ? 'active' : '' }}">
                    <i class="material-icons">assignment_ind</i>
                    <span>Tugas Guru</span>
                </a>
            </li>
            <li>
                <a href="{{ route('asc_timetable.index') }}" class="menu-item {{ request()->routeIs('asc_timetable.*') ? 'active' : '' }}">
                    <i class="material-icons">table_chart</i>
                    <span>ASC Time Table</span>
                </a>
            </li>
        </ul>
    @endif

    <h5>⚙️ Pengaturan</h5>
    <ul class="menu-list">
        @if(auth()->user()->hasAnyRole(['Admin','Kepala Sekolah']))
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
        @else
        <li>
            <a href="{{ route('profile.edit') }}" class="menu-item {{ request()->routeIs('profile.edit') ? 'active' : '' }}">
                <i class="material-icons">person</i>
                <span>Edit Profile</span>
            </a>
        </li>
        @endif
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
