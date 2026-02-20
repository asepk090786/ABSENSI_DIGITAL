<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Pembinaan BK - {{ $sekolah->nama_sekolah ?? 'Sekolah' }}</title>
    <style>
        body { font-family: Arial, sans-serif; color: #000; margin: 24px 28px; font-size: 13px; }
        .school-header { margin-bottom: 10px; }
        .school-header-top { display: flex; align-items: center; justify-content: space-between; gap: 12px; }
        .school-header-side { width: 70px; min-width: 70px; height: 70px; display: flex; align-items: center; justify-content: center; }
        .school-header-center { flex: 1; text-align: center; }
        .school-header-line { margin: 0; }
        .school-logo { max-height: 70px; max-width: 70px; margin-bottom: 6px; }
        .header-divider { border-top: 2px solid #111; margin: 8px 0 12px; }
        .header { text-align: center; margin-bottom: 12px; }
        .header-title { font-weight: 700; font-size: 18px; text-transform: uppercase; margin: 0; }
        .header-subtitle { margin: 4px 0 0; font-size: 15px; font-weight: 700; }
        .info { margin: 8px 0 10px; font-size: 13px; }
        table { width: 100%; border-collapse: collapse; }
        table, th, td { border: 1px solid #111; }
        th, td { padding: 6px 6px; vertical-align: top; font-size: 12px; }
        th { text-align: center; font-weight: 700; }
        .text-center { text-align: center; }
        .signatures { display: flex; justify-content: space-between; margin-top: 40px; }
        .signature-col { width: 40%; }
        .signature-title { margin-bottom: 58px; }
        .signature-name { font-weight: 700; text-decoration: underline; margin-bottom: 2px; }
        .btn-print { margin-bottom: 14px; display: inline-block; padding: 7px 12px; background: #111; color: #fff; text-decoration: none; border-radius: 4px; font-size: 12px; }
        @media print { .no-print { display: none !important; } body { margin: 10mm 12mm; } }
    </style>
</head>
<body>
    <div class="no-print">
        <a href="#" class="btn-print" onclick="window.print(); return false;">Print</a>
    </div>

    <div class="school-header">
        <div class="school-header-top">
            <div class="school-header-side">
                @if(!empty($sekolah?->logo_header_kiri))
                    <img src="{{ asset('storage/' . $sekolah->logo_header_kiri) }}" alt="Logo Kiri" class="school-logo">
                @endif
            </div>
            <div class="school-header-center">
                @if(!empty($sekolah?->header_html))
                    {!! $sekolah->header_html !!}
                @else
                    @if(!empty($sekolah?->header_line1))<div class="school-header-line">{!! $sekolah->header_line1 !!}</div>@endif
                    @if(!empty($sekolah?->header_line2))<div class="school-header-line">{!! $sekolah->header_line2 !!}</div>@endif
                    @if(!empty($sekolah?->header_line3))<div class="school-header-line">{!! $sekolah->header_line3 !!}</div>@endif
                    @if(!empty($sekolah?->header_line4))<div class="school-header-line">{!! $sekolah->header_line4 !!}</div>@endif
                @endif
            </div>
            <div class="school-header-side">
                @if(!empty($sekolah?->logo))
                    <img src="{{ asset('storage/' . $sekolah->logo) }}" alt="Logo Kanan" class="school-logo">
                @endif
            </div>
        </div>
    </div>

    <div class="header-divider"></div>

    <div class="header">
        <p class="header-title">Laporan Pembinaan BK</p>
        <p class="header-subtitle">({{ $sekolah->nama_sekolah ?? 'Nama Sekolah' }})</p>
    </div>

    <div class="info">
        <strong>Kelas:</strong> {{ $kelas->nama_kelas }}
        @if($selectedSiswa)
            | <strong>Siswa:</strong> {{ $selectedSiswa->nama }}
        @endif
        @if(!empty($tanggalMulai) || !empty($tanggalSelesai))
            | <strong>Periode:</strong> {{ $tanggalMulai ? \Carbon\Carbon::parse($tanggalMulai)->format('d/m/Y') : '-' }} s/d {{ $tanggalSelesai ? \Carbon\Carbon::parse($tanggalSelesai)->format('d/m/Y') : '-' }}
        @endif
    </div>

    <table>
        <thead>
            <tr>
                <th style="width:5%;">No</th>
                <th style="width:14%;">Tanggal</th>
                <th style="width:16%;">Siswa</th>
                <th style="width:14%;">Rekap Absensi</th>
                <th style="width:17%;">Permasalahan</th>
                <th style="width:17%;">Penanganan</th>
                <th style="width:17%;">Tindak Lanjut</th>
            </tr>
        </thead>
        <tbody>
            @forelse($pembinaanItems as $index => $item)
                <tr>
                    <td class="text-center">{{ $index + 1 }}</td>
                    <td class="text-center">{{ $item->created_at?->format('d/m/Y') }}</td>
                    <td>{{ $item->siswa->nama ?? '-' }}</td>
                    <td>H:{{ $item->hadir }}, S:{{ $item->sakit }}, I:{{ $item->izin }}, A:{{ $item->alpa }}, T:{{ $item->terlambat }}</td>
                    <td>{{ $item->deskripsi_permasalahan }}</td>
                    <td>{{ $item->penanganan }}</td>
                    <td>{{ $item->tindak_lanjut ?? '-' }}</td>
                </tr>
            @empty
                <tr><td colspan="7" class="text-center">Tidak ada data pembinaan.</td></tr>
            @endforelse
        </tbody>
    </table>

    <div class="signatures">
        <div class="signature-col">
            <div class="signature-title">Guru BK</div>
            <div class="signature-name">{{ $guruBkNama }}</div>
            <div>NIP {{ $guruBkNip }}</div>
        </div>
        <div class="signature-col" style="text-align: left;">
            <div class="signature-title">Pontang, {{ $todayLabel }}<br>Kepala Sekolah</div>
            <div class="signature-name">{{ $kepalaSekolahNama }}</div>
            <div>NIP {{ $kepalaSekolahNip }}</div>
        </div>
    </div>
</body>
</html>
