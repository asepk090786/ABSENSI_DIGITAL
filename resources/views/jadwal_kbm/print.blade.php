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
        <div style="text-align: center; margin-bottom: 20px; padding-bottom: 15px; border-bottom: 3px solid #2c5282;">
            <div style="display: flex; align-items: center; justify-content: center; gap: 15px; margin-bottom: 10px;">
                @if($sekolah && $sekolah->logo && file_exists(public_path('storage/' . $sekolah->logo)))
                    <img src="{{ asset('storage/' . $sekolah->logo) }}" style="height: 50px;">
                @endif
                <div style="text-align: left;">
                    <h3 style="margin: 0; font-weight: 700; color: #1a202c; font-size: 16px;">{{ $sekolah->nama_sekolah ?? 'Sekolah' }}</h3>
                    <p style="margin: 2px 0; font-size: 11px; color: #666;">{{ $sekolah->alamat ?? '' }}</p>
                </div>
            </div>
        </div>

        <!-- Judul -->
        <div style="text-align: center; margin-bottom: 15px;">
            <h2 style="margin: 0 0 5px 0; font-weight: 700; color: #2c5282; font-size: 18px;">JADWAL PELAJARAN</h2>
            <h3 style="margin: 0 0 8px 0; font-weight: 600; color: #4a5568; font-size: 14px;">Kelas {{ strtoupper($kelas->nama_kelas) }}</h3>
            <p style="margin: 0; font-size: 11px; color: #718096;">
                <strong>{{ $tahunAjaranAktif->nama_tahun ?? '-' }}</strong> | 
                <strong>{{ $semesterAktif->nama_semester ?? '-' }} (Aktif)</strong>
            </p>
        </div>

        <!-- Tabel Jadwal Kompak -->
        <div style="margin-bottom: 15px;">
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
                    <div style="margin-bottom: 12px;">
                        <!-- Nama Hari Header -->
                        <div style="background: linear-gradient(90deg, #2c5282, #2d3748); color: white; padding: 6px 10px; margin-bottom: 6px; font-weight: 600; font-size: 12px; border-radius: 4px;">
                            📅 {{ $hari }}
                        </div>
                        
                        <!-- Tabel Jadwal per Hari -->
                        <table style="width: 100%; border-collapse: collapse; font-size: 11px;">
                            <thead>
                                <tr style="background: #edf2f7;">
                                    <th style="border: 1px solid #cbd5e0; padding: 5px; text-align: center; font-weight: 600; width: 15%;">Jam</th>
                                    <th style="border: 1px solid #cbd5e0; padding: 5px; text-align: left; font-weight: 600;">Mapel / Kegiatan</th>
                                    <th style="border: 1px solid #cbd5e0; padding: 5px; text-align: center; font-weight: 600; width: 12%;">Guru</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($jamHari as $jam)
                                    @php
                                        $jadwalJam = $jadwalHari->where('jam_ke', $jam->urutan)->first();
                                    @endphp
                                    <tr style="border-bottom: 1px solid #e2e8f0; {{ $jam->jenis !== 'KBM' ? 'background: #f0f4f8;' : '' }}">
                                        <td style="border: 1px solid #cbd5e0; padding: 5px; text-align: center; background: #f7fafc; font-weight: 500;">{{ $jam->urutan }}</td>
                                        <td style="border: 1px solid #cbd5e0; padding: 5px;">
                                            @if($jam->jenis === 'KBM' && $jadwalJam)
                                                <strong>{{ $jadwalJam->mataPelajaran->nama_mapel ?? '-' }}</strong>
                                            @else
                                                <em style="color: #718096;">{{ $jam->jenis }}</em>
                                            @endif
                                        </td>
                                        <td style="border: 1px solid #cbd5e0; padding: 5px; text-align: center; font-weight: 500; color: #2c5282;">
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
        </div>

        <!-- Garis Pemisah -->
        <div style="border-top: 2px solid #2c5282; margin: 12px 0;"></div>

        <!-- Keterangan Guru Compact -->
        <div style="margin-top: 12px;">
            <h4 style="margin: 0 0 6px 0; font-weight: 600; color: #2c5282; font-size: 12px;">DAFTAR GURU PENGAJAR</h4>
            <table style="width: 100%; border-collapse: collapse; font-size: 10px;">
                <tbody>
                    @php
                        $guruChunks = $guruList->chunk(3);
                    @endphp
                    @foreach($guruChunks as $chunk)
                        <tr>
                            @foreach($chunk as $guru)
                                <td style="padding: 4px 10px; width: 33%; border-bottom: 1px solid #e2e8f0;">
                                    <strong>{{ $guru->nip }}</strong> - {{ substr($guru->nama, 0, 20) }}{{ strlen($guru->nama) > 20 ? '...' : '' }}
                                </td>
                            @endforeach
                            @for($i = $chunk->count(); $i < 3; $i++)
                                <td style="padding: 4px 10px; width: 33%;"></td>
                            @endfor
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <!-- Footer -->
        <div style="margin-top: 12px; text-align: right; font-size: 9px; color: #718096;">
            <p style="margin: 2px 0;">Dicetak: {{ now()->format('d F Y H:i') }}</p>
        </div>
    </div>
</div>

<style>
    /* Print Styles - Hide everything except jadwal */
    @media print {
        * {
            margin: 0 !important;
            padding: 0 !important;
            box-shadow: none !important;
            border: none !important;
        }
        
        body {
            background: white !important;
            color: black !important;
            font-family: Arial, sans-serif !important;
        }
        
        /* Hide all non-print elements */
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
        
        /* Print container */
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
            padding: 20px !important;
            background: white !important;
        }
        
        /* Make content fit A4 */
        #printContent {
            max-width: 210mm !important;
            height: 297mm !important;
            margin: 0 !important;
            padding: 0 !important;
        }
        
        /* Table styling for print */
        table {
            page-break-inside: avoid !important;
            width: 100% !important;
        }
        
        thead {
            display: table-header-group !important;
        }
        
        tfoot {
            display: table-footer-group !important;
        }
        
        tr {
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
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }
    }
</style>
@endsection
