@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
<div class="welcome-banner d-flex align-items-center gap-3">
    <div class="flex-shrink-0">
        <span class="avatar avatar-lg rounded-circle bg-white text-primary" style="width:3rem;height:3rem;font-size:1.3rem;">
            <i class="ti ti-user"></i>
        </span>
    </div>
    <div>
        <h3 class="mb-1 text-white">Selamat Datang, {{ auth()->user()->name ?? 'User' }}! 👋</h3>
        <p class="mb-0 opacity-75">
            Anda masuk sebagai <span class="badge bg-white text-primary">{{ auth()->user()->role->role_name ?? 'User' }}</span>.
            Gunakan menu di samping untuk mengelola sistem absensi digital.
        </p>
    </div>
    <div class="ms-auto text-end d-none d-md-block">
        <span class="badge bg-white bg-opacity-25 text-dark px-3 py-2">
            <i class="ti ti-calendar me-1"></i>{{ \Carbon\Carbon::now()->translatedFormat('l, d F Y') }}
        </span>
    </div>
</div>

<!-- Stat Cards -->
<div class="row g-3 mb-4">
    <div class="col-sm-6 col-xl-3">
        <div class="stat-card d-flex align-items-center gap-3">
            <div class="stat-icon bg-primary-light text-primary">
                <i class="ti ti-users"></i>
            </div>
            <div>
                <div class="stat-label">Total Guru</div>
                <div class="stat-value">{{ $guru ?? 0 }}</div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="stat-card d-flex align-items-center gap-3">
            <div class="stat-icon bg-accent-light text-accent">
                <i class="ti ti-school"></i>
            </div>
            <div>
                <div class="stat-label">Total Siswa</div>
                <div class="stat-value">{{ $siswa ?? 0 }}</div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="stat-card d-flex align-items-center gap-3">
            <div class="stat-icon" style="background:#fef3c7;color:#d97706;">
                <i class="ti ti-building"></i>
            </div>
            <div>
                <div class="stat-label">Total Kelas</div>
                <div class="stat-value">{{ $kelas ?? 0 }}</div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="stat-card d-flex align-items-center gap-3">
            <div class="stat-icon" style="background:#ede9fe;color:#7c3aed;">
                <i class="ti ti-clipboard-check"></i>
            </div>
            <div>
                <div class="stat-label">Total Absensi</div>
                <div class="stat-value">{{ $absensi ?? 0 }}</div>
            </div>
        </div>
    </div>
</div>

<!-- Quick Actions & Info -->
<div class="row g-3 mb-4">
    <div class="col-lg-6">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title"><i class="ti ti-bolt me-2 text-warning"></i>Aksi Cepat</h5>
            </div>
            <div class="card-body">
                <div class="row g-2">
                    <div class="col-6">
                        <a href="{{ route('absensi.rekap-bulanan') }}" class="quick-menu-card">
                            <div class="qm-icon bg-primary"><i class="ti ti-clipboard-check"></i></div>
                            <div class="qm-label">Rekap Absensi</div>
                        </a>
                    </div>
                    <div class="col-6">
                        <a href="{{ route('agenda_kelas.index') }}" class="quick-menu-card">
                            <div class="qm-icon bg-accent"><i class="ti ti-calendar-event"></i></div>
                            <div class="qm-label">Lihat Agenda</div>
                        </a>
                    </div>
                    <div class="col-6">
                        <a href="{{ route('jam_belajar.index') }}" class="quick-menu-card">
                            <div class="qm-icon" style="background:#0ea5e9;"><i class="ti ti-clock"></i></div>
                            <div class="qm-label">Jam Belajar</div>
                        </a>
                    </div>
                    <div class="col-6">
                        <a href="{{ route('absensi.index') }}" class="quick-menu-card">
                            <div class="qm-icon" style="background:#16a34a;"><i class="ti ti-clipboard-check"></i></div>
                            <div class="qm-label">Absensi</div>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-6">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title"><i class="ti ti-info-circle me-2 text-primary"></i>Informasi Hari Ini</h5>
            </div>
            <div class="card-body">
                <div class="d-flex align-items-center py-2 border-bottom">
                    <span class="avatar avatar-sm rounded bg-primary-light text-primary me-3">
                        <i class="ti ti-calendar"></i>
                    </span>
                    <div>
                        <div class="fw-medium" style="font-size:0.85rem;">Tanggal</div>
                        <div class="text-muted small">{{ \Carbon\Carbon::now()->translatedFormat('l, d F Y') }}</div>
                    </div>
                </div>
                <div class="d-flex align-items-center py-2 border-bottom">
                    <span class="avatar avatar-sm rounded bg-accent-light text-accent me-3">
                        <i class="ti ti-clock"></i>
                    </span>
                    <div>
                        <div class="fw-medium" style="font-size:0.85rem;">Waktu Sekarang</div>
                        <div class="text-muted small" id="currentTime">{{ date('H:i:s') }} WIB</div>
                    </div>
                </div>
                <div class="d-flex align-items-center py-2">
                    <span class="avatar avatar-sm rounded me-3" style="background:#fef3c7;color:#d97706;">
                        <i class="ti ti-book"></i>
                    </span>
                    <div>
                        <div class="fw-medium" style="font-size:0.85rem;">Tahun Ajaran</div>
                        <div class="text-muted small">{{ $tahunAjaran ?? 'Belum Dipilih' }} — {{ $semestrName ?? 'Belum Dipilih' }}</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Kehadiran Ringkasan -->
<div class="card">
    <div class="card-header">
        <h5 class="card-title"><i class="ti ti-chart-bar me-2 text-primary"></i>Ringkasan Kehadiran Minggu Ini</h5>
    </div>
    <div class="card-body">
        <div class="row g-3">
            <div class="col-md-3 col-6">
                <div class="rounded-3 p-3 text-center" style="background: #d1fae5;">
                    <div class="h2 mb-1 text-accent fw-bold">{{ $hadir ?? 0 }}</div>
                    <div class="text-muted small fw-medium"><i class="ti ti-check me-1"></i>Hadir</div>
                </div>
            </div>
            <div class="col-md-3 col-6">
                <div class="rounded-3 p-3 text-center" style="background: #fef3c7;">
                    <div class="h2 mb-1 fw-bold" style="color:#d97706;">{{ $izin ?? 0 }}</div>
                    <div class="text-muted small fw-medium"><i class="ti ti-file-text me-1"></i>Izin</div>
                </div>
            </div>
            <div class="col-md-3 col-6">
                <div class="rounded-3 p-3 text-center" style="background: #dbeafe;">
                    <div class="h2 mb-1 text-primary fw-bold">{{ $sakit ?? 0 }}</div>
                    <div class="text-muted small fw-medium"><i class="ti ti-mood-sick me-1"></i>Sakit</div>
                </div>
            </div>
            <div class="col-md-3 col-6">
                <div class="rounded-3 p-3 text-center" style="background: #fee2e2;">
                    <div class="h2 mb-1 text-danger fw-bold">{{ $alpa ?? 0 }}</div>
                    <div class="text-muted small fw-medium"><i class="ti ti-x me-1"></i>Alpa</div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('js')
<script>
    function updateTime() {
        const now = new Date();
        const timeString = now.toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit', second: '2-digit' }) + ' WIB';
        const el = document.getElementById('currentTime');
        if (el) el.textContent = timeString;
    }
    setInterval(updateTime, 1000);
</script>
@endpush