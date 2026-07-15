@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header border-0 pt-3 pb-2">
                    <div class="row align-items-center">
                        <div class="col">
                            <h4 class="card-title fw-semibold m-0">Tugas Mengajar - {{ $guru->nama }}</h4>
                            <p class="text-muted mb-0 mt-1">
                                <small>
                                    <i class="ti ti-id me-1"></i>NIP: {{ $guru->nip ?? '-' }}
                                </small>
                            </p>
                        </div>
                        <div class="col-auto">
                            <a href="{{ route('tugas_guru.index') }}" class="btn btn-secondary btn-sm">
                                <i class="ti ti-arrow-left me-1"></i>Kembali
                            </a>
                            <button onclick="window.print()" class="btn btn-sm btn-info btn-modern">
                                <i class="ti ti-printer me-1"></i>Cetak
                            </button>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    @if($tugasGuru->isEmpty())
                        <div class="alert alert-info">
                            <i class="ti ti-info-circle me-2"></i>
                            Guru ini belum memiliki tugas mengajar.
                        </div>
                    @else
                        @php
                            $tingkatList = ['X', 'XI', 'XII'];
                        @endphp

                        @foreach($tingkatList as $tingkat)
                            @php
                                $tugasTingkat = $tugasGuru->get($tingkat, collect());
                            @endphp
                            
                            @if($tugasTingkat->isNotEmpty())
                            <div class="mb-4">
                                <h5 class="border-bottom pb-2 mb-2">
                                    <i class="ti ti-school me-2"></i>Tingkat {{ $tingkat }}
                                </h5>
                                <div class="table-responsive">
                                    <table class="table table-bordered table-hover">
                                        <thead class="table-light">
                                            <tr>
                                                <th width="5%" class="text-center">No</th>
                                                <th width="35%">Mata Pelajaran</th>
                                                <th width="30%">Kelas</th>
                                                <th width="15%" class="text-center">Status</th>
                                                <th width="15%">Keterangan</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($tugasTingkat as $index => $tugas)
                                            <tr>
                                                <td class="text-center">{{ $index + 1 }}</td>
                                                <td>
                                                    <strong>{{ $tugas->mataPelajaran->nama_mapel ?? '-' }}</strong>
                                                </td>
                                                <td>
                                                    @if($tugas->kelas)
                                                        <span class="badge bg-primary text-white">{{ $tugas->kelas->nama_kelas }}</span>
                                                    @else
                                                        <span class="badge bg-warning text-white">Semua Kelas Tingkat {{ $tingkat }}</span>
                                                    @endif
                                                </td>
                                                <td class="text-center">
                                                    @if($tugas->is_active)
                                                        <span class="badge bg-success text-white">Aktif</span>
                                                    @else
                                                        <span class="badge bg-danger text-white">Tidak Aktif</span>
                                                    @endif
                                                </td>
                                                <td>
                                                    <small class="text-muted">{{ $tugas->keterangan ?? '-' }}</small>
                                                </td>
                                            </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                            @endif
                        @endforeach

                        
                        <div class="card bg-light mt-4">
                            <div class="card-body">
                                <h6 class="card-title mb-2">
                                    <i class="ti ti-info-circle me-2"></i>Ringkasan
                                </h6>
                                <div class="row">
                                    <div class="col-md-4">
                                        <p class="mb-2">
                                            <strong>Total Tugas:</strong> 
                                            <span class="badge bg-primary text-white">{{ $tugasGuru->flatten()->count() }}</span>
                                        </p>
                                    </div>
                                    <div class="col-md-4">
                                        <p class="mb-2">
                                            <strong>Tugas Aktif:</strong> 
                                            <span class="badge bg-success text-white">{{ $tugasGuru->flatten()->where('is_active', 1)->count() }}</span>
                                        </p>
                                    </div>
                                    <div class="col-md-4">
                                        <p class="mb-2">
                                            <strong>Mata Pelajaran:</strong> 
                                            <span class="badge bg-info text-white">{{ $tugasGuru->flatten()->pluck('mata_pelajaran_id')->unique()->count() }}</span>
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>


<style>
@media print {
    .btn, .navbar, .sidebar {
        display: none !important;
    }
    .card {
        border: none !important;
        box-shadow: none !important;
    }
    .table {
        font-size: 12px;
    }
}
</style>
@endsection
