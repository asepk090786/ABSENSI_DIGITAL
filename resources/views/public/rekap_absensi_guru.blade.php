<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Rekap Kehadiran Guru</title>
    <link href="https://cdn.jsdelivr.net/npm/@tabler/core@1.4.0/dist/css/tabler.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.4/dist/chart.umd.min.js"></script>
    <style>
        body { color: #172033; }
        .text-muted { color: #475569 !important; }
        .badge { font-weight: 700; }
        .badge.bg-success, .badge.bg-danger, .badge.bg-secondary { color: #fff !important; }
        .badge.bg-warning, .badge.bg-info { color: #172033 !important; }
        .table thead th { color: #172033; background: #e9eef5; font-weight: 700; }
        .table tbody td { color: #273449; }
        .attendance-chart { min-height: 310px; }
        .attendance-chart canvas { max-height: 210px; }
        .attendance-percentage { color: #172033; font-size: 1.15rem; font-weight: 800; text-align: center; }
    </style>
</head>
<body class="bg-light">
<main class="container py-4">
    <div class="d-flex justify-content-between align-items-center gap-3 mb-4 flex-wrap">
        <div><h1 class="h3 mb-1">Rekap Kehadiran Guru</h1><p class="text-muted mb-0">{{ $tanggal->translatedFormat('l, d F Y') }}</p></div>
        <div class="d-flex gap-2"><a class="btn btn-outline-primary" href="{{ route('public.rekap-absensi') }}">Rekap Siswa</a><a class="btn btn-outline-primary" href="{{ route('public.agenda-kelas') }}">Agenda Kelas</a></div>
    </div>

    <form class="row g-2 align-items-end mb-4" method="get">
        <div class="col-auto"><label class="form-label" for="tanggal">Tanggal</label><input class="form-control" type="date" id="tanggal" name="tanggal" value="{{ $tanggal->format('Y-m-d') }}"></div>
        <div class="col-auto"><button class="btn btn-primary" type="submit">Tampilkan</button></div>
    </form>

    <div class="d-flex flex-wrap gap-2 mb-4">
        <span class="badge bg-success p-2">Hadir: {{ $statusCounts['hadir'] ?? 0 }}</span>
        <span class="badge bg-danger p-2">Tidak Hadir: {{ $statusCounts['tidak_hadir'] ?? 0 }}</span>
        <span class="badge bg-info text-dark p-2">Izin: {{ $statusCounts['izin'] ?? 0 }}</span>
        <span class="badge bg-warning text-dark p-2">Sakit: {{ $statusCounts['sakit'] ?? 0 }}</span>
        <span class="badge bg-secondary p-2">Belum Dicatat: {{ $statusCounts['belum_dicatat'] ?? 0 }}</span>
    </div>

    @php
        $guruChartLabels = ['Hadir', 'Izin', 'Sakit', 'Tidak Hadir', 'Belum Dicatat'];
        $guruChartKeys = ['hadir', 'izin', 'sakit', 'tidak_hadir', 'belum_dicatat'];
        $totalGuruStatus = array_sum($statusCounts->toArray());
        $persentaseGuruHadir = $totalGuruStatus > 0 ? (($statusCounts['hadir'] ?? 0) / $totalGuruStatus) * 100 : 0;
    @endphp
    <section class="mb-4">
        <h2 class="h4 mb-3">Statistik Kehadiran Guru</h2>
        <div class="row g-3">
            <div class="col-12 col-sm-8 col-md-6 col-lg-4">
                <div class="card shadow-sm attendance-chart">
                    <div class="card-header"><h3 class="h5 mb-0">Semua Guru</h3></div>
                    <div class="card-body">
                        <canvas id="teacherAttendanceChart" aria-label="Grafik kehadiran semua guru"></canvas>
                        <div class="attendance-percentage">{{ number_format($persentaseGuruHadir, 2) }}% Hadir</div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    @if($guru->isEmpty())
        <div class="alert alert-info">Belum ada data guru aktif.</div>
    @else
        <div class="table-responsive bg-white shadow-sm rounded">
            <table class="table table-hover align-middle mb-0">
                <thead><tr><th>No</th><th>Nama Guru</th><th>NIP/Kode</th><th>Status Kehadiran</th><th>Keterangan</th></tr></thead>
                <tbody>
                @foreach($guru as $index => $item)
                    @php
                        $record = $absensi->get($item->id);
                        $status = strtolower((string) ($record->status ?? 'belum_dicatat'));
                        $label = $record ? ucwords(str_replace('_', ' ', $status)) : 'Belum Dicatat';
                        $badge = match ($status) {
                            'hadir' => 'bg-success',
                            'izin' => 'bg-info text-dark',
                            'sakit' => 'bg-warning text-dark',
                            'tidak_hadir' => 'bg-danger',
                            default => 'bg-secondary',
                        };
                    @endphp
                    <tr><td>{{ $index + 1 }}</td><td>{{ $item->nama }}</td><td>{{ $item->nip ?: ($item->kode_guru ?: '-') }}</td><td><span class="badge {{ $badge }}">{{ $label }}</span></td><td>{{ $record->keterangan ?? '-' }}</td></tr>
                @endforeach
                </tbody>
            </table>
        </div>
    @endif
</main>
<script>
    new Chart(document.getElementById('teacherAttendanceChart'), {
        type: 'doughnut',
        data: {
            labels: @json($guruChartLabels),
            datasets: [{
                data: @json(collect($guruChartKeys)->map(fn ($key) => (int) ($statusCounts[$key] ?? 0))->values()),
                backgroundColor: ['#198754', '#0d6efd', '#f59f00', '#dc3545', '#6c757d'],
                borderColor: '#fff',
                borderWidth: 2
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            cutout: '52%',
            plugins: { legend: { position: 'bottom', labels: { color: '#172033', padding: 12, usePointStyle: true } } }
        }
    });
</script>
</body>
</html>