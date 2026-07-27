<style>
    @page {
        size: A4 landscape;
        margin: 8mm;
    }

    body {
        margin: 0;
        padding: 0;
        font-family: DejaVu Sans, sans-serif;
    }

    .header-info {
        text-align: center;
        margin-bottom: 6px;
    }

    .header-info h3 {
        margin: 2px 0;
        font-size: 14px;
    }

    .header-info p {
        margin: 1px 0;
        font-size: 11px;
    }

    .attendance-table {
        width: 100%;
        border-collapse: collapse;
        margin-top: 8px;
        font-size: 10px;
        table-layout: fixed;
    }

    .attendance-table th,
    .attendance-table td {
        border: 1px solid #222;
        padding: 3px 4px;
        text-align: center;
        vertical-align: middle;
        word-wrap: break-word;
    }

    .attendance-table th {
        background-color: #f5f5f5;
        font-weight: bold;
        line-height: 1.2;
    }

    .attendance-table tbody td {
        height: 18px;
        line-height: 1.2;
    }

    .attendance-table thead tr:first-child th {
        text-align: center;
    }

    .signature-section {
        margin-top: 14px;
        padding: 0 20px;
    }

    .signature-table {
        width: 100%;
        border-collapse: collapse;
    }

    .signature-block {
        width: 45%;
        vertical-align: top;
    }

    .signature-block.left {
        text-align: left;
    }

    .signature-block.right {
        text-align: right;
    }

    .signature-label {
        font-weight: bold;
        font-size: 11px;
        margin-bottom: 2px;
    }

    .signature-name {
        font-size: 11px;
        min-height: 14px;
        margin: 10px 0 3px 0;
    }

    .signature-line {
        border-top: 1px solid #000;
        width: 180px;
        margin: 3px 0 0.5px 0;
        display: inline-block;
    }

    .signature-nip {
        font-size: 11px;
        min-height: 14px;
    }
</style>

<div class="header-info">
    <h3 style="font-weight: bold;">REKAP KEHADIRAN GURU</h3>
    <p><strong>Sekolah:</strong> {{ $sekolahNama }}</p>
    <p><strong>Tanggal:</strong> {{ \Carbon\Carbon::parse($selectedTanggal)->format('d/m/Y') }}</p>
    <p><strong>Tahun Pelajaran:</strong> {{ $tahunAjaran }} | <strong>Semester:</strong> {{ $semesterNama }}</p>
</div>

<table class="attendance-table">
    <thead>
        <tr>
            <th rowspan="2" style="width: 4%;">No</th>
            <th rowspan="2" style="width: 16%;">Nama Guru</th>
            <th rowspan="2" style="width: 14%;">NIP</th>
            <th colspan="{{ count($jamColumns) }}" style="width: auto;">Kehadiran di jam ke</th>
            <th rowspan="2" style="width: auto;">Keterangan</th>
        </tr>
        <tr>
            @foreach($jamColumns as $jam)
                <th style="width: 6%;">{{ $jam }}</th>
            @endforeach
        </tr>
    </thead>
    <tbody>
        @foreach($rows as $item)
            <tr>
                <td style="text-align: center;">{{ $item['no'] }}</td>
                <td style="text-align: left;">{{ $item['nama'] }}</td>
                <td style="text-align: center;">{{ $item['nip'] }}</td>
                @foreach($jamColumns as $jam)
                    @php
                        $bg = $item['jam_colors'][$jam] ?? '#ffffff';
                    @endphp
                    <td style="text-align: center; background-color: {{ $bg }};">{{ $item['jam_status'][$jam] ?? '' }}</td>
                @endforeach
                <td style="text-align: left; font-size: 9px;">{{ $item['keterangan'] }}</td>
            </tr>
        @endforeach
    </tbody>
</table>

<div style="margin-top: 14px; padding: 0 20px;">
    <strong>REKAP KEHADIRAN</strong><br><br>
    <table class="signature-table">
        <tr>
            <td class="signature-block left">
                <div class="signature-label">Hadir: {{ $summary['hadir'] ?? 0 }}</div>
                <div class="signature-label">Tidak Hadir: {{ $summary['tidak_hadir'] ?? 0 }}</div>
                <div class="signature-label">Izin: {{ $summary['izin'] ?? 0 }}</div>
                <div class="signature-label">Sakit: {{ $summary['sakit'] ?? 0 }}</div>
                <div class="signature-label">Total Guru: {{ $summary['total'] ?? 0 }}</div>
            </td>
            <td class="signature-block right">
                <div class="signature-label">Guru Piket</div><br>
                <div class="signature-name">{{ $namaGuruPiket ?? '' }}</div>
                <div class="signature-line"></div>
                <div class="signature-nip">NIP. {{ optional($pencatatGuru)->nip ?? '' }}</div>
            </td>
        </tr>
    </table>
</div>
