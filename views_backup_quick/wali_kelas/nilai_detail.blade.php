@extends('layouts.app', ['pageSlug' => 'wali-kelas'])

@section('title', 'Detail Nilai Siswa')

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <div>
                        <h4 class="card-title mb-1">
                            <i class="ti ti-user-check me-2"></i>Detail Nilai Siswa
                        </h4>
                        <p class="text-muted mb-0">
                            Kelas: <strong>{{ $kelasBinaan->nama_kelas ?? '-' }}</strong> | 
                            Siswa: <strong>{{ $siswa->nama ?? '-' }}</strong>
                        </p>
                    </div>
                    <div class="d-flex gap-2">
                        <a href="{{ route('wali_kelas.nilai') }}" class="btn btn-secondary">
                            <i class="ti ti-arrow-left me-1"></i>Kembali
                        </a>
                        <a href="{{ route('rekap_nilai.index', ['wali_kelas' => 1, 'kelas_id' => $kelasBinaan->id]) }}" class="btn btn-primary">
                            <i class="ti ti-report-analytics me-1"></i>Rekap Nilai
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    @if($nilai->isEmpty())
                        <div class="alert alert-info">
                            <i class="ti ti-info-circle me-2"></i>Belum ada data nilai untuk siswa ini.
                        </div>
                    @else
                        <div class="table-responsive">
                            <table class="table table-striped table-hover">
                                <thead>
                                    <tr>
                                        <th width="5%">No</th>
                                        <th>Tanggal</th>
                                        <th>Mata Pelajaran</th>
                                        <th>Komponen</th>
                                        <th class="text-center" width="120">Nilai</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($nilai as $index => $row)
                                    <tr>
                                        <td>{{ $index + 1 }}</td>
                                        <td>{{ $row->tanggal ? \Carbon\Carbon::parse($row->tanggal)->format('d/m/Y') : '-' }}</td>
                                        <td>{{ $row->nama_mapel ?? '-' }}</td>
                                        <td>{{ $row->nama_komponen ?? '-' }}</td>
                                        <td class="text-center">
                                            <span class="badge bg-{{ ($row->nilai ?? 0) >= 75 ? 'success' : (($row->nilai ?? 0) >= 60 ? 'warning' : 'danger') }}">
                                                {{ $row->nilai ?? '-' }}
                                            </span>
                                        </td>
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
