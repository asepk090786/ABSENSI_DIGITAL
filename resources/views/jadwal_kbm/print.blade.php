@extends('layouts.app')

@section('title', 'Print Jadwal Kelas')

@section('content')
<div class="row mb-3 no-print">
    <div class="col-12">
        <button onclick="window.print()" class="btn btn-primary">
            <i class="ti ti-printer me-2"></i>Print Jadwal
        </button>
        <a href="{{ route('jadwal-kbm.export-pdf', $kelas->id) }}" class="btn btn-success" target="_blank">
            <i class="ti ti-download me-2"></i>Download PDF
        </a>
        <a href="{{ route('jadwal-kbm.create-by-kelas', $kelas->id) }}" class="btn btn-secondary">
            <i class="ti ti-arrow-left me-2"></i>Kembali
        </a>
    </div>
</div>

<!-- Print Content -->
<div id="printContent" class="card no-card-style">
    <div class="card-body print-body">
        <!-- Header -->
        @if($sekolah && $sekolah->header_html)
            <!-- Custom Header from Editor -->
            <div class="header-section custom-header-wrapper">
                {!! $sekolah->header_html !!}
            </div>
        @else
            <!-- Default Header -->
            <div class="header-section">
            <div class="header-content">
                <!-- Logo Kiri -->
                <div class="logo-left">
                    @if($sekolah && $sekolah->logo_header_kiri && file_exists(public_path('storage/' . $sekolah->logo_header_kiri)))
                        <img src="{{ asset('storage/' . $sekolah->logo_header_kiri) }}" class="school-logo">
                    @endif
                </div>

                <!-- Nama Sekolah di Tengah -->
                <div class="school-info">
                    <h3 class="school-name">{{ strtoupper($sekolah->nama_sekolah ?? 'Sekolah') }}</h3>
                    <p class="school-address">
                        @if($sekolah && $sekolah->alamat_jalan)
                            {{ $sekolah->alamat_jalan }}
                        @endif
                    </p>
                    <p class="school-contact">
                        @if($sekolah && $sekolah->website)
                            Website : {{ $sekolah->website }}<br>
                        @endif
                        @if($sekolah && $sekolah->email)
                            E-Mail : {{ $sekolah->email }}
                        @endif
                    </p>
                </div>

                <!-- Logo Kanan (Logo Sekolah) -->
                <div class="logo-right">
                    @if($sekolah && $sekolah->logo && file_exists(public_path('storage/' . $sekolah->logo)))
                        <img src="{{ asset('storage/' . $sekolah->logo) }}" class="school-logo-right">
                    @endif
                </div>
            </div>
            <div class="header-divider"></div>
        </div>
        @endif

        <!-- Judul -->
        <div class="title-section">
            <h2 class="main-title">JADWAL PELAJARAN</h2>
            <h3 class="sub-title">KELAS {{ strtoupper($kelas->nama_kelas) }}</h3>
            <p class="period-info">
                {{ $tahunAjaranAktif->nama_tahun ?? '-' }} | {{ $semesterAktif->nama_semester ?? '-' }}
            </p>
        </div>

        @php
            $jadwalByHari = $jadwalSorted->groupBy('hari');
            $hariList = ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat'];
        @endphp

        <!-- Tabel Jadwal Format 2 Kolom -->
        <div class="schedule-container">
            <!-- Baris 1: Senin dan Selasa -->
            <div class="schedule-row">
                @foreach(['Senin', 'Selasa'] as $hari)
                    @php
                        $jadwalHari = $jadwalByHari->get($hari, collect());
                        $jamHari = $jamBelajarByHari->get($hari, collect())->sortBy('urutan');
                    @endphp
                    <div class="schedule-column">
                        @if($jamHari->count() > 0)
                            <table class="schedule-table">
                                <thead>
                                    <tr>
                                        <th colspan="4" class="day-header">{{ strtoupper($hari) }}</th>
                                    </tr>
                                    <tr class="column-header">
                                        <th class="col-waktu">Waktu</th>
                                        <th class="col-jam">Jam</th>
                                        <th class="col-kode">Kode</th>
                                        <th class="col-mapel">Mata Pelajaran</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($jamHari as $jam)
                                        @php
                                            $jadwalJam = $jadwalHari->where('jam_ke', $jam->urutan)->first();
                                            $isKbm = $jam->jenis === 'KBM';
                                            $rowClass = $isKbm ? '' : 'non-kbm-row';
                                        @endphp
                                        <tr class="{{ $rowClass }}">
                                            <td class="col-waktu">
                                                {{ \Carbon\Carbon::parse($jam->jam_mulai)->format('H:i') }} - {{ \Carbon\Carbon::parse($jam->jam_selesai)->format('H:i') }}
                                            </td>
                                            <td class="col-jam">
                                                @if($isKbm)
                                                    {{ $jam->urutan }}
                                                @else
                                                    -
                                                @endif
                                            </td>
                                            <td class="col-kode">
                                                @if($isKbm && $jadwalJam)
                                                    {{ $jadwalJam->guru->nip ?? '-' }}
                                                @else
                                                    -
                                                @endif
                                            </td>
                                            <td class="col-mapel">
                                                @if($isKbm && $jadwalJam)
                                                    {{ $jadwalJam->mataPelajaran->nama_mapel ?? '-' }}
                                                @else
                                                    <em class="non-kbm-text">{{ $jam->jenis }}</em>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        @else
                            <div class="empty-schedule">Tidak ada jadwal</div>
                        @endif
                    </div>
                @endforeach
            </div>

            <!-- Baris 2: Rabu dan Kamis -->
            <div class="schedule-row">
                @foreach(['Rabu', 'Kamis'] as $hari)
                    @php
                        $jadwalHari = $jadwalByHari->get($hari, collect());
                        $jamHari = $jamBelajarByHari->get($hari, collect())->sortBy('urutan');
                    @endphp
                    <div class="schedule-column">
                        @if($jamHari->count() > 0)
                            <table class="schedule-table">
                                <thead>
                                    <tr>
                                        <th colspan="4" class="day-header">{{ strtoupper($hari) }}</th>
                                    </tr>
                                    <tr class="column-header">
                                        <th class="col-waktu">Waktu</th>
                                        <th class="col-jam">Jam</th>
                                        <th class="col-kode">Kode</th>
                                        <th class="col-mapel">Mata Pelajaran</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($jamHari as $jam)
                                        @php
                                            $jadwalJam = $jadwalHari->where('jam_ke', $jam->urutan)->first();
                                            $isKbm = $jam->jenis === 'KBM';
                                            $rowClass = $isKbm ? '' : 'non-kbm-row';
                                        @endphp
                                        <tr class="{{ $rowClass }}">
                                            <td class="col-waktu">
                                                {{ \Carbon\Carbon::parse($jam->jam_mulai)->format('H:i') }} - {{ \Carbon\Carbon::parse($jam->jam_selesai)->format('H:i') }}
                                            </td>
                                            <td class="col-jam">
                                                @if($isKbm)
                                                    {{ $jam->urutan }}
                                                @else
                                                    -
                                                @endif
                                            </td>
                                            <td class="col-kode">
                                                @if($isKbm && $jadwalJam)
                                                    {{ $jadwalJam->guru->nip ?? '-' }}
                                                @else
                                                    -
                                                @endif
                                            </td>
                                            <td class="col-mapel">
                                                @if($isKbm && $jadwalJam)
                                                    {{ $jadwalJam->mataPelajaran->nama_mapel ?? '-' }}
                                                @else
                                                    <em class="non-kbm-text">{{ $jam->jenis }}</em>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        @else
                            <div class="empty-schedule">Tidak ada jadwal</div>
                        @endif
                    </div>
                @endforeach
            </div>

            <!-- Baris 3: Jumat -->
            <div class="schedule-row">
                @php
                    $hari = 'Jumat';
                    $jadwalHari = $jadwalByHari->get($hari, collect());
                    $jamHari = $jamBelajarByHari->get($hari, collect())->sortBy('urutan');
                @endphp
                <div class="schedule-column">
                    @if($jamHari->count() > 0)
                        <table class="schedule-table">
                            <thead>
                                <tr>
                                    <th colspan="4" class="day-header">{{ strtoupper($hari) }}</th>
                                </tr>
                                <tr class="column-header">
                                    <th class="col-waktu">Waktu</th>
                                    <th class="col-jam">Jam</th>
                                    <th class="col-kode">Kode</th>
                                    <th class="col-mapel">Mata Pelajaran</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($jamHari as $jam)
                                    @php
                                        $jadwalJam = $jadwalHari->where('jam_ke', $jam->urutan)->first();
                                        $isKbm = $jam->jenis === 'KBM';
                                        $rowClass = $isKbm ? '' : 'non-kbm-row';
                                    @endphp
                                    <tr class="{{ $rowClass }}">
                                        <td class="col-waktu">
                                            {{ \Carbon\Carbon::parse($jam->jam_mulai)->format('H:i') }} - {{ \Carbon\Carbon::parse($jam->jam_selesai)->format('H:i') }}
                                        </td>
                                        <td class="col-jam">
                                            @if($isKbm)
                                                {{ $jam->urutan }}
                                            @else
                                                -
                                            @endif
                                        </td>
                                        <td class="col-kode">
                                            @if($isKbm && $jadwalJam)
                                                {{ $jadwalJam->guru->nip ?? '-' }}
                                            @else
                                                -
                                            @endif
                                        </td>
                                        <td class="col-mapel">
                                            @if($isKbm && $jadwalJam)
                                                {{ $jadwalJam->mataPelajaran->nama_mapel ?? '-' }}
                                            @else
                                                <em class="non-kbm-text">{{ $jam->jenis }}</em>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    @else
                        <div class="empty-schedule">Tidak ada jadwal</div>
                    @endif
                </div>
                <div class="schedule-column"></div>
            </div>
        </div>

        <!-- Garis Pemisah -->
        <div class="divider"></div>

        <!-- Keterangan Guru -->
        <div class="guru-section">
            <h4 class="guru-title">DAFTAR GURU PENGAJAR</h4>
            <table class="guru-table">
                <tbody>
                    @php
                        $guruSorted = $guruList->sortBy('nip');
                        $guruChunks = $guruSorted->chunk(3);
                    @endphp
                    @foreach($guruChunks as $chunk)
                        <tr>
                            @foreach($chunk as $guru)
                                <td class="guru-item">
                                    <strong>{{ $guru->nip }}</strong> - {{ $guru->nama }}
                                </td>
                            @endforeach
                            @for($i = $chunk->count(); $i < 3; $i++)
                                <td class="guru-item"></td>
                            @endfor
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <!-- Footer -->
        <div class="footer-section">
            <p>Dicetak: {{ now()->format('d F Y, H:i') }} WIB</p>
        </div>
    </div>
