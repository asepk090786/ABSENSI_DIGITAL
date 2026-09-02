@extends('layouts.app', ['pageSlug' => 'modul_ajar'])

@section('title', $mode === 'edit' ? 'Edit Modul Ajar' : 'Tambah Modul Ajar')

@section('content')
<div class="app-shell" style="min-height: calc(100vh - 140px); background: #dfe7f0; border-radius: 16px; overflow: hidden; box-shadow: 0 4px 16px rgba(0,0,0,.08);">
    <header class="app-header" style="padding: 14px 20px; display:flex; align-items:center; gap:14px; background: linear-gradient(115deg, #174780, #2d6ab6); color:#fff;">
        <div style="width: 42px; height: 42px; display:grid; place-items:center; border-radius: 10px; background:#fff; color:#235c9f; font-weight:700; font-size: 20px;">M</div>
        <div>
            <div style="font-weight:700; font-size:15px;">{{ $mode === 'edit' ? 'Edit Modul Ajar' : 'Tambah Modul Ajar' }}</div>
            <div style="font-size:12px; opacity:.9;">{{ $mode === 'edit' ? 'Kelola informasi, upload, preview dan edit dokumen modul ajar' : 'Form isi dan pratinjau modul ajar' }}</div>
        </div>
        <a href="{{ route('rencana_pembelajaran.index') }}" class="btn btn-sm btn-light text-primary" style="margin-left:auto;">Kembali ke Daftar</a>
    </header>

    <link href="{{ asset('vendor/summernote/summernote-lite.min.css') }}" rel="stylesheet">
    <script>
        window.jQuery || document.write('<script src="{{ asset('vendor/summernote/jquery-3.6.0.min.js') }}"><\/script>');
    </script>
    <script src="{{ asset('vendor/summernote/summernote-lite.min.js') }}"></script>
    <style>
        .note-toolbar {
            border-radius: 10px 10px 0 0;
        }
        .note-editor .note-editable {
            min-height: 120px;
        }
    </style>

    <main id="workspace" class="workspace" style="display:grid; grid-template-columns:minmax(320px,410px) minmax(500px,1fr); min-height:0;">
        <aside class="editor-side" style="overflow:auto; padding:22px 18px 36px; background:#f7f9fc; border-right:1px solid #cad7e6;">
            <div style="max-width:390px; margin:0 auto;">
                <div style="margin:16px 0; padding:14px; border:1px solid #d8e3f0; border-radius:12px; background:#fff; box-shadow:0 5px 16px rgba(32,73,119,.05);">
                    <div style="display:flex; justify-content:space-between; gap:10px; align-items:center; font-size:12px;">
                        <span>Kelengkapan formulir</span>
                        <span id="progress-value" style="font-size:11px; font-weight:700; color:#2865aa">13 / 13</span>
                    </div>
                    <div style="height:7px; margin-top:10px; overflow:hidden; border-radius:999px; background:#e5edf6;">
                        <div id="progress-fill" style="width:100%; height:100%; border-radius:inherit; background:linear-gradient(90deg,#3e86dc,#70a7e6); transition:width .2s ease;"></div>
                    </div>
                </div>

                <form id="rpp-form" action="{{ $mode === 'edit' ? route('rencana_pembelajaran.update', $moduleId) : route('rencana_pembelajaran.store') }}" method="POST">
                    @csrf
                    @if($mode === 'edit')
                        @method('PUT')
                    @endif
                    <input type="hidden" name="module_id" value="{{ $moduleId ?? '' }}">
                    <div style="display:flex; justify-content:space-between; align-items:center; gap:10px; margin-bottom:12px;">
                        <a href="{{ route('rencana_pembelajaran.index') }}" class="btn btn-outline-secondary btn-sm">Batal</a>
                        <div style="display:flex; gap:8px;">
                            @if($mode === 'create')
                            <button type="button" id="btn-import-word" style="display:none;" class="btn btn-outline-success btn-sm">
                                <i class="ti ti-file-type-docx"></i> Import dari Word
                            </button>
                            @endif
                            <button type="submit" id="save-module-btn" class="btn btn-primary btn-sm">Simpan</button>
                        </div>
                    </div>

                    @if($mode === 'create')
                    <div id="import-section" style="display:none; margin-bottom:14px; padding:14px; border:1px dashed #c9d7e7; border-radius:11px; background:#fff;">
                        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:10px;">
                            <span style="font-size:12px; font-weight:600; color:#24496f;">Import dari file Word (.docx)</span>
                            <button type="button" id="btn-close-import" class="btn btn-sm btn-light">Tutup</button>
                        </div>
                        <div style="display:flex; gap:10px; align-items:center; flex-wrap:wrap;">
                            <input type="file" id="import-file" accept=".docx" style="font-size:12px;" />
                            <button type="button" id="btn-process-import" class="btn btn-success btn-sm">Proses Import</button>
                            <a href="{{ route('rencana_pembelajaran.template') }}" class="btn btn-outline-secondary btn-sm">Download Template</a>
                        </div>
                        <div id="import-status" style="margin-top:8px; font-size:11px;"></div>
                    </div>
                    @endif


                    <section class="form-section" style="margin-bottom:10px; overflow:hidden; border:1px solid #d8e2ee; border-radius:11px; background:#fff;">
                        <button class="section-toggle" type="button" aria-expanded="true" style="width:100%; display:flex; align-items:center; justify-content:space-between; gap:10px; padding:14px; border:0; background:#fff; color:#24496f; text-align:left;">
                            <span style="display:flex; align-items:center; gap:10px;"><span style="width:29px; height:29px; display:grid; place-items:center; border-radius:8px; background:#eaf3ff; color:#2865aa;"><i class="ti ti-file-text"></i></span><span style="font-size:13px; font-weight:600;">Informasi Umum</span></span>
                            <i class="ti ti-chevron-down"></i>
                        </button>
                        <div class="section-content" style="padding:0 14px 15px;">
                            <div class="field" style="margin-top:13px;"><label for="input-title" style="display:block; margin-bottom:6px; font-size:12px; font-weight:600;">Judul Modul Ajar</label><input type="text" id="input-title" class="form-control" style="width:100%; min-height:44px; border:1px solid #c9d7e7; border-radius:8px; background:#fff; padding:9px 10px; color:#1c2c3d; font-size:12px;" /><input type="hidden" name="title" id="field-title" value=""></div>
                            <div class="field" style="margin-top:13px;"><label for="input-subject" style="display:block; margin-bottom:6px; font-size:12px; font-weight:600;">Mata Pelajaran</label>
                                @if(!empty($mataPelajaranList) && $mataPelajaranList->isNotEmpty())
                                    <select id="input-subject" class="form-select" style="width:100%; min-height:44px; border:1px solid #c9d7e7; border-radius:8px; background:#fff; padding:9px 10px; font-size:12px;" aria-label="Mata Pelajaran">
                                        <option value="">-- Pilih Mata Pelajaran --</option>
                                        @foreach($mataPelajaranList as $mp)
                                            <option value="{{ $mp->id }}">{{ $mp->nama_mapel ?? ($mp['nama_mapel'] ?? $mp->name ?? '') }}</option>
                                        @endforeach
                                    </select>
                                @else
                                    <div id="input-subject" contenteditable="true" data-editor-type="inline" style="width:100%; min-height:44px; border:1px solid #c9d7e7; border-radius:8px; background:#fff; padding:9px 10px; color:#1c2c3d; font-size:12px; line-height:1.5;"></div>
                                @endif
                                <input type="hidden" name="subject" id="field-subject" value="">
                                <input type="hidden" name="mata_pelajaran_id" id="field-mata_pelajaran_id" value="">
                            </div>
                            <div class="field" style="margin-top:13px;"><label for="input-class" style="display:block; margin-bottom:6px; font-size:12px; font-weight:600;">Kelas</label>
                                @if(!empty($kelasList) && $kelasList->isNotEmpty())
                                    <div id="input-class-combobox" style="width:100%;">
                                        <div id="class-chips" style="min-height:38px; display:flex; gap:6px; flex-wrap:wrap; align-items:center; padding:6px; border:1px solid #c9d7e7; border-radius:8px; background:#fff;"></div>
                                        <input id="input-class-search" type="text" placeholder="Cari atau pilih kelas..." autocomplete="off" style="width:100%; margin-top:8px; padding:8px 10px; border:1px solid #c9d7e7; border-radius:8px; background:#fff;">
                                        <ul id="class-dropdown" style="display:none; max-height:220px; overflow:auto; border:1px solid #c9d7e7; border-radius:8px; margin:6px 0 0 0; padding:6px; background:#fff; list-style:none;">
                                            @foreach($kelasList as $k)
                                                <li class="class-option" data-id="{{ $k->id }}" data-label="{{ $k->nama_kelas ?? ($k['nama_kelas'] ?? $k->nama ?? '') }}" style="padding:6px 8px; cursor:pointer; border-radius:6px;">{{ $k->nama_kelas ?? ($k['nama_kelas'] ?? $k->nama ?? '') }}</li>
                                            @endforeach
                                        </ul>
                                    </div>
                                @else
                                    <div id="input-class" contenteditable="true" data-editor-type="inline" style="width:100%; min-height:44px; border:1px solid #c9d7e7; border-radius:8px; background:#fff; padding:9px 10px; color:#1c2c3d; font-size:12px; line-height:1.5;"></div>
                                @endif
                                <input type="hidden" name="class" id="field-class" value="">
                                <input type="hidden" name="kelas_id" id="field-kelas_id" value="">
                            </div>
                            <div class="field" style="margin-top:13px;"><label for="input-fase" style="display:block; margin-bottom:6px; font-size:12px; font-weight:600;">Fase</label>
                                @if(!empty($faseOptions))
                                    <select id="input-fase" class="form-select" style="width:100%; min-height:44px; border:1px solid #c9d7e7; border-radius:8px; background:#fff; padding:9px 10px; font-size:12px;" aria-label="Fase">
                                        <option value="">-- Pilih Fase --</option>
                                        @foreach($faseOptions as $value => $label)
                                            <option value="{{ $value }}" {{ (isset($selectedFase) && $selectedFase == $value) ? 'selected' : '' }}>{{ $label }}</option>
                                        @endforeach
                                    </select>
                                @else
                                    <input type="text" id="input-fase" class="form-control" style="width:100%;" />
                                @endif
                                <input type="hidden" name="fase" id="field-fase" value="{{ $selectedFase ?? '' }}">
                            </div>
                            <div class="field" style="margin-top:13px;"><label for="input-status" style="display:block; margin-bottom:6px; font-size:12px; font-weight:600;">Status</label>
                                <select id="input-status" class="form-select" style="width:100%; min-height:44px; border:1px solid #c9d7e7; border-radius:8px; background:#fff; padding:9px 10px; font-size:12px;" aria-label="Status">
                                    <option value="draft">Draft (Belum digunakan untuk KBM)</option>
                                    <option value="published">Publish (Digunakan untuk KBM)</option>
                                </select>
                                <input type="hidden" name="status" id="field-status" value="draft">
                            </div>
                            <div class="field" style="margin-top:13px;"><label for="input-duration" style="display:block; margin-bottom:6px; font-size:12px; font-weight:600;">Alokasi Waktu</label><input type="text" id="input-duration" class="form-control" style="width:100%; min-height:44px; border:1px solid #c9d7e7; border-radius:8px; background:#fff; padding:9px 10px; color:#1c2c3d; font-size:12px;" /><input type="hidden" name="duration" id="field-duration" value=""></div>
                            <div class="field" style="margin-top:13px;"><label for="input-dimensi-search" style="display:block; margin-bottom:6px; font-size:12px; font-weight:600;">Dimensi Lulusan</label><p style="margin-top:-3px; margin-bottom:8px; font-size:11px; color:#5b6770;">Tuliskan / Pilih dimensi lulusan yang berhubungan dengan materi ajar</p>
                                <div id="input-dimensi-combobox" style="width:100%;">
                                    <div id="dimensi-chips" style="min-height:38px; display:flex; gap:6px; flex-wrap:wrap; align-items:center; padding:6px; border:1px solid #c9d7e7; border-radius:8px; background:#fff;"></div>
                                    <input id="input-dimensi-search" type="text" placeholder="Cari atau pilih dimensi lulusan..." autocomplete="off" style="width:100%; margin-top:8px; padding:8px 10px; border:1px solid #c9d7e7; border-radius:8px; background:#fff;">
                                    <ul id="dimensi-dropdown" style="display:none; max-height:220px; overflow:auto; border:1px solid #c9d7e7; border-radius:8px; margin:6px 0 0 0; padding:6px; background:#fff; list-style:none;">
                                        <li class="dimensi-option" data-label="Keimanan dan Ketakwaan terhadap Tuhan YME" style="padding:6px 8px; cursor:pointer; border-radius:6px;">Keimanan dan Ketakwaan terhadap Tuhan YME</li>
                                        <li class="dimensi-option" data-label="Kewargaan" style="padding:6px 8px; cursor:pointer; border-radius:6px;">Kewargaan</li>
                                        <li class="dimensi-option" data-label="Penalaran Kritis" style="padding:6px 8px; cursor:pointer; border-radius:6px;">Penalaran Kritis</li>
                                        <li class="dimensi-option" data-label="Kreativitas" style="padding:6px 8px; cursor:pointer; border-radius:6px;">Kreativitas</li>
                                        <li class="dimensi-option" data-label="Kolaborasi" style="padding:6px 8px; cursor:pointer; border-radius:6px;">Kolaborasi</li>
                                        <li class="dimensi-option" data-label="Kemandirian" style="padding:6px 8px; cursor:pointer; border-radius:6px;">Kemandirian</li>
                                        <li class="dimensi-option" data-label="Kesehatan" style="padding:6px 8px; cursor:pointer; border-radius:6px;">Kesehatan</li>
                                        <li class="dimensi-option" data-label="Komunikasi" style="padding:6px 8px; cursor:pointer; border-radius:6px;">Komunikasi</li>
                                    </ul>
                                </div>
                                <input type="hidden" name="dimensi_lulusan" id="field-dimensi_lulusan" value="">
                            </div>
                        </div>
                    </section>

                    <section class="form-section" style="margin-bottom:10px; overflow:hidden; border:1px solid #d8e2ee; border-radius:11px; background:#fff;">
                        <button class="section-toggle" type="button" aria-expanded="true" style="width:100%; display:flex; align-items:center; justify-content:space-between; gap:10px; padding:14px; border:0; background:#fff; color:#24496f; text-align:left;">
                            <span style="display:flex; align-items:center; gap:10px;"><span style="width:29px; height:29px; display:grid; place-items:center; border-radius:8px; background:#eaf3ff; color:#2865aa;"><i class="ti ti-target"></i></span><span style="font-size:13px; font-weight:600;">Modul Ajar Inti</span></span>
                            <i class="ti ti-chevron-down"></i>
                        </button>
                        <div class="section-content" style="padding:0 14px 15px;">
                            <div class="field" style="margin-top:13px;"><label for="input-achievement" style="display:block; margin-bottom:6px; font-size:12px; font-weight:600;">Capaian Pembelajaran</label><div id="input-achievement" contenteditable="true" data-editor-type="block" style="width:100%; min-height:110px; border:1px solid #c9d7e7; border-radius:8px; background:#fff; padding:9px 10px; color:#1c2c3d; font-size:12px; line-height:1.5; resize:vertical;"></div><input type="hidden" name="achievement" id="field-achievement" value=""></div>
                            <div class="field" style="margin-top:13px;"><label for="input-objectives" style="display:block; margin-bottom:6px; font-size:12px; font-weight:600;">Tujuan Pembelajaran</label><div id="input-objectives" contenteditable="true" data-editor-type="block" style="width:100%; min-height:110px; border:1px solid #c9d7e7; border-radius:8px; background:#fff; padding:9px 10px; color:#1c2c3d; font-size:12px; line-height:1.5; resize:vertical;"></div><input type="hidden" name="objectives" id="field-objectives" value=""></div>
                            <div class="field" style="margin-top:13px;"><label for="input-methods" style="display:block; margin-bottom:6px; font-size:12px; font-weight:600;">Metode Pembelajaran</label><div id="input-methods" contenteditable="true" data-editor-type="block" style="width:100%; min-height:110px; border:1px solid #c9d7e7; border-radius:8px; background:#fff; padding:9px 10px; color:#1c2c3d; font-size:12px; line-height:1.5; resize:vertical;"></div><input type="hidden" name="methods" id="field-methods" value=""></div>
                            <div class="field" style="margin-top:13px;"><label for="input-practice" style="display:block; margin-bottom:6px; font-size:12px; font-weight:600;">Praktik Pedagogis</label><div id="input-practice" contenteditable="true" data-editor-type="block" style="width:100%; min-height:110px; border:1px solid #c9d7e7; border-radius:8px; background:#fff; padding:9px 10px; color:#1c2c3d; font-size:12px; line-height:1.5; resize:vertical;"></div><input type="hidden" name="practice" id="field-practice" value=""></div>
                            <div class="field" style="margin-top:13px;"><label for="input-environment" style="display:block; margin-bottom:6px; font-size:12px; font-weight:600;">Lingkungan Pembelajaran</label><div id="input-environment" contenteditable="true" data-editor-type="block" style="width:100%; min-height:110px; border:1px solid #c9d7e7; border-radius:8px; background:#fff; padding:9px 10px; color:#1c2c3d; font-size:12px; line-height:1.5; resize:vertical;"></div><input type="hidden" name="environment" id="field-environment" value=""></div>
                            <div class="field" style="margin-top:13px;"><label for="input-digital" style="display:block; margin-bottom:6px; font-size:12px; font-weight:600;">Pemanfaatan Digital</label><div id="input-digital" contenteditable="true" data-editor-type="block" style="width:100%; min-height:110px; border:1px solid #c9d7e7; border-radius:8px; background:#fff; padding:9px 10px; color:#1c2c3d; font-size:12px; line-height:1.5; resize:vertical;"></div><input type="hidden" name="digital" id="field-digital" value=""></div>
                            <div class="field" style="margin-top:13px;"><label for="input-experience" style="display:block; margin-bottom:6px; font-size:12px; font-weight:600;">Pengalaman Pembelajaran</label><div id="input-experience" contenteditable="true" data-editor-type="block" style="width:100%; min-height:110px; border:1px solid #c9d7e7; border-radius:8px; background:#fff; padding:9px 10px; color:#1c2c3d; font-size:12px; line-height:1.5; resize:vertical;"></div><input type="hidden" name="experience" id="field-experience" value=""></div>
                            <div class="field" style="margin-top:13px;"><label for="input-reflection" style="display:block; margin-bottom:6px; font-size:12px; font-weight:600;">Refleksi Pembelajaran</label><div id="input-reflection" contenteditable="true" data-editor-type="block" style="width:100%; min-height:110px; border:1px solid #c9d7e7; border-radius:8px; background:#fff; padding:9px 10px; color:#1c2c3d; font-size:12px; line-height:1.5; resize:vertical;"></div><input type="hidden" name="reflection" id="field-reflection" value=""></div>
                            <div class="field" style="margin-top:13px;"><label for="input-assessment" style="display:block; margin-bottom:6px; font-size:12px; font-weight:600;">Asesmen</label><div id="input-assessment" contenteditable="true" data-editor-type="block" style="width:100%; min-height:110px; border:1px solid #c9d7e7; border-radius:8px; background:#fff; padding:9px 10px; color:#1c2c3d; font-size:12px; line-height:1.5; resize:vertical;"></div><input type="hidden" name="assessment" id="field-assessment" value=""></div>
                        </div>
                    </section>
                </form>
            </div>
        </aside>

        <section class="preview-side" style="min-width:0; display:flex; flex-direction:column; background:#dfe7f0;">
            <div style="min-height:47px; padding:0 22px; display:flex; align-items:center; justify-content:space-between; gap:14px; background:rgba(255,255,255,.88); border-bottom:1px solid #cad7e6; font-size:11px;">
                <span style="display:inline-flex; align-items:center; gap:7px; font-weight:700;"><i class="ti ti-file-check"></i> Pratinjau Modul Ajar</span>
                @if($mode === 'edit')
                <div style="display:none; gap:8px; align-items:center;">
                    <input id="document-upload-input" type="file" accept=".doc,.docx" style="display:none;" />
                    <button type="button" id="btn-upload-document" class="btn btn-sm btn-outline-primary" style="display:none;">Upload Dokumen</button>
                    <button type="button" id="btn-preview-collabora" class="btn btn-sm btn-light" disabled style="display:none;">Preview</button>
                    <button type="button" id="btn-edit-collabora" class="btn btn-sm btn-primary" disabled style="display:none;">Edit dengan Collabora</button>
                    <button type="button" id="btn-refresh-versions" class="btn btn-sm btn-outline-secondary" disabled style="display:none;">Riwayat Versi</button>
                </div>
                @else
                <span>Preview</span>
                @endif
            </div>

            @if($mode === 'edit')
            <div id="doc-meta" style="padding:12px 22px; font-size:12px; border-bottom:1px solid #cad7e6; background:#f5f9ff; color:#234;">
                <div id="doc-status-line">Belum ada dokumen Modul Ajar</div>
                <div id="doc-detail-line" style="margin-top:4px; color:#4e647c;"></div>
            </div>
            @endif

            <div id="doc-viewer-wrap" style="display:none; flex:1; min-height:0; background:#dfe7f0;">
                <div id="doc-loading" style="padding:20px; text-align:center; font-size:13px; color:#2c5074;">Memuat dokumen...</div>
                <iframe id="collabora-frame" style="display:none; width:100%; height:calc(100vh - 170px); border:0;"></iframe>
                <div id="doc-error" style="display:none; padding:20px; text-align:center; font-size:13px; color:#8c1f1f;">
                    Dokumen tidak dapat dibuka.
                    <div style="margin-top:10px;">
                        <button type="button" id="btn-retry-doc" class="btn btn-sm btn-outline-danger">Coba Lagi</button>
                    </div>
                </div>
            </div>

            <div id="html-preview-wrap" style="flex:1; overflow:auto; padding:28px 28px 52px; background-color:#dfe7f0; background-image:radial-gradient(#becbd9 .7px,transparent .7px); background-size:15px 15px;">
                <article id="document-page" style="width:min(100%,820px); min-height:1100px; margin:0 auto; padding:58px 65px 80px; background:#fff; box-shadow:0 4px 12px rgba(26,55,89,.18),0 24px 44px rgba(26,55,89,.13);">
                    <p class="text-muted text-center mb-2" style="font-size:10px; letter-spacing:1px; text-transform:uppercase;">MODUL AJAR</p>
                    <h1 style="margin:0 0 30px; color:#16283c; font-size:26px; font-weight:700; line-height:1.28; text-align:center;">MODUL AJAR</h1>
                    <h2 style="margin:27px 0 10px; padding-bottom:6px; border-bottom:1.5px solid #4b78a9; font-size:15px;">Informasi Umum</h2>
                    <table style="width:100%; border-collapse:collapse; margin-bottom:18px;">
                        <tbody>
                            <tr><th style="width:165px; padding:8px 10px; border:1px solid #afbdce; text-align:left; vertical-align:top; background:#eef5fc; font-weight:700;">Judul Modul Ajar</th><td id="preview-title" style="padding:8px 10px; border:1px solid #afbdce; vertical-align:top;"></td></tr>
                            <tr><th style="width:165px; padding:8px 10px; border:1px solid #afbdce; text-align:left; vertical-align:top; background:#eef5fc; font-weight:700;">Mata Pelajaran</th><td id="preview-subject" style="padding:8px 10px; border:1px solid #afbdce; vertical-align:top;"></td></tr>
                            <tr><th style="width:165px; padding:8px 10px; border:1px solid #afbdce; text-align:left; vertical-align:top; background:#eef5fc; font-weight:700;">Kelas / Fase</th><td id="preview-class" style="padding:8px 10px; border:1px solid #afbdce; vertical-align:top;"></td></tr>
                            <tr><th style="width:165px; padding:8px 10px; border:1px solid #afbdce; text-align:left; vertical-align:top; background:#eef5fc; font-weight:700;">Status</th><td id="preview-status" style="padding:8px 10px; border:1px solid #afbdce; vertical-align:top;"></td></tr>
                            <tr><th style="width:165px; padding:8px 10px; border:1px solid #afbdce; text-align:left; vertical-align:top; background:#eef5fc; font-weight:700;">Alokasi Waktu</th><td id="preview-duration" style="padding:8px 10px; border:1px solid #afbdce; vertical-align:top;"></td></tr>
                            <tr><th style="width:165px; padding:8px 10px; border:1px solid #afbdce; text-align:left; vertical-align:top; background:#eef5fc; font-weight:700;">Dimensi Lulusan</th><td id="preview-dimensi_lulusan" style="padding:8px 10px; border:1px solid #afbdce; vertical-align:top;"><span style="color:#778593; font-style:italic; font-size:10px;">Belum dipilih</span></td></tr>
                        </tbody>
                    </table>
                    <h2>Capaian Pembelajaran</h2>
                    <p id="preview-achievement" style="margin:0 0 10px; white-space:pre-wrap;"></p>
                    <h2>Tujuan Pembelajaran</h2>
                    <p id="preview-objectives" style="margin:0 0 10px; white-space:pre-wrap;"></p>
                    <h2>Metode Pembelajaran</h2>
                    <p id="preview-methods" style="margin:0 0 10px; white-space:pre-wrap;"></p>
                    <h2>Praktik Pedagogis</h2>
                    <p id="preview-practice" style="margin:0 0 10px; white-space:pre-wrap;"></p>
                    <h2>Lingkungan Pembelajaran</h2>
                    <p id="preview-environment" style="margin:0 0 10px; white-space:pre-wrap;"></p>
                    <h2>Pemanfaatan Digital</h2>
                    <p id="preview-digital" style="margin:0 0 10px; white-space:pre-wrap;"></p>
                    <h2>Pengalaman Pembelajaran</h2>
                    <p id="preview-experience" style="margin:0 0 10px; white-space:pre-wrap;"></p>
                    <h2>Refleksi Pembelajaran</h2>
                    <p id="preview-reflection" style="margin:0 0 10px; white-space:pre-wrap;"></p>
                    <h2>Asesmen</h2>
                    <p id="preview-assessment" style="margin:0 0 10px; white-space:pre-wrap;"></p>
                </article>
            </div>
        </section>
    </main>
