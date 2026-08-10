@extends('layouts.app', ['pageSlug' => 'modul_ajar'])

@section('title', 'Daftar Modul Ajar')

@section('content')
<div class="card shadow-sm border-0">
    <div class="card-header border-0">
        <div>
            <h3 class="fw-semibold mb-1">Daftar Modul Ajar</h3>
            <p class="text-muted mb-0">Kelola data modul ajar pada halaman tersendiri.</p>
        </div>
    </div>
    <div class="card-body">
        <ul class="nav nav-tabs" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active" id="tab-list-tab" data-bs-toggle="tab" data-bs-target="#tab-list" type="button" role="tab" aria-controls="tab-list" aria-selected="true">Daftar Modul Ajar</button>
            </li>
            <li class="nav-item" role="presentation">
                <a class="nav-link" id="tab-create-tab" href="{{ route('rencana_pembelajaran.create') }}" role="tab" aria-controls="tab-create" aria-selected="false">Tambah Modul Ajar</a>
            </li>
        </ul>
        <div class="tab-content mt-3">
            <div class="tab-pane fade show active" id="tab-list" role="tabpanel" aria-labelledby="tab-list-tab">
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Nama Modul Ajar</th>
                                <th>Kelas</th>
                                <th>Alokasi Waktu</th>
                                <th>File Modul Ajar</th>
                                <th>Status</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($modules as $index => $module)
                                <tr>
                                    <td>{{ $loop->iteration }}</td>
                                    <td>{{ $module['title'] ?? '-' }}</td>
                                    <td>{{ $module['class'] ?? '-' }}</td>
                                    <td>{{ $module['duration'] ?? '-' }}</td>
                                    <td>{{ $module['title'] ?? '-' }}</td>
                                    <td>{{ ucfirst($module['status'] ?? '-') }}</td>
                                    <td>
                                        <button type="button" class="btn btn-sm btn-outline-secondary btn-view-module" data-module='@json($module)'>View</button>
                                        <a href="{{ route('rencana_pembelajaran.edit', $module['id'] ?? $index) }}" class="btn btn-sm btn-outline-primary">Edit</a>
                                        <form action="{{ route('rencana_pembelajaran.destroy', $module['id'] ?? $index) }}" method="POST" class="d-inline" onsubmit="return confirm('Hapus modul ajar ini?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-outline-danger">Hapus</button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="text-center text-muted">Belum ada modul ajar yang tersimpan.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('js')
