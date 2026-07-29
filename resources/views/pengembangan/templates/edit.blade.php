@extends('layouts.app')

@section('title','Edit Template Sertifikat')

@section('content')
    <h3>Edit Template Sertifikat</h3>
    <form method="POST" action="{{ route('pengembangan.templates.update', $item->id) }}" enctype="multipart/form-data">
        @csrf
        @method('PUT')
        <div class="mb-3">
            <label class="form-label">Nama Template</label>
            <input name="nama" class="form-control" value="{{ old('nama', $item->nama) }}" />
        </div>
        @if ($errors->any())
            <div class="alert alert-danger">
                <strong>Perbaiki kesalahan berikut:</strong>
                <ul class="mb-0">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif
        <div class="row">
            <div class="col-md-6 mb-3">
                <label class="form-label">Format Output</label>
                <select name="output_format" class="form-control">
                    <option value="pdf" {{ old('output_format', $item->output_format ?? 'pdf') == 'pdf' ? 'selected' : '' }}>PDF</option>
                    <option value="docx" {{ old('output_format', $item->output_format ?? 'pdf') == 'docx' ? 'selected' : '' }}>Word (.docx)</option>
                    <option value="xlsx" {{ old('output_format', $item->output_format ?? 'pdf') == 'xlsx' ? 'selected' : '' }}>Excel (.xlsx)</option>
                    <option value="jpeg" {{ old('output_format', $item->output_format ?? 'pdf') == 'jpeg' ? 'selected' : '' }}>JPEG</option>
                </select>
            </div>
            <div class="col-md-3 mb-3">
                <label class="form-label">Ukuran Halaman</label>
                <select name="page_size" class="form-control">
                    <option value="A4" {{ old('page_size', $item->page_size ?? 'A4') == 'A4' ? 'selected' : '' }}>A4</option>
                    <option value="Letter" {{ old('page_size', $item->page_size ?? 'A4') == 'Letter' ? 'selected' : '' }}>Letter</option>
                </select>
            </div>
            <div class="col-md-3 mb-3">
                <label class="form-label">Orientasi</label>
                <select name="page_orientation" class="form-control">
                    <option value="portrait" {{ old('page_orientation', $item->page_orientation ?? 'portrait') == 'portrait' ? 'selected' : '' }}>Portrait</option>
                    <option value="landscape" {{ old('page_orientation', $item->page_orientation ?? 'portrait') == 'landscape' ? 'selected' : '' }}>Landscape</option>
                </select>
            </div>
        </div>
        <div class="mb-3">
            <label class="form-label">Upload Background Image</label>
            <input type="file" id="backgroundImageInput" name="background_image" class="form-control" accept="image/*" />
            <small class="text-muted">Upload JPEG/PNG sebagai latar sertifikat. Jika ada, editor akan menampilkan background dan teks dapat diposisikan.</small>
            <div id="backgroundPreviewContainer" class="mt-2" style="display:none;">
                <label class="form-label">Preview Background</label>
                <div class="border rounded p-2 bg-white">
                    <img id="backgroundPreview" src="" alt="Background preview" style="width:100%;max-height:240px;object-fit:contain;" />
                </div>
            </div>
        </div>
        <input type="hidden" id="placeholderPositions" name="placeholder_positions" value="{{ old('placeholder_positions', isset($item->placeholder_positions) ? $item->placeholder_positions : '') }}" />
        <div class="mb-3">
            <label class="form-label">Preset Template</label>
            <select id="templatePreset" class="form-control">
                <option value="">-- Pilih preset --</option>
                <option value="default">Preset Standar</option>
                <option value="word">Preset Word</option>
                <option value="jpeg">Preset JPEG</option>
            </select>
        </div>
        <div class="row g-3 mb-3">
            <div class="col-md-4">
                <label class="form-label">Pilih Placeholder</label>
                <select id="placeholderTarget" class="form-control">
                    <option value="all">Semua Placeholder</option>
                    <option value="dragName">Nama Peserta</option>
                    <option value="dragNamaKegiatan">Nama Kegiatan</option>
                    <option value="dragTemaKegiatan">Tema Kegiatan</option>
                    <option value="dragNomorSurat">Nomor Surat</option>
                    <option value="dragBarcode">No. Sertifikat</option>
                </select>
            </div>
            <div class="col-md-4">
                <label class="form-label">Font Placeholder</label>
                <select id="placeholderFontFamily" name="placeholder_font_family" class="form-control">
                    <option value="Arial">Arial</option>
                    <option value="'Times New Roman', Times, serif">Times New Roman</option>
                    <option value="Georgia">Georgia</option>
                    <option value="Verdana">Verdana</option>
                    <option value="Tahoma">Tahoma</option>
                    <option value="'Segoe UI', Tahoma, Geneva, Verdana, sans-serif">Segoe UI</option>
                    <option value="Calibri">Calibri</option>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label">Ukuran Teks</label>
                <select id="placeholderFontSize" name="placeholder_font_size" class="form-control">
                    <option value="12">12px</option>
                    <option value="14">14px</option>
                    <option value="16" selected>16px</option>
                    <option value="18">18px</option>
                    <option value="20">20px</option>
                    <option value="24">24px</option>
                    <option value="28">28px</option>
                    <option value="32">32px</option>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label">Warna Teks</label>
                <input id="placeholderFontColor" name="placeholder_font_color" type="color" class="form-control form-control-color" value="#000000" title="Pilih warna teks" />
            </div>
        </div>
        <div id="removedPlaceholderButtons" class="mb-3"></div>
        <div class="mb-3">
            <label class="form-label">Jenis Editor</label>
            <select id="editorModeSelect" name="editor_mode" class="form-control">
                <option value="image" {{ old('editor_mode', $item->editor_mode ?? 'image') == 'image' ? 'selected' : '' }}>Editor Gambar (Drag & Drop)</option>
                <option value="html" {{ old('editor_mode', $item->editor_mode ?? 'image') == 'html' ? 'selected' : '' }}>Editor HTML</option>
            </select>
            <small class="text-muted">Pilih editor gambar untuk menata placeholder secara visual, atau HTML untuk menulis markup langsung.</small>
        </div>
        <div id="htmlEditorContainer" class="mb-3">
            <label class="form-label">HTML Template</label>
            <textarea id="templateHtml" name="template_html" class="form-control" rows="12">{{ old('template_html', $item->template_html) }}</textarea>
            <small class="text-muted">Gunakan placeholder: <code>@{{name}}</code>, <code>@{{kegiatan->nama_kegiatan}}</code>, <code>@{{kegiatan->tema_kegiatan}}</code>, <code>@{{barcode}}</code></small>
        </div>

        <div id="imageEditorContainer" class="card mb-3">
            <div class="card-header d-flex justify-content-between align-items-center">
                <strong>Editor Sertifikat</strong>
                <button type="button" id="resetTemplatePositions" class="btn btn-sm btn-secondary">Reset Posisi</button>
            </div>
            <div class="card-body">
                @php
                    $backgroundUrl = null;
                    if (isset($item->background_image) && $item->background_image) {
                        $backgroundUrl = url('storage/' . $item->background_image);
                    }
                @endphp
                <div id="templateEditor" class="border rounded mx-auto" style="position:relative;width:100%;max-width:900px;aspect-ratio:210 / 297;background-color:#f8f9fa;background-size:contain;background-position:center;overflow:hidden;" data-background-url="{{ $backgroundUrl }}">
                    <div id="dragName" class="draggable-text" style="position:absolute;left:50%;top:30%;transform:translate(-50%,-50%);cursor:move;user-select:none;touch-action:none;padding:0.35rem 0.6rem;background:rgba(255,255,255,0.9);border:1px dashed #6c757d;border-radius:4px;">
                        Nama Peserta
                        <span class="remove-placeholder" style="position:absolute;top:-6px;right:-6px;width:18px;height:18px;line-height:16px;text-align:center;border-radius:50%;background:#fff;color:#dc3545;border:1px solid #dc3545;cursor:pointer;user-select:none;z-index:10;pointer-events:auto;">×</span>
                    </div>
                    <div id="dragNamaKegiatan" class="draggable-text" style="position:absolute;left:50%;top:40%;transform:translate(-50%,-50%);cursor:move;user-select:none;touch-action:none;padding:0.35rem 0.6rem;background:rgba(255,255,255,0.9);border:1px dashed #6c757d;border-radius:4px;">
                        @{{kegiatan->nama_kegiatan}}
                        <span class="remove-placeholder" style="position:absolute;top:-6px;right:-6px;width:18px;height:18px;line-height:16px;text-align:center;border-radius:50%;background:#fff;color:#dc3545;border:1px solid #dc3545;cursor:pointer;user-select:none;z-index:10;pointer-events:auto;">×</span>
                    </div>
                    <div id="dragTemaKegiatan" class="draggable-text" style="position:absolute;left:50%;top:50%;transform:translate(-50%,-50%);cursor:move;user-select:none;touch-action:none;padding:0.35rem 0.6rem;background:rgba(255,255,255,0.9);border:1px dashed #6c757d;border-radius:4px;">
                        @{{kegiatan->tema_kegiatan}}
                        <span class="remove-placeholder" style="position:absolute;top:-6px;right:-6px;width:18px;height:18px;line-height:16px;text-align:center;border-radius:50%;background:#fff;color:#dc3545;border:1px solid #dc3545;cursor:pointer;user-select:none;z-index:10;pointer-events:auto;">×</span>
                    </div>
                    <div id="dragNomorSurat" class="draggable-text" style="position:absolute;left:50%;top:60%;transform:translate(-50%,-50%);cursor:move;user-select:none;touch-action:none;padding:0.35rem 0.6rem;background:rgba(255,255,255,0.9);border:1px dashed #6c757d;border-radius:4px;">
                        Nomor Surat
                        <span class="remove-placeholder" style="position:absolute;top:-6px;right:-6px;width:18px;height:18px;line-height:16px;text-align:center;border-radius:50%;background:#fff;color:#dc3545;border:1px solid #dc3545;cursor:pointer;user-select:none;z-index:10;pointer-events:auto;">×</span>
                    </div>
                    <div id="dragBarcode" class="draggable-text" style="position:absolute;left:50%;top:80%;transform:translate(-50%,-50%);cursor:move;user-select:none;touch-action:none;padding:0.35rem 0.6rem;background:rgba(255,255,255,0.9);border:1px dashed #6c757d;border-radius:4px;">
                        No. Sertifikat
                        <span class="remove-placeholder" style="position:absolute;top:-6px;right:-6px;width:18px;height:18px;line-height:16px;text-align:center;border-radius:50%;background:#fff;color:#dc3545;border:1px solid #dc3545;cursor:pointer;user-select:none;z-index:10;pointer-events:auto;">×</span>
                    </div>
                </div>
                <p class="mt-2 text-muted">Tarik dan letakkan teks ke posisi yang diinginkan pada background.</p>
            </div>
        </div>

        <div class="mb-3">
            <div class="card bg-light border-secondary">
                <div class="card-header py-2 d-flex justify-content-between align-items-center">
                    <strong class="small">Contoh Template HTML</strong>
                    <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-toggle="collapse" data-bs-target="#htmlExampleCollapse" aria-expanded="false" aria-controls="htmlExampleCollapse">Lihat</button>
                </div>
                <div id="htmlExampleCollapse" class="collapse">
                    <div class="card-body p-3">
                        <div class="d-flex justify-content-end mb-2">
                            <button type="button" class="btn btn-sm btn-outline-info" data-bs-toggle="modal" data-bs-target="#htmlTemplateHelpModal">Bantuan HTML</button>
                        </div>
                        <pre class="mb-0" style="font-size:0.9rem; white-space:pre-wrap; word-break:break-word;">
