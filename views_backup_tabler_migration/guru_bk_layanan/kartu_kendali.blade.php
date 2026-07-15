@extends('layouts.app')

@section('content')
<div class="container-fluid">
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="close" data-dismiss="alert"><span aria-hidden="true">&times;</span></button>
        </div>
    @endif

    @if($errors->any())
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <ul class="mb-0">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
            <button type="button" class="close" data-dismiss="alert"><span aria-hidden="true">&times;</span></button>
        </div>
    @endif

    <div class="row mb-3">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-1">Input Pelanggaran & Point (Guru BK)</h5>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('guru_bk_layanan.kartu_kendali.store', ['kelas' => $kelas->id]) }}" class="row g-2 align-items-end">
                        @csrf
                        <div class="col-md-3">
                            <label class="form-label mb-1">Tanggal</label>
                            <input type="date" name="tanggal" class="form-control" value="{{ old('tanggal', now()->format('Y-m-d')) }}" required>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label mb-1">Siswa</label>
                            <select name="siswa_id" class="form-select" required>
                                <option value="">Pilih Siswa</option>
                                @foreach($siswaList as $siswa)
                                    <option value="{{ $siswa->id }}" {{ (string) old('siswa_id') === (string) $siswa->id ? 'selected' : '' }}>{{ $siswa->nama }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label mb-1">Status</label>
                            <select name="status_absensi" class="form-select" required>
                                <option value="hadir" {{ old('status_absensi') === 'hadir' ? 'selected' : '' }}>Hadir</option>
                                <option value="terlambat" {{ old('status_absensi', 'terlambat') === 'terlambat' ? 'selected' : '' }}>Terlambat</option>
                                <option value="sakit" {{ old('status_absensi') === 'sakit' ? 'selected' : '' }}>Sakit</option>
                                <option value="izin" {{ old('status_absensi') === 'izin' ? 'selected' : '' }}>Izin</option>
                                <option value="alpa" {{ old('status_absensi') === 'alpa' ? 'selected' : '' }}>Alpa</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label mb-1">Jenis Pelanggaran</label>
                            <select name="jenis_pelanggaran_id" id="jenis_pelanggaran_id" class="form-select" required>
                                <option value="">Pilih Jenis</option>
                                @foreach(($jenisPelanggaranOptions ?? collect()) as $jenis)
                                    <option value="{{ $jenis->id }}" data-poin="{{ $jenis->poin_default }}" {{ (string) old('jenis_pelanggaran_id') === (string) $jenis->id ? 'selected' : '' }}>{{ $jenis->nama }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label mb-1">Point</label>
                            <input type="number" id="poin_pelanggaran" name="poin_pelanggaran" class="form-control" min="0" max="1000" value="{{ old('poin_pelanggaran', 0) }}" required>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label mb-1">Terlambat (menit)</label>
                            <input type="number" name="terlambat_menit" class="form-control" min="0" max="1000" value="{{ old('terlambat_menit', 0) }}">
                        </div>
                        <div class="col-md-10">
                            <label class="form-label mb-1">Detail Pelanggaran (Opsional)</label>
                            <input type="text" name="deskripsi_pelanggaran" class="form-control" value="{{ old('deskripsi_pelanggaran') }}" placeholder="Contoh: Tidak memakai dasi hari Senin">
                        </div>
                        <div class="col-md-2 d-grid">
                            <button type="submit" class="btn btn-primary"><i class="ti ti-device-floppy me-1"></i>Simpan</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <div class="row mb-3">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h3 class="card-title mb-0">Kartu Kendali Pelanggaran - {{ $kelas->nama_kelas }}</h3>
                    <a href="{{ route('guru_bk_layanan.menu', ['kelas' => $kelas->id]) }}" class="btn btn-secondary btn-sm">
                        <i class="ti ti-arrow-left"></i> Kembali ke Menu
                    </a>
                </div>
                <div class="card-body">
                    <form method="GET" action="{{ route('guru_bk_layanan.kartu_kendali', ['kelas' => $kelas->id]) }}" class="row g-2 align-items-end mb-3">
                        <div class="col-md-4">
                            <label class="form-label mb-1">Siswa</label>
                            <select name="siswa_id" class="form-select">
                                <option value="">Semua Siswa</option>
                                @foreach($siswaList as $siswa)
                                    <option value="{{ $siswa->id }}" {{ (string) $selectedSiswaId === (string) $siswa->id ? 'selected' : '' }}>{{ $siswa->nama }} ({{ $siswa->nis ?: '-' }})</option>
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
                        <div class="col-md-2 d-flex gap-2">
                            <button type="submit" class="btn btn-primary"><i class="ti ti-search me-1"></i>Filter</button>
                            <a href="{{ route('guru_bk_layanan.kartu_kendali', ['kelas' => $kelas->id]) }}" class="btn btn-light border">Reset</a>
                        </div>
                    </form>

                    <div class="mb-3">
                        <a href="{{ route('guru_bk_layanan.kartu_kendali.print', ['kelas' => $kelas->id, 'siswa_id' => $selectedSiswaId, 'tanggal_mulai' => $tanggalMulai, 'tanggal_selesai' => $tanggalSelesai]) }}" target="_blank" class="btn btn-dark btn-sm">
                            <i class="ti ti-printer me-1"></i>Print Kartu Kendali
                        </a>
                    </div>

                    @php $totalPoint = ($kartuItems ?? collect())->sum('poin_pelanggaran'); @endphp
                    <div class="alert alert-info">
                        <strong>Total Data:</strong> {{ ($kartuItems ?? collect())->count() }} |
                        <strong>Total Point:</strong> {{ $totalPoint }}
                    </div>

                    <div class="table-responsive">
                        <table class="table table-bordered table-striped align-middle">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>Hari/Tgl</th>
                                    <th>Nama Siswa</th>
                                    <th>Jenis Pelanggaran</th>
                                    <th class="text-center">Point</th>
                                    <th class="text-center">Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse(($kartuItems ?? collect()) as $index => $item)
                                    <tr>
                                        <td>{{ $index + 1 }}</td>
                                        <td>{{ \Carbon\Carbon::parse($item->tanggal)->translatedFormat('l, d/m/Y') }}</td>
                                        <td>{{ $item->nama_siswa }}<br><small class="text-muted">NIS {{ $item->nis ?: '-' }} / NISN {{ $item->nisn ?: '-' }}</small></td>
                                        <td>{{ $item->deskripsi_pelanggaran }}</td>
                                        <td class="text-center"><span class="badge bg-danger">{{ $item->poin_pelanggaran }}</span></td>
                                        <td class="text-center">{{ strtoupper((string) $item->status_absensi) }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-center text-muted">Belum ada data pelanggaran untuk filter yang dipilih.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('js')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const jenisSelect = document.getElementById('jenis_pelanggaran_id');
        const poinInput = document.getElementById('poin_pelanggaran');

        if (!jenisSelect || !poinInput) {
            return;
        }

        const setPoinFromJenis = function () {
            const selected = jenisSelect.options[jenisSelect.selectedIndex];
            if (!selected) {
                return;
            }
            const poin = selected.getAttribute('data-poin');
            if (poin !== null && poin !== '') {
                poinInput.value = poin;
            }
        };

        jenisSelect.addEventListener('change', setPoinFromJenis);

        if (!poinInput.value || poinInput.value === '0') {
            setPoinFromJenis();
        }
    });
</script>
@endpush
