@extends('layouts.app', ['pageSlug' => 'wali-kelas'])

@section('title', 'Data Siswa - Wali Kelas')

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <div>
                        <h4 class="card-title mb-1">
                            <i class="ti ti-users me-2"></i>Data Siswa
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
                        <div class="table-responsive">
                            <table class="table table-striped table-hover">
                                <thead>
                                    <tr>
                                        <th width="5%">No</th>
                                        <th>NIS</th>
                                        <th>NISN</th>
                                        <th>Nama Siswa</th>
                                        <th>Jenis Kelamin</th>
                                        <th>Email</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($siswa as $index => $s)
                                    <tr>
                                        <td>{{ $index + 1 }}</td>
                                        <td>{{ $s->nis ?? '-' }}</td>
                                        <td>{{ $s->nisn ?? '-' }}</td>
                                        <td>{{ $s->nama ?? '-' }}</td>
                                        <td>{{ $s->jenis_kelamin ?? '-' }}</td>
                                        <td>{{ $s->email ?? '-' }}</td>
                                        <td>{{ ($s->status_aktif ?? 0) ? 'Aktif' : 'Nonaktif' }}</td>
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
