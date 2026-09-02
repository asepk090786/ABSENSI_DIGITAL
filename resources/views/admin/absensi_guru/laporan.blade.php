@extends('layouts.app')

@section('title', 'Laporan Absensi Guru')

@section('content')
<div class="container-fluid">
    <div class="page-header d-print-none mb-4">
        <div class="row align-items-center">
            <div class="col">
                <h2 class="page-title"><i class="ti ti-report me-2"></i>Laporan Absensi Guru</h2>
                <div class="text-muted">Rekap dan analisa absensi guru</div>
            </div>
            <div class="col-auto">
                <a href="{{ route('admin.absensi_guru.index') }}" class="btn btn-outline-primary">
                    <i class="ti ti-arrow-left me-1"></i>Kembali
                </a>
            </div>
        </div>
    </div>

    <div class="card mb-4">
        <div class="card-header">
            <h3 class="card-title">Filter Laporan</h3>
        </div>
        <div class="card-body">
            <form method="GET" class="row g-3">
                <div class="col-md-4">
                    <label class="form-label">Tanggal Mulai</label>
                    <input type="date" class="form-control" name="start_date" value="{{ $startDate }}">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Tanggal Akhir</label>
                    <input type="date" class="form-control" name="end_date" value="{{ $endDate }}">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Guru</label>
                    <select class="form-select" name="guru_id">
                        <option value="">-- Semua Guru --</option>
                        @foreach($guru as $g)
                        <option value="{{ $g->id }}" @selected($g->id == $guruId)>
                            {{ $g->nama }} ({{ $g->nip ?? '-' }})
                        </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-12">
                    <button type="submit" class="btn btn-primary">
                        <i class="ti ti-search me-1"></i>Filter
                    </button>
                    <a href="{{ route('admin.absensi_guru.laporan') }}" class="btn btn-outline-secondary">
                        <i class="ti ti-reset me-1"></i>Reset
                    </a>
                    <button type="button" class="btn btn-outline-info float-end" onclick="window.print()">
                        <i class="ti ti-printer me-1"></i>Cetak
                    </button>
                </div>
            </form>
        </div>
    </div>

    @if($absensi->count() > 0)
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">
                Data Absensi
                @if($startDate && $endDate)
                    ({{ \Carbon\Carbon::parse($startDate)->format('d M Y') }} - {{ \Carbon\Carbon::parse($endDate)->format('d M Y') }})
                @elseif($guruId)
                    ({{ $guru->where('id', $guruId)->first()?->nama ?? 'Semua Guru' }})
                @endif
            </h3>
        </div>
        <div class="table-responsive">
            <table class="table table-hover table-sm mb-0">
                <thead>
                    <tr>
                        <th style="width: 5%;">No</th>
                        <th>Tanggal</th>
                        <th>Nama Guru</th>
                        <th>NIP</th>
                        <th style="width: 12%;">Status</th>
                        <th>Keterangan</th>
                        <th>Pencatat</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($absensi as $index => $item)
                    <tr>
                        <td>{{ ($absensi->currentPage() - 1) * $absensi->perPage() + $index + 1 }}</td>
                        <td>{{ $item->tanggal->format('d M Y') }}</td>
                        <td><strong>{{ $item->guru->nama ?? '-' }}</strong></td>
                        <td>{{ $item->guru->nip ?? '-' }}</td>
                        <td>
                            @php
                                $statusBadges = [
                                    'hadir' => 'success',
                                    'tidak_hadir' => 'danger',
                                    'izin' => 'warning',
                                    'sakit' => 'info'
                                ];
                            @endphp
                            <span class="badge bg-{{ $statusBadges[$item->status] ?? 'secondary' }}">
                                {{ ucfirst(str_replace('_', ' ', $item->status)) }}
                            </span>
                        </td>
                        <td>{{ $item->keterangan ?? '-' }}</td>
                        <td><small>{{ $item->pencatat?->nama ?? 'System' }}</small></td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center text-muted py-4">
                            Tidak ada data absensi yang sesuai dengan filter.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($absensi->hasPages())
        <div class="card-footer">
            {{ $absensi->links() }}
        </div>
        @endif
    </div>

    <!-- Statistik Ringkas -->
    <div class="row mt-4">
        <div class="col-md-3">
            <div class="card text-center">
                <div class="card-body">
                    <h2 class="text-success">
                        {{ $absensi->collection->where('status', 'hadir')->count() }}
                    </h2>
                    <small class="text-muted">Hadir</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-center">
                <div class="card-body">
                    <h2 class="text-danger">
                        {{ $absensi->collection->where('status', 'tidak_hadir')->count() }}
                    </h2>
                    <small class="text-muted">Tidak Hadir</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-center">
                <div class="card-body">
                    <h2 class="text-warning">
                        {{ $absensi->collection->where('status', 'izin')->count() }}
                    </h2>
                    <small class="text-muted">Izin</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-center">
                <div class="card-body">
                    <h2 class="text-info">
                        {{ $absensi->collection->where('status', 'sakit')->count() }}
                    </h2>
                    <small class="text-muted">Sakit</small>
                </div>
            </div>
        </div>
    </div>
    @else
    <div class="card text-center">
        <div class="card-body py-5">
            <i class="ti ti-inbox fs-1 d-block mb-2 text-muted"></i>
            <p class="text-muted">Tidak ada data absensi yang sesuai dengan filter.</p>
        </div>
    </div>
    @endif
</div>
@endsection
