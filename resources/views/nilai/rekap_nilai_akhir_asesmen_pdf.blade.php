<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Rekap Nilai Akhir</title>
    <style>
        @page { margin: 24px; }
        body { font-family: DejaVu Sans, sans-serif; color: #202938; font-size: 10px; }
        h1 { font-size: 18px; margin: 0 0 5px; }
        .meta { color: #536174; margin-bottom: 16px; }
        .summary { width: 100%; margin-bottom: 16px; border-collapse: collapse; }
        .summary td { border: 1px solid #d8dee8; padding: 7px; width: 25%; }
        .label { color: #657386; display: block; font-size: 9px; margin-bottom: 4px; }
        table.scores { width: 100%; border-collapse: collapse; }
        .scores th, .scores td { border: 1px solid #cbd3df; padding: 6px; }
        .scores th { background: #edf2f7; text-align: left; }
        .scores td.number, .scores th.number { text-align: center; }
        .footer { margin-top: 14px; text-align: right; color: #657386; font-size: 9px; }
    </style>
</head>
<body>
    <h1>Rekap Nilai Akhir</h1>
    <div class="meta">
        Kelas: {{ $kelas->nama_kelas }} | Mata Pelajaran: {{ $mapel->nama_mapel }}<br>
        @if($tahunAjaranAktif && $semesterAktif)
            Tahun Ajaran: {{ $tahunAjaranAktif->tahun_ajaran }} | Semester: {{ $semesterAktif->nama_semester }}
        @endif
    </div>

    <table class="summary">
        <tr>
            <td><span class="label">Bobot Formatif</span>{{ number_format($data['bobot_formatif'], 2) }}%</td>
            <td><span class="label">Bobot Sumatif</span>{{ number_format($data['bobot_sumatif'], 2) }}%</td>
            <td><span class="label">Rata-rata Formatif</span>{{ $data['rata_formatif'] !== null ? number_format($data['rata_formatif'], 2) : '-' }}</td>
            <td><span class="label">Rata-rata Sumatif</span>{{ $data['rata_sumatif'] !== null ? number_format($data['rata_sumatif'], 2) : '-' }}</td>
        </tr>
    </table>

    <table class="scores">
        <thead>
            <tr>
                <th class="number">No</th>
                <th>NIS</th>
                <th>Nama Siswa</th>
                <th class="number">Rata-rata Formatif</th>
                <th class="number">Rata-rata Sumatif</th>
                <th class="number">Nilai Akhir</th>
                <th>Keterangan</th>
            </tr>
        </thead>
        <tbody>
            @forelse($data['students'] as $index => $student)
                <tr>
                    <td class="number">{{ $index + 1 }}</td>
                    <td>{{ $student->nis ?: '-' }}</td>
                    <td>{{ $student->nama }}</td>
                    <td class="number">{{ $student->rata_formatif !== null ? number_format($student->rata_formatif, 2) : '-' }}</td>
                    <td class="number">{{ $student->rata_sumatif !== null ? number_format($student->rata_sumatif, 2) : '-' }}</td>
                    <td class="number">{{ $student->nilai_akhir !== null ? number_format($student->nilai_akhir, 2) : '-' }}</td>
                    <td>{{ $student->nilai_akhir !== null ? 'Lengkap' : 'Nilai formatif atau sumatif belum tersedia' }}</td>
                </tr>
            @empty
                <tr><td colspan="7">Belum ada siswa pada kelas ini.</td></tr>
            @endforelse
        </tbody>
    </table>

    <div class="footer">Rata-rata nilai akhir kelas: {{ $data['nilai_akhir'] !== null ? number_format($data['nilai_akhir'], 2) : '-' }}</div>
</body>
</html>