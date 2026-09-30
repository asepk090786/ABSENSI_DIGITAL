@extends('layouts.app')

@section('title', 'Kategorisasi Asesmen')

@section('content')
<div class="page-header d-print-none">
    <div class="container-xl">
        <div class="row g-2 align-items-center">
            <div class="col">
                <h2 class="page-title">
                    <i class="ti ti-adjustments me-2"></i>Kategorisasi Asesmen
                </h2>
                <div class="text-muted mt-1">
                    {{ $selectedKelas->nama_kelas }}
                    @if($selectedMapel)
                        &middot; {{ $selectedMapel->nama_mapel }}
                    @else
                        &middot; Semua mata pelajaran
                    @endif
                </div>
                @if($tahunAjaranAktif && $semesterAktif)
                    <div class="text-muted small mt-2">
                        <i class="ti ti-calendar me-1"></i>{{ $tahunAjaranAktif->tahun_ajaran }} - <strong>{{ $semesterAktif->nama_semester }}</strong>
                    </div>
                @endif
            </div>
            <div class="col-auto ms-auto d-print-none">
                <a href="{{ route('nilai.index', array_filter(['kelas_id' => $kelasId, 'mapel_id' => $mapelId])) }}" class="btn btn-outline-secondary">
                    <i class="ti ti-arrow-left me-1"></i>Kembali
                </a>
            </div>
        </div>
    </div>
</div>

<div class="page-body">
    <div class="container-xl">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Sesi Penilaian ({{ $assessmentRows->count() }})</h3>
            </div>
            <div class="table-responsive">
                <table class="table table-vcenter table-striped table-hover">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Penilaian</th>
                            <th>Mata Pelajaran</th>
                            <th>Tanggal</th>
                            <th class="text-center">Siswa</th>
                            <th class="text-center">Nilai Terisi</th>
                            <th>Kategori</th>
                            <th class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($assessmentRows as $index => $assessment)
                            <tr>
                                <td>{{ $index + 1 }}</td>
                                <td>{{ $assessment->judul }}</td>
                                <td>{{ $assessment->mapel }}</td>
                                <td>{{ $assessment->tanggal ? \Carbon\Carbon::parse($assessment->tanggal)->format('d/m/Y') : '-' }}</td>
                                <td class="text-center">{{ number_format($assessment->jumlah_siswa) }}</td>
                                <td class="text-center">{{ number_format($assessment->jumlah_nilai) }}</td>
                                <td colspan="2">
                                    <form method="POST" action="{{ route('nilai.kategorisasi.update') }}" class="d-flex flex-wrap align-items-center justify-content-end gap-2">
                                        @csrf
                                        <input type="hidden" name="record_id" value="{{ $assessment->record_id }}">
                                        <select class="form-select form-select-sm" name="kategori_asesmen" aria-label="Kategori untuk {{ $assessment->judul }}" required style="max-width: 190px">
                                            <option value="" disabled {{ !$assessment->kategori || $assessment->kategori === 'campuran' ? 'selected' : '' }}>Pilih kategori</option>
                                            <option value="formatif" {{ $assessment->kategori === 'formatif' ? 'selected' : '' }}>Asesmen Formatif</option>
                                            <option value="sumatif" {{ $assessment->kategori === 'sumatif' ? 'selected' : '' }}>Asesmen Sumatif</option>
                                        </select>
                                        <button type="submit" class="btn btn-sm btn-primary" title="Simpan kategori" aria-label="Simpan kategori">
                                            <i class="ti ti-device-floppy"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center text-muted py-4">Belum ada sesi penilaian untuk filter ini.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection