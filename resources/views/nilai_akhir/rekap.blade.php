@extends('layouts.app', ['pageSlug' => 'nilai_akhir_rekap'])

@section('title', 'Rekapitulasi Penilaian Akhir')

@section('content')
<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header border-0 pt-3 pb-2">
                <div class="row align-items-center">
                    <div class="col">
                        <h4 class="card-title fw-semibold m-0">Rekapitulasi Penilaian Akhir</h4>
                        @if($tahunAjaranActive && $semesterActive)
                            <p class="text-muted mb-0 small">
                                {{ $tahunAjaranActive->tahun_ajaran }} - {{ $semesterActive->nama_semester }}
                            </p>
                        @endif
                    </div>
                </div>
            </div>
            <div class="card-body">
                <form method="GET" action="{{ route('nilai_akhir.rekap') }}" class="mb-4">
                    <div class="row">
                        <div class="col-md-4 mb-2">
                            <label class="form-label">Kelas <span class="text-danger">*</span></label>
                            <select name="kelas_id" id="kelasSelect" class="form-select" required>
                                <option value="">Pilih Kelas</option>
                                @foreach($kelasOptions as $kelas)
                                    <option value="{{ $kelas->id }}" {{ $kelasId == $kelas->id ? 'selected' : '' }}>
                                        {{ $kelas->nama_kelas }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4 mb-2">
                            <label class="form-label">Mata Pelajaran <span class="text-danger">*</span></label>
                            <select name="mapel_id" id="mapelSelect" class="form-select" required>
                                <option value="">Pilih Mata Pelajaran</option>
                                @foreach($mapelOptions as $mapel)
                                    <option value="{{ $mapel->id }}" {{ $mapelId == $mapel->id ? 'selected' : '' }}>
                                        {{ $mapel->nama_mapel }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4 d-flex align-items-end">
                            <button type="submit" class="btn btn-primary w-100">
                                <i class="ti ti-filter me-1"></i>Tampilkan Rekap
                            </button>
                        </div>
                    </div>
                </form>

                @if($rekapData->count() > 0)
                    <div class="alert alert-info mb-3">
                        <div class="row">
                            <div class="col-md-4">
                                <strong>Kelas:</strong> {{ $selectedKelas->nama_kelas }}
                            </div>
                            <div class="col-md-4">
                                <strong>Mata Pelajaran:</strong> {{ $selectedMapel->nama_mapel }}
                            </div>
                            <div class="col-md-4">
                                <strong>Periode:</strong> {{ $tahunAjaranActive->tahun_ajaran }} - {{ $semesterActive->nama_semester }}
                            </div>
                        </div>
                    </div>

                    @php
                        $tuntasCount = $rekapData->where('status', 'TUNTAS')->count();
                        $belumTuntasCount = $rekapData->where('status', 'BELUM TUNTAS')->count();
                        $validScores = $rekapData->whereNotNull('final_score')->pluck('final_score');
                        $rataRataKelas = $validScores->count() ? round($validScores->avg(), 2) : null;
                        $nilaiTertinggi = $validScores->count() ? round($validScores->max(), 2) : null;
                        $nilaiTerendah = $validScores->count() ? round($validScores->min(), 2) : null;
                    @endphp

                    <div class="row mb-3">
                        <div class="col-md-3">
                            <div class="card bg-primary text-white">
                                <div class="card-body">
                                    <h5 class="card-title">Total Siswa</h5>
                                    <h2 class="mb-0">{{ $rekapData->count() }}</h2>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card bg-success text-white">
                                <div class="card-body">
                                    <h5 class="card-title">Tuntas</h5>
                                    <h2 class="mb-0">{{ $tuntasCount }}</h2>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card bg-warning text-white">
                                <div class="card-body">
                                    <h5 class="card-title">Belum Tuntas</h5>
                                    <h2 class="mb-0">{{ $belumTuntasCount }}</h2>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card bg-info text-white">
                                <div class="card-body">
                                    <h5 class="card-title">Rata-rata Kelas</h5>
                                    <h2 class="mb-0">{{ $rataRataKelas ?? '-' }}</h2>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-body">
                                    <h5 class="card-title">Nilai Tertinggi</h5>
                                    <h3 class="text-success">{{ $nilaiTertinggi ?? '-' }}</h3>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-body">
                                    <h5 class="card-title">Nilai Terendah</h5>
                                    <h3 class="text-danger">{{ $nilaiTerendah ?? '-' }}</h3>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-bordered table-hover">
                            <thead class="table-light">
                                <tr>
                                    <th width="50">No</th>
                                    <th>Nama Siswa</th>
                                    <th class="text-center">KOGNITIF</th>
                                    <th class="text-center">AFEKTIF</th>
                                    <th class="text-center">PSIKOMOTORIK</th>
                                    <th class="text-center">NILAI AKHIR</th>
                                    <th class="text-center">PREDIKAT</th>
                                    <th class="text-center">STATUS</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($rekapData as $index => $siswa)
                                    @php
                                        $assessment = $siswa;
                                    @endphp
                                    <tr>
                                        <td>{{ $index + 1 }}</td>
                                        <td>
                                            <a href="{{ route('nilai_akhir.detail', ['siswa' => $siswa->student->id, 'kelas_id' => $kelasId, 'mapel_id' => $mapelId]) }}">
                                                {{ $siswa->student->nama }}
                                            </a>
                                        </td>
                                        <td class="text-center">{{ $assessment['kognitif'] !== null ? number_format($assessment['kognitif'], 2) : '-' }}</td>
                                        <td class="text-center">{{ $assessment['afektif'] !== null ? number_format($assessment['afektif'], 2) : '-' }}</td>
                                        <td class="text-center">{{ $assessment['psikomotorik'] !== null ? number_format($assessment['psikomotorik'], 2) : '-' }}</td>
                                        <td class="text-center fw-bold">{{ $assessment['final_score'] !== null ? number_format($assessment['final_score'], 2) : '-' }}</td>
                                        <td class="text-center">
                                            @if($assessment['predicate'])
                                                <span class="badge bg-primary">{{ $assessment['predicate']['grade'] }}</span>
                                                <small class="text-muted d-block">{{ $assessment['predicate']['description'] }}</small>
                                            @else
                                                -
                                            @endif
                                        </td>
                                        <td class="text-center">
                                            @if($assessment['status'] === 'TUNTAS')
                                                <span class="badge bg-success">TUNTAS</span>
                                            @elseif($assessment['status'] === 'BELUM TUNTAS')
                                                <span class="badge bg-danger">BELUM TUNTAS</span>
                                            @else
                                                <span class="text-muted">-</span>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="8" class="text-center text-muted">Tidak ada data siswa</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="alert alert-info">
                        <i class="ti ti-info-circle me-2"></i>
                        Silakan pilih kelas dan mata pelajaran untuk menampilkan rekap penilaian
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection

@push('js')
<script>
    document.getElementById('kelasSelect').addEventListener('change', function() {
        if (this.value) {
            this.form.submit();
        }
    });
</script>
@endpush
