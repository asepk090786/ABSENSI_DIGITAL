@extends('layouts.app', ['pageSlug' => 'jadwal-kbm'])

@section('title','Jadwal Mengajar Guru')

@section('content')
<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header">
                <div class="row align-items-center">
                    <div class="col">
                        <h4 class="card-title mb-0">Jadwal Mengajar - {{ $guru->nama }}</h4>
                        <p class="text-muted mb-0 mt-1">
                            <small>
                                <i class="ti ti-id me-1"></i>NIP: {{ $guru->nip ?? '-' }}
                                @if($tahunAjaranAktif && $semesterAktif)
                                | <i class="ti ti-calendar ms-2 me-1"></i>{{ $tahunAjaranAktif->nama }} - {{ $semesterAktif->nama }}
                                @endif
                            </small>
                        </p>
                    </div>
                    <div class="col-auto">
                        <a href="{{ route('jadwal-kbm.index') }}" class="btn btn-secondary btn-sm">
                            <i class="ti ti-arrow-left me-1"></i>Kembali
                        </a>
                        <button onclick="window.print()" class="btn btn-info btn-sm">
                            <i class="ti ti-printer me-1"></i>Cetak
                        </button>
                        <div class="btn-group" role="group">
                            <button type="button" class="btn btn-success btn-sm dropdown-toggle" data-toggle="dropdown" aria-expanded="false">
                                <i class="ti ti-download me-1"></i>Download PDF
                            </button>
                            <ul class="dropdown-menu">
                                <li><a class="dropdown-item" href="{{ route('jadwal-kbm.export-pdf-guru', ['guru' => $guru->id, 'paper_size' => 'a4']) }}" target="_blank">
                                    <i class="ti ti-file-text me-2"></i>A4 (210 x 297 mm)
                                </a></li>
                                <li><a class="dropdown-item" href="{{ route('jadwal-kbm.export-pdf-guru', ['guru' => $guru->id, 'paper_size' => 'f4']) }}" target="_blank">
                                    <i class="ti ti-file-text me-2"></i>F4/Folio (210 x 330 mm)
                                </a></li>
                                <li><a class="dropdown-item" href="{{ route('jadwal-kbm.export-pdf-guru', ['guru' => $guru->id, 'paper_size' => 'legal']) }}" target="_blank">
                                    <i class="ti ti-file-text me-2"></i>Legal (216 x 356 mm)
                                </a></li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
            <div class="card-body">
                @if($jadwalGuru->isEmpty())
                    <div class="alert alert-info">
                        <i class="ti ti-info-circle me-2"></i>
                        Belum ada jadwal mengajar untuk guru ini.
                    </div>
                @else
                    @php
                        $hariList = ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
                    @endphp

                    @foreach($hariList as $hari)
                        @php
                            $jadwalHari = $jadwalGuru->get($hari, collect());
                        @endphp
                        
                        @if($jadwalHari->isNotEmpty())
                        <div class="mb-4">
                            <h5 class="border-bottom pb-2 mb-3">
                                <i class="ti ti-calendar me-2"></i>{{ $hari }}
                            </h5>
                            <div class="table-responsive">
                                <table class="table table-bordered table-hover">
                                    <thead class="table-light">
                                        <tr>
                                            <th width="10%" class="text-center">Jam Ke</th>
                                            <th width="20%">Waktu</th>
                                            <th width="30%">Mata Pelajaran</th>
                                            <th width="40%">Kelas</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($jadwalHari->sortBy('jam_ke') as $jadwal)
                                        <tr>
                                            <td class="text-center">{{ $jadwal->jam_ke }}</td>
                                            <td>
                                                <i class="ti ti-clock me-1"></i>
                                                {{ $jadwal->jamBelajar->jam_mulai }} - {{ $jadwal->jamBelajar->jam_selesai }}
                                            </td>
                                            <td>
                                                <strong>{{ $jadwal->mataPelajaran->nama_mapel }}</strong>
                                                <br>
                                                <small class="text-muted">
                                                    <i class="ti ti-book me-1"></i>{{ $jadwal->mataPelajaran->kode_mapel }}
                                                </small>
                                            </td>
                                            <td>
                                                <span class="badge bg-primary">{{ $jadwal->kelas->nama_kelas }}</span>
                                                @if($jadwal->kelas->tingkat_kelas)
                                                <small class="text-muted ms-2">Tingkat: {{ $jadwal->kelas->tingkat_kelas }}</small>
                                                @endif
                                                @if($jadwal->kelas->jurusan)
                                                <small class="text-muted ms-2">Jurusan: {{ $jadwal->kelas->jurusan }}</small>
                                                @endif
                                            </td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        @endif
                    @endforeach

                    <!-- Tugas Guru (Assignment Info) -->
                    @if($tugasGuru->isNotEmpty())
                    <div class="card bg-light mt-4 mb-4">
                        <div class="card-body">
                            <h5 class="card-title">
                                <i class="ti ti-clipboard-check me-2"></i>Tugas Mengajar Guru
                            </h5>
                            <p class="text-muted mb-3">
                                <small>Daftar mata pelajaran yang ditugaskan kepada guru ini</small>
                            </p>
                            <div class="table-responsive">
                                <table class="table table-bordered table-sm">
                                    <thead class="table-secondary">
                                        <tr>
                                            <th width="5%">No</th>
                                            <th width="40%">Mata Pelajaran</th>
                                            <th width="15%">Tingkat</th>
                                            <th width="25%">Kelas</th>
                                            <th width="15%">Status</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($tugasGuru as $index => $tugas)
                                        <tr>
                                            <td class="text-center">{{ $index + 1 }}</td>
                                            <td>
                                                <strong>{{ $tugas->mataPelajaran->nama_mapel }}</strong>
                                                @if($tugas->mataPelajaran->kode_mapel)
                                                <br><small class="text-muted">{{ $tugas->mataPelajaran->kode_mapel }}</small>
                                                @endif
                                            </td>
                                            <td><span class="badge bg-info">{{ $tugas->tingkat_kelas }}</span></td>
                                            <td>
                                                @if($tugas->kelas)
                                                    <span class="badge bg-primary">{{ $tugas->kelas->nama_kelas }}</span>
                                                @else
                                                    <span class="text-muted">Semua kelas tingkat {{ $tugas->tingkat_kelas }}</span>
                                                @endif
                                            </td>
                                            <td>
                                                @if($tugas->is_active)
                                                    <span class="badge bg-success">Aktif</span>
                                                @else
                                                    <span class="badge bg-secondary">Tidak Aktif</span>
                                                @endif
                                            </td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    @endif

                    <!-- Summary -->
                    <div class="alert alert-light mt-4">
                        <div class="row">
                            <div class="col-md-4">
                                <strong>Total Jam Mengajar:</strong> 
                                {{ $jadwalGuru->flatten()->count() }} jam/minggu
                            </div>
                            <div class="col-md-4">
                                <strong>Jumlah Kelas:</strong> 
                                {{ $jadwalGuru->flatten()->pluck('kelas_id')->unique()->count() }} kelas
                            </div>
                            <div class="col-md-4">
                                <strong>Jumlah Mata Pelajaran:</strong> 
                                {{ $jadwalGuru->flatten()->pluck('mata_pelajaran_id')->unique()->count() }} mapel
                            </div>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

