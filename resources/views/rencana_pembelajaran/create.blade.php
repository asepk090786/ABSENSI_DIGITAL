@extends('layouts.app', ['pageSlug' => 'rencana_pembelajaran'])

@section('title', 'Tambah Rencana Pembelajaran')

@section('page-header')
<div class="page-header d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-3 mb-3">
    <div>
        <h1 class="page-title" style="margin:0;font-size:1.1rem;font-weight:700;color:#0f172a;">Rencana Pembelajaran</h1>
        <p class="page-subtitle" style="margin:.35rem 0 0;color:#64748b;font-size:.95rem;">{{ $mataPelajaran->nama_mapel }} - Tingkat {{ $tingkat }}</p>
    </div>
    <div class="d-flex flex-wrap gap-2" style="align-items:center;">
        <span class="badge bg-secondary" style="font-size:.85rem;padding:.7rem .9rem;">RPP Baru</span>
    </div>
</div>
@endsection

@section('content')
<div class="rpp-editor-container">
  <main class="rpp-editor-grid">
    <aside class="form-panel" style="background:#f5f6f8;border-right:1px solid #c8cbd0;overflow-y:auto;padding:22px 20px 40px;" aria-label="Form editor RPP">
      <div style="border-bottom:1px solid #cbd2da;padding-bottom:15px;margin-bottom:18px;">
        <h1 style="margin:0 0 4px;font-size:18px;font-weight:700;color:#185abd;">Buat Rencana Pembelajaran</h1>
        <p style="margin:0;font-size:12px;color:#68717d;">Isi formulir di sebelah kiri, lalu edit dokumen RPP di sebelah kanan menggunakan OnlyOffice.</p>
      </div>

      <form action="{{ route('rencana_pembelajaran.store') }}" method="POST" id="rpp-form">
        @csrf
        <input type="hidden" name="mata_pelajaran_id" value="{{ $mataPelajaran->id }}">
        <input type="hidden" name="temp_key" value="{{ $tempKey ?? '' }}">
        <div id="hidden-kelas-inputs"></div>
        <div id="selected-kelas-tags" class="mb-3"></div>

        <section class="form-card-section" style="background:#fff;border:1px solid #d6d9de;border-radius:5px;padding:15px;margin-bottom:13px;box-shadow:0 1px 2px rgba(24,36,50,.04);">
          <h2 style="color:#185abd;border-bottom:1px solid #dce5f2;padding-bottom:8px;margin:0 0 12px;font-size:13px;font-weight:700;">Informasi Umum</h2>
          <div style="margin-bottom:12px;">
            <label style="display:block;margin-bottom:5px;color:#3d4753;font-size:11px;font-weight:700;">Mata Pelajaran</label>
            <input type="text" class="editor-input" value="{{ $mataPelajaran->nama_mapel }}" disabled style="width:100%;border:1px solid #b9c1cb;color:#202124;border-radius:3px;background:#f5f6f8;padding:8px 9px;font-size:12px;">
          </div>
          <div class="info-umum-row">
            <div>
              <label style="display:block;margin-bottom:5px;color:#3d4753;font-size:11px;font-weight:700;">Fase</label>
              <select id="fase-selector" class="form-select" style="width:100%;border:1px solid #b9c1cb;color:#202124;border-radius:3px;background:#fff;padding:8px 9px;font-size:12px;">
                @foreach($faseOptions as $value => $label)
                  <option value="{{ $value }}" {{ $selectedFase == $value ? 'selected' : '' }}>{{ $label }}</option>
                @endforeach
              </select>
            </div>
            <div>
              <label style="display:block;margin-bottom:5px;color:#3d4753;font-size:11px;font-weight:700;">Kelas</label>
              <button type="button" id="btn-pilih-kelas-header" class="btn btn-outline-primary" style="width:100%;min-height:38px;"> <i class="ti ti-school me-1"></i><span id="label-pilih-kelas">Pilih Kelas...</span></button>
            </div>
          </div>
          <div style="margin-bottom:12px;">
            <label style="display:block;margin-bottom:5px;color:#3d4753;font-size:11px;font-weight:700;">Judul <span style="color:#dc2626;">*</span></label>
            <input type="text" name="judul" id="input-title" class="editor-input" value="{{ old('judul') }}" required style="width:100%;border:1px solid #b9c1cb;color:#202124;border-radius:3px;background:#fff;padding:8px 9px;font-size:12px;">
            @error('judul')
              <div style="color:#dc2626;font-size:11px;margin-top:4px;">{{ $message }}</div>
            @enderror
          </div>
          <div style="margin-bottom:12px;">
            <label style="display:block;margin-bottom:5px;color:#3d4753;font-size:11px;font-weight:700;">Alokasi Waktu</label>
            <input type="text" name="alokasi_waktu" id="input-duration" class="editor-input" value="{{ old('alokasi_waktu') }}" style="width:100%;border:1px solid #b9c1cb;color:#202124;border-radius:3px;background:#fff;padding:8px 9px;font-size:12px;">
          </div>
        </section>

        <section class="form-card-section" style="background:#fff;border:1px solid #d6d9de;border-radius:5px;padding:15px;margin-bottom:13px;box-shadow:0 1px 2px rgba(24,36,50,.04);">
          <h2 style="color:#185abd;border-bottom:1px solid #dce5f2;padding-bottom:8px;margin:0 0 12px;font-size:13px;font-weight:700;">Komponen Penilaian</h2>
          <div>
            @forelse($komponenNilai as $komponen)
              <div style="margin-bottom:8px;display:flex;align-items:center;gap:8px;">
                <input type="checkbox" name="komponen_nilai_ids[]" value="{{ $komponen->id }}" id="komponen_{{ $komponen->id }}" {{ in_array($komponen->id, old('komponen_nilai_ids', [])) ? 'checked' : '' }} style="width:14px;height:14px;accent-color:#185abd;">
                <label for="komponen_{{ $komponen->id }}" style="font-size:12px;color:#202124;cursor:pointer;">
                  {{ $komponen->nama_komponen }}
                  @if($komponen->bobot)
                    <span style="color:#68717d;font-size:11px;">({{ $komponen->bobot }}%)</span>
                  @endif
                </label>
              </div>
            @empty
              <div style="background:#eef6ff;border:1px solid #c5d9f5;border-radius:4px;padding:10px;font-size:12px;color:#185abd;">
                <i class="ti ti-info-circle" style="margin-right:6px;"></i>Belum ada komponen penilaian
              </div>
            @endforelse
          </div>
          @error('komponen_nilai_ids')
            <div style="color:#dc2626;font-size:11px;margin-top:4px;">{{ $message }}</div>
          @enderror
        </section>

        <section class="form-card-section" style="background:#fff;border:1px solid #d6d9de;border-radius:5px;padding:15px;margin-bottom:13px;box-shadow:0 1px 2px rgba(24,36,50,.04);">
          <h2 style="color:#185abd;border-bottom:1px solid #dce5f2;padding-bottom:8px;margin:0 0 12px;font-size:13px;font-weight:700;">Pengaturan</h2>
          <div style="margin-bottom:12px;">
            <label style="display:block;margin-bottom:5px;color:#3d4753;font-size:11px;font-weight:700;">Status <span style="color:#dc2626;">*</span></label>
            <select name="status" class="editor-input" required style="width:100%;border:1px solid #b9c1cb;color:#202124;border-radius:3px;background:#fff;padding:8px 9px;font-size:12px;">
              <option value="draft" {{ old('status') === 'draft' ? 'selected' : '' }}>Draft</option>
              <option value="published" {{ old('status') === 'published' ? 'selected' : '' }}>Published</option>
            </select>
            @error('status')
              <div style="color:#dc2626;font-size:11px;margin-top:4px;">{{ $message }}</div>
            @enderror
          </div>
        </section>

        <div style="display:flex;justify-content:space-between;align-items:center;gap:10px;">
          <a href="{{ route('rencana_pembelajaran.index', ['mata_pelajaran_id' => $mataPelajaran->id, 'tingkat' => $tingkat]) }}" style="color:#185abd;text-decoration:none;font-size:12px;font-weight:600;">Batal</a>
          <button type="submit" style="background:#185abd;color:white;border:0;border-radius:4px;padding:8px 18px;font-size:12px;font-weight:600;cursor:pointer;">Simpan</button>
        </div>
      </form>
    </aside>

    <section class="preview-panel" style="overflow:hidden;padding:0;background-color:#e4e5e7;display:flex;flex-direction:column;" aria-label="Editor OnlyOffice">
      <div id="onlyoffice-preview-summary" style="background:#ffffff;border-bottom:1px solid #dce5f2;padding:16px 20px;">
        <div style="display:flex;flex-wrap:wrap;gap:12px;align-items:center;">
          <div style="min-width:160px;">
            <div style="font-size:11px;font-weight:700;color:#0f172a;letter-spacing:.03em;">Judul</div>
            <div id="preview-field-judul" style="font-size:14px;color:#1f2937;">-</div>
          </div>
          <div style="min-width:160px;">
            <div style="font-size:11px;font-weight:700;color:#0f172a;letter-spacing:.03em;">Fase</div>
            <div id="preview-field-fase" style="font-size:14px;color:#1f2937;">-</div>
          </div>
          <div style="min-width:160px;">
            <div style="font-size:11px;font-weight:700;color:#0f172a;letter-spacing:.03em;">Kelas</div>
            <div id="preview-field-kelas" style="font-size:14px;color:#1f2937;">-</div>
          </div>
          <div style="min-width:160px;">
            <div style="font-size:11px;font-weight:700;color:#0f172a;letter-spacing:.03em;">Status</div>
            <div id="preview-field-status" style="font-size:14px;color:#1f2937;">-</div>
          </div>
          <div style="min-width:160px;">
            <div style="font-size:11px;font-weight:700;color:#0f172a;letter-spacing:.03em;">Pengguna Aktif</div>
            <div id="preview-field-active_users" style="font-size:14px;color:#1f2937;">-</div>
          </div>
          <div style="min-width:160px;">
            <div style="font-size:11px;font-weight:700;color:#0f172a;letter-spacing:.03em;">Alokasi Waktu</div>
            <div id="preview-field-alokasi_waktu" style="font-size:14px;color:#1f2937;">-</div>
          </div>
        </div>
        <div style="margin-top:16px;">
          <div style="font-size:11px;font-weight:700;color:#0f172a;letter-spacing:.03em;">Komponen Penilaian</div>
          <div id="preview-field-komponen_nilai" style="font-size:14px;color:#1f2937;min-height:1.4rem;">-</div>
        </div>
      </div>
      <div style="flex:1;position:relative;min-height:calc(100vh - 240px);max-height:calc(100vh - 240px);">
