<style>
    @page {
        size: A4 landscape;
        margin: 8mm;
    }

    body { margin: 0; padding: 0; font-family: DejaVu Sans, sans-serif; }

    .journal-table {
        width: 100%;
        border-collapse: collapse;
        margin-top: 8px;
        font-size: 11px;
        table-layout: fixed;
    }

    .journal-table th, .journal-table td {
        border: 1px solid #222;
        padding: 4px 6px;
        text-align: left;
        vertical-align: middle;
        word-wrap: break-word;
    }

    .journal-table th {
        background-color: #f5f5f5;
        font-weight: bold;
        text-align: center;
        line-height: 1.2;
        padding: 4px 3px;
    }

    .journal-table thead tr:nth-child(2) th {
        font-size: 11px;
        padding: 3px 2px;
    }

    .journal-table tbody td {
        height: 38px;
        line-height: 1.2;
    }

    .journal-table td.col-center {
        text-align: center;
    }

    .journal-table td.text-center {
        text-align: center;
    }

    .page {
        page-break-after: always;
        margin-bottom: 10px;
    }

    .header-info {
        text-align: center;
        margin-bottom: 6px;
    }

    .header-info h3 {
        margin: 2px 0;
        font-size: 14px;
    }

    .header-info h4 {
        margin: 2px 0;
        font-size: 13px;
    }

    .header-info p {
        margin: 1px 0;
        font-size: 11px;
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

    .signature-block.left { text-align: left; }
    .signature-block.right { text-align: right; }

    .signature-label {
        font-weight: bold;
        font-size: 11px;
        margin-bottom: 2px;
    }

    .signature-name {
        font-size: 11px;
        min-height: 14px;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        display: block;
        max-width: 100%;
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
    <h3 style="font-weight: bold;">AGENDA MENGAJAR GURU</h3>
    <h4 style="font-weight: bold;">(JURNAL HARIAN)</h4>

    <div style="margin-top: 12px; text-align: left; margin-left: 40px;">
        <p><strong>Nama Guru</strong>: {{ $guru->nama ?? '-' }}</p>
        <p><strong>Nama Sekolah</strong>: {{ $sekolah->nama_sekolah ?? '-' }}</p>
        <p><strong>Mata Pelajaran</strong>: {{ $mataPelajaran ? $mataPelajaran->nama_mapel : '-' }}</p>
        <p><strong>Bulan</strong>: {{ $monthName[$bulan] }}</p>
    </div>
</div>

@if($agendaList->isEmpty())
    <div style="padding: 20px; text-align: center; color: #666;">
        <p>Belum ada data agenda untuk bulan {{ $monthName[$bulan] }} {{ $tahunFilter }}</p>
    </div>
@else
    <table class="journal-table">
        <thead>
            <tr>
                <th style="width: 4%;">No</th>
                <th style="width: 10%;">Hari/Tanggal</th>
                <th style="width: 10%;">Jam Pelajaran</th>
                <th style="width: 10%;">Jenis Kegiatan</th>
                <th style="width: 24%;">Materi ajar</th>
                <th style="width: 34%;">Keterangan/Uraian Kegiatan</th>
                <th style="width: 8%;">Paraf</th>
            </tr>
        </thead>
        <tbody>
            @php
                $no = 1;
            @endphp
            @foreach($agendaList as $item)
                @php
                    $dayName = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
                    $day = $dayName[$item->tanggal->dayOfWeek];
                    $absensiSummary = $item->getAbsensiSummary();
                    $jumlahTidakHadir = $absensiSummary['absen'] + $absensiSummary['izin'] + $absensiSummary['sakit'];
                @endphp
                <tr>
                    <td class="text-center">{{ $no++ }}</td>
                    <td>{{ $day }}<br>{{ $item->tanggal->format('d/m/Y') }}</td>
                    <td class="text-center">
                            @if($item->jamBelajar)
                                @if(!empty($item->jamBelajar->urutan))
                                    <div class="fw-semibold">Jam Ke-{{ $item->jamBelajar->urutan }}</div>
                                @endif
                                <div>{{ $item->jamBelajar->jam_mulai }} - {{ $item->jamBelajar->jam_selesai }}</div>
                            @else
                                -
                            @endif
                    </td>
                    <td class="col-center">
                        {{ ($item->jenis_kegiatan ?? 'kbm') === 'pengembangan_diri' ? 'Pengembangan Diri' : 'KBM' }}
                    </td>
                    <td style="line-height: 1.2;">
                        {{ Str::limit(strip_tags($item->kegiatan), 120) }}
                    </td>
                    <td style="line-height: 1.2; font-size: 10px;">
                        <div>
                            {{ Str::limit(strip_tags(($item->catatan_tambahan ?? '') ?: ($item->kegiatan ?? '')), 90) }}
                        </div>
                        @if(($item->jenis_kegiatan ?? 'kbm') === 'kbm')
                            <div style="border-top: 1px solid #000; margin-top: 3px; padding-top: 3px;">
                                <div>S: {{ $absensiSummary['sakit'] > 0 ? $absensiSummary['sakit'] : '-' }}, I: {{ $absensiSummary['izin'] > 0 ? $absensiSummary['izin'] : '-' }}, A: {{ $absensiSummary['absen'] > 0 ? $absensiSummary['absen'] : '-' }}</div>
                                <div>Hadir: {{ $absensiSummary['hadir'] > 0 ? $absensiSummary['hadir'] : '-' }} | Tidak: {{ $jumlahTidakHadir > 0 ? $jumlahTidakHadir : '-' }}</div>
                            </div>
                        @endif
                    </td>
                    <td></td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="signature-section">
        <table class="signature-table">
            <tr>
                <td class="signature-block left">
                    <div class="signature-label">Guru Mata Pelajaran</div><br>
                    <div class="signature-name">{{ $guru->nama ?? '' }}</div>
                    <div class="signature-line"></div>
                    <div class="signature-nip">{{ !empty($nipGuru) ? 'NIP. ' . $nipGuru : '' }}</div>
                </td>
                <td class="signature-block right">
                    <div class="signature-label">Kepala Sekolah</div><br>
                    <div class="signature-name">{{ $namaKepalaSekolah ?? '-' }}</div>
                    <div class="signature-line"></div>
                    <div class="signature-nip">{{ !empty($nipKepalaSekolah) ? 'NIP. ' . $nipKepalaSekolah : '' }}</div>
                </td>
            </tr>
        </table>
    </div>
@endif
