@extends('layouts.app')

@section('title', $title)

@section('content')
<div class="page-header d-print-none">
    <div class="container-xl">
        <div class="row g-2 align-items-center">
            <div class="col">
                <h2 class="page-title">
                    <i class="ti ti-report-analytics me-2"></i>{{ $title }}
                </h2>
                <div class="text-muted mt-1">
                    {{ $selectedKelas->nama_kelas }}
                    @if($selectedMapel)
                        &middot; {{ $selectedMapel->nama_mapel }}
                    @else
                        &middot; Semua mata pelajaran
                    @endif
                </div>
                @if($tahunAjaranAktif && $semesterAktif)
                    <div class="text-muted small mt-2">
                        <i class="ti ti-calendar me-1"></i>{{ $tahunAjaranAktif->tahun_ajaran }} - <strong>{{ $semesterAktif->nama_semester }}</strong>
                    </div>
                @endif
            </div>
            <div class="col-auto ms-auto d-print-none">
            <div class="col-auto ms-auto d-print-none d-flex gap-2">
                <a href="{{ route('nilai.kategorisasi', array_filter(['kelas_id' => $kelasId, 'mapel_id' => $mapelId])) }}" class="btn btn-outline-warning">
                    <i class="ti ti-adjustments me-1"></i>Atur Kategori
                </a>
                <a href="{{ route('nilai.index', array_filter(['kelas_id' => $kelasId, 'mapel_id' => $mapelId])) }}" class="btn btn-outline-secondary">
                    <i class="ti ti-arrow-left me-1"></i>Kembali
                </a>
            </div>
        </div>
    </div>
</div>

<div class="page-body">
    <div class="container-xl">
        @if($uncategorizedRecordCount > 0)
            <div class="alert alert-warning" role="alert">
                <i class="ti ti-alert-triangle me-2"></i>
                {{ number_format($uncategorizedRecordCount) }} data nilai belum memiliki kategori asesmen dan tidak dimasukkan ke rekap ini.
                            {{ number_format($uncategorizedRecordCount) }} data nilai belum memiliki kategori asesmen dan tidak dimasukkan ke rekap ini.
            </div>
        @endif

        <div class="row row-cards mb-3">
            <div class="col-sm-6 col-lg-3">
                <div class="card card-sm"><div class="card-body">
                    <div class="subheader">Jumlah Siswa</div>
                    <div class="h2 mb-0">{{ number_format($summary['total_siswa']) }}</div>
                </div></div>
            </div>
            <div class="col-sm-6 col-lg-3">
                <div class="card card-sm"><div class="card-body">
                    <div class="subheader">Jumlah Asesmen</div>
                    <div class="h2 mb-0">{{ number_format($summary['total_asesmen']) }}</div>
                </div></div>
            </div>
            <div class="col-sm-6 col-lg-3">
                <div class="card card-sm"><div class="card-body">
                    <div class="subheader">Nilai Terisi</div>
                    <div class="h2 mb-0">{{ number_format($summary['total_nilai']) }}</div>
                </div></div>
            </div>
            <div class="col-sm-6 col-lg-3">
                <div class="card card-sm"><div class="card-body">
                    <div class="subheader">Rata-rata Kelas</div>
                    <div class="h2 mb-0">{{ $summary['rata_rata'] !== null ? number_format($summary['rata_rata'], 2) : '-' }}</div>
                </div></div>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Rekap Nilai Siswa</h3>
            </div>
            <div class="table-responsive">
                <table class="table table-vcenter table-striped table-hover">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Nama Siswa</th>
                            @foreach($assessmentColumns as $column)
                                <th class="text-center">
                                    {{ $column->judul }}
                                    <div class="text-muted small fw-normal">{{ $column->mapel }} · {{ \Carbon\Carbon::parse($column->tanggal)->format('d/m/Y') }}</div>
                                </th>
                            @endforeach
                            <th class="text-center">Jumlah</th>
                            <th class="text-center">Rata-rata</th>
                        </tr>
                    </thead>
                    <tbody>
                        @if($assessmentColumns->isEmpty())
                            <tr>
                                <td colspan="4" class="text-center text-muted py-4">Belum ada data {{ strtolower(str_replace('Rekap ', '', $title)) }} untuk filter ini.</td>
                            </tr>
                        @elseif($students->isEmpty())
                            <tr>
                                <td colspan="{{ 4 + $assessmentColumns->count() }}" class="text-center text-muted py-4">Belum ada siswa pada kelas ini.</td>
                            </tr>
                        @else
                            @foreach($students as $index => $student)
                                <tr>
                                    <td>{{ $index + 1 }}</td>
                                    <td>{{ $student->nama }}</td>
                                    @foreach($assessmentColumns as $column)
                                        @php($score = $student->nilai_per_asesmen[$column->key] ?? null)
                                        <td class="text-center">
                                            {{ $score !== null ? rtrim(rtrim(number_format($score, 2, '.', ''), '0'), '.') : '-' }}
                                        </td>
                                    @endforeach
                                    <td class="text-center fw-semibold">{{ $student->jumlah_nilai !== null ? number_format($student->jumlah_nilai, 2) : '-' }}</td>
                                    <td class="text-center fw-semibold">{{ $student->rata_rata !== null ? number_format($student->rata_rata, 2) : '-' }}</td>
                                </tr>
                            @endforeach
                        @endif
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection