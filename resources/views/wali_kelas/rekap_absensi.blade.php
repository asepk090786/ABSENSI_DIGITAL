@extends('layouts.app', ['pageSlug' => 'wali-kelas'])

@section('title', 'Rekap Kehadiran Siswa')

@section('content')
<style>
    .wali-rekap-page {
        --rekap-ink: #17324d;
        --rekap-muted: #64748b;
        --rekap-line: #dbe5f0;
        --rekap-paper: #f5f8fc;
        color: var(--rekap-ink);
    }
    .wali-rekap-page .rekap-card {
        border: 1px solid #e1eaf3;
        border-radius: 1rem;
        box-shadow: 0 12px 34px rgba(36, 67, 107, .08);
    }
    .wali-rekap-page .stat-card {
        border: 1px solid #e1eaf3;
        border-radius: 1rem;
        box-shadow: 0 8px 24px rgba(36, 67, 107, .06);
    }
    .wali-rekap-page .stat-icon {
        width: 2.6rem;
        height: 2.6rem;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: .75rem;
    }
    .wali-rekap-page .table-scroll { overflow-x: auto; }
    .wali-rekap-page .attendance-table {
        min-width: max-content;
        border-collapse: separate;
        border-spacing: 0;
    }
    .wali-rekap-page .attendance-table th,
    .wali-rekap-page .attendance-table td {
        border-bottom: 1px solid #e5edf5;
        border-right: 1px solid #eef3f8;
        padding: .65rem .55rem;
        text-align: center;
        white-space: nowrap;
        font-size: .8rem;
    }
    .wali-rekap-page .attendance-table th {
        background: #eef4fb;
        color: #58708b;
        border-bottom-color: var(--rekap-line);
        font-size: .7rem;
        font-weight: 700;
        letter-spacing: .05em;
        text-transform: uppercase;
    }
    .wali-rekap-page .attendance-table .jam-title {
        background: #e4edf8;
        color: #29455f;
    }
    .wali-rekap-page .attendance-table .jam-start { border-left: 2px solid #8095aa !important; }
    .wali-rekap-page .attendance-table .jam-end { border-right: 2px solid #8095aa !important; }
    .wali-rekap-page .attendance-table .class-cell {
        min-width: 120px;
        text-align: left;
        font-weight: 700;
    }
    .wali-rekap-page .attendance-table .status-h { background: #e8f8ef; color: #168456; }
    .wali-rekap-page .attendance-table .status-s { background: #eaf2ff; color: #2463b6; }
    .wali-rekap-page .attendance-table .status-i { background: #fff5d9; color: #a66800; }
    .wali-rekap-page .attendance-table .status-a { background: #ffebeb; color: #c53d3d; }
    .wali-rekap-page .attendance-table tbody tr:hover { background: #f7faff; }
    .wali-rekap-page .filter-button { border: 1px solid #dbe5f0; color: #52708c; }
    .wali-rekap-page .filter-button.is-active,
    .wali-rekap-page .filter-button:hover { background: #1967d2; border-color: #1967d2; color: #fff; }
    .wali-rekap-page .percentage-track { width: 70px; height: 7px; overflow: hidden; border-radius: 99px; background: #e7edf5; }
    .wali-rekap-page .percentage-fill { height: 100%; border-radius: inherit; background: linear-gradient(90deg, #1fa971, #49c78b); }
</style>

<div class="container-fluid wali-rekap-page">
    <div class="d-flex flex-column flex-lg-row align-items-lg-end justify-content-between gap-3 mb-4">
        <div>
            <div class="d-flex align-items-center gap-2 mb-2 text-muted small text-uppercase fw-bold">
                <i class="ti ti-clipboard-check"></i> Rekap Kehadiran Siswa
            </div>
            <h3 class="mb-1">Kelas {{ $kelasBinaan->nama_kelas }}</h3>
            <p class="text-muted mb-0">Rekap per jam KBM untuk {{ $bulanLabels[$bulan] }} {{ $tahun }}.</p>
        </div>
        <a href="{{ route('wali_kelas.absensi', ['bulan' => $bulan, 'tahun' => $tahun]) }}" class="btn btn-outline-secondary">
            <i class="ti ti-arrow-left me-1"></i>Kembali ke Absensi
        </a>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-6 col-xl-3"><div class="stat-card bg-white p-3 h-100"><div class="d-flex justify-content-between align-items-start"><div><div class="text-muted small">Siswa Ditampilkan</div><div class="fs-3 fw-bold">{{ $totalSiswa }}</div></div><span class="stat-icon" style="background:#e5efff;color:#1967d2"><i class="ti ti-users"></i></span></div></div></div>
        <div class="col-6 col-xl-3"><div class="stat-card bg-white p-3 h-100"><div class="d-flex justify-content-between align-items-start"><div><div class="text-muted small">Kehadiran</div><div class="fs-3 fw-bold">{{ $persentase }}%</div></div><span class="stat-icon" style="background:#e0f6eb;color:#1a9864"><i class="ti ti-trending-up"></i></span></div></div></div>
        <div class="col-6 col-xl-3"><div class="stat-card bg-white p-3 h-100"><div class="d-flex justify-content-between align-items-start"><div><div class="text-muted small">Perlu Perhatian</div><div class="fs-3 fw-bold">{{ $watchStudents }}</div></div><span class="stat-icon" style="background:#fff2d2;color:#c1840e"><i class="ti ti-triangle-alert"></i></span></div></div></div>
        <div class="col-6 col-xl-3"><div class="stat-card bg-white p-3 h-100"><div class="d-flex justify-content-between align-items-start"><div><div class="text-muted small">Hari Ditampilkan</div><div class="fs-3 fw-bold">{{ $tanggalKolom->count() }}</div></div><span class="stat-icon" style="background:#fde8e8;color:#d55050"><i class="ti ti-calendar"></i></span></div></div></div>
    </div>

    <section class="rekap-card bg-white overflow-hidden">
        <div class="p-3 p-md-4 border-bottom">
            <div class="d-flex flex-column flex-xl-row align-items-xl-center justify-content-between gap-3">
                <div><h5 class="mb-1 fw-bold">Rekap Kehadiran {{ $kelasBinaan->nama_kelas }}</h5><p class="text-muted mb-0 small">Setiap kolom jam menampilkan status kehadiran siswa pada periode terpilih.</p></div>
                <form method="GET" action="{{ route('wali_kelas.rekap_absensi') }}" class="d-flex flex-wrap gap-2 align-items-end">
                    <div><label class="form-label small mb-1" for="rekap-bulan-baru">Bulan</label><select id="rekap-bulan-baru" name="bulan" class="form-select form-select-sm">@foreach($bulanLabels as $nomor => $label)<option value="{{ $nomor }}" {{ $bulan === $nomor ? 'selected' : '' }}>{{ $label }}</option>@endforeach</select></div>
                    <div><label class="form-label small mb-1" for="rekap-tahun-baru">Tahun</label><input id="rekap-tahun-baru" name="tahun" type="number" min="2000" max="2100" value="{{ $tahun }}" class="form-control form-control-sm"></div>
                    <button class="btn btn-primary btn-sm" type="submit"><i class="ti ti-filter me-1"></i>Tampilkan</button>
                </form>
            </div>
            <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mt-4">
                <div class="d-flex flex-wrap gap-2" role="group" aria-label="Filter status kehadiran">
                    <button type="button" class="btn btn-sm rounded-pill filter-button is-active" data-filter="all">Semua</button>
                    <button type="button" class="btn btn-sm rounded-pill filter-button" data-filter="H">Hadir</button>
                    <button type="button" class="btn btn-sm rounded-pill filter-button" data-filter="S">Sakit</button>
                    <button type="button" class="btn btn-sm rounded-pill filter-button" data-filter="I">Izin</button>
                    <button type="button" class="btn btn-sm rounded-pill filter-button" data-filter="A">Alpa</button>
                </div>
                <div class="small text-muted"><span class="me-3"><span class="badge rounded-circle p-1 bg-success"> </span> Hadir</span><span class="me-3"><span class="badge rounded-circle p-1 bg-primary"> </span> Sakit</span><span class="me-3"><span class="badge rounded-circle p-1 bg-warning"> </span> Izin</span><span><span class="badge rounded-circle p-1 bg-danger"> </span> Alpa</span></div>
            </div>
        </div>
        <div class="px-3 px-md-4 py-2 bg-light border-bottom small text-muted" id="rekap-result">Menampilkan rekap kelas binaan.</div>
        @if($tanggalKolom->isEmpty())
            <div class="p-4"><div class="alert alert-info mb-0">Belum ada jadwal KBM untuk kelas ini pada periode aktif.</div></div>
        @else
            <div class="table-scroll">
                <table class="table attendance-table w-100 mb-0">
                    <thead>
                        <tr><th rowspan="3" class="class-cell">No</th><th rowspan="3" class="class-cell">Siswa</th><th colspan="{{ $tanggalKolom->count() * 4 }}">Tanggal</th><th colspan="4">Jumlah</th><th rowspan="3">Kehadiran</th></tr>
                        <tr>@foreach($tanggalKolom as $tanggal)<th colspan="4" class="jam-title jam-start jam-end" title="{{ \Carbon\Carbon::parse($tanggal)->translatedFormat('l, d F Y') }}">{{ \Carbon\Carbon::parse($tanggal)->format('d') }}</th>@endforeach<th colspan="4"></th></tr>
                        <tr>@foreach($tanggalKolom as $tanggal)<th class="status-h jam-start">H</th><th class="status-s">S</th><th class="status-i">I</th><th class="status-a jam-end">A</th>@endforeach<th class="status-h">H</th><th class="status-s">S</th><th class="status-i">I</th><th class="status-a">A</th></tr>
                    </thead>
                    <tbody>
                        @foreach($rows as $index => $row)
                            <tr data-row-status="{{ collect($row->jumlah)->filter(fn ($value) => $value > 0)->keys()->implode(' ') }}" data-student-name="{{ strtolower($row->nama) }}">
                                <td>{{ $index + 1 }}</td>
                                <td class="class-cell">{{ $row->nama }}<small class="d-block text-muted fw-normal">NIS: {{ $row->nis ?: '-' }}</small></td>
                                @foreach($tanggalKolom as $tanggal)
                                    <td class="jam-start">{{ $row->counts[$tanggal]['H'] ?? 0 }}</td><td>{{ $row->counts[$tanggal]['S'] ?? 0 }}</td><td>{{ $row->counts[$tanggal]['I'] ?? 0 }}</td><td class="jam-end">{{ $row->counts[$tanggal]['A'] ?? 0 }}</td>
                                @endforeach
                                <td class="fw-bold text-success">{{ $row->jumlah['H'] }}</td><td class="fw-bold text-primary">{{ $row->jumlah['S'] }}</td><td class="fw-bold text-warning">{{ $row->jumlah['I'] }}</td><td class="fw-bold text-danger">{{ $row->jumlah['A'] }}</td>
                                <td><div class="d-flex align-items-center justify-content-center gap-2"><div class="percentage-track"><div class="percentage-fill" style="width:{{ min(100, $row->persentase) }}%"></div></div><strong>{{ $row->persentase }}%</strong></div></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
        <div class="p-3 border-top text-muted small">H mencakup status hadir dan terlambat. Kolom jumlah menampilkan total bilangan bulat selama periode terpilih.</div>
    </section>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const rows = Array.from(document.querySelectorAll('[data-row-status]'));
    const result = document.getElementById('rekap-result');
    document.querySelectorAll('[data-filter]').forEach(function (button) {
        button.addEventListener('click', function () {
            document.querySelectorAll('[data-filter]').forEach(function (item) { item.classList.remove('is-active'); });
            button.classList.add('is-active');
            const filter = button.dataset.filter;
            let visibleCount = 0;
            rows.forEach(function (row) {
                const visible = filter === 'all' || row.dataset.rowStatus.split(' ').includes(filter);
                row.classList.toggle('d-none', !visible);
                if (visible) visibleCount++;
            });
            if (result) result.textContent = 'Menampilkan ' + visibleCount + ' siswa dari ' + rows.length + '.';
        });
    });
});
</script>
@endsection
