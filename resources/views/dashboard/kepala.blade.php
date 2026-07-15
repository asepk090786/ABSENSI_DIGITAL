@extends('layouts.app')

@section('title','Dashboard Kepala Sekolah')

@section('content')
<div class="welcome-banner">
    <h3><i class="ti ti-user-star me-2"></i>Dashboard Kepala Sekolah</h3>
    <p>Pantau statistik sekolah, absensi, dan laporan dari panel ini.</p>
</div>

<div class="row mb-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title"><i class="ti ti-printer me-2 text-primary"></i>Printout Laporan Kehadiran</h5>
            </div>
            <div class="card-body">
                @php
                    $tanggalLaporan = request('tanggal_laporan', now()->format('Y-m-d'));
                    $kelasLaporanId = request('kelas_laporan_id');
                @endphp
                <form class="row g-2 align-items-end" method="GET" action="{{ route('absensi.laporan-siswa.print') }}" target="_blank">
                    <div class="col-md-3">
                        <label class="form-label">Tanggal Laporan</label>
                        <input type="date" class="form-control" name="tanggal" value="{{ $tanggalLaporan }}">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Kelas (Opsional)</label>
                        <select class="form-select" name="kelas_id">
                            <option value="">Semua Kelas</option>
                            @foreach(($kelasLaporanOptions ?? collect()) as $kelasOption)
                                <option value="{{ $kelasOption->id }}" {{ (string) $kelasLaporanId === (string) $kelasOption->id ? 'selected' : '' }}>{{ $kelasOption->nama_kelas }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-5 d-flex flex-wrap gap-2">
                        <button type="submit" class="btn btn-outline-primary">
                            <i class="ti ti-printer me-1"></i>Print Laporan Kehadiran Siswa
                        </button>
                        <a href="{{ route('absensi.laporan-guru.print', ['tanggal' => $tanggalLaporan]) }}" target="_blank" class="btn btn-outline-success">
                            <i class="ti ti-printer me-1"></i>Print Laporan Kehadiran Guru
                        </a>
                    </div>
                </form>
                <div class="text-muted small mt-2">
                    Laporan guru difilter berdasarkan tanggal. Laporan siswa bisa difilter tanggal dan kelas.
                </div>
            </div>
        </div>
    </div>
</div>
@endsection