<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Jadwal Pelajaran - Kelas {{ $kelas->nama_kelas }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'DejaVu Sans', Arial, sans-serif;
            color: #333;
            background: white;
            line-height: 1.1;
            padding: 5px;
            font-size: 7px;
        }
        
        .page {
            width: 100%;
            margin: 0 auto;
            background: white;
        }
        
        /* Header Section */
        .header-section {
            text-align: center;
            margin-bottom: 6px;
            border-bottom: 2px solid #000;
            padding-bottom: 4px;
        }
        
        .header-content {
            width: 100%;
            text-align: center;
            margin-bottom: 3px;
        }
        
        .logo {
            height: 35px;
            width: auto;
            max-width: 40px;
            display: inline-block;
            vertical-align: middle;
            margin: 0 5px;
        }
        
        .header-text {
            display: inline-block;
            text-align: center;
            vertical-align: middle;
            max-width: 70%;
            font-size: 7px;
        }
        
        .header-text p {
            margin: 1px 0;
            line-height: 1.1;
        }
        
        /* Title Section */
        .title-section {
            text-align: center;
            margin-bottom: 6px;
        }
        
        .title-section h1 {
            margin: 0 0 2px 0;
            font-weight: bold;
            color: #1d4ed8;
            font-size: 11px;
            text-transform: uppercase;
        }
        
        .title-section h2 {
            margin: 0 0 2px 0;
            font-weight: bold;
            color: #333;
            font-size: 9px;
        }
        
        .title-section p {
            margin: 1px 0;
            font-size: 7px;
            color: #666;
        }
        
        /* Schedule Container */
        .schedule-container {
            width: 100%;
        }
        
        .schedule-row {
            width: 100%;
            margin-bottom: 4px;
            page-break-inside: avoid;
        }
        
        .schedule-column {
            width: 48%;
            display: inline-block;
            vertical-align: top;
            margin-right: 2%;
        }
        
        .schedule-column:last-child {
            margin-right: 0;
        }
        
        .schedule-column-full {
            width: 100%;
            display: block;
        }
        
        /* Schedule Table */
        .schedule-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 7px;
            table-layout: fixed;
            border: 1px solid #e0e0e0;
        }
        
        .schedule-table th,
        .schedule-table td {
            border: 1px solid #e0e0e0;
            padding: 2px 1px;
            text-align: center;
            vertical-align: middle;
        }
        
        .day-header {
            background-color: #2563eb;
            color: #fff;
            font-weight: bold;
            font-size: 9px;
            padding: 4px;
            text-transform: uppercase;
            letter-spacing: 0.3px;
        }
        
        .column-header th {
            background-color: #1d4ed8;
            color: #fff;
            font-weight: bold;
            font-size: 7px;
            padding: 3px 1px;
            text-transform: uppercase;
            letter-spacing: 0.2px;
        }
        
        /* Fixed Column Widths */
        .col-waktu {
            width: 22%;
            font-size: 7px;
            white-space: nowrap;
        }
        
        .col-jam {
            width: 8%;
            font-weight: bold;
            font-size: 7.5px;
        }
        
        .col-kode {
            width: 8%;
            font-weight: bold;
            font-size: 7px;
        }
        
        .col-mapel {
            width: 62%;
            text-align: left;
            font-size: 7.5px;
            line-height: 1.3;
        }
        
        /* Table Body Styles */
        .schedule-table tbody tr {
            height: 16px;
        }
        
        .schedule-table tbody tr td {
            vertical-align: middle;
            padding: 2px 1px;
            height: 16px;
            background: #fff;
        }
        
        .schedule-table tbody tr:nth-child(odd) {
            background-color: #f9fafb;
        }
        
        .schedule-table tbody tr:nth-child(even) {
            background-color: #fff;
        }
        
        .non-kbm-row {
            background-color: #fffbeb;
        }
        
        .non-kbm-text {
            color: #d97706;
            font-style: italic;
            font-size: 7px;
            font-weight: 500;
        }
        
        .empty-schedule {
            padding: 8px;
            text-align: center;
            color: #999;
            font-style: italic;
            border: 1px dashed #ccc;
            background-color: #fafafa;
            font-size: 7px;
        }
        
        /* Divider */
        .divider {
            border-top: 2px solid #000;
            margin: 5px 0;
        }
        
        /* Guru Section */
        .guru-section {
            margin-top: 5px;
            page-break-inside: avoid;
        }
        
        .guru-title {
            margin: 0 0 3px 0;
            font-weight: bold;
            font-size: 7px;
            color: #1d4ed8;
            text-transform: uppercase;
        }
        
        .guru-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 7px;
        }
        
        .guru-table th,
        .guru-table td {
            border: 1px solid #ddd;
            padding: 2px 3px;
            text-align: left;
        }
        
        .guru-table th {
            background-color: #2563eb;
            color: white;
            font-weight: bold;
            text-transform: uppercase;
            font-size: 7px;
        }
        
        .guru-table tbody tr:nth-child(even) {
            background-color: #f9fafb;
        }
    </style>
