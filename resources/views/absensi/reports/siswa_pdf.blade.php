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

@if($period === 'daily')
    @php $grouped = $laporanRows->groupBy('nama_kelas'); @endphp

    @foreach($grouped as $kelasName => $rows)
        <h4 style="margin:10px 0 4px 0">Kelas: {{ $kelasName }}</h4>
        <table>
            <thead>
                <tr>
                    <th style="width:4%">No</th>
                    <th style="width:12%">NIS</th>
                    <th style="width:12%">NISN</th>
                    <th>Nama Siswa</th>
                    <th style="width:12%">Jenis Kelamin</th>
                    <th style="width:12%" class="center">Kehadiran</th>
                </tr>
            </thead>
            <tbody>
                @foreach($rows as $i => $row)
                    <tr>
                        <td class="center">{{ $i + 1 }}</td>
                        <td class="center">{{ $row->nis ?? '-' }}</td>
                        <td class="center">{{ $row->nisn ?? '-' }}</td>
                        <td>{{ $row->nama_siswa }}</td>
                        <td class="center">{{ $row->jenis_kelamin ?? '-' }}</td>
                        <td class="center">{{ $row->status ?? '-' }}</td>
                    </tr>
                @endforeach

                @php
                    $totalHadir = $rows->where('status','Hadir')->count();
                    $totalTerlambat = $rows->where('status','Terlambat')->count();
                    $totalSakit = $rows->where('status','Sakit')->count();
                    $totalIzin = $rows->where('status','Izin')->count();
                    $totalAlpa = $rows->where('status','Absen')->count();
                    $totalStudents = $rows->count();
                    $attendancePercent = $totalStudents ? round(($totalHadir / $totalStudents) * 100, 1) : 0;
                @endphp

                <tr style="font-weight:700;background:#f8f9fa">
                    <td colspan="2">&nbsp;</td>
                    <td colspan="1">Total</td>
                    <td class="center">Hadir: {{ $totalHadir }}</td>
                    <td class="center">Tidak Hadir: {{ $totalStudents - $totalHadir }}</td>
                    <td class="center">Persentase: {{ $attendancePercent }}%</td>
                </tr>
                <tr style="font-size:11px">
                    <td colspan="6">Rincian: Hadir {{ $totalHadir }} &nbsp;|&nbsp; Terlambat {{ $totalTerlambat }} &nbsp;|&nbsp; Sakit {{ $totalSakit }} &nbsp;|&nbsp; Izin {{ $totalIzin }} &nbsp;|&nbsp; Alpa {{ $totalAlpa }}</td>
                </tr>
            </tbody>
        </table>
    @endforeach
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
