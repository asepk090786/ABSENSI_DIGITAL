@extends('layouts.app', ['pageSlug' => 'rekap_nilai'])

@section('title', 'Rekap Nilai')

@section('content')
<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header border-0 pt-3 pb-2">
                <div class="row align-items-center">
                    <div class="col">
                        <h4 class="card-title fw-semibold m-0">Rekap Nilai</h4>
                        @if($tahunAjaranActive && $semesterActive)
                            <p class="text-muted mb-0 small">
                                {{ $tahunAjaranActive->tahun_ajaran }} - {{ $semesterActive->nama_semester }}
                            </p>
                        @endif
                    </div>
                </div>
            </div>
            <div class="card-body">
                
                @php
                    $isSiswa = auth()->user()->hasRole('Siswa');
                    $siswaKelas = optional(auth()->user()->siswa)->kelas_id ? ($kelasOptions->first() ?? null) : null;
                @endphp
                <form method="GET" action="{{ route('rekap_nilai.index') }}" class="mb-4">
                    @if(request()->boolean('wali_kelas'))
                        <input type="hidden" name="wali_kelas" value="1">
                    @endif
                    <div class="row">
                        <div class="col-md-4 mb-2">
                            <label class="form-label">Kelas <span class="text-danger">*</span></label>
                            @if($isSiswa && $siswaKelas)
                                <input type="text" class="form-control" value="{{ $siswaKelas->nama_kelas }}" readonly>
                                <input type="hidden" name="kelas_id" value="{{ $kelasId }}">
                            @else
                                <select name="kelas_id" id="kelasSelect" class="form-select" required>
                                    <option value="">Pilih Kelas</option>
                                    @foreach($kelasOptions as $kelas)
                                        <option value="{{ $kelas->id }}" {{ $kelasId == $kelas->id ? 'selected' : '' }}>
                                            {{ $kelas->nama_kelas }}
                                        </option>
                                    @endforeach
                                </select>
                            @endif
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
                        <div class="col-md-4 mb-2">
                            <label class="form-label">Komponen Penilaian</label>
                            <select name="komponen_id" class="form-select">
                                <option value="">Semua Komponen</option>
                                @foreach($komponenOptions as $komponen)
                                    <option value="{{ $komponen->id }}" {{ $komponenId == $komponen->id ? 'selected' : '' }}>
                                        {{ $komponen->nama_komponen }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-12">
                            <button type="submit" class="btn btn-primary">
                                <i class="ti ti-filter me-1"></i>Tampilkan Rekap
                            </button>
                            @if($rekapData)
                                <a href="{{ route('rekap_nilai.export', ['kelas_id' => $kelasId, 'mapel_id' => $mapelId, 'komponen_id' => $komponenId, 'wali_kelas' => request()->boolean('wali_kelas') ? 1 : null]) }}" class="btn btn-success">
                                    <i class="ti ti-file-spreadsheet me-1"></i>Export Excel
                                </a>
                            @endif
                        </div>
                    </div>
                </form>

                @if($rekapData)
                    
                    <div class="alert alert-info mb-2">
                        <div class="row">
                            <div class="col-md-4">
                                <strong>Kelas:</strong> {{ $selectedKelas->nama_kelas }}
                            </div>
                            <div class="col-md-4">
                                <strong>Mata Pelajaran:</strong> {{ $selectedMapel->nama_mapel }}
                            </div>
                            <div class="col-md-4">
                                <strong>Komponen:</strong> {{ $selectedKomponen ? $selectedKomponen->nama_komponen : 'Semua Komponen' }}
                            </div>
                        </div>
                    </div>

                    
                    <div class="row mb-2">
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
                                    <h5 class="card-title">Rata-rata Kelas</h5>
                                    <h2 class="mb-0">{{ $rekapData->whereNotNull('rata_rata')->count() ? number_format($rekapData->whereNotNull('rata_rata')->avg('rata_rata'), 2) : '-' }}</h2>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card bg-info text-white">
                                <div class="card-body">
                                    <h5 class="card-title">Jumlah Tertinggi</h5>
                                    <h2 class="mb-0">{{ $rekapData->whereNotNull('jumlah')->count() ? number_format($rekapData->max('jumlah'), 2) : '-' }}</h2>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card bg-warning text-white">
                                <div class="card-body">
                                    <h5 class="card-title">Jumlah Terendah</h5>
                                    <h2 class="mb-0">{{ $rekapData->whereNotNull('jumlah')->count() ? number_format($rekapData->min('jumlah'), 2) : '-' }}</h2>
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
                                    @forelse(($rekapKomponenColumns ?? collect()) as $komponen)
                                        <th class="text-center">{{ strtoupper($komponen->nama) }}</th>
                                    @empty
                                        <th class="text-center">KOMPONEN</th>
                                    @endforelse
                                    <th class="text-center">JUMLAH</th>
                                    <th class="text-center">RATA-RATA</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($rekapData as $index => $siswa)
                                    <tr>
                                        <td>{{ $index + 1 }}</td>
                                        <td>{{ $siswa->nama }}</td>
                                        @forelse(($rekapKomponenColumns ?? collect()) as $komponen)
                                            @php $nilaiKomponen = $siswa->nilai_komponen[$komponen->id] ?? null; @endphp
                                            <td class="text-center">{{ $nilaiKomponen !== null ? number_format($nilaiKomponen, 2) : '-' }}</td>
                                        @empty
                                            <td class="text-center">-</td>
                                        @endforelse
                                        <td class="text-center fw-bold">{{ $siswa->jumlah !== null ? number_format($siswa->jumlah, 2) : '-' }}</td>
                                        <td class="text-center fw-bold">{{ $siswa->rata_rata !== null ? number_format($siswa->rata_rata, 2) : '-' }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="{{ 4 + (($rekapKomponenColumns ?? collect())->count()) }}" class="text-center text-muted">Tidak ada data siswa</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="alert alert-info">
                        <i class="ti ti-info-circle me-2"></i>
                        Silakan pilih kelas dan mata pelajaran untuk menampilkan rekap nilai
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection

@push('js')
<script>
    // Reload mapel when kelas changes
    document.getElementById('kelasSelect').addEventListener('change', function() {
        if (this.value) {
            // Submit form to reload page with selected kelas
            this.form.submit();
        }
    });
</script>
@endpush
