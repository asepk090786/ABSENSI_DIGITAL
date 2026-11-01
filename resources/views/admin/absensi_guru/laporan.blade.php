@extends('layouts.app')

@section('title', 'Laporan Absensi Guru')

@section('content')
<style>
    .report-attendance {
        color: #1f2937;
    }

    .report-attendance .attendance-header {
        margin-bottom: 1.25rem;
        text-align: center;
    }

    .report-attendance .attendance-table-wrap {
        overflow-x: auto;
        width: 100%;
    }

    .report-attendance .attendance-table {
        border-collapse: collapse;
        margin-bottom: 0;
        width: 100%;
    }

    .report-attendance .attendance-table th,
    .report-attendance .attendance-table td {
        border: 1px solid #cbd5e1;
        padding: .55rem .65rem;
        vertical-align: middle;
    }

    .report-attendance .attendance-table th {
        background: #f1f5f9;
        color: #1f2937;
        font-weight: 700;
        text-align: center;
        white-space: normal;
        word-break: normal;
    }

    .report-attendance .attendance-summary td {
        text-align: center;
    }

    .report-attendance .attendance-details td:first-child {
        font-weight: 600;
        white-space: nowrap;
    }

    .report-attendance .attendance-details td:last-child {
        overflow-wrap: break-word;
    }

    .report-attendance .report-teacher-identity {
        align-items: center;
        display: flex;
        gap: 1rem;
        justify-content: center;
        margin-top: .75rem;
    }

    .report-attendance .report-teacher-photo,
    .report-attendance .report-teacher-photo-placeholder {
        border: 1px solid #cbd5e1;
        height: 96px;
        width: 76px;
    }

    .report-attendance .report-teacher-photo {
        object-fit: cover;
    }

    .report-attendance .report-teacher-photo-placeholder {
        align-items: center;
        display: flex;
        font-size: .75rem;
        justify-content: center;
        text-align: center;
    }

    .report-attendance .report-teacher-details {
        line-height: 1.7;
        text-align: left;
    }

    @media print {
        @page {
            size: A4 landscape;
            margin: 8mm;
        }

        body * {
            visibility: hidden !important;
        }

        #attendance-report-print,
        #attendance-report-print * {
            visibility: visible !important;
        }

        #attendance-report-print {
            position: absolute;
            inset: 0;
            width: 100% !important;
            margin: 0 !important;
            break-inside: avoid;
            page-break-inside: avoid;
        }

        .report-attendance,
        .report-attendance * {
            color: #000 !important;
        }

        .report-attendance.card {
            border: 0;
            box-shadow: none;
        }

        .report-attendance .card-body {
            padding: 0;
        }

        .report-attendance .attendance-header {
            margin-bottom: 5mm;
        }

        .report-attendance .attendance-header h2 {
            font-size: 15pt;
            margin-bottom: 2mm;
        }

        .report-attendance .attendance-table-wrap {
            overflow: visible !important;
            width: 100%;
        }

        .report-attendance .attendance-table {
            border-collapse: collapse;
            font-size: 9pt;
            line-height: 1.25;
            table-layout: auto;
            width: 100%;
        }

        .report-attendance .attendance-summary {
            margin-bottom: 5mm;
        }

        .report-attendance .attendance-table th,
        .report-attendance .attendance-table td {
            background: #fff !important;
            border: 1px solid #64748b !important;
            padding: 5px 7px !important;
            vertical-align: middle;
        }

        .report-attendance .attendance-table th {
            background: #f1f5f9 !important;
            font-size: 8pt;
            font-weight: 700;
            text-align: center;
            white-space: normal;
            word-break: normal;
        }

        .report-attendance .attendance-summary td {
            text-align: center;
        }

        .report-attendance .attendance-details td:last-child {
            overflow-wrap: break-word;
        }

        .report-attendance .report-teacher-identity {
            gap: 4mm;
            margin-top: 3mm;
        }

        .report-attendance .report-teacher-photo,
        .report-attendance .report-teacher-photo-placeholder {
            height: 24mm;
            width: 19mm;
        }

        .report-attendance .report-teacher-details {
            font-size: 10pt;
            line-height: 1.5;
        }

        .report-attendance thead {
            display: table-header-group;
        }

        .report-attendance tr {
            break-inside: avoid;
            page-break-inside: avoid;
        }

        .report-attendance .small {
            font-size: 8pt;
            margin-top: 3mm !important;
        }
    }
