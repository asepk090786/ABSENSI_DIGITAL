@extends('layouts.app')

@section('title', 'Rekap Absensi Bulanan')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-4">
        <div>
            <h3 class="mb-1">Rekap Absensi Bulanan</h3>
            <p class="text-muted mb-0">Daftar rekap absensi per bulan untuk setiap kelas yang diajar.</p>
        </div>
        <a href="{{ route('absensi.index') }}" class="btn btn-primary">
            <i class="ti ti-arrow-left me-1"></i> Kembali ke Absensi
        </a>
    </div>

    @if(($rekapBulanan ?? collect())->isEmpty())
        <div class="alert alert-info">
            <i class="ti ti-info-circle me-2"></i>
            Belum ada data rekap absensi bulanan untuk tahun ajaran dan semester aktif.
        </div>
    @else
        <div class="card">
            <div class="card-header border-0 pt-3 pb-2">
                <h5 class="card-title fw-semibold mb-1">Rekap per Kelas & Bulan</h5>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-vcenter table-hover table-bordered mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Kelas</th>
                                <th>Bulan</th>
                                <th class="text-center">Pertemuan</th>
                                <th class="text-center">Hadir</th>
                                <th class="text-center">Terlambat</th>
                                <th class="text-center">Izin</th>
                                <th class="text-center">Sakit</th>
                                <th class="text-center">Alpha</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php
                                $bulanLabels = [
                                    1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
                                    5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
                                    9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
                                ];
                            @endphp
                            @foreach($rekapBulanan as $rekap)
                                <tr>
                                    <td class="fw-medium">{{ $rekap->nama_kelas }}</td>
                                    <td>{{ $bulanLabels[$rekap->bulan ?? 0] ?? ($rekap->bulan ?? '-') }} {{ $rekap->tahun }}</td>
                                    <td class="text-center">{{ (int) ($rekap->total_pertemuan ?? 0) }}</td>
                                    <td class="text-center text-success fw-semibold">{{ (int) ($rekap->hadir ?? 0) }}</td>
                                    <td class="text-center text-warning fw-semibold">{{ (int) ($rekap->terlambat ?? 0) }}</td>
                                    <td class="text-center text-info fw-semibold">{{ (int) ($rekap->izin ?? 0) }}</td>
                                    <td class="text-center text-primary fw-semibold">{{ (int) ($rekap->sakit ?? 0) }}</td>
                                    <td class="text-center text-danger fw-semibold">{{ (int) ($rekap->alpha ?? 0) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    @endif
</div>
@endsection
