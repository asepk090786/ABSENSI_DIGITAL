@extends('layouts.app')

@section('title', 'Print Jadwal Kelas')

@section('content')
<div class="row mb-2 no-print">
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

<style>
    .jadwal-container {
        max-width: 210mm;
        height: 297mm;
        padding: 15px;
        background: white;
        font-family: Arial, sans-serif;
    }

    .jadwal-header {
        text-align: center;
        margin-bottom: 18px;
    }

    .jadwal-header-top {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 15px;
        margin-bottom: 10px;
    }

    .jadwal-header-logo {
        height: 50px;
        width: auto;
    }

    .jadwal-header-info h3 {
        margin: 0;
        font-weight: 700;
        font-size: 16px;
        color: #1a202c;
    }

    .jadwal-header-info p {
        margin: 2px 0 0 0;
        font-size: 11px;
        color: #666;
    }

    .jadwal-title {
        background: linear-gradient(135deg, #2c5282 0%, #1a365d 100%);
        color: white;
        padding: 12px 15px;
        border-radius: 6px;
        margin-bottom: 12px;
        text-align: center;
    }

    .jadwal-title h1 {
        margin: 0 0 4px 0;
        font-size: 26px;
        font-weight: 800;
        letter-spacing: 0.5px;
    }

    .jadwal-title h2 {
        margin: 0 0 6px 0;
        font-size: 18px;
        font-weight: 600;
    }

    .jadwal-title p {
        margin: 0;
        font-size: 10px;
        opacity: 0.9;
    }

    .jadwal-grid {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 10px;
        margin-bottom: 12px;
    }

    .jadwal-card {
        border-radius: 6px;
        padding: 10px;
        color: white;
        box-shadow: 0 2px 6px rgba(0,0,0,0.12);
        min-height: 140px;
        display: flex;
        flex-direction: column;
    }

    .jadwal-card h3 {
        margin: 0 0 8px 0;
        font-size: 15px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.5px;
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
        padding: 5px 7px;
        margin-bottom: 5px;
        border-radius: 3px;
        font-size: 9px;
        line-height: 1.3;
        border-left: 2px solid rgba(255,255,255,0.5);
    }

    .jadwal-item-mapel {
        font-weight: 600;
        margin-bottom: 2px;
    }

    .jadwal-item-guru {
        font-size: 8px;
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
        padding: 10px;
        border-radius: 6px;
        border-left: 4px solid #2c5282;
    }

    .jadwal-guru-section h4 {
        margin: 0 0 8px 0;
        font-size: 12px;
        font-weight: 700;
        color: #2c5282;
    }

    .jadwal-guru-list {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 6px;
    }

    .jadwal-guru-item {
        font-size: 8px;
        padding: 5px 6px;
        background: white;
        border-left: 3px solid #2c5282;
        border-radius: 3px;
        box-shadow: 0 1px 3px rgba(0,0,0,0.08);
    }

    .jadwal-guru-nip {
        font-weight: 700;
        color: #2c5282;
        display: block;
        font-size: 8px;
    }

    .jadwal-guru-nama {
        color: #4a5568;
        font-size: 7px;
        display: block;
    }

    .jadwal-footer {
        text-align: right;
        font-size: 8px;
        color: #718096;
        margin-top: 8px;
        padding-top: 6px;
        border-top: 1px solid #e2e8f0;
    }

    @media print {
        * {
            margin: 0 !important;
            padding: 0 !important;
            box-shadow: none !important;
        }
        
        body {
            background: white !important;
            color: black !important;
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
        }
        
        .card,
        .no-card-style {
            border: none !important;
            box-shadow: none !important;
            padding: 0 !important;
            margin: 0 !important;
            background: white !important;
        }
        
        .card-body,
        .print-body {
            padding: 0 !important;
            background: white !important;
        }
        
        #printContent {
            max-width: 210mm !important;
            height: 297mm !important;
            margin: 0 !important;
            padding: 0 !important;
        }
        
        .jadwal-card,
        .jadwal-guru-section,
        .jadwal-item {
            page-break-inside: avoid !important;
        }
        
        img {
            max-width: 100% !important;
        }
    }

    @media screen {
        .no-print {
            display: block !important;
            margin-bottom: 20px;
        }
        
        #printContent {
            max-width: 210mm;
            margin: 20px auto;
            box-shadow: 0 2px 12px rgba(0,0,0,0.15);
        }
    }
</style>

<div id="printContent" class="card no-card-style">
    <div class="card-body print-body">
        <div class="jadwal-container">
            
            <div class="jadwal-header">
                <div class="jadwal-header-top">
                    @if($sekolah && $sekolah->logo && file_exists(public_path('storage/' . $sekolah->logo)))
                        <img src="{{ asset('storage/' . $sekolah->logo) }}" class="jadwal-header-logo">
                    @endif
                    <div class="jadwal-header-info">
                        @if($sekolah && $sekolah->header_line1)
                            <div style="margin: 0; padding: 0; line-height: {{ $sekolah->header_line1_spacing ?? 1.0 }}; font-size: 11px;">{!! $sekolah->header_line1 !!}</div>
                        @endif
                        
                        @if($sekolah && $sekolah->header_line2)
                            <h3 style="margin: 2px 0 0 0; padding: 0; line-height: {{ $sekolah->header_line2_spacing ?? 1.0 }}; font-size: 16px; font-weight: 700; color: #1a202c;">{!! $sekolah->header_line2 !!}</h3>
                        @else
                            <h3>{{ $sekolah->nama_sekolah ?? 'Sekolah' }}</h3>
                        @endif
                        
                        @if($sekolah && $sekolah->header_line3)
                            <p style="margin: 2px 0 0 0; padding: 0; line-height: {{ $sekolah->header_line3_spacing ?? 1.0 }}; font-size: 11px; color: #666;">{!! $sekolah->header_line3 !!}</p>
                        @else
                            <p>{{ $sekolah->alamat ?? '' }}</p>
                        @endif
                        
                        @if($sekolah && $sekolah->header_line4)
                            <p style="margin: 2px 0 0 0; padding: 0; line-height: {{ $sekolah->header_line4_spacing ?? 1.0 }}; font-size: 10px; color: #777;">{!! $sekolah->header_line4 !!}</p>
                        @endif
                    </div>
                </div>
            </div>

            
            <div class="jadwal-title">
                <h1>Jadwal Pelajaran Kelas</h1>
                <h2>{{ strtoupper($kelas->nama_kelas) }}</h2>
                <p><strong>{{ $tahunAjaranAktif->nama_tahun ?? '-' }}</strong> | <strong>{{ $semesterAktif->nama_semester ?? '-' }}</strong></p>
            </div>

            
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

            
            <div class="jadwal-guru-section">
                <h4>📋 DAFTAR GURU PENGAJAR</h4>
                <div class="jadwal-guru-list">
                    @foreach($guruList as $guru)
                        <div class="jadwal-guru-item">
                            <span class="jadwal-guru-nip">{{ $guru->nip }}</span>
                            <span class="jadwal-guru-nama">{{ substr($guru->nama, 0, 16) }}{{ strlen($guru->nama) > 16 ? '.' : '' }}</span>
                        </div>
                    @endforeach
                </div>
            </div>

            
            <div class="jadwal-footer">
                <p>Dicetak: {{ now()->format('d F Y H:i') }}</p>
            </div>
        </div>
    </div>
</div>

@endsection
