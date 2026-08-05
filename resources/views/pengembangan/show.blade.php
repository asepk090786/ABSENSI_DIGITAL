@extends('layouts.app')

@section('title','Detail Pengembangan')

@section('content')
<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-lg-10 col-xl-9">
            <div class="card mb-3">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h3 class="card-title fw-semibold m-0">Detail Kegiatan Pengembangan</h3>
                    <a href="{{ url()->previous() }}" class="btn btn-secondary btn-sm">Kembali</a>
                </div>
                <div class="card-body">
                    <h4 class="fw-bold">{{ $item->nama_kegiatan }}</h4>
                    <hr>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <div class="p-3 border rounded bg-light h-100">
                                <small class="text-muted">Jenis Kegiatan</small>
                                <div class="fw-semibold">{{ \App\Models\JenisKegiatan::where('kode', $item->jenis_kegiatan)->value('nama') ?? $item->jenis_kegiatan }}</div>
                                @if(!empty($item->tema_kegiatan))
                                    <div class="text-muted small mt-1">Tema: <span class="fw-semibold">{{ $item->tema_kegiatan }}</span></div>
                                @endif
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="p-3 border rounded bg-light h-100">
                                <small class="text-muted">Pemateri</small>
                                <div class="fw-semibold">
                                    @if(is_array($item->pemateri))
                                        {{ implode(', ', $item->pemateri) }}
                                    @else
                                        {{ $item->pemateri }}
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row g-3 mt-3">
                        <div class="col-md-6">
                            <div class="p-3 border rounded bg-light h-100">
                                <small class="text-muted">Tanggal Mulai</small>
                                <div class="fw-semibold">{{ optional($item->tanggal_mulai)->format('d-m-Y') ?? '-' }}</div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="p-3 border rounded bg-light h-100">
                                <small class="text-muted">Tanggal Selesai</small>
                                <div class="fw-semibold">{{ optional($item->tanggal_selesai)->format('d-m-Y') ?? '-' }}</div>
                            </div>
                        </div>
                    </div>

                    @if(!empty($item->deskripsi))
                    <div class="mt-3">
                        <div class="p-3 border rounded bg-light">
                            <small class="text-muted">Deskripsi</small>
                            <div>{!! nl2br(e($item->deskripsi)) !!}</div>
                        </div>
                    </div>
                    @endif
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <h5 class="card-title fw-semibold m-0">Peserta</h5>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('pengembangan.generate_certificates',$item->id) }}" enctype="multipart/form-data">
                        @csrf
                        <div class="row g-3">
                            <div class="col-md-7">
                                <div class="mb-2">
                                    <label class="form-label">Pilih Peserta untuk Sertifikat</label>
                                    <select multiple class="form-select" name="participant_ids[]" size="8">
                                        @foreach($participants as $p)
                                            <option value="{{ $p['id'] }}">
                                                {{ $p['name'] }} ({{ strtoupper($p['type']) }})
                                                @if(!empty($p['instansi'])) - {{ $p['instansi'] }}
                                                @endif
                                            </option>
                                        @endforeach
                                    </select>
                                    <small class="text-muted">Pilih beberapa peserta (Ctrl/Cmd + klik) atau biarkan kosong untuk semua.</small>
                                </div>
                            </div>
                            <div class="col-md-5">
                                <div class="mb-2">
                                    <label class="form-label">Template Sertifikat</label>
                                        <div class="d-flex justify-content-between align-items-center mb-1">
                                            <label class="form-label mb-0">Template Sertifikat</label>
                                            <a href="{{ route('pengembangan.templates.index') }}" class="small">Kelola Template</a>
                                        </div>
                                        <select name="template_id" class="form-select">
                                        <option value="" {{ empty($defaultTemplateId) ? 'selected' : '' }}>-- Default Template --</option>
                                        @foreach($templates as $t)
                                            <option value="{{ $t->id }}" {{ (string) $t->id === (string) ($defaultTemplateId ?? '') ? 'selected' : '' }}>{{ $t->nama }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="mb-2">
                                    <label class="form-label">Nomor Sertifikat</label>
                                    <input type="text" id="nomorSuratInput" name="nomor_surat" class="form-control" value="{{ old('nomor_surat', $defaultNomorSertifikat ?? '') }}" placeholder="Contoh: SRT-001/2026" />
                                    <small class="text-muted">Nomor ini akan disimpan pada setiap sertifikat yang dibuat.</small>
                                </div>
                                <div class="mb-2">
                                    <label class="form-label">Bukti Dukung: Daftar Hadir</label>
                                    <input type="file" name="bukti_dukung_daftar_hadir" class="form-control" accept=".pdf,image/*">
                                </div>
                                <div class="mb-2">
                                    <label class="form-label">Bukti Dukung: Dokumentasi</label>
                                    <input type="file" name="bukti_dukung_dokumentasi[]" class="form-control" accept=".pdf,image/*" multiple>
                                    <small class="text-muted">Pilih lebih dari satu file dokumentasi jika diperlukan.</small>
                                </div>
                                <div class="mb-2">
                                    <label class="form-label">Bukti Dukung: Materi</label>
                                    <input type="file" name="bukti_dukung_materi[]" class="form-control" accept=".pdf,.doc,.docx,image/*" multiple>
                                    <small class="text-muted">Pilih lebih dari satu file materi jika diperlukan.</small>
                                </div>
                                <div class="text-muted small mt-2">
                                    Gunakan tombol di bawah untuk menyimpan pengaturan nomor surat dan template yang dipilih tanpa langsung membuat sertifikat.
                                </div>
                                <div class="d-flex gap-2 mt-2">
                                    <button type="submit" name="save_only" value="1" class="btn btn-outline-primary">
                                        <i class="ti ti-device-floppy me-1"></i> Simpan Pengaturan
                                    </button>
                                    <button type="submit" class="btn btn-success">
                                        <i class="ti ti-file-certificate me-1"></i> Generate Certificates
                                    </button>
                                </div>
                            </div>
                        </div>
                    </form>

                    <hr class="my-4">

                    <div class="row g-3">
                        <div class="col-md-7">
                            <label class="form-label">Preview Sertifikat untuk peserta</label>
                            <div class="d-flex gap-2">
                                <select id="previewParticipant" class="form-select">
                                    <option value="">-- Pilih peserta --</option>
                                    @foreach($participants as $p)
                                        <option value="{{ $p['id'] }}">{{ $p['name'] }} ({{ strtoupper($p['type']) }})</option>
                                    @endforeach
                                </select>
                                <a id="previewBtn" href="#" target="_blank" class="btn btn-outline-secondary">Preview</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Daftar Sertifikat yang sudah digenerate --}}
            <div class="card mt-3">
                @php $visibleCount = $certificates->where('is_visible', true)->count(); @endphp
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title fw-semibold m-0">
                        <i class="ti ti-certificate me-1"></i> Daftar Sertifikat
                        <span class="badge bg-secondary ms-2">{{ $certificates->count() }}</span>
                    </h5>
                    <div class="d-flex gap-2">
                        @if($certificates->isNotEmpty())
                        <form method="POST" action="{{ route('pengembangan.certificates.toggle_visibility_all', $item->id) }}" class="d-inline">
                            @csrf
                            <button type="submit" class="btn btn-sm {{ $visibleCount ? 'btn-warning' : 'btn-success' }}">
                                <i class="ti ti-eye{{ $visibleCount ? '-off' : '' }} me-1"></i>
                                {{ $visibleCount ? 'Sembunyikan Semua Sertifikat' : 'Tampilkan Semua Sertifikat' }}
                            </button>
                        </form>
                        @endif
                        <button type="submit" id="deleteSelectedBtn" form="bulkDeleteForm" class="btn btn-danger btn-sm d-none">
                            <i class="ti ti-trash me-1"></i> Hapus Terpilih
                        </button>
                    </div>
                </div>
                <form id="bulkDeleteForm" method="POST" action="{{ url('pengembangan/bulk-hapus-sertifikat') }}" onsubmit="return confirmBulkDelete(event)">
                    @csrf
                    <input type="hidden" name="pengembangan_id" value="{{ $item->id }}">
                    <div class="card-body p-0">
                        @if($certificates->isEmpty())
                            <div class="text-center text-muted py-4">
                                <i class="ti ti-certificate-off fs-1 d-block mb-2"></i>
                                Belum ada sertifikat yang digenerate.
                            </div>
                        @else
                            <div class="table-responsive">
                                <table class="table table-striped table-sm mb-0">
                                    <thead>
                                        <tr>
                                            <th style="width:40px">
                                                <input type="checkbox" id="selectAll" onchange="toggleSelectAll(this)">
                                            </th>
                                            <th style="width:50px">No</th>
                                            <th>Nama Peserta</th>
                                            <th>Tipe</th>
                                            <th>Nomor Sertifikat</th>
                                            <th>Barcode</th>
                                            <th>Tanggal Generate</th>
                                            <th style="width:140px">Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($certificates as $idx => $cert)
                                        <tr>
                                            <td>
                                                <input type="checkbox" name="certificate_ids[]" value="{{ $cert->id }}" class="cert-checkbox" onchange="toggleDeleteButton()">
                                            </td>
                                            <td>{{ $idx + 1 }}</td>
                                            <td>{{ $cert->participant_name ?? '-' }}</td>
                                            <td>
                                                @php
                                                    $badgeClass = 'secondary';
                                                    if ($cert->peserta_type === 'guru') {
                                                        $badgeClass = 'info';
                                                    } elseif ($cert->peserta_type === 'siswa') {
                                                        $badgeClass = 'warning';
                                                    } elseif ($cert->peserta_type === 'pemateri') {
                                                        $badgeClass = 'success';
                                                    }
                                                @endphp
                                                <span class="badge bg-{{ $badgeClass }} text-white">{{ strtoupper($cert->peserta_type) }}</span>
                                            </td>
                                            <td>{{ $cert->nomor_sertifikat ?? '-' }}</td>
                                            <td><code class="small">{{ Str::limit($cert->barcode, 12) }}</code></td>
                                            <td>{{ optional($cert->created_at)->format('d-m-Y H:i') }}</td>
                                            <td>
                                                @if($cert->is_visible)
                                                    <form method="POST" action="{{ route('pengembangan.certificates.toggle_visibility', $cert->id) }}" class="d-inline">
                                                        @csrf
                                                        <button type="submit" class="btn btn-sm btn-outline-warning" title="Sembunyikan Sertifikat">
                                                            <i class="ti ti-eye-off"></i>
                                                        </button>
                                                    </form>
                                                @else
                                                    <form method="POST" action="{{ route('pengembangan.certificates.toggle_visibility', $cert->id) }}" class="d-inline">
                                                        @csrf
                                                        <button type="submit" class="btn btn-sm btn-outline-success" title="Tampilkan Sertifikat">
                                                            <i class="ti ti-eye"></i>
                                                        </button>
                                                    </form>
                                                @endif
                                                <a href="{{ route('pengembangan.certificates.download', $cert->id) }}" class="btn btn-sm btn-outline-success" title="Download">
                                                    <i class="ti ti-download"></i>
                                                </a>
                                                <button type="button" class="btn btn-sm btn-outline-danger" title="Hapus" onclick="deleteCertificate({{ $cert->id }})">
                                                    <i class="ti ti-trash"></i>
                                                </button>
                                            </td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @endif
                    </div>
                </form>
            </div>
            {{-- End Daftar Sertifikat --}}

            <div class="card mt-3">
                <div class="card-header">
                    <h5 class="card-title fw-semibold m-0">Keterangan Bukti Dukung</h5>
                </div>
                <div class="card-body p-0">
                    @if($certificates->isEmpty())
                        <div class="text-center text-muted py-4">
                            <i class="ti ti-file-description-off fs-1 d-block mb-2"></i>
                            Belum ada bukti dukung karena sertifikat belum dibuat.
                        </div>
                    @else
                        <div class="table-responsive">
                            <table class="table table-bordered table-sm mb-0">
                                <thead>
                                    <tr>
                                        <th style="width:40px">No</th>
                                        <th>Peserta</th>
                                        <th>Daftar Hadir</th>
                                        <th>Dokumentasi</th>
                                        <th>Materi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($certificates as $idx => $cert)
                                        <tr>
                                            <td>{{ $idx + 1 }}</td>
                                            <td>{{ $cert->participant_name ?? ($cert->peserta_name ?? ($cert->peserta_type . ' #' . $cert->peserta_id)) }}</td>
                                            <td>
                                                @if($cert->bukti_dukung_daftar_hadir)
                                                    <a href="{{ asset('storage/' . $cert->bukti_dukung_daftar_hadir) }}" target="_blank">Lihat</a>
                                                @else
                                                    -
                                                @endif
                                            </td>
                                            <td>
                                                @if(!empty($cert->bukti_dukung_dokumentasi))
                                                    <ul class="list-unstyled mb-0">
                                                        @foreach((array) $cert->bukti_dukung_dokumentasi as $idx => $path)
                                                            <li>
                                                                <a href="{{ asset('storage/' . $path) }}" target="_blank">{{ basename($path) }}</a>
                                                                <button type="button" class="btn btn-link btn-sm p-0 ms-2 evidence-preview-btn" data-url="{{ asset('storage/' . $path) }}" data-name="{{ basename($path) }}">Preview</button>
                                                                <form action="{{ route('pengembangan.certificates.evidence.destroy', ['id' => $cert->id, 'type' => 'dokumentasi', 'index' => $idx]) }}" method="POST" class="d-inline ms-2">
                                                                    @csrf
                                                                    @method('DELETE')
                                                                    <button type="submit" class="btn btn-link btn-sm p-0 text-danger">Hapus</button>
                                                                </form>
                                                            </li>
                                                        @endforeach
                                                    </ul>
                                                @else
                                                    -
                                                @endif
                                            </td>
                                            <td>
                                                @if(!empty($cert->bukti_dukung_materi))
                                                    <ul class="list-unstyled mb-0">
                                                        @foreach((array) $cert->bukti_dukung_materi as $idx => $path)
                                                            <li>
                                                                <a href="{{ asset('storage/' . $path) }}" target="_blank">{{ basename($path) }}</a>
                                                                <button type="button" class="btn btn-link btn-sm p-0 ms-2 evidence-preview-btn" data-url="{{ asset('storage/' . $path) }}" data-name="{{ basename($path) }}">Preview</button>
                                                                <form action="{{ route('pengembangan.certificates.evidence.destroy', ['id' => $cert->id, 'type' => 'materi', 'index' => $idx]) }}" method="POST" class="d-inline ms-2">
                                                                    @csrf
                                                                    @method('DELETE')
                                                                    <button type="submit" class="btn btn-link btn-sm p-0 text-danger">Hapus</button>
                                                                </form>
                                                            </li>
                                                        @endforeach
                                                    </ul>
                                                @else
                                                    -
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
            </div>

