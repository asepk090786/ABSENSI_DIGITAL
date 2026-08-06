@extends('layouts.app', ['pageSlug' => 'rencana_pembelajaran'])

@section('title', 'Detail Rencana Pembelajaran')

@section('content')
<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header border-0 pt-3 pb-2">
                <div class="row align-items-center">
                    <div class="col">
                        <h4 class="card-title">Detail Rencana Pembelajaran</h4>
                    </div>
                    <div class="col-auto">
                        <a href="{{ route('rencana_pembelajaran.index', ['mata_pelajaran_id' => $item->mata_pelajaran_id, 'tingkat' => optional($item->kelas)->tingkat_kelas]) }}" class="btn btn-secondary btn-sm">
                            <i class="ti ti-arrow-left me-1"></i>Kembali
                        </a>
                    </div>
                </div>
            </div>
            <div class="card-body">
                <div class="rpp-action-bar d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
                    <div>
                        <div class="d-flex flex-wrap align-items-center gap-2 mb-2">
                            <span class="badge bg-primary-lt text-primary">Rencana Pembelajaran</span>
                            <span class="badge bg-{{ $item->status === 'published' ? 'success' : 'warning' }}-lt text-{{ $item->status === 'published' ? 'success' : 'warning' }}">{{ ucfirst($item->status ?? 'draft') }}</span>
                        </div>
                        <h5 class="fw-semibold mb-0">{{ $item->judul ?? 'Tanpa Judul' }}</h5>
                    </div>
                    <div class="d-flex flex-wrap gap-2">
                        <a href="{{ route('rencana_pembelajaran.edit', $item->id) }}" class="btn btn-primary btn-sm">
                            <i class="ti ti-edit me-1"></i>Edit
                        </a>
                        <a href="{{ route('rencana_pembelajaran.export_pdf', $item->id) }}" class="btn btn-danger btn-sm" target="_blank">
                            <i class="ti ti-file-pdf me-1"></i>Export PDF
                        </a>
                    </div>
                </div>

                <div id="rpp-interactive-root">
                    <div class="app-shell">
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
                                    <div id="preview-achievement" class="preview-text"></div>
                                    <h2>Tujuan Operasional</h2>
                                    <ul id="preview-objectives" class="preview-list"></ul>
                                    <h2>Metode</h2>
                                    <ul id="preview-methods" class="preview-list"></ul>
                                    <h2>Media &amp; Sumber</h2>
                                    <ul id="preview-media" class="preview-list"></ul>
                                    <h2>Praktik Pedagogis</h2>
                                    <div id="preview-practice" class="preview-text"></div>
                                    <h2>Lingkungan Pembelajaran</h2>
                                    <div id="preview-environment" class="preview-text"></div>
                                    <h2>Pemanfaatan Digital</h2>
                                    <div id="preview-digital" class="preview-text"></div>
                                    <h2>Pengalaman Pembelajaran</h2>
                                    <div id="preview-experience" class="preview-text"></div>
                                    <h2>Refleksi</h2>
                                    <div id="preview-reflection" class="preview-text"></div>
                                    <h2>Asesmen</h2>
                                    <div id="preview-assessment" class="preview-text"></div>
                                </article>
                            </div>
                        </section>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('js')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const initialData = {
            title: @json($item->judul),
            subject: @json(optional($item->mataPelajaran)->nama_mapel),
            classText: @json(optional($item->kelas)->nama_kelas),
            status: @json(ucfirst($item->status ?? 'draft')),
            duration: @json($item->alokasi_waktu),
            achievement: @json($item->capaian_pembelajaran),
            objectives: @json($item->tujuan),
            methods: @json($item->metode),
            media: @json($item->media),
            resources: @json($item->sumber),
            practice: @json($item->praktik_pedagogis),
            environment: @json($item->lingkungan_pembelajaran),
            digital: @json($item->pemanfaatan_digital),
            experience: @json($item->pengalaman_pembelajaran),
            reflection: @json($item->refleksi_pembelajaran),
            assessment: @json($item->penilaian),
        };

        const formatState = { fontFamily: 'Source Serif 4', fontSize: '13', bold: false, italic: false, underline: false, color: '#111827' };
        const listFields = ['objectives', 'methods', 'media'];

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
            document.getElementById('preview-title').textContent = initialData.title || '';
            document.getElementById('preview-subject').textContent = initialData.subject || '';
            document.getElementById('preview-class').textContent = initialData.classText || '';
            document.getElementById('preview-status').textContent = initialData.status || '';
            document.getElementById('preview-duration').textContent = initialData.duration || '';
            document.getElementById('preview-achievement').innerHTML = initialData.achievement || '';
            setList(document.getElementById('preview-objectives'), initialData.objectives || '');
            setList(document.getElementById('preview-methods'), initialData.methods || '');
            setList(document.getElementById('preview-media'), [initialData.media, initialData.resources].filter(Boolean).join('\n'));
            document.getElementById('preview-practice').innerHTML = initialData.practice || '';
            document.getElementById('preview-environment').innerHTML = initialData.environment || '';
            document.getElementById('preview-digital').innerHTML = initialData.digital || '';
            document.getElementById('preview-experience').innerHTML = initialData.experience || '';
            document.getElementById('preview-reflection').innerHTML = initialData.reflection || '';
            document.getElementById('preview-assessment').innerHTML = initialData.assessment || '';
            applyFormattingToDocument();
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

        updatePreview();
    });
</script>
@endpush

@push('css')
    <link href="https://fonts.googleapis.com/css2?family=Libre+Franklin:wght@400;500;600;700&family=Source+Serif+4:wght@400;600;700&display=swap" rel="stylesheet">
    <style>
        #rpp-interactive-root .app-shell { width:100%; min-height:420px; background:#e4e5e7; display:flex; flex-direction:column; }
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
        #rpp-interactive-root .preview-list { margin:0; padding-left: 18px; }
        @media (max-width:930px) { #rpp-interactive-root .app-shell { flex-direction:column; } }
    </style>
@endpush
@endsection
