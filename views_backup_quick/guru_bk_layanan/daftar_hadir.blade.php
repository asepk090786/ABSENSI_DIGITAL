@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h3 class="card-title mb-0">Daftar Hadir Layanan BK - {{ $kelas->nama_kelas }}</h3>
                    <div class="d-flex gap-2">
                        <button type="button" class="btn btn-dark btn-sm" data-toggle="modal" data-target="#printPreviewModalDaftarHadir" id="btnOpenPrintPreviewDaftarHadir" data-print-url="{{ route('guru_bk_layanan.daftar_hadir.print', ['kelas' => $kelas->id, 'tanggal' => $selectedTanggal]) }}">
                            <i class="ti ti-printer"></i> Print Output
                        </button>
                        <a href="{{ route('guru_bk_layanan.layanan', ['kelas' => $kelas->id]) }}" class="btn btn-primary btn-sm">
                            <i class="ti ti-plus"></i> Input Layanan BK
                        </a>
                        <a href="{{ route('guru_bk_layanan.menu', ['kelas' => $kelas->id]) }}" class="btn btn-secondary btn-sm">
                            <i class="ti ti-arrow-left"></i> Kembali ke Menu
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <div class="alert alert-info">
                        <i class="ti ti-info-circle me-2"></i>
                        Data kehadiran layanan BK pada halaman ini diambil otomatis dari input pada menu <strong>Layanan BK</strong>.
                    </div>

                    <form method="GET" action="{{ route('guru_bk_layanan.daftar_hadir', ['kelas' => $kelas->id]) }}" class="row g-2 align-items-end mb-3">
                        <div class="col-12 col-md-4 col-lg-3">
                            <label for="tanggal" class="form-label mb-1">Filter Tanggal</label>
                            <input type="date" id="tanggal" name="tanggal" class="form-control" value="{{ $selectedTanggal }}">
                        </div>
                        <div class="col-auto">
                            <button type="submit" class="btn btn-primary">
                                <i class="ti ti-search me-1"></i>Tampilkan
                            </button>
                        </div>
                        @if(!empty($selectedTanggal))
                        <div class="col-auto">
                            <a href="{{ route('guru_bk_layanan.daftar_hadir', ['kelas' => $kelas->id]) }}" class="btn btn-light border">
                                <i class="ti ti-refresh me-1"></i>Reset
                            </a>
                        </div>
                        @endif
                    </form>

                    @php
                        $totalKehadiran = ($daftarHadirItems ?? collect())->count();
                        $totalSiswaUnik = ($daftarHadirItems ?? collect())->pluck('siswa_id')->filter()->unique()->count();
                    @endphp

                    <div class="d-flex flex-wrap gap-2 mb-3">
                        <span class="badge bg-primary">Total Kehadiran Layanan: {{ $totalKehadiran }}</span>
                        <span class="badge bg-success">Total Siswa Terlayani: {{ $totalSiswaUnik }}</span>
                    </div>

                    @if(($daftarHadirItems ?? collect())->isEmpty())
                        <div class="alert alert-light border mb-0">
                            Belum ada data kehadiran layanan BK.
                        </div>
                    @else
                        <div class="table-responsive">
                            <table class="table table-striped table-hover">
                                <thead>
                                    <tr>
                                        <th>No</th>
                                        <th>Nama Siswa</th>
                                        <th>Kelas</th>
                                        <th>Tanggal</th>
                                        <th>Jenis Layanan</th>
                                        <th>Keterangan</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($daftarHadirItems as $index => $item)
                                        <tr>
                                            <td>{{ $index + 1 }}</td>
                                            <td>{{ $item->siswa->nama ?? '-' }}</td>
                                            <td>{{ $kelas->nama_kelas }}</td>
                                            <td>{{ $item->tanggal ? $item->tanggal->format('d/m/Y') : '-' }}</td>
                                            <td>{{ $item->jenis_layanan ?? '-' }}</td>
                                            <td>{{ $item->deskripsi_layanan ?? '-' }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="printPreviewModalDaftarHadir" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Preview Print Daftar Hadir Layanan BK</h5>
                <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span></button>
            </div>
            <div class="modal-body p-0" style="height: 75vh;">
                <iframe id="printPreviewFrameDaftarHadir" src="" style="width:100%;height:100%;border:0;"></iframe>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Tutup</button>
                <button type="button" class="btn btn-primary" id="btnPrintFromPreviewDaftarHadir">
                    <i class="ti ti-printer me-1"></i>Print
                </button>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const openBtn = document.getElementById('btnOpenPrintPreviewDaftarHadir');
    const frame = document.getElementById('printPreviewFrameDaftarHadir');
    const printBtn = document.getElementById('btnPrintFromPreviewDaftarHadir');

    if (openBtn && frame) {
        openBtn.addEventListener('click', function () {
            frame.src = this.getAttribute('data-print-url');
        });
    }

    if (printBtn && frame) {
        printBtn.addEventListener('click', function () {
            if (frame.contentWindow) {
                frame.contentWindow.focus();
                frame.contentWindow.print();
            }
        });
    }
});
</script>
@endsection
