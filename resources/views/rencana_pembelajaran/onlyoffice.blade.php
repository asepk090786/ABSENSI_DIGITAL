@extends('layouts.app', ['pageSlug' => 'rencana_pembelajaran'])

@section('title', 'Edit Rencana Pembelajaran dengan OnlyOffice')

@section('content')
<div class="d-flex align-items-center justify-content-between mb-3">
    <div>
        <h4 class="card-title mb-1">Edit Rencana Pembelajaran</h4>
        <p class="text-muted mb-0">Mengedit dokumen Word asli menggunakan OnlyOffice. Klik tombol simpan untuk mengirim versi terbaru ke aplikasi web.</p>
    </div>
    <div>
        <a href="{{ route('rencana_pembelajaran.show', $item->id) }}" class="btn btn-secondary btn-sm">
            <i class="ti ti-arrow-left me-1"></i>Kembali ke RPP
        </a>
    </div>
</div>

<div class="card border-0 shadow-sm mb-3">
    <div class="card-body d-flex flex-wrap justify-content-between align-items-center gap-2">
        <div>
            <h6 class="mb-1">SAVE</h6>
            <p class="text-muted mb-0 small">Saat dokumen selesai diedit, tekan tombol di bawah agar perubahan tersimpan ke sistem.</p>
        </div>
        <div class="d-flex align-items-center gap-2">
            <span id="onlyoffice-save-status" class="text-muted small">Menunggu editor OnlyOffice siap...</span>
            <button type="button" id="onlyoffice-save-btn" class="btn btn-success btn-sm">
                <i class="ti ti-device-floppy me-1"></i>Simpan ke Web
            </button>
        </div>
    </div>
</div>

<div class="border rounded overflow-hidden onlyoffice-page-shell">
    <x-onlyoffice
        :file-url="$fileUrl"
        :callback-url="$callbackUrl"
        :file-type="$fileType"
        :title="$item->judul"
        :readonly="false"
        :token="$onlyOfficeJwtToken"
    />
</div>
@endsection

@push('css')
<style>
    .onlyoffice-page-shell {
        min-height: 720px;
    }
</style>
@endpush

@push('js')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const saveButton = document.getElementById('onlyoffice-save-btn');
    const saveStatus = document.getElementById('onlyoffice-save-status');

    if (!saveButton || !saveStatus) {
        return;
    }

    const updateStatus = function (message, typeClass = 'text-muted') {
        saveStatus.className = 'small ' + typeClass;
        saveStatus.textContent = message;
    };

    const saveDocumentToWeb = function () {
        const editor = window.onlyOfficeEditor;
        if (!editor) {
            updateStatus('Editor OnlyOffice belum siap.', 'text-warning');
            return;
        }

        try {
            updateStatus('Menyimpan ke web...', 'text-info');
            if (typeof editor.save === 'function') {
                editor.save();
            } else if (typeof editor.serviceCommand === 'function') {
                editor.serviceCommand('save');
            } else {
                updateStatus('Editor tidak menyediakan perintah simpan.', 'text-danger');
            }
        } catch (error) {
            console.error('Gagal menjalankan perintah simpan OnlyOffice:', error);
            updateStatus('Gagal menjalankan perintah simpan.', 'text-danger');
        }
    };

    saveButton.addEventListener('click', function (event) {
        event.preventDefault();
        saveDocumentToWeb();
    });

    window.addEventListener('onlyoffice:ready', function () {
        updateStatus('Editor siap. Klik Simpan untuk mengirim dokumen ke web.', 'text-success');
    });

    window.addEventListener('onlyoffice:save-requested', function () {
        updateStatus('Dokumen sedang disimpan ke server...', 'text-info');
    });

    window.addEventListener('onlyoffice:document-state', function (event) {
        const state = event?.detail?.state || '';
        if (state === 'Saved') {
            updateStatus('Dokumen berhasil disimpan ke web.', 'text-success');
        } else if (state === 'Save' || state === 'Saving') {
            updateStatus('Dokumen sedang disimpan ke server...', 'text-info');
        }
    });

    if (window.onlyOfficeEditor) {
        updateStatus('Editor siap. Klik Simpan untuk mengirim dokumen ke web.', 'text-success');
    }
});
</script>
@endpush
