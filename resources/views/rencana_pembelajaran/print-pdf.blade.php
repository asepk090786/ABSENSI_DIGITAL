<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Rencana Pembelajaran - {{ $rencanaPembelajaran->judul }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: 'DejaVu Sans', Arial, sans-serif;
            color: #222;
            background: white;
            line-height: 1.5;
            padding: 18px;
            font-size: 10pt;
        }
        h1 {
            font-size: 16pt;
            margin-bottom: 6px;
        }
        h2 {
            font-size: 12pt;
            margin-bottom: 4px;
        }
        h3 {
            font-size: 11pt;
            margin-bottom: 4px;
        }
        .section {
            margin-bottom: 14px;
        }
        .section-header {
            display: block;
            background: #f0f0f0;
            border-radius: 4px;
            padding: 6px 10px;
            margin-bottom: 8px;
            font-weight: bold;
            font-size: 11pt;
        }
        .section-content {
            padding-left: 8px;
        }
        .field-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 12px;
        }
        .field-table th,
        .field-table td {
            padding: 8px 10px;
            border: 1px solid #d0d0d0;
            vertical-align: top;
        }
        .field-table th {
            width: 200px;
            background: #fafafa;
            text-align: left;
        }
        .content-block {
            margin-bottom: 10px;
        }
        .content-block p {
            margin-bottom: 8px;
        }
        .komponen-list {
            list-style: none;
            padding-left: 0;
        }
        .komponen-list li {
            margin-bottom: 4px;
            padding: 6px 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            background: #fbfbfb;
        }
        .label-muted {
            color: #555;
            font-size: 9pt;
            margin-bottom: 4px;
            display: block;
        }
    </style>
</head>
<body>
    <h1>Rencana Pembelajaran</h1>
    <p style="margin-bottom: 16px; color:#555;">{{ $rencanaPembelajaran->mataPelajaran->nama_mapel }} - {{ $rencanaPembelajaran->kelas->nama_kelas }}</p>

    <table class="field-table">
        <tr>
            <th>Judul</th>
            <td>{{ $rencanaPembelajaran->judul }}</td>
        </tr>
        <tr>
            <th>Mata Pelajaran</th>
            <td>{{ $rencanaPembelajaran->mataPelajaran->nama_mapel }}</td>
        </tr>
        <tr>
            <th>Kelas</th>
            <td>{{ $rencanaPembelajaran->kelas->nama_kelas }}</td>
        </tr>
        <tr>
            <th>Status</th>
            <td>{{ ucfirst($rencanaPembelajaran->status) }}</td>
        </tr>
        @if($rencanaPembelajaran->tanggal_mulai || $rencanaPembelajaran->tanggal_selesai)
            <tr>
                <th>Periode</th>
                <td>
                    @if($rencanaPembelajaran->tanggal_mulai)
                        {{ $rencanaPembelajaran->tanggal_mulai->format('d/m/Y') }}
                        @if($rencanaPembelajaran->tanggal_selesai)
                            - {{ $rencanaPembelajaran->tanggal_selesai->format('d/m/Y') }}
                        @endif
                    @else
                        -
                    @endif
                </td>
            </tr>
        @endif
    </table>

    @php
        $sections = [
            'capaian_pembelajaran' => ['title' => 'Capaian Pembelajaran', 'hint' => 'Tuliskan capaian pembelajaran sesuai ketentuan.'],
            'tujuan' => ['title' => 'Tujuan Pembelajaran', 'hint' => 'Tuliskan tujuan pembelajaran yang mengacu pada capaian.'],
            'metode' => ['title' => 'Metode Pembelajaran', 'hint' => 'Jelaskan metode yang digunakan.'],
            'media' => ['title' => 'Media Pembelajaran', 'hint' => 'Cantumkan media pembelajaran yang dipakai.'],
            'sumber' => ['title' => 'Sumber Belajar', 'hint' => 'Cantumkan sumber referensi atau bahan ajar.'],
            'praktik_pedagogis' => ['title' => 'Praktik Pedagogis', 'hint' => 'Jelaskan praktik atau pendekatan pedagogis.'],
            'lingkungan_pembelajaran' => ['title' => 'Lingkungan Pembelajaran', 'hint' => 'Jelaskan lingkungan fisik atau virtual pembelajaran.'],
            'pemanfaatan_digital' => ['title' => 'Pemanfaatan Digital', 'hint' => 'Jelaskan pemanfaatan sumber digital.'],
            'pengalaman_pembelajaran' => ['title' => 'Pengalaman Pembelajaran', 'hint' => 'Jelaskan rangkaian kegiatan pembelajaran.'],
            'refleksi_pembelajaran' => ['title' => 'Refleksi Pembelajaran', 'hint' => 'Tuliskan refleksi pembelajaran jika ada.'],
            'penilaian' => ['title' => 'Asesmen', 'hint' => 'Tuliskan bentuk dan kriteria asesmen.'],
        ];
    @endphp

    @if(!empty($rencanaPembelajaran->html_content))
        <div class="section">
            <span class="section-header">Preview Dokumen HTML</span>
            <div class="section-content">
                <div class="content-block">{!! $rencanaPembelajaran->html_content !!}</div>
            </div>
        </div>
    @else
        @foreach($sections as $field => $section)
            @if(!empty($rencanaPembelajaran->{$field}))
                @php
                    $fieldValue = $rencanaPembelajaran->{$field};
                    $hasHtmlTags = preg_match('/<\s*(table|ul|ol|p|br|div|span|h[1-6]|strong|em|b|i|u)[^>]*>/i', $fieldValue);
                @endphp
                <div class="section">
                    <span class="section-header">{{ $section['title'] }}</span>
                    <div class="section-content">
                        <span class="label-muted">{{ $section['hint'] }}</span>
                        <div class="content-block">
                            @if($hasHtmlTags)
                                {!! $fieldValue !!}
                            @else
                                {!! nl2br(e($fieldValue)) !!}
                            @endif
                        </div>
                    </div>
                </div>
            @endif
        @endforeach
    @endif

    @if($rencanaPembelajaran->komponenNilai->count() > 0)
        <div class="section">
            <span class="section-header">Komponen Penilaian</span>
            <div class="section-content">
                <ul class="komponen-list">
                    @foreach($rencanaPembelajaran->komponenNilai as $komponen)
                        <li>
                            <strong>{{ $komponen->nama_komponen }}</strong>
                            @if($komponen->bobot)
                                <div>Bobot: {{ $komponen->bobot }}%</div>
                            @endif
                        </li>
                    @endforeach
                </ul>
            </div>
        </div>
    @endif
</body>
</html>
