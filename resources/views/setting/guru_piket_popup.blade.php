<form action="{{ route('setting.guru_piket_popup.update') }}" method="POST">
    @csrf
    @method('PUT')
    <div class="alert alert-info small mb-4">
        Tulis dan format pengumuman seperti di pengolah kata. Konten ini ditampilkan kepada guru yang mendapat tugas piket pada hari login.
    </div>

    <div class="mb-3">
        <label for="guru-piket-popup-title" class="form-label">Judul popup</label>
        <input
            type="text"
            id="guru-piket-popup-title"
            name="title"
            class="form-control"
            maxlength="120"
            value="{{ old('title', $settings['title'] ?? '') }}"
            required
        >
        @error('title')
            <div class="invalid-feedback d-block">{{ $message }}</div>
        @enderror
    </div>

    <div class="mb-3">
        <label for="guru-piket-popup-content" class="form-label">Isi popup</label>
        <textarea
            id="guru-piket-popup-content"
            name="content"
            class="form-control"
            rows="12"
            required
        >{{ old('content', $settings['content'] ?? '') }}</textarea>
        <div class="form-text">Gunakan toolbar untuk pemformatan teks, perataan, daftar, tautan, dan tabel. Maksimal 30.000 karakter.</div>
        @error('content')
            <div class="invalid-feedback d-block">{{ $message }}</div>
        @enderror
    </div>

    <button type="submit" class="btn btn-primary">Simpan Konten Popup</button>
</form>

@push('js')
<script src="https://cdn.jsdelivr.net/npm/tinymce@6.7.0/tinymce.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        if (!window.tinymce) {
            return;
        }

        window.tinymce.init({
            selector: '#guru-piket-popup-content',
            height: 420,
            menubar: 'edit view insert format table',
            plugins: 'advlist autolink lists link table paste wordcount',
            toolbar: 'undo redo | blocks | bold italic underline strikethrough | forecolor backcolor | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | link table | removeformat',
            toolbar_mode: 'sliding',
            paste_as_text: false,
            license_key: 'gpl',
            content_style: 'body { color: #212529; font-family: Arial, sans-serif; font-size: 14px; } table { border-collapse: collapse; width: 100%; } td, th { border: 1px solid #adb5bd; padding: 6px; }'
        });
    });
</script>
@endpush
