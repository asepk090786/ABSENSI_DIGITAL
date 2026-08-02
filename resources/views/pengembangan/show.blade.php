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
                    <form method="POST" action="{{ route('pengembangan.generate_certificates',$item->id) }}">
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
                <form id="bulkDeleteForm" method="POST" action="{{ url('pengembangan/bulk-hapus-sertifikat') }}" onsubmit="return confirmBulkDelete(event)">
                    @csrf
                    <input type="hidden" name="pengembangan_id" value="{{ $item->id }}">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="card-title fw-semibold m-0">
                            <i class="ti ti-certificate me-1"></i> Daftar Sertifikat
                            <span class="badge bg-secondary ms-2">{{ $certificates->count() }}</span>
                        </h5>
                        <button type="submit" id="deleteSelectedBtn" class="btn btn-danger btn-sm d-none">
                            <i class="ti ti-trash me-1"></i> Hapus Terpilih
                        </button>
                    </div>
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
                                            <td><span class="badge bg-{{ $cert->peserta_type === 'guru' ? 'info' : 'warning' }}">{{ strtoupper($cert->peserta_type) }}</span></td>
                                            <td>{{ $cert->nomor_sertifikat ?? '-' }}</td>
                                            <td><code class="small">{{ Str::limit($cert->barcode, 12) }}</code></td>
                                            <td>{{ optional($cert->created_at)->format('d-m-Y H:i') }}</td>
                                            <td>
                                                <div class="d-flex gap-1">
                                                    <a href="{{ route('pengembangan.certificates.download', $cert->id) }}" class="btn btn-sm btn-outline-success" title="Download">
                                                        <i class="ti ti-download"></i>
                                                    </a>
                                                    <button type="button" class="btn btn-sm btn-outline-danger" title="Hapus" onclick="deleteCertificate({{ $cert->id }})">
                                                        <i class="ti ti-trash"></i>
                                                    </button>
                                                </div>
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

{{-- Hidden forms for individual certificate deletion --}}
@foreach($certificates as $cert)
<form method="POST" action="{{ route('pengembangan.certificates.destroy', $cert->id) }}" id="delete-cert-form-{{ $cert->id }}" style="display:none">
    @csrf @method('DELETE')
</form>
@endforeach

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