</div>

<style>
    /* Base Styles */
    .print-body {
        font-family: 'Segoe UI', Arial, sans-serif;
        line-height: 1.4;
        color: #000;
        background: #fff;
    }

    /* Header Section */
    .header-section {
        text-align: center;
        margin-bottom: 15px;
        padding-bottom: 12px;
    }

    .custom-header-wrapper {
        border: 2px solid #333;
        padding: 15px;
        background: white;
        margin-bottom: 20px;
    }

    .header-content {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 15px;
    }

    .logo-left {
        width: 80px;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
    }

    .logo-right {
        width: 80px;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
    }

    .school-logo {
        height: 70px;
        width: auto;
        max-width: 70px;
    }

    .school-logo-right {
        height: 70px;
        width: auto;
        max-width: 70px;
    }

    .school-info {
        text-align: center;
        flex: 1;
        padding: 0 10px;
    }

    .school-name {
        margin: 0 0 3px 0;
        font-weight: 700;
        font-size: 16px;
        color: #000;
        letter-spacing: 0.5px;
    }

    .school-address {
        margin: 0 0 3px 0;
        font-size: 10px;
        color: #333;
        font-weight: 500;
    }

    .school-contact {
        margin: 0;
        font-size: 9px;
        color: #555;
        line-height: 1.3;
    }

    .header-divider {
        border-top: 3px double #000;
        margin-top: 8px;
    }

    /* Title Section */
    .title-section {
        text-align: center;
        margin-bottom: 15px;
    }

    .main-title {
        margin: 0 0 3px 0;
        font-weight: 700;
        font-size: 16px;
        color: #000;
        letter-spacing: 1px;
    }

    .sub-title {
        margin: 0 0 3px 0;
        font-weight: 600;
        font-size: 14px;
        color: #000;
    }

    .period-info {
        margin: 0;
        font-size: 10px;
        color: #555;
    }

    /* Schedule Container */
    .schedule-container {
        width: 100%;
    }

    .schedule-row {
        display: flex;
        gap: 10px;
        margin-bottom: 10px;
    }

    .schedule-column {
        flex: 1;
        min-width: 0;
    }

    /* Schedule Table */
    .schedule-table {
        width: 100%;
        border-collapse: collapse;
        font-size: 11px;
        table-layout: fixed;
        border: 2px solid #333;
    }

    .schedule-table th,
    .schedule-table td {
        border: 1px solid #555;
        padding: 5px 6px;
        text-align: center;
        vertical-align: middle;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .day-header {
        background: linear-gradient(180deg, #4a90d9 0%, #357abd 100%);
        color: #fff;
        font-weight: 700;
        font-size: 12px;
        padding: 7px !important;
        text-transform: uppercase;
        letter-spacing: 1px;
        border: none !important;
    }

    .column-header th {
        background: #e9ecef;
        font-weight: 600;
        font-size: 10px;
        color: #333;
        padding: 5px 4px !important;
        border: 1px solid #555 !important;
    }

    /* Fixed Column Widths */
    .col-waktu {
        width: 22%;
        font-size: 10px !important;
        white-space: nowrap;
        padding: 5px 4px !important;
    }

    .col-jam {
        width: 8%;
        font-weight: 600;
        font-size: 11px;
    }

    .col-kode {
        width: 8%;
        font-weight: 600;
        font-size: 10px;
        word-wrap: break-word;
    }

    .col-mapel {
        width: 62%;
        text-align: left !important;
        padding: 5px 8px !important;
        font-size: 11px;
        word-wrap: break-word;
        overflow-wrap: break-word;
        white-space: normal;
        line-height: 1.3;
    }

    /* Table Body Styles */
    .schedule-table tbody tr:nth-child(even) {
        background: #f8f9fa;
    }

    .schedule-table tbody tr:hover {
        background: #e3f2fd;
    }

    .non-kbm-row {
        background: #fffde7 !important;
    }

    .non-kbm-row:hover {
        background: #fff9c4 !important;
    }

    .non-kbm-text {
        color: #f57c00;
        font-style: italic;
        font-size: 10px;
    }

    .empty-schedule {
        padding: 20px;
        text-align: center;
        color: #999;
        font-style: italic;
        border: 1px dashed #ccc;
        background: #fafafa;
    }

    /* Divider */
    .divider {
        border-top: 2px solid #000;
        margin: 15px 0;
    }

    /* Guru Section */
    .guru-section {
        margin-top: 10px;
    }

    .guru-title {
        margin: 0 0 8px 0;
        font-weight: 700;
        font-size: 11px;
        color: #000;
    }

    .guru-table {
        width: 100%;
        border-collapse: collapse;
        font-size: 9px;
    }

    .guru-item {
        padding: 4px 8px;
        width: 33.33%;
        border-bottom: 1px solid #ddd;
        text-align: left;
    }

    /* Footer Section */
    .footer-section {
        margin-top: 15px;
        text-align: right;
        font-size: 9px;
        color: #666;
    }

    .footer-section p {
        margin: 0;
    }

    /* Print Styles */
    @media print {
        * {
            -webkit-print-color-adjust: exact !important;
            print-color-adjust: exact !important;
        }

        body {
            background: white !important;
            margin: 0 !important;
            padding: 0 !important;
        }

        .no-print,
        .navbar-vertical,
        header,
        footer,
        .btn,
        .page-header,
        .modal,
        nav {
            display: none !important;
        }

        .page-wrapper,
        .page-body,
        .container-xl {
            background: white !important;
            padding: 0 !important;
            margin: 0 !important;
            max-width: none !important;
        }

        .card,
        .no-card-style {
            border: none !important;
            box-shadow: none !important;
            margin: 0 !important;
        }

        .card-body,
        .print-body {
            padding: 10mm !important;
        }

        #printContent {
            max-width: 100% !important;
            width: 100% !important;
            margin: 0 !important;
            box-shadow: none !important;
        }

        .schedule-table {
            page-break-inside: avoid;
        }

        .schedule-row {
            page-break-inside: avoid;
        }

        .day-header {
            background: #4a90d9 !important;
            -webkit-print-color-adjust: exact !important;
        }

        .column-header th {
            background: #e9ecef !important;
        }

        .non-kbm-row {
            background: #fff9e6 !important;
        }
    }

    /* Screen Styles */
    @media screen {
        .no-print {
            display: block !important;
            margin-bottom: 20px;
        }

        #printContent {
            max-width: 210mm;
            margin: 20px auto;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
            border-radius: 8px;
            overflow: hidden;
        }

        .card-body {
            padding: 25px;
        }
    }
</style>
@endsection
