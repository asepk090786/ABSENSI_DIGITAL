@extends('layouts.app')

@section('title','Dashboard Guru')

@section('content')
<div class="welcome-banner d-flex align-items-center gap-3">
    <div class="flex-shrink-0">
        <span class="avatar rounded-circle bg-white text-primary" style="width:2.8rem;height:2.8rem;font-size:1.2rem;display:flex;align-items:center;justify-content:center;">
            <i class="ti ti-user-star"></i>
        </span>
    </div>
    <div>
        <h3 class="mb-0 text-white">Dashboard Guru</h3>
        <p class="mb-0 opacity-75" style="font-size:0.82rem;">Selamat datang, {{ auth()->user()->name ?? 'Guru' }}. Kelola jadwal, absensi, agenda, dan nilai di sini.</p>
    </div>
</div>

<div class="alert alert-info d-flex align-items-center mb-3">
    <i class="ti ti-calendar-event me-3" style="font-size:1.5rem;"></i>
    <div>
        <strong>Tahun Ajaran:</strong> {{ $tahunAjaran ?? 'Belum ada tahun ajaran aktif' }} |
        <strong>Semester:</strong> {{ $semestrName ?? 'Belum ada semester aktif' }}
    </div>
</div>

@if(($isGuruBk ?? false) === true)
<div class="card mb-3">
    <div class="card-header">
        <h5 class="card-title">Kelas Binaan Guru BK</h5>
        <span class="badge bg-primary">{{ ($kelasBinaanBk ?? collect())->count() }} Kelas</span>
    </div>
    <div class="card-body">
        @if(($kelasBinaanBk ?? collect())->isEmpty())
            <div class="text-muted">Belum ada kelas binaan yang ditetapkan.</div>
        @else
            <div class="row g-2">
                @foreach($kelasBinaanBk as $kelasBinaan)
                    <div class="col-md-6 col-lg-4">
                        <a href="{{ route('guru_bk_layanan.menu', ['kelas' => $kelasBinaan->id]) }}" class="btn btn-outline-primary w-100 text-start py-3">
                            <div class="fw-bold">{{ $kelasBinaan->nama_kelas }}</div>
                            <div class="small text-muted">Tingkat: {{ $kelasBinaan->tingkat_kelas ?? '-' }} • {{ $kelasBinaan->total_siswa ?? 0 }} siswa</div>
                            <div class="small text-primary mt-1">Klik untuk lihat statistik kelas binaan</div>
                        </a>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
</div>
@endif

<!-- Stat Cards -->
<div class="row g-3 mb-4">
    <div class="col-sm-6 col-xl-3">
        <div class="stat-card d-flex align-items-center gap-3">
            <div class="stat-icon bg-primary-light text-primary">
                <i class="ti ti-calendar-time"></i>
            </div>
            <div>
                <div class="stat-label">{{ $totalJadwal ?? 0 }} Jadwal</div>
                <div class="text-muted small">Total Jadwal Mengajar</div>
                <div class="stat-change text-primary"><i class="ti ti-clock me-1"></i>{{ $jadwalHariIni ?? 0 }} hari ini</div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="stat-card d-flex align-items-center gap-3">
            <div class="stat-icon bg-accent-light text-accent">
                <i class="ti ti-check"></i>
            </div>
            <div>
                <div class="stat-label">{{ $totalAbsensiGuru ?? 0 }} Absensi</div>
                <div class="text-muted small">Total Absensi Terisi</div>
                <div class="stat-change text-accent"><i class="ti ti-calendar-check me-1"></i>{{ $absensiHariIni ?? 0 }} hari ini</div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="stat-card d-flex align-items-center gap-3">
            <div class="stat-icon" style="background:#fef3c7;color:#d97706;">
                <i class="ti ti-file-text"></i>
            </div>
            <div>
                <div class="stat-label">{{ $totalAgendaGuru ?? 0 }} Agenda Guru</div>
                <div class="text-muted small">Total Agenda Guru</div>
                <div class="stat-change" style="color:#0ea5e9;"><i class="ti ti-calendar-week me-1"></i>{{ $agendaGuruMingguIni ?? 0 }} minggu ini</div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="stat-card d-flex align-items-center gap-3">
            <div class="stat-icon" style="background:#ecfdf5;color:#15803d;">
                <i class="ti ti-school"></i>
            </div>
            <div>
                <div class="stat-label">{{ $totalAgendaKelas ?? 0 }} Agenda Kelas</div>
                <div class="text-muted small">Total Agenda Kelas</div>
                <div class="stat-change" style="color:#16a34a;"><i class="ti ti-calendar-week me-1"></i>{{ $agendaKelasMingguIni ?? 0 }} minggu ini</div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="stat-card d-flex align-items-center gap-3">
            <div class="stat-icon" style="background:#fee2e2;color:#ef4444;">
                <i class="ti ti-report-analytics"></i>
            </div>
            <div>
                <div class="stat-label">{{ $totalNilai ?? 0 }} Nilai</div>
                <div class="text-muted small">Total Nilai Terinput</div>
                <div class="stat-change text-muted"><i class="ti ti-school me-1"></i>{{ $kelasYangDiajar ?? 0 }} kelas</div>
            </div>
        </div>
    </div>
