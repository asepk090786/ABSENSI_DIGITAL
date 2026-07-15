@extends('layouts.app')

@section('title','Dashboard Siswa')

@section('content')
<div class="welcome-banner d-flex align-items-center gap-3">
    <div class="flex-shrink-0">
        <span class="avatar rounded-circle bg-white text-primary" style="width:2.8rem;height:2.8rem;font-size:1.2rem;display:flex;align-items:center;justify-content:center;">
            <i class="ti ti-school"></i>
        </span>
    </div>
    <div>
        <h3 class="mb-0 text-white">Dashboard Siswa</h3>
        <p class="mb-0 opacity-75" style="font-size:0.82rem;">Pantau absensi, agenda kelas, dan progress kehadiran Anda di sini.</p>
    </div>
</div>

<div class="alert alert-info d-flex align-items-center mb-3">
    <i class="ti ti-calendar-event me-3" style="font-size:1.5rem;"></i>
    <div>
        <strong>Tahun Ajaran:</strong> {{ $tahunAjaran ?? 'Belum ada tahun ajaran aktif' }} |
        <strong>Semester:</strong> {{ $semestrName ?? 'Belum ada semester aktif' }}
    </div>
</div>

<!-- Stat Cards -->
<div class="row g-3 mb-4">
    <div class="col-sm-4 col-md-2">
        <div class="stat-card text-center">
            <div class="stat-icon bg-primary-light text-primary mx-auto mb-2">
                <i class="ti ti-calendar-check"></i>
            </div>
            <div class="stat-value text-primary">{{ $attendanceSummary['present_percent'] ?? 0 }}%</div>
            <div class="stat-label mb-2">Kehadiran</div>
            <div class="progress" style="height:6px;">
                <div class="progress-bar bg-primary" style="width:{{ $attendanceSummary['present_percent'] ?? 0 }}%;"></div>
            </div>
        </div>
    </div>
    <div class="col-sm-4 col-md-2">
        <div class="stat-card text-center">
            <div class="stat-icon bg-accent-light text-accent mx-auto mb-2"><i class="ti ti-user-check"></i></div>
            <div class="stat-value text-accent">{{ $attendanceSummary['hadir'] ?? 0 }}</div>
            <div class="stat-label">Hadir</div>
        </div>
    </div>
    <div class="col-sm-4 col-md-2">
        <div class="stat-card text-center">
            <div class="stat-icon mx-auto mb-2" style="background:#fef3c7;color:#d97706;"><i class="ti ti-alert-circle"></i></div>
            <div class="stat-value" style="color:#d97706;">{{ $attendanceSummary['terlambat'] ?? 0 }}</div>
            <div class="stat-label">Terlambat</div>
        </div>
    </div>
    <div class="col-sm-4 col-md-2">
        <div class="stat-card text-center">
            <div class="stat-icon mx-auto mb-2" style="background:#dbeafe;color:#0ea5e9;"><i class="ti ti-medical-cross"></i></div>
            <div class="stat-value" style="color:#0ea5e9;">{{ $attendanceSummary['izin'] ?? 0 }}</div>
            <div class="stat-label">Izin</div>
        </div>
    </div>
    <div class="col-sm-4 col-md-2">
        <div class="stat-card text-center">
            <div class="stat-icon mx-auto mb-2" style="background:#ede9fe;color:#7c3aed;"><i class="ti ti-heartbeat"></i></div>
            <div class="stat-value" style="color:#7c3aed;">{{ $attendanceSummary['sakit'] ?? 0 }}</div>
            <div class="stat-label">Sakit</div>
        </div>
    </div>
    <div class="col-sm-4 col-md-2">
        <div class="stat-card text-center">
            <div class="stat-icon mx-auto mb-2" style="background:#fee2e2;color:#ef4444;"><i class="ti ti-circle-x"></i></div>
            <div class="stat-value text-danger">{{ $attendanceSummary['alpa'] ?? 0 }}</div>
            <div class="stat-label">Alpa</div>
        </div>
    </div>
</div>

