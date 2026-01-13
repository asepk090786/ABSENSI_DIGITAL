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
            line-height: 1.2;
        }
        
        .page {
            width: 210mm;
            height: 297mm;
            margin: 0 auto;
            padding: 12px;
            background: white;
        }
        
        .jadwal-header {
            text-align: center;
            margin-bottom: 12px;
        }
        
        .jadwal-header-top {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 12px;
            margin-bottom: 8px;
        }
        
        .logo {
            height: 45px;
            width: auto;
        }
        
        .school-info {
            text-align: left;
        }
        
        .school-info h3 {
            margin: 0;
            font-weight: 700;
            color: #1a202c;
            font-size: 14px;
        }
        
        .school-info p {
            margin: 1px 0;
            font-size: 9px;
            color: #666;
        }
        
        .jadwal-title {
            background: linear-gradient(135deg, #2c5282 0%, #1a365d 100%);
            color: white;
            padding: 10px 12px;
            border-radius: 5px;
            margin-bottom: 10px;
            text-align: center;
        }
        
        .jadwal-title h1 {
            margin: 0 0 3px 0;
            font-weight: 800;
            color: white;
            font-size: 22px;
            letter-spacing: 0.3px;
        }
        
        .jadwal-title h2 {
            margin: 0 0 4px 0;
            font-weight: 600;
            color: white;
            font-size: 16px;
        }
        
        .jadwal-title p {
            margin: 0;
            font-size: 9px;
            opacity: 0.9;
        }
        
        .jadwal-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 8px;
            margin-bottom: 10px;
        }
        
        .jadwal-card {
            border-radius: 5px;
            padding: 9px;
            color: white;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
            min-height: 120px;
            display: flex;
            flex-direction: column;
        }
        
        .jadwal-card h3 {
            margin: 0 0 6px 0;
            font-weight: 700;
            color: white;
            font-size: 13px;
            text-transform: uppercase;
            letter-spacing: 0.3px;
        }
        
        .jadwal-card-content {
            flex: 1;
            overflow-y: auto;
        }
        
        .jadwal-card-hari-senin { background: linear-gradient(135deg, #48bb78, #38a169); }
        .jadwal-card-hari-selasa { background: linear-gradient(135deg, #4299e1, #3182ce); }
        .jadwal-card-hari-rabu { background: linear-gradient(135deg, #ed8936, #dd6b20); }
        .jadwal-card-hari-kamis { background: linear-gradient(135deg, #9f7aea, #805ad5); }
        .jadwal-card-hari-jumat { background: linear-gradient(135deg, #ed64a6, #d53f8c); }
        
        .jadwal-item {
            background: rgba(255,255,255,0.12);
            padding: 4px 6px;
            margin-bottom: 4px;
            border-radius: 3px;
            font-size: 8px;
            line-height: 1.2;
            border-left: 2px solid rgba(255,255,255,0.5);
        }
        
        .jadwal-item-mapel {
            font-weight: 600;
            margin-bottom: 1px;
        }
        
        .jadwal-item-guru {
            font-size: 7px;
            opacity: 0.85;
        }
        
        .jadwal-item-kegiatan {
            font-style: italic;
            font-weight: 600;
            text-align: center;
            opacity: 0.9;
        }
        
        .jadwal-guru-section {
            background: linear-gradient(135deg, #f7fafc, #edf2f7);
            padding: 8px;
            border-radius: 5px;
            border-left: 4px solid #2c5282;
        }
        
        .jadwal-guru-section h4 {
            margin: 0 0 6px 0;
            font-weight: 700;
            color: #2c5282;
            font-size: 11px;
        }
        
        .jadwal-guru-list {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 5px;
        }
        
        .jadwal-guru-item {
            font-size: 7.5px;
            padding: 4px 5px;
            background: white;
            border-left: 3px solid #2c5282;
            border-radius: 2px;
            box-shadow: 0 1px 2px rgba(0,0,0,0.05);
        }
        
        .jadwal-guru-nip {
            font-weight: 700;
            color: #2c5282;
            display: block;
            font-size: 7.5px;
        }
        
        .jadwal-guru-nama {
            color: #4a5568;
            font-size: 6.5px;
            display: block;
        }
        
        .jadwal-footer {
            text-align: right;
            font-size: 7px;
            color: #718096;
            margin-top: 6px;
            padding-top: 5px;
            border-top: 1px solid #e2e8f0;
        }
    </style>
</head>
<body>
    <div class="page">
        <!-- Header -->
        <div class="jadwal-header">
            <div class="jadwal-header-top">
                @if($logoBase64)
                    <img src="{{ $logoBase64 }}" class="logo">
                @endif
                <div class="school-info">
                    <h3>{{ $sekolah->nama_sekolah ?? 'Sekolah' }}</h3>
                    <p>{{ $sekolah->alamat ?? '' }}</p>
                </div>
            </div>
        </div>

        <!-- Title Card -->
        <div class="jadwal-title">
            <h1>Jadwal Pelajaran Kelas</h1>
            <h2>{{ strtoupper($kelas->nama_kelas) }}</h2>
            <p><strong>{{ $tahunAjaranAktif->nama_tahun ?? '-' }}</strong> | <strong>{{ $semesterAktif->nama_semester ?? '-' }}</strong></p>
        </div>

        <!-- Schedule Grid -->
        @php
            $jadwalByHari = $jadwalSorted->groupBy('hari');
            $hariList = [
                'Senin' => 'jadwal-card-hari-senin',
                'Selasa' => 'jadwal-card-hari-selasa',
                'Rabu' => 'jadwal-card-hari-rabu',
                'Kamis' => 'jadwal-card-hari-kamis',
                'Jumat' => 'jadwal-card-hari-jumat'
            ];
        @endphp

        <div class="jadwal-grid">
            @foreach($hariList as $hari => $hariClass)
                @php
                    $jadwalHari = $jadwalByHari->get($hari, collect());
                    $jamHari = $jamBelajarByHari->get($hari, collect())->sortBy('urutan');
                @endphp
                
                <div class="jadwal-card {{ $hariClass }}">
                    <h3>{{ $hari }}</h3>
                    <div class="jadwal-card-content">
                        @if($jamHari->count() > 0)
                            @foreach($jamHari as $jam)
                                @php
                                    $jadwalJam = $jadwalHari->where('jam_ke', $jam->urutan)->first();
                                @endphp
                                
                                <div class="jadwal-item">
                                    @if($jam->jenis === 'KBM' && $jadwalJam)
                                        <div class="jadwal-item-mapel">{{ $jam->urutan }}. {{ $jadwalJam->mataPelajaran->nama_mapel ?? '-' }}</div>
                                        <div class="jadwal-item-guru">{{ $jadwalJam->guru->nip ?? '-' }}</div>
                                    @else
                                        <div class="jadwal-item-kegiatan">{{ $jam->urutan }}. {{ $jam->jenis }}</div>
                                    @endif
                                </div>
                            @endforeach
                        @endif
                    </div>
                </div>
            @endforeach
        </div>

        <!-- Guru List Section -->
        <div class="jadwal-guru-section">
            <h4>📋 DAFTAR GURU PENGAJAR</h4>
            <div class="jadwal-guru-list">
                @foreach($guruList as $guru)
                    <div class="jadwal-guru-item">
                        <span class="jadwal-guru-nip">{{ $guru->nip }}</span>
                        <span class="jadwal-guru-nama">{{ substr($guru->nama, 0, 15) }}{{ strlen($guru->nama) > 15 ? '.' : '' }}</span>
                    </div>
                @endforeach
            </div>
        </div>

        <!-- Footer -->
        <div class="jadwal-footer">
            <p>Dicetak: {{ now()->format('d F Y H:i') }}</p>
        </div>
    </div>
</body>
</html>
