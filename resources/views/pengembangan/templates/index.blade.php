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
                                        data-placeholder-positions="{{ e($it->placeholder_positions ?? '') }}"
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
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const buttons = document.querySelectorAll('.preview-template-btn');
        const modalBody = document.getElementById('templatePreviewModalBody');
        const previewModal = document.getElementById('templatePreviewModal');
        const modalTitle = document.getElementById('templatePreviewModalLabel');
        const placeholderLabelMap = {
            dragName: 'Nama Peserta',
            dragNamaKegiatan: 'Nama Kegiatan',
            dragTemaKegiatan: 'Tema Kegiatan',
            dragNomorSurat: 'Nomor Surat',
            dragBarcode: 'No. Sertifikat'
        };

        function getDefaultPlaceholderPositions() {
            return {
                dragName: { left: '50%', top: '30%', transform: 'translate(-50%,-50%)' },
                dragNamaKegiatan: { left: '50%', top: '40%', transform: 'translate(-50%,-50%)' },
                dragTemaKegiatan: { left: '50%', top: '50%', transform: 'translate(-50%,-50%)' },
                dragNomorSurat: { left: '50%', top: '60%', transform: 'translate(-50%,-50%)' },
                dragBarcode: { left: '50%', top: '80%', transform: 'translate(-50%,-50%)' }
            };
        }

        function parsePlaceholderPositions(value) {
            if (!value) return null;
            try {
                return JSON.parse(value);
            } catch (e) {
                return null;
            }
        }

        buttons.forEach(function (button) {
            button.addEventListener('click', function () {
                const encodedHtml = button.getAttribute('data-template-html') || '';
                const backgroundImage = button.getAttribute('data-background-image') || '';
                const templateName = button.getAttribute('data-template-name') || 'Template';
                const pageSize = button.getAttribute('data-page-size') || 'A4';
                const pageOrientation = button.getAttribute('data-page-orientation') || 'portrait';
                const storedPositions = parsePlaceholderPositions(button.getAttribute('data-placeholder-positions') || '');
                const positions = storedPositions || getDefaultPlaceholderPositions();
                const ratio = pageSize === 'Letter'
                    ? (pageOrientation === 'landscape' ? '279 / 216' : '216 / 279')
                    : (pageOrientation === 'landscape' ? '297 / 210' : '210 / 297');

                if (modalTitle) {
                    modalTitle.textContent = 'Preview Template - ' + templateName;
                }

                const decodeHtmlEntities = function (value) {
                    const textarea = document.createElement('textarea');
                    textarea.innerHTML = value;
                    return textarea.value;
                };

                let previewMarkup = encodedHtml ? decodeHtmlEntities(encodedHtml) : '<div class="text-muted">Template belum memiliki isi preview.</div>';
                previewMarkup = previewMarkup
                    .replace(/@?\{\{name\}\}/g, 'Nama Peserta')
                    .replace(/@?\{\{nomor_surat\}\}/g, '123/SMAN1/PONTANG')
                    .replace(/@?\{\{kegiatan->nama_kegiatan\}\}/g, 'Nama Kegiatan Contoh')
                    .replace(/@?\{\{kegiatan->tema_kegiatan\}\}/g, 'Tema Kegiatan Contoh')
                    .replace(/@?\{\{barcode\}\}/g, 'ABC123-VERIFY');

                let previewHtml = '<div style="position:relative;width:100%;max-width:900px;margin:0 auto;border:1px solid #dee2e6;border-radius:8px;overflow:hidden;background:#fff;aspect-ratio:' + ratio + ';">';
                if (backgroundImage) {
                    previewHtml += '<div style="position:absolute;inset:0;background:url(\'' + backgroundImage + '\') center/contain no-repeat;background-color:#fff;opacity:0.15;pointer-events:none;"></div>';
                }

                previewHtml += '<div style="position:relative;z-index:1;padding:1.25rem;height:100%;overflow:auto;">' + previewMarkup + '</div></div>';

                if (modalBody) {
                    modalBody.innerHTML = previewHtml;
                }

                if (previewModal && window.bootstrap && window.bootstrap.Modal) {
                    const modal = new window.bootstrap.Modal(previewModal);
                    modal.show();
                }
            });
        });
    });
</script>
@endpush
