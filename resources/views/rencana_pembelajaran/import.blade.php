@extends('layouts.app', ['pageSlug' => 'rencana_pembelajaran'])

@section('title', 'Import Rencana Pembelajaran')

@section('content')
<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header border-0 pt-3 pb-2">
                <div class="row align-items-center">
                    <div class="col">
                        <h4 class="card-title fw-semibold m-0">Import Rencana Pembelajaran dari Word</h4>
                    </div>
                    <div class="col-auto">
                        <a href="{{ route('rencana_pembelajaran.index', ['mata_pelajaran_id' => optional($mataPelajaran)->id, 'tingkat' => $tingkat]) }}" class="btn btn-secondary btn-sm">
                            <i class="ti ti-arrow-left me-1"></i>Kembali
                        </a>
                    </div>
                </div>
            </div>
            <div class="card-body">
                @if(session('error'))
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <i class="ti ti-alert-circle me-2"></i>{{ session('error') }}
                        <button type="button" class="close" data-bs-dismiss="alert"><span aria-hidden="true">&times;</span></button>
                    </div>
                @endif

                <form action="{{ route('rencana_pembelajaran.import_word') }}" method="POST" enctype="multipart/form-data">
                    @csrf

                    <input type="hidden" name="mata_pelajaran_id" value="{{ optional($mataPelajaran)->id }}">

                    <div class="row mb-4">
                        <div class="col-md-12">
                            <div class="alert alert-info">
                                <i class="ti ti-info-circle me-2"></i>
                                <strong>Petunjuk:</strong>
                                <ol class="mb-0 mt-2">
                                    <li>Download template Word terlebih dahulu</li>
                                    <li>Isi data sesuai format template, termasuk kolom <strong>Status</strong> (Draft atau Published)</li>
                                    <li>Setelah upload, Anda akan melihat preview HTML dan bisa mengedit ringan sebelum menyimpan</li>
                                    <li>Pastikan informasi sudah benar sebelum konfirmasi simpan</li>
                                </ol>
                            </div>

                            <div class="mb-2">
                                <a href="{{ route('rencana_pembelajaran.template') }}" class="btn btn-outline-primary btn-sm">
                                    <i class="ti ti-download me-1"></i>Download Template Word
                                </a>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-12 mb-2">
                            <label class="form-label">Mata Pelajaran</label>
                            <input type="text" class="form-control" value="{{ optional($mataPelajaran)->nama_mapel ?? '' }}" disabled>
                        </div>

                        <div class="col-md-12 mb-2">
                            <label class="form-label">Pilih Kelas <span class="text-danger">*</span></label>
                            <div class="@error('kelas_ids') is-invalid @enderror">
                                @forelse($kelas as $k)
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="kelas_ids[]" value="{{ $k->id }}" id="kelas_{{ $k->id }}" {{ in_array($k->id, old('kelas_ids', [])) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="kelas_{{ $k->id }}">
                                            {{ $k->nama_kelas }}
                                        </label>
                                    </div>
                                @empty
                                    <div class="alert alert-info alert-sm mb-0">
                                        <i class="ti ti-info-circle me-2"></i>Belum ada kelas untuk mata pelajaran ini
                                    </div>
                                @endforelse
                            </div>
                            @error('kelas_ids')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-12 mb-2">
                            <label class="form-label">File Word (.docx) <span class="text-danger">*</span></label>
                            <input type="file" name="file" class="form-control @error('file') is-invalid @enderror" accept=".docx" required>
                            <small class="form-hint">Format: .docx, maksimal 5MB</small>
                            @error('file')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="form-footer">
                        <a href="{{ route('rencana_pembelajaran.index', ['mata_pelajaran_id' => $mataPelajaran->id, 'tingkat' => $tingkat]) }}" class="btn btn-link">Batal</a>
                        <button type="submit" class="btn btn-primary">
                            <i class="ti ti-upload me-1"></i>Import dari Word
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