<x-onlyoffice
    :file-url="$templateUrl"
    :callback-url="$callbackUrl"
    file-type="docx"
    :title="'Rencana Pembelajaran - ' . $mataPelajaran->nama_mapel"
    :readonly="false"
    :token="$onlyOfficeJwtToken"
    container-id="onlyoffice-editor-container"
/>
      </div>
    </section>
  </main>
</div>
@endsection

<div class="modal fade" id="modalPilihKelas" tabindex="-1" aria-labelledby="modalPilihKelasLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="modalPilihKelasLabel">Pilih Kelas</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <div class="mb-3">
          <input type="text" class="form-control" id="search-kelas" placeholder="Cari kelas...">
        </div>
        <div class="list-group list-group-flush" id="daftar-kelas" style="max-height: 50vh; overflow-y: auto;">
          @php
            $faseMap = [
                'I' => 'A', 'II' => 'A',
                'III' => 'B', 'IV' => 'B',
                'V' => 'C', 'VI' => 'C',
                'VII' => 'D', 'VIII' => 'D', 'IX' => 'D',
                'X' => 'E',
                'XI' => 'F', 'XII' => 'F',
            ];
          @endphp
          @foreach($kelas as $k)
            @php $fase = $faseMap[$k->tingkat_kelas] ?? ''; @endphp
            <label class="list-group-item list-group-item-action d-flex align-items-center gap-2" data-nama="{{ strtolower($k->nama_kelas) }}" data-fase="{{ $fase }}">
              <input class="form-check-input m-0 kelas-checkbox" type="checkbox" name="kelas_ids[]" value="{{ $k->id }}">
              <span>{{ $k->nama_kelas }}</span>
            </label>
          @endforeach
        </div>
        @if($kelas->isEmpty())
          <div class="alert alert-info mb-0">
            <i class="ti ti-info-circle me-2"></i>Belum ada kelas untuk mata pelajaran ini
          </div>
        @endif
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
        <button type="button" class="btn btn-primary" id="btn-pilih-kelas-ok">Pilih</button>
      </div>
    </div>
  </div>
