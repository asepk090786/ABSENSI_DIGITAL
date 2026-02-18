@extends('layouts.app', ['pageSlug' => 'agenda_guru'])

@section('title','Export Agenda Mengajar Guru')

@section('content')
<style>
    @media print {
        body { margin: 0; padding: 0; }
        .btn-group, .card-header .btn-group { display: none; }
        .no-print { display: none; }
    }
    
    .journal-table {
        width: 100%;
        border-collapse: collapse;
        margin-top: 20px;
        font-size: 13px;
    }
    
    .journal-table th, .journal-table td {
        border: 1px solid #000;
        padding: 8px;
        text-align: left;
        vertical-align: middle;
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
        height: 42px;
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
        margin-bottom: 15px;
    }
    
    .header-info h3 {
        margin: 5px 0;
        font-size: 16px;
    }
    
    .header-info p {
        margin: 3px 0;
        font-size: 12px;
    }
    
    .signature-section {
        margin-top: 30px;
        display: flex;
        justify-content: space-between;
        padding-right: 50px;
    }
    
    .signature-block {
        text-align: center;
        width: 150px;
    }

    .signature-name {
        margin: 50px 0 6px 0;
        font-size: 12px;
        min-height: 18px;
    }

    .signature-line {
        border-top: 1px solid #000;
        width: 100%;
        margin: 0;
    }

    .signature-nip {
        margin: 5px 0 0 0;
        font-size: 12px;
        min-height: 16px;
    }
</style>

<div class="container-fluid no-print">
    <div class="col-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h4 class="card-title mb-0">
                    <i class="ti ti-file-pdf me-2"></i>Export PDF - Agenda Mengajar Guru
                </h4>
                <div class="btn-group">
                    <button type="button" class="btn btn-primary btn-sm" onclick="window.print()">
                        <i class="ti ti-printer me-1"></i>Print
                    </button>
                    <a href="{{ route('agenda_guru.index') }}" class="btn btn-secondary btn-sm">
                        <i class="ti ti-x me-1"></i>Kembali
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<div style="page-break-after: always; margin-top: 20px;">
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
                    <th style="width: 4%;" rowspan="2">No</th>
                    <th style="width: 10%;" rowspan="2">Hari/Tanggal</th>
                    <th style="width: 10%;" rowspan="2">Jam Pelajaran</th>
                    <th style="width: 26%;" rowspan="2">Materi ajar</th>
                    <th style="width: 15%;" colspan="3">Kehadiran siswa</th>
                    <th style="width: 10%;" rowspan="2">Jumlah Hadir</th>
                    <th style="width: 12%;" rowspan="2">Jumlah Tidak hadir</th>
                    <th style="width: 6%;" rowspan="2">Paraf</th>
                </tr>
                <tr>
                    <th style="width: 5%;">Sakit</th>
                    <th style="width: 5%;">Izin</th>
                    <th style="width: 5%;">Alpa</th>
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
                                {{ $item->jamBelajar->jam_mulai }}<br>
                                {{ $item->jamBelajar->jam_selesai }}
                            @else
                                -
                            @endif
                        </td>
                        <td>
                            @if($item->kelas)
                                [{{ $item->kelas->nama_kelas }}] 
                            @endif
                            {{ Str::limit(strip_tags($item->kegiatan), 200) }}
                        </td>
                        <td class="col-center">{{ $absensiSummary['sakit'] > 0 ? $absensiSummary['sakit'] : '-' }}</td>
                        <td class="col-center">{{ $absensiSummary['izin'] > 0 ? $absensiSummary['izin'] : '-' }}</td>
                        <td class="col-center">{{ $absensiSummary['absen'] > 0 ? $absensiSummary['absen'] : '-' }}</td>
                        <td class="col-center">{{ $absensiSummary['hadir'] > 0 ? $absensiSummary['hadir'] : '-' }}</td>
                        <td class="col-center">{{ $jumlahTidakHadir > 0 ? $jumlahTidakHadir : '-' }}</td>
                        <td></td>
                    </tr>
                @endforeach
                
                <!-- Add empty rows for printing -->
                @for($i = $agendaList->count(); $i < 20; $i++)
                    <tr>
                        <td class="text-center">{{ $i + 1 }}</td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                    </tr>
                @endfor
            </tbody>
        </table>

        <div class="signature-section">
            <div class="signature-block">
                <strong>Guru Mata Pelajaran</strong>
                <div class="signature-name">{{ $guru->nama ?? '' }}</div>
                <div class="signature-line"></div>
                <div class="signature-nip">{{ !empty($nipGuru) ? 'NIP. ' . $nipGuru : '' }}</div>
            </div>
            <div class="signature-block">
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
