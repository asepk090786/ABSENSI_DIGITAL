@extends('layouts.app', ['pageSlug' => 'dashboard-guru'])

@section('title','Dashboard Guru')

@section('content')
<div class="page-header mb-4">
    <h2 class="page-title">Dashboard Guru</h2>
    <div class="text-muted mt-1">
        Selamat datang di Dashboard Guru. Anda dapat melihat jadwal mengajar, absensi, agenda pembelajaran, dan daftar nilai di sini.
    </div>
</div>

<!-- Info Tahun Ajaran & Semester Aktif -->
<div class="alert alert-info d-flex align-items-center mb-4">
    <i class="ti ti-calendar-event me-3" style="font-size: 24px;"></i>
    <div>
        <strong>Tahun Ajaran:</strong> {{ $tahunAjaran ?? 'Belum ada tahun ajaran aktif' }} | 
        <strong>Semester:</strong> {{ $semestrName ?? 'Belum ada semester aktif' }}
    </div>
</div>

<!-- Quick Stats Cards -->
<div class="row row-cards mb-4">
    <!-- Jadwal Mengajar -->
    <div class="col-sm-6 col-lg-3">
        <div class="card card-sm">
            <div class="card-body">
                <div class="row align-items-center">
                    <div class="col-auto">
                        <span class="bg-primary text-white avatar">
                            <i class="ti ti-calendar-time"></i>
                        </span>
                    </div>
                    <div class="col">
                        <div class="font-weight-medium">
                            {{ $totalJadwal ?? 0 }} Jadwal
                        </div>
                        <div class="text-muted">
                            Total Jadwal Mengajar
                        </div>
                    </div>
                </div>
                <div class="mt-2">
                    <small class="text-success">
                        <i class="ti ti-clock me-1"></i>{{ $jadwalHariIni ?? 0 }} jadwal hari ini
                    </small>
                </div>
            </div>
        </div>
    </div>

    <!-- Absensi -->
    <div class="col-sm-6 col-lg-3">
        <div class="card card-sm">
            <div class="card-body">
                <div class="row align-items-center">
                    <div class="col-auto">
                        <span class="bg-success text-white avatar">
                            <i class="ti ti-check"></i>
                        </span>
                    </div>
                    <div class="col">
                        <div class="font-weight-medium">
                            {{ $totalAbsensiGuru ?? 0 }} Absensi
                        </div>
                        <div class="text-muted">
                            Total Absensi Terisi
                        </div>
                    </div>
                </div>
                <div class="mt-2">
                    <small class="text-primary">
                        <i class="ti ti-calendar-check me-1"></i>{{ $absensiHariIni ?? 0 }} absensi hari ini
                    </small>
                </div>
            </div>
        </div>
    </div>

    <!-- Agenda Pembelajaran -->
    <div class="col-sm-6 col-lg-3">
        <div class="card card-sm">
            <div class="card-body">
                <div class="row align-items-center">
                    <div class="col-auto">
                        <span class="bg-warning text-white avatar">
                            <i class="ti ti-file-text"></i>
                        </span>
                    </div>
                    <div class="col">
                        <div class="font-weight-medium">
                            {{ $totalAgenda ?? 0 }} Agenda
                        </div>
                        <div class="text-muted">
                            Total Agenda Pembelajaran
                        </div>
                    </div>
                </div>
                <div class="mt-2">
                    <small class="text-info">
                        <i class="ti ti-calendar-week me-1"></i>{{ $agendaMingguIni ?? 0 }} agenda minggu ini
                    </small>
                </div>
            </div>
        </div>
    </div>

    <!-- Nilai -->
    <div class="col-sm-6 col-lg-3">
        <div class="card card-sm">
            <div class="card-body">
                <div class="row align-items-center">
                    <div class="col-auto">
                        <span class="bg-danger text-white avatar">
                            <i class="ti ti-report-analytics"></i>
                        </span>
                    </div>
                    <div class="col">
                        <div class="font-weight-medium">
                            {{ $totalNilai ?? 0 }} Nilai
                        </div>
                        <div class="text-muted">
                            Total Nilai Terinput
                        </div>
                    </div>
                </div>
                <div class="mt-2">
                    <small class="text-secondary">
                        <i class="ti ti-school me-1"></i>{{ $kelasYangDiajar ?? 0 }} kelas diajar
                    </small>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Quick Access Menu -->
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Menu Akses Cepat</h3>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <!-- Jadwal Mengajar -->
                    <div class="col-md-6 col-lg-3">
                        <a href="{{ route('jadwal-kbm.index') }}" class="btn btn-outline-primary w-100 py-4" style="height: auto;">
                            <div class="text-center">
                                <i class="ti ti-calendar-time" style="font-size: 48px;"></i>
                                <div class="mt-3 fw-bold">Jadwal Mengajar</div>
                                <div class="text-muted small">Lihat jadwal KBM Anda</div>
                            </div>
                        </a>
                    </div>

                    <!-- Absensi Siswa -->
                    <div class="col-md-6 col-lg-3">
                        <a href="{{ route('absensi.index') }}" class="btn btn-outline-success w-100 py-4" style="height: auto;">
                            <div class="text-center">
                                <i class="ti ti-check" style="font-size: 48px;"></i>
                                <div class="mt-3 fw-bold">Absensi Siswa</div>
                                <div class="text-muted small">Input absensi kelas</div>
                            </div>
                        </a>
                    </div>

                    <!-- Agenda Pembelajaran -->
                    <div class="col-md-6 col-lg-3">
                        <a href="{{ route('agenda_kelas.index') }}" class="btn btn-outline-warning w-100 py-4" style="height: auto;">
                            <div class="text-center">
                                <i class="ti ti-file-text" style="font-size: 48px;"></i>
                                <div class="mt-3 fw-bold">Agenda Pembelajaran</div>
                                <div class="text-muted small">Kelola agenda mengajar</div>
                            </div>
                        </a>
                    </div>

                    <!-- Daftar Nilai -->
                    <div class="col-md-6 col-lg-3">
                        <a href="{{ route('nilai.index') }}" class="btn btn-outline-danger w-100 py-4" style="height: auto;">
                            <div class="text-center">
                                <i class="ti ti-report-analytics" style="font-size: 48px;"></i>
                                <div class="mt-3 fw-bold">Daftar Nilai</div>
                                <div class="text-muted small">Input & kelola nilai siswa</div>
                            </div>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Additional Info -->
