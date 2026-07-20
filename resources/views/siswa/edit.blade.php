@extends('layouts.app', ['pageSlug' => 'siswa'])

@section('title','Edit Siswa')

@php
    $backRoute = $backRoute ?? route('siswa.index');
@endphp

@section('content')
<div class="row">
    <div class="col-md-10 mx-auto">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h4 class="card-title fw-semibold m-0">Edit Siswa</h4>
                <a href="{{ $backRoute }}" class="btn btn-secondary btn-sm">Kembali</a>
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('siswa.update', $siswa->id) }}">
                    @csrf
                    @method('PUT')

                    <div class="alert alert-info">
                        <i class="ti ti-info-circle me-2"></i>
                        <strong>Informasi:</strong> Perubahan akan memperbarui data siswa dan akun login terkait. Kosongkan password jika tidak ingin mengubah.
                    </div>

                    <h5 class="mb-2">Data Pribadi</h5>
                    <div class="row">
                        <div class="col-md-6 mb-2">
                            <label class="form-label">NIS <span class="text-danger">*</span></label>
                            <input type="text" name="nis" class="form-control @error('nis') is-invalid @enderror" value="{{ old('nis', $siswa->nis) }}" required>
                            @error('nis')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6 mb-2">
                            <label class="form-label">NISN <span class="text-danger">*</span></label>
                            <input type="text" name="nisn" class="form-control @error('nisn') is-invalid @enderror" value="{{ old('nisn', $siswa->nisn) }}" required>
                            @error('nisn')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>

                    <div class="mb-2">
                        <label class="form-label">Nama <span class="text-danger">*</span></label>
                        <input type="text" name="nama" class="form-control @error('nama') is-invalid @enderror" value="{{ old('nama', $siswa->nama) }}" required>
                        @error('nama')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="row">
                        <div class="col-md-4 mb-2">
                            <label class="form-label">Jenis Kelamin <span class="text-danger">*</span></label>
                            <select name="jenis_kelamin" class="form-select @error('jenis_kelamin') is-invalid @enderror" required>
                                <option value="">Pilih</option>
                                <option value="L" {{ old('jenis_kelamin', $siswa->jenis_kelamin)=='L' ? 'selected' : '' }}>Laki-laki</option>
                                <option value="P" {{ old('jenis_kelamin', $siswa->jenis_kelamin)=='P' ? 'selected' : '' }}>Perempuan</option>
                            </select>
                            @error('jenis_kelamin')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-4 mb-2">
                            <label class="form-label">Kelas <span class="text-danger">*</span></label>
                            <select name="kelas_id" class="form-select @error('kelas_id') is-invalid @enderror" required>
                                <option value="">Pilih Kelas</option>
                                @foreach($kelasList as $kelas)
                                    <option value="{{ $kelas->id }}" {{ old('kelas_id', $siswa->kelas_id)==$kelas->id ? 'selected' : '' }}>{{ $kelas->nama_kelas }}</option>
                                @endforeach
                            </select>
                            @error('kelas_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        @if($canManageClassPositions)
                            <div class="col-md-4 mb-2">
                                <label class="form-label">Jabatan Kelas</label>
                                <select name="jabatan_kelas" class="form-select @error('jabatan_kelas') is-invalid @enderror">
                                    <option value="">Tidak ada</option>
                                    <option value="ketua" {{ old('jabatan_kelas', $siswa->jabatan_kelas) == 'ketua' ? 'selected' : '' }}>Ketua Kelas</option>
                                    <option value="wakil" {{ old('jabatan_kelas', $siswa->jabatan_kelas) == 'wakil' ? 'selected' : '' }}>Wakil Ketua Kelas</option>
                                    <option value="sekretaris" {{ old('jabatan_kelas', $siswa->jabatan_kelas) == 'sekretaris' ? 'selected' : '' }}>Sekretaris Kelas</option>
                                    <option value="bendahara" {{ old('jabatan_kelas', $siswa->jabatan_kelas) == 'bendahara' ? 'selected' : '' }}>Bendahara</option>
                                </select>
                                @error('jabatan_kelas')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                        @endif
                        <div class="col-md-4 mb-2">
                            <label class="form-label">Email <span class="text-danger">*</span></label>
                            <input type="email" name="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email', $siswa->user->email ?? $siswa->email) }}" required>
                            @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>

                    <hr class="my-4">

                    <h5 class="mb-2">Data Akun Login</h5>
                    <div class="row">
                        <div class="col-md-6 mb-2">
                            <label class="form-label">Username <span class="text-danger">*</span></label>
                            <input type="text" name="username" class="form-control @error('username') is-invalid @enderror" value="{{ old('username', $siswa->user->username ?? '') }}" required>
                            <small class="form-hint">Username untuk login ke sistem</small>
                            @error('username')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-2">
                            <label class="form-label">Password Baru</label>
                            <input type="password" name="password" class="form-control @error('password') is-invalid @enderror">
                            <small class="form-hint">Isi hanya jika ingin mengganti password (minimal 6 karakter)</small>
                            @error('password')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6 mb-2">
                            <label class="form-label">Konfirmasi Password</label>
                            <input type="password" name="password_confirmation" class="form-control">
                        </div>
                    </div>

                    <div class="mt-4">
                        <button type="submit" class="btn btn-primary">
                            <i class="ti ti-device-floppy me-2"></i>Perbarui
                        </button>
                        <a href="{{ $backRoute }}" class="btn btn-secondary">Batal</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
