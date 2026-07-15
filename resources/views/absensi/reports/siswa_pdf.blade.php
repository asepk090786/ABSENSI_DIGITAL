<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <title>Rekap Absensi Siswa</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size:10px; color:#222; margin:6px }
        .header { margin-bottom:18px }
        h2 { margin:0 0 6px 0 }
        table { width:100%; border-collapse:collapse; margin-top:6px }
        th, td { padding:6px 4px; border-bottom:1px solid #e6e6e6; text-align:left }
        th { background:#fafafa; font-weight:600 }
        .center { text-align:center }
        .small { font-size:11px; color:#666 }
    </style>
</head>
<body>
@php
    $period = $period ?? 'daily';
    $start = $startDate ?? $selectedTanggal;
    $end = $endDate ?? $selectedTanggal;
@endphp

<div class="header">
    <h2>
        @if($period === 'daily')
            Tabel Rekap Absensi Siswa Harian
        @elseif($period === 'weekly')
            Tabel Rekap Absensi Mingguan
        @else
            Tabel Rekap Absensi Bulanan
        @endif
    </h2>
    <div class="small">Kelas: {{ $kelasLabel ?? '-' }} &nbsp;|&nbsp; Periode: {{ $period === 'daily' ? \Carbon\Carbon::parse($selectedTanggal)->format('d-m-Y') : (\Carbon\Carbon::parse($start)->format('d-m-Y') . ' s/d ' . \Carbon\Carbon::parse($end)->format('d-m-Y')) }}</div>
</div>

@if($period === 'daily')
    <table>
        <thead>
            <tr>
                <th style="width:4%">No</th>
                <th>Nama Siswa</th>
                @if(!empty($jamColumns) && is_array($jamColumns))
                    @foreach($jamColumns as $jc)
                        <th style="width:6%" class="center">J{{ $loop->iteration }}</th>
                    @endforeach
                @else
                    <th style="width:6%" class="center">Hadir</th>
                    <th style="width:6%" class="center">Sakit</th>
                    <th style="width:6%" class="center">Izin</th>
                    <th style="width:6%" class="center">Alpa</th>
                @endif
                <th style="width:8%" class="center">Total Absen</th>
                <th style="width:12%" class="center">% Kehadiran</th>
            </tr>
        </thead>
        <tbody>
            @foreach($laporanRows as $i => $row)
                <tr>
                    <td class="center">{{ $i + 1 }}</td>
                    <td>{{ $row->nama_siswa }}</td>
                    @if(!empty($jamColumns) && is_array($jamColumns))
                        @php
                            $absenCount = 0;
                            $hadirCount = 0;
                            $totalJams = count($jamColumns);
                        @endphp
                        @foreach($jamColumns as $jc)
                            @php
                                $st = $row->statuses[$jc['id']] ?? '-';
                                if ($st === 'Absen') $absenCount++;
                                if ($st === 'Hadir') $hadirCount++;
                                $display = '-';
                                if ($st === 'Hadir') $display = 'H';
                                elseif ($st === 'Terlambat') $display = 'T';
                                elseif ($st === 'Sakit') $display = 'S';
                                elseif ($st === 'Izin') $display = 'I';
                                elseif ($st === 'Absen') $display = 'A';
                            @endphp
                            <td class="center">{{ $display }}</td>
                        @endforeach
                        @php $attendancePercent = $totalJams ? round(($hadirCount / $totalJams) * 100, 1) : 0; @endphp
                        <td class="center">{{ $absenCount }}</td>
                        <td class="center">{{ $attendancePercent }}%</td>
                    @else
                        <td class="center">{!! $row->status === 'Hadir' ? '&#10003;' : '' !!}</td>
                        <td class="center">{!! $row->status === 'Sakit' ? '&#10003;' : '' !!}</td>
                        <td class="center">{!! $row->status === 'Izin' ? '&#10003;' : '' !!}</td>
                        <td class="center">{!! in_array($row->status, ['Absen','-']) ? '&#10003;' : '' !!}</td>
                        <td class="center">-</td>
                        <td class="center">-</td>
                    @endif
                </tr>
            @endforeach

            @if(!empty($jamColumns) && is_array($jamColumns))
                {{-- Totals per jam (Hadir counts) --}}
                <tr style="font-weight:700;background:#f8f9fa">
                    <td class="center">-</td>
                    <td></td>
                    <td class="center">Total Hadir</td>
                    <td></td>
                    @foreach($jamColumns as $jc)
                        @php
                            $count = 0;
                            foreach($laporanRows as $r) {
                                if(isset($r->statuses[$jc['id']]) && $r->statuses[$jc['id']] === 'Hadir') $count++;
                            }
                        @endphp
                        <td class="center">{{ $count }}</td>
                    @endforeach
                    <td></td>
                </tr>

                {{-- Summary --}}
                @php
                    $totalStudents = $laporanRows->count();
                    $sumHadir = 0;
                    foreach($jamColumns as $jc) {
                        foreach($laporanRows as $r) {
                            if(isset($r->statuses[$jc['id']]) && $r->statuses[$jc['id']] === 'Hadir') $sumHadir++;
                        }
                    }
                    $totalPossible = $totalStudents * count($jamColumns);
                    $attendancePercent = $totalPossible ? round(($sumHadir / $totalPossible) * 100, 1) : 0;
                @endphp
                <tr>
                    <td colspan="{{ 4 + count($jamColumns) }}" style="border:none;padding-top:10px">
                        <div style="font-weight:600">Ringkasan Kehadiran: &nbsp;
                            Jumlah Siswa: {{ $totalStudents }} &nbsp;|&nbsp;
                            Total Hadir (semua jam): {{ $sumHadir }} &nbsp;|&nbsp;
                            Persentase Kehadiran: {{ $attendancePercent }}%
                        </div>
                    </td>
                </tr>
            @else
                {{-- existing totals when no jamColumns --}}
                @php
                    $totalHadir = $laporanRows->where('status','Hadir')->count();
                    $totalTerlambat = $laporanRows->where('status','Terlambat')->count();
                    $totalSakit = $laporanRows->where('status','Sakit')->count();
                    $totalIzin = $laporanRows->where('status','Izin')->count();
                    $totalAlpa = $laporanRows->where('status','Absen')->count();
                    $totalStudents = $laporanRows->count();
                    $totalNotPresent = $totalStudents - $totalHadir;
                    $attendancePercent = $totalStudents ? round(($totalHadir / $totalStudents) * 100, 1) : 0;
                @endphp
                <tr style="font-weight:700;background:#f8f9fa">
                    <td class="center">-</td>
                    <td></td>
                    <td class="center">Total</td>
                    <td></td>
                    <td class="center">{{ $totalHadir }}</td>
                    <td class="center">{{ $totalSakit }}</td>
                    <td class="center">{{ $totalIzin }}</td>
                    <td class="center">{{ $totalAlpa }}</td>
                    <td></td>
                </tr>
                <tr>
                    <td colspan="9" style="border:none;padding-top:10px">
                        <div style="font-weight:600">Ringkasan Kehadiran: &nbsp;
                            Jumlah Siswa: {{ $totalStudents }} &nbsp;|&nbsp;
                            Hadir: {{ $totalHadir }} &nbsp;|&nbsp;
                            Tidak Hadir: {{ $totalNotPresent }} &nbsp;|&nbsp;
                            Persentase Kehadiran: {{ $attendancePercent }}%
                        </div>
                    </td>
                </tr>
            @endif
        </tbody>
    </table>

@else
    @php
        $firstRow = $laporanRows->first();
        $isAggregated = isset($firstRow->hadir_count) || isset($firstRow->hadir);
        if ($isAggregated) {
            // aggregated per-student rows
            $totalDays = $laporanRows->first()->total_days ?? (\Carbon\Carbon::parse($end)->diffInDays(\Carbon\Carbon::parse($start)) + 1);
            $grouped = $laporanRows->keyBy('siswa_id');
        } else {
            $dates = $laporanRows->pluck('tanggal')->unique()->values();
            $totalDays = $dates->count() ?: 1;
            $grouped = $laporanRows->groupBy('siswa_id');
        }
    @endphp
    <table>
        <thead>
            <tr>
                <th style="width:4%">No</th>
                <th>Nama Siswa</th>
                <th style="width:12%">Kelas</th>
                @if($period === 'weekly')
                    <th style="width:10%">Minggu Ke</th>
                @else
                    <th style="width:12%">Bulan</th>
                @endif
                <th style="width:8%" class="center">Hadir</th>
                <th style="width:8%" class="center">Sakit</th>
                <th style="width:8%" class="center">Izin</th>
                <th style="width:8%" class="center">Alpa</th>
                <th style="width:10%" class="center">{{ $period === 'monthly' ? 'Total Hari Efektif' : ' ' }}</th>
                <th style="width:12%" class="center">Persentase Kehadiran</th>
            </tr>
        </thead>
        <tbody>
            @if($isAggregated)
                @foreach($grouped as $siswaId => $row)
                    @php
                        $hadir = (int) ($row->hadir_count ?? $row->hadir ?? 0);
                        $sakit = (int) ($row->sakit_count ?? $row->sakit ?? 0);
                        $izin = (int) ($row->izin_count ?? $row->izin ?? 0);
                        $alfa = (int) ($row->alfa_count ?? $row->alfa ?? 0);
                        $totalSessions = $hadir + $sakit + $izin + $alfa;
                        $percent = $totalSessions ? round(($hadir / $totalSessions) * 100, 1) : 0;
                    @endphp
                    <tr>
                        <td class="center">{{ $loop->index + 1 }}</td>
                        <td>{{ $row->nama_siswa }}</td>
                        <td>{{ $row->nama_kelas }}</td>
                        <td class="center">{{ $period === 'weekly' ? (\Carbon\Carbon::parse($start)->format('d/m/Y') . ' - ' . \Carbon\Carbon::parse($end)->format('d/m/Y')) : \Carbon\Carbon::parse($start)->format('F Y') }}</td>
                        <td class="center">{{ $hadir }}</td>
                        <td class="center">{{ $sakit }}</td>
                        <td class="center">{{ $izin }}</td>
                        <td class="center">{{ $alfa }}</td>
                        @if($period === 'monthly')
                            <td class="center">{{ $totalDays }}</td>
                        @else
                            <td></td>
                        @endif
                        <td class="center">{{ $percent }}%</td>
                    </tr>
                @endforeach
            @else
                @foreach($grouped as $idx => $group)
                    @php
                        $first = $group->first();
                        $hadir = $group->where('status','Hadir')->count();
                        $sakit = $group->where('status','Sakit')->count();
                        $izin = $group->where('status','Izin')->count();
                        $alfa = $group->where('status','Absen')->count();
                        $percent = $totalDays ? round(($hadir / $totalDays) * 100, 1) : 0;
                    @endphp
                    <tr>
                        <td class="center">{{ $loop->index + 1 }}</td>
                        <td>{{ $first->nama_siswa }}</td>
                        <td>{{ $first->nama_kelas }}</td>
                        <td class="center">{{ $period === 'weekly' ? 'Minggu 1' : \Carbon\Carbon::parse($start)->format('F Y') }}</td>
                        <td class="center">{{ $hadir }}</td>
                        <td class="center">{{ $sakit }}</td>
                        <td class="center">{{ $izin }}</td>
                        <td class="center">{{ $alfa }}</td>
                        @if($period === 'monthly')
                            <td class="center">{{ $totalDays }}</td>
                        @else
                            <td></td>
                        @endif
                        <td class="center">{{ $percent }}%</td>
                    </tr>
                @endforeach
            @endif
            {{-- Totals for weekly/monthly --}}
            @php
                if ($isAggregated ?? false) {
                    $totalStudents = $grouped->count();
                    $sumHadir = $laporanRows->sum(fn($r) => (int) ($r->hadir_count ?? $r->hadir ?? 0));
                    $sumSakit = $laporanRows->sum(fn($r) => (int) ($r->sakit_count ?? $r->sakit ?? 0));
                    $sumIzin = $laporanRows->sum(fn($r) => (int) ($r->izin_count ?? $r->izin ?? 0));
                    $sumAlpa = $laporanRows->sum(fn($r) => (int) ($r->alfa_count ?? $r->alfa ?? 0));
                    $sumTotalSessions = $sumHadir + $sumSakit + $sumIzin + $sumAlpa;
                } else {
                    $totalStudents = $grouped->count();
                    $sumHadir = 0; $sumSakit = 0; $sumIzin = 0; $sumAlpa = 0; $sumTotalSessions = 0;
                    foreach($grouped as $g) {
                        $sumHadir += $g->where('status','Hadir')->count();
                        $sumSakit += $g->where('status','Sakit')->count();
                        $sumIzin += $g->where('status','Izin')->count();
                        $sumAlpa += $g->where('status','Absen')->count();
                        $sumTotalSessions += $g->count();
                    }
                }
                $totalPossible = $sumTotalSessions;
                $overallPercent = $totalPossible ? round(($sumHadir / $totalPossible) * 100, 1) : 0;
            @endphp
            <tr style="font-weight:700;background:#f8f9fa">
                <td class="center">-</td>
                <td>Total</td>
                <td></td>
                <td></td>
                <td class="center">{{ $sumHadir }}</td>
                <td class="center">{{ $sumSakit }}</td>
                <td class="center">{{ $sumIzin }}</td>
                <td class="center">{{ $sumAlpa }}</td>
                @if($period === 'monthly')
                    <td class="center">{{ $totalDays }}</td>
                    <td class="center">{{ $overallPercent }}%</td>
                @else
                    <td></td>
                    <td class="center">{{ $overallPercent }}%</td>
                @endif
            </tr>
        </tbody>
    </table>
@endif

</body>
</html>
