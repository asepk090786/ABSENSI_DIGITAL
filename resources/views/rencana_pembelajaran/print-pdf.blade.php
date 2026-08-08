<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Rencana Pembelajaran - {{ $rencanaPembelajaran->judul }}</title>
    <style>
        @page {
            size: A4 portrait;
            margin: 20mm;
        }
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        html, body {
            width: 100%;
            min-height: 100%;
            font-family: 'DejaVu Sans', Arial, sans-serif;
            color: #222;
            background: #fff;
            line-height: 1.5;
            font-size: 11pt;
        }
        body {
            padding: 0;
        }
        .document-body {
            width: 100%;
            margin: 0;
            padding: 0;
        }
        .document-body p {
            margin-bottom: 10px;
        }
        .document-body table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 12px;
        }
        .document-body table th,
        .document-body table td {
            border: 1px solid #333;
            padding: 6px 8px;
        }
        .document-body table th {
            background: #f7f7f7;
            font-weight: 700;
        }
        .document-body h1,
        .document-body h2,
        .document-body h3,
        .document-body h4 {
            margin: 18px 0 8px;
            font-family: 'Source Serif 4', Georgia, serif;
            font-weight: 700;
        }
        .document-body ul,
        .document-body ol {
            margin: 0 0 10px 20px;
            padding: 0;
        }
        .document-body li {
            margin-bottom: 6px;
        }
        .document-body strong,
        .document-body b {
            font-weight: 700;
        }
        .fallback-section {
            margin-bottom: 16px;
        }
        .fallback-section .section-header {
            display: block;
            margin-bottom: 8px;
            font-weight: 700;
            font-size: 12pt;
        }
        .fallback-section .content-block {
            margin-bottom: 12px;
        }
        .fallback-section .content-block p {
            margin-bottom: 10px;
        }
        .komponen-list {
            list-style: none;
            padding-left: 0;
        }
        .komponen-list li {
            margin-bottom: 6px;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            background: #fbfbfb;
        }
        .label-muted {
            color: #555;
            font-size: 9pt;
            margin-bottom: 6px;
            display: block;
        }
    </style>
        .field-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 18px;
        }
        .field-table th,
        .field-table td {
            padding: 8px 10px;
            border: 1px solid #d0d0d0;
            vertical-align: top;
        }
        .field-table th {
            width: 180px;
            background: #f7f7f7;
            text-align: left;
            font-weight: 700;
        }
        .section {
            margin-bottom: 16px;
            page-break-inside: avoid;
        }
        .section-header {
            display: block;
            background: #f7f8fa;
            border-radius: 4px;
            padding: 8px 10px;
            margin-bottom: 8px;
            font-weight: 700;
            font-size: 12pt;
        }
        .section-content {
            padding-left: 4px;
        }
        .content-block {
            margin-bottom: 12px;
            word-wrap: break-word;
        }
        .content-block p {
            margin-bottom: 10px;
        }
        .content-block ul,
        .content-block ol {
            margin: 0 0 10px 18px;
        }
        .content-block table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 12px;
        }
        .content-block table th,
        .content-block table td {
            border: 1px solid #d0d0d0;
            padding: 6px 8px;
        }
        .komponen-list {
            list-style: none;
            padding-left: 0;
        }
        .komponen-list li {
            margin-bottom: 6px;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            background: #fbfbfb;
        }
        .label-muted {
            color: #555;
            font-size: 9pt;
            margin-bottom: 6px;
            display: block;
        }
        .doc-kicker {
            margin-bottom: 20px;
            text-align: center;
            letter-spacing: .1em;
            color: #5b6770;
            font-size: 9pt;
            font-weight: 700;
        }
        .document-body,
        .document-page p,
        .document-page li,
        .document-page td,
        .document-page th {
            font-family: 'Source Serif 4', Georgia, serif;
            font-size: 11pt;
            line-height: 1.7;
        }
        .document-page h2,
        .document-page h3,
        .document-page h4 {
            margin-top: 18px;
            margin-bottom: 8px;
            font-family: 'Source Serif 4', Georgia, serif;
            font-weight: 700;
        }
        @media print {
            body {
                background: white;
                padding: 0;
            }
            .document-page {
                box-shadow: none;
                margin: 0;
                width: auto;
                min-height: auto;
                page-break-after: always;
            }
        }
    </style>