@if(!$jadwalGuru->isEmpty())
<!-- Tabel Ringkasan Mingguan -->
<div class="row mt-4">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">Ringkasan Jadwal Mingguan</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered text-center">
                        <thead class="table-primary">
                            <tr>
                                <th>Jam</th>
                                @foreach($hariList as $hari)
                                <th>{{ $hari }}</th>
                                @endforeach
                            </tr>
                        </thead>
                        <tbody>
                            @php
                                $maxJam = $jadwalGuru->flatten()->max('jam_ke') ?? 10;
                            @endphp
                            @for($jam = 1; $jam <= $maxJam; $jam++)
                            <tr>
                                <td><strong>{{ $jam }}</strong></td>
                                @foreach($hariList as $hari)
                                    @php
                                        $jadwalHari = $jadwalGuru->get($hari, collect());
                                        $jadwalJam = $jadwalHari->firstWhere('jam_ke', $jam);
                                    @endphp
                                    <td class="{{ $jadwalJam ? 'bg-light' : '' }}">
                                        @if($jadwalJam)
                                            <div class="small">
                                                <strong>{{ $jadwalJam->mataPelajaran->nama_mapel }}</strong><br>
                                                <span class="text-muted">{{ $jadwalJam->kelas->nama_kelas }}</span>
                                            </div>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                @endforeach
                            </tr>
                            @endfor
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endif

