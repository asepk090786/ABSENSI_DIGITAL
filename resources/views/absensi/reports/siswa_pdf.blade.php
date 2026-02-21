<!doctype html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Kehadiran Siswa</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 11px; color: #222; }
        .title { text-align: center; margin-bottom: 8px; }
        .title h2 { margin: 0; font-size: 16px; }
        .meta { margin-bottom: 10px; }
        .meta td { padding: 2px 4px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #555; padding: 5px; }
        th { background: #f1f5f9; }
        .text-center { text-align: center; }
        .summary { margin: 8px 0 10px; }
        .summary span { display: inline-block; margin-right: 10px; }
    </style>
</head>
<body>
    <div class="title">
        <h2>Laporan Kehadiran Siswa</h2>
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
            <td>Kelas</td>
            <td>:</td>
            <td>{{ $kelasLabel ?? 'Semua Kelas' }}</td>
            <td>Semester</td>
            <td>:</td>
            <td>{{ $semester->nama_semester ?? '-' }}</td>
        </tr>
    </table>

    <div class="summary">
        <span><b>Total:</b> {{ $summary['total'] }}</span>
        <span><b>Hadir:</b> {{ $summary['hadir'] }}</span>
        <span><b>Terlambat:</b> {{ $summary['terlambat'] }}</span>
        <span><b>Izin:</b> {{ $summary['izin'] }}</span>
        <span><b>Sakit:</b> {{ $summary['sakit'] }}</span>
        <span><b>Alpha:</b> {{ $summary['alpha'] }}</span>
    </div>

    <table>
        <thead>
            <tr>
                <th width="35">No</th>
                <th width="90">Kelas</th>
                <th>Nama Siswa</th>
                <th width="80">NIS</th>
                <th width="90">Status</th>
                <th width="120">Guru Penginput</th>
                <th>Keterangan</th>
            </tr>
        </thead>
        <tbody>
            @forelse($laporanRows as $index => $row)
                <tr>
                    <td class="text-center">{{ $index + 1 }}</td>
                    <td>{{ $row->nama_kelas }}</td>
                    <td>{{ $row->nama_siswa }}</td>
                    <td class="text-center">{{ $row->nis }}</td>
                    <td class="text-center">{{ strtoupper((string) $row->status) }}</td>
                    <td>{{ $row->nama_guru }}</td>
                    <td>{{ $row->keterangan ?: '-' }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" class="text-center">Tidak ada data kehadiran siswa pada tanggal ini.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>
