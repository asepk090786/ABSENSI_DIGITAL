@extends('layouts.app')

@section('content')
<style>
    .hover-shadow {
        transition: all 0.3s ease;
    }
    .hover-shadow:hover {
        transform: translateY(-5px);
        box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15) !important;
    }
</style>

<div class="container-fluid">
    @if($kelasQuickAccess->isNotEmpty())
    <!-- Menu Akses Cepat -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header bg-primary-subtle">
                    <h5 class="card-title mb-0">
                        <i class="ti ti-clock-play me-2"></i>Menu Akses Cepat - Absen Kelas Anda
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        @foreach($kelasQuickAccess as $kelas)
                        <div class="col-md-6 col-lg-4 col-xl-3">
                            <div class="card border border-primary h-100 hover-shadow">
                                <div class="card-body text-center">
                                    <div class="mb-3">
                                        <i class="ti ti-school" style="font-size: 48px; color: var(--bs-primary);"></i>
                                    </div>
                                    <h5 class="card-title mb-2">{{ $kelas->nama_kelas }}</h5>
                                    <p class="text-muted small mb-3">
                                        @if($kelas->waliKelas)
                                        <i class="ti ti-user me-1"></i>{{ $kelas->waliKelas->nama }}
                                        @endif
                                    </p>
                                    <a href="{{ route('absensi.create', ['kelas_id' => $kelas->id]) }}" 
                                       class="btn btn-primary btn-sm w-100">
                                        <i class="ti ti-check me-1"></i>Absen Kelas Ini
                                    </a>
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h3 class="card-title">Data Absensi Kelas</h3>
                    <a href="{{ route('absensi.create') }}" class="btn btn-primary">
                        <i class="ti ti-plus"></i> Tambah Absensi
                    </a>
                </div>
                <div class="card-body">
                    @if($items->isEmpty())
                        <div class="alert alert-info">
                            <i class="ti ti-info-circle"></i> Belum ada data absensi.
                        </div>
                    @else
                        <div class="table-responsive">
                            <table class="table table-striped table-hover">
                                <thead>
                                    <tr>
                                        <th>No</th>
                                        <th>Tanggal</th>
                                        <th>Kelas</th>
                                        <th>Guru</th>
                                        <th>Jam Belajar</th>
                                        <th>Status Kelas</th>
                                        <th>Tahun Ajaran</th>
                                        <th>Semester</th>
                                        <th>Jumlah Siswa</th>
                                        <th>Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($items as $index => $item)
                                        <tr>
                                            <td>{{ $index + 1 }}</td>
                                            <td>{{ $item->tanggal->format('d/m/Y') }}</td>
                                            <td>{{ $item->kelas->nama_kelas ?? '-' }}</td>
                                            <td>{{ $item->guru->nama ?? '-' }}</td>
                                            <td>{{ $item->jamBelajar->jam_mulai ?? '-' }} - {{ $item->jamBelajar->jam_selesai ?? '-' }}</td>
                                            <td>
                                                @if($item->status_kelas)
                                                    <span class="badge bg-success">{{ $item->status_kelas }}</span>
                                                @else
                                                    <span class="badge bg-secondary">-</span>
                                                @endif
                                            </td>
                                            <td>{{ $item->tahunAjaran->nama_tahun ?? '-' }}</td>
                                            <td>{{ $item->semester->nama_semester ?? '-' }}</td>
                                            <td>
                                                <span class="badge bg-info">
                                                    {{ $item->absensiSiswa->count() }} Siswa
                                                </span>
                                            </td>
                                            <td>
                                                <div class="btn-group" role="group">
                                                    <a href="{{ route('absensi.show', $item->id) }}" class="btn btn-sm btn-info">
                                                        <i class="ti ti-eye"></i>
                                                    </a>
                                                    <a href="{{ route('absensi.edit', $item->id) }}" class="btn btn-sm btn-warning">
                                                        <i class="ti ti-edit"></i>
                                                    </a>
                                                    <form action="{{ route('absensi.destroy', $item->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Yakin ingin menghapus data ini?')">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="btn btn-sm btn-danger">
                                                            <i class="ti ti-trash"></i>
                                                        </button>
                                                    </form>
                                                </div>
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