</div>


@push('js')
<script>
document.addEventListener('DOMContentLoaded', function () {
  const btnPilihHeader = document.getElementById('btn-pilih-kelas-header');
  const btnPilihForm = document.getElementById('btn-pilih-kelas');
  const modalEl = document.getElementById('modalPilihKelas');
  const searchInput = document.getElementById('search-kelas');
  const daftarKelas = document.getElementById('daftar-kelas');
  const btnOk = document.getElementById('btn-pilih-kelas-ok');
  const labelPilihHeader = document.getElementById('label-pilih-kelas');
  const labelPilihForm = document.getElementById('label-pilih-kelas-form') || labelPilihHeader;
  const tagsContainer = document.getElementById('selected-kelas-tags');
  const faseSelector = document.getElementById('fase-selector');
  const oldChecked = {!! json_encode(old('kelas_ids', [])) !!};

  function initCheckboxes() {
    const checkboxes = daftarKelas.querySelectorAll('.kelas-checkbox');
    checkboxes.forEach(cb => {
      const id = cb.value;
      const label = cb.closest('label');
      const checked = oldChecked.includes(id) || oldChecked.includes(parseInt(id, 10));
      cb.checked = checked;
      label.dataset.selected = checked ? '1' : '0';
    });
  }

  let modalInstance = null;

  function updateSelectedState() {
    const checkboxes = daftarKelas.querySelectorAll('.kelas-checkbox');
    checkboxes.forEach(cb => {
      cb.checked = cb.closest('label').dataset.selected === '1';
    });
  }

  function syncSelectedFromCheckboxes() {
    const items = daftarKelas.querySelectorAll('label[data-nama]');
    items.forEach(label => {
      const cb = label.querySelector('.kelas-checkbox');
      label.dataset.selected = cb.checked ? '1' : '0';
    });
  }

  function syncHiddenInputs() {
    const container = document.getElementById('hidden-kelas-inputs');
    if (!container) return;
    container.innerHTML = '';
    const checkboxes = daftarKelas.querySelectorAll('.kelas-checkbox:checked');
    checkboxes.forEach(cb => {
      const input = document.createElement('input');
      input.type = 'hidden';
      input.name = 'kelas_ids[]';
      input.value = cb.value;
      container.appendChild(input);
    });
  }

  function renderTags() {
    if (!tagsContainer) return;
    tagsContainer.innerHTML = '';
    const checkboxes = daftarKelas.querySelectorAll('.kelas-checkbox:checked');
    checkboxes.forEach(cb => {
      const id = cb.value;
      const nama = cb.closest('label').querySelector('span').textContent.trim();
      const badge = document.createElement('span');
      badge.className = 'badge bg-primary text-white d-inline-flex align-items-center gap-1';
      badge.innerHTML = `${nama} <button type="button" class="btn btn-sm btn-link text-white p-0 ms-1 lh-1" data-hapus-kelas="${id}">&times;</button>`;
      tagsContainer.appendChild(badge);
    });

    const count = tagsContainer.querySelectorAll('button[data-hapus-kelas]').length;
    const text = count > 0 ? `${count} kelas dipilih` : 'Pilih kelas...';
    labelPilihHeader.textContent = text;
    if (labelPilihForm) {
      labelPilihForm.textContent = text;
    }

    tagsContainer.querySelectorAll('button[data-hapus-kelas]').forEach(btn => {
      btn.addEventListener('click', function () {
        const id = this.dataset.hapusKelas;
        const cb = daftarKelas.querySelector(`.kelas-checkbox[value="${id}"]`);
        if (cb) {
          cb.checked = false;
          cb.closest('label').dataset.selected = '0';
        }
        renderTags();
        syncHiddenInputs();
      });
    });
  }

  function openModal() {
    syncSelectedFromCheckboxes();
    updateSelectedState();
    renderTags();
    syncHiddenInputs();
    searchInput.value = '';
    applyFilters();
    modalInstance.show();
  }

  function applyFilters() {
    const keyword = (searchInput.value || '').toLowerCase();
    const fase = faseSelector ? faseSelector.value : '';
    const items = daftarKelas.querySelectorAll('label[data-nama]');
    items.forEach(label => {
      const nama = label.dataset.nama || '';
      const itemFase = label.dataset.fase || '';
      const matchKeyword = nama.includes(keyword);
      const matchFase = !fase || itemFase === fase;
      label.style.display = (matchKeyword && matchFase) ? '' : 'none';
    });
  }

  if (btnPilihHeader && modalEl) {
    modalInstance = new bootstrap.Modal(modalEl);

    btnPilihHeader.addEventListener('click', openModal);
    if (btnPilihForm) btnPilihForm.addEventListener('click', openModal);

    btnOk.addEventListener('click', function () {
      syncSelectedFromCheckboxes();
      renderTags();
      syncHiddenInputs();
      modalInstance.hide();
    });

    searchInput.addEventListener('input', function () {
      applyFilters();
    });

    if (faseSelector) {
      faseSelector.addEventListener('change', function () {
        if (modalInstance && modalEl.classList.contains('show')) {
          applyFilters();
        }
      });
    }
  }

  function filterKelas(keyword) {
    const items = daftarKelas.querySelectorAll('label[data-nama]');
    items.forEach(label => {
      const nama = label.dataset.nama || '';
      label.style.display = nama.includes(keyword) ? '' : 'none';
    });
  }

  initCheckboxes();
  renderTags();
  syncHiddenInputs();
  syncPreviewFields();
  bindOnlyOfficeSync();
});

