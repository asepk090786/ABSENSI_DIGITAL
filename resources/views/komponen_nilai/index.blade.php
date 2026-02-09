@extends('layouts.app')

@section('title', 'Komponen Penilaian')

@section('content')
<div class="page-header d-print-none">
    <div class="container-xl">
        <div class="row g-2 align-items-center">
            <div class="col">
                <h2 class="page-title">
                    <i class="ti ti-checklist me-2"></i>Komponen Penilaian
                </h2>
                <div class="text-muted mt-1">Kelola komponen penilaian seperti Harian, Tugas, UTS, UAS</div>
            </div>
        </div>
    </div>
</div>

<div class="page-body">
    <div class="container-xl">
        @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="ti ti-check me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        @endif

        <div class="card mb-3">
            <div class="card-header">
                <h3 class="card-title">Tambah Komponen</h3>
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('komponen_nilai.store') }}">
                    @csrf
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Nama Komponen</label>
                            <input type="text" name="nama_komponen" class="form-control @error('nama_komponen') is-invalid @enderror" value="{{ old('nama_komponen') }}" required>
                            @error('nama_komponen')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Bobot (%)</label>
                            <input type="number" name="bobot" class="form-control @error('bobot') is-invalid @enderror" value="{{ old('bobot') }}" min="0" max="100" step="0.01">
                            @error('bobot')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-2 d-flex align-items-end">
                            <button type="submit" class="btn btn-primary w-100">
                                <i class="ti ti-plus me-1"></i>Simpan
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Daftar Komponen</h3>
            </div>
            <div class="card-body">
                @if($items->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-vcenter table-hover">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>Nama Komponen</th>
                                    <th>Bobot (%)</th>
                                    <th class="text-center">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($items as $index => $item)
                                <tr>
                                    <td>{{ $index + 1 }}</td>
                                    <td>{{ $item->nama_komponen }}</td>
                                    <td>{{ $item->bobot ?? '-' }}</td>
                                    <td class="text-center">
                                        <div class="btn-group" role="group">
                                            <a href="{{ route('komponen_nilai.edit', $item->id) }}" class="btn btn-sm btn-warning" title="Edit">
                                                <i class="ti ti-edit"></i>
                                            </a>
                                            <form action="{{ route('komponen_nilai.destroy', $item->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Hapus komponen ini?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-danger" title="Hapus">
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
                @else
                    <div class="text-muted">Belum ada komponen penilaian.</div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
