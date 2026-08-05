@extends('layouts.app')

@section('title', 'Edit Template Sertifikat')

@section('content')
<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h4 class="card-title fw-semibold m-0">Edit Template: {{ $item->nama }}</h4>
                <a href="{{ route('pengembangan.templates.index') }}" class="btn btn-secondary btn-sm">Kembali</a>
            </div>
            <div class="card-body">
                @if ($errors->any())
                    <div class="alert alert-danger">
                        <strong>Perbaiki kesalahan:</strong>
                        <ul class="mb-0">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
                    </div>
                @endif

                <form method="POST" action="{{ route('pengembangan.templates.update', $item->id) }}" enctype="multipart/form-data" id="templateForm">
                    @csrf
                    @method('PUT')

                    <div class="row g-3 mb-4">
                        <div class="col-md-4">
                            <label class="form-label">Nama Template <span class="text-danger">*</span></label>
                            <input name="nama" class="form-control" value="{{ old('nama', $item->nama) }}" required>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Format Output</label>
                            <select name="output_format" class="form-select">
                                <option value="pdf" {{ ($item->output_format ?? 'pdf') == 'pdf' ? 'selected' : '' }}>PDF</option>
                                <option value="jpeg" {{ ($item->output_format ?? 'pdf') == 'jpeg' ? 'selected' : '' }}>JPEG</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Orientasi</label>
                            <select name="page_orientation" class="form-select">
                                <option value="landscape" {{ ($item->page_orientation ?? 'landscape') == 'landscape' ? 'selected' : '' }}>Landscape</option>
                                <option value="portrait" {{ ($item->page_orientation ?? 'landscape') == 'portrait' ? 'selected' : '' }}>Portrait</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Font Kustom (TTF/OTF)</label>
                            <input type="file" name="font_file" class="form-control" accept=".ttf,.otf">
                            @if($item->font_file)
                            <small class="text-muted">Font: {{ $item->font_file }}</small>
                            @endif
                        </div>
                    </div>

                    <div class="row g-3 mb-4">
                        <div class="col-md-6">
                            <label class="form-label">Background Template</label>
                            <input type="file" name="background_image" id="bgInput" class="form-control" accept="image/jpeg,image/png">
                            <small class="text-muted">Biarkan kosong untuk mempertahankan background yang ada.</small>
                            @if($item->background_image)
                            <div class="mt-2"><img id="existingBg" src="{{ asset('storage/'.$item->background_image) }}" style="max-height:100px;" class="border rounded"></div>
                            @endif
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Opsi Verifikasi</label>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="include_verification_code" value="1" id="includeVerificationCode" {{ ($item->include_verification_code ?? (isset($savedPositions['barcode']) ? true : false)) ? 'checked' : '' }}>
                                <label class="form-check-label small" for="includeVerificationCode">Tampilkan kode verifikasi pada template</label>
                            </div>
                            <div class="form-check mt-1">
                                <input class="form-check-input" type="checkbox" name="include_verification_qr" value="1" id="includeVerificationQr" {{ ($item->include_verification_qr ?? (isset($savedPositions['barcode']) ? true : false)) ? 'checked' : '' }}>
                                <label class="form-check-label small" for="includeVerificationQr">Gunakan QR untuk kode verifikasi</label>
                            </div>
                            <small class="text-muted d-block">Centang untuk menyertakan kode verifikasi dan/atau QR verifikasi terpisah.</small>
                            <button type="button" id="addVerificationTextPlaceholderBtn" class="btn btn-outline-primary btn-sm mt-2">Tempatkan placeholder teks verifikasi</button>
                            <button type="button" id="addVerificationQrPlaceholderBtn" class="btn btn-outline-secondary btn-sm mt-2 ms-2">Tempatkan placeholder QR verifikasi</button>
                        </div>
                    </div>

                    <input type="hidden" id="placeholderPositions" name="placeholder_positions" value="">
                    <input type="hidden" name="editor_mode" value="image">

                    <hr>
                    <h5 class="mb-3">🖱️ Editor Sertifikat</h5>
                    <p class="text-muted small">Drag teks placeholder untuk mengatur posisi. Klik teks untuk edit properti.</p>

                    <div class="row g-3">
                        <div class="col-md-9">
                            <div class="border rounded bg-white p-1" style="background:#f8f9fa;">
                                <canvas id="certCanvas" width="900" height="600" style="width:100%;"></canvas>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card">
                                <div class="card-header py-2"><strong>Properti</strong></div>
                                <div class="card-body py-2">
                                    <div id="noSelection" class="text-muted small py-3 text-center">Klik teks di canvas</div>
                                    <div id="propsPanel" style="display:none;">
                                        <div class="mb-1">
                                            <label class="form-label small mb-0">Field</label>
                                            <input id="propField" class="form-control form-control-sm" readonly>
                                        </div>
                                        <div class="mb-1">
                                            <label class="form-label small mb-0">Ukuran Font</label>
                                            <input id="propFontSize" type="number" class="form-control form-control-sm" min="1" max="200">
                                        </div>
                                        <div class="mb-1">
                                            <label class="form-label small mb-0">Font</label>
                                            <select id="propFontFamily" class="form-select form-select-sm">
                                                <option value="Arial">Arial</option>
                                                <option value="Times New Roman">Times New Roman</option>
                                                <option value="Georgia">Georgia</option>
                                                <option value="Verdana">Verdana</option>
                                                <option value="Tahoma">Tahoma</option>
                                                <option value="Courier New">Courier New</option>
                                                <option value="Impact">Impact</option>
                                            </select>
                                        </div>
                                        <div class="mb-1">
                                            <label class="form-label small mb-0">Warna</label>
                                            <input id="propColor" type="color" class="form-control form-control-color">
                                        </div>
                                        <div id="verificationControls" style="display:none;">
                                            <div class="form-check mb-1">
                                                <input class="form-check-input" type="checkbox" id="propIsQr">
                                                <label class="form-check-label small" for="propIsQr">Render sebagai QR</label>
                                            </div>
                                            <div class="mb-1">
                                                <label class="form-label small mb-0">Ukuran QR (px)</label>
                                                <input id="propQrSize" type="number" class="form-control form-control-sm" min="40" max="800">
                                            </div>
                                        </div>
                                        <div class="row g-1 mb-1">
                                            <div class="col-6">
                                                <label class="form-label small mb-0">X</label>
                                                <input id="propX" type="number" class="form-control form-control-sm" min="0">
                                            </div>
                                            <div class="col-6">
                                                <label class="form-label small mb-0">Y</label>
                                                <input id="propY" type="number" class="form-control form-control-sm" min="0">
                                            </div>
                                        </div>
                                        <button type="button" id="removePlhBtn" class="btn btn-danger btn-sm w-100 mt-1"><i class="ti ti-trash me-1"></i>Hapus</button>
                                    </div>
                                    <hr class="my-2">
                                    <button type="button" id="resetPlhBtn" class="btn btn-outline-secondary btn-sm w-100"><i class="ti ti-refresh me-1"></i>Reset</button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="d-flex justify-content-end gap-2 mt-3">
                        <button type="button" id="previewBtn" class="btn btn-info"><i class="ti ti-eye me-1"></i>Preview</button>
                        <a href="{{ route('pengembangan.templates.index') }}" class="btn btn-secondary">Batal</a>
                        <button type="submit" class="btn btn-primary"><i class="ti ti-device-floppy me-1"></i>Simpan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('js')
