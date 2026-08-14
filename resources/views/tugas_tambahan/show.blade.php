@extends('layouts.app')

@section('title','Detail Tugas Tambahan')

@section('content')
<div class="container-fluid">
    <div class="page-header d-print-none">
        <div class="row align-items-center">
            <div class="col">
                <h2 class="page-title">
                    <i class="ti ti-briefcase me-2"></i>Detail Tugas Tambahan
                </h2>
            </div>
            <div class="col-auto">
                <a href="{{ route('tugas_tambahan.index') }}" class="btn btn-secondary">
                    <i class="ti ti-arrow-left me-1"></i>Kembali
                </a>
            </div>
        </div>
    </div>
</div>

<div class="page-wrapper">
    <div class="container-xl">
        <div class="row">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-body">
                        <!-- Tenaga Pendidikan Info -->
                        <div class="mb-4">
                            <h4 class="card-title">Tenaga Pendidikan</h4>
                            <div class="row">
                                <div class="col-md-6">
                                    <p class="text-muted mb-1">Nama</p>
                                    <p class="mb-3"><strong>{{ $tugaTambahan->tenagaPendidikan->nama ?? 'N/A' }}</strong></p>
                                </div>
                                <div class="col-md-6">
                                    <p class="text-muted mb-1">NIP</p>
                                    <p class="mb-3"><strong>{{ $tugaTambahan->tenagaPendidikan->nip ?? '-' }}</strong></p>
                                </div>
                            </div>
                        </div>

                        <hr>

                        <!-- Tugas Info -->
                        <div class="mb-4">
                            <h4 class="card-title">Tugas</h4>
                            <p class="text-muted mb-1">Nama Tugas</p>
                            <p class="mb-3"><strong>{{ $tugaTambahan->tugas }}</strong></p>

                            <p class="text-muted mb-1">Keterangan</p>
                            <p class="mb-3">
                                @if ($tugaTambahan->keterangan)
                                    {{ $tugaTambahan->keterangan }}
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </p>

                            <p class="text-muted mb-1">Status</p>
                            <p class="mb-3">
                                @if ($tugaTambahan->is_active)
                                    <span class="badge bg-success">Aktif</span>
                                @else
                                    <span class="badge bg-danger">Tidak Aktif</span>
                                @endif
                            </p>
                        </div>

                        <hr>

                        <!-- Timeline -->
                        <div class="mb-4">
                            <h4 class="card-title">Timeline</h4>
                            <p class="text-muted mb-1">Dibuat pada</p>
                            <p class="mb-3">{{ $tugaTambahan->created_at->format('d M Y H:i') }}</p>

                            <p class="text-muted mb-1">Terakhir diperbarui</p>
                            <p class="mb-0">{{ $tugaTambahan->updated_at->format('d M Y H:i') }}</p>
                        </div>
                    </div>

                    <div class="card-footer text-end">
                        <a href="{{ route('tugas_tambahan.edit', $tugaTambahan->id) }}" class="btn btn-primary">
                            <i class="ti ti-edit me-1"></i>Edit
                        </a>
                        <form action="{{ route('tugas_tambahan.destroy', $tugaTambahan->id) }}" method="POST" style="display:inline;">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger" onclick="return confirm('Yakin ingin menghapus?')">
                                <i class="ti ti-trash me-1"></i>Hapus
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
