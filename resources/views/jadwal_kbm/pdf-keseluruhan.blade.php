<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Jadwal Keseluruhan KBM</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        @page {
            size: A4 portrait;
            margin: 4mm;
        }

        body {
            font-family: Arial, sans-serif;
            font-size: 5px;
            color: #333;
            margin: 0;
            padding: 0;
        }
        
        .container {
            padding: 2mm;
            column-count: 2;
            column-gap: 3mm;
        }
        
        .header {
            text-align: center;
            margin-bottom: 3px;
            border-bottom: 1px solid #000;
            padding-bottom: 3px;
            break-inside: avoid;
            page-break-inside: avoid;
            -webkit-column-break-inside: avoid;
            column-break-inside: avoid;
            column-span: all;
            -webkit-column-span: all;
        }
        
        .header h2 {
            font-size: 10px;
            margin-bottom: 1px;
        }
        
        .header p {
            font-size: 6px;
            margin: 1px 0;
        }
        
        .hari-section {
            break-inside: avoid;
            page-break-inside: avoid;
            -webkit-column-break-inside: avoid;
            margin-bottom: 3px;
            border: 0.3px solid #999;
            padding: 1px;
            background-color: #ffffff;
        }
        
        .hari-title {
            background-color: #2563eb;
            color: white;
            padding: 1px 3px;
            font-weight: bold;
            font-size: 6px;
            text-align: center;
        }
        
        .kode-guru {
            font-weight: bold;
            font-size: 7px;
        }
        
        /* Background colors untuk setiap tingkat kelas */
        .kelas-10 {
            background-color: #e0f2fe !important;
        }
        
        .kelas-11 {
            background-color: #fef3c7 !important;
        }
        
        .kelas-12 {
            background-color: #dcfce7 !important;
        }
        
        .table-wrapper {
            overflow-x: visible;
            margin-top: 1px;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 5px;
        }
        
        thead {
            background-color: #dbeafe;
        }
        
        th {
            border: 0.3px solid #999;
            padding: 1px;
            text-align: center;
            font-weight: bold;
            background-color: #dbeafe;
            line-height: 1;
        }
        
        td {
            border: 0.3px solid #999;
            padding: 1px;
            text-align: center;
            vertical-align: middle;
            line-height: 1;
            word-wrap: break-word;
            overflow-wrap: break-word;
            white-space: normal;
        }
        
        tbody tr:nth-child(odd) {
            background-color: #ffffff;
        }
        
        tbody tr:nth-child(even) {
            background-color: #ffffff;
        }
        
        .jam-col {
            width: 6%;
            text-align: center;
            padding: 1px;
        }
        
        .waktu-col {
            width: 11%;
            text-align: center;
            padding: 1px;
        }
        
        .kelas-col {
            width: auto;
            min-width: 5%;
            text-align: center;
            padding: 1px !important;
        }
        
        .badge {
            display: inline-block;
            background-color: #fbbf24;
            color: #000;
            padding: 0.5px 1px;
            border-radius: 2px;
            font-size: 5px;
            font-weight: bold;
        }

        .legend-section {
            margin-top: 4px;
            column-span: all;
            -webkit-column-span: all;
            break-inside: avoid;
            page-break-inside: avoid;
            -webkit-column-break-inside: avoid;
        }

        .legend-title {
            font-size: 5px;
            font-weight: bold;
            margin-bottom: 2px;
        }

        .legend-table-wrapper {
            width: 100%;
            overflow-x: visible;
        }

        .legend-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 7px;
            table-layout: fixed;
        }

        .legend-table th,
        .legend-table td {
            border: 0.3px solid #999;
            padding: 0.5px;
            text-align: left;
            vertical-align: top;
            line-height: 1;
            word-wrap: break-word;
            overflow-wrap: break-word;
        }

        .legend-table th {
            background-color: #f3f4f6;
            font-weight: bold;
        }

        .legend-table td {
            font-size: 7px;
        }

        .legend-table th,
        .legend-table td {
            border: 0.3px solid #999;
            padding: 0.5px;
            text-align: left;
            vertical-align: top;
            line-height: 1;
        }

        .legend-table th {
            background-color: #f3f4f6;
            font-weight: bold;
        }
        
        /* Highlighting untuk current user */
        .current-user-jadwal {
            background-color: #86efac !important;
            font-weight: bold;
            border: 1px solid #22c55e;
        }
        
        /* Override semua background table menjadi white */
        tbody td {
            background-color: #ffffff !important;
        }
        
        tbody td.current-user-jadwal {
            background-color: #86efac !important;
        }
        
        @media print {
            body {
                margin: 0;
                padding: 0;
            }
            
            .hari-section {
                page-break-inside: avoid;
            }
            
            table {
                page-break-inside: avoid;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Header -->
        <div class="header">
            @if($sekolah)
                <h2>Jadwal KBM - {{ $sekolah->nama_sekolah }}</h2>
                <p>{{ $sekolah->alamat }}</p>
            @else
                <h2>Jadwal KBM</h2>
            @endif
            @if($tahunAjaranAktif && $semesterAktif)
                <p>{{ $tahunAjaranAktif->nama_tahun }} - {{ $semesterAktif->nama_semester }}</p>
            @endif
        </div>

        <!-- Jadwal per Hari -->
        @php
            $guruLegend = collect();
            foreach ($jadwalKeseluruhan as $jadwalHariGroup) {
                foreach ($jadwalHariGroup as $jadwalHariItem) {
                    if (!empty($jadwalHariItem->guru) && !empty($jadwalHariItem->guru->kode_guru)) {
                        $guruLegend[$jadwalHariItem->guru->kode_guru] = $jadwalHariItem->guru->nama;
                    }
                }
            }
            $guruLegend = collect($guruLegend)->map(function($nama, $kode) {
                return ['kode' => $kode, 'nama' => $nama];
            })->sortBy('kode')->values();
        @endphp

        @foreach($hariList as $hari)
            @php
                $jadwalHari = $jadwalKeseluruhan->get($hari, collect());
                $jamBelajarHari = $jamBelajarByHari->get($hari, collect());
                $maxJam = $jamBelajarHari->max('urutan') ?? $jadwalHari->max('jam_ke') ?? 0;
                
                $kelasJadwal = $jadwalHari->groupBy('kelas_id')->map(function($items) {
                    return $items->first()->kelas;
                })->unique('id')->sortBy(function($kelas) {
                    // Parse kelas name untuk sorting: 12.C4 -> [12, C, 4]
                    preg_match('/(\d+)\.([A-Z]+)(\d*)/', $kelas->nama_kelas, $matches);
                    $tingkat = intval($matches[1] ?? 0);
                    $jurusan = $matches[2] ?? '';
                    $nomor = intval($matches[3] ?? 0);
                    return sprintf("%02d%s%02d", $tingkat, $jurusan, $nomor);
                });
            @endphp

            @if($jadwalHari->isNotEmpty() || $jamBelajarHari->isNotEmpty())
            <div class="hari-section">
                <div class="hari-title">{{ $hari }}</div>
                <div class="table-wrapper">
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
                                <td class="jam-col"><strong>{{ $jam }}</strong></td>
                                <td class="waktu-col">
                                    @php
                                        $jamBelajarEntry = $jamBelajarHari->where('urutan', $jam)->first();
                                        if ($jamBelajarEntry) {
                                            // Tampilkan hanya waktu pada kolom Waktu di PDF
                                            echo $jamBelajarEntry->jam_mulai . '-' . substr($jamBelajarEntry->jam_selesai, 0, 5);
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
                                    
                                    // Check if this is current user's jadwal
                                    $isCurrentUserJadwal = false;
                                    $jadwalJam = $jadwalHari->where('jam_ke', $jam)->where('kelas_id', $kelas->id)->first();
                                    if ($jadwalJam && $jadwalJam->guru && isset($currentUserGuruKode)) {
                                        $isCurrentUserJadwal = $jadwalJam->guru->kode_guru === $currentUserGuruKode;
                                    }
                                    $currentUserClass = $isCurrentUserJadwal ? 'current-user-jadwal' : '';
                                @endphp
                                <td class="kelas-col {{ $kelasClass }} {{ $currentUserClass }}">
                                    @php
                                        $jamBelajarEntry = $jamBelajarHari->where('urutan', $jam)->first();
                                        if ($jamBelajarEntry && $jamBelajarEntry->jenis && $jamBelajarEntry->jenis !== 'KBM') {
                                            // Tampilkan kode kegiatan (bukan nama kegiatan)
                                            $kodeKegiatan = null;
                                            if (isset($kegiatanList)) {
                                                $jenisTrim = strtolower(trim($jamBelajarEntry->jenis));
                                                $kegiatan = collect($kegiatanList)->first(function($item) use ($jenisTrim) {
                                                    return strtolower(trim($item->nama_kegiatan)) === $jenisTrim;
                                                });
                                                if ($kegiatan && isset($kegiatan->kode_kegiatan)) {
                                                    $kodeKegiatan = $kegiatan->kode_kegiatan;
                                                }
                                            }
                                            echo '<span class="badge">' . ($kodeKegiatan ? $kodeKegiatan : strtoupper($jamBelajarEntry->jenis)) . '</span>';
                                        } else {
                                            $jadwalJam = $jadwalHari->where('jam_ke', $jam)->where('kelas_id', $kelas->id)->first();
                                            if ($jadwalJam) {
                                                if ($jadwalJam->guru_id && $jadwalJam->guru) {
                                                    $guru = $jadwalJam->guru;
                                                    echo '<span class="kode-guru">' . ($guru->kode_guru ?? substr($guru->nama, 0, 3)) . '</span>';
                                                } else {
                                                    echo '-';
                                                }
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
            </div>
            @endif
        @endforeach

        @if($guruLegend->isNotEmpty())
            @php
                $legendColumns = 3;
                $legendCount = $guruLegend->count();
                $legendRows = (int) ceil($legendCount / $legendColumns);
            @endphp
            <div class="legend-section">
                <div class="legend-title">Keterangan Kode Guru</div>
                <div class="legend-table-wrapper">
                    <table class="legend-table">
                        <thead>
                            <tr>
                                @for($col = 1; $col <= $legendColumns; $col++)
                                    <th>Kode Guru</th>
                                    <th>Nama Guru</th>
                                @endfor
                            </tr>
                        </thead>
                        <tbody>
                            @for($row = 0; $row < $legendRows; $row++)
                                <tr>
                                    @for($col = 0; $col < $legendColumns; $col++)
                                        @php
                                            $index = $row + ($col * $legendRows);
                                            $guru = $guruLegend->get($index);
                                        @endphp
                                        @if($guru)
                                            <td>{{ $guru['kode'] }}</td>
                                            <td>{{ $guru['nama'] }}</td>
                                        @else
                                            <td></td>
                                            <td></td>
                                        @endif
                                    @endfor
                                </tr>
                            @endfor
                        </tbody>
                    </table>
                </div>
            </div>
        @endif
    </div>
</body>
</html>
