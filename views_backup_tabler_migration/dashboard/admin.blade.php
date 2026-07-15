@extends('layouts.app', ['pageSlug' => 'dashboard-admin'])

@section('title','Dashboard Admin')

@section('content')
<div class="alert alert-info">Selamat datang di Dashboard <b>Admin</b>. Gunakan menu di samping untuk mengelola data master, pengguna, dan pengaturan sistem.</div>
<div class="row mb-3">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-1">Printout Laporan Kehadiran</h5>
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
                            <i class="ti ti-printer me-1"></i>Print Laporan Kehadiran Siswa
                        </button>
                        <a href="{{ route('absensi.laporan-siswa.export', ['tanggal' => $tanggalLaporan, 'kelas_id' => $kelasLaporanId]) }}" class="btn btn-outline-info">
                            <i class="ti ti-file-export me-1"></i>Export Excel Siswa
                        </a>
                        <a href="{{ route('absensi.laporan-guru.print', ['tanggal' => $tanggalLaporan]) }}" target="_blank" class="btn btn-outline-success">
                            <i class="ti ti-printer me-1"></i>Print Laporan Kehadiran Guru
                        </a>
                    </div>
                </form>
                <div class="text-muted small mt-2">
                    Laporan siswa (PDF/Excel) dihitung sebagai status kehadiran harian per siswa agar konsisten dengan statistik harian.
                </div>
            </div>
        </div>
    </div>
</div>
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-1">Menu Cepat</h5>
            </div>
            <div class="card-body">
                <div class="row g-2">
                    <div class="col-6 col-md-4 col-lg-3">
                        <a class="btn btn-outline-primary w-100" href="{{ route('sekolah.index') }}">
                            <i class="ti ti-building-bank me-2"></i>Data Sekolah
                        </a>
                    </div>
                    <div class="col-6 col-md-4 col-lg-3">
                        <a class="btn btn-outline-primary w-100" href="{{ route('guru.index') }}">
                            <i class="ti ti-users me-2"></i>Guru
                        </a>
                    </div>
                    <div class="col-6 col-md-4 col-lg-3">
                        <a class="btn btn-outline-primary w-100" href="{{ route('siswa.index') }}">
                            <i class="ti ti-school me-2"></i>Siswa
                        </a>
                    </div>
                    <div class="col-6 col-md-4 col-lg-3">
                        <a class="btn btn-outline-primary w-100" href="{{ route('kelas.index') }}">
                            <i class="ti ti-building me-2"></i>Kelas
                        </a>
                    </div>
                    <div class="col-6 col-md-4 col-lg-3">
                        <a class="btn btn-outline-primary w-100" href="{{ route('mata_pelajaran.index') }}">
                            <i class="ti ti-books me-2"></i>Mata Pelajaran
                        </a>
                    </div>
                    <div class="col-6 col-md-4 col-lg-3">
                        <a class="btn btn-outline-primary w-100" href="{{ route('jam_belajar.index') }}">
                            <i class="ti ti-clock me-2"></i>Jam Belajar
                        </a>
                    </div>
                    <div class="col-6 col-md-4 col-lg-3">
                        <a class="btn btn-outline-primary w-100" href="{{ route('absensi.generate.form') }}">
                            <i class="ti ti-bolt me-2"></i>Generate Absensi
                        </a>
                    </div>
                    <div class="col-6 col-md-4 col-lg-3">
                        <a class="btn btn-outline-primary w-100" href="{{ route('users.index') }}">
                            <i class="ti ti-lock me-2"></i>Akun Pengguna
                        </a>
                    </div>
                    <div class="col-6 col-md-4 col-lg-3">
                        <a class="btn btn-outline-primary w-100" href="{{ route('tahun_ajaran.index') }}">
                            <i class="ti ti-settings me-2"></i>Pengaturan
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row mt-3">
    <div class="col-md-6">
        <div class="card h-100">
            <div class="card-header">
                <h5 class="card-title mb-1">Statistik Kehadiran Siswa Hari Ini</h5>
                <div class="text-muted small">{{ \Carbon\Carbon::parse($statistikKehadiranSiswaHarian->tanggal)->format('d M Y') }}</div>
            </div>
            <div class="card-body">
                <div class="row g-2 mb-2">
                    <div class="col-6"><span class="badge bg-success-lt w-100 py-2">Hadir: {{ $statistikKehadiranSiswaHarian->hadir }}</span></div>
                    <div class="col-6"><span class="badge bg-yellow-lt w-100 py-2">Terlambat: {{ $statistikKehadiranSiswaHarian->terlambat }}</span></div>
                    <div class="col-6"><span class="badge bg-blue-lt w-100 py-2">Izin: {{ $statistikKehadiranSiswaHarian->izin }}</span></div>
                    <div class="col-6"><span class="badge bg-indigo-lt w-100 py-2">Sakit: {{ $statistikKehadiranSiswaHarian->sakit }}</span></div>
                    <div class="col-12"><span class="badge bg-red-lt w-100 py-2">Alpha: {{ $statistikKehadiranSiswaHarian->alpha }}</span></div>
                </div>
                <div class="small text-muted">Total entri: <b>{{ $statistikKehadiranSiswaHarian->total_entri }}</b></div>
                <div class="small text-muted">Persentase hadir: <b>{{ number_format((float) $statistikKehadiranSiswaHarian->persentase_hadir, 2, ',', '.') }}%</b></div>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card h-100">
            <div class="card-header">
                <h5 class="card-title mb-1">Statistik Kehadiran Guru Hari Ini</h5>
                <div class="text-muted small">{{ \Carbon\Carbon::parse($statistikKehadiranGuruHarian->tanggal)->format('d M Y') }}</div>
            </div>
            <div class="card-body">
                <div class="row g-2 mb-2">
                    <div class="col-6"><span class="badge bg-success-lt w-100 py-2">Hadir: {{ $statistikKehadiranGuruHarian->hadir }}</span></div>
                    <div class="col-6"><span class="badge bg-blue-lt w-100 py-2">Izin: {{ $statistikKehadiranGuruHarian->izin }}</span></div>
                    <div class="col-6"><span class="badge bg-indigo-lt w-100 py-2">Sakit: {{ $statistikKehadiranGuruHarian->sakit }}</span></div>
                    <div class="col-6"><span class="badge bg-red-lt w-100 py-2">Tidak Hadir: {{ $statistikKehadiranGuruHarian->tidak_hadir }}</span></div>
                </div>
                <div class="small text-muted">Total entri: <b>{{ $statistikKehadiranGuruHarian->total_entri }}</b></div>
                <div class="small text-muted">Persentase hadir: <b>{{ number_format((float) $statistikKehadiranGuruHarian->persentase_hadir, 2, ',', '.') }}%</b></div>
            </div>
        </div>
    </div>
