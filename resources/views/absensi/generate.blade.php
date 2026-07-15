@extends('layouts.app')

@section('title', 'Generate Absensi Siswa')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h3 class="card-title fw-semibold m-0">Generate Absensi Siswa</h3>
                    <a href="{{ route('absensi.index') }}" class="btn btn-secondary">
                        <i class="ti ti-arrow-left me-1"></i> Kembali
                    </a>
                </div>
                <div class="card-body">
                    @if ($errors->any())
                        <div class="alert alert-danger">
                            <strong><i class="ti ti-alert-circle me-1"></i>Terjadi kesalahan:</strong>
                            <ul class="mb-0 mt-2">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <div class="alert alert-info">
                        <i class="ti ti-info-circle me-1"></i>
                        Fitur ini akan membuat data absensi siswa otomatis sesuai komposisi status yang Anda tentukan.
                    </div>

                    <form method="POST" action="{{ route('absensi.generate.store') }}" id="formGenerateAbsensi">
                        @csrf

                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label">Mode Generate <span class="text-danger">*</span></label>
                                <select name="mode_generate" id="mode_generate" class="form-select" required>
                                    <option value="per_jam" {{ old('mode_generate', 'per_jam') === 'per_jam' ? 'selected' : '' }}>Per Jam Mata Pelajaran</option>
                                    <option value="per_hari" {{ old('mode_generate') === 'per_hari' ? 'selected' : '' }}>Per Hari (Harian)</option>
                                </select>
                                <small class="text-muted">Pilih <b>Per Hari</b> jika absensi dihitung sebagai kehadiran harian siswa.</small>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Tanggal <span class="text-danger">*</span></label>
                                <input type="date" name="tanggal" class="form-control" value="{{ old('tanggal', now()->format('Y-m-d')) }}" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Guru Pencatat <span class="text-danger">*</span></label>
                                <select name="guru_id" class="form-select" required>
                                    <option value="">Pilih Guru</option>
                                    @foreach($guruList as $guru)
                                        <option value="{{ $guru->id }}" {{ (string) old('guru_id') === (string) $guru->id ? 'selected' : '' }}>
                                            {{ $guru->nama }} ({{ $guru->kode_guru }})
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-4" id="jamBelajarWrapper">
                                <label class="form-label">Jam Belajar <span class="text-danger">*</span></label>
                                <select name="jam_belajar_id" id="jam_belajar_id" class="form-select" required>
                                    <option value="">Pilih Jam Belajar</option>
                                    @foreach($jamBelajarList as $jam)
                                        <option value="{{ $jam->id }}" {{ (string) old('jam_belajar_id') === (string) $jam->id ? 'selected' : '' }}>
                                            Jam ke-{{ $jam->urutan }} ({{ $jam->jam_mulai }} - {{ $jam->jam_selesai }})
                                        </option>
                                    @endforeach
                                </select>
                                <small class="text-muted" id="jamBelajarHint">Dipakai jika mode generate per jam mata pelajaran.</small>
                            </div>

                            <div class="col-md-4">
                                <label class="form-label">Scope Generate <span class="text-danger">*</span></label>
                                <select name="scope" id="scope" class="form-select" required>
                                    <option value="single" {{ old('scope', 'single') === 'single' ? 'selected' : '' }}>Kelas Tertentu</option>
                                    <option value="all" {{ old('scope') === 'all' ? 'selected' : '' }}>Semua Kelas</option>
                                </select>
                            </div>
                            <div class="col-md-4" id="kelasWrapper">
                                <label class="form-label">Kelas</label>
                                <select name="kelas_id" id="kelas_id" class="form-select">
                                    <option value="">Pilih Kelas</option>
                                    @foreach($kelasList as $kelas)
                                        <option value="{{ $kelas->id }}" {{ (string) old('kelas_id') === (string) $kelas->id ? 'selected' : '' }}>
                                            {{ $kelas->nama_kelas }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Status Kelas (Opsional)</label>
                                <select name="status_kelas" class="form-select">
                                    <option value="">-- Pilih Status Kelas (opsional) --</option>
                                    <option value="Sangat Kondusif" {{ old('status_kelas') === 'Sangat Kondusif' ? 'selected' : '' }}>Sangat Kondusif</option>
                                    <option value="Kondusif" {{ old('status_kelas') === 'Kondusif' ? 'selected' : '' }}>Kondusif</option>
                                    <option value="Normal" {{ old('status_kelas') === 'Normal' ? 'selected' : '' }}>Normal</option>
                                    <option value="Kurang Kondusif" {{ old('status_kelas') === 'Kurang Kondusif' ? 'selected' : '' }}>Kurang Kondusif</option>
                                    <option value="Tidak Kondusif" {{ old('status_kelas') === 'Tidak Kondusif' ? 'selected' : '' }}>Tidak Kondusif</option>
                                </select>
                            </div>

                            <div class="col-md-12">
                                <div class="card border">
                                    <div class="card-header py-2">
                                        <strong>Komposisi Status Siswa</strong>
                                    </div>
                                    <div class="card-body">
                                        <div class="row g-2">
                                            <div class="col-md-2">
                                                <label class="form-label">Hadir</label>
                                                <input type="number" min="0" name="jumlah_hadir" class="form-control" value="{{ old('jumlah_hadir', 0) }}">
                                            </div>
                                            <div class="col-md-2">
                                                <label class="form-label">Terlambat</label>
                                                <input type="number" min="0" name="jumlah_terlambat" class="form-control" value="{{ old('jumlah_terlambat', 0) }}">
                                            </div>
                                            <div class="col-md-2">
                                                <label class="form-label">Sakit</label>
                                                <input type="number" min="0" name="jumlah_sakit" class="form-control" value="{{ old('jumlah_sakit', 0) }}">
                                            </div>
                                            <div class="col-md-2">
                                                <label class="form-label">Izin</label>
                                                <input type="number" min="0" name="jumlah_izin" class="form-control" value="{{ old('jumlah_izin', 0) }}">
                                            </div>
                                            <div class="col-md-2">
                                                <label class="form-label">Alpa</label>
                                                <input type="number" min="0" name="jumlah_alpa" class="form-control" value="{{ old('jumlah_alpa', 0) }}">
                                            </div>
                                            <div class="col-md-2">
                                                <label class="form-label">Sisa Diisi</label>
                                                <select name="status_sisa" class="form-select">
                                                    <option value="hadir" {{ old('status_sisa', 'hadir') === 'hadir' ? 'selected' : '' }}>Hadir</option>
                                                    <option value="terlambat" {{ old('status_sisa') === 'terlambat' ? 'selected' : '' }}>Terlambat</option>
                                                    <option value="sakit" {{ old('status_sisa') === 'sakit' ? 'selected' : '' }}>Sakit</option>
                                                    <option value="izin" {{ old('status_sisa') === 'izin' ? 'selected' : '' }}>Izin</option>
                                                    <option value="alpa" {{ old('status_sisa') === 'alpa' ? 'selected' : '' }}>Alpa</option>
                                                </select>
                                            </div>
                                        </div>
                                        <small class="text-muted d-block mt-2">
                                            Jika total komposisi lebih kecil dari jumlah siswa aktif, sisa siswa akan diisi sesuai pilihan "Sisa Diisi".
                                        </small>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-12">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="overwrite_existing" value="1" id="overwrite_existing" {{ old('overwrite_existing') ? 'checked' : '' }}>
                                    <label class="form-check-label" for="overwrite_existing">
                                        Overwrite data absensi yang sudah ada pada kelas/jam/tanggal yang sama
                                    </label>
                                </div>
                            </div>
                        </div>

                        <div class="mt-4 d-flex gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="ti ti-bolt me-1"></i> Generate Absensi
                            </button>
                            <a href="{{ route('absensi.index') }}" class="btn btn-secondary">Batal</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const scope = document.getElementById('scope');
        const modeGenerate = document.getElementById('mode_generate');
        const kelasWrapper = document.getElementById('kelasWrapper');
        const kelasId = document.getElementById('kelas_id');
        const jamBelajarWrapper = document.getElementById('jamBelajarWrapper');
        const jamBelajarId = document.getElementById('jam_belajar_id');
        const jamBelajarHint = document.getElementById('jamBelajarHint');

        function toggleKelas() {
            const isSingle = scope.value === 'single';
            kelasWrapper.style.display = isSingle ? '' : 'none';
            if (isSingle) {
                kelasId.setAttribute('required', 'required');
            } else {
                kelasId.removeAttribute('required');
                kelasId.value = '';
            }
        }

        function toggleModeGenerate() {
            const isPerHari = modeGenerate.value === 'per_hari';
            jamBelajarId.required = !isPerHari;
            jamBelajarId.disabled = isPerHari;
            jamBelajarWrapper.classList.toggle('opacity-50', isPerHari);

            if (isPerHari) {
                jamBelajarHint.textContent = 'Mode per hari: sistem menggunakan jam belajar default (jam pertama) sebagai referensi data.';
            } else {
                jamBelajarHint.textContent = 'Dipakai jika mode generate per jam mata pelajaran.';
            }
        }

        scope.addEventListener('change', toggleKelas);
        modeGenerate.addEventListener('change', toggleModeGenerate);
        toggleKelas();
        toggleModeGenerate();
    });
</script>
@endsection
