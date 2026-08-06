@extends('layouts.app', ['pageSlug' => 'rencana_pembelajaran'])

@section('title', 'Edit Rencana Pembelajaran')

@section('content')
<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header border-0 pt-3 pb-2">
                <div class="row align-items-center">
                    <div class="col">
                        <h4 class="card-title">Edit Rencana Pembelajaran</h4>
                    </div>
                    <div class="col-auto">
                        <a href="{{ route('rencana_pembelajaran.index', ['mata_pelajaran_id' => $item->mata_pelajaran_id, 'tingkat' => $item->kelas->tingkat_kelas]) }}" class="btn btn-secondary btn-sm">
                            <i class="ti ti-arrow-left me-1"></i>Kembali
                        </a>
                    </div>
                </div>
            </div>
            <div class="card-body">
                <form action="{{ route('rencana_pembelajaran.update', $item->id) }}" method="POST">
                    @csrf
                    @method('PUT')

                    <div class="row mb-4">
                        <div class="col-md-12">
                            <h5 class="mb-2">1. Informasi Umum</h5>
                            <div class="mb-2">
                                <label class="form-label">Mata Pelajaran</label>
                                <input type="text" class="form-control" value="{{ $item->mataPelajaran->nama_mapel }}" disabled>
                            </div>
                            <div class="mb-2 @error('kelas_id') is-invalid @enderror">
                                <label class="form-label">Kelas <span class="text-danger">*</span></label>
                                @forelse($kelas as $k)
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="kelas_id" value="{{ $k->id }}" id="kelas_{{ $k->id }}" {{ old('kelas_id', $item->kelas_id) == $k->id ? 'checked' : '' }}>
                                        <label class="form-check-label" for="kelas_{{ $k->id }}">{{ $k->nama_kelas }}</label>
                                    </div>
                                @empty
                                    <div class="alert alert-info alert-sm mb-0">
                                        <i class="ti ti-info-circle me-2"></i>Belum ada kelas untuk mata pelajaran ini
                                    </div>
                                @endforelse
                                @error('kelas_id')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="mb-2">
                                <label class="form-label">Judul <span class="text-danger">*</span></label>
                                <input type="text" id="input-title" name="judul" class="form-control @error('judul') is-invalid @enderror" value="{{ old('judul', $item->judul) }}" required>
                                @error('judul')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="row mb-4">
                        <div class="col-md-12">
                            <div class="card card-body p-3 border-0 shadow-sm">
                                <h5 class="mb-2">2. Editor RPP (Pratinjau Interaktif)</h5>
                                <div class="form-text text-muted mb-3">Gunakan form di kiri untuk mengedit RPP, pratinjau akan diperbarui otomatis.</div>

                                <div id="rpp-interactive-root">
                                    <div class="app-shell">
                                        <main class="workspace" style="grid-template-columns: minmax(360px, .9fr) minmax(480px, 1.4fr);">
                                            <aside class="form-panel">
                                                <div id="rpp-mini-form">
                                                    <section class="form-group">
                                                        <h3>Informasi &amp; Metadata</h3>
                                                        <div class="field-row">
                                                            <label class="field-label">Judul</label>
                                                            <input id="input-title" name="judul" class="editor-input form-control" value="{{ old('judul', $item->judul) }}">
                                                        </div>
                                                        <div class="field-row">
                                                            <label class="field-label">Mata Pelajaran</label>
                                                            <input id="input-subject" class="editor-input form-control" value="{{ $item->mataPelajaran->nama_mapel }}" disabled>
                                                        </div>
                                                        <div class="field-row">
                                                            <label class="field-label">Alokasi Waktu</label>
                                                            <input id="input-duration" name="alokasi_waktu" class="editor-input form-control" value="{{ old('alokasi_waktu', $item->alokasi_waktu) }}">
                                                        </div>
                                                        <div class="field-row">
                                                            <label class="field-label">Status</label>
                                                            <select id="input-status" name="status" class="editor-input form-control">
                                                                <option value="draft" {{ old('status', $item->status) === 'draft' ? 'selected' : '' }}>Draft</option>
                                                                <option value="published" {{ old('status', $item->status) === 'published' ? 'selected' : '' }}>Published</option>
                                                            </select>
                                                        </div>
                                                    </section>

                                                    <section class="form-group">
                                                        <h3>Isi RPP</h3>
                                                        <div class="field-row">
                                                            <label class="field-label">Capaian Pembelajaran</label>
                                                            <textarea id="input-achievement" name="capaian_pembelajaran" class="editor-textarea form-control rich-editor">{{ old('capaian_pembelajaran', $item->capaian_pembelajaran) }}</textarea>
                                                        </div>
                                                        <div class="field-row">
                                                            <label class="field-label">Tujuan / Objektif</label>
                                                            <textarea id="input-objectives" name="tujuan" class="editor-textarea form-control rich-editor">{{ old('tujuan', $item->tujuan) }}</textarea>
                                                            <p class="helper">Pisahkan baris untuk setiap butir tujuan.</p>
                                                        </div>
                                                        <div class="field-row">
                                                            <label class="field-label">Metode</label>
                                                            <textarea id="input-methods" name="metode" class="editor-textarea form-control rich-editor">{{ old('metode', $item->metode) }}</textarea>
                                                        </div>
                                                        <div class="field-row">
                                                            <label class="field-label">Media</label>
                                                            <textarea id="input-media" name="media" class="editor-textarea form-control rich-editor">{{ old('media', $item->media) }}</textarea>
                                                        </div>
                                                        <div class="field-row">
                                                            <label class="field-label">Sumber / Referensi</label>
                                                            <textarea id="input-resources" name="sumber" class="editor-textarea form-control rich-editor">{{ old('sumber', $item->sumber) }}</textarea>
                                                        </div>
                                                        <div class="field-row">
                                                            <label class="field-label">Praktik Pedagogis</label>
                                                            <textarea id="input-practice" name="praktik_pedagogis" class="editor-textarea form-control rich-editor">{{ old('praktik_pedagogis', $item->praktik_pedagogis) }}</textarea>
                                                        </div>
                                                        <div class="field-row">
                                                            <label class="field-label">Lingkungan Pembelajaran</label>
                                                            <textarea id="input-environment" name="lingkungan_pembelajaran" class="editor-textarea form-control rich-editor">{{ old('lingkungan_pembelajaran', $item->lingkungan_pembelajaran) }}</textarea>
                                                        </div>
                                                        <div class="field-row">
                                                            <label class="field-label">Pemanfaatan Digital</label>
                                                            <textarea id="input-digital" name="pemanfaatan_digital" class="editor-textarea form-control rich-editor">{{ old('pemanfaatan_digital', $item->pemanfaatan_digital) }}</textarea>
                                                        </div>
                                                        <div class="field-row">
                                                            <label class="field-label">Pengalaman Pembelajaran</label>
                                                            <textarea id="input-experience" name="pengalaman_pembelajaran" class="editor-textarea form-control rich-editor">{{ old('pengalaman_pembelajaran', $item->pengalaman_pembelajaran) }}</textarea>
                                                        </div>
                                                        <div class="field-row">
                                                            <label class="field-label">Refleksi Pembelajaran</label>
                                                            <textarea id="input-reflection" name="refleksi_pembelajaran" class="editor-textarea form-control rich-editor">{{ old('refleksi_pembelajaran', $item->refleksi_pembelajaran) }}</textarea>
                                                        </div>
                                                        <div class="field-row">
                                                            <label class="field-label">Asesmen / Penilaian</label>
                                                            <textarea id="input-assessment" name="penilaian" class="editor-textarea form-control rich-editor">{{ old('penilaian', $item->penilaian) }}</textarea>
                                                        </div>
                                                    </section>
                                                </div>
                                            </aside>

                                            <section class="preview-panel">
                                                <div class="format-toolbar">
                                                    <select id="font-family" class="toolbar-select"><option value="Source Serif 4">Serif</option><option value="Libre Franklin">Sans-serif</option><option value="monospace">Monospace</option></select>
                                                    <select id="font-size" class="toolbar-select"><option value="11">11</option><option value="12">12</option><option value="13" selected>13</option><option value="14">14</option></select>
                                                    <div class="toolbar-divider"></div>
                                                    <button id="btn-bold" class="toolbar-button" type="button"><strong>B</strong></button>
                                                    <button id="btn-italic" class="toolbar-button" type="button"><em>I</em></button>
                                                    <button id="btn-underline" class="toolbar-button" type="button"><u>U</u></button>
                                                    <div class="toolbar-divider"></div>
                                                    <select id="color-select" class="toolbar-select"><option value="#111827">Black</option><option value="#173b70">Dark Blue</option><option value="#185abd">Blue</option></select>
                                                    <div class="toolbar-divider"></div>
                                                    <button id="btn-reset-format" class="toolbar-button" type="button">Reset</button>
                                                    <div style="flex:1"></div>
                                                    <button id="print-button" class="btn btn-light btn-sm" type="button">Print</button>
                                                </div>
                                                <div class="preview-scroll">
                                                    <article class="document-page">
                                                        <h1 id="preview-title" class="doc-title"></h1>
                                                        <table class="info-table"><tbody>
                                                            <tr><th>Subjek</th><td id="preview-subject"></td></tr>
                                                            <tr><th>Kelas</th><td id="preview-class"></td></tr>
                                                            <tr><th>Status</th><td id="preview-status"></td></tr>
                                                            <tr><th>Alokasi Waktu</th><td id="preview-duration"></td></tr>
                                                        </tbody></table>
                                                        <h2>Tujuan / Capaian</h2>
                                                        <p id="preview-achievement" class="preview-text"></p>
                                                        <h2>Tujuan Operasional</h2>
                                                        <ul id="preview-objectives" class="preview-list"></ul>
                                                        <h2>Metode</h2>
                                                        <ul id="preview-methods" class="preview-list"></ul>
                                                        <h2>Media &amp; Sumber</h2>
                                                        <ul id="preview-media" class="preview-list"></ul>
                                                        <h2>Praktik Pedagogis</h2>
                                                        <p id="preview-practice" class="preview-text"></p>
                                                        <h2>Lingkungan Pembelajaran</h2>
                                                        <p id="preview-environment" class="preview-text"></p>
                                                        <h2>Pemanfaatan Digital</h2>
                                                        <p id="preview-digital" class="preview-text"></p>
                                                        <h2>Pengalaman Pembelajaran</h2>
                                                        <p id="preview-experience" class="preview-text"></p>
                                                        <h2>Refleksi</h2>
                                                        <p id="preview-reflection" class="preview-text"></p>
                                                        <h2>Asesmen</h2>
                                                        <p id="preview-assessment" class="preview-text"></p>
                                                    </article>
                                                </div>
                                            </section>
                                        </main>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-12 mb-3">
                            <label class="form-label">Komponen Penilaian</label>
                            <div class="@error('komponen_nilai_ids') is-invalid @enderror">
                                @forelse($komponenNilai as $komponen)
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="komponen_nilai_ids[]" value="{{ $komponen->id }}" id="komponen_{{ $komponen->id }}" {{ in_array($komponen->id, old('komponen_nilai_ids', $selectedKomponenIds ?? [])) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="komponen_{{ $komponen->id }}">
                                            {{ $komponen->nama_komponen }}
                                            @if($komponen->bobot)
                                                <span class="text-muted">({{ $komponen->bobot }}%)</span>
                                            @endif
                                        </label>
                                    </div>
                                @empty
                                    <div class="alert alert-info alert-sm mb-0">
                                        <i class="ti ti-info-circle me-2"></i>Belum ada komponen penilaian
                                    </div>
                                @endforelse
                            </div>
                            @error('komponen_nilai_ids')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6 mb-2">
                            <label class="form-label">Status <span class="text-danger">*</span></label>
                            <select name="status" class="form-select @error('status') is-invalid @enderror" required>
                                <option value="draft" {{ old('status', $item->status) === 'draft' ? 'selected' : '' }}>Draft</option>
                                <option value="published" {{ old('status', $item->status) === 'published' ? 'selected' : '' }}>Published</option>
                            </select>
                            @error('status')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="form-footer">
                        <a href="{{ route('rencana_pembelajaran.index', ['mata_pelajaran_id' => $item->mata_pelajaran_id, 'tingkat' => $item->kelas->tingkat_kelas]) }}" class="btn btn-link">Batal</a>
                        <button type="submit" class="btn btn-primary">
                            <i class="ti ti-check me-1"></i>Perbarui
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

@push('js')
<script src="https://cdn.jsdelivr.net/npm/tinymce@6.7.0/tinymce.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const initialData = {
        title: @json(old('judul', $item->judul)),
        subject: @json($item->mataPelajaran->nama_mapel),
        classText: @json(old('kelas_id') ? optional($kelas->firstWhere('id', old('kelas_id')))->nama_kelas : optional($item->kelas)->nama_kelas),
        status: @json(ucfirst(old('status', $item->status ?? 'draft'))),
        duration: @json(old('alokasi_waktu', $item->alokasi_waktu)),
        achievement: @json(old('capaian_pembelajaran', $item->capaian_pembelajaran)),
        objectives: @json(old('tujuan', $item->tujuan)),
        methods: @json(old('metode', $item->metode)),
        media: @json(old('media', $item->media)),
        resources: @json(old('sumber', $item->sumber)),
        practice: @json(old('praktik_pedagogis', $item->praktik_pedagogis)),
        environment: @json(old('lingkungan_pembelajaran', $item->lingkungan_pembelajaran)),
        digital: @json(old('pemanfaatan_digital', $item->pemanfaatan_digital)),
        experience: @json(old('pengalaman_pembelajaran', $item->pengalaman_pembelajaran)),
        reflection: @json(old('refleksi_pembelajaran', $item->refleksi_pembelajaran)),
        assessment: @json(old('penilaian', $item->penilaian)),
    };

    const formatState = { fontFamily: 'Source Serif 4', fontSize: '13', bold: false, italic: false, underline: false, color: '#111827' };
    const listFields = ['objectives','methods','media','resources'];

    function applyFormattingToDocument() {
        const docPage = document.querySelector('.document-page');
        if (!docPage) return;
        const elements = docPage.querySelectorAll('.preview-text, .preview-list, .info-table');
        elements.forEach(el => {
            el.style.fontFamily = formatState.fontFamily;
            el.style.fontSize = formatState.fontSize + 'px';
            el.style.fontWeight = formatState.bold ? '700' : '400';
            el.style.fontStyle = formatState.italic ? 'italic' : 'normal';
            el.style.textDecoration = formatState.underline ? 'underline' : 'none';
            el.style.color = formatState.color;
        });
    }

    function setList(target, value) {
        target.replaceChildren();
        value.split('\n').map(item => item.trim()).filter(Boolean).forEach(item => {
            const li = document.createElement('li');
            li.textContent = item.replace(/^[•\-\d.]+\s*/, '');
            li.style.fontFamily = formatState.fontFamily;
            li.style.fontSize = formatState.fontSize + 'px';
            li.style.fontWeight = formatState.bold ? '700' : '400';
            li.style.fontStyle = formatState.italic ? 'italic' : 'normal';
            li.style.textDecoration = formatState.underline ? 'underline' : 'none';
            li.style.color = formatState.color;
            target.appendChild(li);
        });
    }

    function updatePreview() {
        document.getElementById('preview-title').textContent = document.getElementById('input-title').value || initialData.title || '';
        document.getElementById('preview-subject').textContent = initialData.subject || '';
        document.getElementById('preview-class').textContent = getSelectedClassName() || initialData.classText || '';
        document.getElementById('preview-status').textContent = document.getElementById('input-status').value ? (document.getElementById('input-status').value.charAt(0).toUpperCase() + document.getElementById('input-status').value.slice(1)) : initialData.status || '';
        document.getElementById('preview-duration').textContent = document.getElementById('input-duration').value || initialData.duration || '';
        document.getElementById('preview-achievement').innerHTML = document.getElementById('input-achievement').value || initialData.achievement || '';
        setList(document.getElementById('preview-objectives'), document.getElementById('input-objectives').value || initialData.objectives || '');
        setList(document.getElementById('preview-methods'), document.getElementById('input-methods').value || initialData.methods || '');
        setList(document.getElementById('preview-media'), (document.getElementById('input-media').value || initialData.media || '') + '\n' + (document.getElementById('input-resources').value || initialData.resources || ''));
        document.getElementById('preview-practice').innerHTML = document.getElementById('input-practice').value || initialData.practice || '';
        document.getElementById('preview-environment').innerHTML = document.getElementById('input-environment').value || initialData.environment || '';
        document.getElementById('preview-digital').innerHTML = document.getElementById('input-digital').value || initialData.digital || '';
        document.getElementById('preview-experience').innerHTML = document.getElementById('input-experience').value || initialData.experience || '';
        document.getElementById('preview-reflection').innerHTML = document.getElementById('input-reflection').value || initialData.reflection || '';
        document.getElementById('preview-assessment').innerHTML = document.getElementById('input-assessment').value || initialData.assessment || '';
        applyFormattingToDocument();
    }

    function getSelectedClassName() {
        const selected = document.querySelector('input[name="kelas_id"]:checked');
        return selected ? selected.nextElementSibling?.textContent.trim() : '';
    }

    function updateClassPreview() {
        document.getElementById('preview-class').textContent = getSelectedClassName() || initialData.classText || '';
    }

    document.getElementById('font-family').addEventListener('change', e => { formatState.fontFamily = e.target.value; applyFormattingToDocument(); });
    document.getElementById('font-size').addEventListener('change', e => { formatState.fontSize = e.target.value; applyFormattingToDocument(); });
    document.getElementById('btn-bold').addEventListener('click', () => { formatState.bold = !formatState.bold; applyFormattingToDocument(); });
    document.getElementById('btn-italic').addEventListener('click', () => { formatState.italic = !formatState.italic; applyFormattingToDocument(); });
    document.getElementById('btn-underline').addEventListener('click', () => { formatState.underline = !formatState.underline; applyFormattingToDocument(); });
    document.getElementById('color-select').addEventListener('change', e => { formatState.color = e.target.value; applyFormattingToDocument(); });
    document.getElementById('btn-reset-format').addEventListener('click', () => {
        Object.assign(formatState, { fontFamily: 'Source Serif 4', fontSize: '13', bold: false, italic: false, underline: false, color: '#111827' });
        document.getElementById('font-family').value = formatState.fontFamily;
        document.getElementById('font-size').value = formatState.fontSize;
        document.getElementById('color-select').value = formatState.color;
        applyFormattingToDocument();
    });

    ['input-title','input-duration','input-status','input-achievement','input-objectives','input-methods','input-media','input-resources','input-practice','input-environment','input-digital','input-experience','input-reflection','input-assessment'].forEach(id => {
        const el = document.getElementById(id);
        if (!el) return;
        el.addEventListener('input', updatePreview);
        el.addEventListener('change', updatePreview);
    });

    document.querySelectorAll('input[name="kelas_id"]').forEach(radio => {
        radio.addEventListener('change', updateClassPreview);
    });

    document.getElementById('print-button').addEventListener('click', () => {
        const doc = document.querySelector('.document-page');
        if (!doc) return window.print();
        const style = `body{margin:0;color:#202124;font-family:'Source Serif 4',Georgia,serif}.document-page{box-sizing:border-box;width:840px;margin:0 auto;padding:70px 76px 84px;background:#fff}.doc-title{font-size:25px;text-align:center;margin-bottom:20px}.info-table{width:100%;border-collapse:collapse;margin-bottom:18px}.info-table th,.info-table td{border:1px solid #aeb9c8;padding:8px 10px;text-align:left;vertical-align:top}.info-table th{background:#eaf1fb;color:#1e3a62}.preview-text{white-space:pre-wrap}`;
        const printWindow = window.open('', '_blank');
        printWindow.document.write('<!doctype html><html><head><title>Print RPP</title><meta charset="utf-8"><link href="https://fonts.googleapis.com/css2?family=Source+Serif+4:wght@400;600;700&display=swap" rel="stylesheet"><style>' + style + '</style></head><body>' + doc.outerHTML + '</body></html>');
        printWindow.document.close();
        printWindow.focus();
        setTimeout(() => { printWindow.print(); setTimeout(() => { try { printWindow.close(); } catch (e) {} }, 500); }, 250);
    });

    if (typeof tinymce !== 'undefined') {
        tinymce.init({
            selector: 'textarea.rich-editor',
            plugins: 'lists link image table code help advlist autolink',
            toolbar: 'undo redo | formatselect | bold italic underline | alignleft aligncenter alignright | bullist numlist | link image table | code help',
            menubar: false,
            height: 220,
            content_style: 'body { font-family: inherit; color: #202124; }',
            setup: function(editor) {
                editor.on('Change KeyUp', function() {
                    const ta = document.getElementById(editor.id);
                    if (ta) ta.value = editor.getContent();
                    updatePreview();
                });
            }
        });
    }

    updatePreview();
    updateClassPreview();
});
</script>
@endpush

