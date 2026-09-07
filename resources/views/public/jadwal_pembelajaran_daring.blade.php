<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Jadwal Pembelajaran Daring</title>
    <link href="https://cdn.jsdelivr.net/npm/@tabler/core@1.4.0/dist/css/tabler.min.css" rel="stylesheet">
    <style>
        body { color: #172033; }
        .text-muted { color: #475569 !important; }
        .table thead th { color: #172033; background: #e9eef5; font-weight: 700; }
        .table tbody td { color: #273449; }
        .text-muted { color: #475569 !important; }
    </style>
</head>
<body class="bg-light">
<main class="container py-4">
    <div class="d-flex justify-content-between align-items-center gap-3 mb-4 flex-wrap">
        <div><h1 class="h3 mb-1">Jadwal Pembelajaran Daring</h1><p class="text-muted mb-0">Jadwal KBM aktif per kelas · {{ $tanggal->translatedFormat('l, d F Y') }}</p></div>
        <div class="d-flex gap-2"><a class="btn btn-outline-primary" href="{{ route('public.agenda-kelas') }}">Agenda Kelas</a><a class="btn btn-outline-primary" href="{{ route('public.rekap-absensi') }}">Rekap Absensi</a></div>
    </div>

    <form class="row g-2 align-items-end mb-4" method="get">
        <div class="col-auto"><label class="form-label" for="tanggal">Tanggal</label><input class="form-control" type="date" id="tanggal" name="tanggal" value="{{ $tanggal->format('Y-m-d') }}"></div>
        <div class="col-auto"><button class="btn btn-primary" type="submit">Tampilkan</button></div>
    </form>

    @if(!$tahun || !$semester)
        <div class="alert alert-warning">Tahun ajaran atau semester aktif belum tersedia.</div>
    @elseif($jadwalPerKelas->isEmpty())
        <div class="alert alert-info">Tidak ada jadwal KBM aktif pada tanggal ini.</div>
    @else
        @foreach($jadwalPerKelas as $namaKelas => $items)
            <section class="card shadow-sm mb-4">
                <div class="card-header"><h2 class="h5 mb-0">{{ $namaKelas }}</h2></div>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead><tr><th>No</th><th>Waktu</th><th>Jam Ke</th><th>Nama Guru</th><th>Mata Pelajaran</th><th>Link Daring</th></tr></thead>
                        <tbody>
                        @foreach($items as $index => $item)
                            @php
                                $agendaItem = $agenda->get($item->kelas_id . '-' . $item->guru_id . '-' . $item->jam_belajar_id);
                            @endphp
                            <tr>
                                <td>{{ $index + 1 }}</td>
                                <td>{{ $item->jamBelajar->jam_mulai ?? '-' }} - {{ $item->jamBelajar->jam_selesai ?? '-' }}</td>
                                <td>{{ $item->jam_ke ?? ($item->jamBelajar->urutan ?? '-') }}</td>
                                <td>{{ $item->guru->nama ?? '-' }}</td>
                                <td>{{ $item->mataPelajaran->nama_mapel ?? '-' }}</td>
                                <td>
                                    @if($agendaItem?->link_pembelajaran_daring)
                                        <a href="{{ $agendaItem->link_pembelajaran_daring }}" target="_blank" rel="noopener noreferrer" class="btn btn-sm btn-primary">{{ $agendaItem->platform_pembelajaran_daring ?: 'Buka Link' }}</a>
                                    @else
                                        <span class="text-muted">Belum tersedia</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
            </section>
        @endforeach
    @endif
</main>
</body>
</html>