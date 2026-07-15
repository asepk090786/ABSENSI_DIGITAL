<!doctype html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Kehadiran Guru</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 11px; color: #222; }
        .title { text-align: center; margin-bottom: 8px; }
        .title h2 { margin: 0; font-size: 16px; }
        .meta { margin-bottom: 10px; }
        .meta td { padding: 2px 4px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #555; padding: 6px; }
        th { background: #f1f5f9; }
        .text-center { text-align: center; }
        .summary { margin: 8px 0 10px; }
        .summary span { display: inline-block; margin-right: 10px; }
    </style>
</head>
<body>
    <div class="title">
        <h2>Laporan Kehadiran Guru</h2>
        <div>{{ $sekolah->nama_sekolah ?? 'Sekolah' }}</div>
    </div>

    <table class="meta">
        <tr>
            <td width="140">Tanggal Laporan</td>
            <td width="10">:</td>
            <td>{{ \Carbon\Carbon::parse($selectedTanggal)->format('d-m-Y') }}</td>
            <td width="140">Tahun Ajaran</td>
            <td width="10">:</td>
            <td>{{ $tahun->nama_tahun ?? '-' }}</td>
        </tr>
        <tr>
            <td>Semester</td>
            <td>:</td>
            <td>{{ $semester->nama_semester ?? '-' }}</td>
            <td></td>
            <td></td>
            <td></td>
        </tr>
    </table>

    <div class="summary">
        <span><b>Total:</b> {{ $summary['total'] }}</span>
        <span><b>Hadir:</b> {{ $summary['hadir'] }}</span>
        <span><b>Izin:</b> {{ $summary['izin'] }}</span>
        <span><b>Sakit:</b> {{ $summary['sakit'] }}</span>
        <span><b>Tidak Hadir:</b> {{ $summary['tidak_hadir'] }}</span>
    </div>

    <table>
        <thead>
            <tr>
                <th width="35">No</th>
                <th>Nama Guru</th>
                <th width="130">NIP</th>
                <th width="110">Status</th>
                <th>Keterangan</th>
                <th width="150">Dicatat Oleh</th>
            </tr>
        </thead>
        <tbody>
            @forelse($laporanRows as $index => $row)
                <tr>
                    <td class="text-center">{{ $index + 1 }}</td>
                    <td>{{ $row->nama_guru }}</td>
                    <td class="text-center">{{ $row->nip }}</td>
                    <td class="text-center">{{ strtoupper(str_replace('_', ' ', (string) $row->status)) }}</td>
                    <td>{{ $row->keterangan ?: '-' }}</td>
                    <td>{{ $row->dicatat_oleh }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="text-center">Tidak ada data kehadiran guru pada tanggal ini.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>
