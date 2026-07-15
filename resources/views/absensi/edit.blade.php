@extends('layouts.app')

@section('title', 'Edit Absensi Kelas')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header border-0 pt-3 pb-2">
                    <div class="d-flex justify-content-between align-items-center">
                        <h3 class="card-title fw-semibold m-0">Edit Absensi Kelas</h3>
                        <a href="{{ route('absensi.show', $absensi->id) }}" class="btn btn-secondary">
                            <i class="ti ti-arrow-left me-1"></i> Kembali
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <form action="{{ route('absensi.update', $absensi->id) }}" method="POST">
                        @csrf
                        @method('PUT')

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-2">
                                    <label for="tanggal" class="form-label">Tanggal <span class="text-danger">*</span></label>
                                    <input type="date" class="form-control @error('tanggal') is-invalid @enderror" 
                                           id="tanggal" name="tanggal" value="{{ old('tanggal', $absensi->tanggal->format('Y-m-d')) }}" required>
                                    @error('tanggal')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="mb-2">
                                    <label for="kelas_id" class="form-label">Kelas <span class="text-danger">*</span></label>
                                    <select class="form-select @error('kelas_id') is-invalid @enderror" 
                                            id="kelas_id" name="kelas_id" required>
                                        <option value="">Pilih Kelas</option>
                                        @foreach($kelasList as $kelas)
                                            <option value="{{ $kelas->id }}" {{ old('kelas_id', $absensi->kelas_id) == $kelas->id ? 'selected' : '' }}>
                                                {{ $kelas->nama_kelas }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('kelas_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="mb-2">
                                    <label for="guru_id" class="form-label">Guru <span class="text-danger">*</span></label>
                                    <select class="form-select @error('guru_id') is-invalid @enderror" 
                                            id="guru_id" name="guru_id" required>
                                        <option value="">Pilih Guru</option>
                                        @foreach($guruList as $guru)
                                            <option value="{{ $guru->id }}" {{ old('guru_id', $absensi->guru_id) == $guru->id ? 'selected' : '' }}>
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
                                <div class="mb-2">
                                    <label for="jam_belajar_id" class="form-label">Jam Belajar <span class="text-danger">*</span></label>
                                    <select class="form-select @error('jam_belajar_id') is-invalid @enderror" 
                                            id="jam_belajar_id" name="jam_belajar_id" required>
                                        <option value="">Pilih Jam Belajar</option>
                                        @foreach($jamBelajarList as $jam)
                                            <option value="{{ $jam->id }}" {{ old('jam_belajar_id', $absensi->jam_belajar_id) == $jam->id ? 'selected' : '' }}>
                                                Jam ke-{{ $jam->urutan }} ({{ $jam->jam_mulai }} - {{ $jam->jam_selesai }})
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('jam_belajar_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="mb-2">
                                    <label for="tahun_ajaran_id" class="form-label">Tahun Ajaran <span class="text-danger">*</span></label>
                                    <select class="form-select @error('tahun_ajaran_id') is-invalid @enderror" 
                                            id="tahun_ajaran_id" name="tahun_ajaran_id" required>
                                        <option value="">Pilih Tahun Ajaran</option>
                                        @foreach($tahunAjaranList as $ta)
                                            <option value="{{ $ta->id }}" {{ old('tahun_ajaran_id', $absensi->tahun_ajaran_id) == $ta->id ? 'selected' : '' }}>
                                                {{ $ta->nama_tahun }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('tahun_ajaran_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="mb-2">
                                    <label for="semester_id" class="form-label">Semester <span class="text-danger">*</span></label>
                                    <select class="form-select @error('semester_id') is-invalid @enderror" 
                                            id="semester_id" name="semester_id" required>
                                        <option value="">Pilih Semester</option>
                                        @foreach($semesterList as $sem)
                                            <option value="{{ $sem->id }}" {{ old('semester_id', $absensi->semester_id) == $sem->id ? 'selected' : '' }}>
                                                {{ $sem->nama_semester }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('semester_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-12">
                                <div class="mb-2">
                                    <label for="status_kelas" class="form-label">Status Kelas</label>
                                    <select class="form-control @error('status_kelas') is-invalid @enderror" id="status_kelas" name="status_kelas">
                                        <option value="">-- Pilih Status Kelas (opsional) --</option>
                                        <option value="Sangat Kondusif" {{ old('status_kelas', $absensi->status_kelas) === 'Sangat Kondusif' ? 'selected' : '' }}>Sangat Kondusif</option>
                                        <option value="Kondusif" {{ old('status_kelas', $absensi->status_kelas) === 'Kondusif' ? 'selected' : '' }}>Kondusif</option>
                                        <option value="Normal" {{ old('status_kelas', $absensi->status_kelas) === 'Normal' ? 'selected' : '' }}>Normal</option>
                                        <option value="Kurang Kondusif" {{ old('status_kelas', $absensi->status_kelas) === 'Kurang Kondusif' ? 'selected' : '' }}>Kurang Kondusif</option>
                                        <option value="Tidak Kondusif" {{ old('status_kelas', $absensi->status_kelas) === 'Tidak Kondusif' ? 'selected' : '' }}>Tidak Kondusif</option>
                                    </select>
                                    @error('status_kelas')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <small class="text-muted">Opsional - Kondisi atau keterangan kelas</small>
                                </div>
                            </div>
                        </div>

                        <div class="mt-4">
                            <button type="submit" class="btn btn-primary">
                                <i class="ti ti-check me-1"></i> Update
                            </button>
                            <a href="{{ route('absensi.show', $absensi->id) }}" class="btn btn-secondary">
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
