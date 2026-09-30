@extends('layouts.app')

@section('title', 'Dashboard Pengawas Pembina')

@section('content')
<div class="welcome-banner">
    <h3><i class="ti ti-shield-check me-2"></i>Dashboard Pengawas Pembina</h3>
    <p>Monitoring ringkas data siswa, guru, kegiatan belajar mengajar, absensi, dan modul ajar sekolah.</p>
</div>

<div class="row gy-4 mb-4">
    <div class="col-12 col-md-6 col-xl-3">
        <a href="{{ route('siswa.index') }}" class="card shadow-sm h-100 text-decoration-none">
            <div class="card-body">
                <div class="d-flex align-items-center justify-content-between mb-3">
                    <div><h6 class="text-uppercase text-secondary mb-2">Data Siswa</h6><h3 class="mb-0">{{ number_format($siswa ?? 0) }}</h3></div>
                    <span class="badge bg-primary p-3 rounded-circle"><i class="ti ti-users"></i></span>
                </div>
                <p class="text-muted small mb-0">Jumlah siswa terdaftar.</p>
            </div>
        </a>
    </div>
    <div class="col-12 col-md-6 col-xl-3">
        <a href="{{ route('guru.index') }}" class="card shadow-sm h-100 text-decoration-none">
            <div class="card-body">
                <div class="d-flex align-items-center justify-content-between mb-3">
                    <div><h6 class="text-uppercase text-secondary mb-2">Data Guru</h6><h3 class="mb-0">{{ number_format($guru ?? 0) }}</h3></div>
                    <span class="badge bg-success p-3 rounded-circle"><i class="ti ti-users-group"></i></span>
                </div>
                <p class="text-muted small mb-0">Jumlah guru terdaftar.</p>
            </div>
        </a>
    </div>
    <div class="col-12 col-md-6 col-xl-3">
        <a href="{{ route('jadwal-kbm.index') }}" class="card shadow-sm h-100 text-decoration-none">
            <div class="card-body">
                <div class="d-flex align-items-center justify-content-between mb-3">
                    <div><h6 class="text-uppercase text-secondary mb-2">Jadwal KBM</h6><h3 class="mb-0">{{ number_format($totalJadwalKbm ?? 0) }}</h3></div>
                    <span class="badge bg-warning p-3 rounded-circle"><i class="ti ti-calendar"></i></span>
                </div>
                <p class="text-muted small mb-0">Jadwal pembelajaran tersusun.</p>
            </div>
        </a>
    </div>
    <div class="col-12 col-md-6 col-xl-3">
        <a href="{{ route('rencana_pembelajaran.index') }}" class="card shadow-sm h-100 text-decoration-none">
            <div class="card-body">
                <div class="d-flex align-items-center justify-content-between mb-3">
                    <div><h6 class="text-uppercase text-secondary mb-2">Modul Ajar</h6><h3 class="mb-0">{{ number_format($totalRencanaPembelajaran ?? 0) }}</h3></div>
                    <span class="badge bg-info p-3 rounded-circle"><i class="ti ti-book-2"></i></span>
                </div>
                <p class="text-muted small mb-0">Modul ajar yang dibuat guru.</p>
            </div>
        </a>
    </div>
</div>

