@extends('layouts.app', ['pageSlug' => 'modul_ajar'])

@section('title', 'Editor Modul')

@section('content')
<div class="card shadow-sm border-0">
    <div class="card-header border-0">
        <div class="d-flex justify-content-between align-items-start gap-3 flex-wrap">
            <div>
                <h3 class="fw-semibold mb-1">Editor Modul</h3>
                <p class="text-muted mb-0">Upload file DOCX modul ajar untuk mengedit di editor.</p>
            </div>
            <a href="{{ route('rencana_pembelajaran.index') }}" class="btn btn-outline-secondary btn-sm">
                <i class="ti ti-arrow-left me-1"></i>Kembali ke Daftar
            </a>
        </div>
    </div>
    <div class="card-body">
        <form action="{{ route('rencana_pembelajaran.editor_upload') }}" method="POST" enctype="multipart/form-data" class="row g-3">
            @csrf
            <div class="col-md-6">
                <label class="form-label">Judul Modul Ajar</label>
                <input type="text" name="judul" class="form-control" value="{{ old('judul') }}" placeholder="Masukkan judul modul ajar">
                @error('judul')
                    <div class="text-danger mt-1">{{ $message }}</div>
                @enderror
            </div>
            <div class="col-md-6">
                <label class="form-label">File DOCX <span class="text-danger">*</span></label>
                <input type="file" name="file" class="form-control" accept=".docx" required>
                @error('file')
                    <div class="text-danger mt-1">{{ $message }}</div>
                @enderror
            </div>
            <div class="col-12">
                <button type="submit" class="btn btn-primary">
                    <i class="ti ti-upload me-1"></i>Upload DOCX
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
