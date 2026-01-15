@extends('layouts.app', ['pageSlug' => 'jadwal-kbm'])

@section('title','Jadwal Keseluruhan')

@section('content')
<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header">
                <div class="row align-items-center">
                    <div class="col">
                        <h4 class="card-title mb-0">Jadwal Keseluruhan (KBM)</h4>
                        <p class="text-muted mb-0 mt-1">
                            <small>
                                @if($tahunAjaranAktif && $semesterAktif)
                                <i class="ti ti-calendar me-1"></i>{{ $tahunAjaranAktif->nama }} - {{ $semesterAktif->nama }}
                                @endif
                            </small>
                        </p>
                    </div>
                    <div class="col-auto">
                        <a href="{{ route('jadwal-kbm.index') }}" class="btn btn-secondary btn-sm">
                            <i class="ti ti-arrow-left me-1"></i>Kembali
                        </a>
                        <div class="btn-group me-2" role="group">
                            <a href="{{ route('jadwal-kbm.keseluruhan', ['view' => 'full']) }}" 
                               class="btn btn-sm {{ $viewType === 'full' ? 'btn-primary' : 'btn-outline-primary' }}">
                                <i class="ti ti-list me-1"></i>Lengkap
                            </a>
                            <a href="{{ route('jadwal-kbm.keseluruhan', ['view' => 'compact']) }}" 
                               class="btn btn-sm {{ $viewType === 'compact' ? 'btn-primary' : 'btn-outline-primary' }}">
                                <i class="ti ti-book me-1"></i>Jadwal Mapel
                            </a>
                            <a href="{{ route('jadwal-kbm.keseluruhan', ['view' => 'kode']) }}" 
                               class="btn btn-sm {{ $viewType === 'kode' ? 'btn-primary' : 'btn-outline-primary' }}">
                                <i class="ti ti-id me-1"></i>Jadwal Kode
                            </a>
                        </div>
                        @if(in_array($viewType, ['compact', 'kode']))
                        <select id="paperSize" class="form-select form-select-sm d-inline-block w-auto me-2" style="width: 100px !important;">
                            <option value="a4">A4</option>
                            <option value="f4">F4</option>
                            <option value="folio">Folio</option>
                        </select>
                        @endif
                        @if($viewType === 'compact')
                        <a href="{{ route('jadwal-kbm.export-pdf-keseluruhan-mapel', ['paper_size' => 'a4']) }}" class="btn btn-danger btn-sm me-2">
                            <i class="ti ti-file-pdf me-1"></i>Download PDF Mapel (A4)
                        </a>
                        <a href="{{ route('jadwal-kbm.export-pdf-keseluruhan-mapel', ['paper_size' => 'f4']) }}" class="btn btn-danger btn-sm me-2">
                            <i class="ti ti-file-pdf me-1"></i>Download PDF Mapel (F4)
                        </a>
                        <a href="{{ route('jadwal-kbm.export-pdf-keseluruhan-mapel', ['paper_size' => 'folio']) }}" class="btn btn-danger btn-sm me-2">
                            <i class="ti ti-file-pdf me-1"></i>Download PDF Mapel (Folio)
                        </a>
                        @endif
                        @if($viewType === 'kode')
                        <a href="{{ route('jadwal-kbm.export-pdf-keseluruhan', ['paper_size' => 'a4']) }}" class="btn btn-danger btn-sm me-2">
                            <i class="ti ti-file-pdf me-1"></i>Download PDF Kode (A4)
                        </a>
                        <a href="{{ route('jadwal-kbm.export-pdf-keseluruhan', ['paper_size' => 'f4']) }}" class="btn btn-danger btn-sm me-2">
                            <i class="ti ti-file-pdf me-1"></i>Download PDF Kode (F4)
                        </a>
                        <a href="{{ route('jadwal-kbm.export-pdf-keseluruhan', ['paper_size' => 'folio']) }}" class="btn btn-danger btn-sm me-2">
                            <i class="ti ti-file-pdf me-1"></i>Download PDF Kode (Folio)
                        </a>
                        @endif
                        <button onclick="window.print()" class="btn btn-info btn-sm">
                            <i class="ti ti-printer me-1"></i>Cetak
                        </button>
                    </div>
                </div>
            </div>
            <div class="card-body">
                @if($jadwalKeseluruhan->isEmpty())
                    <div class="alert alert-info">
                        <i class="ti ti-info-circle me-2"></i>
                        Belum ada jadwal KBM.
                    </div>
                @else
                    @if($viewType === 'full')
                        <!-- TAMPILAN LENGKAP -->
                        @foreach($hariList as $hari)
                        @php
                            $jadwalHari = $jadwalKeseluruhan->get($hari, collect());
                            $jamBelajarHari = $jamBelajarByHari->get($hari, collect());
                            
                            // Get max jam dari jam_belajar entries untuk hari ini
                            $maxJam = $jamBelajarHari->max('urutan') ?? $jadwalHari->max('jam_ke') ?? 0;
                            
                            // Collect all KBM kelas untuk hari ini
                            $kelasJadwal = $jadwalHari->groupBy('kelas_id')->map(function($items) {
                                return $items->first()->kelas;
                            })->unique('id')->sortBy(function($kelas) {
                                // Parse kelas name untuk sorting: 12.C4 -> [12, C, 4]
                                preg_match('/(\d+)\.([A-Z]+)(\d*)/', $kelas->nama_kelas, $matches);
                                $tingkat = intval($matches[1] ?? 0);
                                $jurusan = $matches[2] ?? '';
                                $nomor = intval($matches[3] ?? 0);
                                return sprintf("%02d%s%02d", $tingkat, $jurusan, $nomor);
                            });
                        @endphp
                        
                        @if($jadwalHari->isNotEmpty() || $jamBelajarHari->isNotEmpty())
                        <div class="mb-5">
                            <h5 class="border-bottom pb-2 mb-3">
                                <i class="ti ti-calendar me-2"></i>{{ $hari }}
                            </h5>
                            <div class="table-responsive">
                                <table class="table table-bordered table-sm time-hide">
                                    <thead class="table-primary">
                                        <tr>
                                            <th class="text-center">Jam Ke</th>
                                            <th class="text-center">Waktu</th>
                                            @foreach($kelasJadwal as $kelas)
                                            <th class="text-center">{{ $kelas->nama_kelas }}</th>
                                            @endforeach
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @for($jam = 1; $jam <= $maxJam; $jam++)
                                        <tr>
                                            <td class="text-center fw-bold">{{ $jam }}</td>
                                            <td class="text-center time-cell">
                                                @php
                                                    // Cari jam belajar entry untuk urutan ini
                                                    $jamBelajarEntry = $jamBelajarHari->where('urutan', $jam)->first();
                                                    if ($jamBelajarEntry) {
                                                            // Tampilkan hanya waktu; nama kegiatan tidak ditampilkan di kolom waktu
                                                            echo $jamBelajarEntry->jam_mulai . ' - ' . $jamBelajarEntry->jam_selesai;
                                                    } else {
                                                        // Fallback ke jadwal KBM
                                                        $jadwalFirst = $jadwalHari->where('jam_ke', $jam)->first();
                                                        if ($jadwalFirst) {
                                                            echo $jadwalFirst->jamBelajar->jam_mulai . ' - ' . $jadwalFirst->jamBelajar->jam_selesai;
                                                        }
                                                    }
                                                @endphp
                                            </td>
                                            @foreach($kelasJadwal as $kelas)
                                            <td style="font-size: 11px; vertical-align: middle;">
                                                @php
                                                    // Check if this is a non-KBM jam (UPACARA, ISTIRAHAT, PEMBIASAAN)
                                                    $jamBelajarEntry = $jamBelajarHari->where('urutan', $jam)->first();
                                                    if ($jamBelajarEntry && $jamBelajarEntry->jenis && $jamBelajarEntry->jenis !== 'KBM') {
                                                        // Non-KBM: tampilkan kode kegiatan saja (ambil dari master kegiatan jika ada, jika tidak pakai jenis)
                                                        $kodeKegiatan = null;
                                                        if (isset($kegiatanList)) {
                                                            $kegiatan = collect($kegiatanList)->firstWhere('nama', strtoupper($jamBelajarEntry->jenis));
                                                            if ($kegiatan && isset($kegiatan['kode'])) {
                                                                $kodeKegiatan = $kegiatan['kode'];
                                                            }
                                                        }
                                                        echo '<div class="badge bg-warning text-dark" style="font-size: 10px;">' . ($kodeKegiatan ? $kodeKegiatan : strtoupper($jamBelajarEntry->jenis)) . '</div>';
                                                    } else {
                                                        // KBM: check specific kelas schedule - hanya tampilkan kode guru
                                                        $jadwalJam = $jadwalHari->where('jam_ke', $jam)->where('kelas_id', $kelas->id)->first();
                                                        if ($jadwalJam && $jadwalJam->guru) {
                                                            echo $jadwalJam->guru->kode_guru;
                                                        } else {
                                                            echo '-';
                                                        }
                                                    }
                                                @endphp
                                            </td>
                                            @endforeach
                                        </tr>
                                        @endfor
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        @endif
                    @endforeach
                    @elseif($viewType === 'compact')
                        <!-- TAMPILAN KOMPAK (HANYA KODE MAPEL) -->
                        <div class="compact-grid">
                        @foreach($hariList as $hari)
                        @php
                            $jadwalHari = $jadwalKeseluruhan->get($hari, collect());
                            $jamBelajarHari = $jamBelajarByHari->get($hari, collect());
                            
                            // Get max jam dari jam_belajar entries untuk hari ini
                            $maxJam = $jamBelajarHari->max('urutan') ?? $jadwalHari->max('jam_ke') ?? 0;
                            
                            // Collect all KBM kelas untuk hari ini
                            $kelasJadwal = $jadwalHari->groupBy('kelas_id')->map(function($items) {
                                return $items->first()->kelas;
                            })->unique('id')->sortBy(function($kelas) {
                                preg_match('/(\d+)\.([A-Z]+)(\d*)/', $kelas->nama_kelas, $matches);
                                $tingkat = intval($matches[1] ?? 0);
                                $jurusan = $matches[2] ?? '';
                                $nomor = intval($matches[3] ?? 0);
                                return sprintf("%02d%s%02d", $tingkat, $jurusan, $nomor);
                            });
                        @endphp
                        
                        @if($jadwalHari->isNotEmpty() || $jamBelajarHari->isNotEmpty())
                        <div class="mb-5 compact-card">
                            <h5 class="border-bottom pb-2 mb-3">
                                <i class="ti ti-calendar me-2"></i>{{ $hari }}
                            </h5>
                            <div class="table-responsive">
                                <table class="table table-bordered table-sm compact-view time-hide">
                                    <thead class="table-primary">
                                        <tr>
                                            <th class="text-center" style="width: 5%;">Jam</th>
                                            <th class="text-center" style="width: 15%;">Waktu</th>
                                            @foreach($kelasJadwal as $kelas)
                                            <th class="text-center" style="width: auto; min-width: 45px;">{{ $kelas->nama_kelas }}</th>
                                            @endforeach
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @for($jam = 1; $jam <= $maxJam; $jam++)
                                        <tr>
                                            <td class="text-center fw-bold">{{ $jam }}</td>
                                            <td class="text-center" style="font-size: 10px;">
                                                @php
                                                    // Cari jam belajar entry untuk urutan ini
                                                    $jamBelajarEntry = $jamBelajarHari->where('urutan', $jam)->first();
                                                    if ($jamBelajarEntry) {
                                                        // Hanya tampilkan waktu, tidak badge kegiatan
                                                        echo $jamBelajarEntry->jam_mulai . '-' . substr($jamBelajarEntry->jam_selesai, 0, 5);
                                                    } else {
                                                        $jadwalFirst = $jadwalHari->where('jam_ke', $jam)->first();
                                                        if ($jadwalFirst) {
                                                            echo $jadwalFirst->jamBelajar->jam_mulai . '-' . substr($jadwalFirst->jamBelajar->jam_selesai, 0, 5);
                                                        }
                                                    }
                                                @endphp
                                            </td>
                                            @foreach($kelasJadwal as $kelas)
                                            <td style="font-size: 13px; text-align: center; vertical-align: middle; padding: 4px 2px !important; font-weight: bold;">
                                                @php
                                                    // Check if this is a non-KBM jam (UPACARA, ISTIRAHAT, PEMBIASAAN)
                                                    $jamBelajarEntry = $jamBelajarHari->where('urutan', $jam)->first();
                                                    if ($jamBelajarEntry && $jamBelajarEntry->jenis && $jamBelajarEntry->jenis !== 'KBM') {
                                                        // Non-KBM: apply to all kelas
                                                        echo '<div class="badge bg-warning text-dark" style="font-size: 10px;">' . strtoupper($jamBelajarEntry->jenis) . '</div>';
                                                    } else {
                                                        // KBM: check specific kelas schedule
                                                        $jadwalJam = $jadwalHari->where('jam_ke', $jam)->where('kelas_id', $kelas->id)->first();
                                                        if ($jadwalJam) {
                                                            echo $jadwalJam->mataPelajaran->kode_mapel;
                                                        } else {
                                                            echo '-';
                                                        }
                                                    }
                                                @endphp
                                            </td>
                                            @endforeach
                                        </tr>
                                        @endfor
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        @endif
                        @endforeach
                        </div>
                    @elseif($viewType === 'kode')
                        <!-- TAMPILAN JADWAL KODE GURU -->
                        @foreach($hariList as $hari)
                        @php
                            $jadwalHari = $jadwalKeseluruhan->get($hari, collect());
                            $jamBelajarHari = $jamBelajarByHari->get($hari, collect());
                            
                            // Get max jam dari jam_belajar entries untuk hari ini
                            $maxJam = $jamBelajarHari->max('urutan') ?? $jadwalHari->max('jam_ke') ?? 0;
                            
                            // Collect all KBM kelas untuk hari ini
                            $kelasJadwal = $jadwalHari->groupBy('kelas_id')->map(function($items) {
                                return $items->first()->kelas;
                            })->unique('id')->sortBy(function($kelas) {
                                preg_match('/(\d+)\.([A-Z]+)(\d*)/', $kelas->nama_kelas, $matches);
                                $tingkat = intval($matches[1] ?? 0);
                                $jurusan = $matches[2] ?? '';
                                $nomor = intval($matches[3] ?? 0);
                                return sprintf("%02d%s%02d", $tingkat, $jurusan, $nomor);
                            });
                        @endphp
                        
                        @if($jadwalHari->isNotEmpty() || $jamBelajarHari->isNotEmpty())
                        <div class="mb-5">
                            <h5 class="border-bottom pb-2 mb-3">
                                <i class="ti ti-calendar me-2"></i>{{ $hari }}
                            </h5>
                            <div class="table-responsive">
                                <table class="table table-bordered table-sm compact-view time-hide">
                                    <thead class="table-primary">
                                        <tr>
                                            <th class="text-center" style="width: 5%;">Jam</th>
                                            <th class="text-center" style="width: 15%;">Waktu</th>
                                            @foreach($kelasJadwal as $kelas)
                                            <th class="text-center" style="width: auto; min-width: 45px;">{{ $kelas->nama_kelas }}</th>
                                            @endforeach
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @for($jam = 1; $jam <= $maxJam; $jam++)
                                        <tr>
                                            <td class="text-center fw-bold">{{ $jam }}</td>
                                            <td class="text-center" style="font-size: 10px;">
                                                @php
                                                    // Cari jam belajar entry untuk urutan ini
                                                    $jamBelajarEntry = $jamBelajarHari->where('urutan', $jam)->first();
                                                    if ($jamBelajarEntry) {
                                                        // Hanya tampilkan waktu, tidak badge kegiatan
                                                        echo $jamBelajarEntry->jam_mulai . '-' . substr($jamBelajarEntry->jam_selesai, 0, 5);
                                                    } else {
                                                        $jadwalFirst = $jadwalHari->where('jam_ke', $jam)->first();
                                                        if ($jadwalFirst) {
                                                            echo $jadwalFirst->jamBelajar->jam_mulai . '-' . substr($jadwalFirst->jamBelajar->jam_selesai, 0, 5);
                                                        }
                                                    }
                                                @endphp
                                            </td>
                                            @foreach($kelasJadwal as $kelas)
                                            <td style="font-size: 13px; text-align: center; vertical-align: middle; padding: 4px 2px !important; font-weight: bold;">
                                                @php
                                                    // Check if this is a non-KBM jam (UPACARA, ISTIRAHAT, PEMBIASAAN)
                                                    $jamBelajarEntry = $jamBelajarHari->where('urutan', $jam)->first();
                                                    if ($jamBelajarEntry && $jamBelajarEntry->jenis && $jamBelajarEntry->jenis !== 'KBM') {
                                                        // Non-KBM: tampilkan kode kegiatan dari master (case-insensitive)
                                                        $kodeKegiatan = null;
                                                        if (isset($kegiatanList)) {
                                                            foreach ($kegiatanList as $kegiatan) {
                                                                if (strtoupper(trim($kegiatan['nama'])) === strtoupper(trim($jamBelajarEntry->jenis))) {
                                                                    $kodeKegiatan = $kegiatan['kode'];
                                                                    break;
                                                                }
                                                            }
                                                        }
                                                        echo '<div class="badge bg-warning text-dark" style="font-size: 10px;">' . ($kodeKegiatan ? $kodeKegiatan : strtoupper($jamBelajarEntry->jenis)) . '</div>';
                                                    } else {
                                                        // KBM: check specific kelas schedule - hanya tampilkan kode guru
                                                        $jadwalJam = $jadwalHari->where('jam_ke', $jam)->where('kelas_id', $kelas->id)->first();
                                                        if ($jadwalJam && $jadwalJam->guru) {
                                                            echo $jadwalJam->guru->kode_guru;
                                                        } else {
                                                            echo '-';
                                                        }
                                                    }
                                                @endphp
                                            </td>
                                            @endforeach
                                        </tr>
                                        @endfor
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        @endif
                        @endforeach
                    @endif
                @endif
            </div>
        </div>
    </div>