<div class="row gy-4 mb-4">
    <div class="col-12 col-xl-6">
        <div class="card shadow-sm h-100">
            <div class="card-header d-flex align-items-center justify-content-between">
                <div><h5 class="card-title mb-1">Absensi Siswa</h5><p class="text-muted small mb-0">Ringkasan kehadiran hari ini.</p></div>
                <a href="{{ route('absensi.index') }}" class="btn btn-outline-primary btn-sm">Buka Data</a>
            </div>
            <div class="card-body">
                <div class="row g-3 text-center">
                    <div class="col-6 col-sm-3"><div class="p-3 rounded bg-light"><div class="text-success h4 mb-1">{{ $statistikKehadiranSiswaHarian->hadir ?? 0 }}</div><div class="text-muted small">Hadir</div></div></div>
                    <div class="col-6 col-sm-3"><div class="p-3 rounded bg-light"><div class="text-warning h4 mb-1">{{ $statistikKehadiranSiswaHarian->terlambat ?? 0 }}</div><div class="text-muted small">Terlambat</div></div></div>
                    <div class="col-6 col-sm-3"><div class="p-3 rounded bg-light"><div class="text-info h4 mb-1">{{ $statistikKehadiranSiswaHarian->izin ?? 0 }}</div><div class="text-muted small">Izin</div></div></div>
                    <div class="col-6 col-sm-3"><div class="p-3 rounded bg-light"><div class="text-danger h4 mb-1">{{ $statistikKehadiranSiswaHarian->alpa ?? 0 }}</div><div class="text-muted small">Alpa</div></div></div>
                </div>
                <div class="text-muted small mt-3">Total entri: {{ $statistikKehadiranSiswaHarian->total_entri ?? 0 }} | Hadir: {{ number_format($statistikKehadiranSiswaHarian->persentase_hadir ?? 0, 2) }}%</div>
            </div>
        </div>
    </div>
    <div class="col-12 col-xl-6">
        <div class="card shadow-sm h-100">
            <div class="card-header d-flex align-items-center justify-content-between">
                <div><h5 class="card-title mb-1">Absensi Guru</h5><p class="text-muted small mb-0">Ringkasan kehadiran hari ini.</p></div>
                <a href="{{ route('admin.absensi_guru.index') }}" class="btn btn-outline-success btn-sm">Buka Data</a>
            </div>
            <div class="card-body">
                <div class="row g-3 text-center">
                    <div class="col-6 col-sm-3"><div class="p-3 rounded bg-light"><div class="text-success h4 mb-1">{{ $statistikKehadiranGuruHarian->hadir ?? 0 }}</div><div class="text-muted small">Hadir</div></div></div>
                    <div class="col-6 col-sm-3"><div class="p-3 rounded bg-light"><div class="text-info h4 mb-1">{{ $statistikKehadiranGuruHarian->izin ?? 0 }}</div><div class="text-muted small">Izin</div></div></div>
                    <div class="col-6 col-sm-3"><div class="p-3 rounded bg-light"><div class="text-secondary h4 mb-1">{{ $statistikKehadiranGuruHarian->sakit ?? 0 }}</div><div class="text-muted small">Sakit</div></div></div>
                    <div class="col-6 col-sm-3"><div class="p-3 rounded bg-light"><div class="text-danger h4 mb-1">{{ $statistikKehadiranGuruHarian->tidak_hadir ?? 0 }}</div><div class="text-muted small">Tidak Hadir</div></div></div>
                </div>
                <div class="text-muted small mt-3">Total entri hari ini: {{ $statistikKehadiranGuruHarian->total_entri ?? 0 }} | Hadir: {{ number_format($statistikKehadiranGuruHarian->persentase_hadir ?? 0, 2) }}%</div>
            </div>
        </div>
    </div>
</div>

<div class="row gy-4">
    <div class="col-12 col-xl-8">
        <div class="card shadow-sm">
            <div class="card-header d-flex align-items-center justify-content-between">
                <div><h5 class="card-title mb-1">Kehadiran Siswa per Kelas</h5><p class="text-muted small mb-0">Rekap untuk hari ini.</p></div>
                <a href="{{ route('absensi.index') }}" class="btn btn-outline-primary btn-sm">Lihat Absensi</a>
            </div>
            <div class="card-body">
                @if(($rekapKehadiranSiswaPerKelas ?? collect())->isEmpty())
                    <div class="alert alert-warning mb-0">Belum ada data kehadiran siswa untuk hari ini.</div>
                @else
                    <div class="table-responsive"><table class="table table-hover align-middle mb-0"><thead><tr><th>Kelas</th><th>Hadir</th><th>Terlambat</th><th>Izin</th><th>Sakit</th><th>Alpa</th></tr></thead><tbody>
                    @foreach($rekapKehadiranSiswaPerKelas as $row)
                        <tr><td>{{ $row->nama_kelas }}</td><td>{{ $row->hadir }}</td><td>{{ $row->terlambat }}</td><td>{{ $row->izin }}</td><td>{{ $row->sakit }}</td><td>{{ $row->alpa }}</td></tr>
                    @endforeach
                    </tbody></table></div>
                @endif
            </div>
        </div>
    </div>
    <div class="col-12 col-xl-4">
        <div class="card shadow-sm h-100">
            <div class="card-header"><h5 class="card-title mb-0">Akses Monitoring</h5></div>
            <div class="list-group list-group-flush">
                <a href="{{ route('siswa.index') }}" class="list-group-item list-group-item-action"><i class="ti ti-users me-2"></i>Data Siswa</a>
                <a href="{{ route('guru.index') }}" class="list-group-item list-group-item-action"><i class="ti ti-users-group me-2"></i>Data Guru</a>
                <a href="{{ route('jadwal-kbm.index') }}" class="list-group-item list-group-item-action"><i class="ti ti-calendar me-2"></i>Jadwal KBM</a>
                <a href="{{ route('rencana_pembelajaran.index') }}" class="list-group-item list-group-item-action"><i class="ti ti-book-2 me-2"></i>Modul Ajar Guru</a>
                <a href="{{ route('supervisi.dashboard') }}" class="list-group-item list-group-item-action"><i class="ti ti-eye me-2"></i>Supervisi</a>
            </div>
        </div>
    </div>
</div>
@endsection