function syncPreviewFields() {
  const fieldMap = {
    judul: 'Judul',
    fase: 'Fase',
    kelas: 'Kelas',
    status: 'Status',
    alokasi_waktu: 'Alokasi Waktu',
  };

  const previewNodes = {};
  Object.keys(fieldMap).forEach(key => {
    previewNodes[key] = document.getElementById('preview-field-' + key);
  });

  const updatePreview = () => {
    const judul = document.getElementById('input-title')?.value || '-';
    const fase = document.getElementById('fase-selector')?.value || '-';
    const status = document.querySelector('select[name="status"]')?.value || '-';
    const alokasiWaktu = document.getElementById('input-duration')?.value || '-';
    const checkedKomponen = Array.from(document.querySelectorAll('input[name="komponen_nilai_ids[]"]:checked'))
      .map(el => el.closest('label')?.textContent.trim())
      .filter(Boolean);

    if (previewNodes.judul) previewNodes.judul.textContent = judul || '-';
    if (previewNodes.fase) previewNodes.fase.textContent = fase || '-';
    if (previewNodes.kelas) previewNodes.kelas.textContent = checkedKomponen.length > 0 ? checkedKomponen.join(', ') : 'Belum dipilih';
    if (previewNodes.status) previewNodes.status.textContent = status || '-';
    if (previewNodes.alokasi_waktu) previewNodes.alokasi_waktu.textContent = alokasiWaktu || '-';
    if (previewNodes.komponen_nilai) previewNodes.komponen_nilai.textContent = checkedKomponen.length > 0 ? checkedKomponen.join(', ') : '-';
  };

  document.getElementById('input-title')?.addEventListener('input', updatePreview);
  document.getElementById('fase-selector')?.addEventListener('input', updatePreview);
  document.getElementById('input-duration')?.addEventListener('input', updatePreview);
  document.querySelector('select[name="status"]')?.addEventListener('change', updatePreview);
  document.querySelectorAll('input[name="komponen_nilai_ids[]"]').forEach(el => {
    el.addEventListener('change', updatePreview);
  });

  document.getElementById('btn-pilih-kelas-header')?.addEventListener('click', () => {
    setTimeout(updatePreview, 100);
  });

  updatePreview();
}