<script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.9.2/html2pdf.bundle.min.js"></script>
<div class="modal fade" id="previewModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-xl modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Pratinjau Modul Ajar</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <div id="preview-content" style="padding:24px; background:#fff;">
          <article id="modal-preview-document" style="width:min(100%,820px); min-height:1100px; margin:0 auto; padding:58px 65px 80px; background:#fff; box-shadow:0 4px 12px rgba(26,55,89,.18),0 24px 44px rgba(26,55,89,.13);">
            <p class="text-muted text-center mb-2" style="font-size:10px; letter-spacing:1px; text-transform:uppercase;">MODUL AJAR</p>
            <h1 style="margin:0 0 30px; color:#16283c; font-size:26px; font-weight:700; line-height:1.28; text-align:center;">MODUL AJAR</h1>
            <h2 style="margin:27px 0 10px; padding-bottom:6px; border-bottom:1.5px solid #4b78a9; font-size:15px;">Informasi Umum</h2>
            <table style="width:100%; border-collapse:collapse; margin-bottom:18px;">
              <tbody>
                <tr><th style="width:165px; padding:8px 10px; border:1px solid #afbdce; text-align:left; vertical-align:top; background:#eef5fc; font-weight:700;">Judul Modul Ajar</th><td id="modal-preview-title" style="padding:8px 10px; border:1px solid #afbdce; vertical-align:top;"></td></tr>
                <tr><th style="width:165px; padding:8px 10px; border:1px solid #afbdce; text-align:left; vertical-align:top; background:#eef5fc; font-weight:700;">Mata Pelajaran</th><td id="modal-preview-subject" style="padding:8px 10px; border:1px solid #afbdce; vertical-align:top;"></td></tr>
                <tr><th style="width:165px; padding:8px 10px; border:1px solid #afbdce; text-align:left; vertical-align:top; background:#eef5fc; font-weight:700;">Kelas / Fase</th><td id="modal-preview-class" style="padding:8px 10px; border:1px solid #afbdce; vertical-align:top;"></td></tr>
                <tr><th style="width:165px; padding:8px 10px; border:1px solid #afbdce; text-align:left; vertical-align:top; background:#eef5fc; font-weight:700;">Status</th><td id="modal-preview-status" style="padding:8px 10px; border:1px solid #afbdce; vertical-align:top;"></td></tr>
                <tr><th style="width:165px; padding:8px 10px; border:1px solid #afbdce; text-align:left; vertical-align:top; background:#eef5fc; font-weight:700;">Alokasi Waktu</th><td id="modal-preview-duration" style="padding:8px 10px; border:1px solid #afbdce; vertical-align:top;"></td></tr>
                <tr><th style="width:165px; padding:8px 10px; border:1px solid #afbdce; text-align:left; vertical-align:top; background:#eef5fc; font-weight:700;">Dimensi Lulusan</th><td id="modal-preview-dimensi_lulusan" style="padding:8px 10px; border:1px solid #afbdce; vertical-align:top;"><span style="color:#778593; font-style:italic; font-size:10px;">Belum dipilih</span></td></tr>
              </tbody>
            </table>
            <h2>Capaian Pembelajaran</h2>
            <p id="modal-preview-achievement" style="margin:0 0 10px; white-space:pre-wrap;"></p>
            <h2>Tujuan Pembelajaran</h2>
            <p id="modal-preview-objectives" style="margin:0 0 10px; white-space:pre-wrap;"></p>
            <h2>Praktik Pedagogis</h2>
            <p id="modal-preview-practice" style="margin:0 0 10px; white-space:pre-wrap;"></p>
            <h2>Lingkungan Pembelajaran</h2>
            <p id="modal-preview-environment" style="margin:0 0 10px; white-space:pre-wrap;"></p>
            <h2>Pemanfaatan Digital</h2>
            <p id="modal-preview-digital" style="margin:0 0 10px; white-space:pre-wrap;"></p>
            <h2>Pengalaman Pembelajaran</h2>
            <p id="modal-preview-experience" style="margin:0 0 10px; white-space:pre-wrap;"></p>
            <h2>Refleksi Pembelajaran</h2>
            <p id="modal-preview-reflection" style="margin:0 0 10px; white-space:pre-wrap;"></p>
            <h2>Asesmen</h2>
            <p id="modal-preview-assessment" style="margin:0 0 10px; white-space:pre-wrap;"></p>

            <div style="display:flex; justify-content:space-between; gap:12mm; margin-top:36px; page-break-inside:avoid;">
              <div style="width:48%; text-align:left;">
                <div style="margin-bottom:6px;">Mengetahui,</div>
                <div style="font-weight:600; margin-bottom:4px;">Kepala {{ $sekolah->nama_sekolah ?? '' }}</div>
                <div style="height:64px;"></div>
                <div style="font-weight:700; margin-top:6px;">{{ $kepalaName ?? '_________________________' }}</div>
                <div style="margin-top:4px;">{{ $kepalaNip ? ('NIP. ' . $kepalaNip) : 'NIP. ................................' }}</div>
              </div>
              <div style="width:48%; text-align:right;">
                <div>{{ $sekolah->kota ?? 'Kota/Kabupaten' }}, <span id="modal-preview-date"></span></div>
                <div style="font-weight:600; margin-bottom:4px;">Guru Bidang Studi</div>
                <div style="height:64px;"></div>
                <div style="font-weight:700; margin-top:6px;">{{ $guruName ?? '_________________________' }}</div>
                <div style="margin-top:4px;">{{ $guruNip ? ('NIP. ' . $guruNip) : 'NIP. ................................' }}</div>
              </div>
            </div>
          </article>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
        <button type="button" id="download-pdf" class="btn btn-primary">Download as PDF</button>
      </div>
    </div>
  </div>
