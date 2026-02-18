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
    }
    
    .journal-table th {
        background-color: #f5f5f5;
        font-weight: bold;
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
    
    .signature-block p {
        margin: 50px 0 0 0;
        border-top: 1px solid #000;
        padding-top: 5px;
        font-size: 12px;
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
                    <th style="width: 5%;">NO</th>
                    <th style="width: 12%;">HARI / TGL</th>
                    <th style="width: 12%;">JAM PELAJARAN</th>
                    <th style="width: 30%;">MATERI AJAR</th>
                    <th style="width: 15%;">KEHADIRAN SISWA</th>
                    <th style="width: 10%;">JML HADIR</th>
                    <th style="width: 10%;">JML TDK HADIR</th>
                    <th style="width: 6%;">PARAF</th>
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
                        <td class="text-center">
                            <table style="width: 100%; border: none; margin: 0;">
                                <tr style="border: none;">
                                    <td style="border: none; text-align: center; padding: 2px;">S</td>
                                    <td style="border: none; text-align: center; padding: 2px;">I</td>
                                    <td style="border: none; text-align: center; padding: 2px;">A</td>
                                </tr>
                                <tr style="border: none;">
                                    <td style="border: none; height: 20px;"></td>
                                    <td style="border: none;"></td>
                                    <td style="border: none;"></td>
                                </tr>
                            </table>
                        </td>
                        <td style="height: 40px;"></td>
                        <td style="height: 40px;"></td>
                        <td style="height: 40px;"></td>
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
                        <td style="height: 40px;"></td>
                        <td style="height: 40px;"></td>
                        <td style="height: 40px;"></td>
                    </tr>
                @endfor
            </tbody>
        </table>

        <div class="signature-section">
            <div class="signature-block">
                <strong>Guru Mata Pelajaran</strong>
                <p>{{ $guru->nama ?? '' }}</p>
            </div>
            <div class="signature-block">
                <strong>Kepala Sekolah</strong>
                <p></p>
            </div>
        </div>
    @endif
</div>

<script>
    // Auto print on page load
    // window.print();
</script>

@endsection
