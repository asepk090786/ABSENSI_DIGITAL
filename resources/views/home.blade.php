@extends('layouts.app', ['pageSlug' => 'dashboard'])

@section('title','Dashboard')

@section('content')
<!-- Page Header - Clean Style -->
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <small class="text-muted text-uppercase fw-medium">Overview</small>
        <h2 class="mb-0 fw-bold">Dashboard</h2>
    </div>
    <div>
        <span class="badge bg-light text-dark px-3 py-2">
            <i class="ti ti-calendar me-1"></i>{{ date('d F Y') }}
        </span>
    </div>
</div>

<!-- Welcome Card - Softer Colors -->
<div class="card border-0 shadow-sm mb-4" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
    <div class="card-body py-4">
        <div class="d-flex align-items-center">
            <div class="me-3">
                <span class="avatar avatar-lg rounded-circle bg-white text-primary">
                    <i class="ti ti-user" style="font-size: 1.5rem;"></i>
                </span>
            </div>
            <div class="text-white">
                <h3 class="mb-1 text-white">Selamat Datang, {{ auth()->user()->name ?? 'User' }}! 👋</h3>
                <div class="opacity-75">
                    Anda masuk sebagai <span class="badge bg-white text-primary">Administrator</span>. 
                    Gunakan menu di samping untuk mengelola sistem absensi digital sekolah.
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Stats Cards -->
<div class="row g-3 mb-4">
    <!-- Guru Card -->
    <div class="col-sm-6 col-lg-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="flex-shrink-0">
                        <span class="avatar avatar-lg rounded" style="background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%);">
                            <i class="ti ti-users text-white" style="font-size: 1.5rem;"></i>
                        </span>
                    </div>
                    <div class="flex-grow-1 ms-3">
                        <div class="text-muted small">Total Guru</div>
                        <div class="h2 mb-0">{{ $guru ?? 0 }}</div>
                    </div>
                </div>
            </div>
            <div class="card-footer bg-transparent border-0 pt-0">
                <a href="{{ route('guru.index') }}" class="text-primary small text-decoration-none">
                    Lihat Detail <i class="ti ti-chevron-right"></i>
                </a>
            </div>
        </div>
    </div>
    
    <!-- Siswa Card -->
    <div class="col-sm-6 col-lg-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="flex-shrink-0">
                        <span class="avatar avatar-lg rounded" style="background: linear-gradient(135deg, #10b981 0%, #059669 100%);">
                            <i class="ti ti-school text-white" style="font-size: 1.5rem;"></i>
                        </span>
                    </div>
                    <div class="flex-grow-1 ms-3">
                        <div class="text-muted small">Total Siswa</div>
                        <div class="h2 mb-0">{{ $siswa ?? 0 }}</div>
                    </div>
                </div>
            </div>
            <div class="card-footer bg-transparent border-0 pt-0">
                <a href="{{ route('siswa.index') }}" class="text-success small text-decoration-none">
                    Lihat Detail <i class="ti ti-chevron-right"></i>
                </a>
            </div>
        </div>
    </div>
    
    <!-- Kelas Card -->
    <div class="col-sm-6 col-lg-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="flex-shrink-0">
                        <span class="avatar avatar-lg rounded" style="background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);">
                            <i class="ti ti-building text-white" style="font-size: 1.5rem;"></i>
                        </span>
                    </div>
                    <div class="flex-grow-1 ms-3">
                        <div class="text-muted small">Total Kelas</div>
                        <div class="h2 mb-0">{{ $kelas ?? 0 }}</div>
                    </div>
                </div>
            </div>
            <div class="card-footer bg-transparent border-0 pt-0">
                <a href="{{ route('kelas.index') }}" class="text-warning small text-decoration-none">
                    Lihat Detail <i class="ti ti-chevron-right"></i>
                </a>
            </div>
        </div>
    </div>
    
    <!-- Absensi Card -->
    <div class="col-sm-6 col-lg-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="flex-shrink-0">
                        <span class="avatar avatar-lg rounded" style="background: linear-gradient(135deg, #8b5cf6 0%, #7c3aed 100%);">
                            <i class="ti ti-clipboard-check text-white" style="font-size: 1.5rem;"></i>
                        </span>
                    </div>
                    <div class="flex-grow-1 ms-3">
                        <div class="text-muted small">Total Absensi</div>
                        <div class="h2 mb-0">{{ $absensi ?? 0 }}</div>
                    </div>
                </div>
            </div>
            <div class="card-footer bg-transparent border-0 pt-0">
                <a href="{{ route('absensi.index') }}" class="text-purple small text-decoration-none">
                    Lihat Detail <i class="ti ti-chevron-right"></i>
                </a>
            </div>
        </div>
    </div>
</div>