</div>

    <script>
    const SCHOOL_CITY = {!! json_encode($sekolah->kota ?? 'Kota/Kabupaten') !!};
    const SCHOOL_NAME = {!! json_encode($sekolah->nama_sekolah ?? '') !!};
    const TEACHER_NAME = {!! json_encode($guruName ?? '') !!};
    const TEACHER_NIP = {!! json_encode($guruNip ?? '') !!};
    const KEPALA_NAME = {!! json_encode($kepalaName ?? '') !!};
    const KEPALA_NIP = {!! json_encode($kepalaNip ?? '') !!};

    function decodeHtmlEntities(str) {
        if (!str || (typeof str !== 'string')) return '';
        if (!str.includes('&lt;') && !str.includes('&amp;')) return str;
        const txt = document.createElement('textarea');
        txt.innerHTML = str;
        return txt.value;
    }

    function populateModalPreview(module) {
        const setText = (id, value) => {
            const el = document.getElementById(id);
            if (el) el.textContent = value || '';
        };
        const setHtml = (id, value) => {
            const el = document.getElementById(id);
            if (!el) return;
            const decoded = decodeHtmlEntities(value || '');
            el.innerHTML = decoded;
        };
        const setDimensi = (id, value) => {
            const el = document.getElementById(id);
            if (!el) return;
            const raw = value || '';
            const sep = raw.includes('\n') ? '\n' : ',';
            const parts = raw.split(sep).map(s => s.trim()).filter(Boolean);
            const itemSep = '<hr style="margin:4px 0; border:0; border-top:1px dashed #c9d7e7;">';
            el.innerHTML = parts.length > 0 ? parts.join(itemSep) : '<span style="color:#778593; font-style:italic; font-size:10px;">Belum dipilih</span>';
        };

        setText('modal-preview-title', module.title);
        setText('modal-preview-subject', module.subject);
        setText('modal-preview-class', module.class);
        setText('modal-preview-status', module.status === 'published'
            ? 'Publish (Digunakan untuk KBM)'
            : 'Draft (Belum digunakan untuk KBM)');
        setText('modal-preview-duration', module.duration);
        setDimensi('modal-preview-dimensi_lulusan', module.dimensi_lulusan);
        setHtml('modal-preview-achievement', module.achievement);
        setHtml('modal-preview-objectives', module.objectives);
        setHtml('modal-preview-practice', module.practice);
        setHtml('modal-preview-environment', module.environment);
        setHtml('modal-preview-digital', module.digital);
        setHtml('modal-preview-experience', module.experience);
        setHtml('modal-preview-reflection', module.reflection);
        setHtml('modal-preview-assessment', module.assessment);

        const date = new Date();
        const monthNames = ['Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember'];
        const tanggal = date.getDate() + ' ' + monthNames[date.getMonth()] + ' ' + date.getFullYear();
        setText('modal-preview-date', tanggal);
    }

    document.addEventListener('DOMContentLoaded', () => {
        document.querySelectorAll('.btn-view-module').forEach(btn => {
            btn.addEventListener('click', () => {
                const module = JSON.parse(btn.getAttribute('data-module') || '{}');
                populateModalPreview(module);
                const modalEl = document.getElementById('previewModal');
                const modal = new bootstrap.Modal(modalEl);
                modal.show();

                    document.getElementById('download-pdf').onclick = function() {
                    const element = document.getElementById('modal-preview-document');
                    const opt = {
                        margin:       10,
                        filename:     (module.title || 'modul') + '.pdf',
                        image:        { type: 'jpeg', quality: 0.98 },
                        html2canvas:  { scale: 2 },
                        jsPDF:        { unit: 'mm', format: 'a4', orientation: 'portrait' }
                    };
                    html2pdf().set(opt).from(element).save();
                };
            });
        });
    });
</script>
@endpush
