<!doctype html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Kartu Kendali Pelanggaran</title>
    <style>
        @page { size: A4 portrait; margin: 18mm 12mm; }
        body { font-family: Arial, sans-serif; font-size: 12px; color: #111; }
        .text-center { text-align: center; }
        .header-title { font-size: 30px; font-weight: 700; margin: 0; letter-spacing: 0.5px; }
        .sub-title { font-size: 12px; margin-top: 3px; }
        .meta { margin-top: 14px; margin-bottom: 10px; }
        .meta table { width: 100%; border-collapse: collapse; }
        .meta td { padding: 2px 4px; font-size: 18px; }
        .kendali-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 6px;
            table-layout: fixed;
            border: 2px solid #111 !important;
        }
        .kendali-table tr { border: 1.5px solid #111 !important; }
        .kendali-table th, .kendali-table td {
            border: 1.5px solid #111 !important;
            padding: 6px;
            vertical-align: top;
        }
        .kendali-table th { text-align: center; font-size: 16px; font-weight: 700; }
        .kendali-table td {
            font-size: 14px;
            min-height: 28px;
            white-space: normal;
            overflow-wrap: anywhere;
            word-break: break-word;
        }
        .col-no { width: 5%; text-align: center; }
        .col-tgl { width: 13%; }
        .col-jenis { width: 44%; }
        .col-point { width: 8%; text-align: center; }
        .col-total { width: 8%; text-align: center; }
        .col-ttd { width: 12%; }
        .col-ket { width: 9%; }
        .ket-cell {
            white-space: normal !important;
            overflow-wrap: anywhere !important;
            word-break: break-word !important;
            line-height: 1.2;
            font-size: 12px;
        }

        @media print {
            body { -webkit-print-color-adjust: exact; print-color-adjust: exact; }
            .kendali-table,
            .kendali-table tr,
            .kendali-table th,
            .kendali-table td {
                border-color: #000 !important;
                border-style: solid !important;
            }
        }
    </style>
</head>
<body>
    <div class="text-center">
        <div class="header-title">KARTU KENDALI PELANGGARAN TATA TERTIB SISWA/SISWI</div>
        <div class="sub-title">{{ $sekolah->nama_sekolah ?? 'SEKOLAH' }}</div>
    </div>

    <div class="meta">
        <table>
            <tr>
                <td width="20%">Nama Siswa</td>
                <td width="2%">:</td>
                <td width="38%">{{ $selectedSiswa->nama ?? '-' }}</td>
                <td width="20%">Kelas</td>
                <td width="2%">:</td>
                <td width="18%">{{ $kelas->nama_kelas }}</td>
            </tr>
            <tr>
                <td>NISN</td>
                <td>:</td>
                <td>{{ $selectedSiswa->nisn ?? '-' }}</td>
                <td>Periode</td>
                <td>:</td>
                <td>{{ !empty($tanggalMulai) ? \Carbon\Carbon::parse($tanggalMulai)->format('d/m/Y') : '-' }} s/d {{ !empty($tanggalSelesai) ? \Carbon\Carbon::parse($tanggalSelesai)->format('d/m/Y') : '-' }}</td>
            </tr>
        </table>
    </div>

    <table class="kendali-table">
        <thead>
            <tr>
                <th class="col-no">No</th>
                <th class="col-tgl">Hari/Tgl</th>
                <th class="col-jenis">Jenis Pelanggaran</th>
                <th class="col-point">Point</th>
                <th class="col-total">Total</th>
                <th class="col-ttd">Ttd Siswa</th>
                <th class="col-ttd">Ttd Wali Kelas</th>
                <th class="col-ket">Ket</th>
            </tr>
        </thead>
        <tbody>
            @php
                $runningTotal = 0;
                $renderedRows = 0;
            @endphp
            @foreach(($kartuItems ?? collect()) as $index => $item)
                @php
                    $runningTotal += (int) ($item->poin_pelanggaran ?? 0);
                    $renderedRows++;
                @endphp
                <tr>
                    <td class="col-no">{{ $index + 1 }}</td>
                    <td class="col-tgl">{{ \Carbon\Carbon::parse($item->tanggal)->translatedFormat('D, d/m/Y') }}</td>
                    <td class="col-jenis">{{ $item->deskripsi_pelanggaran }}</td>
                    <td class="col-point">{{ $item->poin_pelanggaran }}</td>
                    <td class="col-total">{{ $runningTotal }}</td>
                    <td class="col-ttd"></td>
                    <td class="col-ttd"></td>
                    <td class="col-ket ket-cell">{{ strtoupper((string) $item->status_absensi) }}</td>
                </tr>
            @endforeach

            @for($i = $renderedRows; $i < 10; $i++)
                <tr>
                    <td class="col-no">{{ $i + 1 }}</td>
                    <td class="col-tgl"></td>
                    <td class="col-jenis"></td>
                    <td class="col-point"></td>
                    <td class="col-total"></td>
                    <td class="col-ttd"></td>
                    <td class="col-ttd"></td>
                    <td class="col-ket"></td>
                </tr>
            @endfor
        </tbody>
    </table>

    <script>
        window.onload = function() { window.print(); }
    </script>
</body>
</html>