</div>

@endsection

@push('styles')
<style>
    /* Paper size specific styles for Jadwal Kode */
    @media print {
        @page {
            margin: 5mm;
        }
        
        /* A4 Paper (210mm x 297mm) */
        body.paper-a4 {
            width: 200mm;
            height: 287mm;
        }
        
        body.paper-a4 .table {
            font-size: 5px !important;
        }
        
        body.paper-a4 h5 {
            font-size: 6px !important;
        }
        
        /* F4 Paper (215mm x 330mm) */
        body.paper-f4 {
            width: 205mm;
            height: 320mm;
        }
        
        body.paper-f4 .table {
            font-size: 5.5px !important;
        }

        /* Hide activity badges inside the 'Waktu' (second) column for jadwal keseluruhan tables */
        .time-hide tbody td:nth-child(2) .badge,
        .time-hide tbody td:nth-child(2) .badge-sm {
            display: none !important;
        }

        /* Make sure the Waktu column keeps readable spacing */
        .time-hide tbody td:nth-child(2) {
            white-space: nowrap;
            line-height: 1.1;
            vertical-align: middle;
            padding-top: 6px;
            padding-bottom: 6px;
        }

        /* Ensure badges in kelas columns remain visible and nicely spaced */
        .time-hide tbody td:not(:nth-child(2)) .badge,
        .time-hide tbody td:not(:nth-child(2)) .badge-sm {
            display: inline-block !important;
            margin-top: 4px;
            font-size: 10px;
            padding: .25rem .4rem;
        }

        /* Compact view adjustments */
        .compact-view.time-hide td {
            padding: 4px 2px !important;
        }
        .compact-view.time-hide td .badge {
            font-size: 9px !important;
            margin-top: 3px;
        }
        
        body.paper-f4 h5 {
            font-size: 6.5px !important;
        }
        
        /* Folio Paper (215mm x 330mm - same as F4 but different name) */
        body.paper-folio {
            width: 205mm;
            height: 320mm;
        }
        
        body.paper-folio .table {
            font-size: 5.5px !important;
        }
        
        body.paper-folio h5 {
            font-size: 6.5px !important;
        }
    }
    
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
        
        .mb-5 {
            margin-bottom: 1px !important;
            page-break-inside: avoid;
        }
        
        /* Prevent page breaks within tables for one-page printing */
        .table, .table-responsive {
            page-break-inside: auto;
        }
        
        .table tbody tr {
            page-break-inside: avoid;
            page-break-after: auto;
        }
        
        .mb-3 {
            margin-bottom: 0.5px !important;
        }
        
        .mt-1 {
            margin-top: 1px !important;
        }
        
        .table {
            font-size: 5.5px !important;
            margin-bottom: 2px !important;
            border-collapse: collapse;
        }
        
        .table thead {
            background-color: #2563eb !important;
            color: white !important;
        }
        
        .table-primary th {
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
            vertical-align: middle;
            line-height: 1.1;
        }
        
        .table tbody tr {
            height: 10px;
        }
        
        /* Compact view styles - used by Jadwal Kode */
        .compact-view {
            font-size: 7px !important;
            width: 100% !important;
        }
        
        .compact-view td {
            padding: 1px 1px !important;
            height: 8px;
            font-size: 6px !important;
            line-height: 1;
        }
        
        .compact-view th {
            padding: 1px 1px !important;
            font-size: 6px !important;
            line-height: 1;
        }

        /* Compact mapel print: force grid into two columns to fit one page */
        .compact-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 3px;
        }
        .compact-card {
            page-break-inside: avoid;
            border: 0.5px solid #ccc;
            padding: 2px;
            border-radius: 2px;
            background: #fff;
        }
        .compact-card h5 {
            margin: 0 0 2px 0 !important;
        }
        body.paper-a4 .compact-card .table,
        body.paper-f4 .compact-card .table,
        body.paper-folio .compact-card .table {
            font-size: 5px !important;
        }
        body.paper-a4 .compact-card .table td,
        body.paper-a4 .compact-card .table th,
        body.paper-f4 .compact-card .table td,
        body.paper-f4 .compact-card .table th,
        body.paper-folio .compact-card .table td,
        body.paper-folio .compact-card .table th {
            padding: 0.5px 0.8px !important;
            font-size: 4.8px !important;
        }
        
        /* Paper size specific adjustments for compact view */
        body.paper-a4 .compact-view {
            font-size: 5.5px !important;
        }
        
        body.paper-a4 .compact-view td,
        body.paper-a4 .compact-view th {
            font-size: 5px !important;
            padding: 0.5px 1px !important;
        }
        
        body.paper-f4 .compact-view,
        body.paper-folio .compact-view {
            font-size: 6.5px !important;
        }
        
        body.paper-f4 .compact-view td,
        body.paper-f4 .compact-view th,
        body.paper-folio .compact-view td,
        body.paper-folio .compact-view th {
            font-size: 6px !important;
            padding: 0.8px 1px !important;
        }
        
        .table tbody tr:nth-child(odd) {
            background-color: #f9fafb !important;
        }
        
        .table tbody tr:nth-child(even) {
            background-color: #fff !important;
        }
        
        .table-responsive {
            margin-bottom: 2px !important;
        }
        
        .small {
            font-size: 4.5px !important;
        }
        
        .mb-1 {
            margin-bottom: 1px !important;
        }
        
        .text-muted {
            color: #666 !important;
        }
        
        .text-center {
            text-align: center !important;
        }
        
        .fw-bold {
            font-weight: bold !important;
        }
        
        .badge {
            font-size: 4px !important;
            padding: 0.5px 2px !important;
            display: inline-block;
        }
        
        .bg-info {
            background-color: #0dcaf0 !important;
            color: #000 !important;
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
    }
</style>
@endpush

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const paperSizeSelector = document.getElementById('paperSize');
        
        if (paperSizeSelector) {
            // Load saved paper size preference
            const savedPaperSize = localStorage.getItem('jadwal_kode_paper_size') || 'a4';
            paperSizeSelector.value = savedPaperSize;
            applyPaperSize(savedPaperSize);
            
            // Handle paper size change
            paperSizeSelector.addEventListener('change', function() {
                const selectedSize = this.value;
                localStorage.setItem('jadwal_kode_paper_size', selectedSize);
                applyPaperSize(selectedSize);
            });
        }
        
        function applyPaperSize(size) {
            // Remove all paper size classes
            document.body.classList.remove('paper-a4', 'paper-f4', 'paper-folio');
            // Add selected paper size class
            document.body.classList.add('paper-' + size);
        }
    });
</script>
@endpush