&lt;div style="font-family: Arial; text-align:center; border:8px solid #0d6efd; padding:24px; max-width:900px; margin:auto;"&gt;
    &lt;h1 style="color:#0d6efd;"&gt;SERTIFIKAT&lt;/h1&gt;
    &lt;p&gt;Diberikan kepada&lt;/p&gt;
    &lt;h2&gt;@{{name}}&lt;/h2&gt;
    &lt;p&gt;Atas partisipasinya pada kegiatan:&lt;/p&gt;
    &lt;h3&gt;@{{kegiatan->nama_kegiatan}}&lt;/h3&gt;
    &lt;p&gt;Tema: @{{kegiatan->tema_kegiatan}}&lt;/p&gt;
    &lt;div style="margin-top:24px; font-size:0.9rem; color:#555;"&gt;Kode verifikasi: @{{barcode}}&lt;/div&gt;
&lt;/div&gt;
                        </pre>
                    </div>
                </div>
            </div>
        </div>

        <div class="card mb-3">
            <div class="card-header d-flex justify-content-between align-items-center">
                <strong>Preview</strong>
                <div>
                    <button type="button" id="previewTemplateBtn" class="btn btn-sm btn-outline-secondary">Update Preview</button>
                    <button type="button" id="openPreviewModalBtn" class="btn btn-sm btn-primary ms-2" data-bs-toggle="modal" data-bs-target="#certificatePreviewModal">Buka Preview</button>
                </div>
            </div>
            <div class="card-body">
                <div id="templatePreview" class="border rounded p-3 bg-light" style="min-height: 220px;"></div>
            </div>
        </div>

        <button class="btn btn-primary">Simpan</button>
    </form>

    <div class="modal fade" id="certificatePreviewModal" tabindex="-1" aria-labelledby="certificatePreviewModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-fullscreen modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="certificatePreviewModalLabel">Preview Sertifikat</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-0 bg-dark">
                    <div class="d-flex justify-content-center align-items-center" style="min-height:80vh; padding:1rem;">
                        <div id="certificatePreviewSheet" class="position-relative border bg-white" style="width:100%;max-width:1100px;aspect-ratio:210 / 297;background-size:contain;background-position:center;background-repeat:no-repeat;">
                            <div id="modalPreviewDragName" class="position-absolute" style="left:50%;top:30%;transform:translate(-50%,-50%);padding:0.35rem 0.6rem;background:rgba(255,255,255,0.9);border:1px dashed #6c757d;border-radius:4px;">Nama Peserta</div>
                            <div id="modalPreviewDragNamaKegiatan" class="position-absolute" style="left:50%;top:40%;transform:translate(-50%,-50%);padding:0.35rem 0.6rem;background:rgba(255,255,255,0.9);border:1px dashed #6c757d;border-radius:4px;">@{{kegiatan->nama_kegiatan}}</div>
                            <div id="modalPreviewDragTemaKegiatan" class="position-absolute" style="left:50%;top:50%;transform:translate(-50%,-50%);padding:0.35rem 0.6rem;background:rgba(255,255,255,0.9);border:1px dashed #6c757d;border-radius:4px;">@{{kegiatan->tema_kegiatan}}</div>
                            <div id="modalPreviewDragNomorSurat" class="position-absolute" style="left:50%;top:60%;transform:translate(-50%,-50%);padding:0.35rem 0.6rem;background:rgba(255,255,255,0.9);border:1px dashed #6c757d;border-radius:4px;">Nomor Surat</div>
                            <div id="modalPreviewDragBarcode" class="position-absolute" style="left:50%;top:80%;transform:translate(-50%,-50%);padding:0.35rem 0.6rem;background:rgba(255,255,255,0.9);border:1px dashed #6c757d;border-radius:4px;">No. Sertifikat</div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-primary" id="downloadPreviewBtn">Download</button>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="htmlTemplateHelpModal" tabindex="-1" aria-labelledby="htmlTemplateHelpModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="htmlTemplateHelpModalLabel">Contoh Script HTML</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <pre class="mb-0" style="font-size:0.9rem; white-space:pre-wrap; word-break:break-word;">&lt;div style="font-family: Arial; text-align:center; border:8px solid #0d6efd; padding:24px; max-width:900px; margin:auto;"&gt;
    &lt;h1 style="color:#0d6efd;"&gt;SERTIFIKAT&lt;/h1&gt;
    &lt;p&gt;Diberikan kepada&lt;/p&gt;
    &lt;h2&gt;@{{name}}&lt;/h2&gt;
    &lt;p&gt;Atas partisipasinya pada kegiatan:&lt;/p&gt;
    &lt;h3&gt;@{{kegiatan->nama_kegiatan}}&lt;/h3&gt;
    &lt;p&gt;Tema: @{{kegiatan->tema_kegiatan}}&lt;/p&gt;
    &lt;div style="margin-top:24px; font-size:0.9rem; color:#555;"&gt;Kode verifikasi: @{{barcode}}&lt;/div&gt;
