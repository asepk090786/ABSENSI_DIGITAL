@extends('layouts.app')

@section('content')
<style>
    .hover-shadow {
        transition: all 0.3s ease;
    }
    .hover-shadow:hover {
        transform: translateY(-5px);
        box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15) !important;
    }
    /* Ensure badge numbers are white on this page */
    .table-tabler .badge, .card .badge { color: #fff !important; }
</style>

<div class="container-fluid">
    @php
        $isAdminOrKepala = auth()->user()->hasAnyRole(['Admin', 'Kepala Sekolah']);
        $isGuruBk = $isGuruBk ?? auth()->user()->hasRole('Guru BK');
        $isSiswaWithoutClassPosition = auth()->user()->hasRole('Siswa') && ! auth()->user()->hasClassPosition();
    @endphp

        @if($kelasQuickAccess->isNotEmpty() && !($isGuruPiket ?? false))
    
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header bg-primary-subtle">
                    <h5 class="card-title fw-semibold m-0">
                        <i class="ti ti-clock-play me-2"></i>
                        @if($isAdminOrKepala)
                            Menu Akses Cepat - Absensi Kelas Aktif
                        @elseif($isGuruBk)
                            Menu Akses Cepat - Absensi Kelas Binaan BK
                        @else
                            Menu Akses Cepat - Absen Kelas Anda
                        @endif
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        @foreach($kelasQuickAccess as $kelas)
                        @php
                            // Ekstrak tingkat kelas dari nama kelas (ambil 2 digit pertama)
                            $namaKelas = $kelas->nama_kelas;
                            $tingkatKelas = (int) substr($namaKelas, 0, 2);
                            
                            // Tentukan warna dan class berdasarkan tingkat kelas
                            if ($tingkatKelas == 10) {
                                $borderColor = '#3b82f6';
                                $bgColor = 'rgba(59, 130, 246, 0.05)';
                                $iconColor = '#3b82f6';
                                $btnColor = '#3b82f6';
                                $btnHover = '#2563eb';
                                $badgeColor = '#3b82f6';
                                $badgeBg = 'rgba(59, 130, 246, 0.1)';
                                $tingkatLabel = 'Kelas X';
                            } elseif ($tingkatKelas == 11) {
                                $borderColor = '#10b981';
                                $bgColor = 'rgba(16, 185, 129, 0.05)';
                                $iconColor = '#10b981';
                                $btnColor = '#10b981';
                                $btnHover = '#059669';
                                $badgeColor = '#10b981';
                                $badgeBg = 'rgba(16, 185, 129, 0.1)';
                                $tingkatLabel = 'Kelas XI';
                            } elseif ($tingkatKelas == 12) {
                                $borderColor = '#f59e0b';
                                $bgColor = 'rgba(245, 158, 11, 0.05)';
                                $iconColor = '#f59e0b';
                                $btnColor = '#f59e0b';
                                $btnHover = '#d97706';
                                $badgeColor = '#f59e0b';
                                $badgeBg = 'rgba(245, 158, 11, 0.1)';
                                $tingkatLabel = 'Kelas XII';
                            } else {
                                $borderColor = '#8b5cf6';
                                $bgColor = 'rgba(139, 92, 246, 0.05)';
                                $iconColor = '#8b5cf6';
                                $btnColor = '#8b5cf6';
                                $btnHover = '#7c3aed';
                                $badgeColor = '#8b5cf6';
                                $badgeBg = 'rgba(139, 92, 246, 0.1)';
                                $tingkatLabel = 'Kelas Lainnya';
                            }
                        @endphp
                        <div class="col-md-6 col-lg-4 col-xl-3">
                            <div class="card border-2 h-100 hover-shadow" 
                                 style="border-color: {{ $borderColor }} !important; 
                                        background: linear-gradient(135deg, {{ $bgColor }} 0%, {{ $bgColor }} 100%);
                                        transition: all 0.3s ease;">
                                <div class="card-body text-center">
                                    <div class="card-class-badge" 
                                         style="background-color: {{ $badgeBg }}; 
                                                color: {{ $badgeColor }};
                                                display: inline-block;
                                                padding: 0.25rem 0.75rem;
                                                border-radius: 0.5rem;
                                                font-size: 0.85rem;
                                                font-weight: 600;
                                                margin-bottom: 0.5rem;">
                                        {{ $tingkatLabel }}
                                    </div>
                                    <div class="mb-2">
                                        <i class="ti ti-school" style="font-size: 48px; color: {{ $iconColor }} !important;"></i>
                                    </div>
                                    <h5 class="card-title mb-2">{{ $kelas->nama_kelas }}</h5>
                                    <p class="text-muted small mb-2">
                                        @if(optional($sekolah)->tampilkan_nama_wali_kelas !== false)
                                            @if($kelas->waliKelas)
                                                <i class="ti ti-user me-1"></i>{{ $kelas->waliKelas->nama }}
                                            @endif
                                        @else
                                            <i class="ti ti-alert-triangle me-1"></i>
                                            {!! optional($sekolah)->wali_kelas_hidden_message ?: 'Info wali kelas disembunyikan oleh administrator.' !!}
                                        @endif
                                    </p>
                                    @unless($isSiswaWithoutClassPosition)
                                    <a href="{{ route('absensi.create', ['kelas_id' => $kelas->id]) }}" 
                                       class="btn btn-sm w-100" 
                                       style="background-color: {{ $btnColor }} !important; 
                                              border-color: {{ $btnColor }} !important; 
                                              color: white !important;
                                              transition: all 0.3s ease;"
                                       onmouseover="this.style.backgroundColor='{{ $btnHover }}'; this.style.borderColor='{{ $btnHover }}';"
                                       onmouseout="this.style.backgroundColor='{{ $btnColor }}'; this.style.borderColor='{{ $btnColor }}';">
                                        <i class="ti ti-check me-1"></i>
                                        @if($isAdminOrKepala)
                                            Buat Absensi
                                        @elseif($isGuruBk)
                                            Absen Kelas Binaan
                                        @else
                                            Absen Kelas Ini
                                        @endif
                                    </a>
                                    @endunless
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif

    @if($isGuruBk && !($isGuruPiket ?? false))
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header border-0 pt-3 pb-2">
                    <h5 class="card-title fw-semibold mb-1">Monitoring Siswa Kelas Binaan BK</h5>
                    <p class="card-category mb-0">Menampilkan siswa terlambat dan tidak masuk dari kelas binaan pada tanggal dipilih</p>
                </div>
                <div class="card-body">
                    <form method="GET" action="{{ route('absensi.index') }}" class="row g-2 align-items-end mb-2">
                        <div class="col-12 col-md-4 col-lg-3">
                            <label for="tanggal-bk" class="form-label mb-1">Tanggal Kehadiran</label>
                            <input
                                type="date"
                                id="tanggal-bk"
                                name="tanggal"
                                class="form-control"
                                value="{{ $selectedTanggal ?? now()->format('Y-m-d') }}"
                            >
                        </div>
                        <div class="col-auto">
                            <button type="submit" class="btn btn-primary">
                                <i class="ti ti-search me-1"></i>Tampilkan
                            </button>
                        </div>
                        <div class="col-auto">
                            <a href="{{ route('absensi.bk-monitoring.export', ['tanggal' => ($selectedTanggal ?? now()->format('Y-m-d'))]) }}" class="btn btn-success">
                                <i class="ti ti-file-export me-1"></i>Export Excel
                            </a>
                        </div>
                    </form>

                    @php
                        $totalTerlambatBk = ($siswaPerluPerhatian ?? collect())
                            ->filter(function ($row) {
                                return in_array(strtolower((string) ($row->status ?? '')), ['terlambat', 'telat'], true);
                            })
                            ->unique('siswa_id')
                            ->count();

                        $totalTidakMasukBk = ($siswaPerluPerhatian ?? collect())
                            ->filter(function ($row) {
                                return in_array(strtolower((string) ($row->status ?? '')), ['alpa', 'alpha', 'alfa', 'absen'], true);
                            })
                            ->unique('siswa_id')
                            ->count();
                    @endphp

                    <div class="d-flex flex-wrap gap-2 mb-2">
                        <span class="badge" style="background:#f59e0b;color:#fff;">Total Terlambat: {{ $totalTerlambatBk }}</span>
                        <span class="badge bg-danger">Total Tidak Masuk: {{ $totalTidakMasukBk }}</span>
                    </div>

                    @if(($siswaPerluPerhatian ?? collect())->isEmpty())
                        <div class="alert alert-info mb-0">
                            <i class="ti ti-info-circle"></i> Tidak ada siswa terlambat/tidak masuk pada tanggal ini.
                        </div>
                    @else
                        <div class="table-responsive">
                            <table class="table table-vcenter table-hover table-tabler">
                                <thead>
                                    <tr>
                                        <th>No</th>
                                        <th>Tanggal</th>
                                        <th>Kelas</th>
                                        <th>Siswa</th>
                                        <th>Status</th>
                                        <th>Guru Penginput</th>
                                        <th>Keterangan</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach(($siswaPerluPerhatian ?? collect()) as $index => $row)
                                        @php
                                            $statusNormalized = strtolower((string) ($row->status ?? ''));
                                            $isTerlambat = in_array($statusNormalized, ['terlambat', 'telat'], true);
                                        @endphp
                                        <tr>
                                            <td>{{ $index + 1 }}</td>
                                            <td>{{ \Carbon\Carbon::parse($row->tanggal)->format('d/m/Y') }}</td>
                                            <td>{{ $row->nama_kelas ?? '-' }}</td>
                                            <td>{{ $row->nama_siswa ?? '-' }}</td>
                                            <td>
                                                @if($isTerlambat)
                                                    <span class="badge" style="background:#f59e0b;color:#fff;">Terlambat</span>
                                                @else
                                                    <span class="badge bg-danger">Tidak Masuk</span>
                                                @endif
                                            </td>
                                            <td>{{ $row->nama_guru ?? '-' }}</td>
                                            <td>{{ $row->keterangan ?: '-' }}</td>
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
    @endif

    @if($isAdminOrKepala || ($isGuruPiket ?? false))
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header border-0 pt-3 pb-2">
                    <h5 class="card-title fw-semibold mb-1">Rekap Absensi Siswa per Kelas</h5>
                    <p class="card-category mb-0">Statistik siswa dihitung per hari (status dominan harian per siswa), bukan per jam mata pelajaran</p>
                </div>
                <div class="card-body">
                    <form method="GET" action="{{ route('absensi.index') }}" class="row g-2 align-items-end mb-2">
                        <div class="col-12 col-md-4 col-lg-3">
                            <label for="tanggal" class="form-label mb-1">Tanggal Kehadiran</label>
                            <input
                                type="date"
                                id="tanggal"
                                name="tanggal"
                                class="form-control"
                                value="{{ $selectedTanggal ?? now()->format('Y-m-d') }}"
                            >
                        </div>
                        <div class="col-auto">
                            <button type="submit" class="btn btn-primary">
                                <i class="ti ti-search me-1"></i>Tampilkan
                            </button>
                        </div>
                        @unless($isSiswaWithoutClassPosition)
                        <div class="col-auto">
                            <a href="{{ route('absensi.create') }}" class="btn btn-success">
                                <i class="ti ti-plus me-1"></i>Tambah Absensi
                            </a>
                        </div>
                        @endunless
                        @if($isAdminOrKepala)
                        <div class="col-auto">
                            <a href="{{ route('absensi.generate.form') }}" class="btn btn-primary">
                                <i class="ti ti-bolt me-1"></i>Generate Absensi
                            </a>
                        </div>
                        @endif
                    </form>

                    @if($isAdminOrKepala)
                    <form method="POST" action="{{ route('absensi.destroy-by-date') }}" class="row g-2 align-items-end mb-2" onsubmit="return confirm('Yakin ingin menghapus semua data absensi pada tanggal yang dipilih? Tindakan ini tidak dapat dibatalkan.');">
                        @csrf
                        @method('DELETE')
                        <div class="col-12 col-md-4 col-lg-3">
                            <label for="tanggal_hapus" class="form-label mb-1">Hapus Data Tanggal</label>
                            <input
                                type="date"
                                id="tanggal_hapus"
                                name="tanggal_hapus"
                                class="form-control"
                                value="{{ $selectedTanggal ?? now()->format('Y-m-d') }}"
                                required
                            >
                        </div>
                        <div class="col-auto">
                            <button type="submit" class="btn btn-danger">
                                <i class="ti ti-trash me-1"></i>Hapus Absensi per Tanggal
                            </button>
                        </div>
                    </form>
                    @endif

                    @php
                        $totalHadirHarian = ($rekapPerKelas ?? collect())->sum('total_hadir');
                        $totalTerlambatHarian = ($rekapPerKelas ?? collect())->sum('total_terlambat');
                        $totalSakitHarian = ($rekapPerKelas ?? collect())->sum('total_sakit');
                        $totalIzinHarian = ($rekapPerKelas ?? collect())->sum('total_izin');
                        $totalAlpaHarian = ($rekapPerKelas ?? collect())->sum(function($r){ return $r->total_alpa ?? $r->total_alpha ?? 0; });
                    @endphp

                        <div class="d-flex flex-wrap gap-2 mb-2">
                        <span class="badge bg-success">Total Hadir: {{ $totalHadirHarian }}</span>
                        <span class="badge" style="background:#f59e0b;color:#fff;">Total Terlambat: {{ $totalTerlambatHarian }}</span>
                        <span class="badge bg-danger">Total Sakit: {{ $totalSakitHarian }}</span>
                        <span class="badge bg-info">Total Izin: {{ $totalIzinHarian }}</span>
                        <span class="badge bg-danger">Total Alpa: {{ $totalAlpaHarian }}</span>
                    </div>

                    @if(($rekapPerKelas ?? collect())->isEmpty())
                        <div class="alert alert-info mb-0">
                            <i class="ti ti-info-circle"></i> Belum ada kelas aktif/digunakan pada periode berjalan.
                        </div>
                    @else
                        <div class="table-responsive">
                            <table class="table table-vcenter table-hover table-tabler">
                                <thead>
                                    <tr>
                                        <th>No</th>
                                        <th>Kelas</th>
                                        <th>Wali Kelas</th>
                                        <th>Pertemuan</th>
                                        <th>Hadir</th>
                                        <th>Terlambat</th>
                                        <th>Sakit</th>
                                        <th>Izin</th>
                                        <th>Alpa</th>
                                        <th>Total Data Siswa</th>
                                        <th>Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($rekapPerKelas as $index => $rekap)
                                        <tr>
                                            <td>{{ $index + 1 }}</td>
                                            <td>{{ $rekap->kelas->nama_kelas ?? '-' }}</td>
                                            <td>{{ $rekap->kelas->waliKelas->nama ?? '-' }}</td>
                                            <td><span class="badge bg-primary">{{ $rekap->total_pertemuan }}</span></td>
                                            <td><span class="badge bg-success">{{ $rekap->total_hadir }}</span></td>
                                            <td><span class="badge" style="background:#f59e0b;color:#fff;">{{ $rekap->total_terlambat }}</span></td>
                                            <td><span class="badge bg-danger">{{ $rekap->total_sakit }}</span></td>
                                            <td><span class="badge bg-info">{{ $rekap->total_izin }}</span></td>
                                            <td><span class="badge bg-danger">{{ $rekap->total_alpa ?? $rekap->total_alpha }}</span></td>
                                            <td><span class="badge bg-secondary">{{ $rekap->total_data_siswa }}</span></td>
                                            <td>
                                                @if($isGuruPiket || $isAdminOrKepala)
                                                    <a href="{{ route('absensi.create', ['kelas_id' => $rekap->kelas->id, 'tanggal' => $selectedTanggal ?? now()->format('Y-m-d')]) }}" class="btn btn-sm btn-outline-primary">Edit Absensi</a>
                                                @else
                                                    -
                                                @endif
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
    @endif

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h3 class="card-title">
                        @if($isGuruPiket ?? false)
                            Data Absensi Siswa (Input Guru)
                        @else
                            Data Absensi Kelas
                        @endif
                    </h3>
                    @if(!($isAdminOrKepala || ($isGuruPiket ?? false)))
                        <div class="d-flex align-items-center">
                            @php $currentUser = auth()->user(); @endphp
                            <div class="d-flex align-items-center gap-2 me-3">
                                <label class="mb-0">Periode:</label>
                                <select id="print_period" class="form-select form-select-sm" style="width:140px;">
                                    <option value="daily">Harian</option>
                                    <option value="weekly">Mingguan</option>
                                    <option value="monthly">Bulanan</option>
                                </select>

                                <div id="print_time_container" style="min-width:220px;">
                                    <input type="date" id="print_tanggal" class="form-control form-control-sm" style="width:150px;" value="{{ $selectedTanggal ?? now()->format('Y-m-d') }}">
                                </div>
                            </div>

                            <div class="ms-auto d-flex align-items-center gap-2">
                                <button id="btn_print_rekap" class="btn btn-sm btn-success btn-modern">
                                    <i class="ti ti-file-text me-1"></i> Cetak Rekap Absensi
                                </button>

                                @unless($isSiswaWithoutClassPosition)
                                <a href="{{ route('absensi.create') }}" class="btn btn-primary btn-sm">
                                    <i class="ti ti-plus"></i> Tambah Absensi
                                </a>
                                @endunless
                            </div>
                        </div>
                    @endif
                </div>
                <div class="card-body">
                    @if($items->isEmpty())
                        <div class="alert alert-info">
                            <i class="ti ti-info-circle"></i> Belum ada data absensi.
                        </div>
                    @else
                        <form method="GET" action="{{ route('absensi.index') }}" class="row g-2 align-items-end mb-2">
                            <div class="col-12 col-md-3">
                                <label class="form-label mb-1">Tanggal</label>
                                <input type="date" name="tanggal" class="form-control" value="{{ $selectedTanggal ?? now()->format('Y-m-d') }}">
                            </div>
                            <div class="col-12 col-md-3">
                                <label class="form-label mb-1">Kelas</label>
                                <select name="kelas_id" class="form-select" {{ ($isSiswaOfficer ?? false) ? 'disabled' : '' }}>
                                    <option value="">Semua Kelas</option>
                                    @foreach($kelasList ?? collect() as $k)
                                        <option value="{{ $k->id }}" {{ (isset($filterKelasId) && $filterKelasId == $k->id) ? 'selected' : '' }}>{{ $k->nama_kelas }}</option>
                                    @endforeach
                                </select>
                                @if($isSiswaOfficer ?? false)
                                    <input type="hidden" name="kelas_id" value="{{ $filterKelasId }}">
                                @endif
                            </div>
                            <div class="col-12 col-md-3">
                                <label class="form-label mb-1">Guru</label>
                                <select name="guru_id" class="form-select">
                                    <option value="">Semua Guru</option>
                                    @foreach($guruList ?? collect() as $g)
                                        <option value="{{ $g->id }}" {{ (isset($filterGuruId) && $filterGuruId == $g->id) ? 'selected' : '' }}>{{ $g->nama }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-12 col-md-3">
                                <label class="form-label mb-1">Cari</label>
                                <div class="d-flex gap-2">
                                    <input type="text" name="q" class="form-control" placeholder="Cari kelas atau guru..." value="{{ $filterQuery ?? '' }}">
                                    <button class="btn btn-primary">Tampilkan</button>
                                </div>
                            </div>
                        </form>

                        <div class="table-responsive">
                            <table class="table table-vcenter table-hover table-tabler">
                                <thead>
                                    <tr>
                                        <th>No</th>
                                        <th>Tanggal</th>
                                        <th>Kelas</th>
                                        <th>Guru</th>
                                        <th>Jam Belajar</th>
                                        <th>Status Kelas</th>
                                        <th>Tahun Ajaran</th>
                                        <th>Semester</th>
                                        <th>Hadir</th>
                                        <th>Terlambat</th>
                                        <th>Sakit</th>
                                        <th>Izin</th>
                                        <th>Tidak Hadir</th>
                                        <th>Jumlah Siswa</th>
                                        <th>Aksi</th>
                                        <th>Aksi Piket</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($items as $index => $item)
                                        @php
                                            $countStatus = function($statuses) use ($item) {
                                                $needles = collect($statuses)->map(fn($s) => strtolower((string) $s))->all();
                                                return $item->absensiSiswa->filter(function($row) use ($needles) {
                                                    return in_array(strtolower((string) ($row->status ?? '')), $needles, true);
                                                })->count();
                                            };
                                            $hadirCount = $countStatus(['hadir']);
                                            $terlambatCount = $countStatus(['terlambat', 'telat']);
                                            $sakitCount = $countStatus(['sakit']);
                                            $izinCount = $countStatus(['izin', 'ijin']);
                                            $alpaCount = $countStatus(['alpa', 'alpha', 'alfa', 'absen']);
                                        @endphp
                                        <tr>
                                            <td>{{ $index + 1 }}</td>
                                            <td>{{ $item->tanggal->format('d/m/Y') }}</td>
                                            <td>{{ $item->kelas->nama_kelas ?? '-' }}</td>
                                            <td>{{ $item->guru->nama ?? '-' }}</td>
                                            <td>{{ $item->jamBelajar->jam_mulai ?? '-' }} - {{ $item->jamBelajar->jam_selesai ?? '-' }}</td>
                                            <td>
                                                @if($item->status_kelas)
                                                    <span class="badge bg-success">{{ $item->status_kelas }}</span>
                                                @else
                                                    <span class="badge bg-secondary">-</span>
                                                @endif
                                            </td>
                                            <td>{{ $item->tahunAjaran->nama_tahun ?? '-' }}</td>
                                            <td>{{ $item->semester->nama_semester ?? '-' }}</td>
                                            <td><span class="badge bg-success">{{ $hadirCount }}</span></td>
                                            <td><span class="badge" style="background:#f59e0b;color:#fff;">{{ $terlambatCount }}</span></td>
                                            <td><span class="badge bg-danger">{{ $sakitCount }}</span></td>
                                            <td><span class="badge bg-info">{{ $izinCount }}</span></td>
                                            <td><span class="badge bg-danger">{{ $alpaCount }}</span></td>
                                            <td>
                                                <span class="badge bg-info">
                                                    {{ $item->absensiSiswa->count() }} Siswa
                                                </span>
                                            </td>
                                            <td>
                                                @unless($isSiswaWithoutClassPosition)
                                                <div class="btn-group" role="group">
                                                    <a href="{{ route('absensi.show', $item->id) }}" class="btn btn-sm btn-info btn-modern">
                                                        <i class="ti ti-eye"></i>
                                                    </a>
                                                    <a href="{{ route('absensi.edit', $item->id) }}" class="btn btn-sm btn-warning">
                                                        <i class="ti ti-edit"></i>
                                                    </a>
                                                    <form action="{{ route('absensi.destroy', $item->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Yakin ingin menghapus data ini?')">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="btn btn-sm btn-danger">
                                                            <i class="ti ti-trash"></i>
                                                        </button>
                                                    </form>
                                                </div>
                                                @endunless
                                            </td>
                                            <td>
                                                @if($isGuruPiket || $isAdminOrKepala)
                                                    <a href="{{ route('absensi.edit', $item->id) }}" class="btn btn-sm btn-outline-primary">Edit Absensi</a>
                                                @else
                                                    -
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif

                    <script>
                    document.addEventListener('DOMContentLoaded', function(){
                        var periodSelect = document.getElementById('print_period');
                        var container = document.getElementById('print_time_container');
                        var initialDate = document.getElementById('print_tanggal').value || new Date().toISOString().slice(0,10);
                        var user = {!! json_encode(auth()->user()->hasRole('Siswa') ? ['siswa'=>true,'kelas_id'=>auth()->user()->siswa->kelas_id ?? null] : ['siswa'=>false]) !!};

                        function isoDate(d) {
                            return d.getFullYear() + '-' + String(d.getMonth()+1).padStart(2,'0') + '-' + String(d.getDate()).padStart(2,'0');
                        }

                        function formatDDMM(d) {
                            return String(d.getDate()).padStart(2,'0') + '/' + String(d.getMonth()+1).padStart(2,'0');
                        }

                        function getMonday(d) {
                            var date = new Date(d.getFullYear(), d.getMonth(), d.getDate());
                            var day = date.getDay();
                            var diff = (day + 6) % 7; // days since Monday
                            date.setDate(date.getDate() - diff);
                            return date;
                        }

                        function renderDaily(dateVal) {
                            container.innerHTML = '<input type="date" id="print_tanggal" class="form-control form-control-sm" style="width:150px;" value="'+dateVal+'">';
                        }

                        function renderWeekly(dateVal) {
                            var d = new Date(dateVal);
                            var year = d.getFullYear();
                            var month = d.getMonth();
                            var firstOfMonth = new Date(year, month, 1);
                            var lastOfMonth = new Date(year, month + 1, 0);

                            var weekStarts = [];
                            for (var day = 1; day <= lastOfMonth.getDate(); day++) {
                                var cur = new Date(year, month, day);
                                var monday = getMonday(cur);
                                var key = isoDate(monday);
                                if (!weekStarts.includes(key)) weekStarts.push(key);
                            }

                            var sel = document.createElement('select');
                            sel.id = 'print_week';
                            sel.className = 'form-select form-select-sm';
                            sel.style.width = '220px';

                            weekStarts.forEach(function(ws, idx){
                                var wsDate = new Date(ws);
                                var weDate = new Date(wsDate);
                                weDate.setDate(wsDate.getDate() + 6);
                                // intersect with month
                                var startLabel = new Date(Math.max(wsDate, firstOfMonth));
                                var endLabel = new Date(Math.min(weDate, lastOfMonth));
                                var label = 'Minggu ke ' + (idx+1) + ' (' + formatDDMM(startLabel) + ' - ' + formatDDMM(endLabel) + ')';
                                var opt = document.createElement('option');
                                opt.value = isoDate(wsDate);
                                opt.text = label;
                                sel.appendChild(opt);
                            });

                            container.innerHTML = '';
                            container.appendChild(sel);
                        }

                        function renderMonthly(dateVal) {
                            var d = new Date(dateVal);
                            var currentYear = d.getFullYear();
                            var startYear = currentYear - 1;
                            var endYear = currentYear + 0;

                            var sel = document.createElement('select');
                            sel.id = 'print_month';
                            sel.className = 'form-select form-select-sm';
                            sel.style.width = '220px';

                            for (var y = startYear; y <= endYear; y++) {
                                for (var m = 0; m < 12; m++) {
                                    var opt = document.createElement('option');
                                    var dval = new Date(y, m, 1);
                                    opt.value = isoDate(dval);
                                    opt.text = dval.toLocaleString('default', { month: 'long' }) + ' ' + y;
                                    // preselect if same month/year as dateVal
                                    if (y === d.getFullYear() && m === d.getMonth()) opt.selected = true;
                                    sel.appendChild(opt);
                                }
                            }

                            container.innerHTML = '';
                            container.appendChild(sel);
                        }

                        function renderTimeControl(period, dateVal) {
                            if (!dateVal) dateVal = initialDate;
                            if (period === 'daily') renderDaily(dateVal);
                            else if (period === 'weekly') renderWeekly(dateVal);
                            else if (period === 'monthly') renderMonthly(dateVal);
                        }

                        // initial render
                        renderTimeControl(periodSelect.value, initialDate);

                        // change handlers
                        periodSelect.addEventListener('change', function(){
                            var curDate = document.querySelector('#print_tanggal') ? document.querySelector('#print_tanggal').value : initialDate;
                            renderTimeControl(this.value, curDate);
                        });

                        // when date input changes while in daily mode, update stored value
                        document.addEventListener('change', function(e){
                            if (e.target && e.target.id === 'print_tanggal' && periodSelect.value === 'weekly') {
                                // re-render weekly based on new date
                                renderTimeControl('weekly', e.target.value);
                            }
                        });

                        var btn = document.getElementById('btn_print_rekap');
                        if (!btn) return;
                        btn.addEventListener('click', function(e){
                            e.preventDefault();
                            var period = document.getElementById('print_period').value;
                            var tanggal = '';
                            if (period === 'daily') {
                                tanggal = (document.getElementById('print_tanggal') || {value: initialDate}).value;
                            } else if (period === 'weekly') {
                                var sel = document.getElementById('print_week');
                                tanggal = sel ? sel.value : initialDate;
                            } else if (period === 'monthly') {
                                var msel = document.getElementById('print_month');
                                tanggal = msel ? msel.value : initialDate;
                            }

                            var url = '';
                            // compute explicit range_start and range_end to avoid backend ambiguity
                            var rangeStart = '';
                            var rangeEnd = '';
                            if (period === 'daily') {
                                rangeStart = tanggal;
                                rangeEnd = tanggal;
                            } else if (period === 'weekly') {
                                // tanggal holds the monday start-of-week value
                                var s = new Date(tanggal + 'T00:00:00');
                                var e = new Date(s);
                                e.setDate(s.getDate() + 6);
                                rangeStart = s.toISOString().slice(0,10);
                                rangeEnd = e.toISOString().slice(0,10);
                            } else if (period === 'monthly') {
                                var s = new Date(tanggal + 'T00:00:00');
                                var e = new Date(s.getFullYear(), s.getMonth()+1, 0);
                                rangeStart = s.toISOString().slice(0,10);
                                rangeEnd = e.toISOString().slice(0,10);
                            }

                            if (user.siswa) {
                                url = '{{ route('absensi.laporan-siswa.print') }}' + '?tanggal=' + encodeURIComponent(tanggal) + '&period=' + encodeURIComponent(period) + '&kelas_id=' + encodeURIComponent(user.kelas_id) + '&range_start=' + encodeURIComponent(rangeStart) + '&range_end=' + encodeURIComponent(rangeEnd);
                            } else {
                                url = '{{ route('absensi.guru.print') }}' + '?tanggal=' + encodeURIComponent(tanggal) + '&period=' + encodeURIComponent(period) + '&range_start=' + encodeURIComponent(rangeStart) + '&range_end=' + encodeURIComponent(rangeEnd);
                            }
                            window.open(url, '_blank');
                        });
                    });
                    </script>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
