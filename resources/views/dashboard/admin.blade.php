@extends('layouts.app')

@section('title','Dashboard Admin')

@section('content')
<div class="welcome-banner">
    <h3><i class="ti ti-shield me-2"></i>Dashboard Admin</h3>
    <p>Selamat datang di panel administrasi. Gunakan menu untuk mengelola data master, pengguna, dan pengaturan sistem.</p>
</div>

<div class="row mb-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title"><i class="ti ti-printer me-2 text-primary"></i>Printout Laporan Kehadiran</h5>
            </div>
            <div class="card-body">
                @php
                    $tanggalLaporan = request('tanggal_laporan', now()->format('Y-m-d'));
                    $kelasLaporanId = request('kelas_laporan_id');
                @endphp
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
                            <i class="ti ti-printer me-1"></i>Print Laporan Siswa
                        </button>
                        <a href="{{ route('absensi.laporan-siswa.export', ['tanggal' => $tanggalLaporan, 'kelas_id' => $kelasLaporanId]) }}" class="btn btn-outline-info">
                            <i class="ti ti-file-export me-1"></i>Export Excel
                        </a>
                        <a href="{{ route('absensi.laporan-guru.print', ['tanggal' => $tanggalLaporan]) }}" target="_blank" class="btn btn-outline-success">
                            <i class="ti ti-printer me-1"></i>Print Laporan Guru
                        </a>
                    </div>
                </form>
                <div class="text-muted small mt-2">
                    Laporan siswa (PDF/Excel) dihitung sebagai status kehadiran harian per siswa agar konsisten dengan statistik harian.
    </div>
</div>

