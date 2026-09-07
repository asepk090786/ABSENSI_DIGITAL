<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Rekap Absensi {{ $tanggal->format('d/m/Y') }}</title>
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
            .attendance-chart { min-height: 290px; }
            .attendance-chart canvas { max-height: 190px; }
            .attendance-percentage { color: #172033; font-size: 1.15rem; font-weight: 800; text-align: center; }
            .attendance-modal { display: none; position: fixed; inset: 0; z-index: 1050; padding: 1rem; background: rgba(15, 23, 42, .6); overflow-y: auto; }
            .attendance-modal.is-open { display: flex; align-items: center; justify-content: center; }
            .attendance-modal-dialog { width: min(100%, 900px); max-height: calc(100vh - 2rem); overflow: hidden; background: #fff; border-radius: .5rem; box-shadow: 0 1rem 3rem rgba(15, 23, 42, .3); }
            .attendance-modal-body { max-height: calc(100vh - 10rem); overflow-y: auto; }
            body.modal-open { overflow: hidden; }
    </style>
</head>
<body class="bg-light">
<main class="container py-4">
    <div class="d-flex justify-content-between align-items-center gap-3 mb-4 flex-wrap">
        <div>
            <h1 class="h3 mb-1">Rekap Absensi Hari Ini</h1>
            <p class="text-muted mb-0">{{ $tanggal->translatedFormat('l, d F Y') }}</p>
        </div>
        <div class="d-flex gap-2"><a class="btn btn-outline-primary" href="{{ route('public.agenda-kelas') }}">Agenda Kelas</a><a class="btn btn-outline-primary" href="{{ route('public.rekap-absensi-guru') }}">Rekap Guru</a></div>
    </div>

    <form method="get" class="row g-2 align-items-end mb-4">
        <div class="col-auto">
            <label for="tanggal" class="form-label fw-semibold">Pilih Tanggal Rekap</label>
            <input type="date" id="tanggal" name="tanggal" class="form-control" value="{{ $tanggal->format('Y-m-d') }}">
        </div>
        <div class="col-auto"><button type="submit" class="btn btn-primary">Tampilkan</button></div>
        <div class="col-auto"><a href="{{ route('public.rekap-absensi') }}" class="btn btn-outline-secondary">Hari Ini</a></div>
    </form>

    @if(!$tahun || !$semester)
        <div class="alert alert-warning">Tahun ajaran atau semester aktif belum tersedia.</div>
    @else
        <section class="mb-4">
            <h2 class="h4 mb-3">Statistik Kehadiran</h2>
            <div class="row g-3">
                @foreach($statistikKehadiran as $judul => $statistik)
                    <div class="col-12 col-sm-6 col-xl-3">
                        <div class="card shadow-sm h-100 attendance-chart">
                            <div class="card-header"><h3 class="h5 mb-0">{{ $judul }}</h3></div>
                            <div class="card-body">
                                <canvas id="attendanceChart{{ $loop->index }}" aria-label="Grafik {{ $judul }}"></canvas>
                                @php
                                    $totalStatistik = array_sum($statistik);
                                    $persentaseHadir = $totalStatistik > 0 ? ($statistik['hadir'] / $totalStatistik) * 100 : 0;
                                @endphp
                                <div class="attendance-percentage">{{ number_format($persentaseHadir, 2) }}% Hadir</div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </section>
        @if($rekapPerKelas->isEmpty())
            <div class="alert alert-info">Belum ada data absensi untuk hari ini.</div>
        @else
        <div class="table-responsive bg-white shadow-sm rounded">
            <table class="table table-hover align-middle mb-0">
                <thead><tr><th>No</th><th>Kelas</th><th colspan="5" class="text-center">Kehadiran</th><th>Persentase Kehadiran</th><th>Aksi</th><th>Keterangan</th></tr>
                <tr><th></th><th></th><th>Hadir</th><th>Sakit</th><th>Izin</th><th>Alpa</th><th>Terlambat</th><th></th><th></th><th></th></tr></thead>
                <tbody>
                @foreach($rekapPerKelas as $index => $item)
                    @php
                        $rowsKelas = $rowsPerKelas->get($item->kelas->id, collect());
                        $namaStatus = function ($status) use ($rowsKelas) {
                            $normalized = match (strtolower(trim((string) $status))) { 'alpha', 'absen' => 'alpa', 'telat' => 'terlambat', default => strtolower(trim((string) $status)) };
                            return $rowsKelas->filter(fn ($row) => (match (strtolower(trim((string) $row->status))) { 'alpha', 'absen' => 'alpa', 'telat' => 'terlambat', default => strtolower(trim((string) $row->status)) }) === $normalized)->pluck('siswa.nama')->filter()->unique()->values();
                        };
                        $tanpaKeterangan = $rowsKelas->filter(fn ($row) => empty(trim((string) $row->keterangan)) && strtolower(trim((string) $row->status)) !== 'hadir')->pluck('siswa.nama')->filter()->unique()->values();
                    @endphp
                    <tr>
                        <td>{{ $index + 1 }}</td><td class="fw-semibold">{{ $item->kelas->nama_kelas ?? '-' }}</td>
                        <td><span class="badge bg-success">{{ $item->hadir }}</span></td><td><span class="badge bg-warning text-dark">{{ $item->sakit }}</span></td><td><span class="badge bg-info text-dark">{{ $item->izin }}</span></td><td><span class="badge bg-danger">{{ $item->alpa }}</span></td><td><span class="badge bg-primary">{{ $item->terlambat }}</span></td>
                        <td class="fw-bold">{{ number_format($item->persentase, 2) }}%</td>
                        <td><button type="button" class="btn btn-sm btn-primary" data-attendance-modal="attendanceModal{{ $item->kelas->id }}">View</button></td>
                        <td class="small" style="min-width:260px"><div><strong>Alpa:</strong> {{ $namaStatus('alpa')->implode(', ') ?: '-' }}</div><div><strong>Sakit:</strong> {{ $namaStatus('sakit')->implode(', ') ?: '-' }}</div><div><strong>Izin:</strong> {{ $namaStatus('izin')->implode(', ') ?: '-' }}</div><div><strong>Tanpa Keterangan:</strong> {{ $tanpaKeterangan->implode(', ') ?: '-' }}</div></td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
        @foreach($rekapPerKelas as $item)
            @php $rowsKelas = $rowsPerKelas->get($item->kelas->id, collect()); @endphp
            <div class="attendance-modal" id="attendanceModal{{ $item->kelas->id }}" role="dialog" aria-modal="true" aria-labelledby="attendanceModalTitle{{ $item->kelas->id }}">
                <div class="attendance-modal-dialog">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h2 class="h4 mb-0" id="attendanceModalTitle{{ $item->kelas->id }}">Rekap Kehadiran {{ $item->kelas->nama_kelas ?? '-' }}</h2>
                        <button type="button" class="btn-close" data-close-attendance-modal aria-label="Tutup"></button>
                    </div>
                    <div class="attendance-modal-body p-3">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead><tr><th>No</th><th>Nama Siswa</th><th>Status</th><th>Keterangan</th></tr></thead>
                                <tbody>
                                @foreach($rowsKelas as $detailIndex => $detailRow)
                                    <tr><td>{{ $detailIndex + 1 }}</td><td>{{ $detailRow->siswa->nama ?? '-' }}</td><td>{{ ucfirst($detailRow->status) }}</td><td>{{ $detailRow->keterangan ?: 'Tanpa keterangan' }}</td></tr>
                                @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
        @endif
    @endif

    <section class="card shadow-sm mt-4">
        <div class="card-header"><h2 class="h5 mb-0">Guru</h2></div>
        <div class="card-body"><div class="row row-cols-1 row-cols-md-3 g-2">
            @foreach($guru as $item)<div class="col"><span class="d-block border rounded p-2">{{ $item->nama }} <small class="text-muted">{{ $item->kode_guru ?: '' }}</small></span></div>@endforeach
        </div></div>
    </section>
</main>
<script>
    const attendanceStats = @json($statistikKehadiran);
    const chartLabels = ['Hadir', 'Sakit', 'Izin', 'Alpa', 'Terlambat'];
    const chartColors = ['#198754', '#f59f00', '#0d6efd', '#dc3545', '#6f42c1'];
    Object.entries(attendanceStats).forEach(([, stats], index) => {
        const canvas = document.getElementById(`attendanceChart${index}`);
        if (!canvas) return;
        new Chart(canvas, {
            type: 'doughnut',
            data: { labels: chartLabels, datasets: [{ data: chartLabels.map(label => stats[label.toLowerCase()]), backgroundColor: chartColors, borderColor: '#fff', borderWidth: 2 }] },
            options: { responsive: true, maintainAspectRatio: false, cutout: '52%', plugins: { legend: { position: 'bottom', labels: { color: '#172033', padding: 12, usePointStyle: true } } } }
        });
    });

    document.querySelectorAll('[data-attendance-modal]').forEach((button) => {
        button.addEventListener('click', () => {
            const modal = document.getElementById(button.dataset.attendanceModal);
            if (modal) {
                modal.classList.add('is-open');
                document.body.classList.add('modal-open');
            }
        });
    });
    document.querySelectorAll('[data-close-attendance-modal]').forEach((button) => {
        button.addEventListener('click', () => {
            button.closest('.attendance-modal')?.classList.remove('is-open');
            document.body.classList.remove('modal-open');
        });
    });
    document.querySelectorAll('.attendance-modal').forEach((modal) => {
        modal.addEventListener('click', (event) => {
            if (event.target === modal) {
                modal.classList.remove('is-open');
                document.body.classList.remove('modal-open');
            }
        });
    });
    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape') {
            document.querySelectorAll('.attendance-modal.is-open').forEach((modal) => modal.classList.remove('is-open'));
            document.body.classList.remove('modal-open');
        }
    });
</script>
</body>
</html>