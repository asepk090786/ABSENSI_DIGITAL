@extends('layouts.app')

@section('title', isset($title) ? 'Edit Help — ' . $title : 'Edit Help')

@section('page-header')
    <div class="page-header">
        <h2 class="page-title">Edit Halaman Help</h2>
    </div>
@endsection

@section('content')
    <div class="card">
        <div class="card-body">
            @if(session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
            @endif
            @if(session('error'))
                <div class="alert alert-danger">{{ session('error') }}</div>
            @endif

            <form method="POST" action="{{ route('help.admin.update', $slug) }}">
                @csrf
                @method('PUT')
                <div class="mb-3">
                    <label class="form-label">Judul</label>
                    <input type="text" name="title" class="form-control" value="{{ old('title', $title ?? '') }}" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Konten Help (HTML)</label>
                    <textarea id="helpContentEditor" name="content" class="form-control" rows="20">{{ old('content', $content ?? '') }}</textarea>
                </div>
                <div class="mb-3">
                    <label class="form-label">Link Video (YouTube, dll)</label>
                    <input type="url" name="video_link" class="form-control" value="{{ old('video_link', $video_link ?? '') }}" placeholder="https://www.youtube.com/watch?v=...">
                    <small class="text-muted">Opsional. Link video YouTube atau URL video lain yang bisa di-embed.</small>
                </div>
                <button class="btn btn-primary">Simpan Perubahan</button>
            </form>
        </div>
    </div>
@endsection
<script src="https://unpkg.com/tinymce@6/tinymce.min.js" referrerpolicy="origin"></script>
<script>
    document.addEventListener('DOMContentLoaded', function(){
        if (window.tinymce) {
            tinymce.init({
                selector: '#helpContentEditor',
                height: 600,
                menubar: true,
                plugins: [
                    'advlist autolink lists link image charmap preview anchor',
                    'searchreplace visualblocks code fullscreen',
                    'insertdatetime media table paste help wordcount'
                ],
                toolbar: 'undo redo | formatselect | bold italic backcolor | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | link image media | removeformat | code | preview',
                relative_urls: false,
                remove_script_host: false,
                convert_urls: true,
                content_css: [
                    'https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css'
                ],
                image_caption: true,
                quickbars_selection_toolbar: 'bold italic | quicklink h2 h3 blockquote',
            });
        }
    });
</script>