</head>
<body>
    <div class="page">
        <!-- Header -->
        <div class="header-section">
            <div class="header-content">
                @if($logoHeaderKiriBase64)
                    <img src="{{ $logoHeaderKiriBase64 }}" alt="Logo Kiri" class="logo">
                @endif
                
                @if($logoBase64)
                    <img src="{{ $logoBase64 }}" alt="Logo Sekolah" class="logo">
                @endif
            </div>
            <div class="header-text">
                @if($sekolah && $sekolah->header_line1)
                    {!! $sekolah->header_line1 !!}
                @endif
                @if($sekolah && $sekolah->header_line2)
                    {!! $sekolah->header_line2 !!}
                @endif
                @if($sekolah && $sekolah->header_line3)
                    {!! $sekolah->header_line3 !!}
                @endif
                @if($sekolah && $sekolah->header_line4)
                    {!! $sekolah->header_line4 !!}
                @endif
            </div>
        </div>
        
        <!-- Title -->
        <div class="title-section">
            <h1>Jadwal Pelajaran</h1>
            <h2>KELAS {{ $kelas->nama_kelas }}</h2>
            @if($tahunAjaranAktif)
                <p>{{ $tahunAjaranAktif->nama_tahun }} | {{ $semesterAktif ? $semesterAktif->nama_semester : '' }} | Ganji/Genap</p>
            @endif
        </div>
        
        <!-- Schedule Tables -->
        <div class="schedule-container">
            @php
                $hariList = ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat'];
            @endphp
            
            <!-- Baris 1: Senin dan Selasa -->
            <table style="width: 100%; margin-bottom: 4px; border: 0;">
                <tr>
                    @foreach(['Senin', 'Selasa'] as $hari)
                        @php
                            $jadwalHari = $jadwalByHari->get($hari, collect());
                            $jamHari = $jamBelajarByHari->get($hari, collect())->sortBy('urutan');
                        @endphp
                        <td class="schedule-column" style="vertical-align: top;">
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
                        </td>
                    @endforeach
                </tr>
            </table>

            <!-- Baris 2: Rabu dan Kamis -->
            <table style="width: 100%; margin-bottom: 4px; border: 0;">
                <tr>
                    @foreach(['Rabu', 'Kamis'] as $hari)
                        @php
                            $jadwalHari = $jadwalByHari->get($hari, collect());
                            $jamHari = $jamBelajarByHari->get($hari, collect())->sortBy('urutan');
                        @endphp
                        <td class="schedule-column" style="vertical-align: top;">
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
                        </td>
                    @endforeach
                </tr>
            </table>

            <!-- Baris 3: Jumat -->
            @php
                $hari = 'Jumat';
                $jadwalHari = $jadwalByHari->get($hari, collect());
                $jamHari = $jamBelajarByHari->get($hari, collect())->sortBy('urutan');
            @endphp
            <div class="schedule-column-full">
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
        </div>
        
        <!-- Divider -->
        <div class="divider"></div>

        <!-- Guru List -->
        @if($guruList->count() > 0)
            <div class="guru-section">
                <h3 class="guru-title">Daftar Guru Pengajar</h3>
                <table class="guru-table">
                    <thead>
                        <tr>
                            <th style="width: 5%;">No</th>
                            <th style="width: 15%;">NIP</th>
                            <th style="width: 30%;">Nama Guru</th>
                            <th style="width: 50%;">Mata Pelajaran</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($guruList as $index => $guru)
                            <tr>
                                <td style="text-align: center;">{{ $index + 1 }}</td>
                                <td>{{ $guru->nip ?? '-' }}</td>
                                <td>{{ $guru->nama ?? '-' }}</td>
                                <td>
                                    @php
                                        $mapelGuru = $jadwalSorted->where('guru_id', $guru->id)->pluck('mataPelajaran.nama_mapel')->unique()->implode(', ');
                                    @endphp
                                    {{ $mapelGuru ?: '-' }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
</body>
</html>
