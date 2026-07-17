<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Preview Agenda Kelas</title>
    <style>
        * {
            margin: 0;
            padding: 0;
        }
        
        body {
            font-family: 'Calibri', 'Arial', sans-serif;
            font-size: 11px;
            line-height: 1.4;
        }
        
        .header {
            text-align: center;
            margin-bottom: 20px;
            border-bottom: 2px solid #000;
            padding-bottom: 10px;
        }
        
        .header h2 {
            font-size: 16px;
            margin-bottom: 5px;
        }
        
        .header .info {
            font-size: 11px;
            margin: 3px 0;
        }
        
        .section-title {
            background-color: #4472c4;
            color: white;
            padding: 8px;
            margin-top: 15px;
            margin-bottom: 10px;
            font-weight: bold;
            font-size: 12px;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 10px;
        }
        
        table th {
            background-color: #e7e6e6;
            padding: 8px;
            text-align: left;
            border: 1px solid #999;
            font-weight: bold;
            font-size: 10px;
        }
        
        table td {
            padding: 8px;
            border: 1px solid #ccc;
            vertical-align: top;
        }
        
        table tr:nth-child(even) {
            background-color: #f9f9f9;
        }
        
        .col-date {
            width: 15%;
            font-weight: bold;
        }
        
        .col-jam {
            width: 12%;
            font-size: 10px;
        }
        
        .col-materi {
            width: 73%;
        }
        
        .no-data {
            text-align: center;
            padding: 20px;
            color: #666;
        }
        
        .page-break {
            page-break-after: always;
        }
        
        .signature-section {
            margin-top: 40px;
            page-break-inside: avoid;
        }
        
        .signature-top-row {
            width: 100%;
            margin-top: 30px;
            margin-bottom: 80px;
        }
        
        .signature-top-row::after {
            content: '';
            display: table;
            clear: both;
        }
        
        .signature-box {
            width: 45%;
            text-align: center;
            float: left;
            margin-right: 10%;
        }
        
        .signature-box:last-child {
            margin-right: 0;
        }
        
        .signature-box-center {
            width: 45%;
            text-align: center;
            margin: 0 auto;
            margin-top: 30px;
            clear: both;
        }
        
        .signature-line {
            margin-top: 5px;
            border-bottom: 1px solid #000;
            margin-bottom: 50px;
            width: 100%;
            height: 30px;
        }
        
        .signature-name {
            font-size: 11px;
            font-weight: bold;
            margin-top: 2px;
            text-decoration: underline;
            margin-bottom: 3px;
        }
        
        .signature-nip {
            font-size: 9px;
            color: #666;
            margin-top: 5px;
        }
        
        .signature-title {
            font-size: 10px;
            color: #666;
            margin-bottom: 3px;
        }

        .kepala-sekolah-model {
            width: 100%;
            text-align: center;
            margin-top: 50px;
        }

        .kepala-sekolah-title {
            font-size: 11px;
            color: #000;
            font-weight: bold;
            margin-bottom: 5px;
        }

        .kepala-sekolah-sekolah {
            font-size: 11px;
            color: #000;
            font-weight: bold;
            margin-bottom: 3px;
        }

        .kepala-sekolah-signature-space {
            height: 30px;
            margin: 20px 0 10px 0;
        }}

        .kepala-sekolah-nama {
            font-size: 11px;
            color: #000;
            font-weight: bold;
            text-decoration: underline;
            margin-top: 5px;
            margin-bottom: 3px;
        }

        .kepala-sekolah-nip {
            font-size: 9px;
            color: #000;
            font-weight: bold;
        }

        .wali-kelas-model {
            width: 45%;
            text-align: center;
            float: left;
            margin-right: 10%;
        }

        .wali-kelas-title {
            font-size: 11px;
            color: #000;
            font-weight: bold;
            margin-bottom: 2px;
            line-height: 1.2;
        }

        .wali-kelas-kelas {
            font-size: 10px;
            color: #000;
            font-weight: bold;
            margin-bottom: 2px;
            margin-top: 2px;
            line-height: 1.2;
        }

        .wali-kelas-signature-space {
            height: 30px;
            margin: 8px 0 8px 0;
        }

        .wali-kelas-nama {
            font-size: 11px;
            color: #000;
            font-weight: bold;
            text-decoration: underline;
            margin-top: 3px;
            margin-bottom: 2px;
            line-height: 1.2;
        }
        }

        .wali-kelas-nip {
            font-size: 9px;
            color: #000;
            font-weight: bold;
        }

        .guru-model {
            width: 45%;
            text-align: center;
            float: left;
        }

        .guru-title {
            font-size: 11px;
            color: #000;
            font-weight: bold;
            margin-bottom: 2px;
            line-height: 1.2;
            padding-top: 16px;
        }

        .guru-signature-space {
            height: 30px;
            margin: 8px 0 8px 0;
        }

        .guru-nama {
            font-size: 11px;
            color: #000;
            font-weight: bold;
            text-decoration: underline;
            margin-top: 3px;
            margin-bottom: 2px;
            line-height: 1.2;
        }
        }

        .guru-nip {
            font-size: 9px;
            color: #000;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <div class="header">
        <h2>AGENDA PEMBELAJARAN KELAS</h2>
        <div class="info"><strong>Kelas:</strong> {{ $kelas->nama_kelas }}</div>
        <div class="info"><strong>Guru Pengajar:</strong> {{ $guru->nama }}</div>
        <div class="info"><strong>Tahun Ajaran:</strong> {{ $tahunAjaran->nama_tahun }} | <strong>Semester:</strong> {{ $semester->nama_semester }}</div>
        <div class="info"><strong>Tanggal Cetak:</strong> {{ \Carbon\Carbon::now()->locale('id')->translatedFormat('d F Y H:i') }}</div>
    </div>

    @if($agendas->isEmpty())
        <div class="no-data">
            Belum ada agenda untuk kelas ini pada tahun ajaran yang aktif.
        </div>
    @else
        <div class="section-title">
            <i>Daftar Agenda Pembelajaran</i>
        </div>
        
        <table>
            <thead>
                <tr>
                    <th class="col-date">Tanggal</th>
                    <th class="col-jam">Jam KBM</th>
                    <th class="col-materi">Materi / Kegiatan</th>
                </tr>
            </thead>
            <tbody>
                @forelse($agendas as $agenda)
                    <tr>
                        <td class="col-date">
                            {{ \Carbon\Carbon::parse($agenda->tanggal)->locale('id')->translatedFormat('d/m/Y (D)') }}
                        </td>
                        <td class="col-jam">
                            {{ $agenda->jamBelajar->jam_mulai ?? '-' }}<br>
                            <small>(Jam Ke-{{ $agenda->jamBelajar->urutan ?? '-' }})</small>
                        </td>
                        <td class="col-materi">
                            {!! nl2br(strip_tags($agenda->kegiatan)) ?? '<em>-</em>' !!}
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="3" class="no-data">
                            Tidak ada agenda untuk periode ini
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        @if($agendas->count() > 0)
            <div class="signature-section">
                <!-- Row 1: Wali Kelas dan Guru -->
                <div class="signature-top-row">
                    <!-- Wali Kelas Model -->
                    <div class="wali-kelas-model">
                        <div class="wali-kelas-title">Wali Kelas</div>
                        <div class="wali-kelas-kelas">{{ $kelas->nama_kelas }}</div>
                        
                        <div class="wali-kelas-signature-space"></div>
                        
                        <div class="wali-kelas-nama">
                            @if(optional($sekolah)->tampilkan_nama_wali_kelas !== false)
                                @if($waliKelas)
                                    {{ $waliKelas->nama }}
                                @else
                                    <span style="color: #999;">(----)</span>
                                @endif
                            @else
                                {!! optional($sekolah)->wali_kelas_hidden_message ?: '<span style="color: #999;">Nama wali kelas disembunyikan oleh administrator.</span>' !!}
                            @endif
                        </div>
                        <div class="wali-kelas-nip">
                            @if(optional($sekolah)->tampilkan_nama_wali_kelas !== false && $waliKelas && $waliKelas->nip)
                                NIP. {{ $waliKelas->nip }}
                            @endif
                        </div>
                    </div>
                    
                    <!-- Guru Mata Pelajaran Model -->
                    <div class="guru-model">
                        <div class="guru-title">Guru Mata Pelajaran</div>
                        
                        <div class="guru-signature-space"></div>
                        
                        <div class="guru-nama">
                            {{ $guru->nama }}
                        </div>
                        <div class="guru-nip">
                            @if($guru->nip)
                                NIP. {{ $guru->nip }}
                            @endif
                        </div>
                    </div>
                </div>
                
                <!-- Row 2: Kepala Sekolah (Model Signature) -->
                <div class="kepala-sekolah-model">
                    <div class="kepala-sekolah-title">Kepala Sekolah</div>
                    <div class="kepala-sekolah-sekolah">{{ $sekolah->nama_sekolah ?? 'SMA Negeri 1 Sleman' }}</div>
                    
                    <div class="kepala-sekolah-signature-space"></div>
                    
                    <div class="kepala-sekolah-nama">
                        @if($kepalaSekolah)
                            {{ $kepalaSekolah->nama }}
                        @else
                            <span style="color: #999;">(----)</span>
                        @endif
                    </div>
                    <div class="kepala-sekolah-nip">
                        @if($kepalaSekolah && $kepalaSekolah->nip)
                            NIP. {{ $kepalaSekolah->nip }}
                        @endif
                    </div>
                </div>
                
                <div style="margin-top: 30px; padding-top: 10px; border-top: 1px solid #ccc; font-size: 9px; color: #999; text-align: center; clear: both;">
                    <p style="margin: 3px 0;">Dicetak oleh: {{ auth()->user()->name }} | {{ \Carbon\Carbon::now()->locale('id')->translatedFormat('d F Y H:i') }}</p>
                    <p style="margin: 3px 0;">Sistem Manajemen Pembelajaran - Absensi Digital</p>
                </div>
            </div>
        @endif
    @endif
</body>
</html>