</head>
<body>
        @if(!empty($rencanaPembelajaran->html_content))
            @php
                $htmlContent = $rencanaPembelajaran->html_content;
                $htmlContent = preg_replace('/<\s*html[^>]*>/i', '', $htmlContent);
                $htmlContent = preg_replace('/<\s*\/\s*html\s*>/i', '', $htmlContent);
                $htmlContent = preg_replace('/<\s*head[^>]*>.*?<\s*\/\s*head\s*>/is', '', $htmlContent);
                $htmlContent = preg_replace('/<\s*body[^>]*>/i', '', $htmlContent);
                $htmlContent = preg_replace('/<\s*\/\s*body\s*>/i', '', $htmlContent);
                $htmlContent = trim($htmlContent);
                $htmlContent = preg_replace('/<\s*div[^>]*class="office-shell"[^>]*>/i', '', $htmlContent);
                $htmlContent = preg_replace('/<\s*header[^>]*>.*?<\s*\/\s*header\s*>/is', '', $htmlContent);
                $htmlContent = preg_replace('/<\s*nav[^>]*>.*?<\s*\/\s*nav\s*>/is', '', $htmlContent);
                $htmlContent = preg_replace('/<\s*main[^>]*>.*?<\s*\/\s*main\s*>/is', '$1', $htmlContent);
                $htmlContent = preg_replace('/<\s*footer[^>]*>.*?<\s*\/\s*footer\s*>/is', '', $htmlContent);
                $htmlContent = trim($htmlContent);
            @endphp
            <div class="document-body">
                {!! $htmlContent !!}
            </div>
        @else
            <div class="document-body">
                <p><strong>Judul:</strong> {{ $rencanaPembelajaran->judul }}</p>
                <p><strong>Mata Pelajaran:</strong> {{ $rencanaPembelajaran->mataPelajaran->nama_mapel }}</p>
                <p><strong>Kelas:</strong> {{ $rencanaPembelajaran->kelas->nama_kelas }}</p>
                <p><strong>Status:</strong> {{ ucfirst($rencanaPembelajaran->status) }}</p>
                @if($rencanaPembelajaran->tanggal_mulai || $rencanaPembelajaran->tanggal_selesai)
                    <p><strong>Periode:</strong>
                        @if($rencanaPembelajaran->tanggal_mulai)
                            {{ $rencanaPembelajaran->tanggal_mulai->format('d/m/Y') }}
                            @if($rencanaPembelajaran->tanggal_selesai)
                                - {{ $rencanaPembelajaran->tanggal_selesai->format('d/m/Y') }}
                            @endif
                        @else
                            -
                        @endif
                    </p>
                @endif

                @foreach([
                'capaian_pembelajaran' => 'Capaian Pembelajaran',
                'tujuan' => 'Tujuan Pembelajaran',
                'metode' => 'Metode Pembelajaran',
                'media' => 'Media Pembelajaran',
                'sumber' => 'Sumber Belajar',
                'praktik_pedagogis' => 'Praktik Pedagogis',
                'lingkungan_pembelajaran' => 'Lingkungan Pembelajaran',
                'pemanfaatan_digital' => 'Pemanfaatan Digital',
                'pengalaman_pembelajaran' => 'Pengalaman Pembelajaran',
                'refleksi_pembelajaran' => 'Refleksi Pembelajaran',
                'penilaian' => 'Asesmen',
            ] as $field => $title)
                @if(!empty($rencanaPembelajaran->{$field}))
                    <div class="section">
                        <span class="section-header">{{ $title }}</span>
                        <div class="content-block">
                            @php
                                $fieldValue = $rencanaPembelajaran->{$field};
                                $hasHtmlTags = preg_match('/<\s*(table|ul|ol|p|br|div|span|h[1-6]|strong|em|b|i|u)[^>]*>/i', $fieldValue);
                            @endphp
                            @if($hasHtmlTags)
                                {!! $fieldValue !!}
                            @else
                                {!! nl2br(e($fieldValue)) !!}
                            @endif
                        </div>
                    </div>
                @endif
            @endforeach
        @endif

        @if($rencanaPembelajaran->komponenNilai->count() > 0)
            <div class="section">
                <span class="section-header">Komponen Penilaian</span>
                <div class="komponen-list">
                    @foreach($rencanaPembelajaran->komponenNilai as $komponen)
                        <div class="content-block">
                            <strong>{{ $komponen->nama_komponen }}</strong>
                            @if($komponen->bobot)
                                <div>Bobot: {{ $komponen->bobot }}%</div>
                            @endif
                        </div>
                    @endforeach
                </div>
            @endforeach
        @endif
    </div>
</body>
</html>
