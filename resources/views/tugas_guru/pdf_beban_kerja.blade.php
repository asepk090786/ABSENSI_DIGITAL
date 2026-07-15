<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Beban Kerja Guru</title>
    <style>
        table { border-collapse: collapse; width: 100%; font-size:12px }
        th, td { border: 1px solid #444; padding: 6px; }
        th { background: #eee; }
    </style>
    </head>
<body>
    <h3>Beban Kerja Guru</h3>
    <table>
        <thead>
            <tr>
                <th>NO</th>
                <th>NAMA GURU</th>
                <th>GOL/RUANG</th>
                <th>MATA PELAJARAN</th>
                @foreach($kelasList as $kelas)
                    <th>{{ $kelas->nama_kelas }}</th>
                @endforeach
                <th>JUMLAH JAM KBM</th>
            </tr>
        </thead>
        <tbody>
            @php $no = 0; @endphp
            @foreach($guruBebanKerja as $guru)
                @php $guruMapels = $guru->tugasGuru->groupBy('mata_pelajaran_id'); @endphp
                @foreach($guruMapels as $mapelId => $tugasPerMapel)
                    @php $no++; $mapel = $tugasPerMapel->first()->mataPelajaran; @endphp
                    <tr>
                        <td>{{ $no }}</td>
                        <td>{{ $guru->nama }}</td>
                        <td>{{ $guru->golongan ?? '-' }} / {{ $guru->ruang ?? '-' }}</td>
                        <td>{{ $mapel->nama_mapel ?? '-' }}</td>
                        @php $sumJam = 0; @endphp
                        @foreach($kelasList as $kelas)
                            @php
                                $jumlahJam = 0;
                                $hasSpesificTask = $tugasPerMapel->contains(function($task) use ($kelas) {
                                    return $task->kelas_id === $kelas->id;
                                });
                                if ($hasSpesificTask) {
                                    $key = $guru->id . '_' . $mapel->id . '_' . $kelas->id;
                                    $jumlahJam = $jadwalKbmJumlah[$key] ?? 0;
                                } else {
                                    $hasGeneralTask = $tugasPerMapel->contains(function($task) { return $task->kelas_id === null; });
                                    if ($hasGeneralTask) {
                                        $key = $guru->id . '_' . $mapel->id . '_' . $kelas->id;
                                        $jumlahJam = $jadwalKbmJumlah[$key] ?? 0;
                                    }
                                }
                                $sumJam += $jumlahJam;
                            @endphp
                            <td class="text-center">{{ $jumlahJam }}</td>
                        @endforeach
                        <td class="text-center">{{ $sumJam }}</td>
                    </tr>
                @endforeach
            @endforeach
        </tbody>
    </table>
</body>
</html>