<!-- Quick Actions & Recent Activity -->
<div class="row g-3 mb-4">
    <!-- Quick Actions -->
    <div class="col-lg-6">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-transparent border-0">
                <h5 class="card-title mb-0">
                    <i class="ti ti-bolt me-2 text-warning"></i>Aksi Cepat
                </h5>
            </div>
            <div class="card-body pt-0">
                <div class="row g-2">
                    <div class="col-6">
                        <a href="{{ route('absensi.index') }}" class="btn btn-light border w-100 py-3 text-start">
                            <i class="ti ti-clipboard-check text-primary me-2"></i>Input Absensi
                        </a>
                    </div>
                    <div class="col-6">
                        <a href="{{ route('agenda_kelas.index') }}" class="btn btn-light border w-100 py-3 text-start">
                            <i class="ti ti-calendar-event text-success me-2"></i>Lihat Agenda
                        </a>
                    </div>
                    <div class="col-6">
                        <a href="{{ route('jam_belajar.index') }}" class="btn btn-light border w-100 py-3 text-start">
                            <i class="ti ti-clock text-info me-2"></i>Jam Belajar
                        </a>
                    </div>
                    <div class="col-6">
                        <a href="#" class="btn btn-light border w-100 py-3 text-start">
                            <i class="ti ti-report text-warning me-2"></i>Laporan
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Info Hari Ini -->
    <div class="col-lg-6">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-transparent border-0">
                <h5 class="card-title mb-0">
                    <i class="ti ti-info-circle me-2 text-primary"></i>Informasi Hari Ini
                </h5>
            </div>
            <div class="card-body pt-0">
                <div class="d-flex align-items-center py-2 border-bottom">
                    <span class="avatar avatar-sm rounded me-3" style="background: rgba(59, 130, 246, 0.1);">
                        <i class="ti ti-calendar text-primary"></i>
                    </span>
                    <div>
                        <div class="fw-medium">Tanggal</div>
                        <div class="text-muted small">{{ \Carbon\Carbon::now()->translatedFormat('l, d F Y') }}</div>
                    </div>
                </div>
                <div class="d-flex align-items-center py-2 border-bottom">
                    <span class="avatar avatar-sm rounded me-3" style="background: rgba(16, 185, 129, 0.1);">
                        <i class="ti ti-clock text-success"></i>
                    </span>
                    <div>
                        <div class="fw-medium">Waktu Sekarang</div>
                        <div class="text-muted small" id="currentTime">{{ date('H:i:s') }} WIB</div>
                    </div>
                </div>
                <div class="d-flex align-items-center py-2">
                    <span class="avatar avatar-sm rounded me-3" style="background: rgba(245, 158, 11, 0.1);">
                        <i class="ti ti-book text-warning"></i>
                    </span>
                    <div>
                        <div class="fw-medium">Tahun Ajaran</div>
                        <div class="text-muted small">{{ $tahunAjaran ?? 'Belum Dipilih' }} - {{ $semestrName ?? 'Belum Dipilih' }}</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Attendance Summary -->
<div class="row g-3">
    <div class="col-12">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-transparent border-0">
                <h5 class="card-title mb-0">
                    <i class="ti ti-chart-bar me-2 text-primary"></i>Ringkasan Kehadiran Minggu Ini
                </h5>
            </div>
            <div class="card-body pt-0">
                <div class="row g-3">
                    <div class="col-md-3 col-6">
                        <div class="rounded-3 p-3 text-center" style="background: rgba(16, 185, 129, 0.1);">
                            <div class="h2 mb-1 text-success">{{ $hadir ?? 0 }}</div>
                            <div class="text-muted small">
                                <i class="ti ti-check me-1"></i>Hadir
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3 col-6">
                        <div class="rounded-3 p-3 text-center" style="background: rgba(245, 158, 11, 0.1);">
                            <div class="h2 mb-1 text-warning">{{ $izin ?? 0 }}</div>
                            <div class="text-muted small">
                                <i class="ti ti-file-text me-1"></i>Izin
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3 col-6">
                        <div class="rounded-3 p-3 text-center" style="background: rgba(59, 130, 246, 0.1);">
                            <div class="h2 mb-1 text-info">{{ $sakit ?? 0 }}</div>
                            <div class="text-muted small">
                                <i class="ti ti-mood-sick me-1"></i>Sakit
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3 col-6">
                        <div class="rounded-3 p-3 text-center" style="background: rgba(239, 68, 68, 0.1);">
                            <div class="h2 mb-1 text-danger">{{ $alpa ?? 0 }}</div>
                            <div class="text-muted small">
                                <i class="ti ti-x me-1"></i>Alpa
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('js')
<script>
    // Update waktu secara realtime
    function updateTime() {
        const now = new Date();
        const timeString = now.toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit', second: '2-digit' }) + ' WIB';
        document.getElementById('currentTime').textContent = timeString;
    }
    setInterval(updateTime, 1000);
</script>
@endpush
