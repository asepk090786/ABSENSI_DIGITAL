<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Jadwal Mengajar - {{ $guru->nama }}</title>
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
        
        /* Schedule Table */
        .schedule-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 7px;
            border: 1px solid #e0e0e0;
            margin-bottom: 4px;
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
            width: 20%;
            font-size: 7px;
        }
        
        .col-jam {
            width: 8%;
            font-weight: bold;
            font-size: 7.5px;
        }
        
        .col-mapel {
            width: 36%;
            text-align: left;
            font-size: 7.5px;
            line-height: 1.3;
        }
        
        .col-kelas {
            width: 36%;
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
            margin: 4px 0;
        }
        
        /* Summary */
        .summary-section {
            margin-top: 4px;
            font-size: 7px;
            border: 1px solid #ddd;
            padding: 4px;
            background-color: #f9fafb;
        }
        
        .summary-item {
            display: inline-block;
            margin-right: 10px;
            margin-bottom: 2px;
        }
        
        .summary-item strong {
            color: #1d4ed8;
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
            <h1>Jadwal Mengajar</h1>
            <h2>{{ $guru->nama }}</h2>
            <p>
                NIP: {{ $guru->nip ?? '-' }}
                @if($tahunAjaranAktif)
                    | {{ $tahunAjaranAktif->nama_tahun }}
                @endif
                @if($semesterAktif)
                    | {{ $semesterAktif->nama_semester }}
                @endif
            </p>
        </div>
        
        <!-- Schedule Tables by Hari -->
        @php
            $hariList = ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
            $hasJadwal = false;
        @endphp
        
        @foreach($hariList as $hari)
            @php
                $jadwalHari = $jadwalGuru->get($hari, collect());
            @endphp
            
            @if($jadwalHari->isNotEmpty())
                @php $hasJadwal = true; @endphp
                <table class="schedule-table">
                    <thead>
                        <tr>
                            <th colspan="4" class="day-header">{{ strtoupper($hari) }}</th>
                        </tr>
                        <tr class="column-header">
                            <th class="col-jam">Jam</th>
                            <th class="col-waktu">Waktu</th>
                            <th class="col-mapel">Mata Pelajaran</th>
                            <th class="col-kelas">Kelas</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($jadwalHari->sortBy('jam_ke') as $jadwal)
                            <tr>
                                <td class="col-jam">{{ $jadwal->jam_ke }}</td>
                                <td class="col-waktu">
                                    {{ $jadwal->jamBelajar->jam_mulai ?? '-' }} - {{ $jadwal->jamBelajar->jam_selesai ?? '-' }}
                                </td>
                                <td class="col-mapel">
                                    {{ $jadwal->mataPelajaran->nama_mapel ?? '-' }}
                                    <br>
                                    <small>{{ $jadwal->mataPelajaran->kode_mapel ?? '' }}</small>
                                </td>
                                <td class="col-kelas">
                                    {{ $jadwal->kelas->nama_kelas ?? '-' }}
                                    @if($jadwal->kelas->tingkat_kelas)
                                        <br><small>Tingkat: {{ $jadwal->kelas->tingkat_kelas }}</small>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif
        @endforeach
        
        @if(!$hasJadwal)
            <div class="empty-schedule">Belum ada jadwal mengajar untuk guru ini.</div>
        @else
            <!-- Divider -->
            <div class="divider"></div>
            
            <!-- Summary -->
            <div class="summary-section">
                <div class="summary-item">
                    <strong>Total Jam:</strong> {{ $jadwalGuru->flatten()->count() }} jam/minggu
                </div>
                <div class="summary-item">
                    <strong>Jumlah Kelas:</strong> {{ $jadwalGuru->flatten()->pluck('kelas_id')->unique()->count() }} kelas
                </div>
                <div class="summary-item">
                    <strong>Jumlah Mapel:</strong> {{ $jadwalGuru->flatten()->pluck('mata_pelajaran_id')->unique()->count() }} mapel
                </div>
            </div>
        @endif
    </div>
</body>
</html>
