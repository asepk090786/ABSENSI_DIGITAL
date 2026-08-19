@extends('layouts.app')

@section('title', $pageTitle ?? 'Dashboard Kepala Sekolah')

@section('content')
<div class="welcome-banner">
    <h3><i class="ti ti-user-star me-2"></i>{{ $pageTitle ?? 'Dashboard Kepala Sekolah' }}</h3>
    <p>Pantau statistik kehadiran siswa, guru, RPP/penilaian, dan Bimbingan BK dari panel ini.</p>
</div>

<div class="row gy-4 mb-4">
    <div class="col-12 col-md-6 col-xl-3">
        <div class="card shadow-sm">
            <div class="card-body">
                <div class="d-flex align-items-center justify-content-between mb-3">
                    <div>
                        <h6 class="text-uppercase text-secondary mb-2">RPP</h6>
                        <h3 class="mb-0">{{ number_format($totalRencanaPembelajaran ?? 0) }}</h3>
                    </div>
                    <span class="badge bg-primary p-3 rounded-circle">
                        <i class="ti ti-book"></i>
                    </span>
                </div>
                <p class="text-muted small mb-0">Total rencana pembelajaran terdaftar.</p>
            </div>
        </div>
    </div>
    <div class="col-12 col-md-6 col-xl-3">
        <div class="card shadow-sm">
            <div class="card-body">
                <div class="d-flex align-items-center justify-content-between mb-3">
                    <div>
                        <h6 class="text-uppercase text-secondary mb-2">Nilai Harian</h6>
                        <h3 class="mb-0">{{ number_format($totalNilaiHarian ?? 0) }}</h3>
                    </div>
                    <span class="badge bg-success p-3 rounded-circle">
                        <i class="ti ti-clipboard-list"></i>
                    </span>
                </div>
                <p class="text-muted small mb-0">Total data penilaian siswa yang terinput.</p>
            </div>
        </div>
    </div>
    <div class="col-12 col-md-6 col-xl-3">
        <div class="card shadow-sm">
            <div class="card-body">
                <div class="d-flex align-items-center justify-content-between mb-3">
                    <div>
                        <h6 class="text-uppercase text-secondary mb-2">Komponen Penilaian</h6>
                        <h3 class="mb-0">{{ number_format($totalKomponenPenilaian ?? 0) }}</h3>
                    </div>
                    <span class="badge bg-warning p-3 rounded-circle">
                        <i class="ti ti-list-details"></i>
                    </span>
                </div>
                <p class="text-muted small mb-0">Komponen penilaian dan capaian pembelajaran.</p>
            </div>
        </div>
    </div>
    <div class="col-12 col-md-6 col-xl-3">
        <div class="card shadow-sm">
            <div class="card-body">
                <div class="d-flex align-items-center justify-content-between mb-3">
                    <div>
                        <h6 class="text-uppercase text-secondary mb-2">Bimbingan BK</h6>
                        <h3 class="mb-0">{{ number_format(($totalLayananBk ?? 0) + ($totalPembinaanBk ?? 0) + ($totalTindakLanjutBk ?? 0)) }}</h3>
                    </div>
                    <span class="badge bg-danger p-3 rounded-circle">
                        <i class="ti ti-heart-handshake"></i>
                    </span>
                </div>
                <p class="text-muted small mb-0">Layanan BK, pembinaan, dan tindak lanjut.</p>
            </div>
        </div>
    </div>
</div>

