<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rencana Tindak Lanjut - {{ $tindakLanjut->nama_siswa }}</title>
    <style>
        body { font-family: Arial, sans-serif; color: #000; margin: 24px 28px; font-size: 13px; }
        .school-header { margin-bottom: 2px; }
        .header-table { width: 100%; border-collapse: collapse; }
        .header-table, .header-table td { border: none !important; }
        .header-table td { vertical-align: top; padding: 0; }
        .school-header-side { width: 74px; text-align: center; }
        .school-header-center { text-align: center; }
        .school-header-line { margin: 0; }
        .school-header-center p,
        .school-header-center h1,
        .school-header-center h2,
        .school-header-center h3,
        .school-header-center h4,
        .school-header-center h5,
        .school-header-center h6 { margin: 0; }
        .school-logo { max-height: 64px; max-width: 64px; }
        .header-divider { border-top: 2px solid #111; margin: 2px 0 10px; }
        .doc-title { text-align: center; font-size: 18px; font-weight: 700; text-transform: uppercase; margin: 0 0 14px; }
        .meta-table { width: 100%; border-collapse: collapse; margin-bottom: 12px; }
        .meta-table td { padding: 3px 4px; vertical-align: top; }
        .meta-label { width: 160px; font-weight: 700; }
        .meta-sep { width: 12px; text-align: center; }
        table { width: 100%; border-collapse: collapse; }
        table, th, td { border: 1px solid #111; }
        th, td { padding: 6px 6px; vertical-align: top; font-size: 12px; }
        th { text-align: center; font-weight: 700; }
        .text-center { text-align: center; }
        .signatures-table { width: 100%; border-collapse: collapse; margin-top: 42px; }
        .signatures-table, .signatures-table td { border: none !important; }
        .signatures-table td { width: 50%; vertical-align: top; padding: 0; }
        .signature-title { min-height: 42px; }
        .signature-space { height: 58px; }
        .signature-name { font-weight: 700; text-decoration: underline; margin-bottom: 2px; }
        .btn-print { margin-bottom: 14px; display: inline-block; padding: 7px 12px; background: #111; color: #fff; text-decoration: none; border-radius: 4px; font-size: 12px; }
        .btn-pdf { margin-left: 8px; margin-bottom: 14px; display: inline-block; padding: 7px 12px; background: #0d6efd; color: #fff; text-decoration: none; border-radius: 4px; font-size: 12px; }
        @media print { .no-print { display: none !important; } body { margin: 10mm 12mm; } }
    </style>
</head>
<body>
    @php
        $buildLogoDataUri = function (?string $relativePath): ?string {
            if (empty($relativePath)) {
                return null;
            }

            $relativePath = ltrim($relativePath, '/');
            $candidatePaths = [
                public_path('storage/' . $relativePath),
                storage_path('app/public/' . $relativePath),
            ];

            $fullPath = null;
            foreach ($candidatePaths as $candidatePath) {
                if (is_file($candidatePath)) {
                    $fullPath = $candidatePath;
                    break;
                }
            }

            if (!$fullPath) {
                return null;
            }

            $extension = strtolower(pathinfo($fullPath, PATHINFO_EXTENSION));
            $mime = match ($extension) {
                'jpg', 'jpeg' => 'image/jpeg',
                'png' => 'image/png',
                'gif' => 'image/gif',
                'webp' => 'image/webp',
                'svg' => 'image/svg+xml',
                default => null,
            };

            if (!$mime) {
                return null;
            }

            $content = @file_get_contents($fullPath);
            if ($content === false) {
                return null;
            }

            return 'data:' . $mime . ';base64,' . base64_encode($content);
        };

        $logoKiriDataUri = $buildLogoDataUri($sekolah->logo_header_kiri ?? null);
        $logoKananDataUri = $buildLogoDataUri($sekolah->logo ?? null);
    @endphp

    <div class="no-print">
        <a href="#" class="btn-print" onclick="window.print(); return false;">Print</a>
        <a href="{{ route('guru_bk_layanan.tindak_lanjut.pdf', ['kelas' => $kelas->id, 'tindakLanjut' => $tindakLanjut->id]) }}" class="btn-pdf">Download PDF</a>
    </div>

    <div class="school-header">
        <table class="header-table">
            <tr>
                <td class="school-header-side">
                    @if(!empty($logoKiriDataUri))
                        <img src="{{ $logoKiriDataUri }}" alt="" class="school-logo">
                    @endif
                </td>
                <td class="school-header-center">
                    @if(!empty($sekolah?->header_html))
                        {!! $sekolah->header_html !!}
                    @else
                        @if(!empty($sekolah?->header_line1))<div class="school-header-line">{!! $sekolah->header_line1 !!}</div>@endif
                        @if(!empty($sekolah?->header_line2))<div class="school-header-line">{!! $sekolah->header_line2 !!}</div>@endif
                        @if(!empty($sekolah?->header_line3))<div class="school-header-line">{!! $sekolah->header_line3 !!}</div>@endif
                        @if(!empty($sekolah?->header_line4))<div class="school-header-line">{!! $sekolah->header_line4 !!}</div>@endif
                    @endif
                </td>
                <td class="school-header-side">
                    @if(!empty($logoKananDataUri))
                        <img src="{{ $logoKananDataUri }}" alt="" class="school-logo">
                    @endif
                </td>
            </tr>
        </table>
    </div>

    <div class="header-divider"></div>

    <p class="doc-title">RENCANA TINDAK LANJUT</p>

    <table class="meta-table">
        <tr>
            <td class="meta-label">Nama Siswa</td>
            <td class="meta-sep">:</td>
            <td>{{ $tindakLanjut->nama_siswa }}</td>
        </tr>
        <tr>
            <td class="meta-label">Kelas</td>
            <td class="meta-sep">:</td>
            <td>{{ $tindakLanjut->nama_kelas }}</td>
        </tr>
        <tr>
            <td class="meta-label">NIS / NISN</td>
            <td class="meta-sep">:</td>
            <td>{{ $tindakLanjut->nis ?: '-' }} / {{ $tindakLanjut->nisn ?: '-' }}</td>
        </tr>
        <tr>
            <td class="meta-label">Waktu</td>
            <td class="meta-sep">:</td>
            <td>{{ $tindakLanjut->waktu }}</td>
        </tr>
        <tr>
            <td class="meta-label">Nama Wali Kelas</td>
            <td class="meta-sep">:</td>
            <td>{{ $tindakLanjut->nama_wali_kelas ?: '-' }}</td>
        </tr>
        <tr>
            <td class="meta-label">Nama Guru BK</td>
            <td class="meta-sep">:</td>
            <td>{{ $tindakLanjut->nama_guru_bk ?: $guruBkNama }}</td>
        </tr>
    </table>

    <table>
        <thead>
            <tr>
                <th style="width:6%;">No</th>
                <th style="width:34%;">Rencana Kegiatan</th>
                <th style="width:30%;">Waktu & tempat</th>
                <th style="width:30%;">Pihak terkait</th>
            </tr>
        </thead>
        <tbody>
            @forelse(($tindakLanjut->rencana_items ?? []) as $item)
                <tr>
                    <td class="text-center">{{ $item['no'] ?? '-' }}</td>
                    <td>{{ $item['rencana_kegiatan'] ?? '-' }}</td>
                    <td>{{ $item['waktu_tempat'] ?? '-' }}</td>
                    <td>{{ $item['pihak_terkait'] ?? '-' }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="4" class="text-center">Tidak ada item rencana tindak lanjut.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <table class="signatures-table">
        <tr>
            <td>
                <div class="signature-title">Pontang, {{ $todayLabel }}<br>Guru BK</div>
                <div class="signature-space"></div>
                <div class="signature-name">{{ $guruBkNama }}</div>
                <div>NIP {{ $guruBkNip }}</div>
            </td>
            <td>
                <div class="signature-title">Pontang, {{ $todayLabel }}<br>Kepala Sekolah</div>
                <div class="signature-space"></div>
                <div class="signature-name">{{ $kepalaSekolahNama }}</div>
                <div>NIP {{ $kepalaSekolahNip }}</div>
            </td>
        </tr>
    </table>
</body>
</html>
