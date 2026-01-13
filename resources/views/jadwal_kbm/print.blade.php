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
        <div style="text-align: center; margin-bottom: 10px; padding-bottom: 8px; border-bottom: 2px solid #000;">
            <div style="display: flex; align-items: center; justify-content: center; gap: 15px; margin-bottom: 5px;">
                @if($sekolah && $sekolah->logo && file_exists(public_path('storage/' . $sekolah->logo)))
                    <img src="{{ asset('storage/' . $sekolah->logo) }}" style="height: 60px; width: auto;">
                @endif
                <div style="text-align: left;">
                    <h3 style="margin: 0; font-weight: 700; color: #000; font-size: 16px;">{{ $sekolah->nama_sekolah ?? 'Sekolah' }}</h3>
                    <p style="margin: 2px 0; font-size: 10px; color: #333;">{{ $sekolah->alamat ?? '' }}</p>
                </div>
            </div>
        </div>

        <!-- Judul -->
        <div style="text-align: center; margin-bottom: 15px;">
            <h2 style="margin: 0 0 3px 0; font-weight: 700; color: #000; font-size: 16px;">JADWAL PELAJARAN</h2>
            <h3 style="margin: 0 0 3px 0; font-weight: 600; color: #000; font-size: 14px;">KELAS {{ strtoupper($kelas->nama_kelas) }}</h3>
            <p style="margin: 0; font-size: 10px; color: #333;">
                {{ $tahunAjaranAktif->nama_tahun ?? '-' }} | {{ $semesterAktif->nama_semester ?? '-' }}
            </p>
        </div>

        @php
            $jadwalByHari = $jadwalSorted->groupBy('hari');
            $hariList = ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat'];
            
            // Bagi hari menjadi 2 kolom (Senin-Rabu di kiri, Kamis-Jumat di kanan)
            $kolomKiri = ['Senin', 'Rabu'];
            $kolomKanan = ['Selasa', 'Kamis'];
        @endphp

        <!-- Tabel Jadwal Format 2 Kolom -->
        <table style="width: 100%; border-collapse: collapse; font-size: 9px; margin-bottom: 10px;">
            <tbody>
                <!-- Baris 1: Senin dan Selasa -->
                <tr>
                    <!-- Kolom Kiri: Senin -->
                    @foreach(['Senin', 'Selasa'] as $index => $hari)
                        @php
                            $jadwalHari = $jadwalByHari->get($hari, collect());
                            $jamHari = $jamBelajarByHari->get($hari, collect())->sortBy('urutan');
                        @endphp
                        <td style="width: 50%; vertical-align: top; {{ $index === 0 ? 'padding-right: 5px;' : 'padding-left: 5px;' }}">
                            @if($jamHari->count() > 0)
                                <table style="width: 100%; border-collapse: collapse; font-size: 9px;">
                                    <!-- Header Hari -->
                                    <thead>
                                        <tr>
                                            <th colspan="5" style="border: 1px solid #000; padding: 4px; text-align: center; font-weight: bold; background-color: #e8e8e8;">{{ strtoupper($hari) }}</th>
                                        </tr>
                                        <tr style="background-color: #f0f0f0;">
                                            <th style="border: 1px solid #000; padding: 4px; text-align: center; font-weight: bold; width: 15%;">Hari</th>
                                            <th style="border: 1px solid #000; padding: 4px; text-align: center; font-weight: bold; width: 25%;">Waktu</th>
                                            <th style="border: 1px solid #000; padding: 4px; text-align: center; font-weight: bold; width: 12%;">Jam Ke</th>
                                            <th style="border: 1px solid #000; padding: 4px; text-align: center; font-weight: bold; width: 18%;">KODE GURU</th>
                                            <th style="border: 1px solid #000; padding: 4px; text-align: center; font-weight: bold;">MAPEL</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($jamHari as $index => $jam)
                                            @php
                                                $jadwalJam = $jadwalHari->where('jam_ke', $jam->urutan)->first();
                                            @endphp
                                            <tr>
                                                <!-- Hari (hanya tampil di baris pertama) -->
                                                @if($index === 0)
                                                    <td rowspan="{{ $jamHari->count() }}" style="border: 1px solid #000; padding: 4px; text-align: center; font-weight: bold; vertical-align: top;">{{ $hari }}</td>
                                                @endif
                                                
                                                <!-- Waktu -->
                                                <td style="border: 1px solid #000; padding: 4px; text-align: center; font-size: 8px;">
                                                    {{ $jam->waktu_mulai }} - {{ $jam->waktu_selesai }}
                                                </td>
                                                
                                                <!-- Jam Ke -->
                                                <td style="border: 1px solid #000; padding: 4px; text-align: center; font-weight: bold;">
                                                    @if($jam->jenis === 'KBM')
                                                        {{ $jam->urutan }}
                                                    @else
                                                        {{ $jam->jenis === 'ISTIRAHAT' ? 'IH' . ($jam->urutan - 3) : $jam->jenis }}
                                                    @endif
                                                </td>
                                                
                                                <!-- Kode Guru -->
                                                <td style="border: 1px solid #000; padding: 4px; text-align: center; font-weight: bold;">
                                                    @if($jam->jenis === 'KBM' && $jadwalJam)
                                                        {{ $jadwalJam->guru->nip ?? '-' }}
                                                    @endif
                                                </td>
                                                
                                                <!-- Mapel -->
                                                <td style="border: 1px solid #000; padding: 4px; text-align: left;">
                                                    @if($jam->jenis === 'KBM' && $jadwalJam)
                                                        {{ $jadwalJam->mataPelajaran->nama_mapel ?? '-' }}
                                                    @else
                                                        <em style="color: #666;">{{ $jam->jenis }}</em>
                                                    @endif
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            @endif
                        </td>
                    @endforeach
                </tr>
                
                <!-- Spacer -->
                <tr>
                    <td colspan="2" style="height: 15px;"></td>
                </tr>
                
                <!-- Baris 2: Rabu dan Kamis -->
                <tr>
                    @foreach(['Rabu', 'Kamis'] as $index => $hari)
                        @php
                            $jadwalHari = $jadwalByHari->get($hari, collect());
                            $jamHari = $jamBelajarByHari->get($hari, collect())->sortBy('urutan');
                        @endphp
                        <td style="width: 50%; vertical-align: top; {{ $index === 0 ? 'padding-right: 5px;' : 'padding-left: 5px;' }}">
                            @if($jamHari->count() > 0)
                                <table style="width: 100%; border-collapse: collapse; font-size: 9px;">
                                    <!-- Header Hari -->
                                    <thead>
                                        <tr>
                                            <th colspan="5" style="border: 1px solid #000; padding: 4px; text-align: center; font-weight: bold; background-color: #e8e8e8;">{{ strtoupper($hari) }}</th>
                                        </tr>
                                        <tr style="background-color: #f0f0f0;">
                                            <th style="border: 1px solid #000; padding: 4px; text-align: center; font-weight: bold; width: 15%;">Hari</th>
                                            <th style="border: 1px solid #000; padding: 4px; text-align: center; font-weight: bold; width: 25%;">Waktu</th>
                                            <th style="border: 1px solid #000; padding: 4px; text-align: center; font-weight: bold; width: 12%;">Jam Ke</th>
                                            <th style="border: 1px solid #000; padding: 4px; text-align: center; font-weight: bold; width: 18%;">KODE GURU</th>
                                            <th style="border: 1px solid #000; padding: 4px; text-align: center; font-weight: bold;">MAPEL</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($jamHari as $index => $jam)
                                            @php
                                                $jadwalJam = $jadwalHari->where('jam_ke', $jam->urutan)->first();
                                            @endphp
                                            <tr>
                                                <!-- Hari (hanya tampil di baris pertama) -->
                                                @if($index === 0)
                                                    <td rowspan="{{ $jamHari->count() }}" style="border: 1px solid #000; padding: 4px; text-align: center; font-weight: bold; vertical-align: top;">{{ $hari }}</td>
                                                @endif
                                                
                                                <!-- Waktu -->
                                                <td style="border: 1px solid #000; padding: 4px; text-align: center; font-size: 8px;">
                                                    {{ $jam->waktu_mulai }} - {{ $jam->waktu_selesai }}
                                                </td>
                                                
                                                <!-- Jam Ke -->
                                                <td style="border: 1px solid #000; padding: 4px; text-align: center; font-weight: bold;">
                                                    @if($jam->jenis === 'KBM')
                                                        {{ $jam->urutan }}
                                                    @else
                                                        {{ $jam->jenis === 'ISTIRAHAT' ? 'IH' . ($jam->urutan - 3) : $jam->jenis }}
                                                    @endif
                                                </td>
                                                
                                                <!-- Kode Guru -->
                                                <td style="border: 1px solid #000; padding: 4px; text-align: center; font-weight: bold;">
                                                    @if($jam->jenis === 'KBM' && $jadwalJam)
                                                        {{ $jadwalJam->guru->nip ?? '-' }}
                                                    @endif
                                                </td>
                                                
                                                <!-- Mapel -->
                                                <td style="border: 1px solid #000; padding: 4px; text-align: left;">
                                                    @if($jam->jenis === 'KBM' && $jadwalJam)
                                                        {{ $jadwalJam->mataPelajaran->nama_mapel ?? '-' }}
                                                    @else
                                                        <em style="color: #666;">{{ $jam->jenis }}</em>
                                                    @endif
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            @endif
                        </td>
                    @endforeach
                </tr>

                <!-- Spacer -->
                <tr>
                    <td colspan="2" style="height: 15px;"></td>
                </tr>
                
                <!-- Baris 3: Jumat (full width) -->
                <tr>
                    @php
                        $hari = 'Jumat';
                        $jadwalHari = $jadwalByHari->get($hari, collect());
                        $jamHari = $jamBelajarByHari->get($hari, collect())->sortBy('urutan');
                    @endphp
                    <td colspan="2" style="width: 50%; vertical-align: top;">
                        @if($jamHari->count() > 0)
                            <table style="width: 50%; border-collapse: collapse; font-size: 9px;">
                                <!-- Header Hari -->
                                <thead>
                                    <tr>
                                        <th colspan="5" style="border: 1px solid #000; padding: 4px; text-align: center; font-weight: bold; background-color: #e8e8e8;">{{ strtoupper($hari) }}</th>
                                    </tr>
                                    <tr style="background-color: #f0f0f0;">
                                        <th style="border: 1px solid #000; padding: 4px; text-align: center; font-weight: bold; width: 15%;">Hari</th>
                                        <th style="border: 1px solid #000; padding: 4px; text-align: center; font-weight: bold; width: 25%;">Waktu</th>
                                        <th style="border: 1px solid #000; padding: 4px; text-align: center; font-weight: bold; width: 12%;">Jam Ke</th>
                                        <th style="border: 1px solid #000; padding: 4px; text-align: center; font-weight: bold; width: 18%;">KODE GURU</th>
                                        <th style="border: 1px solid #000; padding: 4px; text-align: center; font-weight: bold;">MAPEL</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($jamHari as $index => $jam)
                                        @php
                                            $jadwalJam = $jadwalHari->where('jam_ke', $jam->urutan)->first();
                                        @endphp
                                        <tr>
                                            <!-- Hari (hanya tampil di baris pertama) -->
                                            @if($index === 0)
                                                <td rowspan="{{ $jamHari->count() }}" style="border: 1px solid #000; padding: 4px; text-align: center; font-weight: bold; vertical-align: top;">{{ $hari }}</td>
                                            @endif
                                            
                                            <!-- Waktu -->
                                            <td style="border: 1px solid #000; padding: 4px; text-align: center; font-size: 8px;">
                                                {{ $jam->waktu_mulai }} - {{ $jam->waktu_selesai }}
                                            </td>
                                            
                                            <!-- Jam Ke -->
                                            <td style="border: 1px solid #000; padding: 4px; text-align: center; font-weight: bold;">
                                                @if($jam->jenis === 'KBM')
                                                    {{ $jam->urutan }}
                                                @else
                                                    {{ $jam->jenis === 'ISTIRAHAT' ? 'IH' . ($jam->urutan - 3) : $jam->jenis }}
                                                @endif
                                            </td>
                                            
                                            <!-- Kode Guru -->
                                            <td style="border: 1px solid #000; padding: 4px; text-align: center; font-weight: bold;">
                                                @if($jam->jenis === 'KBM' && $jadwalJam)
                                                    {{ $jadwalJam->guru->nip ?? '-' }}
                                                @endif
                                            </td>
                                            
                                            <!-- Mapel -->
                                            <td style="border: 1px solid #000; padding: 4px; text-align: left;">
                                                @if($jam->jenis === 'KBM' && $jadwalJam)
                                                    {{ $jadwalJam->mataPelajaran->nama_mapel ?? '-' }}
                                                @else
                                                    <em style="color: #666;">{{ $jam->jenis }}</em>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        @endif
                    </td>
                </tr>
            </tbody>
        </table>

        <!-- Garis Pemisah -->
        <div style="border-top: 2px solid #000; margin: 15px 0;"></div>

        <!-- Keterangan Guru -->
        <div style="margin-top: 10px;">
            <h4 style="margin: 0 0 8px 0; font-weight: bold; color: #000; font-size: 11px;">DAFTAR GURU PENGAJAR</h4>
            <table style="width: 100%; border-collapse: collapse; font-size: 9px;">
                <tbody>
                    @php
                        $guruChunks = $guruList->chunk(3);
                    @endphp
                    @foreach($guruChunks as $chunk)
                        <tr>
                            @foreach($chunk as $guru)
                                <td style="padding: 3px 6px; width: 33%; border-bottom: 1px solid #ddd;">
                                    <strong>{{ $guru->nip }}</strong> - {{ $guru->nama }}
                                </td>
                            @endforeach
                            @for($i = $chunk->count(); $i < 3; $i++)
                                <td style="padding: 3px 6px; width: 33%;"></td>
                            @endfor
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <!-- Footer -->
        <div style="margin-top: 15px; text-align: right; font-size: 9px; color: #333;">
            <p style="margin: 0;">Dicetak: {{ now()->format('d F Y, H:i') }} WIB</p>
        </div>

        </div>
    </div>
</div>

<style>
    /* Print Styles */
    @media print {
        * {
            margin: 0 !important;
            padding: 0 !important;
            box-shadow: none !important;
        }
        
        body {
            background: white !important;
            color: black !important;
            font-family: Arial, sans-serif !important;
            line-height: 1.3 !important;
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
            padding: 15px !important;
            background: white !important;
        }
        
        /* Make content fit A4 */
        #printContent {
            max-width: 210mm !important;
            height: auto !important;
            margin: 0 auto !important;
            padding: 0 !important;
        }
        
        /* Table styling for print */
        table {
            page-break-inside: avoid !important;
            border-collapse: collapse !important;
        }
        
        thead {
            display: table-header-group !important;
        }
        
        tr {
            page-break-inside: avoid !important;
        }
        
        img {
            max-width: 100% !important;
        }
        
        h1, h2, h3, h4, h5, h6 {
            page-break-after: avoid !important;
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
        
        .card-body {
            padding: 20px;
        }
    }
    
    /* General styles */
    .print-body {
        font-family: Arial, sans-serif;
        line-height: 1.3;
    }
</style>
@endsection
