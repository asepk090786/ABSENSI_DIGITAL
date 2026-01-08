<div class="sidebar">
    <div class="sidebar-wrapper">
        <div class="logo">
            <a href="#" class="simple-text logo-mini">{{ _('WD') }}</a>
            <a href="#" class="simple-text logo-normal">{{ _('White Dashboard') }}</a>
        </div>
        <ul class="nav">
            <li @if ($pageSlug == 'dashboard') class="active " @endif>
                <a href="{{ route('home') }}">
                    <i class="tim-icons icon-chart-pie-36"></i>
                    <p>{{ _('Dashboard') }}</p>
                </a>
            </li>

            <li @if ($pageSlug == 'jam_belajar') class="active " @endif>
                <a href="{{ route('jam_belajar.index') }}">
                    <i class="tim-icons icon-time-alarm"></i>
                    <p>{{ _('Jam Belajar') }}</p>
                </a>
            </li>

            <li @if ($pageSlug == 'agenda') class="active " @endif>
                <a href="{{ route('agenda_kelas.index') }}">
                    <i class="tim-icons icon-notes"></i>
                    <p>{{ _('Agenda Kelas') }}</p>
                </a>
            </li>

            <li @if ($pageSlug == 'absensi') class="active " @endif>
                <a href="#">
                    <i class="tim-icons icon-check-2"></i>
                    <p>{{ _('Absensi') }}</p>
                </a>
            </li>

            <li @if ($pageSlug == 'setting') class="active " @endif>
                <a href="{{ route('tahun_ajaran.index') }}">
                    <i class="tim-icons icon-settings-gear-63"></i>
                    <p>{{ _('Pengaturan') }}</p>
                </a>
            </li>
        </ul>
    </div>
</div>
