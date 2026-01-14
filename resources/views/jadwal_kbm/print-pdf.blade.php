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
            line-height: 1.2;
            padding: 10px;
        }
        
        .page {
            width: 100%;
            margin: 0 auto;
            background: white;
        }
        
        /* Header Section */
        .header-section {
            text-align: center;
            margin-bottom: 15px;
            border-bottom: 3px double #000;
            padding-bottom: 10px;
        }
        
        .header-content {
            width: 100%;
            text-align: center;
            margin-bottom: 5px;
        }
        
        .logo {
            height: 60px;
            width: auto;
            max-width: 60px;
            display: inline-block;
            vertical-align: middle;
            margin: 0 10px;
        }
        
        .header-text {
            display: inline-block;
            text-align: center;
            vertical-align: middle;
            max-width: 70%;
        }
        
        .header-text p {
            margin: 2px 0;
            line-height: 1.3;
        }
        
        /* Title Section */
        .title-section {
            text-align: center;
            margin-bottom: 15px;
        }
        
        .title-section h1 {
            margin: 0 0 5px 0;
            font-weight: 800;
            color: #1d4ed8;
            font-size: 20px;
            text-transform: uppercase;
        }
        
        .title-section h2 {
            margin: 0 0 3px 0;
            font-weight: 600;
            color: #333;
            font-size: 16px;
        }
        
        .title-section p {
            margin: 2px 0;
            font-size: 10px;
            color: #666;
        }
        
        /* Schedule Container */
        .schedule-container {
            width: 100%;
        }
        
        .schedule-row {
            width: 100%;
            margin-bottom: 12px;
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
            font-size: 10px;
            table-layout: fixed;
            border: 1px solid #e0e0e0;
        }
        
        .schedule-table th,
        .schedule-table td {
            border: 1px solid #e0e0e0;
            padding: 4px 3px;
            text-align: center;
            vertical-align: middle;
        }
        
        .day-header {
            background-color: #2563eb;
            color: #fff;
            font-weight: bold;
            font-size: 11px;
            padding: 7px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .column-header th {
            background-color: #1d4ed8;
            color: #fff;
            font-weight: bold;
            font-size: 8px;
            padding: 6px 3px;
            text-transform: uppercase;
            letter-spacing: 0.3px;
        }
        
        /* Fixed Column Widths */
        .col-waktu {
            width: 22%;
            font-size: 9px !important;
            white-space: nowrap;
            padding: 5px 3px !important;
            box-sizing: border-box;
        }
        
        .col-jam {
            width: 8%;
            font-weight: 600;
            font-size: 10px;
            padding: 5px 3px !important;
            box-sizing: border-box;
        }
        
        .col-kode {
            width: 8%;
            font-weight: 600;
            font-size: 9px;
            word-wrap: break-word;
            padding: 5px 3px !important;
            box-sizing: border-box;
        }
        
        .col-mapel {
            width: 62%;
            text-align: left !important;
            padding: 5px 6px !important;
            font-size: 9px;
            word-wrap: break-word;
            overflow-wrap: break-word;
            white-space: normal;
            line-height: 1.4;
            box-sizing: border-box;
        }
        
        /* Table Body Styles */
        .schedule-table tbody tr {
            height: 24px;
            border-bottom: 1px solid #e8e8e8;
        }
        
        .schedule-table tbody tr:last-child {
            border-bottom: none;
        }
        
        .schedule-table tbody tr td {
            vertical-align: middle;
            padding: 4px 3px !important;
            box-sizing: border-box;
            overflow: hidden;
            text-overflow: ellipsis;
            height: 24px;
            background: #fff;
        }
        
        .schedule-table tbody tr:nth-child(odd) {
            background: #f9fafb;
        }
        
        .schedule-table tbody tr:nth-child(even) {
            background: #fff;
        }
        
        .non-kbm-row {
            background: #fffbeb !important;
        }
        
        .non-kbm-text {
            color: #d97706;
            font-style: italic;
            font-size: 8px;
            font-weight: 500;
        }
        
        .empty-schedule {
            padding: 15px;
            text-align: center;
            color: #999;
            font-style: italic;
            border: 1px dashed #ccc;
            background: #fafafa;
            border-radius: 4px;
            font-size: 9px;
        }
        
        /* Divider */
        .divider {
            border-top: 2px solid #000;
            margin: 12px 0;
        }
        
        /* Guru Section */
        .guru-section {
            margin-top: 12px;
            page-break-inside: avoid;
        }
        
        .guru-title {
            margin: 0 0 8px 0;
            font-weight: 700;
            font-size: 11px;
            color: #1d4ed8;
            text-transform: uppercase;
        }
        
        .guru-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 9px;
        }
        
        .guru-table th,
        .guru-table td {
            border: 1px solid #ddd;
            padding: 5px 8px;
            text-align: left;
        }
        
        .guru-table th {
            background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%);
            color: white;
            font-weight: 700;
            text-transform: uppercase;
            font-size: 8px;
        }
        
        .guru-table tbody tr:nth-child(even) {
            background: #f9fafb;
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
                
                @if($logoBase64)
                    <img src="{{ $logoBase64 }}" alt="Logo Sekolah" class="logo">
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
                                <td>{{ $guru->nama_guru ?? '-' }}</td>
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
