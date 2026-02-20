@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row mb-3">
        <div class="col-12">
            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif
        </div>
    </div>

    <div class="row mb-3">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h3 class="card-title mb-0">Data Absensi Kelas - {{ $kelas->nama_kelas }}</h3>
                    <div class="d-flex gap-2">
                        <button type="button" class="btn btn-dark btn-sm" data-print-url="{{ route('guru_bk_layanan.layanan.print', ['kelas' => $kelas->id]) }}" data-bs-toggle="modal" data-bs-target="#printPreviewModalLayanan" id="btnOpenPrintPreviewLayanan">
                            <i class="ti ti-printer"></i> Print Output
                        </button>
                        <form method="GET" action="{{ route('guru_bk_layanan.layanan', ['kelas' => $kelas->id]) }}" class="d-flex gap-2">
                            <input type="date" name="tanggal" class="form-control form-control-sm" value="{{ $selectedTanggal }}">
                            <button type="submit" class="btn btn-primary btn-sm">
                                <i class="ti ti-search"></i> Tampilkan
                            </button>
                        </form>
                        <a href="{{ route('guru_bk_layanan.menu', ['kelas' => $kelas->id]) }}" class="btn btn-secondary btn-sm">
                            <i class="ti ti-arrow-left"></i> Kembali ke Menu
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    @if($absensiItems->isEmpty())
                        <div class="alert alert-info mb-0">
                            <i class="ti ti-info-circle me-2"></i>Belum ada data absensi untuk tanggal {{ \Carbon\Carbon::parse($selectedTanggal)->format('d/m/Y') }}.
                        </div>
                    @else
                        <div class="table-responsive">
                            <table class="table table-striped table-hover">
                                <thead>
                                    <tr>
                                        <th>No</th>
                                        <th>Tanggal</th>
                                        <th>Guru</th>
                                        <th>Jam Belajar</th>
                                        <th>Hadir</th>
                                        <th>Terlambat</th>
                                        <th>Tidak Hadir</th>
                                        <th>Jumlah Siswa</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($absensiItems as $index => $item)
                                        @php
                                            $countStatus = function($statuses) use ($item) {
                                                $needles = collect($statuses)->map(fn($s) => strtolower((string) $s))->all();
                                                return $item->absensiSiswa->filter(function($row) use ($needles) {
                                                    return in_array(strtolower((string) ($row->status ?? '')), $needles, true);
                                                })->count();
                                            };
                                            $hadirCount = $countStatus(['hadir']);
                                            $terlambatCount = $countStatus(['terlambat', 'telat']);
                                            $alpaCount = $countStatus(['alpa', 'alpha', 'alfa', 'absen']);
                                        @endphp
                                        <tr>
                                            <td>{{ $index + 1 }}</td>
                                            <td>{{ $item->tanggal->format('d/m/Y') }}</td>
                                            <td>{{ $item->guru->nama ?? '-' }}</td>
                                            <td>{{ $item->jamBelajar->jam_mulai ?? '-' }} - {{ $item->jamBelajar->jam_selesai ?? '-' }}</td>
                                            <td><span class="badge bg-success">{{ $hadirCount }}</span></td>
                                            <td><span class="badge" style="background:#f59e0b;color:#fff;">{{ $terlambatCount }}</span></td>
                                            <td><span class="badge bg-danger">{{ $alpaCount }}</span></td>
                                            <td><span class="badge bg-info">{{ $item->absensiSiswa->count() }}</span></td>
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

    <div class="row">
        <div class="col-lg-5">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title mb-0">Menu Layanan: Isi Layanan Guru BK</h3>
                </div>
                <div class="card-body">
                    <form action="{{ route('guru_bk_layanan.layanan.store', ['kelas' => $kelas->id]) }}" method="POST">
                        @csrf

                        <div class="mb-3">
                            <label class="form-label">Tanggal <span class="text-danger">*</span></label>
                            <input type="date" name="tanggal" class="form-control @error('tanggal') is-invalid @enderror" value="{{ old('tanggal', now()->format('Y-m-d')) }}" required>
                            @error('tanggal')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Siswa (Opsional)</label>
                            <select name="siswa_id" class="form-select @error('siswa_id') is-invalid @enderror">
                                <option value="">-- Layanan Kelas / Umum --</option>
                                @foreach($siswaList as $siswa)
                                    <option value="{{ $siswa->id }}" {{ (string) old('siswa_id') === (string) $siswa->id ? 'selected' : '' }}>{{ $siswa->nama }}</option>
                                @endforeach
                            </select>
                            @error('siswa_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Jenis Layanan <span class="text-danger">*</span></label>
                            <input type="text" name="jenis_layanan" class="form-control @error('jenis_layanan') is-invalid @enderror" value="{{ old('jenis_layanan') }}" placeholder="Contoh: Konseling Individu" required>
                            @error('jenis_layanan')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Deskripsi Layanan <span class="text-danger">*</span></label>
                            <textarea name="deskripsi_layanan" rows="3" class="form-control @error('deskripsi_layanan') is-invalid @enderror" required>{{ old('deskripsi_layanan') }}</textarea>
                            @error('deskripsi_layanan')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Hasil Layanan</label>
                            <textarea name="hasil_layanan" rows="2" class="form-control @error('hasil_layanan') is-invalid @enderror">{{ old('hasil_layanan') }}</textarea>
                            @error('hasil_layanan')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Rencana Tindak Lanjut</label>
                            <textarea name="rencana_tindak_lanjut" rows="2" class="form-control @error('rencana_tindak_lanjut') is-invalid @enderror">{{ old('rencana_tindak_lanjut') }}</textarea>
                            @error('rencana_tindak_lanjut')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <button type="submit" class="btn btn-primary">
                            <i class="ti ti-device-floppy me-1"></i>Simpan Layanan
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-lg-7">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title mb-0">Riwayat Layanan BK</h3>
                </div>
                <div class="card-body">
                    @if($layananItems->isEmpty())
                        <div class="alert alert-light border mb-0">Belum ada data layanan BK.</div>
                    @else
                        <div class="table-responsive">
                            <table class="table table-striped table-hover">
                                <thead>
                                    <tr>
                                        <th>Tanggal</th>
                                        <th>Siswa</th>
                                        <th>Jenis Layanan</th>
                                        <th>Deskripsi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($layananItems as $layanan)
                                        <tr>
                                            <td>{{ $layanan->tanggal->format('d/m/Y') }}</td>
                                            <td>{{ $layanan->siswa->nama ?? 'Kelas/Umum' }}</td>
                                            <td>{{ $layanan->jenis_layanan }}</td>
                                            <td>{{ $layanan->deskripsi_layanan }}</td>
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

<div class="modal fade" id="printPreviewModalLayanan" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Preview Print Layanan BK</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-0" style="height: 75vh;">
                <iframe id="printPreviewFrameLayanan" src="" style="width:100%;height:100%;border:0;"></iframe>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                <button type="button" class="btn btn-primary" id="btnPrintFromPreviewLayanan">
                    <i class="ti ti-printer me-1"></i>Print
                </button>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const openBtn = document.getElementById('btnOpenPrintPreviewLayanan');
    const frame = document.getElementById('printPreviewFrameLayanan');
    const printBtn = document.getElementById('btnPrintFromPreviewLayanan');

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
