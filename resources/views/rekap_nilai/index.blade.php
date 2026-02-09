@extends('layouts.app', ['pageSlug' => 'rekap_nilai'])

@section('title', 'Rekap Nilai')

@section('content')
<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header">
                <div class="row align-items-center">
                    <div class="col">
                        <h4 class="card-title mb-0">Rekap Nilai</h4>
                        @if($tahunAjaranActive && $semesterActive)
                            <p class="text-muted mb-0 small">
                                {{ $tahunAjaranActive->tahun_ajaran }} - {{ $semesterActive->nama_semester }}
                            </p>
                        @endif
                    </div>
                </div>
            </div>
            <div class="card-body">
                <!-- Filter Form -->
                <form method="GET" action="{{ route('rekap_nilai.index') }}" class="mb-4">
                    <div class="row">
                        <div class="col-md-4 mb-3">
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
                        <div class="col-md-4 mb-3">
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
                        <div class="col-md-4 mb-3">
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
                                <a href="{{ route('rekap_nilai.export', ['kelas_id' => $kelasId, 'mapel_id' => $mapelId, 'komponen_id' => $komponenId]) }}" class="btn btn-success">
                                    <i class="ti ti-file-spreadsheet me-1"></i>Export Excel
                                </a>
                            @endif
                        </div>
                    </div>
                </form>

                @if($rekapData)
                    <!-- Header Summary -->
                    <div class="alert alert-info mb-3">
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

                    <!-- Statistics Cards -->
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
                                    <h5 class="card-title">Rata-rata Kelas</h5>
                                    <h2 class="mb-0">{{ $rekapData->avg('rata_rata') ? number_format($rekapData->avg('rata_rata'), 2) : '-' }}</h2>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card bg-info text-white">
                                <div class="card-body">
                                    <h5 class="card-title">Nilai Tertinggi</h5>
                                    <h2 class="mb-0">{{ $rekapData->max('nilai_tertinggi') ?: '-' }}</h2>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card bg-warning text-white">
                                <div class="card-body">
                                    <h5 class="card-title">Nilai Terendah</h5>
                                    <h2 class="mb-0">{{ $rekapData->min('nilai_terendah') ?: '-' }}</h2>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Data Table -->
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover">
                            <thead class="table-light">
                                <tr>
                                    <th width="50">No</th>
                                    <th>NIS</th>
                                    <th>NISN</th>
                                    <th>Nama Siswa</th>
                                    <th width="100">Jumlah Nilai</th>
                                    <th width="100">Rata-rata</th>
                                    <th width="100">Tertinggi</th>
                                    <th width="100">Terendah</th>
                                    <th width="100">Keterangan</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($rekapData as $index => $siswa)
                                    <tr>
                                        <td>{{ $index + 1 }}</td>
                                        <td>{{ $siswa->nis }}</td>
                                        <td>{{ $siswa->nisn }}</td>
                                        <td>{{ $siswa->nama }}</td>
                                        <td class="text-center">{{ $siswa->jumlah_nilai ?: '-' }}</td>
                                        <td class="text-center">
                                            @if($siswa->rata_rata)
                                                <span class="badge bg-{{ $siswa->rata_rata >= 75 ? 'success' : ($siswa->rata_rata >= 60 ? 'warning' : 'danger') }}">
                                                    {{ number_format($siswa->rata_rata, 2) }}
                                                </span>
                                            @else
                                                <span class="text-muted">-</span>
                                            @endif
                                        </td>
                                        <td class="text-center">{{ $siswa->nilai_tertinggi ?: '-' }}</td>
                                        <td class="text-center">{{ $siswa->nilai_terendah ?: '-' }}</td>
                                        <td class="text-center">
                                            @if($siswa->rata_rata)
                                                @if($siswa->rata_rata >= 75)
                                                    <span class="badge bg-success">Baik</span>
                                                @elseif($siswa->rata_rata >= 60)
                                                    <span class="badge bg-warning">Cukup</span>
                                                @else
                                                    <span class="badge bg-danger">Kurang</span>
                                                @endif
                                            @else
                                                <span class="badge bg-secondary">Belum Ada Nilai</span>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="9" class="text-center text-muted">Tidak ada data siswa</td>
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
