@extends('layouts.app', ['pageSlug' => 'nilai_akhir_detail'])

@section('title', 'Detail Penilaian - ' . ($siswa->nama ?? 'Siswa'))

@section('content')
<div class="row">
    <div class="col-md-12">
        <div class="card mb-3">
            <div class="card-header border-0 pt-3 pb-2">
                <div class="row align-items-center">
                    <div class="col">
                        <h4 class="card-title fw-semibold m-0">Detail Penilaian</h4>
                    </div>
                    <div class="col-auto">
                        <a href="{{ route('nilai_akhir.index', ['kelas_id' => $kelasId, 'mapel_id' => $mapelId]) }}" class="btn btn-secondary">
                            <i class="ti ti-arrow-left me-1"></i>Kembali
                        </a>
                    </div>
                </div>
            </div>
            <div class="card-body">
                <div class="row mb-4">
                    <div class="col-md-6">
                        <table class="table table-borderless">
                            <tr><th width="150">Nama</th><td>{{ $siswa->nama }}</td></tr>
                            <tr><th>NIS</th><td>{{ $siswa->nis ?? '-' }}</td></tr>
                            <tr><th>NISN</th><td>{{ $siswa->nisn ?? '-' }}</td></tr>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        @if($assessment['is_complete'])
            <div class="row mb-3">
                <div class="col-md-12">
                    <div class="card bg-primary text-white">
                        <div class="card-body text-center">
                            <h5 class="card-title">NILAI AKHIR</h5>
                            <h1 class="display-2 mb-0">{{ number_format($assessment['final_score'], 2) }}</h1>
                            <div class="mt-2">
                                <span class="badge bg-light text-dark fs-5">{{ $assessment['predicate']['grade'] }} - {{ $assessment['predicate']['description'] }}</span>
                            </div>
                            <div class="mt-2">
                                <span class="badge {{ $assessment['status'] === 'TUNTAS' ? 'bg-success' : 'bg-danger' }} fs-6">
                                    {{ $assessment['status'] }}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row mb-3">
                <div class="col-md-4">
                    <div class="card">
                        <div class="card-header bg-light">
                            <h5 class="card-title mb-0">Kognitif</h5>
                        </div>
                        <div class="card-body">
                            <h2 class="text-primary">{{ number_format($assessment['kognitif'], 2) }}</h2>
                            <div class="progress mt-2" style="height: 20px;">
                                <div class="progress-bar bg-primary" role="progressbar" style="width: {{ $assessment['kognitif'] }}%;" aria-valuenow="{{ $assessment['kognitif'] }}" aria-valuemin="0" aria-valuemax="100">
                                    {{ number_format($assessment['kognitif'], 0) }}%
                                </div>
                            </div>
                            <button type="button" class="btn btn-sm btn-outline-primary mt-2 w-100" data-bs-toggle="collapse" data-bs-target="#kognitifDetail">
                                Lihat Detail
                            </button>
                            <div class="collapse mt-2" id="kognitifDetail">
                                <table class="table table-sm table-bordered">
                                    <thead><tr><th>Komponen</th><th class="text-center">Bobot</th><th class="text-center">Nilai</th></tr></thead>
                                    <tbody>
                                        @foreach($kognitifDetail['components'] as $comp)
                                            <tr>
                                                <td>{{ $comp['nama_komponen'] }}</td>
                                                <td class="text-center">{{ $comp['bobot'] }}</td>
                                                <td class="text-center">{{ number_format($comp['nilai'], 2) }}</td>
                                            </tr>
                                        @endforeach
                                        <tr class="table-light fw-bold">
                                            <td>Rata-rata</td>
                                            <td class="text-center">{{ $kognitifDetail['total_bobot'] }}</td>
                                            <td class="text-center">{{ number_format($kognitifDetail['rata_rata'], 2) }}</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card">
                        <div class="card-header bg-light">
                            <h5 class="card-title mb-0">Afektif</h5>
                        </div>
                        <div class="card-body">
                            <h2 class="text-success">{{ number_format($assessment['afektif'], 2) }}</h2>
                            <div class="progress mt-2" style="height: 20px;">
                                <div class="progress-bar bg-success" role="progressbar" style="width: {{ $assessment['afektif'] }}%;" aria-valuenow="{{ $assessment['afektif'] }}" aria-valuemin="0" aria-valuemax="100">
                                    {{ number_format($assessment['afektif'], 0) }}%
                                </div>
                            </div>
                            <button type="button" class="btn btn-sm btn-outline-success mt-2 w-100" data-bs-toggle="collapse" data-bs-target="#afektifDetail">
                                Lihat Detail
                            </button>
                            <div class="collapse mt-2" id="afektifDetail">
                                <table class="table table-sm table-bordered">
                                    <thead><tr><th>Komponen</th><th class="text-center">Bobot</th><th class="text-center">Nilai</th></tr></thead>
                                    <tbody>
                                        @foreach($afektifDetail['components'] as $comp)
                                            <tr>
                                                <td>{{ $comp['nama_komponen'] }}</td>
                                                <td class="text-center">{{ $comp['bobot'] }}</td>
                                                <td class="text-center">{{ number_format($comp['nilai'], 2) }}</td>
                                            </tr>
                                        @endforeach
                                        <tr class="table-light fw-bold">
                                            <td>Rata-rata</td>
                                            <td class="text-center">{{ $afektifDetail['total_bobot'] }}</td>
                                            <td class="text-center">{{ number_format($afektifDetail['rata_rata'], 2) }}</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card">
                        <div class="card-header bg-light">
                            <h5 class="card-title mb-0">Psikomotorik</h5>
                        </div>
                        <div class="card-body">
                            <h2 class="text-warning">{{ number_format($assessment['psikomotorik'], 2) }}</h2>
                            <div class="progress mt-2" style="height: 20px;">
                                <div class="progress-bar bg-warning" role="progressbar" style="width: {{ $assessment['psikomotorik'] }}%;" aria-valuenow="{{ $assessment['psikomotorik'] }}" aria-valuemin="0" aria-valuemax="100">
                                    {{ number_format($assessment['psikomotorik'], 0) }}%
                                </div>
                            </div>
                            <button type="button" class="btn btn-sm btn-outline-warning mt-2 w-100" data-bs-toggle="collapse" data-bs-target="#psikomotorikDetail">
                                Lihat Detail
                            </button>
                            <div class="collapse mt-2" id="psikomotorikDetail">
                                <table class="table table-sm table-bordered">
                                    <thead><tr><th>Komponen</th><th class="text-center">Bobot</th><th class="text-center">Nilai</th></tr></thead>
                                    <tbody>
                                        @foreach($psikomotorikDetail['components'] as $comp)
                                            <tr>
                                                <td>{{ $comp['nama_komponen'] }}</td>
                                                <td class="text-center">{{ $comp['bobot'] }}</td>
                                                <td class="text-center">{{ number_format($comp['nilai'], 2) }}</td>
                                            </tr>
                                        @endforeach
                                        <tr class="table-light fw-bold">
                                            <td>Rata-rata</td>
                                            <td class="text-center">{{ $psikomotorikDetail['total_bobot'] }}</td>
                                            <td class="text-center">{{ number_format($psikomotorikDetail['rata_rata'], 2) }}</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @else
            <div class="alert alert-warning">
                <i class="ti ti-alert-triangle me-2"></i>
                Data penilaian belum lengkap. Nilai akhir dapat dihitung setelah semua aspek (Kognitif, Afektif, Psikomotorik) memiliki nilai.
            </div>
        @endif
    </div>
</div>
@endsection
