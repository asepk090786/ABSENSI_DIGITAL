@extends('layouts.app')

@section('title', 'Rekap Absensi Kelas')

@section('content')
<style>
    .rekap-kelas-table {
        min-width: max-content;
        border-color: #1f2937;
    }
    .rekap-kelas-table th,
    .rekap-kelas-table td {
        border-color: #1f2937;
        vertical-align: middle;
        white-space: nowrap;
        padding: .3rem .45rem;
        text-align: center;
        font-size: .8rem;
    }
    .rekap-kelas-table .kelas-cell {
        min-width: 4rem;
        text-align: left;
    }
    .rekap-kelas-table .status-h { background: #70ad47; color: #102a13; }
    .rekap-kelas-table .status-a { background: #ff0000; color: #fff; }
    .rekap-kelas-table .status-i { background: #00b0f0; color: #062b3a; }
    .rekap-kelas-table .status-s { background: #ffff00; color: #332f00; }
    .rekap-kelas-table .jam-header { background: #f8fafc; font-weight: 600; }
    .rekap-kelas-table .jumlah-header { background: #fff; }
    .rekap-kelas-table .jam-start { border-left: 2px solid #64748b !important; }
    .rekap-kelas-table .jam-end { border-right: 2px solid #64748b !important; }
    .rekap-kelas-table .jam-title { background: #e8eef5; color: #334155; }
</style>

<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
        <div>
            <h3 class="mb-1">Rekap Absensi Kelas {{ $kelas->nama_kelas }}</h3>
            <p class="text-muted mb-0">{{ $tanggal->translatedFormat('l, d F Y') }} - {{ $hari }}</p>
        </div>
        <a href="{{ route('home') }}" class="btn btn-outline-secondary">
            <i class="ti ti-arrow-left me-1"></i>Kembali
        </a>
    </div>

    <div class="card mb-3">
        <div class="card-body">
            <form method="GET" action="{{ route('absensi.rekap-kelas') }}" class="row g-2 align-items-end">
                <input type="hidden" name="kelas_id" value="{{ $kelas->id }}">
                <div class="col-auto">
                    <label for="tanggal-rekap" class="form-label">Tanggal</label>
                    <input id="tanggal-rekap" type="date" name="tanggal" value="{{ $tanggal->format('Y-m-d') }}" class="form-control">
                </div>
                <div class="col-auto">
                    <button type="submit" class="btn btn-primary"><i class="ti ti-filter me-1"></i>Tampilkan</button>
                </div>
            </form>
        </div>
    </div>

    @if($jamBelajar->isEmpty())
        <div class="alert alert-info">Tidak ada jadwal KBM untuk kelas ini pada {{ $hari }}, {{ $tanggal->format('d/m/Y') }}.</div>
    @else
        <div class="card">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-bordered mb-0 rekap-kelas-table">
                        <thead>
                            <tr>
                                <th rowspan="3" class="kelas-cell">No</th>
                                <th rowspan="3" class="kelas-cell">Siswa</th>
                                <th colspan="{{ $jamBelajar->count() * 4 }}">jam ke</th>
                                <th colspan="4" class="jumlah-header">JUMLAH</th>
                                <th rowspan="3">%</th>
                            </tr>
                            <tr>
                                @foreach($jamBelajar as $jam)
                                    <th colspan="4" class="jam-header jam-title jam-start jam-end" title="{{ $jam->jam_mulai }} - {{ $jam->jam_selesai }}">{{ $jam->urutan }}</th>
                                @endforeach
                                <th colspan="4" class="jumlah-header"></th>
                            </tr>
                            <tr>
                                @foreach($jamBelajar as $jam)
                                    <th class="status-h jam-start">H</th>
                                    <th class="status-a">A</th>
                                    <th class="status-i">I</th>
                                    <th class="status-s jam-end">S</th>
                                @endforeach
                                <th class="status-h">H</th>
                                <th class="status-a">A</th>
                                <th class="status-i">I</th>
                                <th class="status-s">S</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($rows as $index => $row)
                                <tr>
                                    <td>{{ $index + 1 }}</td>
                                    <td class="kelas-cell fw-semibold">{{ $row->nama }}<small class="d-block text-muted fw-normal">NIS: {{ $row->nis ?: '-' }}</small></td>
                                    @foreach($jamBelajar as $jam)
                                        <td class="jam-start">{{ $row->counts[$jam->id]['H'] ?? 0 }}</td>
                                        <td>{{ $row->counts[$jam->id]['A'] ?? 0 }}</td>
                                        <td>{{ $row->counts[$jam->id]['I'] ?? 0 }}</td>
                                        <td class="jam-end">{{ $row->counts[$jam->id]['S'] ?? 0 }}</td>
                                    @endforeach
                                    <td class="fw-semibold">{{ $row->jumlah['H'] }}</td>
                                    <td class="fw-semibold">{{ $row->jumlah['A'] }}</td>
                                    <td class="fw-semibold">{{ $row->jumlah['I'] }}</td>
                                    <td class="fw-semibold">{{ $row->jumlah['S'] }}</td>
                                    <td class="fw-semibold">{{ $row->persentase }}%</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="card-footer text-muted small">
                Total siswa aktif: {{ $totalSiswaAktif }}. H mencakup hadir dan terlambat.
            </div>
        </div>
    @endif
</div>
@endsection