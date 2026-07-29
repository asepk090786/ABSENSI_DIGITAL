@extends('layouts.app')

@section('title', 'Buat Template Sertifikat')

@section('content')
<div class="row">
    <div class="col-md-10 mx-auto">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h4 class="card-title fw-semibold m-0">Buat Template Sertifikat</h4>
                <a href="{{ route('pengembangan.templates.index') }}" class="btn btn-secondary btn-sm">Kembali</a>
            </div>
            <div class="card-body">
                @if ($errors->any())
                    <div class="alert alert-danger">
                        <strong>Perbaiki kesalahan:</strong>
                        <ul class="mb-0">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
                    </div>
                @endif

                <form method="POST" action="{{ route('pengembangan.templates.store') }}" enctype="multipart/form-data" id="templateForm">
                    @csrf

                    <div class="row g-3 mb-4">
                        <div class="col-md-6">
                            <label class="form-label">Nama Template <span class="text-danger">*</span></label>
                            <input name="nama" class="form-control" value="{{ old('nama') }}" required>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Format Output</label>
                            <select name="output_format" class="form-select">
                                <option value="pdf">PDF</option>
                                <option value="jpeg">JPEG</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Orientasi</label>
                            <select name="page_orientation" class="form-select">
                                <option value="landscape">Landscape</option>
                                <option value="portrait">Portrait</option>
                            </select>
                        </div>
                    </div>

                    <div class="mb-4">
                        <label class="form-label">Background Template (JPEG/PNG) <span class="text-danger">*</span></label>
                        <input type="file" name="background_image" class="form-control" accept="image/jpeg,image/png" required>
                        <div class="form-text">Upload desain sertifikat polos (tanpa teks dinamis). Resolusi tinggi dianjurkan.</div>
                        <div id="bgPreview" class="mt-2" style="display:none;">
                            <img id="bgPreviewImg" src="" class="img-fluid border rounded" style="max-height:300px;">
                        </div>
                    </div>

                    <div class="mb-4">
                        <label class="form-label">Font Kustom (TTF/OTF) — opsional</label>
                        <input type="file" name="font_file" class="form-control" accept=".ttf,.otf">
                        <div class="form-text">Upload file font jika ingin font khusus. Kosongkan untuk menggunakan font bawaan.</div>
                    </div>

                    <hr>
                    <h5 class="mb-3">Posisi Placeholder Teks</h5>
                    <p class="text-muted small">Koordinat X (horizontal) dan Y (vertikal) dalam piksel. (0,0) = pojok kiri atas gambar background.</p>

                    <div class="table-responsive">
                        <table class="table table-bordered" id="placeholderTable">
                            <thead class="table-light">
                                <tr>
                                    <th>Field</th>
                                    <th style="width:100px">X (px)</th>
                                    <th style="width:100px">Y (px)</th>
                                    <th style="width:90px">Ukuran Font</th>
                                    <th style="width:80px">Warna</th>
                                    <th style="width:100px">Rata</th>
                                </tr>
                            </thead>
                            <tbody>
                                @php
                                    $fields = [
                                        'name' => ['label' => 'Nama Peserta', 'default' => ['x' => 500, 'y' => 300, 'font_size' => 48, 'color' => '#000000', 'align' => 'center']],
                                        'kegiatan->nama_kegiatan' => ['label' => 'Nama Kegiatan', 'default' => ['x' => 500, 'y' => 400, 'font_size' => 32, 'color' => '#000000', 'align' => 'center']],
                                        'kegiatan->tema_kegiatan' => ['label' => 'Tema Kegiatan', 'default' => ['x' => 500, 'y' => 470, 'font_size' => 24, 'color' => '#000000', 'align' => 'center']],
                                        'barcode' => ['label' => 'Kode Verifikasi', 'default' => ['x' => 500, 'y' => 700, 'font_size' => 16, 'color' => '#000000', 'align' => 'center']],
                                        'nomor_surat' => ['label' => 'Nomor Sertifikat', 'default' => ['x' => 500, 'y' => 550, 'font_size' => 20, 'color' => '#000000', 'align' => 'center']],
                                    ];
                                @endphp
                                @foreach($fields as $key => $field)
                                @php $def = $field['default']; @endphp
                                <tr>
                                    <td><code>{{ $key }}</code><br><small class="text-muted">{{ $field['label'] }}</small></td>
                                    <td><input type="number" name="pos[{{ $key }}][x]" class="form-control form-control-sm pos-x" value="{{ old('pos.'.$key.'.x', $def['x']) }}" min="0"></td>
                                    <td><input type="number" name="pos[{{ $key }}][y]" class="form-control form-control-sm pos-y" value="{{ old('pos.'.$key.'.y', $def['y']) }}" min="0"></td>
                                    <td><input type="number" name="pos[{{ $key }}][font_size]" class="form-control form-control-sm pos-font-size" value="{{ old('pos.'.$key.'.font_size', $def['font_size']) }}" min="8" max="200"></td>
                                    <td><input type="color" name="pos[{{ $key }}][color]" class="form-control form-control-color pos-color" value="{{ old('pos.'.$key.'.color', $def['color']) }}"></td>
                                    <td>
                                        <select name="pos[{{ $key }}][align]" class="form-select form-select-sm pos-align">
                                            <option value="center" {{ old('pos.'.$key.'.align', $def['align']) == 'center' ? 'selected' : '' }}>Tengah</option>
                                            <option value="left" {{ old('pos.'.$key.'.align', $def['align']) == 'left' ? 'selected' : '' }}>Kiri</option>
                                            <option value="right" {{ old('pos.'.$key.'.align', $def['align']) == 'right' ? 'selected' : '' }}>Kanan</option>
                                        </select>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <input type="hidden" id="placeholderPositions" name="placeholder_positions" value="">
                    <input type="hidden" name="editor_mode" value="image">

                    <div class="d-flex justify-content-end gap-2 mt-3">
                        <a href="{{ route('pengembangan.templates.index') }}" class="btn btn-secondary">Batal</a>
                        <button type="submit" class="btn btn-primary"><i class="ti ti-device-floppy me-1"></i>Simpan Template</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('js')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const bgInput = document.querySelector('input[name="background_image"]');
    const bgPreview = document.getElementById('bgPreview');
    const bgPreviewImg = document.getElementById('bgPreviewImg');
    const form = document.getElementById('templateForm');
    const hiddenPos = document.getElementById('placeholderPositions');

    bgInput?.addEventListener('change', function() {
        const file = this.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function(e) { bgPreview.style.display = 'block'; bgPreviewImg.src = e.target.result; };
            reader.readAsDataURL(file);
        }
    });

    form?.addEventListener('submit', function() {
        const positions = {};
        document.querySelectorAll('#placeholderTable tbody tr').forEach(function(row) {
            const key = row.querySelector('code')?.textContent.trim();
            if (!key) return;
            positions[key] = {
                x: parseInt(row.querySelector('.pos-x')?.value || 0),
                y: parseInt(row.querySelector('.pos-y')?.value || 0),
                font_size: parseInt(row.querySelector('.pos-font-size')?.value || 24),
                color: row.querySelector('.pos-color')?.value || '#000000',
                align: row.querySelector('.pos-align')?.value || 'center',
            };
        });
        hiddenPos.value = JSON.stringify(positions);
    });
});
</script>
@endpush
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
