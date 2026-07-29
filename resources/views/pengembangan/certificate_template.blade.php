<html>
<head>
    <style>
        body { font-family: DejaVu Sans, sans-serif; }
        .cert { border: 10px solid #ddd; padding: 40px; text-align: center; }
        .barcode { margin-top: 30px; font-size: 12px; }
    </style>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
</head>
<body>
    <div class="cert">
        <h1>Sertifikat</h1>
        <p>Diberikan kepada</p>
        <h2>{{ $name }}</h2>
        <p>Atas partisipasinya pada kegiatan:</p>
        <h3>{{ $kegiatan->nama_kegiatan }}</h3>
        <p><strong>Pemateri:</strong>
            @if(is_array($kegiatan->pemateri))
                {{ implode(', ', $kegiatan->pemateri) }}
            @else
                {{ $kegiatan->pemateri }}
            @endif
        </p>
        @if(!empty($nomor_surat))
            <p><strong>No. Sertifikat:</strong> {{ $nomor_surat }}</p>
        @endif
        <div class="barcode">Kode Verifikasi: {{ $barcode }}</div>
    </div>
</body>
</html>
