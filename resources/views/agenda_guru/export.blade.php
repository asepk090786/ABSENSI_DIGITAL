@extends('layouts.app', ['pageSlug' => 'agenda_guru'])

@section('title','Export Agenda Mengajar Guru')

@section('content')
<style>
    @media print {
        @page {
            size: A4 landscape;
            margin: 8mm;
        }

        body { margin: 0; padding: 0; }
        .btn-group, .card-header .btn-group { display: none; }
        .no-print { display: none; }

        .journal-table {
            font-size: 10px;
            margin-top: 10px;
        }

        .journal-table th,
        .journal-table td {
            padding: 4px;
        }

        .signature-section {
            margin-top: 12px;
        }

        .signature-name {
            margin: 20px 0 4px 0;
        }
    }
    
    .journal-table {
        width: 100%;
        border-collapse: collapse;
        margin-top: 12px;
        font-size: 12px;
        table-layout: fixed;
    }
    
    .journal-table th, .journal-table td {
        border: 1px solid #222;
        padding: 6px 8px;
        text-align: left;
        vertical-align: middle;
        word-wrap: break-word;
    }
    
    .journal-table th {
        background-color: #f5f5f5;
        font-weight: bold;
        text-align: center;
        line-height: 1.2;
        padding: 6px 4px;
    }

    .journal-table thead tr:nth-child(2) th {
        font-size: 12px;
        padding: 4px 2px;
    }

    .journal-table tbody td {
        height: 46px;
        line-height: 1.25;
    }

    .journal-table td.col-center {
        text-align: center;
    }
    
    .journal-table td.text-center {
        text-align: center;
    }
    
    .page {
        page-break-after: always;
        margin-bottom: 20px;
    }
    
    .header-info {
        text-align: center;
        margin-bottom: 8px;
    }

    /* Center printable content and give it a white background so it looks like a page */
    .print-wrapper {
        max-width: 1120px;
        margin: 8px auto 40px auto;
        background: #fff;
        padding: 10px 18px 24px 18px;
        box-shadow: 0 0 0 rgba(0,0,0,0);
    }
    
    .header-info h3 {
        margin: 3px 0;
        font-size: 15px;
    }
    
    .header-info p {
        margin: 2px 0;
        font-size: 12px;
    }
    
    .signature-section {
        margin-top: 24px;
        display: flex;
        justify-content: space-between;
        padding: 0 30px;
        align-items: flex-end;
    }

    .signature-block {
        width: 45%;
        box-sizing: border-box;
    }
    .signature-block.left { text-align: left; }
    .signature-block.right { text-align: right; }

    .signature-name {
        margin: 28px 0 6px 0;
        font-size: 12px;
        min-height: 18px;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        display: block;
        max-width: 100%;
    }

    .signature-line {
        border-top: 1px solid #000;
        width: 220px;
        margin: 6px 0 12px 0;
        display: inline-block;
    }

    .signature-nip {
        margin: 5px 0 0 0;
        font-size: 12px;
        min-height: 16px;
    }
</style>

@unless(!empty($forPdf) && $forPdf)
<div class="container-fluid no-print">
    <div class="col-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h4 class="card-title fw-semibold m-0">
                    <i class="ti ti-file-pdf me-2"></i>Export PDF - Agenda Mengajar Guru
                </h4>
                <div class="btn-group">
                    <button type="button" class="btn btn-primary btn-sm" onclick="window.print()">
                        <i class="ti ti-printer me-1"></i>Print
                    </button>
                    <a href="{{ route('agenda_guru.export', ['bulan' => $bulan, 'tahun' => $tahunFilter, 'format' => 'pdf']) }}" class="btn btn-success btn-sm">
                        <i class="ti ti-download me-1"></i>Export PDF
                    </a>
                    <a href="{{ route('agenda_guru.index') }}" class="btn btn-secondary btn-sm">
                        <i class="ti ti-x me-1"></i>Kembali
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@endunless

<div class="print-wrapper" style="margin-top: 10px;">
    <div class="header-info">
        <h3 style="font-weight: bold;">AGENDA MENGAJAR GURU</h3>
        <h4 style="font-weight: bold;">(JURNAL HARIAN)</h4>
        
        <div style="margin-top: 20px; text-align: left; margin-left: 50px;">
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
            <div class="signature-block left">
                <strong>Guru Mata Pelajaran</strong>
                <div class="signature-name">{{ $guru->nama ?? '' }}</div>
                <div class="signature-line"></div>
                <div class="signature-nip">{{ !empty($nipGuru) ? 'NIP. ' . $nipGuru : '' }}</div>
            </div>
            <div class="signature-block right">
                <strong>Kepala Sekolah</strong>
                <div class="signature-name">{{ $namaKepalaSekolah ?? '-' }}</div>
                <div class="signature-line"></div>
                <div class="signature-nip">{{ !empty($nipKepalaSekolah) ? 'NIP. ' . $nipKepalaSekolah : '' }}</div>
            </div>
        </div>
    @endif
</div>

<script>
    // Auto print on page load
    // window.print();
</script>

@endsection
