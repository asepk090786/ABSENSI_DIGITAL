@extends('layouts.app', ['pageSlug' => 'materi_pembelajaran'])

@section('title', 'Lihat Materi Pembelajaran')

@section('content')
<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header border-0 pt-3 pb-2">
                <div class="row align-items-center">
                    <div class="col">
                        <h4 class="card-title fw-semibold">{{ $materiPembelajaran->nama_kegiatan }}</h4>
                        <p class="text-muted mt-1">{{ $materiPembelajaran->rencanaPembelajaran->judul }}</p>
                    </div>
                    <div class="col-auto">
                        <span class="badge {{ $materiPembelajaran->status === 'published' ? 'bg-success text-white' : 'bg-warning text-dark' }}">
                            {{ ucfirst($materiPembelajaran->status) }}
                        </span>
                    </div>
                </div>
            </div>
            <div class="card-body">
                <div class="row mb-3">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Nama Kegiatan</label>
                            <p>{{ $materiPembelajaran->nama_kegiatan }}</p>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Tanggal Dibuat</label>
                            <p>{{ $materiPembelajaran->created_at->format('d/m/Y H:i') }}</p>
                        </div>
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-12">
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Materi Pembelajaran</label>
                            <div class="p-3 bg-light rounded">
                                {!! nl2br(e($materiPembelajaran->materi_pembelajaran)) !!}
                            </div>
                        </div>
                    </div>
                </div>

                @if($materiPembelajaran->link_pembelajaran_daring)
                    <div class="row mb-3">
                        <div class="col-md-12">
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Link Pembelajaran Daring</label>
                                <p>
                                    <a href="{{ $materiPembelajaran->link_pembelajaran_daring }}" target="_blank" class="btn btn-outline-info btn-sm">
                                        <i class="ti ti-external-link me-1"></i>Buka Link
                                    </a>
                                </p>
                            </div>
                        </div>
                    </div>
                @endif

                @if($materiPembelajaran->bukti_pembelajaran)
                    <div class="row mb-3">
                        <div class="col-md-12">
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Bukti Pembelajaran</label>
                                <div>
                                    <img 
                                        src="{{ Storage::disk('public')->url($materiPembelajaran->bukti_pembelajaran) }}" 
                                        alt="Bukti Pembelajaran"
                                        class="img-thumbnail"
                                        style="max-width: 500px;"
                                    >
                                    <br>
                                    <a 
                                        href="{{ Storage::disk('public')->url($materiPembelajaran->bukti_pembelajaran) }}" 
                                        target="_blank" 
                                        class="btn btn-outline-info btn-sm mt-2"
                                    >
                                        <i class="ti ti-download me-1"></i>Download Bukti
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                @endif

                <div class="row">
                    <div class="col-md-12">
                        <div class="form-footer">
                            <a href="{{ route('materi_pembelajaran.index', ['rencana_pembelajaran_id' => $materiPembelajaran->rencana_pembelajaran_id]) }}" class="btn btn-secondary">
                                <i class="ti ti-arrow-left me-1"></i>Kembali
                            </a>
                            <a href="{{ route('materi_pembelajaran.edit', $materiPembelajaran->id) }}" class="btn btn-primary">
                                <i class="ti ti-edit me-1"></i>Edit
                            </a>
                            <form action="{{ route('materi_pembelajaran.destroy', $materiPembelajaran->id) }}" method="POST" style="display:inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-danger" onclick="return confirm('Hapus materi pembelajaran ini?')">
                                    <i class="ti ti-trash me-1"></i>Hapus
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
