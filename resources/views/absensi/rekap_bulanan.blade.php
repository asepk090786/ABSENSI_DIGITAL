@extends('layouts.app')

@section('title', 'Rekap Absensi Bulanan')

@section('content')
<div class="container-fluid">
    @php
        $bulanLabels = [
            1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
            5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
            9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
        ];
    @endphp
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-4">
        <div>
            <h3 class="mb-1">Rekap Absensi Bulanan</h3>
            <p class="text-muted mb-0">Daftar rekap absensi per bulan untuk setiap kelas yang diajar.</p>
        </div>
        <a href="{{ route('absensi.index') }}" class="btn btn-primary">
            <i class="ti ti-arrow-left me-1"></i> Kembali ke Absensi
        </a>
    </div>

    <div class="card mb-3">
        <div class="card-body">
            <form method="GET" action="{{ route('absensi.rekap-bulanan') }}" class="row g-3 align-items-end">
                <div class="col-md-4">
                    <label for="filter-kelas" class="form-label">Kelas</label>
                    <select id="filter-kelas" name="kelas_id" class="form-select">
                        <option value="">Semua kelas</option>
                        @foreach($kelasList as $kelas)
                            <option value="{{ $kelas->id }}" {{ (string) $kelasId === (string) $kelas->id ? 'selected' : '' }}>{{ $kelas->nama_kelas }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label for="filter-bulan" class="form-label">Bulan</label>
                    <select id="filter-bulan" name="bulan" class="form-select">
                        <option value="">Semua bulan</option>
                        @foreach($bulanLabels as $nomor => $label)
                            <option value="{{ $nomor }}" {{ (int) $bulan === $nomor ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-auto">
                    <button type="submit" class="btn btn-primary"><i class="ti ti-filter me-1"></i>Filter</button>
                    <a href="{{ route('absensi.rekap-bulanan') }}" class="btn btn-outline-secondary">Reset</a>
                </div>
            </form>
        </div>
    </div>

    @if(($rekapBulanan ?? collect())->isEmpty())
        <div class="alert alert-info">
            <i class="ti ti-info-circle me-2"></i>
            Belum ada data rekap absensi bulanan untuk filter yang dipilih.
        </div>
    @else
        <div class="card">
            <div class="card-header border-0 pt-3 pb-2">
                <h5 class="card-title fw-semibold mb-1">Rekap per Kelas & Bulan</h5>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-vcenter table-hover table-bordered mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Kelas</th>
                                <th>Bulan</th>
                                <th class="text-center">Pertemuan</th>
                                <th class="text-center">Hadir</th>
                                <th class="text-center">Terlambat</th>
                                <th class="text-center">Izin</th>
                                <th class="text-center">Sakit</th>
                                <th class="text-center">Alpha</th>
                                <th class="text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($rekapBulanan as $rekap)
                                <tr>
                                    <td class="fw-medium">{{ $rekap->nama_kelas }}</td>
                                    <td>{{ $bulanLabels[$rekap->bulan ?? 0] ?? ($rekap->bulan ?? '-') }} {{ $rekap->tahun }}</td>
                                    <td class="text-center">{{ (int) ($rekap->total_pertemuan ?? 0) }}</td>
                                    <td class="text-center text-success fw-semibold">{{ (int) ($rekap->hadir ?? 0) }}</td>
                                    <td class="text-center text-warning fw-semibold">{{ (int) ($rekap->terlambat ?? 0) }}</td>
                                    <td class="text-center text-info fw-semibold">{{ (int) ($rekap->izin ?? 0) }}</td>
                                    <td class="text-center text-primary fw-semibold">{{ (int) ($rekap->sakit ?? 0) }}</td>
                                    <td class="text-center text-danger fw-semibold">{{ (int) ($rekap->alpha ?? 0) }}</td>
                                    <td class="text-center text-nowrap">
                                        @php $actionParams = ['kelas_id' => $rekap->kelas_id, 'bulan' => $rekap->bulan, 'tahun' => $rekap->tahun]; @endphp
                                        <button type="button" class="btn btn-sm btn-primary btn-rekap-detail" data-url="{{ route('absensi.rekap-bulanan.detail', $actionParams) }}" data-title="{{ $rekap->nama_kelas }} - {{ $bulanLabels[$rekap->bulan] ?? $rekap->bulan }} {{ $rekap->tahun }}">
                                            <i class="ti ti-eye me-1"></i>Tampilkan Rekap
                                        </button>
                                        <a class="btn btn-sm btn-outline-success" href="{{ route('absensi.rekap-bulanan.export', $actionParams) }}" target="_blank" rel="noopener">
                                            <i class="ti ti-file-spreadsheet me-1"></i>Excel
                                        </a>
                                        <a class="btn btn-sm btn-outline-danger" href="{{ route('absensi.rekap-bulanan.print', $actionParams) }}" target="_blank" rel="noopener">
                                            <i class="ti ti-file-type-pdf me-1"></i>PDF
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    @endif
</div>

<div class="modal fade" id="modalRekapBulanan" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalRekapBulananTitle">Detail Rekap Absensi</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
            </div>
            <div class="modal-body p-0" style="min-height: 420px;"><iframe id="modalRekapBulananFrame" title="Detail rekap absensi" class="w-100 border-0" style="height: 65vh;"></iframe></div>
        </div>
    </div>
</div>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const modalElement = document.getElementById('modalRekapBulanan');
    const frame = document.getElementById('modalRekapBulananFrame');
    const title = document.getElementById('modalRekapBulananTitle');
    document.querySelectorAll('.btn-rekap-detail').forEach(function (button) {
        button.addEventListener('click', function () {
            frame.src = button.dataset.url;
            title.textContent = 'Rekap Absensi ' + button.dataset.title;
            bootstrap.Modal.getOrCreateInstance(modalElement).show();
        });
    });
    modalElement.addEventListener('hidden.bs.modal', function () { frame.src = ''; });
});
</script>
@endsection
