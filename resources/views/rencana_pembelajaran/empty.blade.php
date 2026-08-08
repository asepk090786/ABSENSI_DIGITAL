@extends('layouts.app', ['pageSlug' => 'modul_ajar'])

@section('title', 'Modul Ajar')

@section('content')
<div class="app-shell" style="min-height: calc(100vh - 140px); background: #dfe7f0; border-radius: 16px; overflow: hidden; box-shadow: 0 4px 16px rgba(0,0,0,.08);">
    <header class="app-header" style="padding: 14px 20px; display:flex; align-items:center; gap:14px; background: linear-gradient(115deg, #174780, #2d6ab6); color:#fff;">
        <div style="width: 42px; height: 42px; display:grid; place-items:center; border-radius: 10px; background:#fff; color:#235c9f; font-weight:700; font-size: 20px;">M</div>
        <div>
            <div style="font-weight:700; font-size:15px;">Modul Ajar</div>
            <div style="font-size:12px; opacity:.9;">Form isi dan pratinjau modul ajar</div>
        </div>
        <nav style="margin-left:auto; display:flex; gap:6px; flex-wrap:wrap;">
            <button id="view-button" type="button" class="btn btn-sm btn-light text-primary" style="background: rgba(255,255,255,.15); color:#fff; border:1px solid transparent;">View</button>
            <button id="edit-button" type="button" class="btn btn-sm btn-light text-primary active" style="background:#fff; color:#1f5ca3; border:1px solid transparent;">Edit</button>
            <button id="create-button" type="button" class="btn btn-sm btn-light text-primary" style="background: rgba(255,255,255,.15); color:#fff; border:1px solid transparent;">Baru</button>
            <button id="download-button" type="button" class="btn btn-sm btn-light text-primary" style="background: rgba(255,255,255,.15); color:#fff; border:1px solid transparent;">Unduh</button>
        </nav>
        <div id="header-status" style="font-size:12px; min-width: 140px; text-align:right; opacity:.95;"></div>
    </header>

    <main id="workspace" class="workspace" style="display:grid; grid-template-columns:minmax(320px,410px) minmax(500px,1fr); min-height:0;">
        <aside class="editor-side" style="overflow:auto; padding:22px 18px 36px; background:#f7f9fc; border-right:1px solid #cad7e6;">
            <div style="max-width:390px; margin:0 auto;">
                <div style="margin-bottom:14px; padding:14px; border:1px solid #d8e3f0; border-radius:12px; background:#fff; box-shadow:0 5px 16px rgba(32,73,119,.05);">
                    <div style="display:flex; align-items:center; justify-content:space-between; gap:10px; margin-bottom:10px;">
                        <div>
                            <div style="font-size:13px; font-weight:700; color:#264b6f;">Daftar Modul Ajar</div>
                            <div style="font-size:11px; color:#6b7b8d;">Data modul ajar yang tersedia</div>
                        </div>
                        <button type="button" class="btn btn-sm btn-primary" style="padding:6px 10px; font-size:11px;">+ Tambah</button>
                    </div>
                    <div style="overflow-x:auto;">
                        <table style="width:100%; border-collapse:collapse; font-size:12px;">
                            <thead>
                                <tr style="background:#f3f7fb; color:#42607a;">
                                    <th style="padding:8px; text-align:left;">No</th>
                                    <th style="padding:8px; text-align:left;">Nama Modul Ajar</th>
                                    <th style="padding:8px; text-align:left;">Kelas</th>
                                    <th style="padding:8px; text-align:left;">Alokasi Waktu</th>
                                    <th style="padding:8px; text-align:left;">File</th>
                                    <th style="padding:8px; text-align:left;">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td style="padding:8px; border-top:1px solid #e6edf5;">1</td>
                                    <td style="padding:8px; border-top:1px solid #e6edf5;">Eksponen dan Logaritma</td>
                                    <td style="padding:8px; border-top:1px solid #e6edf5;">X</td>
                                    <td style="padding:8px; border-top:1px solid #e6edf5;">2 JP</td>
                                    <td style="padding:8px; border-top:1px solid #e6edf5;">PDF</td>
                                    <td style="padding:8px; border-top:1px solid #e6edf5;"><button type="button" class="btn btn-link p-0" style="font-size:11px;">Edit</button></td>
                                </tr>
                                <tr>
                                    <td style="padding:8px; border-top:1px solid #e6edf5;">2</td>
                                    <td style="padding:8px; border-top:1px solid #e6edf5;">Persamaan Kuadrat</td>
                                    <td style="padding:8px; border-top:1px solid #e6edf5;">XI</td>
                                    <td style="padding:8px; border-top:1px solid #e6edf5;">3 JP</td>
                                    <td style="padding:8px; border-top:1px solid #e6edf5;">DOCX</td>
                                    <td style="padding:8px; border-top:1px solid #e6edf5;"><button type="button" class="btn btn-link p-0" style="font-size:11px;">Edit</button></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div style="margin-bottom:16px;">
                    <h4 class="fw-semibold mb-1">Isi Informasi Modul</h4>
                    <p class="text-muted mb-0" style="font-size:13px;">Lengkapi kolom di sebelah kiri lalu lihat pratinjau di kanan.</p>
                </div>

                <div style="margin:16px 0; padding:14px; border:1px solid #d8e3f0; border-radius:12px; background:#fff; box-shadow:0 5px 16px rgba(32,73,119,.05);">
                    <div style="display:flex; justify-content:space-between; gap:10px; align-items:center; font-size:12px;">
                        <span>Kelengkapan formulir</span>
                        <span id="progress-value" style="font-size:11px; font-weight:700; color:#2865aa">5 / 5</span>
                    </div>
                    <div style="height:7px; margin-top:10px; overflow:hidden; border-radius:999px; background:#e5edf6;">
                        <div id="progress-fill" style="width:100%; height:100%; border-radius:inherit; background:linear-gradient(90deg,#3e86dc,#70a7e6); transition:width .2s ease;"></div>
                    </div>
                </div>

                <form id="rpp-form">
                    <section class="form-section" style="margin-bottom:10px; overflow:hidden; border:1px solid #d8e2ee; border-radius:11px; background:#fff;">
                        <button class="section-toggle" type="button" aria-expanded="true" style="width:100%; display:flex; align-items:center; justify-content:space-between; gap:10px; padding:14px; border:0; background:#fff; color:#24496f; text-align:left;">
                            <span style="display:flex; align-items:center; gap:10px;">
                                <span style="width:29px; height:29px; display:grid; place-items:center; border-radius:8px; background:#eaf3ff; color:#2865aa;"><i class="ti ti-file-text"></i></span>
                                <span style="font-size:13px; font-weight:600;">Informasi Umum</span>
                            </span>
                            <i class="ti ti-chevron-down"></i>
                        </button>
                        <div class="section-content" style="padding:0 14px 15px;">
                            <div class="field" style="margin-top:13px;">
                                <label for="input-title" style="display:block; margin-bottom:6px; font-size:12px; font-weight:600;">Judul Modul Ajar</label>
                                <input id="input-title" type="text" style="width:100%; border:1px solid #c9d7e7; border-radius:8px; background:#fff; padding:9px 10px; color:#1c2c3d; font-size:12px; line-height:1.5;">
                            </div>
                            <div class="field" style="margin-top:13px;">
                                <label for="input-subject" style="display:block; margin-bottom:6px; font-size:12px; font-weight:600;">Mata Pelajaran</label>
                                <input id="input-subject" type="text" style="width:100%; border:1px solid #c9d7e7; border-radius:8px; background:#fff; padding:9px 10px; color:#1c2c3d; font-size:12px; line-height:1.5;">
                            </div>
                            <div class="field" style="margin-top:13px;">
                                <label for="input-class" style="display:block; margin-bottom:6px; font-size:12px; font-weight:600;">Kelas / Fase</label>
                                <input id="input-class" type="text" style="width:100%; border:1px solid #c9d7e7; border-radius:8px; background:#fff; padding:9px 10px; color:#1c2c3d; font-size:12px; line-height:1.5;">
                            </div>
                            <div class="field" style="margin-top:13px;">
                                <label for="input-status" style="display:block; margin-bottom:6px; font-size:12px; font-weight:600;">Status</label>
                                <input id="input-status" type="text" style="width:100%; border:1px solid #c9d7e7; border-radius:8px; background:#fff; padding:9px 10px; color:#1c2c3d; font-size:12px; line-height:1.5;">
                            </div>
                            <div class="field" style="margin-top:13px;">
                                <label for="input-duration" style="display:block; margin-bottom:6px; font-size:12px; font-weight:600;">Alokasi Waktu</label>
                                <input id="input-duration" type="text" style="width:100%; border:1px solid #c9d7e7; border-radius:8px; background:#fff; padding:9px 10px; color:#1c2c3d; font-size:12px; line-height:1.5;">
                            </div>
                        </div>
                    </section>

                    <section class="form-section" style="margin-bottom:10px; overflow:hidden; border:1px solid #d8e2ee; border-radius:11px; background:#fff;">
                        <button class="section-toggle" type="button" aria-expanded="true" style="width:100%; display:flex; align-items:center; justify-content:space-between; gap:10px; padding:14px; border:0; background:#fff; color:#24496f; text-align:left;">
                            <span style="display:flex; align-items:center; gap:10px;">
                                <span style="width:29px; height:29px; display:grid; place-items:center; border-radius:8px; background:#eaf3ff; color:#2865aa;"><i class="ti ti-target"></i></span>
                                <span style="font-size:13px; font-weight:600;">Capaian & Tujuan</span>
                            </span>
                            <i class="ti ti-chevron-down"></i>
                        </button>
                        <div class="section-content" style="padding:0 14px 15px;">
                            <div class="field" style="margin-top:13px;">
                                <label for="input-achievement" style="display:block; margin-bottom:6px; font-size:12px; font-weight:600;">Capaian Pembelajaran</label>
                                <textarea id="input-achievement" rows="4" style="width:100%; border:1px solid #c9d7e7; border-radius:8px; background:#fff; padding:9px 10px; color:#1c2c3d; font-size:12px; line-height:1.5; min-height:82px; resize:vertical;"></textarea>
                            </div>
                            <div class="field" style="margin-top:13px;">
                                <label for="input-objectives" style="display:block; margin-bottom:6px; font-size:12px; font-weight:600;">Tujuan Pembelajaran</label>
                                <textarea id="input-objectives" rows="4" style="width:100%; border:1px solid #c9d7e7; border-radius:8px; background:#fff; padding:9px 10px; color:#1c2c3d; font-size:12px; line-height:1.5; min-height:82px; resize:vertical;"></textarea>
                                <p class="text-muted mt-2" style="font-size:10px; line-height:1.45;">Pisahkan tiap poin dengan enter.</p>
                            </div>
                        </div>
                    </section>

                    <section class="form-section" style="margin-bottom:10px; overflow:hidden; border:1px solid #d8e2ee; border-radius:11px; background:#fff;">
                        <button class="section-toggle" type="button" aria-expanded="true" style="width:100%; display:flex; align-items:center; justify-content:space-between; gap:10px; padding:14px; border:0; background:#fff; color:#24496f; text-align:left;">
                            <span style="display:flex; align-items:center; gap:10px;">
                                <span style="width:29px; height:29px; display:grid; place-items:center; border-radius:8px; background:#eaf3ff; color:#2865aa;"><i class="ti ti-book-open"></i></span>
                                <span style="font-size:13px; font-weight:600;">Sumber & Media</span>
                            </span>
                            <i class="ti ti-chevron-down"></i>
                        </button>
                        <div class="section-content" style="padding:0 14px 15px;">
                            <div class="field" style="margin-top:13px;">
                                <label for="input-methods" style="display:block; margin-bottom:6px; font-size:12px; font-weight:600;">Metode</label>
                                <textarea id="input-methods" rows="4" style="width:100%; border:1px solid #c9d7e7; border-radius:8px; background:#fff; padding:9px 10px; color:#1c2c3d; font-size:12px; line-height:1.5; min-height:82px; resize:vertical;"></textarea>
                            </div>
                            <div class="field" style="margin-top:13px;">
                                <label for="input-media" style="display:block; margin-bottom:6px; font-size:12px; font-weight:600;">Media</label>
                                <textarea id="input-media" rows="4" style="width:100%; border:1px solid #c9d7e7; border-radius:8px; background:#fff; padding:9px 10px; color:#1c2c3d; font-size:12px; line-height:1.5; min-height:82px; resize:vertical;"></textarea>
                            </div>
                            <div class="field" style="margin-top:13px;">
                                <label for="input-resources" style="display:block; margin-bottom:6px; font-size:12px; font-weight:600;">Sumber Belajar</label>
                                <textarea id="input-resources" rows="4" style="width:100%; border:1px solid #c9d7e7; border-radius:8px; background:#fff; padding:9px 10px; color:#1c2c3d; font-size:12px; line-height:1.5; min-height:82px; resize:vertical;"></textarea>
                            </div>
                        </div>
                    </section>

                    <section class="form-section closed" style="margin-bottom:10px; overflow:hidden; border:1px solid #d8e2ee; border-radius:11px; background:#fff;">
                        <button class="section-toggle" type="button" aria-expanded="false" style="width:100%; display:flex; align-items:center; justify-content:space-between; gap:10px; padding:14px; border:0; background:#fff; color:#24496f; text-align:left;">
                            <span style="display:flex; align-items:center; gap:10px;">
                                <span style="width:29px; height:29px; display:grid; place-items:center; border-radius:8px; background:#eaf3ff; color:#2865aa;"><i class="ti ti-lightbulb"></i></span>
                                <span style="font-size:13px; font-weight:600;">Implementasi</span>
                            </span>
                            <i class="ti ti-chevron-down"></i>
                        </button>
                        <div class="section-content" style="padding:0 14px 15px; display:none;">
                            <div class="field" style="margin-top:13px;">
                                <label for="input-practice" style="display:block; margin-bottom:6px; font-size:12px; font-weight:600;">Praktik Pedagogis</label>
                                <textarea id="input-practice" rows="4" style="width:100%; border:1px solid #c9d7e7; border-radius:8px; background:#fff; padding:9px 10px; color:#1c2c3d; font-size:12px; line-height:1.5; min-height:82px; resize:vertical;"></textarea>
                            </div>
                            <div class="field" style="margin-top:13px;">
                                <label for="input-environment" style="display:block; margin-bottom:6px; font-size:12px; font-weight:600;">Lingkungan Pembelajaran</label>
                                <textarea id="input-environment" rows="4" style="width:100%; border:1px solid #c9d7e7; border-radius:8px; background:#fff; padding:9px 10px; color:#1c2c3d; font-size:12px; line-height:1.5; min-height:82px; resize:vertical;"></textarea>
                            </div>
                            <div class="field" style="margin-top:13px;">
                                <label for="input-digital" style="display:block; margin-bottom:6px; font-size:12px; font-weight:600;">Pemanfaatan Digital</label>
                                <textarea id="input-digital" rows="4" style="width:100%; border:1px solid #c9d7e7; border-radius:8px; background:#fff; padding:9px 10px; color:#1c2c3d; font-size:12px; line-height:1.5; min-height:82px; resize:vertical;"></textarea>
                            </div>
                        </div>
                    </section>

                    <section class="form-section closed" style="margin-bottom:10px; overflow:hidden; border:1px solid #d8e2ee; border-radius:11px; background:#fff;">
                        <button class="section-toggle" type="button" aria-expanded="false" style="width:100%; display:flex; align-items:center; justify-content:space-between; gap:10px; padding:14px; border:0; background:#fff; color:#24496f; text-align:left;">
                            <span style="display:flex; align-items:center; gap:10px;">
                                <span style="width:29px; height:29px; display:grid; place-items:center; border-radius:8px; background:#eaf3ff; color:#2865aa;"><i class="ti ti-clipboard-check"></i></span>
                                <span style="font-size:13px; font-weight:600;">Penilaian & Refleksi</span>
                            </span>
                            <i class="ti ti-chevron-down"></i>
                        </button>
                        <div class="section-content" style="padding:0 14px 15px; display:none;">
                            <div class="field" style="margin-top:13px;">
                                <label for="input-experience" style="display:block; margin-bottom:6px; font-size:12px; font-weight:600;">Pengalaman Pembelajaran</label>
                                <textarea id="input-experience" rows="4" style="width:100%; border:1px solid #c9d7e7; border-radius:8px; background:#fff; padding:9px 10px; color:#1c2c3d; font-size:12px; line-height:1.5; min-height:82px; resize:vertical;"></textarea>
                            </div>
                            <div class="field" style="margin-top:13px;">
                                <label for="input-reflection" style="display:block; margin-bottom:6px; font-size:12px; font-weight:600;">Refleksi</label>
                                <textarea id="input-reflection" rows="4" style="width:100%; border:1px solid #c9d7e7; border-radius:8px; background:#fff; padding:9px 10px; color:#1c2c3d; font-size:12px; line-height:1.5; min-height:82px; resize:vertical;"></textarea>
                            </div>
                            <div class="field" style="margin-top:13px;">
                                <label for="input-assessment" style="display:block; margin-bottom:6px; font-size:12px; font-weight:600;">Asesmen</label>
                                <textarea id="input-assessment" rows="4" style="width:100%; border:1px solid #c9d7e7; border-radius:8px; background:#fff; padding:9px 10px; color:#1c2c3d; font-size:12px; line-height:1.5; min-height:82px; resize:vertical;"></textarea>
                            </div>
                        </div>
                    </section>
                </form>
            </div>
        </aside>

        <section class="preview-side" style="min-width:0; display:flex; flex-direction:column; background:#dfe7f0;">
            <div style="min-height:47px; padding:0 22px; display:flex; align-items:center; justify-content:space-between; gap:14px; background:rgba(255,255,255,.88); border-bottom:1px solid #cad7e6; font-size:11px;">
                <span style="display:inline-flex; align-items:center; gap:7px; font-weight:700;"><i class="ti ti-file-check"></i> Pratinjau Modul Ajar</span>
                <span>Preview</span>
            </div>
            <div style="flex:1; overflow:auto; padding:28px 28px 52px; background-color:#dfe7f0; background-image:radial-gradient(#becbd9 .7px,transparent .7px); background-size:15px 15px;">
                <article id="document-page" style="width:min(100%,820px); min-height:1100px; margin:0 auto; padding:58px 65px 80px; background:#fff; box-shadow:0 4px 12px rgba(26,55,89,.18),0 24px 44px rgba(26,55,89,.13);">
                    <p class="text-muted text-center mb-2" style="font-size:10px; letter-spacing:1px; text-transform:uppercase;">MODUL AJAR</p>
                    <h1 id="preview-title" style="margin:0 0 30px; color:#16283c; font-size:26px; font-weight:700; line-height:1.28; text-align:center;"></h1>
                    <h2 style="margin:27px 0 10px; padding-bottom:6px; border-bottom:1.5px solid #4b78a9; font-size:15px;">Informasi Umum</h2>
                    <table style="width:100%; border-collapse:collapse;">
                        <tbody>
                            <tr>
                                <th style="width:165px; padding:8px 10px; border:1px solid #afbdce; text-align:left; vertical-align:top; background:#eef5fc; font-weight:700;">Mata Pelajaran</th>
                                <td id="preview-subject" style="padding:8px 10px; border:1px solid #afbdce; vertical-align:top;"></td>
                            </tr>
                            <tr>
                                <th style="width:165px; padding:8px 10px; border:1px solid #afbdce; text-align:left; vertical-align:top; background:#eef5fc; font-weight:700;">Kelas / Fase</th>
                                <td id="preview-class" style="padding:8px 10px; border:1px solid #afbdce; vertical-align:top;"></td>
                            </tr>
                            <tr>
                                <th style="width:165px; padding:8px 10px; border:1px solid #afbdce; text-align:left; vertical-align:top; background:#eef5fc; font-weight:700;">Status</th>
                                <td id="preview-status" style="padding:8px 10px; border:1px solid #afbdce; vertical-align:top;"></td>
                            </tr>
                            <tr>
                                <th style="width:165px; padding:8px 10px; border:1px solid #afbdce; text-align:left; vertical-align:top; background:#eef5fc; font-weight:700;">Alokasi Waktu</th>
                                <td id="preview-duration" style="padding:8px 10px; border:1px solid #afbdce; vertical-align:top;"></td>
                            </tr>
                        </tbody>
                    </table>
                    <h2>Tujuan Pembelajaran</h2>
                    <ul id="preview-objectives" style="margin:0; padding-left:22px;"></ul>
                    <h2>Metode Pembelajaran</h2>
                    <ul id="preview-methods" style="margin:0; padding-left:22px;"></ul>
                    <h2>Media Pembelajaran</h2>
                    <ul id="preview-media" style="margin:0; padding-left:22px;"></ul>
                    <h2>Sumber Belajar</h2>
                    <ul id="preview-resources" style="margin:0; padding-left:22px;"></ul>
                    <h2>Praktik Pedagogis</h2>
                    <p id="preview-practice" style="margin:0; white-space:pre-wrap;"></p>
                    <h2>Lingkungan Pembelajaran</h2>
                    <p id="preview-environment" style="margin:0; white-space:pre-wrap;"></p>
                    <h2>Pemanfaatan Digital</h2>
                    <p id="preview-digital" style="margin:0; white-space:pre-wrap;"></p>
                    <h2>Pengalaman Pembelajaran</h2>
                    <p id="preview-experience" style="margin:0; white-space:pre-wrap;"></p>
                    <h2>Refleksi</h2>
                    <p id="preview-reflection" style="margin:0; white-space:pre-wrap;"></p>
                    <h2>Asesmen</h2>
                    <p id="preview-assessment" style="margin:0; white-space:pre-wrap;"></p>
                </article>
            </div>
        </section>
    </main>
