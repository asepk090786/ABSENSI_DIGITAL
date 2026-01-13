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
            font-family: Arial, sans-serif;
            color: #333;
            background: white;
            line-height: 1.4;
        }
        
        .page {
            width: 210mm;
            height: 297mm;
            margin: 0 auto;
            padding: 20px;
            background: white;
        }
        
        .header {
            text-align: center;
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 3px solid #2c5282;
        }
        
        .header-content {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 15px;
            margin-bottom: 10px;
        }
        
        .logo {
            height: 50px;
        }
        
        .school-info {
            text-align: left;
        }
        
        .school-info h3 {
            margin: 0;
            font-weight: 700;
            color: #1a202c;
            font-size: 16px;
        }
        
        .school-info p {
            margin: 2px 0;
            font-size: 11px;
            color: #666;
        }
        
        .title {
            text-align: center;
            margin-bottom: 15px;
        }
        
        .title h2 {
            margin: 0 0 5px 0;
            font-weight: 700;
            color: #2c5282;
            font-size: 18px;
        }
        
        .title h3 {
            margin: 0 0 8px 0;
            font-weight: 600;
            color: #4a5568;
            font-size: 14px;
        }
        
        .title p {
            margin: 0;
            font-size: 11px;
            color: #718096;
        }
        
        .jadwal-section {
            margin-bottom: 12px;
        }
        
        .hari-header {
            background: linear-gradient(90deg, #2c5282, #2d3748);
            color: white;
            padding: 6px 10px;
            margin-bottom: 6px;
            font-weight: 600;
            font-size: 12px;
            border-radius: 4px;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 11px;
            margin-bottom: 12px;
        }
        
        thead {
            background: #edf2f7;
        }
        
        thead th {
            border: 1px solid #cbd5e0;
            padding: 5px;
            text-align: left;
            font-weight: 600;
        }
        
        thead th:nth-child(1),
        thead th:nth-child(3) {
            text-align: center;
            width: 12%;
        }
        
        tbody td {
            border: 1px solid #cbd5e0;
            padding: 5px;
        }
        
        tbody tr td:first-child {
            background: #f7fafc;
            font-weight: 500;
            text-align: center;
        }
        
        tbody tr td:last-child {
            text-align: center;
            font-weight: 500;
            color: #2c5282;
        }
        
        tbody tr.non-kbm {
            background: #f0f4f8;
        }
        
        .divider {
            border-top: 2px solid #2c5282;
            margin: 12px 0;
        }
        
        .guru-section {
            margin-top: 12px;
        }
        
        .guru-section h4 {
            margin: 0 0 6px 0;
            font-weight: 600;
            color: #2c5282;
            font-size: 12px;
        }
        
        .guru-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 10px;
        }
        
        .guru-table td {
            padding: 4px 10px;
            width: 33%;
            border-bottom: 1px solid #e2e8f0;
        }
        
        .guru-table td strong {
            color: #2c5282;
        }
        
        .footer {
            margin-top: 12px;
            text-align: right;
            font-size: 9px;
            color: #718096;
        }
        
        .footer p {
            margin: 2px 0;
        }
    </style>
</head>
<body>
    <div class="page">
        <!-- Header -->
        <div class="header">
            <div class="header-content">
                @if($sekolah && $sekolah->logo && file_exists(public_path('storage/' . $sekolah->logo)))
                    <img src="{{ public_path('storage/' . $sekolah->logo) }}" class="logo">
                @endif
                <div class="school-info">
                    <h3>{{ $sekolah->nama_sekolah ?? 'Sekolah' }}</h3>
                    <p>{{ $sekolah->alamat ?? '' }}</p>
                </div>
            </div>
        </div>

        <!-- Judul -->
        <div class="title">
            <h2>JADWAL PELAJARAN</h2>
            <h3>Kelas {{ strtoupper($kelas->nama_kelas) }}</h3>
            <p>
                <strong>{{ $tahunAjaranAktif->nama_tahun ?? '-' }}</strong> | 
                <strong>{{ $semesterAktif->nama_semester ?? '-' }} (Aktif)</strong>
            </p>
        </div>

        <!-- Jadwal per Hari -->
        @php
            $jadwalByHari = $jadwalSorted->groupBy('hari');
            $hariList = ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat'];
        @endphp

        @foreach($hariList as $hari)
            @php
                $jadwalHari = $jadwalByHari->get($hari, collect());
                $jamHari = $jamBelajarByHari->get($hari, collect())->sortBy('urutan');
            @endphp
            @if($jamHari->count() > 0)
                <div class="jadwal-section">
                    <div class="hari-header">📅 {{ $hari }}</div>
                    
                    <table>
                        <thead>
                            <tr>
                                <th>Jam</th>
                                <th>Mapel / Kegiatan</th>
                                <th>Guru</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($jamHari as $jam)
                                @php
                                    $jadwalJam = $jadwalHari->where('jam_ke', $jam->urutan)->first();
                                @endphp
                                <tr class="{{ $jam->jenis !== 'KBM' ? 'non-kbm' : '' }}">
                                    <td>{{ $jam->urutan }}</td>
                                    <td>
                                        @if($jam->jenis === 'KBM' && $jadwalJam)
                                            <strong>{{ $jadwalJam->mataPelajaran->nama_mapel ?? '-' }}</strong>
                                        @else
                                            <em style="color: #718096;">{{ $jam->jenis }}</em>
                                        @endif
                                    </td>
                                    <td>
                                        @if($jadwalJam)
                                            {{ $jadwalJam->guru->nip ?? '-' }}
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
        @endforeach

        <!-- Divider -->
        <div class="divider"></div>

        <!-- Daftar Guru -->
        <div class="guru-section">
            <h4>DAFTAR GURU PENGAJAR</h4>
            <table class="guru-table">
                <tbody>
                    @php
                        $guruChunks = $guruList->chunk(3);
                    @endphp
                    @foreach($guruChunks as $chunk)
                        <tr>
                            @foreach($chunk as $guru)
                                <td>
                                    <strong>{{ $guru->nip }}</strong> - {{ substr($guru->nama, 0, 20) }}{{ strlen($guru->nama) > 20 ? '...' : '' }}
                                </td>
                            @endforeach
                            @for($i = $chunk->count(); $i < 3; $i++)
                                <td></td>
                            @endfor
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <!-- Footer -->
        <div class="footer">
            <p>Dicetak: {{ now()->format('d F Y H:i') }}</p>
        </div>
    </div>
</body>
</html>
