@extends('layouts.app', ['pageSlug' => 'wali-kelas'])

@section('title', 'Laporan Guru ke Wali Kelas')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h3 class="card-title mb-0">Laporan Guru - {{ $kelasBinaan->nama_kelas ?? '-' }}</h3>
                    <a href="{{ route('wali_kelas.index') }}" class="btn btn-secondary btn-sm">
                        <i class="ti ti-arrow-left"></i> Kembali
                    </a>
                </div>
                <div class="card-body">
                    @if($laporanGuru->isEmpty())
                        <div class="alert alert-info mb-0">
                            <i class="ti ti-info-circle me-2"></i>Belum ada laporan dari guru untuk kelas ini.
                        </div>
                    @else
                        <div class="table-responsive">
                            <table class="table table-striped table-hover">
                                <thead>
                                    <tr>
                                        <th>Tanggal</th>
                                        <th>Siswa</th>
                                        <th>Guru Pelapor</th>
                                        <th>Permasalahan</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($laporanGuru as $laporan)
                                        <tr>
                                            <td>{{ \Carbon\Carbon::parse($laporan->created_at)->format('d/m/Y H:i') }}</td>
                                            <td>{{ $laporan->nama_siswa ?? '-' }}<br><small class="text-muted">NIS: {{ $laporan->nis_siswa ?? '-' }}</small></td>
                                            <td>{{ $laporan->nama_guru_pelapor ?? '-' }}</td>
                                            <td>{{ $laporan->deskripsi_permasalahan }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