function bindOnlyOfficeSync() {
  const titleInput = document.getElementById('input-title');
  const durationInput = document.getElementById('input-duration');
  const statusSelect = document.querySelector('select[name="status"]');
  const faseSelector = document.getElementById('fase-selector');

  let syncTimer = null;
  const scheduleSync = () => {
    if (syncTimer) clearTimeout(syncTimer);
    syncTimer = setTimeout(syncToOnlyOffice, 400);
  };

  const syncToOnlyOffice = () => {
    const editor = window.onlyOfficeEditor;
    if (!editor) return;

    const title = titleInput?.value || '';
    const duration = durationInput?.value || '';
    const status = statusSelect?.value || '';
    const fase = faseSelector?.value || '';

    try {
      if (typeof editor.setDocumentTitle === 'function') {
        editor.setDocumentTitle(title || 'Rencana Pembelajaran');
      }
    } catch (e) {
      console.warn('Gagal update judul OnlyOffice:', e);
    }

    try {
      if (typeof editor.serviceCommand === 'function') {
        editor.serviceCommand('info', {
          key: 'rpp-meta',
          userdata: JSON.stringify({
            title,
            fase,
            status,
            alokasi_waktu: duration,
          }),
        });
      }
    } catch (e) {
      console.warn('Gagal kirim metadata OnlyOffice:', e);
    }
  };

  titleInput?.addEventListener('input', scheduleSync);
  durationInput?.addEventListener('input', scheduleSync);
  statusSelect?.addEventListener('change', scheduleSync);
  faseSelector?.addEventListener('change', scheduleSync);

  document.addEventListener('onlyoffice:ready', () => {
    syncToOnlyOffice();
  });
}
</script>
@endpush