</div>

<script>
    @php
        $initialDataPayload = $module ?? [
            'title' => '',
            'subject' => 'Matematika',
            'class' => 'X',
            'status' => 'draft',
            'duration' => '2 JP (2 x 45 menit)',
            'achievement' => 'Tuliskan Capaian pembelajaran untuk masing-masing mapel berdasarkan Kep BSKAP 046/2025 (bagi mapel umum) dan Kep BPKM 020/2026 (bagi mapel PA dan Budi Pekerti)',
            'objectives' => 'Sebutkan Tujuan pembelajaran yang mengacu pada capaian pembelajaran',
            'methods' => 'Jelaskan metode dan Model pembelajaran yang akan digunakan.',
            'practice' => 'Jelaskan praktik pedagogis yang akan digunakan.',
            'environment' => 'Keterangan: Ruang fisik, Ruang Virtual, Budaya Belajar',
            'digital' => 'Keterangan: Tuliskan Sumber Belajar baik berupa buku sumber maupun elektronik',
            'experience' => 'Pendahuluan, Kegiatan Inti dan Kegiatan Penutup',
            'reflection' => 'Refleksi guru dan peserta didik.',
            'assessment' => 'Asesmen diagnostik, formatif, dan sumatif.',
            'dimensi_lulusan' => 'Belum dipilih',
        ];
    @endphp
    const initialData = {!! json_encode($initialDataPayload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) !!};
    const mode = @json($mode);
    const moduleId = @json($moduleId);
    const docInfo = @json($docInfo ?? null);

    const textFields = ['title','subject','class','status','duration','dimensi_lulusan','achievement','objectives','methods','practice','environment','digital','experience','reflection','assessment'];
    const listFields = [];

    function renderList(element, value) {
        element.replaceChildren();
        value.split('\n').map(item => item.trim()).filter(Boolean).forEach(item => {
            const li = document.createElement('li');
            const clean = item.replace(/^[•\-\d.]+\s*/, '');
            // if the line contains HTML tags (or encoded entities), render as HTML
            if (clean.includes('<') || clean.includes('&lt;')) {
                li.innerHTML = decodeHtmlEntities(clean);
            } else {
                li.textContent = clean;
            }
            element.appendChild(li);
        });
    }

    function decodeHtmlEntities(str) {
        if (!str || (typeof str !== 'string')) return '';
        // quick check: if it contains encoded entities like &lt; or &amp;, decode
        if (!str.includes('&lt;') && !str.includes('&amp;')) return str;
        const txt = document.createElement('textarea');
        txt.innerHTML = str;
        return txt.value;
    }

    // Initialize combobox for kelas
    function initClassCombobox() {
        const search = document.getElementById('input-class-search');
        const dropdown = document.getElementById('class-dropdown');
        const options = dropdown ? Array.from(dropdown.querySelectorAll('.class-option')).map(li => ({id: li.dataset.id || null, label: li.dataset.label || li.textContent.trim(), el: li})) : [];
        const container = document.getElementById('input-class-combobox');
        if (!search || !dropdown || !container) return;

        function showDropdown() { dropdown.style.display = 'block'; }
        function hideDropdown() { dropdown.style.display = 'none'; }

        search.addEventListener('focus', () => { renderDropdown(''); showDropdown(); });
        search.addEventListener('input', (e) => { renderDropdown(e.target.value || ''); showDropdown(); });
        // always show dropdown on click even if already focused
        search.addEventListener('click', (e) => { renderDropdown(search.value || ''); showDropdown(); e.stopPropagation(); });

        function renderDropdown(filter) {
            const q = (filter || '').toLowerCase().trim();
            dropdown.replaceChildren();
            const selectedLabels = Array.from(document.getElementById('class-chips').querySelectorAll('.chip')).map(c=>c.dataset.label);
            options.filter(o => !selectedLabels.includes(o.label))
                .filter(o => !q || o.label.toLowerCase().includes(q))
                .forEach(o => {
                    const li = document.createElement('li');
                    li.textContent = o.label;
                    if (o.id !== null) li.dataset.id = o.id;
                    li.dataset.label = o.label;
                    li.style.padding = '6px 8px';
                    li.style.cursor = 'pointer';
                    li.addEventListener('click', () => { addClassChip({id: o.id, label: o.label}); renderDropdown(search.value); });
                    dropdown.appendChild(li);
                });
            if (!dropdown.hasChildNodes()) {
                const li = document.createElement('li');
                li.textContent = 'Tidak ada hasil';
                li.style.padding = '6px 8px';
                li.style.color = '#666';
                dropdown.appendChild(li);
            }
        }

        document.addEventListener('click', (e) => {
            if (!container.contains(e.target)) hideDropdown();
        });

        // prefill from hidden input if present
        const hidden = document.getElementById('field-class');
        if (hidden && hidden.value) {
            renderClassChipsFromValue(hidden.value);
        }
    }

    // Initialize combobox for dimensi lulusan (same chip-style UI as Kelas)
    function initDimensiCombobox() {
        const search = document.getElementById('input-dimensi-search');
        const dropdown = document.getElementById('dimensi-dropdown');
        const options = dropdown ? Array.from(dropdown.querySelectorAll('.dimensi-option')).map(li => ({ label: li.dataset.label || li.textContent.trim() })) : [];
        const container = document.getElementById('input-dimensi-combobox');
        if (!search || !dropdown || !container) return;

        function showDropdown() { dropdown.style.display = 'block'; }
        function hideDropdown() { dropdown.style.display = 'none'; }

        search.addEventListener('focus', () => { renderDimensiDropdown(''); showDropdown(); });
        search.addEventListener('input', (e) => { renderDimensiDropdown(e.target.value || ''); showDropdown(); });
        search.addEventListener('click', (e) => { renderDimensiDropdown(search.value || ''); showDropdown(); e.stopPropagation(); });

        function renderDimensiDropdown(filter) {
            const q = (filter || '').toLowerCase().trim();
            dropdown.replaceChildren();
            const selectedLabels = Array.from(document.getElementById('dimensi-chips').querySelectorAll('.chip')).map(c => c.dataset.label);
            options.filter(o => !selectedLabels.includes(o.label))
                .filter(o => !q || o.label.toLowerCase().includes(q))
                .forEach(o => {
                    const li = document.createElement('li');
                    li.textContent = o.label;
                    li.dataset.label = o.label;
                    li.style.padding = '6px 8px';
                    li.style.cursor = 'pointer';
                    li.addEventListener('click', () => { addDimensiChip(o.label); renderDimensiDropdown(search.value); });
                    dropdown.appendChild(li);
                });
            if (!dropdown.hasChildNodes()) {
                const li = document.createElement('li');
                li.textContent = 'Tidak ada hasil';
                li.style.padding = '6px 8px';
                li.style.color = '#666';
                dropdown.appendChild(li);
            }
        }

        document.addEventListener('click', (e) => {
            if (!container.contains(e.target)) hideDropdown();
        });

        const hidden = document.getElementById('field-dimensi_lulusan');
        if (hidden && hidden.value) {
            renderDimensiChipsFromValue(hidden.value);
        }
    }

    function renderDimensiChipsFromValue(value) {
        const container = document.getElementById('dimensi-chips');
        const hidden = document.getElementById('field-dimensi_lulusan');
        if (!container) return;
        container.replaceChildren();
        const sep = ('' + (value || '')).includes('\n') ? '\n' : ',';
        const parts = ('' + (value || '')).split(sep).map(s => s.trim()).filter(Boolean);
        parts.forEach(v => addDimensiChip(v, false));
        if (hidden) hidden.value = parts.join('\n');
    }

    function addDimensiChip(label, focusSearch = true) {
        const container = document.getElementById('dimensi-chips');
        const hidden = document.getElementById('field-dimensi_lulusan');
        if (!container) return;
        const existing = Array.from(container.querySelectorAll('.chip')).map(c => c.dataset.label);
        if (existing.includes(label)) return;
        const chip = document.createElement('span');
        chip.className = 'chip';
        chip.dataset.label = label;
        chip.style.padding = '6px 8px';
        chip.style.background = '#eef5fc';
        chip.style.borderRadius = '18px';
        chip.style.display = 'inline-flex';
        chip.style.alignItems = 'center';
        chip.style.gap = '8px';
        chip.textContent = label;
        const btn = document.createElement('button');
        btn.type = 'button';
        btn.textContent = '×';
        btn.style.border = '0';
        btn.style.background = 'transparent';
        btn.style.cursor = 'pointer';
        btn.addEventListener('click', () => {
            chip.remove();
            const labels = Array.from(container.querySelectorAll('.chip')).map(c => c.dataset.label);
            if (hidden) hidden.value = labels.join('\n');
            updatePreview('dimensi_lulusan');
        });
        chip.appendChild(btn);
        container.appendChild(chip);
        const labels = Array.from(container.querySelectorAll('.chip')).map(c => c.dataset.label);
        if (hidden) hidden.value = labels.join('\n');
        updatePreview('dimensi_lulusan');
        const searchEl = document.getElementById('input-dimensi-search');
        if (focusSearch) {
            searchEl?.focus();
            if (searchEl) { searchEl.value = ''; }
        }
    }

    function getFieldValue(field) {
        const input = document.getElementById('input-' + field);
        const hidden = document.getElementById('field-' + field);
        if (!input) return hidden ? hidden.value || '' : '';
        if (input.isContentEditable) {
            return (hidden ? hidden.value : input.innerText).replace(/\u00A0/g, ' ');
        }
        return (hidden ? hidden.value : input.value) || '';
    }

    function setFieldValue(field, value) {
        const input = document.getElementById('input-' + field);
        const hidden = document.getElementById('field-' + field);
        const safeValue = value || '';
        if (hidden) hidden.value = safeValue;
        if (!input) return;
        if (input.tagName === 'SELECT' && input.multiple) {
            const sep = (safeValue.includes('\n')) ? '\n' : ',';
            const parts = ('' + safeValue).split(sep).map(s => s.trim()).filter(Boolean);
            Array.from(input.options).forEach(opt => {
                opt.selected = parts.includes(opt.value);
            });
        } else if (summernoteEditors[field]) {
            try {
                summernoteEditors[field].summernote('code', safeValue);
            } catch (e) {
                summernoteEditors[field].text(safeValue);
            }
        } else if (input.isContentEditable) {
            input.innerHTML = safeValue.replace(/\n/g, '<br>');
        } else {
            input.value = safeValue;
        }
    }

    // helper to render chips for combobox from comma-separated value
    function renderClassChipsFromValue(csv) {
        const container = document.getElementById('class-chips');
        const hidden = document.getElementById('field-class');
        const hiddenId = document.getElementById('field-kelas_id');
        if (!container) return;
        container.replaceChildren();
        const parts = ('' + (csv || '')).split(',').map(s => s.trim()).filter(Boolean);
        parts.forEach(v => addClassChip({ id: null, label: v }, false));
        if (hidden) hidden.value = parts.join(', ');
        if (hiddenId) hiddenId.value = '';
    }

    function addClassChip(value, focusSearch = true) {
        const container = document.getElementById('class-chips');
        const hidden = document.getElementById('field-class');
        const hiddenId = document.getElementById('field-kelas_id');
        if (!container) return;
        const item = (typeof value === 'object' && value !== null) ? value : { id: null, label: String(value) };
        const label = item.label || String(item.id || '');
        const id = item.id || null;
        // avoid duplicates
        const existing = Array.from(container.querySelectorAll('.chip')).map(c => c.dataset.label);
        if (existing.includes(label)) return;
        const chip = document.createElement('span');
        chip.className = 'chip';
        if (id !== null) chip.dataset.id = id;
        chip.dataset.label = label;
        chip.style.padding = '6px 8px';
        chip.style.background = '#eef5fc';
        chip.style.borderRadius = '18px';
        chip.style.display = 'inline-flex';
        chip.style.alignItems = 'center';
        chip.style.gap = '8px';
        chip.textContent = label;
        const btn = document.createElement('button');
        btn.type = 'button';
        btn.textContent = '×';
        btn.style.border = '0';
        btn.style.background = 'transparent';
        btn.style.cursor = 'pointer';
        btn.addEventListener('click', () => {
            chip.remove();
            const labels = Array.from(container.querySelectorAll('.chip')).map(c => c.dataset.label);
            if (hidden) hidden.value = labels.join(', ');
            if (hiddenId) {
                const ids = Array.from(container.querySelectorAll('.chip')).map(c => c.dataset.id).filter(Boolean);
                hiddenId.value = ids.length ? ids[0] : '';
            }
            const preview = document.getElementById('preview-class');
            if (preview) preview.textContent = hidden.value + (document.getElementById('field-fase')?.value ? ' / Fase ' + document.getElementById('field-fase').value : '');
        });
        chip.appendChild(btn);
        container.appendChild(chip);
        const labels = Array.from(container.querySelectorAll('.chip')).map(c => c.dataset.label);
        if (hidden) hidden.value = labels.join(', ');
        if (hiddenId) {
            const ids = Array.from(container.querySelectorAll('.chip')).map(c => c.dataset.id).filter(Boolean);
            hiddenId.value = ids.length ? ids[0] : '';
        }
        const preview = document.getElementById('preview-class');
        if (preview) preview.textContent = hidden.value + (document.getElementById('field-fase')?.value ? ' / Fase ' + document.getElementById('field-fase').value : '');
        const searchEl = document.getElementById('input-class-search');
        if (focusSearch) {
            searchEl?.focus();
            if (searchEl) { searchEl.value = ''; }
        }
        // refresh dropdown after adding
        if (typeof renderDropdown === 'function') {
            try { renderDropdown(searchEl ? searchEl.value : ''); } catch(e) {}
        }
    }

    // Summernote editor instances map
    const summernoteEditors = {};

    function initSummernoteEditors() {
        document.querySelectorAll('[data-editor-type="block"]').forEach(el => {
            const id = el.id;
            const field = id.replace(/^input-/, '');
            const $editor = $(el);

            $editor.summernote({
                placeholder: 'Tempel teks di sini atau tulis deskripsi...',
                toolbar: [
                    ['style', ['bold', 'italic', 'underline', 'strikethrough', 'clear']],
                    ['font', ['fontname', 'fontsize']],
                    ['color', ['color']],
                    ['para', ['ul', 'ol', 'paragraph', 'height']],
                    ['insert', ['link', 'picture', 'video', 'table', 'hr']],
                    ['view', ['codeview', 'help']]
                ],
                callbacks: {
                    onImageUpload: function(files) {
                        if (!files || !files.length) return;
                        const reader = new FileReader();
                        reader.onload = function(e) {
                            const imgHtml = '<img src="' + e.target.result + '" style="max-width:100%;height:auto;" />';
                            $editor.summernote('pasteHTML', imgHtml);
                        };
                        reader.readAsDataURL(files[0]);
                    },
                    onChange: function(contents, $editable) {
                        const hidden = document.getElementById('field-' + field);
                        if (hidden) hidden.value = contents.trim();
                        updatePreview(field);
                    }
                },
                height: 140
            });

            summernoteEditors[field] = $editor;
            const hidden = document.getElementById('field-' + field);
            if (hidden && hidden.value) {
                $editor.summernote('code', hidden.value);
            }
        });
    }

    // Sync select inputs to hidden fields and preview
    function initSelectSync() {
        const subjectSelect = document.getElementById('input-subject');
        const classSelect = document.getElementById('input-class');
        const faseSelect = document.getElementById('input-fase');
        const statusSelect = document.getElementById('input-status');

        if (subjectSelect && subjectSelect.tagName === 'SELECT') {
            subjectSelect.addEventListener('change', () => {
                const hidden = document.getElementById('field-subject');
                const hiddenId = document.getElementById('field-mata_pelajaran_id');
                const selectedOption = subjectSelect.selectedOptions[0];
                if (hidden) hidden.value = selectedOption ? selectedOption.textContent : '';
                if (hiddenId) hiddenId.value = selectedOption ? selectedOption.value : '';
                const preview = document.getElementById('preview-subject');
                if (preview) preview.textContent = selectedOption ? selectedOption.textContent : '';
            });
        }

        // class combobox handled separately
        if (classSelect && classSelect.tagName === 'SELECT') {
            classSelect.addEventListener('change', () => {
                const hidden = document.getElementById('field-class');
                if (hidden) {
                    const values = Array.from(classSelect.selectedOptions).map(o => o.value);
                    hidden.value = values.join(', ');
                }
                const preview = document.getElementById('preview-class');
                if (preview) preview.textContent = Array.from(classSelect.selectedOptions).map(o => o.value).join(', ');
            });
        }

        if (faseSelect && faseSelect.tagName === 'SELECT') {
            faseSelect.addEventListener('change', () => {
                const hidden = document.getElementById('field-fase');
                if (hidden) hidden.value = faseSelect.value;
                const preview = document.getElementById('preview-class');
                if (preview) {
                    // include fase in preview-class if present
                    const existing = document.getElementById('field-class')?.value || '';
                    preview.textContent = existing + (existing && faseSelect.value ? ' / Fase ' + faseSelect.value : (faseSelect.value ? 'Fase ' + faseSelect.value : ''));
                }
            });
        }

        if (statusSelect && statusSelect.tagName === 'SELECT') {
            statusSelect.addEventListener('change', () => {
                const hidden = document.getElementById('field-status');
                if (hidden) hidden.value = statusSelect.value;
                const preview = document.getElementById('preview-status');
                if (preview) {
                    preview.textContent = statusSelect.value === 'published'
                        ? 'Publish (Digunakan untuk KBM)'
                        : 'Draft (Belum digunakan untuk KBM)';
                }
            });
        }
    }

    function initInputSync() {
        const fields = [
            { id: 'input-title', field: 'title' },
            { id: 'input-duration', field: 'duration' },
        ];

        fields.forEach(({ id, field }) => {
            const input = document.getElementById(id);
            if (!input) return;
            input.addEventListener('input', () => {
                const hidden = document.getElementById('field-' + field);
                if (hidden) hidden.value = input.value || '';
                updatePreview(field);
            });
        });

        const subjectInput = document.getElementById('input-subject');
        if (subjectInput && subjectInput.isContentEditable) {
            subjectInput.addEventListener('input', () => {
                const hidden = document.getElementById('field-subject');
                if (hidden) hidden.value = subjectInput.innerText.replace(/\u00A0/g, ' ').trim();
                const preview = document.getElementById('preview-subject');
                if (preview) preview.textContent = hidden.value;
            });
        }

        const classInput = document.getElementById('input-class');
        if (classInput && classInput.isContentEditable) {
            classInput.addEventListener('input', () => {
                const hidden = document.getElementById('field-class');
                if (hidden) hidden.value = classInput.innerText.replace(/\u00A0/g, ' ').trim();
                const preview = document.getElementById('preview-class');
                if (preview) preview.textContent = hidden.value + (document.getElementById('field-fase')?.value ? ' / Fase ' + document.getElementById('field-fase').value : '');
            });
        }

        const faseInput = document.getElementById('input-fase');
        if (faseInput && faseInput.tagName === 'INPUT') {
            faseInput.addEventListener('input', () => {
                const hidden = document.getElementById('field-fase');
                if (hidden) hidden.value = faseInput.value;
                const preview = document.getElementById('preview-class');
                if (preview) {
                    const existing = document.getElementById('field-class')?.value || '';
                    preview.textContent = existing + (existing && faseInput.value ? ' / Fase ' + faseInput.value : (faseInput.value ? 'Fase ' + faseInput.value : ''));
                }
            });
        }
    }

    function updateProgress() {
        const complete = textFields.filter(field => getFieldValue(field).trim()).length;
        const progressValue = document.getElementById('progress-value');
        const progressFill = document.getElementById('progress-fill');
        if (progressValue) progressValue.textContent = complete + ' / ' + textFields.length;
        if (progressFill) progressFill.style.width = (complete / textFields.length * 100) + '%';
    }

    function updatePreview(field) {
        const output = document.getElementById('preview-' + field);
        if (!output) return;
        const value = getFieldValue(field);
        if (field === 'status') {
            output.textContent = value === 'published'
                ? 'Publish (Digunakan untuk KBM)'
                : 'Draft (Belum digunakan untuk KBM)';
        } else if (field === 'dimensi_lulusan') {
            const sep = value.includes('\n') ? '\n' : ',';
            const parts = value.split(sep).map(s => s.trim()).filter(Boolean);
            const itemSep = '<hr style="margin:4px 0; border:0; border-top:1px dashed #c9d7e7;">';
            output.innerHTML = parts.length > 0 ? parts.join(itemSep) : '<span style="color:#778593; font-style:italic; font-size:10px;">Belum dipilih</span>';
        } else if (listFields.includes(field)) {
            renderList(output, value);
        } else if (summernoteEditors[field]) {
            // render rich HTML; decode entities if present
            const decoded = decodeHtmlEntities(value || '');
            output.innerHTML = decoded;
        } else {
            output.textContent = value;
        }
        updateProgress();
    }

    function populateForm(data) {
        textFields.forEach(field => {
            setFieldValue(field, data[field] || '');
            updatePreview(field);
        });
    }

    function formatBytes(bytes) {
        if (!bytes || bytes <= 0) return '0 B';
        const units = ['B', 'KB', 'MB', 'GB'];
        let i = 0;
        let num = Number(bytes);
        while (num >= 1024 && i < units.length - 1) {
            num /= 1024;
            i += 1;
        }
        return num.toFixed(num >= 10 || i === 0 ? 0 : 1) + ' ' + units[i];
    }

    function formatDateTime(value) {
        if (!value) return '-';
        const dt = new Date(value);
        if (Number.isNaN(dt.getTime())) return value;
        return dt.toLocaleString('id-ID', {
            year: 'numeric', month: 'long', day: '2-digit',
            hour: '2-digit', minute: '2-digit'
        });
    }

    function setDocMeta(documentData) {
        const statusLine = document.getElementById('doc-status-line');
        const detailLine = document.getElementById('doc-detail-line');
        const btnPreview = document.getElementById('btn-preview-collabora');
        const btnEdit = document.getElementById('btn-edit-collabora');
        const btnVersions = document.getElementById('btn-refresh-versions');

        if (!statusLine || !detailLine) return;

        if (!documentData) {
            statusLine.textContent = 'Belum ada dokumen Modul Ajar';
            detailLine.textContent = 'Status: Belum ada dokumen';
            if (btnPreview) btnPreview.disabled = true;
            if (btnEdit) btnEdit.disabled = true;
            if (btnVersions) btnVersions.disabled = true;
            return;
        }

        statusLine.textContent = 'Dokumen: ' + (documentData.original_filename || documentData.filename || '-');
        detailLine.textContent = 'Versi: ' + (documentData.version || '-')
            + ' | Ukuran: ' + formatBytes(documentData.file_size || 0)
            + ' | Terakhir diperbarui: ' + formatDateTime(documentData.updated_at || null)
            + ' | Status: Versi terbaru';

        if (btnPreview) btnPreview.disabled = false;
        if (btnEdit) btnEdit.disabled = false;
        if (btnVersions) btnVersions.disabled = false;
    }

    async function openCollabora(modeToOpen = 'preview') {
        const viewerWrap = document.getElementById('doc-viewer-wrap');
        const htmlPreview = document.getElementById('html-preview-wrap');
        const loading = document.getElementById('doc-loading');
        const iframe = document.getElementById('collabora-frame');
        const error = document.getElementById('doc-error');

        if (!viewerWrap || !htmlPreview || !loading || !iframe || !error || !moduleId) {
            return;
        }

        viewerWrap.style.display = 'block';
        htmlPreview.style.display = 'none';
        loading.style.display = 'block';
        error.style.display = 'none';
        iframe.style.display = 'none';
        iframe.removeAttribute('src');

        try {
            const url = '{{ url('modul-ajar') }}/' + moduleId + '/preview?mode=' + (modeToOpen === 'edit' ? 'edit' : 'preview');
            const res = await fetch(url, { headers: { 'Accept': 'application/json' } });
            const json = await res.json();
            if (!res.ok || !json.success || !json.wopiUrl) {
                throw new Error(json.message || 'Dokumen tidak dapat dibuka.');
            }
            iframe.src = json.wopiUrl;
            iframe.style.display = 'block';
            loading.style.display = 'none';
        } catch (err) {
            loading.style.display = 'none';
            error.style.display = 'block';
            console.error(err);
        }
    }

    document.addEventListener('DOMContentLoaded', () => {
        populateForm(initialData);

        const subjectSelect = document.getElementById('input-subject');
        if (subjectSelect && subjectSelect.tagName === 'SELECT' && initialData.mata_pelajaran_id) {
            subjectSelect.value = String(initialData.mata_pelajaran_id);
            const selected = subjectSelect.selectedOptions[0];
            const hiddenSubject = document.getElementById('field-subject');
            const hiddenSubjectId = document.getElementById('field-mata_pelajaran_id');
            if (hiddenSubject && selected) hiddenSubject.value = selected.textContent.trim();
            if (hiddenSubjectId) hiddenSubjectId.value = String(initialData.mata_pelajaran_id);
        }

        if (initialData.kelas_id && initialData.class) {
            addClassChip({ id: String(initialData.kelas_id), label: String(initialData.class) }, false);
            const hiddenClassId = document.getElementById('field-kelas_id');
            if (hiddenClassId) hiddenClassId.value = String(initialData.kelas_id);
        }

        if (initialData.status) {
            const statusSelect = document.getElementById('input-status');
            const statusHidden = document.getElementById('field-status');
            if (statusSelect && statusSelect.tagName === 'SELECT') statusSelect.value = initialData.status;
            if (statusHidden) statusHidden.value = initialData.status;
        }

        setDocMeta(docInfo);

        // initialize Summernote editors on fields
        if (typeof $.fn.summernote !== 'undefined') {
            initSummernoteEditors();
            textFields.forEach(field => updatePreview(field));
        } else {
            console.error('Summernote tidak ditemukan. Pastikan summernote-lite.min.js dimuat.');
        }

        // initialize select syncs
        initSelectSync();
        // initialize class combobox if present
        initClassCombobox();
        // initialize dimensi lulusan combobox if present
        initDimensiCombobox();
        // initialize text input syncs
        initInputSync();

        // section toggle (collapse/expand) behavior
        document.querySelectorAll('.section-toggle').forEach(button => {
            button.addEventListener('click', () => {
                const section = button.closest('.form-section');
                if (!section) return;
                const content = section.querySelector('.section-content');
                const closed = section.classList.toggle('closed');
                button.setAttribute('aria-expanded', String(!closed));
                if (content) content.style.display = closed ? 'none' : 'block';
                const icon = button.querySelector('i');
                if (icon) {
                    if (icon.classList.contains('ti-chevron-down')) {
                        icon.classList.remove('ti-chevron-down');
                        icon.classList.add('ti-chevron-up');
                    } else if (icon.classList.contains('ti-chevron-up')) {
                        icon.classList.remove('ti-chevron-up');
                        icon.classList.add('ti-chevron-down');
                    } else {
                        // fallback: toggle rotate
                        icon.style.transform = closed ? 'rotate(-90deg)' : 'rotate(0deg)';
                    }
                }
            });
        });

        const form = document.getElementById('rpp-form');
        if (form) {
            form.addEventListener('submit', () => {
                textFields.forEach(field => {
                    const hidden = document.getElementById('field-' + field);
                    if (!hidden) return;
                    if (summernoteEditors[field]) {
                        try {
                            hidden.value = summernoteEditors[field].summernote('code').replace(/\u00A0/g, ' ');
                        } catch (e) {
                            hidden.value = summernoteEditors[field].text().replace(/\u00A0/g, ' ');
                        }
                    } else {
                        const editor = document.getElementById('input-' + field);
                        if (editor) {
                            if (editor.isContentEditable) {
                                hidden.value = editor.innerText.replace(/\u00A0/g, ' ');
                            } else {
                                hidden.value = editor.value || '';
                            }
                        }
                    }
                });

                const subjectSelect = document.getElementById('input-subject');
                const subjectHidden = document.getElementById('field-subject');
                const subjectIdHidden = document.getElementById('field-mata_pelajaran_id');
                if (subjectSelect && subjectSelect.tagName === 'SELECT' && subjectSelect.selectedOptions.length > 0) {
                    const selected = subjectSelect.selectedOptions[0];
                    if (subjectHidden) subjectHidden.value = selected.textContent.trim();
                    if (subjectIdHidden) subjectIdHidden.value = selected.value;
                }

                const classHidden = document.getElementById('field-class');
                const classIdHidden = document.getElementById('field-kelas_id');
                const chips = document.getElementById('class-chips');
                if (chips) {
                    const labels = Array.from(chips.querySelectorAll('.chip')).map(c => c.dataset.label || '');
                    if (classHidden) classHidden.value = labels.join(', ');
                    if (classIdHidden) {
                        const ids = Array.from(chips.querySelectorAll('.chip')).map(c => c.dataset.id).filter(Boolean);
                        classIdHidden.value = ids.length ? ids[0] : '';
                    }
                }
            });
        }

        const btnImport = document.getElementById('btn-import-word');
        const importSection = document.getElementById('import-section');
        const btnCloseImport = document.getElementById('btn-close-import');
        const btnProcess = document.getElementById('btn-process-import');
        const importFile = document.getElementById('import-file');
        const importStatus = document.getElementById('import-status');

        if (btnImport && importSection) {
            btnImport.addEventListener('click', () => {
                importSection.style.display = 'block';
            });
        }

        if (btnCloseImport && importSection) {
            btnCloseImport.addEventListener('click', () => {
                importSection.style.display = 'none';
                importStatus.textContent = '';
            });
        }

        if (btnProcess && importFile) {
            btnProcess.addEventListener('click', async () => {
                const file = importFile.files[0];
                if (!file) {
                    importStatus.textContent = 'Pilih file Word (.docx) terlebih dahulu.';
                    importStatus.style.color = '#c00';
                    return;
                }

                const formData = new FormData();
                formData.append('file', file);
                formData.append('_token', document.querySelector('meta[name=csrf-token]')?.content || '{{ csrf_token() }}');

                btnProcess.disabled = true;
                importStatus.textContent = 'Mengimpor...';
                importStatus.style.color = '#24496f';

                try {
                    const response = await fetch('{{ route('rencana_pembelajaran.import') }}', {
                        method: 'POST',
                        body: formData,
                        headers: {
                            'Accept': 'application/json',
                        }
                    });

                    const result = await response.json();
                    if (!result.success) {
                        throw new Error(result.message || 'Import gagal');
                    }

                    const fieldMap = {
                        achievement: 'achievement',
                        objectives: 'objectives',
                        methods: 'methods',
                        practice: 'practice',
                        environment: 'environment',
                        digital: 'digital',
                        experience: 'experience',
                        reflection: 'reflection',
                        assessment: 'assessment'
                    };

                    Object.keys(fieldMap).forEach(serverKey => {
                        const field = fieldMap[serverKey];
                        const value = result[serverKey] || '';
                        if (!value) return;

                        const input = document.getElementById('input-' + field);
                        const hidden = document.getElementById('field-' + field);
                        if (hidden) hidden.value = value;
                        if (input) {
                            if (input.isContentEditable) {
                                input.innerHTML = value.replace(/\n/g, '<br>');
                            } else if (summernoteEditors[field]) {
                                try {
                                    summernoteEditors[field].summernote('code', value);
                                } catch (e) {
                                    summernoteEditors[field].text(value);
                                }
                            } else {
                                input.value = value;
                            }
                        }
                        updatePreview(field);
                    });

                    const resultPlaceholders = (result.placeholders && result.placeholders.length) ? result.placeholders : [];
                    if (resultPlaceholders.length > 0) {
                        importStatus.textContent = 'Import berhasil. ' + resultPlaceholders.length + ' placeholder ditemukan. Gunakan tombol Sisipkan untuk menempatkan gambar atau tabel ke field tujuan.';
                        const list = document.createElement('div');
                        list.style.marginTop = '8px';
                        resultPlaceholders.forEach((item, idx) => {
                            const row = document.createElement('div');
                            row.style.display = 'inline-flex';
                            row.style.alignItems = 'center';
                            row.style.gap = '8px';
                            row.style.marginRight = '10px';
                            row.style.marginTop = '4px';
                            row.style.flexWrap = 'wrap';

                            const label = document.createElement('span');
                            label.textContent = (item.type === 'image' ? 'Gambar' : 'Tabel') + ' #' + (idx + 1) + (item.caption ? ' (' + item.caption + ')' : '') + ' →';
                            label.style.fontSize = '11px';
                            row.appendChild(label);

                            const select = document.createElement('select');
                            select.style.fontSize = '11px';
                            select.innerHTML = '<option value="">-- Pilih field --</option>' +
                                '<option value="achievement">Capaian Pembelajaran</option>' +
                                '<option value="objectives">Tujuan Pembelajaran</option>' +
                                '<option value="practice">Praktik Pedagogis</option>' +
                                '<option value="environment">Lingkungan Pembelajaran</option>' +
                                '<option value="digital">Pemanfaatan Digital</option>' +
                                '<option value="experience">Pengalaman Pembelajaran</option>' +
                                '<option value="reflection">Refleksi Pembelajaran</option>' +
                                '<option value="assessment">Asesmen</option>';
                            row.appendChild(select);

                            const btn = document.createElement('button');
                            btn.type = 'button';
                            btn.textContent = 'Sisipkan';
                            btn.className = 'btn btn-sm btn-outline-primary';
                            btn.addEventListener('click', () => {
                                const field = select.value;
                                if (!field || !summernoteEditors[field]) {
                                    alert('Pilih field tujuan terlebih dahulu');
                                    return;
                                }
                                const editor = summernoteEditors[field];
                                if (item.type === 'image') {
                                    try {
                                        const imgHtml = '<img src="' + item.url + '" style="max-width:100%;height:auto;" />';
                                        editor.summernote('pasteHTML', imgHtml);
                                    } catch (e) {
                                        alert('Gagal sisipkan gambar: ' + e.message);
                                    }
                                } else if (item.type === 'table') {
                                    const html = '<table style="width:100%;border-collapse:collapse;"><tr><th style="border:1px solid #afbdce;padding:8px;background:#eef5fc;">' + item.caption + '</th><td style="border:1px solid #afbdce;padding:8px;">[isi tabel]</td></tr></table>';
                                    try {
                                        editor.summernote('pasteHTML', html);
                                    } catch (e) {
                                        alert('Gagal sisipkan tabel: ' + e.message);
                                    }
                                }
                                updatePreview(field);
                            });
                            row.appendChild(btn);
                            list.appendChild(row);
                        });
                        importStatus.parentNode.insertBefore(list, importStatus.nextSibling);
                    }
                    importStatus.style.color = '#1e8e3e';
                    if (resultPlaceholders.length === 0) {
                        importSection.style.display = 'none';
                    }
                } catch (e) {
                    importStatus.textContent = 'Gagal import: ' + e.message;
                    importStatus.style.color = '#c00';
                } finally {
                    btnProcess.disabled = false;
                }
            });
        }

        if (mode === 'edit' && moduleId) {
            const uploadInput = document.getElementById('document-upload-input');
            const btnUpload = document.getElementById('btn-upload-document');
            const btnPreview = document.getElementById('btn-preview-collabora');
            const btnEdit = document.getElementById('btn-edit-collabora');
            const btnRetry = document.getElementById('btn-retry-doc');
            const btnVersions = document.getElementById('btn-refresh-versions');

            if (btnUpload && uploadInput) {
                btnUpload.addEventListener('click', () => uploadInput.click());
                uploadInput.addEventListener('change', async () => {
                    const file = uploadInput.files && uploadInput.files[0] ? uploadInput.files[0] : null;
                    if (!file) return;

                    const fd = new FormData();
                    fd.append('document', file);
                    fd.append('_token', '{{ csrf_token() }}');

                    btnUpload.disabled = true;
                    const oldLabel = btnUpload.textContent;
                    btnUpload.textContent = 'Mengupload...';

                    try {
                        const res = await fetch('{{ url('modul-ajar') }}/' + moduleId + '/upload', {
                            method: 'POST',
                            body: fd,
                            headers: { 'Accept': 'application/json' },
                        });
                        const json = await res.json();
                        if (!res.ok || !json.success) {
                            throw new Error(json.message || 'Upload gagal');
                        }
                        setDocMeta(json.document || null);
                        window.alert('Dokumen berhasil diupload');
                        await openCollabora('preview');
                    } catch (err) {
                        console.error(err);
                        window.alert(err.message || 'Upload dokumen gagal.');
                    } finally {
                        btnUpload.disabled = false;
                        btnUpload.textContent = oldLabel;
                        uploadInput.value = '';
                    }
                });
            }

            if (btnPreview) {
                btnPreview.addEventListener('click', () => openCollabora('preview'));
            }
            if (btnEdit) {
                btnEdit.addEventListener('click', async () => {
                    try {
                        const res = await fetch('{{ url('modul-ajar') }}/' + moduleId + '/save-preview-docx', {
                            method: 'POST',
                            headers: {
                                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                'Accept': 'application/json',
                            }
                        });
                        const json = await res.json();
                        if (!res.ok || !json.tempKey) {
                            throw new Error(json.error || 'Gagal menyimpan preview');
                        }
                        window.location.href = '{{ url('akademik/editor-modul') }}/' + moduleId;
                    } catch (err) {
                        console.error(err);
                        window.alert(err.message || 'Gagal membuka editor.');
                    }
                });
            }
            if (btnRetry) {
                btnRetry.addEventListener('click', () => openCollabora('preview'));
            }

            if (btnVersions) {
                btnVersions.addEventListener('click', async () => {
                    try {
                        const res = await fetch('{{ url('modul-ajar') }}/' + moduleId + '/versions', {
                            headers: { 'Accept': 'application/json' }
                        });
                        const json = await res.json();
                        if (!res.ok || !json.success) {
                            throw new Error('Gagal mengambil riwayat versi.');
                        }
                        const versions = Array.isArray(json.versions) ? json.versions : [];
                        if (!versions.length) {
                            window.alert('Belum ada riwayat versi.');
                            return;
                        }

                        const lines = versions.map(v => 'Versi ' + v.version + ' | ' + formatDateTime(v.created_at) + ' | ' + (v.filename || '-'));
                        const chosen = window.prompt('Riwayat Versi:\n' + lines.join('\n') + '\n\nMasukkan nomor versi untuk restore (kosongkan untuk batal):');
                        if (!chosen) return;
                        const versionNo = Number(chosen);
                        if (!Number.isInteger(versionNo)) {
                            window.alert('Nomor versi tidak valid.');
                            return;
                        }
                        const ok = window.confirm('Restore versi ' + versionNo + '? Versi saat ini akan tetap tersimpan sebagai riwayat.');
                        if (!ok) return;

                        const restoreRes = await fetch('{{ url('modul-ajar') }}/' + moduleId + '/versions/' + versionNo + '/restore', {
                            method: 'POST',
                            headers: {
                                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                'Accept': 'application/json',
                            }
                        });
                        const restoreJson = await restoreRes.json();
                        if (!restoreRes.ok || !restoreJson.success) {
                            throw new Error(restoreJson.message || 'Restore gagal');
                        }

                        setDocMeta(restoreJson.document || docInfo);
                        window.alert('Versi berhasil di-restore.');
                        await openCollabora('preview');
                    } catch (err) {
                        console.error(err);
                        window.alert(err.message || 'Gagal memproses riwayat versi.');
                    }
                });
            }
        }
    });
</script>
@endsection
