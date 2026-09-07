<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Agenda Kelas Semua Kelas</title>
    <link href="https://cdn.jsdelivr.net/npm/@tabler/core@1.4.0/dist/css/tabler.min.css" rel="stylesheet">
    <style>
        body { color: #172033; }
        .text-muted { color: #475569 !important; }
        .table thead th { color: #172033; background: #e9eef5; font-weight: 700; }
        .table tbody td { color: #273449; }
    </style>
</head>
<body class="bg-light">
<main class="container py-4">
    <div class="d-flex justify-content-between align-items-center gap-3 mb-4 flex-wrap">
        <div><h1 class="h3 mb-1">Agenda Kelas Semua Kelas</h1><p class="text-muted mb-0">Data agenda semester aktif</p></div>
        <a class="btn btn-outline-primary" href="{{ route('public.rekap-absensi') }}">Rekap Absensi</a>
    </div>
    <form class="row g-2 align-items-end mb-4" method="get">
        <div class="col-auto"><label class="form-label" for="tanggal">Tanggal agenda</label><input class="form-control" type="date" id="tanggal" name="tanggal" value="{{ request('tanggal') }}"></div>
        <div class="col-auto"><button class="btn btn-primary" type="submit">Tampilkan</button></div>
        <div class="col-auto"><a class="btn btn-outline-secondary" href="{{ route('public.agenda-kelas') }}">Semua tanggal</a></div>
    </form>
    @if(!$tahun || !$semester)
        <div class="alert alert-warning">Tahun ajaran atau semester aktif belum tersedia.</div>
    @elseif($agenda->isEmpty())
        <div class="alert alert-info">Belum ada agenda kelas{{ request('tanggal') ? ' pada tanggal ini' : '' }}.</div>
    @else
        <div class="table-responsive bg-white shadow-sm rounded">
            <table class="table table-hover align-middle mb-0">
                <thead><tr><th>Tanggal</th><th>Kelas</th><th>Guru</th><th>Jam</th><th>Kegiatan</th><th>Pembelajaran Daring</th><th>Tujuan</th></tr></thead>
                <tbody>
                @foreach($agenda as $item)
                    @php
                        $kegiatan = trim(strip_tags(html_entity_decode((string) ($item->nama_kegiatan ?: $item->kegiatan ?: ''))));
                        $tujuan = trim(strip_tags(html_entity_decode((string) ($item->tujuan_pembelajaran ?: ''))));
                    @endphp
                    <tr><td>{{ $item->tanggal?->format('d/m/Y') }}</td><td>{{ $item->kelas->nama_kelas ?? '-' }}</td><td>{{ $item->guru->nama ?? '-' }}</td><td>{{ $item->jamBelajar->jam_ke ?? '-' }}</td><td>{{ $kegiatan ?: '-' }}</td><td>@if($item->link_pembelajaran_daring)<a href="{{ $item->link_pembelajaran_daring }}" target="_blank" rel="noopener noreferrer">{{ $item->platform_pembelajaran_daring ?: 'Buka link' }}</a>@else - @endif</td><td>{{ $tujuan ?: '-' }}</td></tr>
                @endforeach
                </tbody>
            </table>
        </div>
    @endif
    <section class="card shadow-sm mt-4"><div class="card-header"><h2 class="h5 mb-0">Guru dalam agenda</h2></div><div class="card-body"><div class="row row-cols-1 row-cols-md-3 g-2">
        @foreach($guru as $item)<div class="col"><span class="d-block border rounded p-2">{{ $item->nama }}</span></div>@endforeach
    </div></div></section>
</main>
</body>
</html>