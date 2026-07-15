@extends('layouts.app')

@section('content')
<div class="container-fluid">
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="close" data-bs-dismiss="alert"><span aria-hidden="true">&times;</span></button>
        </div>
    @endif

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h3 class="card-title fw-semibold m-0">Pembinaan BK - {{ $kelas->nama_kelas }}</h3>
                    <a href="{{ route('guru_bk_layanan.menu', ['kelas' => $kelas->id]) }}" class="btn btn-secondary btn-sm">
                        <i class="ti ti-arrow-left"></i> Kembali ke Menu
                    </a>
                </div>
                <div class="card-body">
                    <div class="mb-4">
                        <h5 class="mb-2">Akumulasi Terlambat Bulan Ini</h5>
                        @if(($akumulasiTerlambatBulanan ?? collect())->isEmpty())
                            <div class="alert alert-light border mb-0">Belum ada data keterlambatan pada bulan ini.</div>
                        @else
                            <div class="table-responsive">
                                <table class="table table-sm table-bordered">
                                    <thead class="table-light">
                                        <tr>
                                            <th>No</th>
                                            <th>Siswa</th>
                                            <th>Total Terlambat</th>
                                            <th>Total Menit</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($akumulasiTerlambatBulanan as $index => $row)
                                            <tr>
                                                <td>{{ $index + 1 }}</td>
                                                <td>{{ $row->nama_siswa }}</td>
                                                <td><span class="badge" style="background:#f59e0b;color:#fff;">{{ $row->total_terlambat }}x</span></td>
                                                <td><span class="badge bg-danger">{{ $row->total_menit_terlambat ?? 0 }} menit</span></td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @endif
                    </div>

                    <form action="{{ route('guru_bk_layanan.pembinaan.store', ['kelas' => $kelas->id]) }}" method="POST" enctype="multipart/form-data">
                        @csrf

                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Nama Siswa (Kelas Binaan) <span class="text-danger">*</span></label>
                                <select name="siswa_id" id="siswa_id" class="form-select @error('siswa_id') is-invalid @enderror" required>
                                    <option value="">-- Pilih Siswa --</option>
                                    @foreach($siswaList as $siswa)
                                        <option value="{{ $siswa->id }}" {{ (string) old('siswa_id') === (string) $siswa->id ? 'selected' : '' }}>{{ $siswa->nama }}</option>
                                    @endforeach
                                </select>
                                @error('siswa_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Nama Wali Kelas</label>
                                <input type="text" id="wali_kelas_nama" class="form-control" value="{{ $waliKelasNama }}" readonly>
                            </div>

                            <div class="col-12">
                                <div class="card border">
                                    <div class="card-header py-2">
                                        <strong>Rekap Absensi (Otomatis)</strong>
                                    </div>
                                    <div class="card-body py-3">
                                        <div class="row g-2">
                                            <div class="col-md-2"><input type="text" id="rekap_hadir" class="form-control" value="Hadir: 0" readonly></div>
                                            <div class="col-md-2"><input type="text" id="rekap_sakit" class="form-control" value="Sakit: 0" readonly></div>
                                            <div class="col-md-2"><input type="text" id="rekap_izin" class="form-control" value="Izin: 0" readonly></div>
                                            <div class="col-md-2"><input type="text" id="rekap_alpa" class="form-control" value="Alpa: 0" readonly></div>
                                            <div class="col-md-2"><input type="text" id="rekap_terlambat" class="form-control" value="Terlambat: 0" readonly></div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="col-12">
                                <label class="form-label">Deskripsi Permasalahan <span class="text-danger">*</span></label>
                                <textarea name="deskripsi_permasalahan" rows="3" class="form-control @error('deskripsi_permasalahan') is-invalid @enderror" required>{{ old('deskripsi_permasalahan') }}</textarea>
                                @error('deskripsi_permasalahan')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>

                            <div class="col-12">
                                <label class="form-label">Penanganan <span class="text-danger">*</span></label>
                                <textarea name="penanganan" rows="3" class="form-control @error('penanganan') is-invalid @enderror" required>{{ old('penanganan') }}</textarea>
                                @error('penanganan')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>

                            <div class="col-12">
                                <label class="form-label">Tindak Lanjut</label>
                                <textarea name="tindak_lanjut" rows="2" class="form-control @error('tindak_lanjut') is-invalid @enderror">{{ old('tindak_lanjut') }}</textarea>
                                @error('tindak_lanjut')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>

                            <div class="col-12">
                                <label class="form-label">Bukti Dukung Absensi</label>
                                <textarea name="bukti_dukung_absensi" id="bukti_dukung_absensi" rows="2" class="form-control @error('bukti_dukung_absensi') is-invalid @enderror">{{ old('bukti_dukung_absensi') }}</textarea>
                                @error('bukti_dukung_absensi')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Laporan Guru</label>
                                <textarea name="laporan_guru" id="laporan_guru" rows="2" class="form-control @error('laporan_guru') is-invalid @enderror">{{ old('laporan_guru') }}</textarea>
                                @error('laporan_guru')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Laporan Wali Kelas</label>
                                <textarea name="laporan_wali_kelas" rows="2" class="form-control @error('laporan_wali_kelas') is-invalid @enderror">{{ old('laporan_wali_kelas') }}</textarea>
                                @error('laporan_wali_kelas')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Upload Gambar Bukti Dukung</label>
                                <input type="file" name="bukti_dukung_files[]" class="form-control @error('bukti_dukung_files') is-invalid @enderror @error('bukti_dukung_files.*') is-invalid @enderror" accept="image/*" multiple>
                                <small class="text-muted">Bisa pilih lebih dari satu gambar.</small>
                                @error('bukti_dukung_files')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                                @error('bukti_dukung_files.*')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Foto dari Kamera</label>
                                <input type="file" name="bukti_dukung_kamera" class="form-control @error('bukti_dukung_kamera') is-invalid @enderror" accept="image/*" capture="environment">
                                <small class="text-muted">Gunakan kamera perangkat untuk ambil foto langsung.</small>
                                @error('bukti_dukung_kamera')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                        </div>

                        <div class="mt-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="ti ti-device-floppy me-1"></i>Simpan Pembinaan BK
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <div class="row mt-2">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h3 class="card-title fw-semibold m-0">Riwayat Pembinaan BK</h3>
                    <button type="button" class="btn btn-dark btn-sm" id="btnOpenPrintPreviewPembinaan" data-print-url="{{ route('guru_bk_layanan.pembinaan.print', ['kelas' => $kelas->id, 'filter_siswa_id' => $selectedSiswaId, 'tanggal_mulai' => $tanggalMulai, 'tanggal_selesai' => $tanggalSelesai]) }}" data-bs-toggle="modal" data-bs-target="#printPreviewModalPembinaan">
                        <i class="ti ti-printer"></i> Print Laporan
                    </button>
                </div>
                <div class="card-body">
                    <form method="GET" action="{{ route('guru_bk_layanan.pembinaan', ['kelas' => $kelas->id]) }}" class="row g-2 align-items-end mb-2">
                        <div class="col-md-4">
                            <label class="form-label mb-1">Filter Siswa</label>
                            <select name="filter_siswa_id" class="form-select">
                                <option value="">Semua Siswa</option>
                                @foreach($siswaList as $siswa)
                                    <option value="{{ $siswa->id }}" {{ (string) $selectedSiswaId === (string) $siswa->id ? 'selected' : '' }}>{{ $siswa->nama }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label mb-1">Tanggal Mulai</label>
                            <input type="date" name="tanggal_mulai" class="form-control" value="{{ $tanggalMulai }}">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label mb-1">Tanggal Selesai</label>
                            <input type="date" name="tanggal_selesai" class="form-control" value="{{ $tanggalSelesai }}">
                        </div>
                        <div class="col-auto">
                            <button type="submit" class="btn btn-primary"><i class="ti ti-search me-1"></i>Filter</button>
                        </div>
                        <div class="col-auto">
                            <a href="{{ route('guru_bk_layanan.pembinaan', ['kelas' => $kelas->id]) }}" class="btn btn-light border"><i class="ti ti-refresh me-1"></i>Reset</a>
                        </div>
                    </form>

                    @if(($pembinaanItems ?? collect())->isEmpty())
                        <div class="alert alert-light border mb-0">Belum ada data pembinaan BK.</div>
                    @else
                        <div class="table-responsive">
                            <table class="table table-vcenter table-hover table-tabler">
                                <thead>
                                    <tr>
                                        <th>Tanggal</th>
                                        <th>Nama Siswa</th>
                                        <th>Rekap Absensi</th>
                                        <th>Permasalahan</th>
                                        <th>Penanganan</th>
                                        <th>Bukti Dukung</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($pembinaanItems as $item)
                                        <tr>
                                            <td>{{ $item->created_at?->format('d/m/Y H:i') }}</td>
                                            <td>{{ $item->siswa->nama ?? '-' }}</td>
                                            <td>
                                                <small>
                                                    H: {{ $item->hadir }},
                                                    S: {{ $item->sakit }},
                                                    I: {{ $item->izin }},
                                                    A: {{ $item->alpa }},
                                                    T: {{ $item->terlambat }}
                                                </small>
                                            </td>
                                            <td>{{ $item->deskripsi_permasalahan }}</td>
                                            <td>{{ $item->penanganan }}</td>
                                            <td>
                                                @if(!empty($item->bukti_dukung_files))
                                                    @foreach($item->bukti_dukung_files as $path)
                                                        <a href="{{ asset('storage/' . $path) }}" target="_blank" class="badge bg-info text-decoration-none me-1">Lihat</a>
                                                    @endforeach
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
        </div>
    </div>
</div>

<div class="modal fade" id="printPreviewModalPembinaan" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Preview Print Laporan Pembinaan BK</h5>
                <button type="button" class="close" data-bs-dismiss="modal"><span aria-hidden="true">&times;</span></button>
            </div>
            <div class="modal-body p-0" style="height: 75vh;">
                <iframe id="printPreviewFramePembinaan" src="" style="width:100%;height:100%;border:0;"></iframe>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                <button type="button" class="btn btn-primary" id="btnPrintFromPreviewPembinaan">
                    <i class="ti ti-printer me-1"></i>Print
                </button>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const siswaSelect = document.getElementById('siswa_id');
    const rekapHadir = document.getElementById('rekap_hadir');
    const rekapSakit = document.getElementById('rekap_sakit');
    const rekapIzin = document.getElementById('rekap_izin');
    const rekapAlpa = document.getElementById('rekap_alpa');
    const rekapTerlambat = document.getElementById('rekap_terlambat');
    const buktiAbsensi = document.getElementById('bukti_dukung_absensi');
    const waliKelas = document.getElementById('wali_kelas_nama');
    const laporanGuru = document.getElementById('laporan_guru');
    const laporanWaliKelas = document.querySelector('textarea[name="laporan_wali_kelas"]');
    const openPrintBtn = document.getElementById('btnOpenPrintPreviewPembinaan');
    const printFrame = document.getElementById('printPreviewFramePembinaan');
    const printFromPreviewBtn = document.getElementById('btnPrintFromPreviewPembinaan');

    const resetRekap = function () {
        rekapHadir.value = 'Hadir: 0';
        rekapSakit.value = 'Sakit: 0';
        rekapIzin.value = 'Izin: 0';
        rekapAlpa.value = 'Alpa: 0';
        rekapTerlambat.value = 'Terlambat: 0';
        if (!buktiAbsensi.value) {
            buktiAbsensi.value = '';
        }
    };

    const fetchRekap = function (siswaId) {
        if (!siswaId) {
            resetRekap();
            return;
        }

        const url = `{{ route('guru_bk_layanan.pembinaan.rekap_absensi', ['kelas' => $kelas->id]) }}?siswa_id=${encodeURIComponent(siswaId)}`;
        fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
            .then(response => {
                if (!response.ok) {
                    throw new Error('Gagal memuat rekap absensi');
                }
                return response.json();
            })
            .then(data => {
                rekapHadir.value = `Hadir: ${data.hadir ?? 0}`;
                rekapSakit.value = `Sakit: ${data.sakit ?? 0}`;
                rekapIzin.value = `Izin: ${data.izin ?? 0}`;
                rekapAlpa.value = `Alpa: ${data.alpa ?? 0}`;
                rekapTerlambat.value = `Terlambat: ${data.terlambat ?? 0}`;
                if (!buktiAbsensi.value) {
                    buktiAbsensi.value = data.bukti_dukung_absensi ?? '';
                }
                if (waliKelas && data.wali_kelas_nama) {
                    waliKelas.value = data.wali_kelas_nama;
                }
                if (laporanGuru && !laporanGuru.value) {
                    laporanGuru.value = data.laporan_guru ?? '';
                }
                if (laporanWaliKelas && !laporanWaliKelas.value) {
                    laporanWaliKelas.value = data.laporan_wali_kelas ?? '';
                }
            })
            .catch(() => {
                resetRekap();
            });
    };

    if (siswaSelect) {
        siswaSelect.addEventListener('change', function () {
            fetchRekap(this.value);
        });

        if (siswaSelect.value) {
            fetchRekap(siswaSelect.value);
        }
    }

    if (openPrintBtn && printFrame) {
        openPrintBtn.addEventListener('click', function () {
            printFrame.src = this.getAttribute('data-print-url');
        });
    }

    if (printFromPreviewBtn && printFrame) {
        printFromPreviewBtn.addEventListener('click', function () {
            if (printFrame.contentWindow) {
                printFrame.contentWindow.focus();
                printFrame.contentWindow.print();
            }
        });
    }
});
</script>
@endsection
