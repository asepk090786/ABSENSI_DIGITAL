@extends('layouts.app', ['pageSlug' => 'modul_ajar'])

@section('title', 'Editor Modul - ' . ($item->judul ?? 'Modul Ajar'))

@section('content')
<div class="card shadow-sm border-0 mb-0">
    <div class="card-header border-0">
        <div class="d-flex justify-content-between align-items-start gap-3 flex-wrap">
            <div>
                <h3 class="fw-semibold mb-1">Editor Modul</h3>
                <p class="text-muted mb-0">Edit dokumen modul ajar menggunakan OnlyOffice Docs.</p>
            </div>
            <div class="d-flex gap-2">
                <a href="{{ route('rencana_pembelajaran.index') }}" class="btn btn-outline-secondary btn-sm">
                    <i class="ti ti-arrow-left me-1"></i>Kembali ke Daftar
                </a>
            </div>
        </div>
    </div>
    <div class="card-body p-0 position-relative">
        <div id="editor-wrapper" style="width:100%; height:calc(100vh - 180px); min-height:600px; position:relative;">
            <div id="onlyoffice-editor" style="width:100%; height:100%;"></div>
            
            <div id="editor-loading" class="position-absolute top-0 start-0 w-100 h-100 d-flex align-items-center justify-content-center bg-white" style="z-index:10;">
                <div class="text-center">
                    <div class="spinner-border text-primary mb-3" role="status"></div>
                    <p class="text-muted mb-0">Memuat editor OnlyOffice...</p>
                </div>
            </div>
            
            <div id="editor-error" class="position-absolute top-0 start-0 w-100 h-100 d-none align-items-center justify-content-center bg-white" style="z-index:10;">
                <div class="text-center">
                    <i class="ti ti-alert-octagon text-danger" style="font-size:48px;"></i>
                    <h4 class="mt-3">Gagal Memuat Editor</h4>
                    <p class="text-muted mb-0" id="editor-error-message">Tidak dapat menghubungkan ke editor dokumen. Silakan coba lagi.</p>
                    <button type="button" class="btn btn-primary mt-3" onclick="location.reload()">Muat Ulang</button>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('js')
<script src="{{ $onlyOfficeServerUrl }}/web-apps/apps/api/documents/api.js"></script>
<script>
(function() {
    const fileUrl = @json($fileUrl);
    const fileName = @json($item->judul ? $item->judul . '.' . $fileType : 'modul-ajar.' . $fileType);
    const docKey = @json($documentKey);

    const config = {
        type: 'desktop',
        width: '100%',
        height: 'calc(100vh - 180px)',
        document: {
            fileType: @json($fileType),
            key: docKey,
            title: fileName,
            url: fileUrl,
        },
        documentType: 'word',
        editorConfig: {
            lang: 'id',
            callbackUrl: @json($callbackUrl),
            user: {
                id: @json((string) auth()->id()),
                name: @json(auth()->user()->name ?? 'User'),
            },
        },
    };

    @if($onlyOfficeJwtToken)
    config.token = @json($onlyOfficeJwtToken);
    @endif

    function showError(message) {
        console.error("ONLYOFFICE Error:", message);
        const loading = document.getElementById('editor-loading');
        const errorDiv = document.getElementById('editor-error');
        const errorMessage = document.getElementById('editor-error-message');

        if (loading) loading.style.display = 'none';
        if (errorDiv) {
            errorDiv.classList.remove('d-none');
            errorDiv.classList.add('d-flex');
        }
        if (errorMessage && message) {
            errorMessage.textContent = message;
        }
    }

    function initEditor() {
        const loading = document.getElementById('editor-loading');
        if (loading) {
            loading.style.display = 'none';
        }

        if (typeof DocsAPI === 'undefined' || !DocsAPI.DocEditor) {
            showError('Tidak dapat memuat editor OnlyOffice. Pastikan URL OnlyOffice dapat diakses.');
            return;
        }

        try {
            new DocsAPI.DocEditor('onlyoffice-editor', config);
        } catch (e) {
            console.error("ONLYOFFICE Init Crash:", e);
            showError('Terjadi kesalahan saat menginisialisasi dokumen. Silakan coba lagi.');
        }
    }

    function waitForDocsAPI(maxAttempts) {
        maxAttempts = maxAttempts || 60;
        let attempts = 0;
        const interval = setInterval(function() {
            attempts++;
            if (typeof DocsAPI !== 'undefined' && DocsAPI.DocEditor) {
                clearInterval(interval);
                initEditor();
            } else if (attempts >= maxAttempts) {
                clearInterval(interval);
                showError('Waktu habis (Timeout). Gagal mengunduh API dari server OnlyOffice.');
            }
        }, 500);
    }

    if (document.readyState === 'complete' || document.readyState === 'interactive') {
        waitForDocsAPI();
    } else {
        window.addEventListener('DOMContentLoaded', function() {
            waitForDocsAPI();
        });
    }
})();
</script>
@endpush