@endsection

@push('styles')
<style>
    /* Print optimization */
    @media print {
        * {
            margin: 0;
            padding: 0;
        }
        
        .card-header .btn,
        .btn-list,
        nav,
        .sidebar,
        .alert {
            display: none !important;
        }
        
        .card {
            border: none !important;
            box-shadow: none !important;
            margin-bottom: 0 !important;
            padding: 0 !important;
            page-break-inside: avoid;
        }
        
        .card-header {
            display: none !important;
        }
        
        .card-body {
            padding: 2px !important;
        }
        
        .row {
            margin: 0 !important;
        }
        
        .col-md-12 {
            padding: 0 !important;
        }
        
        body {
            background: white;
            margin: 0;
            padding: 2px;
            font-size: 6px;
            line-height: 1;
        }
        
        h4 {
            font-size: 8px !important;
            margin: 0 !important;
            padding: 0 !important;
            font-weight: bold;
        }
        
        h5 {
            font-size: 7px !important;
            margin: 2px 0 1px 0 !important;
            padding: 0 !important;
            font-weight: bold;
            color: #1d4ed8;
            border-bottom: 1px solid #ddd;
            padding-bottom: 1px !important;
        }
        
        .mb-4 {
            margin-bottom: 2px !important;
            page-break-inside: avoid;
        }
        
        .mb-3 {
            margin-bottom: 1px !important;
        }
        
        .mt-4 {
            margin-top: 2px !important;
        }
        
        .table {
            font-size: 6px !important;
            margin-bottom: 2px !important;
            border-collapse: collapse;
        }
        
        .table thead {
            background-color: #2563eb !important;
            color: white !important;
        }
        
        .table th {
            padding: 1px 2px !important;
            font-weight: bold;
            border: 0.5px solid #999 !important;
            text-align: center;
            line-height: 1;
        }
        
        .table td {
            padding: 1px 2px !important;
            border: 0.5px solid #999 !important;
            vertical-align: top;
            line-height: 1.1;
        }
        
        .table tbody tr {
            height: 12px;
        }
        
        .table tbody tr:nth-child(odd) {
            background-color: #f9fafb !important;
        }
        
        .table tbody tr:nth-child(even) {
            background-color: #fff !important;
        }
        
        .table-light {
            background-color: #e8ecf1 !important;
        }
        
        .table-light th {
            background-color: #1d4ed8 !important;
            color: white !important;
        }
        
        .table-primary {
            background-color: #2563eb !important;
        }
        
        .table-primary th {
            background-color: #2563eb !important;
            color: white !important;
            padding: 1px 2px !important;
        }
        
        .table-primary td {
            background-color: #f0f4f8 !important;
        }
        
        .table-responsive {
            margin-bottom: 2px !important;
        }
        
        .small {
            font-size: 5px !important;
        }
        
        .text-muted {
            color: #666 !important;
        }
        
        .text-center {
            text-align: center !important;
        }
        
        .badge {
            font-size: 5px !important;
            padding: 0.5px 2px !important;
            display: inline-block;
        }
        
        .bg-primary {
            background-color: #2563eb !important;
            color: white !important;
        }
        
        .alert {
            font-size: 6px !important;
            margin: 0 !important;
            padding: 2px !important;
            display: none !important;
        }
        
        .border-bottom {
            border-bottom: 1px solid #ddd !important;
            padding-bottom: 0.5px !important;
        }
        
        .pb-2 {
            padding-bottom: 0.5px !important;
        }
        
        .ms-2 {
            margin-left: 2px !important;
        }
        
        .me-1 {
            margin-right: 1px !important;
        }
        
        .me-2 {
            margin-right: 2px !important;
        }
        
        .ti {
            display: none !important;
        }
        
        /* Hide the summary table on second page */
        .row:last-of-type {
            display: none !important;
        }
    }
</style>
@endpush