<div class="row gy-4 mb-4">
    <div class="col-12 col-xl-6">
        <div class="card shadow-sm">
            <div class="card-header d-flex align-items-center justify-content-between">
                <div>
                    <h5 class="card-title mb-1">Statistik Kehadiran Siswa</h5>
                    <p class="text-muted small mb-0">Data kehadiran harian siswa di periode aktif.</p>
                </div>
                <span class="badge bg-primary text-white">{{ $labelPeriode ?? 'Hari Ini' }}</span>
            </div>
            <div class="card-body">
                <div class="row g-3 text-center">
                    <div class="col-6 col-sm-4">
                        <div class="p-3 rounded bg-light">
                            <div class="text-success h4 mb-1">{{ $statistikKehadiranSiswaHarian->hadir ?? 0 }}</div>
                            <div class="text-muted small">Hadir</div>
                        </div>
                    </div>
                    <div class="col-6 col-sm-4">
                        <div class="p-3 rounded bg-light">
                            <div class="text-warning h4 mb-1">{{ $statistikKehadiranSiswaHarian->terlambat ?? 0 }}</div>
                            <div class="text-muted small">Terlambat</div>
                        </div>
                    </div>
                    <div class="col-6 col-sm-4">
                        <div class="p-3 rounded bg-light">
                            <div class="text-info h4 mb-1">{{ $statistikKehadiranSiswaHarian->izin ?? 0 }}</div>
                            <div class="text-muted small">Izin</div>
                        </div>
                    </div>
                    <div class="col-6 col-sm-4">
                        <div class="p-3 rounded bg-light">
                            <div class="text-secondary h4 mb-1">{{ $statistikKehadiranSiswaHarian->sakit ?? 0 }}</div>
                            <div class="text-muted small">Sakit</div>
                        </div>
                    </div>
                    <div class="col-6 col-sm-4">
                        <div class="p-3 rounded bg-light">
                            <div class="text-danger h4 mb-1">{{ $statistikKehadiranSiswaHarian->alpa ?? 0 }}</div>
                            <div class="text-muted small">Alpa</div>
                        </div>
                    </div>
                    <div class="col-12 col-sm-4">
                        <div class="p-3 rounded bg-light">
                            <div class="text-primary h4 mb-1">{{ number_format($statistikKehadiranSiswaHarian->persentase_hadir ?? 0, 2) }}%</div>
                            <div class="text-muted small">Persentase Hadir</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-12 col-xl-6">
        <div class="card shadow-sm">
            <div class="card-header d-flex align-items-center justify-content-between">
                <div>
                    <h5 class="card-title mb-1">Statistik Kehadiran Guru</h5>
                    <p class="text-muted small mb-0">Data kehadiran guru hari ini.</p>
                </div>
                <span class="badge bg-primary text-white">{{ $labelPeriode ?? 'Hari Ini' }}</span>
            </div>
            <div class="card-body">
                <div class="row g-3 text-center">
                    <div class="col-6 col-sm-4">
                        <div class="p-3 rounded bg-light">
                            <div class="text-success h4 mb-1">{{ $statistikKehadiranGuruHarian->hadir ?? 0 }}</div>
                            <div class="text-muted small">Hadir</div>
                        </div>
                    </div>
                    <div class="col-6 col-sm-4">
                        <div class="p-3 rounded bg-light">
                            <div class="text-info h4 mb-1">{{ $statistikKehadiranGuruHarian->izin ?? 0 }}</div>
                            <div class="text-muted small">Izin</div>
                        </div>
                    </div>
                    <div class="col-6 col-sm-4">
                        <div class="p-3 rounded bg-light">
                            <div class="text-secondary h4 mb-1">{{ $statistikKehadiranGuruHarian->sakit ?? 0 }}</div>
                            <div class="text-muted small">Sakit</div>
                        </div>
                    </div>
                    <div class="col-6 col-sm-4">
                        <div class="p-3 rounded bg-light">
                            <div class="text-danger h4 mb-1">{{ $statistikKehadiranGuruHarian->tidak_hadir ?? 0 }}</div>
                            <div class="text-muted small">Tidak Hadir</div>
                        </div>
                    </div>
                    <div class="col-12 col-sm-8">
                        <div class="p-3 rounded bg-light">
                            <div class="text-primary h4 mb-1">{{ number_format($statistikKehadiranGuruHarian->persentase_hadir ?? 0, 2) }}%</div>
                            <div class="text-muted small">Persentase Hadir</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row gy-4 mb-4">
    <div class="col-12">
        <div class="card shadow-sm">
            <div class="card-header d-flex align-items-center justify-content-between">
                <div>
                    <h5 class="card-title mb-1">Monitoring Bimbingan BK</h5>
                    <p class="text-muted small mb-0">Ringkasan jumlah layanan BK dan tindak lanjut.</p>
                </div>
                <a href="{{ route('guru_bk.index') }}" class="btn btn-outline-primary btn-sm">Lihat Data Guru BK</a>
            </div>
            <div class="card-body">
                <div class="row g-3 text-center">
                    <div class="col-6 col-md-4">
                        <div class="p-3 rounded bg-light">
                            <div class="text-primary h4 mb-1">{{ number_format($totalLayananBk ?? 0) }}</div>
                            <div class="text-muted small">Layanan BK</div>
                        </div>
                    </div>
                    <div class="col-6 col-md-4">
                        <div class="p-3 rounded bg-light">
                            <div class="text-warning h4 mb-1">{{ number_format($totalPembinaanBk ?? 0) }}</div>
                            <div class="text-muted small">Pembinaan BK</div>
                        </div>
                    </div>
                    <div class="col-6 col-md-4">
                        <div class="p-3 rounded bg-light">
                            <div class="text-danger h4 mb-1">{{ number_format($totalTindakLanjutBk ?? 0) }}</div>
                            <div class="text-muted small">Tindak Lanjut BK</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row gy-4 mb-4">
    <div class="col-12 col-xl-4">
        <div class="card shadow-sm">
            <div class="card-body">
                <h5 class="card-title">Akses Cepat</h5>
                <div class="list-group list-group-flush">
                    <a href="{{ route('absensi.index') }}" class="list-group-item list-group-item-action">Absensi Kelas</a>
                    <a href="{{ route('rekap_nilai.index') }}" class="list-group-item list-group-item-action">Rekap Nilai</a>
                    <a href="{{ route('komponen_nilai.index') }}" class="list-group-item list-group-item-action">Komponen Penilaian</a>
                    <a href="{{ route('guru_bk.index') }}" class="list-group-item list-group-item-action">Guru BK</a>
                </div>
            </div>
        </div>
    </div>
    <div class="col-12 col-xl-8">
        <div class="card shadow-sm">
            <div class="card-header">
                <h5 class="card-title mb-0">Rekap Kehadiran Siswa per Kelas</h5>
            </div>
            <div class="card-body">
                @if(($rekapKehadiranSiswaPerKelas ?? collect())->isEmpty())
                    <div class="alert alert-warning mb-0">Belum ada data kehadiran siswa per kelas untuk periode ini.</div>
                @else
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead>
                                <tr>
                                    <th>Kelas</th>
                                    <th>Hadir</th>
                                    <th>Terlambat</th>
                                    <th>Izin</th>
                                    <th>Sakit</th>
                                    <th>Alpa</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($rekapKehadiranSiswaPerKelas as $row)
                                    <tr>
                                        <td>{{ $row->nama_kelas }}</td>
                                        <td>{{ $row->hadir }}</td>
                                        <td>{{ $row->terlambat }}</td>
                                        <td>{{ $row->izin }}</td>
                                        <td>{{ $row->sakit }}</td>
                                        <td>{{ $row->alpa }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

<div class="row gy-4 mb-4">
    <div class="col-12">
        <div class="card">
            <div class="card-body">
                @php
                    $tanggalLaporan = request('tanggal_laporan', now()->format('Y-m-d'));
                    $kelasLaporanId = request('kelas_laporan_id');
                @endphp
                <h5 class="card-title mb-3"><i class="ti ti-printer me-2 text-primary"></i>Printout Laporan Kehadiran</h5>
                <form class="row g-2 align-items-end" method="GET" action="{{ route('absensi.laporan-siswa.print') }}" target="_blank">
                    <div class="col-md-3">
                        <label class="form-label">Tanggal Laporan</label>
                        <input type="date" class="form-control" name="tanggal" value="{{ $tanggalLaporan }}">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Kelas (Opsional)</label>
                        <select class="form-select" name="kelas_id">
                            <option value="">Semua Kelas</option>
                            @foreach(($kelasLaporanOptions ?? collect()) as $kelasOption)
                                <option value="{{ $kelasOption->id }}" {{ (string) $kelasLaporanId === (string) $kelasOption->id ? 'selected' : '' }}>{{ $kelasOption->nama_kelas }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-5 d-flex flex-wrap gap-2">
                        <button type="submit" class="btn btn-outline-primary">
                            <i class="ti ti-printer me-1"></i>Print Laporan Kehadiran Siswa
                        </button>
                        <a href="{{ route('absensi.laporan-guru.print', ['tanggal' => $tanggalLaporan]) }}" target="_blank" class="btn btn-outline-success">
                            <i class="ti ti-printer me-1"></i>Print Laporan Kehadiran Guru
                        </a>
                    </div>
                </form>
                <div class="text-muted small mt-2">
                    Laporan guru difilter berdasarkan tanggal. Laporan siswa bisa difilter tanggal dan kelas.
                </div>
            </div>
        </div>
    </div>
</div>
@endsection