<div class="row mt-4">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="ti ti-info-circle me-2"></i>Informasi
                </h3>
            </div>
            <div class="card-body">
                <div class="list-group list-group-flush">
                    <div class="list-group-item">
                        <div class="row align-items-center">
                            <div class="col-auto">
                                <i class="ti ti-calendar-check text-primary"></i>
                            </div>
                            <div class="col">
                                <div class="font-weight-medium">Jadwal Hari Ini</div>
                                <div class="text-muted small">{{ $jadwalHariIni ?? 0 }} jam pelajaran</div>
                            </div>
                        </div>
                    </div>
                    <div class="list-group-item">
                        <div class="row align-items-center">
                            <div class="col-auto">
                                <i class="ti ti-users text-success"></i>
                            </div>
                            <div class="col">
                                <div class="font-weight-medium">Kelas yang Diajar</div>
                                <div class="text-muted small">{{ $kelasYangDiajar ?? 0 }} kelas</div>
                            </div>
                        </div>
                    </div>
                    <div class="list-group-item">
                        <div class="row align-items-center">
                            <div class="col-auto">
                                <i class="ti ti-notebook text-warning"></i>
                            </div>
                            <div class="col">
                                <div class="font-weight-medium">Agenda Minggu Ini</div>
                                <div class="text-muted small">{{ $agendaMingguIni ?? 0 }} agenda</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="ti ti-bell me-2"></i>Tips & Panduan
                </h3>
            </div>
            <div class="card-body">
                <div class="list-group list-group-flush">
                    <div class="list-group-item">
                        <div class="d-flex align-items-start">
                            <span class="badge bg-primary me-2">1</span>
                            <div>
                                <strong>Absensi Tepat Waktu</strong>
                                <p class="text-muted small mb-0">Lakukan input absensi di setiap pertemuan untuk data yang akurat.</p>
                            </div>
                        </div>
                    </div>
                    <div class="list-group-item">
                        <div class="d-flex align-items-start">
                            <span class="badge bg-success me-2">2</span>
                            <div>
                                <strong>Isi Agenda Pembelajaran</strong>
                                <p class="text-muted small mb-0">Catat materi dan kegiatan pembelajaran setiap hari.</p>
                            </div>
                        </div>
                    </div>
                    <div class="list-group-item">
                        <div class="d-flex align-items-start">
                            <span class="badge bg-warning me-2">3</span>
                            <div>
                                <strong>Update Nilai Berkala</strong>
                                <p class="text-muted small mb-0">Input nilai tugas, UTS, dan UAS secara berkala.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