{{-- Hidden forms for individual certificate deletion --}}
@foreach($certificates as $cert)
<form method="POST" action="{{ route('pengembangan.certificates.destroy', $cert->id) }}" id="delete-cert-form-{{ $cert->id }}" style="display:none">
    @csrf @method('DELETE')
</form>
@endforeach

        </div>
    </div>
</div>

<div class="modal fade" id="evidencePreviewModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="evidencePreviewModalLabel">Preview Bukti Dukung</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p id="evidencePreviewName" class="fw-semibold"></p>
                <div id="evidencePreviewContent" class="ratio ratio-16x9">
                    <iframe id="evidencePreviewFrame" src="" frameborder="0" allowfullscreen></iframe>
                </div>
            </div>
        </div>
    </div>
</div>

@push('js')
<script>
document.addEventListener('DOMContentLoaded', function(){
    const previewSelect = document.getElementById('previewParticipant');
    const previewBtn = document.getElementById('previewBtn');
    const templateSelect = document.querySelector('select[name="template_id"]');
    function updatePreviewHref(){
        const pid = previewSelect.value;
        if(!pid){ previewBtn.href = '#'; previewBtn.classList.add('disabled'); return; }
        const tid = templateSelect ? templateSelect.value : '';
        const nomorSuratInput = document.getElementById('nomorSuratInput');
        const nomorSurat = nomorSuratInput ? nomorSuratInput.value : '';
        let url = '/pengembangan/{{ $item->id }}/preview-certificate?participant_id=' + pid;
        if(tid) url += '&template_id=' + tid;
        if(nomorSurat) url += '&nomor_surat=' + encodeURIComponent(nomorSurat);
        previewBtn.classList.remove('disabled');
        previewBtn.href = url;
    }
    previewSelect?.addEventListener('change', updatePreviewHref);
    templateSelect?.addEventListener('change', updatePreviewHref);
    document.getElementById('nomorSuratInput')?.addEventListener('input', updatePreviewHref);

    document.querySelectorAll('.evidence-preview-btn').forEach(button => {
        button.addEventListener('click', function() {
            const url = this.dataset.url;
            const name = this.dataset.name;
            document.getElementById('evidencePreviewName').textContent = name;
            document.getElementById('evidencePreviewFrame').src = url;
            new bootstrap.Modal(document.getElementById('evidencePreviewModal')).show();
        });
    });

    // Inisialisasi tombol hapus terpilih
    toggleDeleteButton();
});

