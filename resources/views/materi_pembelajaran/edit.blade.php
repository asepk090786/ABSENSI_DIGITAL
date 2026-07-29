@extends('layouts.app', ['pageSlug' => 'materi_pembelajaran'])

@section('title', 'Edit Materi Pembelajaran')

@section('content')
<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header border-0 pt-3 pb-2">
                <h4 class="card-title fw-semibold">Edit Materi Pembelajaran</h4>
                <p class="text-muted mt-1">{{ $rencanaPembelajaran->judul }}</p>
            </div>
            <div class="card-body">
                <form action="{{ route('materi_pembelajaran.update', $materiPembelajaran->id) }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')

                    <div class="row">
                        <div class="col-md-12 mb-3">
                            <label class="form-label">Nama Kegiatan <span class="text-danger">*</span></label>
                            <input 
                                type="text" 
                                name="nama_kegiatan" 
                                class="form-control @error('nama_kegiatan') is-invalid @enderror" 
                                value="{{ old('nama_kegiatan', $materiPembelajaran->nama_kegiatan) }}" 
                                required
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
                            >{{ old('materi_pembelajaran', $materiPembelajaran->materi_pembelajaran) }}</textarea>
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
                                value="{{ old('link_pembelajaran_daring', $materiPembelajaran->link_pembelajaran_daring) }}"
                            >
                            @error('link_pembelajaran_daring')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="text-muted">Masukkan link video pembelajaran atau materi online</small>
                        </div>

                        <div class="col-md-12 mb-3">
                            <label class="form-label">Bukti Pembelajaran (Foto/Gambar/Screenshot) <span class="text-muted">(Opsional)</span></label>
                            
                            @if($materiPembelajaran->bukti_pembelajaran)
                                <div class="mb-2">
                                    <div class="alert alert-info">
                                        <i class="ti ti-info-circle me-2"></i>File saat ini: 
                                        <a href="{{ Storage::disk('public')->url($materiPembelajaran->bukti_pembelajaran) }}" target="_blank" class="alert-link">
                                            Lihat Bukti
                                        </a>
                                    </div>
                                    <div class="mb-2">
                                        <img 
                                            src="{{ Storage::disk('public')->url($materiPembelajaran->bukti_pembelajaran) }}" 
                                            alt="Bukti Pembelajaran"
                                            class="img-thumbnail"
                                            style="max-width: 300px;"
                                        >
                                    </div>
                                </div>
                            @endif

                            <input 
                                type="file" 
                                name="bukti_pembelajaran" 
                                class="form-control @error('bukti_pembelajaran') is-invalid @enderror"
                                accept="image/*"
                            >
                            @error('bukti_pembelajaran')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="text-muted">Biarkan kosong jika tidak ingin mengubah. Format: JPEG, PNG, JPG, GIF, WebP (Ukuran maksimal: 5MB)</small>
                        </div>

                        <div class="col-md-12 mb-3">
                            <label class="form-label">Status <span class="text-danger">*</span></label>
                            <select name="status" class="form-select @error('status') is-invalid @enderror" required>
                                <option value="">-- Pilih Status --</option>
                                <option value="draft" {{ old('status', $materiPembelajaran->status) === 'draft' ? 'selected' : '' }}>Draft</option>
                                <option value="published" {{ old('status', $materiPembelajaran->status) === 'published' ? 'selected' : '' }}>Dipublikasikan</option>
                            </select>
                            @error('status')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-12">
                            <div class="form-footer">
                                <a href="{{ route('materi_pembelajaran.index', ['rencana_pembelajaran_id' => $rencanaPembelajaran->id]) }}" class="btn btn-link">Batal</a>
                                <button type="submit" class="btn btn-primary">
                                    <i class="ti ti-check me-2"></i>Update Materi
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
