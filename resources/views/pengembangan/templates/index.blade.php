@extends('layouts.app')

@section('title','Template Sertifikat')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h3>Template Sertifikat</h3>
        <a href="{{ route('pengembangan.templates.create') }}" class="btn btn-primary">Buat Template Baru</a>
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <div class="card">
        <div class="card-body">
            <table class="table">
                <thead>
                    <tr><th>Nama</th><th>Format</th><th>Dibuat</th><th>Aksi</th></tr>
                </thead>
                <tbody>
                    @foreach($items as $it)
                        <tr>
                            <td>{{ $it->nama }}</td>
                            <td>{{ strtoupper($it->output_format ?? 'pdf') }}</td>
                            <td>{{ \Carbon\Carbon::parse($it->created_at)->format('d-m-Y') }}</td>
                            <td>
                                <button type="button"
                                        class="btn btn-sm btn-info preview-template-btn"
                                        data-template-name="{{ e($it->nama) }}"
                                        data-template-html="{{ htmlspecialchars($it->template_html ?? '', ENT_QUOTES, 'UTF-8') }}"
                                        data-background-image="{{ $it->background_image ? url('storage/' . $it->background_image) : '' }}"
                                        data-placeholder-positions="{{ base64_encode($it->placeholder_positions ?? '') }}"
                                        data-page-size="{{ e($it->page_size ?? 'A4') }}"
                                        data-page-orientation="{{ e($it->page_orientation ?? 'portrait') }}">
                                    Preview
                                </button>
                                <a href="{{ route('pengembangan.templates.edit', $it->id) }}" class="btn btn-sm btn-secondary">Edit</a>
                                <form method="POST" action="{{ route('pengembangan.templates.destroy', $it->id) }}" style="display:inline-block" onsubmit="return confirm('Hapus template?')">
                                    @csrf
                                    @method('DELETE')
                                    <button class="btn btn-sm btn-danger">Hapus</button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <div class="modal fade" id="templatePreviewModal" tabindex="-1" aria-labelledby="templatePreviewModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="templatePreviewModalLabel">Preview Template Sertifikat</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div id="templatePreviewModalBody" class="border rounded bg-white p-3" style="min-height: 320px;"></div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('js')
@include('pengembangan.templates.partials.template_preview_renderer')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const buttons = document.querySelectorAll('.preview-template-btn');
        const modalBody = document.getElementById('templatePreviewModalBody');
        const previewModal = document.getElementById('templatePreviewModal');
        const modalTitle = document.getElementById('templatePreviewModalLabel');

        buttons.forEach(function (button) {
            button.addEventListener('click', function () {
                const encodedHtml = button.getAttribute('data-template-html') || '';
                const backgroundImage = button.getAttribute('data-background-image') || '';
                const templateName = button.getAttribute('data-template-name') || 'Template';
                const pageSize = button.getAttribute('data-page-size') || 'A4';
                const pageOrientation = button.getAttribute('data-page-orientation') || 'portrait';
                const rawPositionsBase64 = button.getAttribute('data-placeholder-positions') || '';
                let positions = null;
                try {
                    const rawPositions = rawPositionsBase64 ? atob(rawPositionsBase64) : '';
                    positions = rawPositions ? JSON.parse(rawPositions) : null;
                } catch (e) {
                    positions = null;
                }

                if (modalTitle) {
                    modalTitle.textContent = 'Preview Template - ' + templateName;
                }

                const previewWrapper = document.createElement('div');
                previewWrapper.style.position = 'relative';
                previewWrapper.style.width = '100%';
                previewWrapper.style.maxWidth = '900px';
                previewWrapper.style.margin = '0 auto';
                previewWrapper.style.border = '1px solid #dee2e6';
                previewWrapper.style.borderRadius = '8px';
                previewWrapper.style.overflow = 'hidden';
                previewWrapper.style.background = '#fff';
                previewWrapper.style.aspectRatio = window.TemplatePreviewRenderer.getAspectRatio(pageSize, pageOrientation);

                if (modalBody) {
                    modalBody.innerHTML = '';
                    modalBody.appendChild(previewWrapper);
                }

                window.TemplatePreviewRenderer.renderCanvasPreview(previewWrapper, {
                    width: 900,
                    height: 600,
                    backgroundImage: backgroundImage,
                    positions: positions,
                    values: {
                        name: 'Nama Peserta',
                        'kegiatan->nama_kegiatan': 'Nama Kegiatan Contoh',
                        'kegiatan->tema_kegiatan': 'Tema Kegiatan Contoh',
                        barcode: 'ABC123-VERIFY',
                        nomor_surat: '123/SMAN1/PONTANG'
                    }
                });

                if (previewModal && window.bootstrap && window.bootstrap.Modal) {
                    const modal = new window.bootstrap.Modal(previewModal);
                    modal.show();
                }
            });
        });
    });
</script>
@endpush