</div>

<div class="row mt-3">
    <div class="col-lg-7">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-1">Rekap Kehadiran Siswa per Kelas</h5>
                <div class="text-muted small">Berdasarkan data absensi siswa di periode aktif.</div>
            </div>
            <div class="card-body">
                @if(($rekapKehadiranSiswaPerKelas ?? collect())->isEmpty())
                    <div class="alert alert-warning mb-0">Belum ada data kehadiran siswa per kelas.</div>
                @else
                    <div class="table-responsive">
                        <table class="table table-vcenter table-striped">
                            <thead>
                                <tr>
                                    <th>Kelas</th>
                                    <th class="text-center">Hadir</th>
                                    <th class="text-center">Terlambat</th>
                                    <th class="text-center">Izin</th>
                                    <th class="text-center">Sakit</th>
                                    <th class="text-center">Alpha</th>
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
                                    <td class="text-center">{{ $item->alpha }}</td>
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
                <h5 class="card-title mb-1">Rekap Kehadiran Guru per Hari</h5>
                <div class="text-muted small">Ringkasan 14 hari terakhir.</div>
            </div>
            <div class="card-body">
                @if(($rekapKehadiranGuruHarian ?? collect())->isEmpty())
                    <div class="alert alert-warning mb-0">Belum ada data kehadiran guru harian.</div>
                @else
                    <div class="table-responsive">
                        <table class="table table-vcenter table-striped">
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
                                        <a href="{{ route('absensi.index', ['tanggal' => $item->tanggal]) }}" class="btn btn-outline-secondary btn-sm">
                                            Lihat Hari Ini
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
</div>

<div class="row mt-3">
    <div class="col-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <div>
                    <h5 class="card-title mb-1">Rekap Nilai per Kelas & Mata Pelajaran</h5>
                    <div class="text-muted small">Menampilkan nilai yang sudah diinput guru pada tahun ajaran dan semester aktif.</div>
                </div>
                <a href="{{ route('rekap_nilai.index') }}" class="btn btn-primary btn-sm">
                    <i class="ti ti-report-analytics me-1"></i>Lihat Rekap Lengkap
                </a>
            </div>
            <div class="card-body">
                @if(($rekapNilaiKelasMapel ?? collect())->isEmpty())
                    <div class="alert alert-warning mb-0">
                        Belum ada data nilai yang diinput guru untuk periode aktif.
                    </div>
                @else
                    <div class="table-responsive">
                        <table class="table table-vcenter table-striped">
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
                                        <a href="{{ route('rekap_nilai.index', ['kelas_id' => $row->kelas_id, 'mapel_id' => $row->mapel_id]) }}" class="btn btn-outline-primary btn-sm">
                                            Detail
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
</div>
@endsection
