<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Jadwal KBM - Kode Mapel</title>
    <style>
        @page {
            size: landscape;
            margin: 5mm;
        }

        * {
            box-sizing: border-box;
        }

        body {
            font-family: Arial, sans-serif;
            font-size: 7px;
            color: #1f2937;
            margin: 0;
            padding: 0;
        }

        .container {
            padding: 6mm;
        }

        .header {
            text-align: center;
            margin-bottom: 6px;
            border-bottom: 1px solid #d1d5db;
            padding-bottom: 4px;
        }

        .header h2 {
            margin: 0;
            font-size: 14px;
            letter-spacing: 0.3px;
        }

        .header p {
            margin: 2px 0;
            font-size: 8px;
            color: #4b5563;
        }

        .meta {
            margin-top: 2px;
            font-size: 7px;
            color: #374151;
        }

        .grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 6px;
        }

        .hari-card {
            border: 1px solid #d1d5db;
            border-radius: 2px;
            padding: 4px;
            page-break-inside: avoid;
            background: #ffffff;
        }

        .hari-title {
            background: #2563eb;
            color: #ffffff;
            padding: 3px 4px;
            font-weight: bold;
            font-size: 8px;
            text-align: center;
            border-radius: 1px;
            margin-bottom: 3px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
            font-size: 6px;
        }

        thead {
            background: #e5e7eb;
        }

        th, td {
            border: 0.4px solid #9ca3af;
            padding: 2px 1px;
            text-align: center;
            vertical-align: middle;
            line-height: 1.1;
            word-wrap: break-word;
            overflow: hidden;
        }

        th {
            font-weight: bold;
        }

        tbody tr:nth-child(odd) {
            background: #f9fafb;
        }

        .jam-col {
            width: 8%;
            font-weight: bold;
        }

        .waktu-col {
            width: 15%;
        }

        .kelas-col {
            width: auto;
        }

        .badge-non-kbm {
            display: inline-block;
            background: #fbbf24;
            color: #111827;
            padding: 1px 3px;
            border-radius: 2px;
            font-size: 6px;
            font-weight: bold;
        }

        .mapel-kode {
            font-weight: bold;
            font-size: 7px;
            letter-spacing: 0.2px;
        }

        .kelas-10 {
            background: #e0f2fe;
        }

        .kelas-11 {
            background: #fef3c7;
        }

        .kelas-12 {
            background: #dcfce7;
        }

        .footer-note {
            margin-top: 4px;
            font-size: 6px;
            color: #4b5563;
            text-align: right;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h2>{{ $sekolah->nama_sekolah ?? 'Jadwal KBM' }}</h2>
            @if($sekolah && ($sekolah->alamat_jalan || $sekolah->telepon))
                <p>{{ $sekolah->alamat_jalan ?? '' }} @if($sekolah->telepon) | Telp: {{ $sekolah->telepon }} @endif</p>
            @endif
            <div class="meta">
                Jadwal Keseluruhan KBM - Kode Mapel | {{ $tahunAjaranAktif->nama ?? 'Tahun Ajaran' }} {{ $semesterAktif ? ' - ' . $semesterAktif->nama : '' }} | Kertas: {{ strtoupper($paperSize) }} Landscape
            </div>
        </div>

        <div class="grid">
            @foreach($hariList as $hari)
                @php
                    $jadwalHari = $jadwalKeseluruhan->get($hari, collect());
                    $jamBelajarHari = $jamBelajarByHari->get($hari, collect());
                    $maxJam = $jamBelajarHari->max('urutan') ?? $jadwalHari->max('jam_ke') ?? 0;

                    $kelasJadwal = $jadwalHari->groupBy('kelas_id')->map(function($items) {
                        return $items->first()->kelas;
                    })->unique('id')->sortBy(function($kelas) {
                        preg_match('/(\d+)\.([A-Z]+)(\d*)/', $kelas->nama_kelas, $matches);
                        $tingkat = intval($matches[1] ?? 0);
                        $jurusan = $matches[2] ?? '';
                        $nomor = intval($matches[3] ?? 0);
                        return sprintf('%02d%s%02d', $tingkat, $jurusan, $nomor);
                    });
                @endphp

                @if($jadwalHari->isNotEmpty() || $jamBelajarHari->isNotEmpty())
                    <div class="hari-card">
                        <div class="hari-title">{{ $hari }}</div>
                        <table>
                            <thead>
                                <tr>
                                    <th class="jam-col">Jam</th>
                                    <th class="waktu-col">Waktu</th>
                                    @foreach($kelasJadwal as $kelas)
                                        @php
                                            preg_match('/^(\d+)/', $kelas->nama_kelas, $matches);
                                            $tingkatKelas = intval($matches[1] ?? 0);
                                            $kelasClass = 'kelas-' . $tingkatKelas;
                                        @endphp
                                        <th class="kelas-col {{ $kelasClass }}">{{ $kelas->nama_kelas }}</th>
                                    @endforeach
                                </tr>
                            </thead>
                            <tbody>
                                @for($jam = 1; $jam <= $maxJam; $jam++)
                                    <tr>
                                        <td class="jam-col">{{ $jam }}</td>
                                        <td class="waktu-col">
                                            @php
                                                $jamBelajarEntry = $jamBelajarHari->where('urutan', $jam)->first();
                                                if ($jamBelajarEntry) {
                                                    if ($jamBelajarEntry->jenis && $jamBelajarEntry->jenis !== 'KBM') {
                                                        echo '<span class="badge-non-kbm">' . strtoupper($jamBelajarEntry->jenis) . '</span>';
                                                    } else {
                                                        echo $jamBelajarEntry->jam_mulai . '-' . substr($jamBelajarEntry->jam_selesai, 0, 5);
                                                    }
                                                } else {
                                                    $jadwalFirst = $jadwalHari->where('jam_ke', $jam)->first();
                                                    if ($jadwalFirst) {
                                                        echo $jadwalFirst->jamBelajar->jam_mulai . '-' . substr($jadwalFirst->jamBelajar->jam_selesai, 0, 5);
                                                    }
                                                }
                                            @endphp
                                        </td>
                                        @foreach($kelasJadwal as $kelas)
                                            @php
                                                preg_match('/^(\d+)/', $kelas->nama_kelas, $matches);
                                                $tingkatKelas = intval($matches[1] ?? 0);
                                                $kelasClass = 'kelas-' . $tingkatKelas;
                                            @endphp
                                            <td class="kelas-col {{ $kelasClass }}">
                                                @php
                                                    $jamBelajarEntry = $jamBelajarHari->where('urutan', $jam)->first();
                                                    if ($jamBelajarEntry && $jamBelajarEntry->jenis && $jamBelajarEntry->jenis !== 'KBM') {
                                                        echo '<span class="badge-non-kbm">' . strtoupper($jamBelajarEntry->jenis) . '</span>';
                                                    } else {
                                                        $jadwalJam = $jadwalHari->where('jam_ke', $jam)->where('kelas_id', $kelas->id)->first();
                                                        if ($jadwalJam) {
                                                            $mapel = $jadwalJam->mataPelajaran;
                                                            echo '<span class="mapel-kode">' . ($mapel->kode_mapel ?? '-') . '</span>';
                                                        } else {
                                                            echo '-';
                                                        }
                                                    }
                                                @endphp
                                            </td>
                                        @endforeach
                                    </tr>
                                @endfor
                            </tbody>
                        </table>
                    </div>
                @endif
            @endforeach
        </div>

        <div class="footer-note">
            Dicetak: {{ now()->format('d-m-Y H:i') }}
        </div>
    </div>
</body>
</html>
