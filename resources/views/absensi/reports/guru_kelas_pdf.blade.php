<!doctype html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Rekap Absensi Kelas Guru</title>
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
    @if(isset($monthlyRows))
        <div class="title">
            <h2>Rekap Absensi {{ $monthlyKelas->nama_kelas ?? '-' }}</h2>
            <div>{{ $sekolah->nama_sekolah ?? 'Sekolah' }}</div>
            <div>{{ $monthlyBulanLabel }} | Tahun Ajaran {{ $tahun->nama_tahun ?? '-' }} | {{ $semester->nama_semester ?? '-' }}</div>
        </div>
        <table>
            <thead><tr><th>No</th><th>Nama Siswa</th><th>NIS</th><th>Hadir</th><th>Terlambat</th><th>Sakit</th><th>Izin</th><th>Alpa</th><th>Total Hari</th></tr></thead>
            <tbody>
                @forelse($monthlyRows as $index => $row)
                    <tr><td class="text-center">{{ $index + 1 }}</td><td>{{ data_get($row, 'nama_siswa', '-') }}</td><td>{{ data_get($row, 'nis', '-') }}</td><td class="text-center">{{ data_get($row, 'hadir_count', 0) }}</td><td class="text-center">{{ data_get($row, 'terlambat_count', 0) }}</td><td class="text-center">{{ data_get($row, 'sakit_count', 0) }}</td><td class="text-center">{{ data_get($row, 'izin_count', 0) }}</td><td class="text-center">{{ data_get($row, 'alpa_count', data_get($row, 'alfa_count', 0)) }}</td><td class="text-center">{{ data_get($row, 'total_days', 0) }}</td></tr>
                @empty
                    <tr><td colspan="9" class="text-center">Belum ada data siswa.</td></tr>
                @endforelse
            </tbody>
        </table>
    @else
    <div class="title">
        <h2>Rekap Absensi Kelas Guru</h2>
        <div>{{ $sekolah->nama_sekolah ?? 'Sekolah' }}</div>
    </div>

    <table class="meta">
        <tr>
            <td width="160">Tahun Ajaran</td>
            <td width="10">:</td>
            <td>{{ $tahun->nama_tahun ?? '-' }}</td>
            <td width="160">Semester</td>
            <td width="10">:</td>
            <td>{{ $semester->nama_semester ?? '-' }}</td>
        </tr>
        <tr>
            <td colspan="6"></td>
        </tr>
    </table>

    <div class="summary">
        <span><b>Jumlah Pertemuan:</b> {{ $summary['total_sessions'] }}</span>
        <span><b>Total Hadir:</b> {{ $summary['total_hadir'] }}</span>
        <span><b>Total Terlambat:</b> {{ $summary['total_terlambat'] }}</span>
        <span><b>Total Sakit:</b> {{ $summary['total_sakit'] }}</span>
        <span><b>Total Izin:</b> {{ $summary['total_izin'] }}</span>
        <span><b>Total Alpa:</b> {{ $summary['total_alpa'] ?? $summary['total_alpha'] ?? 0 }}</span>
        <span><b>Jumlah Data Siswa:</b> {{ $summary['total_siswa'] }}</span>
    </div>

    <table>
        <thead>
            <tr>
                <th width="32">No</th>
                <th width="110">Tanggal</th>
                <th>Kelas</th>
                <th width="110">Guru</th>
                <th width="110">Jam Pelajaran</th>
                <th width="120">Status Kelas</th>
                <th width="80">Hadir</th>
                <th width="80">Terlambat</th>
                <th width="80">Sakit</th>
                <th width="80">Izin</th>
                <th width="80">Alpa</th>
                <th width="90">Total Siswa</th>
            </tr>
        </thead>
        <tbody>
            @forelse($items as $index => $item)
                @php
                    $countStatus = function ($statuses) use ($item) {
                        $needles = collect($statuses)->map(fn ($status) => strtolower((string) $status))->all();
                        return $item->absensiSiswa->filter(function ($row) use ($needles) {
                            return in_array(strtolower((string) ($row->status ?? '')), $needles, true);
                        })->count();
                    };
                    $hadirCount = $countStatus(['hadir']);
                    $terlambatCount = $countStatus(['terlambat', 'telat']);
                    $sakitCount = $countStatus(['sakit']);
                    $izinCount = $countStatus(['izin', 'ijin']);
                    $alphaCount = $countStatus(['alpa', 'alpha', 'alfa', 'absen']);
                @endphp
                <tr>
                    <td class="text-center">{{ $index + 1 }}</td>
                    <td class="text-center">{{ $item->tanggal->format('d/m/Y') }}</td>
                    <td>{{ $item->kelas->nama_kelas ?? '-' }}</td>
                    <td>{{ $item->guru->nama ?? '-' }}</td>
                    <td class="text-center">{{ $item->jamBelajar->jam_mulai ?? '-' }} - {{ $item->jamBelajar->jam_selesai ?? '-' }}</td>
                    <td class="text-center">{{ $item->status_kelas ?? '-' }}</td>
                    <td class="text-center">{{ $hadirCount }}</td>
                    <td class="text-center">{{ $terlambatCount }}</td>
                    <td class="text-center">{{ $sakitCount }}</td>
                    <td class="text-center">{{ $izinCount }}</td>
                    <td class="text-center">{{ $alphaCount }}</td>
                    <td class="text-center">{{ $item->absensiSiswa->count() }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="12" class="text-center">Tidak ada data absensi kelas untuk periode ini.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
    @endif
</body>
</html>
