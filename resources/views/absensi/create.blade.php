@extends('layouts.app')

@section('title', 'Tambah Absensi Kelas')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <h3 class="card-title mb-0">Tambah Absensi Kelas</h3>
                        <a href="{{ route('absensi.index') }}" class="btn btn-secondary">
                            <i class="ti ti-arrow-left me-1"></i> Kembali
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    @if(auth()->user()->guru_id)
                    <div class="alert alert-info">
                        <i class="ti ti-info-circle me-2"></i>
                        <strong>Info:</strong> Anda dapat menginput absensi untuk {{ $kelasList->count() }} kelas yang Anda ajar.
                        @if($jadwalList->isNotEmpty())
                        <br><strong>Jadwal Hari Ini:</strong> Anda memiliki {{ $jadwalList->count() }} jam mengajar hari ini.
                        @endif
                    </div>
                    @endif

                    @if($isQuickAccess ?? false)
                    <div class="alert alert-success">
                        <i class="ti ti-check-circle me-2"></i>
                        <strong>Mode Akses Cepat:</strong> Kelas dan jam belajar telah diisi otomatis sesuai jadwal hari ini.
                    </div>
                    @endif

                    <form action="{{ route('absensi.store') }}" method="POST">
                        @csrf

                        <input type="hidden" name="tahun_ajaran_id" value="{{ $tahunAjaran->id }}">
                        <input type="hidden" name="semester_id" value="{{ $semester->id }}">

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="tanggal" class="form-label">Tanggal <span class="text-danger">*</span></label>
                                    <input type="date" class="form-control @error('tanggal') is-invalid @enderror" 
                                           id="tanggal" name="tanggal" value="{{ old('tanggal', date('Y-m-d')) }}" required>
                                    @error('tanggal')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="kelas_id" class="form-label">Kelas <span class="text-danger">*</span></label>
                                    <select class="form-select @error('kelas_id') is-invalid @enderror" 
                                            id="kelas_id" name="kelas_id" required
                                            {{ ($isQuickAccess ?? false) ? 'disabled' : '' }}>
                                        <option value="">Pilih Kelas</option>
                                        @foreach($kelasList as $kelas)
                                            <option value="{{ $kelas->id }}" 
                                                {{ old('kelas_id', $selectedKelasId ?? '') == $kelas->id ? 'selected' : '' }}>
                                                {{ $kelas->nama_kelas }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @if($isQuickAccess ?? false)
                                    <input type="hidden" name="kelas_id" value="{{ $selectedKelasId }}">
                                    @endif
                                    @error('kelas_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="guru_id" class="form-label">Guru <span class="text-danger">*</span></label>
                                    <select class="form-select @error('guru_id') is-invalid @enderror" 
                                            id="guru_id" name="guru_id" required>
                                        <option value="">Pilih Guru</option>
                                        @foreach($guruList as $guru)
                                            <option value="{{ $guru->id }}" {{ old('guru_id', auth()->user()->guru_id) == $guru->id ? 'selected' : '' }}>
                                                {{ $guru->nama }} ({{ $guru->kode_guru }})
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('guru_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="jam_belajar_id" class="form-label">Jam Belajar <span class="text-danger">*</span></label>
                                    <select class="form-select @error('jam_belajar_id') is-invalid @enderror" 
                                            id="jam_belajar_id" name="jam_belajar_id" required
                                            {{ ($isQuickAccess ?? false) ? 'disabled' : '' }}>
                                        <option value="">Pilih Jam Belajar</option>
                                        @foreach($jamBelajarList as $jam)
                                            <option value="{{ $jam->id }}" {{ old('jam_belajar_id', $selectedJamBelajarId ?? '') == $jam->id ? 'selected' : '' }}>
                                                Jam ke-{{ $jam->urutan }} ({{ $jam->jam_mulai }} - {{ $jam->jam_selesai }})
                                            </option>
                                        @endforeach
                                    </select>
                                    @if($isQuickAccess ?? false)
                                    <input type="hidden" name="jam_belajar_id" value="{{ $selectedJamBelajarId }}">
                                    @endif
                                    @error('jam_belajar_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-12">
                                <div class="mb-3">
                                    <label for="status_kelas" class="form-label">Status Kelas</label>
                                    <input type="text" class="form-control @error('status_kelas') is-invalid @enderror" 
                                           id="status_kelas" name="status_kelas" value="{{ old('status_kelas') }}" 
                                           placeholder="Contoh: Normal, Kondusif, dll">
                                    @error('status_kelas')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <small class="text-muted">Opsional - Kondisi atau keterangan kelas</small>
                                </div>
                            </div>
                        </div>

                        <div class="alert alert-info">
                            <i class="ti ti-info-circle me-2"></i>
                            <strong>Tahun Ajaran:</strong> {{ $tahunAjaran->nama_tahun ?? '-' }} | 
                            <strong>Semester:</strong> {{ $semester->nama_semester ?? '-' }}
                        </div>

                        @if($jadwalList->isNotEmpty())
                        <div class="card mt-3">
                            <div class="card-header bg-primary-subtle">
                                <h5 class="mb-0"><i class="ti ti-calendar-event me-2"></i>Jadwal Mengajar Hari Ini</h5>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-sm table-bordered">
                                        <thead class="table-light">
                                            <tr>
                                                <th>Jam Ke</th>
                                                <th>Waktu</th>
                                                <th>Kelas</th>
                                                <th>Mata Pelajaran</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($jadwalList as $jadwal)
                                            <tr>
                                                <td>{{ $jadwal->jam_ke }}</td>
                                                <td>{{ $jadwal->jamBelajar->jam_mulai ?? '-' }} - {{ $jadwal->jamBelajar->jam_selesai ?? '-' }}</td>
                                                <td><strong>{{ $jadwal->kelas->nama_kelas ?? '-' }}</strong></td>
                                                <td>{{ $jadwal->mataPelajaran->nama_mapel ?? '-' }}</td>
                                            </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                        @endif

                        <div class="mt-4">
                            <button type="submit" class="btn btn-primary">
                                <i class="ti ti-check me-1"></i> Simpan
                            </button>
                            <a href="{{ route('absensi.index') }}" class="btn btn-secondary">
                                <i class="ti ti-x me-1"></i> Batal
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