@include('pengembangan.templates.partials.template_preview_renderer')
<script src="https://cdnjs.cloudflare.com/ajax/libs/fabric.js/5.3.1/fabric.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    var canvas = new fabric.Canvas('certCanvas', {
        width: 900, height: 600, selection: false, backgroundColor: '#ffffff'
    });

    var bgInput = document.getElementById('bgInput');
    var includeVerificationCodeCheckbox = document.getElementById('includeVerificationCode');
    var includeVerificationQrCheckbox = document.getElementById('includeVerificationQr');
    var addVerificationTextPlaceholderBtn = document.getElementById('addVerificationTextPlaceholderBtn');
    var addVerificationQrPlaceholderBtn = document.getElementById('addVerificationQrPlaceholderBtn');
    var form = document.getElementById('templateForm');
    var hiddenPos = document.getElementById('placeholderPositions');
    var propField = document.getElementById('propField');
    var propFontSize = document.getElementById('propFontSize');
    var propColor = document.getElementById('propColor');
    var propX = document.getElementById('propX');
    var propY = document.getElementById('propY');
    var propsPanel = document.getElementById('propsPanel');
    var noSelection = document.getElementById('noSelection');
    var removeBtn = document.getElementById('removePlhBtn');
    var resetBtn = document.getElementById('resetPlhBtn');

    var bgImage = null;
    var backgroundImageUrl = null;
    var textObjects = {};
    var updating = false;

    var defaults = {
        name: { label: 'Nama Peserta', x: 450, y: 200, size: 36, color: '#000000' },
        'kegiatan->nama_kegiatan': { label: 'Nama Kegiatan', x: 450, y: 290, size: 26, color: '#000000' },
        'kegiatan->tema_kegiatan': { label: 'Tema Kegiatan', x: 450, y: 350, size: 20, color: '#000000' },
        sebagai: { label: 'Sebagai', x: 450, y: 320, size: 22, color: '#000000' },
        verification_text: { label: 'Kode Verifikasi', x: 450, y: 480, size: 16, color: '#000000' },
        verification_qr: { label: 'QR Verifikasi', x: 750, y: 480, size: 16, color: '#000000', qr_size: 120 },
        nomor_surat: { label: 'Nomor Surat', x: 450, y: 430, size: 18, color: '#000000' },
    };

    function getCanvasScaleFactor() {
        if (!bgImage || !bgImage.width || !bgImage.height) return 1;
        var imgW = bgImage.width || 1;
        var imgH = bgImage.height || 1;
        return Math.min((canvas.width || 900) / imgW, (canvas.height || 600) / imgH);
    }

    @php
        $savedPositions = [];
        if (!empty($item->placeholder_positions)) {
            $savedPositions = json_decode($item->placeholder_positions, true) ?? [];
        }
    @endphp

    var savedPos = @json($savedPositions);

    if (savedPos['barcode'] && !savedPos['verification_text'] && !savedPos['verification_qr']) {
        if (includeVerificationCodeCheckbox && includeVerificationCodeCheckbox.checked) {
            savedPos['verification_text'] = savedPos['barcode'];
        }
        if (includeVerificationQrCheckbox && includeVerificationQrCheckbox.checked) {
            savedPos['verification_qr'] = savedPos['barcode'];
        }
    }

    function addText(key, opts) {
        var d = defaults[key];
        if (!d) return;
        var scale = getCanvasScaleFactor();
        var previewFontSize = (opts.fontSize || d.size) * scale;
        var isQr = key === 'verification_qr';
        var textLabel = isQr ? 'QR' : (opts.text || d.label);
        var t = new fabric.Text(textLabel, {
            key: key, left: opts.x || d.x, top: opts.y || d.y,
            fontSize: previewFontSize, fill: opts.color || d.color,
            originX: 'center', originY: 'center', fontFamily: 'Arial, sans-serif',
            fontWeight: 'bold', padding: 10, cornerSize: 8,
            transparentCorners: false, cornerColor: '#0d6efd',
            borderColor: '#0d6efd', hasRotatingPoint: false, lockRotation: true,
            stroke: isQr ? '#000000' : undefined,
            strokeWidth: isQr ? 0.8 : 0,
        });
        if (key === 'verification_qr') {
            t.qr_size = (opts.qr_size !== undefined) ? opts.qr_size : d.qr_size;
            t.text = 'QR';
        }
        textObjects[key] = t;
        canvas.add(t);
    }

    function init() {
        var keys = Object.keys(savedPos);
        if (keys.length === 0) {
            keys = Object.keys(defaults);
        }
        keys.forEach(function(k) {
            if (k === 'verification_text' && includeVerificationCodeCheckbox && !includeVerificationCodeCheckbox.checked) {
                return;
            }
            if (k === 'verification_qr' && includeVerificationQrCheckbox && !includeVerificationQrCheckbox.checked) {
                return;
            }
            var saved = savedPos[k] || {};
            var canvasW = canvas.width || 900;
            var canvasH = canvas.height || 600;
            var previewX = saved.x_ratio !== undefined ? (saved.x_ratio * canvasW) : ((defaults[k] || {}).x || 450);
            var previewY = saved.y_ratio !== undefined ? (saved.y_ratio * canvasH) : ((defaults[k] || {}).y || 300);
            var previewFontSize = saved.font_size !== undefined ? saved.font_size : ((defaults[k] || {}).size || 24);
            addText(k, {
                x: previewX,
                y: previewY,
                fontSize: previewFontSize,
                color: saved.color || ((defaults[k] || {}).color || '#000000'),
                is_qr: saved.is_qr,
                qr_size: saved.qr_size,
            });
        });
        canvas.renderAll();
    }

    function toJSON() {
        var p = {};
        var canvasW = canvas.width || 900;
        var canvasH = canvas.height || 600;
        var scale = getCanvasScaleFactor();
        Object.keys(textObjects).forEach(function(k) {
            var o = textObjects[k];
            if (!o || !o.canvas) return;
            p[k] = {
                x_ratio: Math.round((o.left / canvasW) * 1000) / 1000,
                y_ratio: Math.round((o.top / canvasH) * 1000) / 1000,
                font_size: Math.max(1, Math.round((o.fontSize || defaults[k].size) / (scale || 1))),
                color: o.fill,
                align: 'center'
            };
            if (k === 'verification_qr') {
                p[k].qr_size = o.qr_size || Math.max(80, Math.round((o.fontSize || defaults[k].size) * 4));
            }
        });
        return JSON.stringify(p);
    }

    function showProps(obj) {
        if (!obj || !obj.key) { propsPanel.style.display = 'none'; noSelection.style.display = ''; var vc = document.getElementById('verificationControls'); if (vc) vc.style.display = 'none'; return; }
        updating = true;
        propsPanel.style.display = ''; noSelection.style.display = 'none';
        var scale = getCanvasScaleFactor();
        propField.value = obj.key; propFontSize.value = Math.max(1, Math.round((obj.fontSize || 24) / (scale || 1)));
        propColor.value = obj.fill; propX.value = Math.round(obj.left);
        propY.value = Math.round(obj.top);
        var vc = document.getElementById('verificationControls');
        if (obj.key === 'verification_qr') {
            if (vc) vc.style.display = '';
            var propQrSize = document.getElementById('propQrSize');
            if (propQrSize) propQrSize.value = obj.qr_size || Math.max(80, Math.round((obj.fontSize || 24) * 4));
        } else {
            if (vc) vc.style.display = 'none';
        }
        updating = false;
    }

    canvas.on('selection:created', function(e) { if (e.selected[0] && e.selected[0].key) showProps(e.selected[0]); });
    canvas.on('selection:cleared', function() { showProps(null); });
    canvas.on('object:modified', function(e) { if (e.target && e.target.key) showProps(e.target); });
    canvas.on('object:moving', function(e) {
        var o = e.target;
        if (o && o.key && !updating) { propX.value = Math.round(o.left); propY.value = Math.round(o.top); }
    });

    propFontSize.addEventListener('change', function() {
        var o = canvas.getActiveObject();
        if (o && o.key) {
            var scale = getCanvasScaleFactor();
            o.set({ fontSize: (parseInt(this.value) || 24) * scale });
            canvas.renderAll();
        }
    });
    propColor.addEventListener('input', function() {
        var o = canvas.getActiveObject();
        if (o && o.key) { o.set({ fill: this.value }); canvas.renderAll(); }
    });
    // QR controls handlers
    var propQrSize = document.getElementById('propQrSize');
    if (propQrSize) {
        propQrSize.addEventListener('change', function() {
            var o = canvas.getActiveObject(); if (!o || !o.key) return;
            o.qr_size = parseInt(this.value) || o.qr_size || 120;
        });
    }
    propX.addEventListener('change', function() {
        if (updating) return; var o = canvas.getActiveObject();
        if (o && o.key) { o.set({ left: parseInt(this.value) || 0 }); canvas.renderAll(); }
    });
    propY.addEventListener('change', function() {
        if (updating) return; var o = canvas.getActiveObject();
        if (o && o.key) { o.set({ top: parseInt(this.value) || 0 }); canvas.renderAll(); }
    });

    removeBtn.addEventListener('click', function() { removeSelected(); });

    function removeSelected() {
        var o = canvas.getActiveObject();
        if (o && o.key) { canvas.remove(o); delete textObjects[o.key]; showProps(null); canvas.renderAll(); }
    }

    // Hapus via keyboard (Delete / Backspace)
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Delete' || e.key === 'Backspace') {
            var tag = e.target.tagName;
            if (tag === 'INPUT' || tag === 'TEXTAREA' || tag === 'SELECT') return;
            removeSelected();
        }
    });

    resetBtn.addEventListener('click', function() {
        savedPos = {};
        Object.keys(textObjects).forEach(function(k) { var o = textObjects[k]; if (o && o.canvas) canvas.remove(o); });
        textObjects = {}; init();
    });

    // Load background image if exists
    @if($item->background_image)
    (function() {
        var bgUrl = '{{ asset("storage/".$item->background_image) }}';
        backgroundImageUrl = bgUrl;
        fabric.Image.fromURL(bgUrl, function(img) {
            var s = Math.min(900 / img.width, 600 / img.height);
            img.set({ left: 0, top: 0, scaleX: s, scaleY: s, selectable: false, evented: false });
            bgImage = img;
            canvas.insertAt(img, 0, false);
            canvas.renderAll();
            init();
        }, { crossOrigin: 'anonymous' });
    })();
    @else
    init();
    @endif

    bgInput.addEventListener('change', function() {
        var file = this.files[0];
        if (!file) return;
        var reader = new FileReader();
        reader.onload = function(e) {
            fabric.Image.fromURL(e.target.result, function(img) {
                if (bgImage) canvas.remove(bgImage);
                var s = Math.min(900 / img.width, 600 / img.height);
                img.set({ left: 0, top: 0, scaleX: s, scaleY: s, selectable: false, evented: false });
                bgImage = img;
                canvas.insertAt(img, 0, false);
                init();
            }, { crossOrigin: 'anonymous' });
        };
        reader.readAsDataURL(file);
    });

    form.addEventListener('submit', function() { hiddenPos.value = toJSON(); });

    if (includeVerificationCodeCheckbox) {
        includeVerificationCodeCheckbox.addEventListener('change', function() {
            if (this.checked) {
                if (!textObjects['verification_text']) {
                    var canvasW = canvas.width || 900;
                    var canvasH = canvas.height || 600;
                    var saved = savedPos['verification_text'] || {};
                    var previewX = saved.x_ratio !== undefined ? (saved.x_ratio * canvasW) : (saved.x || defaults['verification_text'].x);
                    var previewY = saved.y_ratio !== undefined ? (saved.y_ratio * canvasH) : (saved.y || defaults['verification_text'].y);
                    addText('verification_text', { x: previewX, y: previewY, fontSize: saved.font_size || defaults['verification_text'].size, color: saved.color || defaults['verification_text'].color });
                    canvas.renderAll();
                }
            } else {
                if (textObjects['verification_text']) {
                    canvas.remove(textObjects['verification_text']);
                    delete textObjects['verification_text'];
                }
                canvas.renderAll();
            }
        });
    }

    function addVerificationTextPlaceholder() {
        if (textObjects['verification_text']) return;
        addText('verification_text', {});
        canvas.renderAll();
    }

    function addVerificationQrPlaceholder() {
        if (textObjects['verification_qr']) return;
        addText('verification_qr', {});
        canvas.renderAll();
    }

    if (addVerificationTextPlaceholderBtn) {
        addVerificationTextPlaceholderBtn.addEventListener('click', function() {
            if (!includeVerificationCodeCheckbox || !includeVerificationCodeCheckbox.checked) {
                includeVerificationCodeCheckbox.checked = true;
            }
            addVerificationTextPlaceholder();
        });
    }

    if (addVerificationQrPlaceholderBtn) {
        addVerificationQrPlaceholderBtn.addEventListener('click', function() {
            if (!includeVerificationQrCheckbox || !includeVerificationQrCheckbox.checked) {
                includeVerificationQrCheckbox.checked = true;
            }
            addVerificationQrPlaceholder();
        });
    }

    // Preview button
    document.getElementById('previewBtn').addEventListener('click', function() {
        hiddenPos.value = toJSON();
        var previewWrapper = document.createElement('div');
        previewWrapper.style.width = '100%';
        previewWrapper.style.maxWidth = '900px';
        previewWrapper.style.margin = '0 auto';
        previewWrapper.style.padding = '16px';

        var previewWindow = window.open('', '_blank');
        previewWindow.document.write('<html><head><title>Preview Template</title><style>body{margin:0;background:#f5f5f5;display:flex;justify-content:center;align-items:center;min-height:100vh;font-family:Arial,sans-serif;}#preview-root{width:100%;max-width:900px;border:1px solid #dee2e6;border-radius:8px;background:#fff;}</style></head><body><div id="preview-root"></div></body></html>');
        previewWindow.document.close();

        var root = previewWindow.document.getElementById('preview-root');
        if (root) {
            window.TemplatePreviewRenderer.renderCanvasPreview(root, {
                width: 900,
                height: 600,
                backgroundImage: backgroundImageUrl,
                rawPositions: hiddenPos.value,
                values: {
                    name: 'Nama Peserta',
                    'kegiatan->nama_kegiatan': 'Nama Kegiatan Contoh',
                    'kegiatan->tema_kegiatan': 'Tema Kegiatan Contoh',
                    verification_text: 'ABC123-VERIFY',
                    verification_qr: 'ABC123-VERIFY',
                    nomor_surat: '123/SMAN1/PONTANG'
                }
            });
        }
    });
});
</script>
@endpush