&lt;/div&gt;</pre>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('js')
<script src="https://cdn.jsdelivr.net/npm/html2canvas@1.4.1/dist/html2canvas.min.js"></script>
<script src="https://cdn.ckeditor.com/ckeditor5/39.0.1/classic/ckeditor.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const textarea = document.getElementById('templateHtml');
        const preview = document.getElementById('templatePreview');
        const btn = document.getElementById('previewTemplateBtn');
        const editor = document.getElementById('templateEditor');
        const editorModeSelect = document.getElementById('editorModeSelect');
        const imageEditorContainer = document.getElementById('imageEditorContainer');
        const htmlEditorContainer = document.getElementById('htmlEditorContainer');
        const imgInput = document.getElementById('backgroundImageInput');
        const backgroundPreviewContainer = document.getElementById('backgroundPreviewContainer');
        const backgroundPreview = document.getElementById('backgroundPreview');
        const placeholderPositionsInput = document.getElementById('placeholderPositions');
        let draggableItems = Array.from(document.querySelectorAll('.draggable-text'));
        const fontFamilySelect = document.getElementById('placeholderFontFamily');
        const fontSizeSelect = document.getElementById('placeholderFontSize');
        const placeholderTarget = document.getElementById('placeholderTarget');
        const placeholderFontColor = document.getElementById('placeholderFontColor');
        const removedPlaceholderButtons = document.getElementById('removedPlaceholderButtons');
        const previewModalBtn = document.getElementById('openPreviewModalBtn');
        const downloadPreviewBtn = document.getElementById('downloadPreviewBtn');
        const modalPreviewSheet = document.getElementById('certificatePreviewSheet');
        const modalPreviewDragName = document.getElementById('modalPreviewDragName');
        const modalPreviewDragNomorSurat = document.getElementById('modalPreviewDragNomorSurat');
        const modalPreviewDragBarcode = document.getElementById('modalPreviewDragBarcode');
        const modalPreviewDragNamaKegiatan = document.getElementById('modalPreviewDragNamaKegiatan');
        const modalPreviewDragTemaKegiatan = document.getElementById('modalPreviewDragTemaKegiatan');
        const placeholderLabelMap = {
            dragName: 'Nama Peserta',
            dragNamaKegiatan: 'Nama Kegiatan',
            dragTemaKegiatan: 'Tema Kegiatan',
            dragNomorSurat: 'Nomor Surat',
            dragBarcode: 'No. Sertifikat'
        };
        const modalPreviewMapping = {
            dragName: modalPreviewDragName,
            dragNamaKegiatan: modalPreviewDragNamaKegiatan,
            dragTemaKegiatan: modalPreviewDragTemaKegiatan,
            dragNomorSurat: modalPreviewDragNomorSurat,
            dragBarcode: modalPreviewDragBarcode,
        };
        const removedPlaceholders = {};
        let ckEditorInstance = null;
        let currentBackgroundUrl = '';

        const preset = document.getElementById('templatePreset');
        const presets = {
            default: `
<div style="font-family: Arial; text-align:center; border:8px solid #0d6efd; padding:24px; max-width:900px; margin:auto;">
    <h1 style="color:#0d6efd;">SERTIFIKAT</h1>
    <p>Diberikan kepada</p>
    <h2>@{{name}}</h2>
    <p>Atas partisipasinya pada kegiatan:</p>
    <h3>@{{kegiatan->nama_kegiatan}}</h3>
    <p>Tema: @{{kegiatan->tema_kegiatan}}</p>
    <p>Barcode: @{{barcode}}</p>
</div>`,
            word: `
<!DOCTYPE html>
<html>
<head><meta charset="utf-8"></head>
<body>
<h1>SERTIFIKAT</h1>
<p>Nama Peserta: @{{name}}</p>
<p>Kegiatan: @{{kegiatan->nama_kegiatan}}</p>
<p>Tema: @{{kegiatan->tema_kegiatan}}</p>
<p>Verifikasi: @{{barcode}}</p>
</body>
</html>`,
            jpeg: `
<div style="font-family: Arial; text-align:center; background:#f7f7f7; padding:30px; border:2px solid #333;">
    <h1>@{{kegiatan->nama_kegiatan}}</h1>
    <p>@{{name}}</p>
    <p>Tema: @{{kegiatan->tema_kegiatan}}</p>
    <p>Barcode: @{{barcode}}</p>
</div>`
        };

        function setEditorBackground(url) {
            if (!editor) return;
            if (url) {
                editor.style.background = `#ffffff url('${url}') no-repeat center / contain`;
            } else {
                editor.style.background = '#f8f9fa';
            }
        }

        function setPreviewBackground(url) {
            if (!preview) return;
            if (url) {
                preview.style.background = `#ffffff url('${url}') no-repeat center / contain`;
            } else {
                preview.style.background = 'transparent';
            }
        }

        function getInitialPlaceholderPositions() {
            const value = placeholderPositionsInput?.value || '';
            if (!value) return null;
            try {
                return JSON.parse(value);
            } catch (e) {
                return null;
            }
        }

        function setPlaceholderPositions(data) {
            if (!data) return;
            draggableItems.forEach(el => {
                const state = data[el.id];
                if (!state) return;
                const visible = state.visible !== false;
                el.style.left = state.left || el.style.left;
                el.style.top = state.top || el.style.top;
                el.style.transform = state.transform || el.style.transform || 'translate(-50%,-50%)';
                el.style.display = visible ? 'block' : 'none';
                if (visible) {
                    delete removedPlaceholders[el.id];
                } else {
                    removedPlaceholders[el.id] = el;
                }
            });
            renderRemovedPlaceholderButtons();
        }

        function getDefaultPlaceholderPositions() {
            return {
                dragName: { left: '50%', top: '30%', transform: 'translate(-50%,-50%)' },
                dragNamaKegiatan: { left: '50%', top: '40%', transform: 'translate(-50%,-50%)' },
                dragTemaKegiatan: { left: '50%', top: '50%', transform: 'translate(-50%,-50%)' },
                dragNomorSurat: { left: '50%', top: '60%', transform: 'translate(-50%,-50%)' },
                dragBarcode: { left: '50%', top: '80%', transform: 'translate(-50%,-50%)' },
            };
        }

        function getSelectedPlaceholderIds() {
            const selected = placeholderTarget?.value || 'all';
            if (selected === 'all') {
                return ['all'];
            }
            return [selected];
        }

        function updatePlaceholderStyles() {
            const family = fontFamilySelect?.value || 'Arial';
            const size = fontSizeSelect?.value || '16';
            const color = placeholderFontColor?.value || '#000000';
            const selectedIds = getSelectedPlaceholderIds();
            draggableItems.forEach(el => {
                if (selectedIds.includes('all') || selectedIds.includes(el.id)) {
                    el.style.fontFamily = family;
                    el.style.fontSize = `${size}px`;
                    el.style.color = color;
                }
            });
            Object.entries(modalPreviewMapping).forEach(([id, modalEl]) => {
                if (!modalEl) return;
                if (selectedIds.includes('all') || selectedIds.includes(id)) {
                    modalEl.style.fontFamily = family;
                    modalEl.style.fontSize = `${size}px`;
                    modalEl.style.color = color;
                }
            });
        }

        function syncModalPreview() {
            if (!modalPreviewSheet) return;
            modalPreviewSheet.style.background = currentBackgroundUrl ? `#ffffff url('${currentBackgroundUrl}') no-repeat center / contain` : '#ffffff';
            const modalElements = [modalPreviewDragName, modalPreviewDragNamaKegiatan, modalPreviewDragTemaKegiatan, modalPreviewDragNomorSurat, modalPreviewDragBarcode];
            modalElements.forEach(el => {
                if (!el) return;
                el.style.display = 'none';
            });
            draggableItems.forEach(el => {
                if (el.style.display === 'none') return;
                const modalEl = modalPreviewMapping[el.id];
                if (!modalEl) return;
                modalEl.style.display = 'block';
                modalEl.style.left = el.style.left;
                modalEl.style.top = el.style.top;
                modalEl.style.transform = el.style.transform;
                modalEl.style.fontFamily = el.style.fontFamily || 'Arial';
                modalEl.style.fontSize = el.style.fontSize || '16px';
                modalEl.style.color = el.style.color || '#000000';
            });
        }

        function savePlaceholderPositions() {
            if (!placeholderPositionsInput) return;
            const positions = draggableItems.reduce((acc, el) => {
                acc[el.id] = {
                    left: el.style.left,
                    top: el.style.top,
                    transform: el.style.transform,
                    visible: el.style.display !== 'none',
                };
                return acc;
            }, {});
            placeholderPositionsInput.value = JSON.stringify(positions);
        }

        function initDraggableElement(el) {
            let active = false;
            let startX = 0;
            let startY = 0;
            let startLeft = 0;
            let startTop = 0;

            const editorRect = () => editor.getBoundingClientRect();

            const onMove = (event) => {
                if (!active) return;
                event.preventDefault();
                const pointer = event.touches ? event.touches[0] : event;
                const dx = pointer.clientX - startX;
                const dy = pointer.clientY - startY;
                const newLeft = startLeft + dx;
                const newTop = startTop + dy;
                const rect = editorRect();
                const percentLeft = Math.min(Math.max(((newLeft - rect.left) / rect.width) * 100, 0), 100);
                const percentTop = Math.min(Math.max(((newTop - rect.top) / rect.height) * 100, 0), 100);
                el.style.left = `${percentLeft}%`;
                el.style.top = `${percentTop}%`;
                el.style.transform = 'translate(-50%,-50%)';
            };

            const onEnd = () => {
                if (!active) return;
                active = false;
                savePlaceholderPositions();
                document.removeEventListener('mousemove', onMove);
                document.removeEventListener('touchmove', onMove);
                document.removeEventListener('mouseup', onEnd);
                document.removeEventListener('touchend', onEnd);
            };

            const onStart = (event) => {
                event.preventDefault();
                const pointer = event.touches ? event.touches[0] : event;
                const rect = el.getBoundingClientRect();
                startX = pointer.clientX;
                startY = pointer.clientY;
                startLeft = rect.left + rect.width / 2;
                startTop = rect.top + rect.height / 2;
                active = true;
                document.addEventListener('mousemove', onMove);
                document.addEventListener('touchmove', onMove, { passive: false });
                document.addEventListener('mouseup', onEnd);
                document.addEventListener('touchend', onEnd);
            };

            el.addEventListener('mousedown', onStart);
            el.addEventListener('touchstart', onStart, { passive: false });
        }

        function mapPageSettings() {
            const pageSize = document.querySelector('[name="page_size"]')?.value || 'A4';
            const pageOrientation = document.querySelector('[name="page_orientation"]')?.value || 'portrait';
            return { pageSize, pageOrientation };
        }

        function initRemovePlaceholderButtons() {
            const removeButtons = Array.from(document.querySelectorAll('.remove-placeholder'));
            removeButtons.forEach(button => {
                button.addEventListener('mousedown', event => event.stopPropagation());
                button.addEventListener('touchstart', event => event.stopPropagation());
                button.addEventListener('click', event => {
                    event.preventDefault();
                    event.stopPropagation();
                    const container = button.closest('.draggable-text');
                    if (!container) return;
                    container.style.display = 'none';
                    removedPlaceholders[container.id] = container;
                    renderRemovedPlaceholderButtons();
                    savePlaceholderPositions();
                    syncModalPreview();
                });
            });
        }

        function renderRemovedPlaceholderButtons() {
            if (!removedPlaceholderButtons) return;
            removedPlaceholderButtons.innerHTML = '';
            const removedIds = Object.keys(removedPlaceholders);
            if (removedIds.length === 0) {
                removedPlaceholderButtons.innerHTML = '<div class="alert alert-secondary py-2 mb-0">Tidak ada placeholder yang dihapus.</div>';
                return;
            }
            removedIds.forEach(id => {
                const button = document.createElement('button');
                button.type = 'button';
                button.className = 'btn btn-sm btn-outline-primary me-2 mb-2';
                button.textContent = `Pulihkan ${placeholderLabelMap[id] || id}`;
                button.addEventListener('click', function () {
                    restorePlaceholder(id);
                });
                removedPlaceholderButtons.appendChild(button);
            });
        }

        function restorePlaceholder(id) {
            const placeholder = removedPlaceholders[id];
            if (!placeholder) return;
            placeholder.style.display = 'block';
            delete removedPlaceholders[id];
            renderRemovedPlaceholderButtons();
            savePlaceholderPositions();
            syncModalPreview();
        }

        function updateEditorAspectRatio() {
            if (!editor) return;
            const { pageSize, pageOrientation } = mapPageSettings();
            const ratios = {
                A4: { portrait: '210 / 297', landscape: '297 / 210' },
                Letter: { portrait: '216 / 279', landscape: '279 / 216' },
            };
            const ratio = ratios[pageSize]?.[pageOrientation] ?? ratios.A4.portrait;
            editor.style.aspectRatio = ratio;
        }

        function updatePreviewOrientation() {
            if (!modalPreviewSheet) return;
            const pageSize = document.querySelector('[name="page_size"]')?.value || 'A4';
            const pageOrientation = document.querySelector('[name="page_orientation"]')?.value || 'portrait';
            const ratios = {
                A4: { portrait: '210 / 297', landscape: '297 / 210' },
                Letter: { portrait: '216 / 279', landscape: '279 / 216' },
            };
            const ratio = ratios[pageSize]?.[pageOrientation] ?? ratios.A4.portrait;
            modalPreviewSheet.style.aspectRatio = ratio;
            modalPreviewSheet.style.maxWidth = pageOrientation === 'landscape' ? '1400px' : '1100px';
        }

        function renderTemplatePreview() {
            if (!textarea || !preview) return;
            let htmlContent = textarea.value || '<p>Template preview akan muncul di sini.</p>';
            htmlContent = htmlContent
                .replace(/@?\{\{name\}\}/g, 'Nama Peserta')
                .replace(/@?\{\{nomor_surat\}\}/g, '123/SMAN1/PONTANG')
                .replace(/@?\{\{kegiatan->nama_kegiatan\}\}/g, 'Nama Kegiatan Contoh')
                .replace(/@?\{\{kegiatan->tema_kegiatan\}\}/g, 'Tema Kegiatan Contoh')
                .replace(/@?\{\{barcode\}\}/g, 'ABC123-VERIFY');
            preview.innerHTML = htmlContent;
        }

        function loadEditorBackground() {
            const url = editor?.dataset.backgroundUrl || '';
            currentBackgroundUrl = url;
            if (url) {
                setEditorBackground(url);
                setPreviewBackground(url);
                backgroundPreview.src = url;
                backgroundPreviewContainer.style.display = 'block';
            } else {
                setPreviewBackground('');
                backgroundPreview.src = '';
                backgroundPreviewContainer.style.display = 'none';
            }
        }

        imgInput?.addEventListener('change', function () {
            if (!this.files || !this.files[0]) return;
            const reader = new FileReader();
            reader.onload = function (event) {
                const imageUrl = event.target.result;
                currentBackgroundUrl = imageUrl;
                setEditorBackground(imageUrl);
                setPreviewBackground(imageUrl);
                backgroundPreview.src = imageUrl;
                backgroundPreviewContainer.style.display = 'block';
            };
            reader.readAsDataURL(this.files[0]);
        });

        function initCkeditor() {
            if (!textarea) return Promise.resolve();
            return ClassicEditor.create(textarea, {
                toolbar: ['heading', '|', 'bold', 'italic', 'link', 'bulletedList', 'numberedList', 'blockQuote', 'insertTable', 'undo', 'redo']
            }).then(editorInstance => {
                ckEditorInstance = editorInstance;
                ckEditorInstance.model.document.on('change:data', renderTemplatePreview);
                return ckEditorInstance;
            }).catch(error => {
                console.error('CKEditor init error', error);
            });
        }

        function updateEditorModeUI() {
            const mode = editorModeSelect?.value || 'image';
            if (imageEditorContainer) {
                imageEditorContainer.style.display = mode === 'image' ? '' : 'none';
            }
            if (htmlEditorContainer) {
                htmlEditorContainer.style.display = mode === 'html' ? '' : 'none';
            }
        }

        const form = document.querySelector('form');
        form?.addEventListener('submit', function () {
            savePlaceholderPositions();
            if (ckEditorInstance) {
                textarea.value = ckEditorInstance.getData();
            } else if (editorModeSelect?.value === 'image') {
                // Generate HTML from image editor with background and positioned placeholders
                var bgUrl = currentBackgroundUrl || '';
                var positions = {};
                try {
                    positions = JSON.parse(placeholderPositionsInput.value || '{}');
                } catch(e) { positions = getDefaultPlaceholderPositions(); }

                var family = fontFamilySelect?.value || 'Arial';
                var size = fontSizeSelect?.value || '16';
                var color = placeholderFontColor?.value || '#000000';

                var generatedHtml = '<!DOCTYPE html><html><head><meta charset="utf-8"><style>body{margin:0;padding:0;font-family:' + family + ';}</style></head><body>';
                if (bgUrl) {
                    generatedHtml += '<div style="position:relative;width:100%;height:100vh;background:url(\'' + bgUrl + '\') no-repeat center / contain;">';
                } else {
                    generatedHtml += '<div style="position:relative;width:100%;height:100vh;">';
                }

                var labels = {
                    dragName: '{{name}}',
                    dragNamaKegiatan: '{{kegiatan->nama_kegiatan}}',
                    dragTemaKegiatan: '{{kegiatan->tema_kegiatan}}',
                    dragNomorSurat: '{{nomor_surat}}',
                    dragBarcode: '{{barcode}}'
                };

                draggableItems.forEach(function(el) {
                    var pid = el.id;
                    var pos = positions[pid] || { left: '50%', top: '50%', transform: 'translate(-50%,-50%)' };
                    var label = labels[pid] || pid;
                    generatedHtml += '<div style="position:absolute;left:' + pos.left + ';top:' + pos.top + ';transform:' + (pos.transform || 'translate(-50%,-50%)') + ';font-family:' + family + ';font-size:' + size + 'px;color:' + color + ';text-align:center;">' + label + '</div>';
                });

                generatedHtml += '</div></body></html>';
                textarea.value = generatedHtml;
            }
        });

        editorModeSelect?.addEventListener('change', updateEditorModeUI);

        preset?.addEventListener('change', function () {
            const selected = presets[this.value];
            if (!selected) return;
            if (ckEditorInstance) {
                ckEditorInstance.setData(selected.trim());
            } else {
                textarea.value = selected.trim();
            }
            renderTemplatePreview();
        });

        const resetBtn = document.getElementById('resetTemplatePositions');
        resetBtn?.addEventListener('click', function () {
            const defaults = getDefaultPlaceholderPositions();
            setPlaceholderPositions(defaults);
            placeholderPositionsInput.value = JSON.stringify(defaults);
        });

        const pageSizeSelect = document.querySelector('[name="page_size"]');
        const pageOrientationSelect = document.querySelector('[name="page_orientation"]');
        pageSizeSelect?.addEventListener('change', function () {
            updateEditorAspectRatio();
            updatePreviewOrientation();
        });
        pageOrientationSelect?.addEventListener('change', function () {
            updateEditorAspectRatio();
            updatePreviewOrientation();
        });

        fontFamilySelect?.addEventListener('change', function () {
            updatePlaceholderStyles();
            savePlaceholderPositions();
            syncModalPreview();
        });

        fontSizeSelect?.addEventListener('change', function () {
            updatePlaceholderStyles();
            savePlaceholderPositions();
            syncModalPreview();
        });

        placeholderFontColor?.addEventListener('change', function () {
            updatePlaceholderStyles();
            savePlaceholderPositions();
            syncModalPreview();
        });

        placeholderTarget?.addEventListener('change', function () {
            updatePlaceholderStyles();
        });

        previewModalBtn?.addEventListener('click', syncModalPreview);
        const previewModal = document.getElementById('certificatePreviewModal');
        previewModal?.addEventListener('show.bs.modal', function () {
            updatePreviewOrientation();
            syncModalPreview();
            if (window.history && window.history.pushState) {
                window.history.pushState({ certificatePreview: true }, '', '');
            }
        });
        previewModal?.addEventListener('hidden.bs.modal', function () {
            if (window.history && window.history.state && window.history.state.certificatePreview) {
                window.history.back();
            }
        });
        window.addEventListener('popstate', function () {
            if (previewModal && previewModal.classList.contains('show')) {
                bootstrap.Modal.getInstance(previewModal)?.hide();
            }
        });

        btn?.addEventListener('click', renderTemplatePreview);
        textarea?.addEventListener('input', renderTemplatePreview);
        downloadPreviewBtn?.addEventListener('click', function () {
            if (!modalPreviewSheet || typeof html2canvas === 'undefined') return;
            html2canvas(modalPreviewSheet, { backgroundColor: null, useCORS: true }).then(canvas => {
                const link = document.createElement('a');
                link.href = canvas.toDataURL('image/png');
                link.download = 'preview-sertifikat.png';
                link.click();
            }).catch(error => console.error('Download preview error', error));
        });

        draggableItems.forEach(initDraggableElement);
        initRemovePlaceholderButtons();
        renderRemovedPlaceholderButtons();
        const storedPositions = getInitialPlaceholderPositions();
        setPlaceholderPositions(storedPositions || getDefaultPlaceholderPositions());
        if (!storedPositions) {
            placeholderPositionsInput.value = JSON.stringify(getDefaultPlaceholderPositions());
        }

        updatePlaceholderStyles();
        syncModalPreview();
        loadEditorBackground();
        updateEditorAspectRatio();
        updateEditorModeUI();
        initCkeditor().then(renderTemplatePreview);
    });
</script>
@endpush