</div>

<script>
    const initialData = {
        title: 'Modul Ajar Matematika - Konsep Eksponen',
        subject: 'Matematika',
        class: 'X / Fase E',
        status: 'Draft',
        duration: '2 JP (2 x 45 menit)',
        achievement: 'Di akhir fase E, peserta didik dapat menggunakan fungsi eksponen dalam menyelesaikan masalah kontekstual.',
        objectives: 'Peserta didik mampu mengidentifikasi bentuk umum fungsi eksponen.\nPeserta didik mampu memodelkan masalah nyata menggunakan fungsi eksponen.\nPeserta didik mampu menyelesaikan masalah kontekstual secara kolaboratif.',
        methods: 'Ceramah interaktif\nDiskusi kelompok\nTanya jawab',
        media: 'Slide PowerPoint\nGeoGebra\nLKPD digital',
        resources: 'Buku teks\nVideo pembelajaran\nWebsite GeoGebra',
        practice: 'Model pembelajaran: Problem Based Learning.',
        environment: 'Ruang kelas fleksibel dan akses internet stabil.',
        digital: 'GeoGebra untuk visualisasi grafik dan Google Classroom untuk tugas.',
        experience: 'Awal pembelajaran dengan apersepsi, inti diskusi kelompok, penutup refleksi.',
        reflection: 'Refleksi guru dan peserta didik pada akhir pembelajaran.',
        assessment: 'Asesmen formatif melalui observasi dan kuis singkat.'
    };

    const fields = Object.keys(initialData);
    const listFields = ['objectives', 'methods', 'media', 'resources'];
    const groups = [
        ['title', 'subject', 'class', 'status', 'duration'],
        ['achievement', 'objectives'],
        ['methods', 'media', 'resources'],
        ['practice', 'environment', 'digital'],
        ['experience', 'reflection', 'assessment']
    ];

    function renderList(element, value) {
        element.replaceChildren();
        value.split('\n').map(item => item.trim()).filter(Boolean).forEach(item => {
            const li = document.createElement('li');
            li.textContent = item.replace(/^[•\-\d.]+\s*/, '');
            element.appendChild(li);
        });
    }

    function updatePreview(field) {
        const input = document.getElementById('input-' + field);
        const output = document.getElementById('preview-' + field);
        if (!input || !output) return;
        if (listFields.includes(field)) renderList(output, input.value);
        else output.textContent = input.value;
        updateProgress();
    }

    function populateForm(data) {
        fields.forEach(field => {
            const input = document.getElementById('input-' + field);
            if (input) {
                input.value = data[field] || '';
                updatePreview(field);
            }
        });
    }

    function updateProgress() {
        const complete = groups.filter(group => group.every(field => {
            const input = document.getElementById('input-' + field);
            return input && input.value.trim();
        })).length;
        document.getElementById('progress-value').textContent = complete + ' / ' + groups.length;
        document.getElementById('progress-fill').style.width = (complete / groups.length * 100) + '%';
    }

    function setMode(mode) {
        const workspace = document.getElementById('workspace');
        const viewButton = document.getElementById('view-button');
        const editButton = document.getElementById('edit-button');
        workspace.style.gridTemplateColumns = mode === 'view' ? '1fr' : 'minmax(320px,410px) minmax(500px,1fr)';
        document.querySelector('.editor-side').style.display = mode === 'view' ? 'none' : 'block';
        viewButton.classList.toggle('active', mode === 'view');
        editButton.classList.toggle('active', mode === 'edit');
        viewButton.style.background = mode === 'view' ? '#fff' : 'rgba(255,255,255,.15)';
        viewButton.style.color = mode === 'view' ? '#1f5ca3' : '#fff';
        editButton.style.background = mode === 'edit' ? '#fff' : 'rgba(255,255,255,.15)';
        editButton.style.color = mode === 'edit' ? '#1f5ca3' : '#fff';
    }

    function setStatus(message) {
        const status = document.getElementById('header-status');
        status.textContent = message;
        window.setTimeout(() => {
            if (status.textContent === message) status.textContent = '';
        }, 3500);
    }

    function downloadDocument() {
        const title = document.getElementById('input-title').value.trim() || 'Modul_Ajar';
        const content = [
            'Modul Ajar',
            '',
            'Judul: ' + (document.getElementById('input-title').value || '-'),
            'Mata Pelajaran: ' + (document.getElementById('input-subject').value || '-'),
            'Kelas / Fase: ' + (document.getElementById('input-class').value || '-'),
            'Status: ' + (document.getElementById('input-status').value || '-'),
            'Alokasi Waktu: ' + (document.getElementById('input-duration').value || '-'),
            '',
            'Capaian Pembelajaran:',
            document.getElementById('input-achievement').value || '-',
            '',
            'Tujuan Pembelajaran:',
            document.getElementById('input-objectives').value || '-',
            '',
            'Metode:',
            document.getElementById('input-methods').value || '-',
            '',
            'Media:',
            document.getElementById('input-media').value || '-',
            '',
            'Sumber Belajar:',
            document.getElementById('input-resources').value || '-'
        ].join('\n');

        const blob = new Blob([content], { type: 'text/plain;charset=utf-8' });
        const link = document.createElement('a');
        link.href = URL.createObjectURL(blob);
        link.download = title.replace(/[^\w\s-]/g, '').replace(/\s+/g, '_') + '.txt';
        document.body.appendChild(link);
        link.click();
        link.remove();
        URL.revokeObjectURL(link.href);
        setStatus('Dokumen berhasil diunduh');
    }

    document.addEventListener('DOMContentLoaded', () => {
        populateForm(initialData);

        document.getElementById('rpp-form').addEventListener('submit', event => event.preventDefault());
        fields.forEach(field => {
            const input = document.getElementById('input-' + field);
            if (input) input.addEventListener('input', () => updatePreview(field));
        });

        document.querySelectorAll('.section-toggle').forEach(button => {
            button.addEventListener('click', () => {
                const section = button.closest('.form-section');
                const content = section.querySelector('.section-content');
                const closed = section.classList.toggle('closed');
                button.setAttribute('aria-expanded', String(!closed));
                if (content) content.style.display = closed ? 'none' : 'block';
            });
        });

        document.getElementById('view-button').addEventListener('click', () => setMode('view'));
        document.getElementById('edit-button').addEventListener('click', () => setMode('edit'));
        document.getElementById('create-button').addEventListener('click', () => {
            populateForm({});
            setMode('edit');
            document.getElementById('input-title').focus();
            setStatus('Template baru siap diisi');
        });
        document.getElementById('download-button').addEventListener('click', downloadDocument);
    });
</script>
@endsection
