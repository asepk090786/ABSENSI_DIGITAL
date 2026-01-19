@extends('layouts.app', ['pageSlug' => 'rencana_pembelajaran'])

@section('title', 'Detail Rencana Pembelajaran')

@section('content')
<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header">
                <div class="row align-items-center">
                    <div class="col">
                        <h4 class="card-title mb-0">{{ $item->judul }}</h4>
                    </div>
                    <div class="col-auto">
                        <div class="btn-list">
                            <a href="{{ route('rencana_pembelajaran.edit', $item->id) }}" class="btn btn-primary btn-sm">
                                <i class="ti ti-edit me-1"></i>Edit
                            </a>
                            <a href="{{ route('rencana_pembelajaran.index', ['mata_pelajaran_id' => $item->mata_pelajaran_id, 'tingkat' => $item->kelas->tingkat_kelas]) }}" class="btn btn-secondary btn-sm">
                                <i class="ti ti-arrow-left me-1"></i>Kembali
                            </a>
                        </div>
                    </div>
                </div>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label text-muted">Mata Pelajaran</label>
                        <p class="form-control-plaintext">{{ $item->mataPelajaran->nama_mapel }}</p>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label text-muted">Kelas</label>
                        <p class="form-control-plaintext">{{ $item->kelas->nama_kelas }}</p>
                    </div>

                    <div class="col-md-6 mb-3">
                        <label class="form-label text-muted">Status</label>
                        <p class="form-control-plaintext">
                            <span class="badge bg-{{ $item->status === 'published' ? 'success' : 'warning' }}">
                                {{ ucfirst($item->status) }}
                            </span>
                        </p>
                    </div>

                    @if($item->tanggal_mulai || $item->tanggal_selesai)
                    <div class="col-md-6 mb-3">
                        <label class="form-label text-muted">Periode</label>
                        <p class="form-control-plaintext">
                            @if($item->tanggal_mulai)
                                {{ $item->tanggal_mulai->format('d/m/Y') }}
                                @if($item->tanggal_selesai)
                                    - {{ $item->tanggal_selesai->format('d/m/Y') }}
                                @endif
                            @else
                                -
                            @endif
                        </p>
                    </div>
                    @endif

                    @if($item->deskripsi)
                    <div class="col-md-12 mb-3">
                        <label class="form-label text-muted">Deskripsi</label>
                        <p class="form-control-plaintext">{!! nl2br(e($item->deskripsi)) !!}</p>
                    </div>
                    @endif

                    @if($item->tujuan)
                    <div class="col-md-12 mb-3">
                        <label class="form-label text-muted">Tujuan Pembelajaran</label>
                        <p class="form-control-plaintext">{!! nl2br(e($item->tujuan)) !!}</p>
                    </div>
                    @endif

                    @if($item->metode)
                    <div class="col-md-12 mb-3">
                        <label class="form-label text-muted">Metode Pembelajaran</label>
                        <p class="form-control-plaintext">{!! nl2br(e($item->metode)) !!}</p>
                    </div>
                    @endif

                    @if($item->media)
                    <div class="col-md-12 mb-3">
                        <label class="form-label text-muted">Media Pembelajaran</label>
                        <p class="form-control-plaintext">{!! nl2br(e($item->media)) !!}</p>
                    </div>
                    @endif

                    @if($item->sumber)
                    <div class="col-md-12 mb-3">
                        <label class="form-label text-muted">Sumber Belajar</label>
                        <p class="form-control-plaintext">{!! nl2br(e($item->sumber)) !!}</p>
                    </div>
                    @endif

                    @if($item->penilaian)
                    <div class="col-md-12 mb-3">
                        <label class="form-label text-muted">Penilaian</label>
                        <p class="form-control-plaintext">{!! nl2br(e($item->penilaian)) !!}</p>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