@push('css')
    <link href="https://fonts.googleapis.com/css2?family=Libre+Franklin:wght@400;500;600;700&family=Source+Serif+4:wght@400;600;700&display=swap" rel="stylesheet">
    <style>
        #rpp-interactive-root .app-shell { width:100%; min-height:420px; background:#e4e5e7; display:flex; flex-direction:column; }
        #rpp-interactive-root .workspace { width:100%; flex:1; display:grid; grid-template-columns: minmax(310px, .84fr) minmax(480px, 1.4fr); overflow:hidden; }
        #rpp-interactive-root .form-panel { background:#f5f6f8; border-right:1px solid #c8cbd0; overflow-y:auto; padding:22px 20px 40px; }
        #rpp-interactive-root .form-group { background:#fff; border:1px solid #d6d9de; border-radius:5px; padding:15px; margin-bottom:13px; box-shadow:0 1px 2px rgba(24,36,50,.04); }
        #rpp-interactive-root .field-label { display:block; margin-bottom:6px; color:#3d4753; font-size:12px; font-weight:700; }
        #rpp-interactive-root .field-row { margin-bottom:15px; }
        #rpp-interactive-root .editor-input, #rpp-interactive-root .editor-textarea { width:100%; border:1px solid #b9c1cb; color:#202124; border-radius:3px; background:#fff; padding:8px 9px; font-size:13px; }
        #rpp-interactive-root .editor-textarea { min-height:92px; resize:vertical; }
        #rpp-interactive-root .preview-panel { overflow:auto; padding:0; background:#e4e5e7; display:flex; flex-direction:column; }
        #rpp-interactive-root .format-toolbar { background:#f5f6f8; border-bottom:1px solid #c8cbd0; padding:10px 16px; display:flex; gap:12px; align-items:center; flex-wrap:wrap; border-right:1px solid #c8cbd0; }
        #rpp-interactive-root .toolbar-select { padding:5px 8px; border:1px solid #b9c1cb; border-radius:3px; background:#fff; font-size:12px; }
        #rpp-interactive-root .toolbar-button { width:28px; height:28px; border:1px solid #b9c1cb; border-radius:3px; background:#fff; cursor:pointer; display:flex; align-items:center; justify-content:center; }
        #rpp-interactive-root .preview-scroll { overflow:auto; flex:1; padding:32px 28px 48px; background-image:radial-gradient(#d2d4d7 .65px, transparent .65px); background-size:13px 13px; }
        #rpp-interactive-root .document-page { width:min(100%,840px); min-height:1120px; margin:0 auto; padding:70px 76px 84px; background:#fff; box-shadow:0 2px 7px rgba(0,0,0,.18),0 18px 38px rgba(0,0,0,.12); }
        #rpp-interactive-root .doc-title { font-family:'Source Serif 4', Georgia, serif; font-size:25px; text-align:center; color:#111827; margin:0 0 31px; font-weight:700; }
        #rpp-interactive-root .info-table { width:100%; border-collapse:collapse; margin-bottom:18px; }
        #rpp-interactive-root .info-table th, #rpp-interactive-root .info-table td { border:1px solid #aeb9c8; padding:8px 10px; text-align:left; vertical-align:top; }
        #rpp-interactive-root .info-table th { width:180px; background:#eaf1fb; color:#1e3a62; font-weight:700; }
        #rpp-interactive-root .preview-text { white-space:pre-wrap; }
        #rpp-interactive-root .preview-list { margin:0; padding-left:18px; }
        @media (max-width:930px) { #rpp-interactive-root .workspace { grid-template-columns:1fr; } #rpp-interactive-root .form-panel { border-right:0; border-bottom:1px solid #c8cbd0; } }
    </style>
@endpush
@endsection

