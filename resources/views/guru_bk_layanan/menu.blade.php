@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row mb-3">
        <div class="col-12 d-flex justify-content-between align-items-center">
            <div>
                <h3 class="mb-1">Menu Kelas Binaan BK</h3>
                <p class="text-muted mb-0">Kelas: <strong>{{ $kelas->nama_kelas }}</strong></p>
            </div>
            <a href="{{ route('absensi.index') }}" class="btn btn-secondary">
                <i class="ti ti-arrow-left"></i> Kembali
            </a>
        </div>
    </div>

    <div class="row g-3">
        <div class="col-md-6 col-lg-3">
            <a href="{{ route('guru_bk_layanan.layanan', ['kelas' => $kelas->id]) }}" class="btn btn-outline-primary w-100 py-4" style="height: auto;">
                <div class="text-center">
                    <i class="ti ti-heart-handshake" style="font-size: 44px;"></i>
                    <div class="mt-2 fw-bold">Layanan BK</div>
                </div>
            </a>
        </div>

        <div class="col-md-6 col-lg-3">
            <a href="{{ route('guru_bk_layanan.daftar_hadir', ['kelas' => $kelas->id]) }}" class="btn btn-outline-success w-100 py-4" style="height: auto;">
                <div class="text-center">
                    <i class="ti ti-user-check" style="font-size: 44px;"></i>
                    <div class="mt-2 fw-bold">Daftar Hadir Layanan BK</div>
                </div>
            </a>
        </div>

        <div class="col-md-6 col-lg-3">
            <a href="{{ route('guru_bk_layanan.pembinaan', ['kelas' => $kelas->id]) }}" class="btn btn-outline-warning w-100 py-4" style="height: auto;">
                <div class="text-center">
                    <i class="ti ti-users-group" style="font-size: 44px;"></i>
                    <div class="mt-2 fw-bold">Pembinaan BK</div>
                </div>
            </a>
        </div>

        <div class="col-md-6 col-lg-3">
            <a href="{{ route('guru_bk_layanan.tindak_lanjut', ['kelas' => $kelas->id]) }}" class="btn btn-outline-danger w-100 py-4" style="height: auto;">
                <div class="text-center">
                    <i class="ti ti-arrow-forward-up" style="font-size: 44px;"></i>
                    <div class="mt-2 fw-bold">Tindak Lanjut</div>
                </div>
            </a>
        </div>

        <div class="col-md-6 col-lg-3">
            <button type="button" class="btn btn-outline-dark w-100 py-4 btn-print-preview" style="height: auto;" data-print-url="{{ route('guru_bk_layanan.layanan.print', ['kelas' => $kelas->id]) }}" data-bs-toggle="modal" data-bs-target="#printPreviewModal">
                <div class="text-center">
                    <i class="ti ti-printer" style="font-size: 44px;"></i>
                    <div class="mt-2 fw-bold">Print Output Layanan BK</div>
                </div>
            </button>
        </div>
    </div>
</div>

<div class="modal fade" id="printPreviewModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Preview Print Layanan BK</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-0" style="height: 75vh;">
                <iframe id="printPreviewFrame" src="" style="width:100%;height:100%;border:0;"></iframe>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                <button type="button" class="btn btn-primary" id="btnPrintFromPreview">
                    <i class="ti ti-printer me-1"></i>Print
                </button>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const previewButtons = document.querySelectorAll('.btn-print-preview');
    const frame = document.getElementById('printPreviewFrame');
    const printBtn = document.getElementById('btnPrintFromPreview');

    previewButtons.forEach(function (button) {
        button.addEventListener('click', function () {
            frame.src = this.getAttribute('data-print-url');
        });
    });

    if (printBtn) {
        printBtn.addEventListener('click', function () {
            if (frame && frame.contentWindow) {
                frame.contentWindow.focus();
                frame.contentWindow.print();
            }
        });
    }
});
</script>
@endsection