<div class="row g-3 mb-4">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title"><i class="ti ti-chart-bar me-2 text-primary"></i>Progress Absensi</h5>
            </div>
            <div class="card-body">
                @if(($attendanceSummary['total'] ?? 0) > 0)
                    <div class="row g-3">
                        <div class="col-md-6">
                            <div class="d-flex justify-content-between mb-1">
                                <span class="fw-medium" style="font-size:0.82rem;">Hadir</span>
                                <span class="fw-bold text-accent">{{ $attendanceSummary['hadir'] }}</span>
                            </div>
                            <div class="progress" style="height:8px;"><div class="progress-bar bg-accent" style="width:{{ round(($attendanceSummary['hadir'] / $attendanceSummary['total']) * 100, 1) }}%;"></div></div>
                        </div>
                        <div class="col-md-6">
                            <div class="d-flex justify-content-between mb-1">
                                <span class="fw-medium" style="font-size:0.82rem;">Terlambat</span>
                                <span class="fw-bold" style="color:#d97706;">{{ $attendanceSummary['terlambat'] }}</span>
                            </div>
                            <div class="progress" style="height:8px;"><div class="progress-bar bg-warning" style="width:{{ round(($attendanceSummary['terlambat'] / $attendanceSummary['total']) * 100, 1) }}%;"></div></div>
                        </div>
                        <div class="col-md-6">
                            <div class="d-flex justify-content-between mb-1">
                                <span class="fw-medium" style="font-size:0.82rem;">Izin</span>
                                <span class="fw-bold" style="color:#0ea5e9;">{{ $attendanceSummary['izin'] }}</span>
                            </div>
                            <div class="progress" style="height:8px;"><div class="progress-bar bg-info" style="width:{{ round(($attendanceSummary['izin'] / $attendanceSummary['total']) * 100, 1) }}%;"></div></div>
                        </div>
                        <div class="col-md-6">
                            <div class="d-flex justify-content-between mb-1">
                                <span class="fw-medium" style="font-size:0.82rem;">Sakit</span>
                                <span class="fw-bold" style="color:#7c3aed;">{{ $attendanceSummary['sakit'] }}</span>
                            </div>
                            <div class="progress" style="height:8px;"><div class="progress-bar bg-purple" style="width:{{ round(($attendanceSummary['sakit'] / $attendanceSummary['total']) * 100, 1) }}%;"></div></div>
                        </div>
                    </div>
                @else
                    <div class="text-muted">Belum ada data absensi untuk tahun ajaran dan semester aktif.</div>
                @endif
            </div>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="card h-100">
            <div class="card-header">
                <h5 class="card-title"><i class="ti ti-id me-2 text-primary"></i>Jabatan Kelas</h5>
            </div>
            <div class="card-body">
                @if($classPositionLabel)
                    <div class="badge bg-primary mb-2 px-3 py-2">{{ $classPositionLabel }}</div>
                    <p class="mb-0" style="font-size:0.85rem;">Anda dapat mengakses fitur Agenda Kelas dan Absensi Kelas sesuai hak jabatan.</p>
                @else
                    <div class="text-muted" style="font-size:0.85rem;">Anda belum memiliki jabatan kelas aktif. Hubungi wali kelas atau admin untuk penempatan jabatan.</div>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Quick Access -->
<div class="row g-3">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title"><i class="ti ti-apps me-2 text-primary"></i>Akses Cepat</h5>
            </div>
            <div class="card-body">
                <div class="row g-2">
                    <div class="col-6 col-md-3">
                        <a href="{{ route('absensi.index') }}" class="quick-menu-card">
                            <div class="qm-icon bg-accent"><i class="ti ti-check"></i></div>
                            <div class="qm-label">Absensi</div>
                        </a>
                    </div>
                    @if($isSiswaOfficer)
                        <div class="col-6 col-md-3">
                            <a href="{{ route('absensi.create', ['kelas_id' => auth()->user()->siswa->kelas_id ?? '']) }}" class="quick-menu-card">
                                <div class="qm-icon bg-primary"><i class="ti ti-plus"></i></div>
                                <div class="qm-label">Buat Absensi</div>
                            </a>
                        </div>
                    @endif
                    <div class="col-6 col-md-3">
                        <a href="{{ route('agenda_kelas.index') }}" class="quick-menu-card">
                            <div class="qm-icon" style="background:#f59e0b;"><i class="ti ti-file-text"></i></div>
                            <div class="qm-label">Agenda Kelas</div>
                        </a>
                    </div>
                    @if($isSiswaOfficer)
                        <div class="col-6 col-md-3">
                            <a href="{{ route('agenda_kelas.create', ['kelas_id' => auth()->user()->siswa->kelas_id ?? '']) }}" class="quick-menu-card">
                                <div class="qm-icon" style="background:#ef4444;"><i class="ti ti-plus"></i></div>
                                <div class="qm-label">Buat Agenda</div>
                            </a>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection