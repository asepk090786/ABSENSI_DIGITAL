@extends('layouts.app')

@section('title','Kelola Pola Jam Belajar')

@section('content')
<div class="row mb-4">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h2 class="fw-bold mb-0">Kelola Pola Jam Belajar</h2>
                <small class="text-muted">Atur template pola jam yang telah disimpan</small>
            </div>
            <a href="{{ route('jam_belajar.index') }}" class="btn btn-secondary">
                <i class="ti ti-arrow-left me-2"></i>Kembali
            </a>
        </div>
    </div>
</div>

@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show">
        <i class="ti ti-check me-2"></i>{{ session('success') }}
        <button type="button" class="close" data-bs-dismiss="alert"><span aria-hidden="true">&times;</span></button>
    </div>
@endif

@if($errors->any())
    <div class="alert alert-danger alert-dismissible fade show">
        <i class="ti ti-alert-circle me-2"></i>
        <ul class="mb-0">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
        <button type="button" class="close" data-bs-dismiss="alert"><span aria-hidden="true">&times;</span></button>
    </div>
@endif

<div class="row">
    @forelse($polas as $pola)
    <div class="col-lg-6 mb-4">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-light">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <h5 class="card-title fw-semibold m-0">
                            <i class="ti ti-layout-grid me-2"></i>{{ $pola->nama_pola }}
                        </h5>
                        @if($pola->deskripsi)
                        <small class="text-muted d-block mt-1">{{ $pola->deskripsi }}</small>
                        @endif
                    </div>
                    <span class="badge {{ $pola->is_active ? 'bg-success' : 'bg-secondary' }}">
                        {{ $pola->is_active ? 'Aktif' : 'Nonaktif' }}
                    </span>
                </div>
            </div>
            <div class="card-body">
                <h6 class="fw-semibold mb-2">Detail Pola:</h6>
                <div class="table-responsive mb-3">
                    <table class="table table-sm table-hover mb-0">
                        <thead class="bg-light">
                            <tr>
                                <th style="width: 15%;">Jam Ke</th>
                                <th style="width: 20%;">Mulai</th>
                                <th style="width: 20%;">Selesai</th>
                                <th style="width: 45%;">Jenis</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($pola->jam_data as $jam)
                            <tr>
                                <td class="fw-bold">{{ $jam['urutan'] }}</td>
                                <td>{{ $jam['jam_mulai'] }}</td>
                                <td>{{ $jam['jam_selesai'] }}</td>
                                <td>
                                    <span class="badge bg-info">{{ $jam['jenis'] }}</span>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <p class="text-muted mb-0 small">
                    <i class="ti ti-calendar me-1"></i>
                    Total {{ count($pola->jam_data) }} slot jam
                </p>
            </div>
            <div class="card-footer bg-light">
                <div class="d-flex gap-2">
                    <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#applyPatternModal{{ $pola->id }}">
                        <i class="ti ti-check me-1"></i>Terapkan Pola
                    </button>
                    <form action="{{ route('jam_belajar.delete_pattern', $pola) }}" method="POST" class="d-inline" onsubmit="return confirm('Hapus pola ini? Tindakan ini tidak dapat dibatalkan.')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-sm btn-danger">
                            <i class="ti ti-trash me-1"></i>Hapus
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Apply Pattern -->
    <div class="modal fade" id="applyPatternModal{{ $pola->id }}" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="ti ti-check me-2"></i>Terapkan Pola: {{ $pola->nama_pola }}
                    </h5>
                </div>
                <form action="{{ route('jam_belajar.apply_pattern') }}" method="POST">
                    @csrf
                    <input type="hidden" name="pola_id" value="{{ $pola->id }}">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Terapkan Ke Hari <span class="text-danger">*</span></label>
                            <div class="row">
                                @foreach($days as $day)
                                <div class="col-6 mb-2">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="to_days[]" value="{{ $day }}" id="apply_{{ $pola->id }}_{{ $day }}">
                                        <label class="form-check-label" for="apply_{{ $pola->id }}_{{ $day }}">{{ $day }}</label>
                                    </div>
                                </div>
                                @endforeach
                            </div>
                        </div>
                        <div class="mb-3">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="replace" id="apply_replace_{{ $pola->id }}" value="1">
                                <label class="form-check-label" for="apply_replace_{{ $pola->id }}">
                                    <strong>Hapus jam yang sudah ada</strong>
                                </label>
                            </div>
                            <small class="text-muted d-block">Jika dicentang, jam lama akan dihapus terlebih dahulu</small>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary">
                            <i class="ti ti-check me-1"></i>Terapkan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    @empty
    <div class="col-12">
        <div class="card border-0 shadow-sm">
            <div class="card-body text-center py-5">
                <i class="ti ti-layout-grid" style="font-size: 3rem; opacity: 0.3;"></i>
                <h5 class="mt-3 text-muted">Belum Ada Pola yang Disimpan</h5>
                <p class="text-muted mb-3">Anda belum menyimpan pola jam apapun. Buat pola pertama Anda dari halaman jam belajar.</p>
                <a href="{{ route('jam_belajar.index') }}" class="btn btn-primary">
                    <i class="ti ti-arrow-left me-2"></i>Kembali ke Jam Belajar
                </a>
            </div>
        </div>
    </div>
    @endforelse
</div>

@if($polas->count() > 0)
<div class="row mt-4">
    <div class="col-12">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <h6 class="fw-bold mb-3"><i class="ti ti-info-circle me-2"></i>Cara Menggunakan Pola Jam</h6>
                <div class="row">
                    <div class="col-md-6">
                        <h6 class="small fw-semibold">1. Simpan Pola</h6>
                        <p class="small text-muted">Dari halaman Jam Belajar, klik tombol "Simpan Pola" untuk menyimpan pola jam dari salah satu hari sebagai template.</p>
                    </div>
                    <div class="col-md-6">
                        <h6 class="small fw-semibold">2. Terapkan Pola</h6>
                        <p class="small text-muted">Di halaman ini, klik "Terapkan Pola" untuk menerapkan template ke satu atau lebih hari. Optionka untuk menghapus jam yang sudah ada.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endif

@endsection
