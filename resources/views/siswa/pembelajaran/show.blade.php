@extends('layouts.app', ['pageSlug' => 'siswa_pembelajaran'])

@section('title', 'Detail Materi Pembelajaran')

@section('content')
<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header border-0 pt-3 pb-2">
                <div class="row align-items-center">
                    <div class="col">
                        <h4 class="card-title fw-semibold">{{ $materi->nama_kegiatan }}</h4>
                        <p class="text-muted mt-1">{{ $materi->rencanaPembelajaran->judul }}</p>
                    </div>
                    <div class="col-auto">
                        <span class="badge bg-{{ $materi->status === 'published' ? 'success' : 'warning' }} text-white">
                            {{ ucfirst($materi->status) }}
                        </span>
                    </div>
                </div>
            </div>
            <div class="card-body">
                <div class="row mb-3">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Mata Pelajaran</label>
                            <p>{{ $materi->rencanaPembelajaran->mataPelajaran->nama_mapel ?? '-' }}</p>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Tanggal Dibuat</label>
                            <p>{{ $materi->created_at->format('d/m/Y H:i') }}</p>
                        </div>
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-12">
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Materi Pembelajaran</label>
                            <div class="p-3 bg-light rounded">
                                {!! nl2br(e($materi->materi_pembelajaran)) !!}
                            </div>
                        </div>
                    </div>
                </div>

                @if($materi->link_pembelajaran_daring)
                    <div class="row mb-3">
                        <div class="col-md-12">
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Link Pembelajaran Daring</label>
                                <p>
                                    <a href="{{ $materi->link_pembelajaran_daring }}" target="_blank" class="btn btn-outline-info btn-sm">
                                        <i class="ti ti-external-link me-1"></i>Buka Link
                                    </a>
                                </p>
                            </div>
                        </div>
                    </div>
                @endif

                @if($materi->bukti_pembelajaran)
                    <div class="row mb-3">
                        <div class="col-md-12">
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Bukti Pembelajaran</label>
                                <div>
                                    <img
                                        src="{{ Storage::disk('public')->url($materi->bukti_pembelajaran) }}"
                                        alt="Bukti Pembelajaran"
                                        class="img-thumbnail"
                                        style="max-width: 500px;"
                                    >
                                    <br>
                                    <a
                                        href="{{ Storage::disk('public')->url($materi->bukti_pembelajaran) }}"
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
                            <a href="{{ route('siswa.pembelajaran.materi') }}" class="btn btn-secondary">
                                <i class="ti ti-arrow-left me-1"></i>Kembali
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
