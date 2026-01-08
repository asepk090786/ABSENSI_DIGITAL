<div class="sidebar">
    <div class="sidebar-wrapper">
        <div class="logo">
            <a href="#" class="simple-text logo-mini">MD</a>
            <a href="#" class="simple-text logo-normal">Material Dashboard</a>
        </div>
        <ul class="nav">
            <li @if ($pageSlug == 'dashboard') class="active " @endif>
                <a href="{{ route('home') }}">
                    <i class="material-icons">dashboard</i>
                    <p>Dashboard</p>
                </a>
            </li>

            <li @if ($pageSlug == 'jam_belajar') class="active " @endif>
                <a href="{{ route('jam_belajar.index') }}">
                    <i class="material-icons">schedule</i>
                    <p>Jam Belajar</p>
                </a>
            </li>

            <li @if ($pageSlug == 'agenda') class="active " @endif>
                <a href="{{ route('agenda_kelas.index') }}">
                    <i class="material-icons">event_note</i>
                    <p>Agenda Kelas</p>
                </a>
            </li>

            <li @if ($pageSlug == 'absensi') class="active " @endif>
                <a href="{{ route('absensi.index') }}">
                    <i class="material-icons">check_circle</i>
                    <p>Absensi</p>
                </a>
            </li>

            <li @if ($pageSlug == 'setting') class="active " @endif>
                <a href="{{ route('tahun_ajaran.index') }}">
                    <i class="material-icons">settings</i>
                    <p>Pengaturan</p>
                </a>
            </li>

            <li>
                <a href="{{ url('material/index.html') }}" target="_blank">
                    <i class="material-icons">web</i>
                    <p>Material React App</p>
                </a>
            </li>
        </ul>
    </div>
</div>
