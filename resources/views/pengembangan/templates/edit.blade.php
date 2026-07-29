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
                                            <input id="propFontSize" type="number" class="form-control form-control-sm" min="8" max="200">
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
<script src="https://cdnjs.cloudflare.com/ajax/libs/fabric.js/5.3.1/fabric.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    var canvas = new fabric.Canvas('certCanvas', {
        width: 900, height: 600, selection: false, backgroundColor: '#ffffff'
    });

    var bgInput = document.getElementById('bgInput');
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
    var textObjects = {};
    var updating = false;

    var defaults = {
        name: { label: 'Nama Peserta', x: 450, y: 200, size: 80, color: '#000000' },
        'kegiatan->nama_kegiatan': { label: 'Nama Kegiatan', x: 450, y: 290, size: 48, color: '#000000' },
        'kegiatan->tema_kegiatan': { label: 'Tema Kegiatan', x: 450, y: 350, size: 36, color: '#000000' },
        barcode: { label: 'Kode Verifikasi', x: 450, y: 480, size: 24, color: '#000000' },
        nomor_surat: { label: 'Nomor Sertifikat', x: 450, y: 430, size: 28, color: '#000000' },
    };

    @php
        $savedPositions = [];
        if (!empty($item->placeholder_positions)) {
            $savedPositions = json_decode($item->placeholder_positions, true) ?? [];
        }
    @endphp

    var savedPos = @json($savedPositions);

    function addText(key, opts) {
        var d = defaults[key];
        if (!d) return;
        var t = new fabric.Text(opts.text || d.label, {
            name: key, left: opts.x || d.x, top: opts.y || d.y,
            fontSize: opts.fontSize || d.size, fill: opts.color || d.color,
            originX: 'center', originY: 'center', fontFamily: opts.fontFamily || 'Arial',
            fontWeight: 'bold', padding: 10, cornerSize: 8,
            transparentCorners: false, cornerColor: '#0d6efd',
            borderColor: '#0d6efd', hasRotatingPoint: false, lockRotation: true,
            hoverCursor: 'move',
        });
        textObjects[key] = t;
        canvas.add(t);
    }

    function init() {
        Object.keys(defaults).forEach(function(k) {
            var saved = savedPos[k] || {};
            addText(k, {
                x: saved.x || defaults[k].x,
                y: saved.y || defaults[k].y,
                fontSize: saved.font_size || defaults[k].size,
                color: saved.color || defaults[k].color,
            });
        });
        canvas.renderAll();
    }

    function toJSON() {
        var p = {};
        Object.keys(textObjects).forEach(function(k) {
            var o = textObjects[k];
            if (!o || !o.canvas) return;
            p[k] = { x: Math.round(o.left), y: Math.round(o.top), font_size: o.fontSize, color: o.fill, align: 'center' };
        });
        return JSON.stringify(p);
    }

    function showProps(obj) {
        if (!obj || !obj.name) { propsPanel.style.display = 'none'; noSelection.style.display = ''; return; }
        updating = true;
        propsPanel.style.display = ''; noSelection.style.display = 'none';
        propField.value = obj.name; propFontSize.value = obj.fontSize;
        propColor.value = obj.fill; propX.value = Math.round(obj.left);
        propY.value = Math.round(obj.top);
        if (propFontFamily) propFontFamily.value = obj.fontFamily || 'Arial';
        updating = false;
    }

    canvas.on('selection:created', function(e) { if (e.selected[0] && e.selected[0].name) showProps(e.selected[0]); });
    canvas.on('selection:cleared', function() { showProps(null); });
    canvas.on('object:modified', function(e) { if (e.target && e.target.name) showProps(e.target); });
    canvas.on('mouse:down', function(e) {
        if (e.target && e.target.name) { showProps(e.target); }
    });
    canvas.on('object:moving', function(e) {
        var o = e.target;
        if (o && o.name && !updating) { propX.value = Math.round(o.left); propY.value = Math.round(o.top); }
    });

    propFontSize.addEventListener('change', function() {
        var o = canvas.getActiveObject();
        if (o && o.name) { o.set({ fontSize: parseInt(this.value) || 24 }); canvas.renderAll(); }
    });
    propColor.addEventListener('input', function() {
        var o = canvas.getActiveObject();
        if (o && o.name) { o.set({ fill: this.value }); canvas.renderAll(); }
    });
    if (propFontFamily) {
        propFontFamily.addEventListener('change', function() {
            var o = canvas.getActiveObject();
            if (o && o.name) { o.set({ fontFamily: this.value }); canvas.renderAll(); }
        });
    }
    propX.addEventListener('change', function() {
        if (updating) return; var o = canvas.getActiveObject();
        if (o && o.name) { o.set({ left: parseInt(this.value) || 0 }); canvas.renderAll(); }
    });
    propY.addEventListener('change', function() {
        if (updating) return; var o = canvas.getActiveObject();
        if (o && o.name) { o.set({ top: parseInt(this.value) || 0 }); canvas.renderAll(); }
    });

    removeBtn.addEventListener('click', function() { removeSelected(); });

    function removeSelected() {
        var o = canvas.getActiveObject();
        if (o && o.name) { var k = getKeyByObject(o); canvas.remove(o); if (k) delete textObjects[k]; showProps(null); canvas.renderAll(); }
    }
    function getKeyByObject(obj) {
        for (var k in textObjects) { if (textObjects[k] === obj) return k; }
        return null;
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

    // Preview button
    document.getElementById('previewBtn').addEventListener('click', function() {
        hiddenPos.value = toJSON();
        var dataUrl = canvas.toDataURL('image/jpeg', 0.9);
        var w = window.open('', '_blank');
        w.document.write('<html><body style="margin:0;display:flex;justify-content:center;align-items:center;min-height:100vh;"><img src="' + dataUrl + '" style="max-width:100%;max-height:100vh;"/></body></html>');
        w.document.close();
    });
});
</script>
@endpush