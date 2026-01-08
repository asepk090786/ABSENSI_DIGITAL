<div class="sidebar">
    <h4>Menu</h4>
    <ul>
        <li @if ($pageSlug == 'dashboard') class="active" @endif>
            <a href="{{ route('home') }}">Dashboard</a>
        </li>
        <li @if ($pageSlug == 'jam_belajar') class="active" @endif>
            <a href="{{ route('jam_belajar.index') }}">Jam Belajar</a>
        </li>
        <li @if ($pageSlug == 'agenda') class="active" @endif>
            <a href="{{ route('agenda_kelas.index') }}">Agenda Kelas</a>
        </li>
        <li @if ($pageSlug == 'absensi') class="active" @endif>
            <a href="{{ route('absensi.index') }}">Absensi</a>
        </li>
        <li @if ($pageSlug == 'setting') class="active" @endif>
            <a href="{{ route('tahun_ajaran.index') }}">Pengaturan</a>
        </li>
    </ul>
</div>