</div>

<!-- Quick Access -->
<div class="row g-3 mb-4">
    <div class="col-lg-6">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title"><i class="ti ti-apps me-2 text-primary"></i>Menu Akses Cepat</h5>
            </div>
            <div class="card-body">
                <div class="row g-2">
                    <div class="col-6">
                        @if(auth()->user()->guru_id)
                        <a href="{{ route('jadwal-kbm.show-by-guru', ['guru' => auth()->user()->guru_id]) }}" class="quick-menu-card">
                        @else
                        <a href="{{ route('jadwal-kbm.index') }}" class="quick-menu-card">
                        @endif
                            <div class="qm-icon bg-primary"><i class="ti ti-calendar-time"></i></div>
                            <div class="qm-label">Jadwal Mengajar</div>
                        </a>
                    </div>
                    <div class="col-6">
                        <a href="{{ route('absensi.index') }}" class="quick-menu-card">
                            <div class="qm-icon bg-accent"><i class="ti ti-check"></i></div>
                            <div class="qm-label">Absensi</div>
                        </a>
                    </div>
                    <div class="col-6">
                        <a href="{{ route('agenda_kelas.index') }}" class="quick-menu-card">
                            <div class="qm-icon" style="background:#f59e0b;"><i class="ti ti-file-text"></i></div>
                            <div class="qm-label">Agenda Pembelajaran</div>
                        </a>
                    </div>
                    <div class="col-6">
                        <a href="{{ route('nilai.index') }}" class="quick-menu-card">
                            <div class="qm-icon" style="background:#ef4444;"><i class="ti ti-report-analytics"></i></div>
                            <div class="qm-label">Daftar Nilai</div>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-6">
        <div class="card h-100">
            <div class="card-header">
                <h5 class="card-title"><i class="ti ti-bulb me-2 text-warning"></i>Tips & Panduan</h5>
            </div>
            <div class="card-body">
                <div class="d-flex align-items-start mb-3">
                    <span class="badge bg-primary me-2 mt-1">1</span>
                    <div>
                        <strong style="font-size:0.85rem;">Absensi Tepat Waktu</strong>
                        <p class="text-muted small mb-0">Lakukan input absensi di setiap pertemuan untuk data yang akurat.</p>
                    </div>
                </div>
                <div class="d-flex align-items-start mb-3">
                    <span class="badge bg-accent me-2 mt-1">2</span>
                    <div>
                        <strong style="font-size:0.85rem;">Isi Agenda Pembelajaran</strong>
                        <p class="text-muted small mb-0">Catat materi dan kegiatan pembelajaran setiap hari.</p>
                    </div>
                </div>
                <div class="d-flex align-items-start">
                    <span class="badge bg-warning me-2 mt-1">3</span>
                    <div>
                        <strong style="font-size:0.85rem;">Update Nilai Berkala</strong>
                        <p class="text-muted small mb-0">Input nilai tugas, UTS, dan UAS secara berkala.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Info -->
<div class="card">
    <div class="card-header">
        <h5 class="card-title"><i class="ti ti-info-circle me-2 text-primary"></i>Informasi</h5>
    </div>
    <div class="card-body">
        <div class="row g-3">
            <div class="col-md-4">
                <div class="d-flex align-items-center gap-3 p-2 rounded-3" style="background:#f1f5f9;">
                    <i class="ti ti-calendar-check text-primary" style="font-size:1.5rem;"></i>
                    <div>
                        <div class="fw-medium" style="font-size:0.85rem;">Jadwal Hari Ini</div>
                        <div class="text-muted small">{{ $jadwalHariIni ?? 0 }} jam pelajaran</div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="d-flex align-items-center gap-3 p-2 rounded-3" style="background:#f1f5f9;">
                    <i class="ti ti-users text-accent" style="font-size:1.5rem;"></i>
                    <div>
                        <div class="fw-medium" style="font-size:0.85rem;">Kelas yang Diajar</div>
                        <div class="text-muted small">{{ $kelasYangDiajar ?? 0 }} kelas</div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="d-flex align-items-center gap-3 p-2 rounded-3" style="background:#f1f5f9;">
                    <i class="ti ti-notebook" style="font-size:1.5rem;color:#d97706;"></i>
                    <div>
                        <div class="fw-medium" style="font-size:0.85rem;">Agenda Minggu Ini</div>
                        <div class="text-muted small">{{ $agendaMingguIni ?? 0 }} agenda</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection