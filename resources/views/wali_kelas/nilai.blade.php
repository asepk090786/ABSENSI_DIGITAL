@extends('layouts.app', ['pageSlug' => 'wali-kelas'])

@section('title', 'Nilai Siswa - Wali Kelas')

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <div>
                        <h4 class="card-title mb-1">
                            <i class="ti ti-chart-bar me-2"></i>Nilai Siswa
                        </h4>
                        <p class="text-muted mb-0">Kelas: <strong>{{ $kelasBinaan->nama_kelas ?? '-' }}</strong></p>
                    </div>
                    <a href="{{ route('wali_kelas.index') }}" class="btn btn-secondary">
                        <i class="ti ti-arrow-left me-1"></i>Kembali
                    </a>
                </div>
                <div class="card-body">
                    @if($siswa->isEmpty())
                        <div class="alert alert-info">
                            <i class="ti ti-info-circle me-2"></i>Belum ada siswa di kelas ini.
                        </div>
                    @else
                        <div class="alert alert-info">
                            <i class="ti ti-info-circle me-2"></i>
                            Halaman ini menampilkan rekap nilai siswa per mata pelajaran. 
                            Detail nilai dapat dilihat di menu <strong>Rekap Nilai</strong>.
                        </div>
                        
                        <div class="table-responsive">
                            <table class="table table-striped table-hover">
                                <thead>
                                    <tr>
                                        <th width="5%">No</th>
                                        <th>NISN</th>
                                        <th>Nama Siswa</th>
                                        <th class="text-center">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($siswa as $index => $s)
                                    <tr>
                                        <td>{{ $index + 1 }}</td>
                                        <td>{{ $s->nisn ?? '-' }}</td>
                                        <td>{{ $s->nama ?? '-' }}</td>
                                        <td class="text-center">
                                            <a href="{{ route('wali_kelas.nilai_siswa', $s->id) }}" 
                                               class="btn btn-sm btn-primary"
                                               title="Lihat nilai">
                                                <i class="ti ti-eye me-1"></i>Lihat Nilai
                                            </a>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <div class="mt-3">
                            <p class="text-muted mb-0">
                                <strong>Total Siswa:</strong> {{ $siswa->count() }} siswa
                            </p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
