@extends('layouts.app', ['pageSlug' => 'dashboard-siswa'])

@section('title','Dashboard Siswa')

@section('content')
<div class="page-header mb-4">
    <h2 class="page-title">Dashboard Siswa</h2>
    <div class="text-muted mt-1">
        Selamat datang di Dashboard Siswa. Anda dapat melihat absensi, agenda kelas, dan progress kehadiran di sini.
    </div>
</div>

<div class="alert alert-info d-flex align-items-center mb-4">
    <i class="ti ti-calendar-event me-3" style="font-size: 24px;"></i>
    <div>
        <strong>Tahun Ajaran:</strong> {{ $tahunAjaran ?? 'Belum ada tahun ajaran aktif' }} | 
        <strong>Semester:</strong> {{ $semestrName ?? 'Belum ada semester aktif' }}
    </div>
</div>

<div class="row row-cards mb-4">
    <div class="col-sm-6 col-lg-3">
        <div class="card card-sm">
            <div class="card-body">
                <div class="row align-items-center">
                    <div class="col-auto">
                        <span class="bg-primary text-white avatar">
                            <i class="ti ti-calendar-check"></i>
                        </span>
                    </div>
                    <div class="col">
                        <div class="font-weight-medium">{{ $attendanceSummary['present_percent'] ?? 0 }}%</div>
                        <div class="text-muted">Kehadiran Semester Ini</div>
                    </div>
                </div>
                <div class="mt-3">
                    <div class="progress" style="height: 8px;">
                        <div class="progress-bar bg-primary" role="progressbar" style="width: {{ $attendanceSummary['present_percent'] ?? 0 }}%;" aria-valuenow="{{ $attendanceSummary['present_percent'] ?? 0 }}" aria-valuemin="0" aria-valuemax="100"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-sm-6 col-lg-3">
        <div class="card card-sm">
            <div class="card-body">
                <div class="row align-items-center">
                    <div class="col-auto">
                        <span class="bg-success text-white avatar">
                            <i class="ti ti-user-check"></i>
                        </span>
                    </div>
                    <div class="col">
                        <div class="font-weight-medium">{{ $attendanceSummary['hadir'] ?? 0 }}</div>
                        <div class="text-muted">Hadir</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-sm-6 col-lg-3">
        <div class="card card-sm">
            <div class="card-body">
                <div class="row align-items-center">
                    <div class="col-auto">
                        <span class="bg-warning text-white avatar">
                            <i class="ti ti-alert-circle"></i>
                        </span>
                    </div>
                    <div class="col">
                        <div class="font-weight-medium">{{ $attendanceSummary['terlambat'] ?? 0 }}</div>
                        <div class="text-muted">Terlambat</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-sm-6 col-lg-3">
        <div class="card card-sm">
            <div class="card-body">
                <div class="row align-items-center">
                    <div class="col-auto">
                        <span class="bg-danger text-white avatar">
                            <i class="ti ti-circle-x"></i>
                        </span>
                    </div>
                    <div class="col">
                        <div class="font-weight-medium">{{ $attendanceSummary['alpa'] ?? 0 }}</div>
                        <div class="text-muted">Alpa / Absen</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row mb-4">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Progress Absensi</h3>
            </div>
            <div class="card-body">
                @if(($attendanceSummary['total'] ?? 0) > 0)
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <div class="d-flex justify-content-between">
                                <div><strong>Hadir</strong></div>
                                <div>{{ $attendanceSummary['hadir'] }}</div>
                            </div>
                            <div class="progress mt-2" style="height: 8px;">
                                <div class="progress-bar bg-success" role="progressbar" style="width: {{ round(($attendanceSummary['hadir'] / $attendanceSummary['total']) * 100, 2) }}%;"></div>
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <div class="d-flex justify-content-between">
                                <div><strong>Terlambat</strong></div>
                                <div>{{ $attendanceSummary['terlambat'] }}</div>
                            </div>
                            <div class="progress mt-2" style="height: 8px;">
                                <div class="progress-bar bg-warning" role="progressbar" style="width: {{ round(($attendanceSummary['terlambat'] / $attendanceSummary['total']) * 100, 2) }}%;"></div>
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <div class="d-flex justify-content-between">
                                <div><strong>Izin</strong></div>
                                <div>{{ $attendanceSummary['izin'] }}</div>
                            </div>
                            <div class="progress mt-2" style="height: 8px;">
                                <div class="progress-bar bg-info" role="progressbar" style="width: {{ round(($attendanceSummary['izin'] / $attendanceSummary['total']) * 100, 2) }}%;"></div>
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <div class="d-flex justify-content-between">
                                <div><strong>Sakit</strong></div>
                                <div>{{ $attendanceSummary['sakit'] }}</div>
                            </div>
                            <div class="progress mt-2" style="height: 8px;">
                                <div class="progress-bar bg-secondary" role="progressbar" style="width: {{ round(($attendanceSummary['sakit'] / $attendanceSummary['total']) * 100, 2) }}%;"></div>
                            </div>
                        </div>
                    </div>
                @else
                    <div class="text-muted">Belum ada data absensi untuk tahun ajaran dan semester aktif.</div>
                @endif
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Jabatan Kelas</h3>
            </div>
            <div class="card-body">
                @if($classPositionLabel)
                    <div class="badge bg-primary mb-3">{{ $classPositionLabel }}</div>
                    <p class="mb-0">Sebagai siswa jabatan, Anda dapat mengakses fitur Agenda Kelas dan Absensi Kelas sesuai hak jabatan.</p>
                @else
                    <div class="text-muted">Anda belum memiliki jabatan kelas aktif. Hubungi wali kelas atau admin untuk penempatan jabatan.</div>
                @endif
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Akses Cepat</h3>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-6 col-lg-3">
                        <a href="{{ route('absensi.index') }}" class="btn btn-outline-success w-100 py-4" style="height: auto;">
                            <div class="text-center">
                                <i class="ti ti-check" style="font-size: 36px;"></i>
                                <div class="mt-3 fw-bold">Absensi</div>
                                <div class="text-muted small">Lihat dan kelola absensi</div>
                            </div>
                        </a>
                    </div>
                    @if($isSiswaOfficer)
                        <div class="col-md-6 col-lg-3">
                            <a href="{{ route('absensi.create', ['kelas_id' => auth()->user()->siswa->kelas_id ?? '']) }}" class="btn btn-success w-100 py-4" style="height: auto;">
                                <div class="text-center">
                                    <i class="ti ti-plus" style="font-size: 36px;"></i>
                                    <div class="mt-3 fw-bold">Buat Absensi</div>
                                    <div class="text-muted small">Input absensi kelas</div>
                                </div>
                            </a>
                        </div>
                    @endif
                    <div class="col-md-6 col-lg-3">
                        <a href="{{ route('agenda_kelas.index') }}" class="btn btn-outline-warning w-100 py-4" style="height: auto;">
                            <div class="text-center">
                                <i class="ti ti-file-text" style="font-size: 36px;"></i>
                                <div class="mt-3 fw-bold">Agenda Kelas</div>
                                <div class="text-muted small">Lihat agenda kelas</div>
                            </div>
                        </a>
                    </div>
                    @if($isSiswaOfficer)
                        <div class="col-md-6 col-lg-3">
                            <a href="{{ route('agenda_kelas.create', ['kelas_id' => auth()->user()->siswa->kelas_id ?? '']) }}" class="btn btn-warning w-100 py-4" style="height: auto;">
                                <div class="text-center">
                                    <i class="ti ti-plus" style="font-size: 36px;"></i>
                                    <div class="mt-3 fw-bold">Buat Agenda</div>
                                    <div class="text-muted small">Tambah agenda kelas</div>
                                </div>
                            </a>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
