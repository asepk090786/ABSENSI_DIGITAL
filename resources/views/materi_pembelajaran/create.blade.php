@extends('layouts.app', ['pageSlug' => 'materi_pembelajaran'])

@section('title', 'Tambah Materi Pembelajaran')

@section('content')
<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header border-0 pt-3 pb-2">
                <h4 class="card-title fw-semibold">Tambah Materi Pembelajaran</h4>
                <p class="text-muted mt-1">{{ $rencanaPembelajaran->judul }}</p>
            </div>
            <div class="card-body">
                <form action="{{ route('materi_pembelajaran.store') }}" method="POST" enctype="multipart/form-data">
                    @csrf

                    <input type="hidden" name="rencana_pembelajaran_id" value="{{ $rencanaPembelajaran->id }}">

                    <div class="row">
                        <div class="col-md-12 mb-3">
                            <label class="form-label">Nama Kegiatan <span class="text-danger">*</span></label>
                            <input 
                                type="text" 
                                name="nama_kegiatan" 
                                class="form-control @error('nama_kegiatan') is-invalid @enderror" 
                                value="{{ old('nama_kegiatan') }}" 
                                required
                                placeholder="Contoh: Pembelajaran Operasi Hitung"
                            >
                            @error('nama_kegiatan')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-12 mb-3">
                            <label class="form-label">Materi Pembelajaran <span class="text-danger">*</span></label>
                            <textarea 
                                name="materi_pembelajaran" 
                                class="form-control @error('materi_pembelajaran') is-invalid @enderror" 
                                rows="8" 
                                required
                                placeholder="Jelaskan materi pembelajaran..."
                            >{{ old('materi_pembelajaran') }}</textarea>
                            @error('materi_pembelajaran')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="text-muted">Anda dapat menuliskan materi secara detail di sini</small>
                        </div>

                        <div class="col-md-12 mb-3">
                            <label class="form-label">Link Pembelajaran Daring <span class="text-muted">(Opsional)</span></label>
                            <input 
                                type="url" 
                                name="link_pembelajaran_daring" 
                                class="form-control @error('link_pembelajaran_daring') is-invalid @enderror" 
                                value="{{ old('link_pembelajaran_daring') }}"
                                placeholder="https://example.com"
                            >
                            @error('link_pembelajaran_daring')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="text-muted">Masukkan link video pembelajaran atau materi online (harus format URL yang valid)</small>
                        </div>

                        <div class="col-md-12 mb-3">
                            <label class="form-label">Bukti Pembelajaran (Foto/Gambar/Screenshot) <span class="text-muted">(Opsional)</span></label>
                            <input 
                                type="file" 
                                name="bukti_pembelajaran" 
                                class="form-control @error('bukti_pembelajaran') is-invalid @enderror"
                                accept="image/*"
                            >
                            @error('bukti_pembelajaran')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="text-muted">Format: JPEG, PNG, JPG, GIF, WebP (Ukuran maksimal: 5MB)</small>
                        </div>

                        <div class="col-md-12 mb-3">
                            <label class="form-label">Status <span class="text-danger">*</span></label>
                            <select name="status" class="form-select @error('status') is-invalid @enderror" required>
                                <option value="">-- Pilih Status --</option>
                                <option value="draft" {{ old('status') === 'draft' ? 'selected' : '' }}>Draft</option>
                                <option value="published" {{ old('status') === 'published' ? 'selected' : '' }}>Dipublikasikan</option>
                            </select>
                            @error('status')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-12">
                            <div class="form-footer">
                                <a href="{{ route('materi_pembelajaran.index', ['rencana_pembelajaran_id' => $rencanaPembelajaran->id]) }}" class="btn btn-link">Batal</a>
                                <button type="submit" class="btn btn-primary">
                                    <i class="ti ti-check me-2"></i>Simpan Materi
                                </button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

@push('js')
<script>
// Initialize tiny editor for materi_pembelajaran if you have it
if (document.querySelectorAll('.tiny-editor').length > 0) {
    tinymce.init({
        selector: '.tiny-editor',
        plugins: 'advlist autolink lists link image charmap print preview hr anchor searchreplace wordcount visualblocks visualchars code fullscreen insertdatetime media nonbreaking table contextmenu directionality emoticons paste textcolor colorpicker textpattern',
        toolbar: 'formatselect | bold italic strikethrough forecolor backcolor | link image media | alignleft aligncenter alignright alignjustify | numlist bullist indent outdent | fullscreen',
        height: 300,
        menubar: false
    });
}
</script>
@endpush
@endsection