</style>
<div class="container-fluid">
    <div class="page-header d-print-none mb-4">
        <div class="row align-items-center">
            <div class="col">
                <h2 class="page-title"><i class="ti ti-report me-2"></i>Laporan Absensi Guru</h2>
                <div class="text-muted">Rekap dan analisa absensi guru</div>
            </div>
            <div class="col-auto">
                <a href="{{ $isPersonalReport ? route('home') : route($routePrefix . '.index') }}" class="btn btn-outline-primary">
                    <i class="ti ti-arrow-left me-1"></i>Kembali
                </a>
            </div>
        </div>
    </div>

    <div class="card mb-4">
        <div class="card-header">
            <h3 class="card-title">Filter Laporan</h3>
        </div>
        <div class="card-body">
            <form method="GET" action="{{ $isPersonalReport ? route('guru.absensi_guru.laporan') : route($routePrefix . '.laporan') }}" class="row g-3">
                <div class="{{ $isPersonalReport ? 'col-md-6' : 'col-md-4' }}">
                    <label class="form-label">Tanggal Mulai</label>
                    <input type="date" class="form-control" name="start_date" value="{{ $startDate }}">
                </div>
                <div class="{{ $isPersonalReport ? 'col-md-6' : 'col-md-4' }}">
                    <label class="form-label">Tanggal Akhir</label>
                    <input type="date" class="form-control" name="end_date" value="{{ $endDate }}">
                </div>
                @unless($isPersonalReport)
                <div class="col-md-4">
                    <label class="form-label">Guru</label>
                    <select class="form-select" name="guru_id">
                        <option value="">-- Semua Guru --</option>
                        @foreach($guru as $g)
                        <option value="{{ $g->id }}" @selected($g->id == $guruId)>
                            {{ $g->nama }} ({{ $g->nip ?? '-' }})
                        </option>
                        @endforeach
                    </select>
                </div>
                @endunless
                <div class="col-12">
                    <button type="submit" class="btn btn-primary">
                        <i class="ti ti-search me-1"></i>Filter
                    </button>
                    <a href="{{ $isPersonalReport ? route('guru.absensi_guru.laporan') : route($routePrefix . '.laporan') }}" class="btn btn-outline-secondary">
                        <i class="ti ti-reset me-1"></i>Reset
                    </a>
                    <button type="button" class="btn btn-outline-info float-end" onclick="window.print()">
                        <i class="ti ti-printer me-1"></i>Cetak
                    </button>
                </div>
            </form>
        </div>
    </div>

    @if($rekapAbsensi->isNotEmpty())
    <div class="card report-attendance" id="attendance-report-print">
        <div class="card-body">
            <div class="attendance-header">
                <h2 class="mb-1">REKAP KEHADIRAN GURU</h2>
                @if($startDate || $endDate)
                <div>
                    Periode:
                    {{ $startDate ? \Carbon\Carbon::parse($startDate)->format('d M Y') : 'Tanpa batas awal' }}
                    -
                    {{ $endDate ? \Carbon\Carbon::parse($endDate)->format('d M Y') : 'Tanpa batas akhir' }}
                </div>
                @else
                    <div>Periode: Semua tanggal</div>
                @endif
                @if($guruId)
                    @php
                        $guruTerpilih = $rekapAbsensi->first()->guru;
                        $fotoGuru = $guruTerpilih->foto ?: $guruTerpilih->user?->foto;
                        $fotoGuruUrl = $fotoGuru
                            ? (str_contains($fotoGuru, '://')
                                ? $fotoGuru
                                : asset('storage/' . ltrim(preg_replace('#^(storage/|/)+#', '', $fotoGuru), '/')))
                            : null;
                    @endphp
                    <div class="report-teacher-identity">
                        @if($fotoGuruUrl)
                            <img class="report-teacher-photo" src="{{ $fotoGuruUrl }}" alt="Foto {{ $guruTerpilih->nama }}">
                        @else
                            <div class="report-teacher-photo-placeholder">Foto tidak tersedia</div>
                        @endif
                        <div class="report-teacher-details">
                            <div><strong>Nama:</strong> {{ $guruTerpilih->nama }}</div>
                            <div><strong>NIP:</strong> {{ $guruTerpilih->nip ?: '-' }}</div>
                            <div><strong>Golongan:</strong> {{ $guruTerpilih->pangkat_golongan ?: '-' }}</div>
                        </div>
                    </div>
                @endif
            </div>
            @if($guruId)
                @php $item = $rekapAbsensi->first(); @endphp
                <div class="table-responsive attendance-table-wrap">
                    <table class="table attendance-table attendance-summary">
                        <thead>
                            <tr class="text-center">
                                <th rowspan="2">No</th>
                                <th colspan="5">Rekap Kehadiran</th>
                                <th rowspan="2">Persentase Kehadiran</th>
                            </tr>
                            <tr class="text-center">
                                <th>Hadir</th>
                                <th>Sakit</th>
                                <th>Izin</th>
                                <th>Tanpa Keterangan</th>
                                <th>Dinas Luar</th>
                            </tr>
                        </thead>
                        <tbody>
                        <tr>
                            <td>1</td>
                            <td>{{ $item->hadir }}</td>
                            <td>{{ $item->sakit }}</td>
                            <td>{{ $item->izin }}</td>
                            <td>{{ $item->tanpa }}</td>
                            <td>{{ $item->dinas_luar }}</td>
                            <td class="text-center">{{ number_format($item->persentase, 1, ',', '.') }}%</td>
                        </tr>
                        </tbody>
                    </table>
                </div>

                <section class="attendance-details mt-3">
                    <h3 class="h4 mb-2">Rincian Ketidakhadiran</h3>
                    <div class="table-responsive attendance-table-wrap">
                        <table class="table attendance-table">
                            <thead>
                                <tr>
                                    <th>Status</th>
                                    <th>Tanggal</th>
                                    <th>Keterangan</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($item->rincian_ketidakhadiran as $rincian)
                                    <tr>
                                        <td>{{ $rincian->status }}</td>
                                        <td>{{ $rincian->tanggal }}</td>
                                        <td>
                                            {{ $rincian->keterangan }}
                                            @if($rincian->bukti_url)
                                                <div><a href="{{ $rincian->bukti_url }}" target="_blank" rel="noopener">Buka bukti surat</a></div>
                                                <small>{{ $rincian->bukti_url }}</small>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="3" class="text-center">Tidak ada catatan ketidakhadiran pada periode ini.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </section>
            @else
                <div class="table-responsive attendance-table-wrap">
                    <table class="table attendance-table">
                        <thead>
                            <tr class="text-center">
                                <th rowspan="2">No</th>
                                <th rowspan="2">Nama</th>
                                <th rowspan="2">NIP</th>
                                <th rowspan="2">Golongan</th>
                                <th colspan="10">Rekap Kehadiran</th>
                                <th rowspan="2">Persentase Kehadiran</th>
                            </tr>
                            <tr class="text-center">
                                <th>Hadir</th>
                                <th>Sakit</th>
                                <th>Izin</th>
                                <th>Tanpa Keterangan</th>
                                <th>Dinas Luar</th>
                                <th>Keterangan Hadir</th>
                                <th>Keterangan Sakit</th>
                                <th>Keterangan Izin</th>
                                <th>Keterangan Tanpa</th>
                                <th>Keterangan Dinas Luar</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($rekapAbsensi as $index => $item)
                                <tr>
                                    <td>{{ $index + 1 }}</td>
                                    <td>{{ $item->guru->nama }}</td>
                                    <td>{{ $item->guru->nip ?? '-' }}</td>
                                    <td>{{ $item->guru->pangkat_golongan ?? '-' }}</td>
                                    <td>{{ $item->hadir }}</td>
                                    <td>{{ $item->sakit }}</td>
                                    <td>{{ $item->izin }}</td>
                                    <td>{{ $item->tanpa }}</td>
                                    <td>{{ $item->dinas_luar }}</td>
                                    <td>{{ $item->keterangan_hadir }} @foreach($item->bukti_hadir as $url)<div><a href="{{ $url }}" target="_blank" rel="noopener">Bukti</a></div>@endforeach</td>
                                    <td>{{ $item->keterangan_sakit }} @foreach($item->bukti_sakit as $url)<div><a href="{{ $url }}" target="_blank" rel="noopener">Bukti</a></div>@endforeach</td>
                                    <td>{{ $item->keterangan_izin }} @foreach($item->bukti_izin as $url)<div><a href="{{ $url }}" target="_blank" rel="noopener">Bukti</a></div>@endforeach</td>
                                    <td>{{ $item->keterangan_tanpa }} @foreach($item->bukti_tanpa as $url)<div><a href="{{ $url }}" target="_blank" rel="noopener">Bukti</a></div>@endforeach</td>
                                    <td>{{ $item->keterangan_dinas_luar }} @foreach($item->bukti_dinas_luar as $url)<div><a href="{{ $url }}" target="_blank" rel="noopener">Bukti</a></div>@endforeach</td>
                                    <td>{{ number_format($item->persentase, 1, ',', '.') }}%</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
            <div class="mt-2 small">
                Tanpa = status tidak hadir. Persentase kehadiran = jumlah Hadir / seluruh catatan absensi pada periode yang dipilih.
            </div>
        </div>
    </div>
    @else
    <div class="card text-center" id="attendance-report-print">
        <div class="card-body py-5">
            <i class="ti ti-inbox fs-1 d-block mb-2 text-muted"></i>
            <p class="text-muted">Tidak ada guru aktif yang sesuai dengan filter.</p>
        </div>
    </div>
    @endif
</div>
@endsection
