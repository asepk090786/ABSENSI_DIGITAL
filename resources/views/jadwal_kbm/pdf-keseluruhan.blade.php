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
        
        body {
            font-family: Arial, sans-serif;
            font-size: 8px;
            color: #333;
        }
        
        .container {
            padding: 5mm;
        }
        
        .header {
            text-align: center;
            margin-bottom: 8px;
            border-bottom: 2px solid #000;
            padding-bottom: 5px;
        }
        
        .header h2 {
            font-size: 12px;
            margin-bottom: 2px;
        }
        
        .header p {
            font-size: 7px;
            margin: 1px 0;
        }
        
        .hari-section {
            page-break-inside: avoid;
            margin-bottom: 8px;
            border: 1px solid #999;
        }
        
        .hari-title {
            background-color: #2563eb;
            color: white;
            padding: 3px 5px;
            font-weight: bold;
            font-size: 8px;
            text-align: center;
        }
        
        .kode-guru {
            font-weight: bold;
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
            overflow-x: auto;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 7px;
        }
        
        thead {
            background-color: #dbeafe;
        }
        
        th {
            border: 0.5px solid #999;
            padding: 2px 1px;
            text-align: center;
            font-weight: bold;
            background-color: #dbeafe;
            height: 12px;
        }
        
        td {
            border: 0.5px solid #999;
            padding: 1px;
            text-align: center;
            vertical-align: middle;
            height: 12px;
            word-wrap: break-word;
        }
        
        tbody tr:nth-child(odd) {
            background-color: #f9fafb;
        }
        
        tbody tr:nth-child(even) {
            background-color: #fff;
        }
        
        .jam-col {
            width: 5%;
            text-align: center;
        }
        
        .waktu-col {
            width: 12%;
            text-align: center;
        }
        
        .kelas-col {
            width: auto;
            min-width: 4%;
            text-align: center;
        }
        
        .badge {
            display: inline-block;
            background-color: #fbbf24;
            color: #000;
            padding: 1px 2px;
            border-radius: 2px;
            font-size: 6px;
            font-weight: bold;
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
                <p>{{ $tahunAjaranAktif->nama }} - {{ $semesterAktif->nama }}</p>
            @endif
        </div>

        <!-- Jadwal per Hari -->
        @foreach($hariList as $hari)
            @php
                $jadwalHari = $jadwalKeseluruhan->get($hari, collect());
                $jamBelajarHari = $jamBelajarByHari->get($hari, collect());
                $maxJam = $jamBelajarHari->max('urutan') ?? $jadwalHari->max('jam_ke') ?? 0;
                
                $kelasJadwal = $jadwalHari->groupBy('kelas_id')->map(function($items) {
                    return $items->first()->kelas;
                })->unique('id')->sortBy(function($kelas) {
                    // Extract tingkat kelas (10, 11, 12)
                    preg_match('/^(\d+)/', $kelas->nama_kelas, $matches);
                    $tingkat = intval($matches[1] ?? 0);
                    // Sort by tingkat ascending (10, 11, 12)
                    return $tingkat;
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
                                @endphp
                                <td class="kelas-col {{ $kelasClass }}">
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
    </div>
</body>
</html>
