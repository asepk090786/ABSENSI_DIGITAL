@extends('layouts.app', ['pageSlug' => 'supervisi'])

@section('title', 'Dashboard Supervisi')

@section('content')
<div class="row g-4">
    <div class="col-md-3">
        <div class="card h-100 border-0 shadow-sm">
            <div class="card-body">
                <div class="text-muted small">Total Supervisi</div>
                <div class="display-6 fw-bold mt-2">{{ $total ?? 0 }}</div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card h-100 border-0 shadow-sm">
            <div class="card-body">
                <div class="text-muted small">Terjadwal</div>
                <div class="display-6 fw-bold mt-2">{{ $terjadwal ?? 0 }}</div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card h-100 border-0 shadow-sm">
            <div class="card-body">
                <div class="text-muted small">Berlangsung</div>
                <div class="display-6 fw-bold mt-2">{{ $berlangsung ?? 0 }}</div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card h-100 border-0 shadow-sm">
            <div class="card-body">
                <div class="text-muted small">Selesai</div>
                <div class="display-6 fw-bold mt-2">{{ $selesai ?? 0 }}</div>
            </div>
        </div>
    </div>
</div>

<div class="row mt-4">
    <div class="col-12">
        <div class="card shadow-sm border-0">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h4 class="card-title mb-0">Tahapan Supervisi</h4>
                <a href="{{ route('akademik.supervisi.create') }}" class="btn btn-primary btn-sm">Tambah Supervisi</a>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-4">
                        <a href="{{ route('supervisi.prasupervisi') }}" class="btn btn-outline-primary w-100 text-start">Pra Supervisi</a>
                    </div>
                    <div class="col-md-4">
                        <a href="{{ route('supervisi.pelaksanaan') }}" class="btn btn-outline-secondary w-100 text-start">Pelaksanaan</a>
                    </div>
                    <div class="col-md-4">
                        <a href="{{ route('supervisi.pascasupervisi') }}" class="btn btn-outline-success w-100 text-start">Pasca Supervisi</a>
                    </div>
                    <div class="col-md-4">
                        <a href="{{ route('supervisi.tindak-lanjut') }}" class="btn btn-outline-warning w-100 text-start">Tindak Lanjut</a>
                    </div>
                    <div class="col-md-4">
                        <a href="{{ route('supervisi.monitoring') }}" class="btn btn-outline-info w-100 text-start">Monitoring</a>
                    </div>
                    <div class="col-md-4">
                        <a href="{{ route('supervisi.laporan') }}" class="btn btn-outline-dark w-100 text-start">Laporan</a>
                    </div>
                    <div class="col-md-6">
                        <a href="{{ route('supervisi.instrumen.index') }}" class="btn btn-outline-danger w-100 text-start">Master Instrumen</a>
                    </div>
                    <div class="col-md-6">
                        <a href="{{ route('supervisi.indikator.index') }}" class="btn btn-outline-primary w-100 text-start">Master Indikator</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row mt-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h4 class="card-title mb-0">Supervisi Terbaru</h4>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead>
                            <tr>
                                <th>Guru</th>
                                <th>Mata Pelajaran</th>
                                <th>Kelas</th>
                                <th>Tanggal</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($items as $item)
                                <tr>
                                    <td>{{ $item->guru->user->name ?? $item->guru->nama ?? '-' }}</td>
                                    <td>{{ $item->mataPelajaran->nama_mapel ?? '-' }}</td>
                                    <td>{{ $item->kelas->nama_kelas ?? '-' }}</td>
                                    <td>{{ $item->tanggal?->format('d-m-Y') ?? '-' }}</td>
                                    <td><span class="badge bg-info">{{ $item->status ?? 'Terjadwal' }}</span></td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center text-muted">Belum ada data supervisi.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