<div class="row mb-4">
    <div class="col-12">
        <div class="card">
            <div class="card-body pb-0">
                <form class="row g-2 align-items-end" method="GET" action="{{ route('home') }}">
                    <div class="col-md-2">
                        <label class="form-label">Tanggal</label>
                        <input type="date" class="form-control" name="filter_tanggal" value="{{ $filterTanggal }}">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Hari</label>
                        <select class="form-select" name="filter_hari">
                            <option value="">Semua</option>
                            @foreach($hariOptions as $hari)
                                <option value="{{ $hari }}" {{ $filterHari === $hari ? 'selected' : '' }}>{{ $hari }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Minggu</label>
                        <input type="week" class="form-control" name="filter_minggu" value="{{ $filterMinggu }}">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Bulan</label>
                        <input type="month" class="form-control" name="filter_bulan" value="{{ $filterBulan }}">
                    </div>
                    <div class="col-md-4 d-flex flex-wrap gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="ti ti-filter me-1"></i>Filter
                        </button>
                        @if($filterTanggal || $filterHari || $filterMinggu || $filterBulan)
                            <a href="{{ route('home') }}" class="btn btn-outline-secondary">
                                <i class="ti ti-x me-1"></i>Reset
                            </a>
                        @endif
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<div class="row mt-4">
    <div class="col-12 col-lg-6">
        <div class="card attendance-summary-card h-100">
            <div class="card-body">
                <div class="attendance-card-header mb-4">
                    <div class="d-flex align-items-center justify-content-between">
                        <div class="d-flex align-items-center gap-3">
                            <span class="attendance-header-icon bg-primary text-white rounded-circle">
                                <i class="ti ti-users"></i>
                            </span>
                            <h5 class="mb-0">Statistik Kehadiran Siswa Hari Ini</h5>
                        </div>
                        <span class="badge rounded-pill bg-light border text-dark py-2 px-3">
                            <i class="ti ti-calendar me-1"></i>{{ $labelPeriode }}
                        </span>
                    </div>
                </div>
                <div class="row g-3 attendance-items">
                    <div class="col-6">
                        <div class="attendance-item attendance-item-success">
                            <div class="attendance-item-icon bg-success text-white rounded-3">
                                <i class="ti ti-check"></i>
                            </div>
                            <div class="attendance-item-content">
                                <div class="attendance-item-label">Hadir</div>
                                <div class="attendance-item-value">{{ $statistikKehadiranSiswaHarian->hadir }}</div>
                            </div>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="attendance-item attendance-item-warning">
                            <div class="attendance-item-icon" style="background-color: #ffc107; color: #1e293b;">
                                <i class="ti ti-clock"></i>
                            </div>
                            <div class="attendance-item-content">
                                <div class="attendance-item-label">Terlambat</div>
                                <div class="attendance-item-value">{{ $statistikKehadiranSiswaHarian->terlambat }}</div>
                            </div>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="attendance-item attendance-item-info">
                            <div class="attendance-item-icon bg-info text-white rounded-3">
                                <i class="ti ti-file-text"></i>
                            </div>
                            <div class="attendance-item-content">
                                <div class="attendance-item-label">Izin</div>
                                <div class="attendance-item-value">{{ $statistikKehadiranSiswaHarian->izin }}</div>
                            </div>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="attendance-item attendance-item-secondary">
                            <div class="attendance-item-icon bg-secondary text-white rounded-3">
                                <i class="ti ti-minus"></i>
                            </div>
                            <div class="attendance-item-content">
                                <div class="attendance-item-label">Tidak Hadir</div>
                                <div class="attendance-item-value">{{ $statistikKehadiranSiswaHarian->sakit }}</div>
                            </div>
                        </div>
                    </div>
                    <div class="col-12">
                        <div class="attendance-item attendance-item-danger">
                            <div class="attendance-item-icon bg-danger text-white rounded-3">
                                <i class="ti ti-alert-circle"></i>
                            </div>
                            <div class="attendance-item-content">
                                <div class="attendance-item-label">Alpa</div>
                                <div class="attendance-item-value">{{ $statistikKehadiranSiswaHarian->alpa }}</div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="attendance-summary-footer d-flex flex-column flex-sm-row gap-3 mt-4 pt-3 text-muted small">
                    <div>Total entri: <strong>{{ $statistikKehadiranSiswaHarian->total_entri }}</strong></div>
                    <div>Persentase hadir: <strong>{{ number_format((float) $statistikKehadiranSiswaHarian->persentase_hadir, 2, ',', '.') }}%</strong></div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-12 col-lg-6">
        <div class="card attendance-summary-card h-100">
            <div class="card-body">
                <div class="attendance-card-header mb-4">
                    <div class="d-flex align-items-center justify-content-between">
                        <div class="d-flex align-items-center gap-3">
                            <span class="attendance-header-icon bg-primary text-white rounded-circle">
                                <i class="ti ti-school"></i>
                            </span>
                            <h5 class="mb-0">Statistik Kehadiran Guru Hari Ini</h5>
                        </div>
                        <span class="badge rounded-pill bg-light border text-dark py-2 px-3">
                            <i class="ti ti-calendar me-1"></i>{{ $labelPeriode }}
                        </span>
                    </div>
                </div>
                <div class="row g-3 attendance-items">
                    <div class="col-6">
                        <div class="attendance-item attendance-item-success">
                            <div class="attendance-item-icon bg-success text-white rounded-3">
                                <i class="ti ti-check"></i>
                            </div>
                            <div class="attendance-item-content">
                                <div class="attendance-item-label">Hadir</div>
                                <div class="attendance-item-value">{{ $statistikKehadiranGuruHarian->hadir }}</div>
                            </div>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="attendance-item attendance-item-info">
                            <div class="attendance-item-icon bg-info text-white rounded-3">
                                <i class="ti ti-file-text"></i>
                            </div>
                            <div class="attendance-item-content">
                                <div class="attendance-item-label">Izin</div>
                                <div class="attendance-item-value">{{ $statistikKehadiranGuruHarian->izin }}</div>
                            </div>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="attendance-item attendance-item-secondary">
                            <div class="attendance-item-icon bg-secondary text-white rounded-3">
                                <i class="ti ti-stethoscope"></i>
                            </div>
                            <div class="attendance-item-content">
                                <div class="attendance-item-label">Sakit</div>
                                <div class="attendance-item-value">{{ $statistikKehadiranGuruHarian->sakit }}</div>
                            </div>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="attendance-item attendance-item-danger">
                            <div class="attendance-item-icon bg-danger text-white rounded-3">
                                <i class="ti ti-user-x"></i>
                            </div>
                            <div class="attendance-item-content">
                                <div class="attendance-item-label">Tidak Hadir</div>
                                <div class="attendance-item-value">{{ $statistikKehadiranGuruHarian->tidak_hadir }}</div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="attendance-summary-footer d-flex flex-column flex-sm-row gap-3 mt-4 pt-3 text-muted small">
                    <div>Total entri: <strong>{{ $statistikKehadiranGuruHarian->total_entri }}</strong></div>
                    <div>Persentase hadir: <strong>{{ number_format((float) $statistikKehadiranGuruHarian->persentase_hadir, 2, ',', '.') }}%</strong></div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row mt-4">
    <div class="col-lg-7">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title">Rekap Kehadiran Siswa per Kelas</h5>
                <div class="text-muted small">Berdasarkan data absensi siswa di periode aktif.</div>
            </div>
            <div class="card-body">
                @if(($rekapKehadiranSiswaPerKelas ?? collect())->isEmpty())
                    <div class="alert alert-warning mb-0">Belum ada data kehadiran siswa per kelas.</div>
                @else
                    <div class="table-responsive">
                        <table class="table table-vcenter">
                            <thead>
                                <tr>
                                    <th>Kelas</th>
                                    <th class="text-center">Hadir</th>
                                    <th class="text-center">Terlambat</th>
                                    <th class="text-center">Izin</th>
                                    <th class="text-center">Sakit</th>
                                    <th class="text-center">Alpa</th>
                                    <th class="text-center">Total</th>
                                    <th class="text-end">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($rekapKehadiranSiswaPerKelas as $item)
                                <tr>
                                    <td>{{ $item->nama_kelas }}</td>
                                    <td class="text-center">{{ $item->hadir }}</td>
                                    <td class="text-center">{{ $item->terlambat }}</td>
                                    <td class="text-center">{{ $item->izin }}</td>
                                    <td class="text-center">{{ $item->sakit }}</td>
                                    <td class="text-center">{{ $item->alpa }}</td>
                                    <td class="text-center fw-bold">{{ $item->total_entri }}</td>
                                    <td class="text-end">
                                        <a href="{{ route('absensi.create', ['kelas_id' => $item->kelas_id, 'tanggal' => $statistikKehadiranSiswaHarian->tanggal]) }}" class="btn btn-outline-primary btn-sm">
                                            Input/Detail
                                        </a>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        </div>
    </div>
    <div class="col-lg-5">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title">Rekap Kehadiran Guru per Hari</h5>
                <div class="text-muted small">Ringkasan 14 hari terakhir.</div>
            </div>
            <div class="card-body">
                @if(($rekapKehadiranGuruHarian ?? collect())->isEmpty())
                    <div class="alert alert-warning mb-0">Belum ada data kehadiran guru harian.</div>
                @else
                    <div class="table-responsive">
                        <table class="table table-vcenter">
                            <thead>
                                <tr>
                                    <th>Tanggal</th>
                                    <th class="text-center">H</th>
                                    <th class="text-center">I</th>
                                    <th class="text-center">S</th>
                                    <th class="text-center">TH</th>
                                    <th class="text-center">Total</th>
                                    <th class="text-end">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($rekapKehadiranGuruHarian as $item)
                                <tr>
                                    <td>{{ \Carbon\Carbon::parse($item->tanggal)->format('d M Y') }}</td>
                                    <td class="text-center">{{ $item->hadir }}</td>
                                    <td class="text-center">{{ $item->izin }}</td>
                                    <td class="text-center">{{ $item->sakit }}</td>
                                    <td class="text-center">{{ $item->tidak_hadir }}</td>
                                    <td class="text-center fw-bold">{{ $item->total_entri }}</td>
                                    <td class="text-end">
                                        <a href="{{ route('absensi.index', ['tanggal' => $item->tanggal]) }}" class="btn btn-outline-secondary btn-sm">Lihat</a>
                                    </td>
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

<div class="row mt-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <div>
                    <h5 class="card-title">Rekap Nilai per Kelas & Mata Pelajaran</h5>
                    <div class="text-muted small">Menampilkan nilai yang sudah diinput guru pada tahun ajaran dan semester aktif.</div>
                </div>
                <a href="{{ route('rekap_nilai.index') }}" class="btn btn-primary btn-sm">
                    <i class="ti ti-report-analytics me-1"></i>Lihat Rekap Lengkap
                </a>
            </div>
            <div class="card-body">
                @if(($rekapNilaiKelasMapel ?? collect())->isEmpty())
                    <div class="alert alert-warning mb-0">Belum ada data nilai yang diinput guru untuk periode aktif.</div>
                @else
                    <div class="table-responsive">
                        <table class="table table-vcenter">
                            <thead>
                                <tr>
                                    <th>Kelas</th>
                                    <th>Mata Pelajaran</th>
                                    <th class="text-center">Guru Input</th>
                                    <th class="text-center">Siswa Terinput</th>
                                    <th class="text-center">Total Nilai</th>
                                    <th class="text-center">Rata-rata</th>
                                    <th>Update Terakhir</th>
                                    <th class="text-end">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($rekapNilaiKelasMapel as $row)
                                <tr>
                                    <td>{{ $row->nama_kelas }}</td>
                                    <td>{{ $row->nama_mapel }}</td>
                                    <td class="text-center">{{ $row->total_guru_penginput }}</td>
                                    <td class="text-center">{{ $row->total_siswa_terinput }}</td>
                                    <td class="text-center">{{ $row->total_input_nilai }}</td>
                                    <td class="text-center">{{ number_format((float) $row->rata_rata_nilai, 2, ',', '.') }}</td>
                                    <td>{{ $row->update_terakhir ? \Carbon\Carbon::parse($row->update_terakhir)->format('d M Y H:i') : '-' }}</td>
                                    <td class="text-end">
                                        <a href="{{ route('rekap_nilai.index', ['kelas_id' => $row->kelas_id, 'mapel_id' => $row->mapel_id]) }}" class="btn btn-outline-primary btn-sm">Detail</a>
                                    </td>
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
@endsection