function toggleSelectAll(source) {
    document.querySelectorAll('.cert-checkbox').forEach(cb => cb.checked = source.checked);
    toggleDeleteButton();
}

function toggleDeleteButton() {
    const checked = document.querySelectorAll('.cert-checkbox:checked').length;
    const btn = document.getElementById('deleteSelectedBtn');
    if (checked > 0) {
        btn.classList.remove('d-none');
        btn.innerHTML = '<i class="ti ti-trash me-1"></i> Hapus Terpilih (' + checked + ')';
    } else {
        btn.classList.add('d-none');
    }
}

function confirmBulkDelete(e) {
    const submitter = e.submitter;
    if (!submitter || submitter.id !== 'deleteSelectedBtn') {
        return true;
    }

    e.preventDefault();
    const checkboxes = document.querySelectorAll('.cert-checkbox:checked');
    if (checkboxes.length === 0) {
        alert('Tidak ada sertifikat yang dipilih.');
        return false;
    }
    if (!confirm('Yakin ingin menghapus ' + checkboxes.length + ' sertifikat terpilih?')) {
        return false;
    }

    const form = document.getElementById('bulkDeleteForm');
    const formData = new FormData(form);

    fetch(form.action, {
        method: 'POST',
        body: formData
    })
    .then(response => {
        if (response.redirected) {
            window.location.href = response.url;
        } else if (response.ok) {
            window.location.reload();
        } else {
            return response.text().then(text => { throw new Error(text); });
        }
    })
    .catch(error => {
        alert('Gagal menghapus sertifikat. Silakan coba lagi.');
        console.error(error);
        window.location.reload();
    });

    return false;
}

function deleteCertificate(certId) {
    if (!confirm('Yakin ingin menghapus sertifikat ini?')) return;
    const form = document.getElementById('delete-cert-form-' + certId);
    if (!form) return;
    fetch(form.action, {
        method: 'POST',
        body: new FormData(form)
    })
    .then(response => {
        if (response.redirected) {
            window.location.href = response.url;
        } else if (response.ok) {
            window.location.reload();
        } else {
            window.location.reload();
        }
    })
    .catch(() => window.location.reload());
}
</script>
@endpush
@endsection