@push('css')
<style>
.rpp-editor-container {
    width: 100%;
    min-height: 80vh;
    overflow: hidden;
}
.rpp-editor-grid {
    display: grid;
    grid-template-columns: 20% 80%;
    gap: 20px;
    min-height: auto;
}
.rpp-editor-grid .form-panel,
.rpp-editor-grid .preview-panel {
    min-height: 720px;
}
.rpp-editor-grid .preview-panel {
    display: flex;
    flex-direction: column;
    min-height: calc(100vh - 220px);
}
.rpp-editor-grid .preview-panel > div:last-child {
    flex: 1 1 auto;
    min-height: 0;
}
@media (max-width: 991px) {
    .rpp-editor-grid {
        grid-template-columns: 1fr;
    }
    .rpp-editor-grid .form-panel,
    .rpp-editor-grid .preview-panel {
        min-height: auto;
    }
    .rpp-editor-grid .preview-panel {
        min-height: 520px;
    }
    .info-umum-row,
    .form-card-section {
        width: 100% !important;
        max-width: 100% !important;
        margin-left: 0 !important;
        margin-right: 0 !important;
    }
}

.info-umum-row {
    display: grid;
    grid-template-columns: minmax(0, 1fr) minmax(140px, 1fr);
    gap: 12px;
    margin-bottom: 12px;
    align-items: end;
    width: 100%;
    max-width: 520px;
}

.info-umum-row > div {
    width: 100%;
}

.info-umum-row button {
    width: 100%;
    min-width: 140px;
}

.form-card-section {
    width: 100%;
    max-width: 560px;
    margin-left: auto;
    margin-right: auto;
}
</style>
@endpush
