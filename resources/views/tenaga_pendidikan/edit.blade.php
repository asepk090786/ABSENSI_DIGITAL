@extends('layouts.app')

@section('title','Edit Tenaga Pendidikan')

@section('content')
<div class="row">
    <div class="col-md-10 mx-auto">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h4 class="card-title fw-semibold m-0">Edit Tenaga Pendidikan</h4>
                <div>
                    <a href="{{ route('tenaga_pendidikan.show', $tenagaPendidikan) }}" class="btn btn-info btn-sm">Lihat Detail</a>
                    <a href="{{ route('tenaga_pendidikan.index') }}" class="btn btn-secondary btn-sm">Kembali</a>
                </div>
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('tenaga_pendidikan.update', $tenagaPendidikan) }}">
                    @csrf
                    @method('PUT')
                    
                    <h5 class="mb-3">Data Pribadi</h5>
                    <div class="mb-3">
                        <label class="form-label">Nama <span class="text-danger">*</span></label>
                        <input type="text" name="nama" class="form-control @error('nama') is-invalid @enderror" value="{{ old('nama', $tenagaPendidikan->nama) }}" required>
                        @error('nama')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">NIP</label>
                            <input type="text" name="nip" class="form-control @error('nip') is-invalid @enderror" value="{{ old('nip', $tenagaPendidikan->nip) }}">
                            @error('nip')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Jabatan</label>
                            <input type="text" name="jabatan" class="form-control @error('jabatan') is-invalid @enderror" value="{{ old('jabatan', $tenagaPendidikan->jabatan) }}">
                            @error('jabatan')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Jenis Kelamin <span class="text-danger">*</span></label>
                            <select name="jenis_kelamin" class="form-select @error('jenis_kelamin') is-invalid @enderror" required>
                                <option value="">Pilih</option>
                                <option value="L" {{ old('jenis_kelamin', $tenagaPendidikan->jenis_kelamin) === 'L' ? 'selected' : '' }}>Laki-laki</option>
                                <option value="P" {{ old('jenis_kelamin', $tenagaPendidikan->jenis_kelamin) === 'P' ? 'selected' : '' }}>Perempuan</option>
                            </select>
                            @error('jenis_kelamin')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Tanggal Lahir</label>
                            <input type="date" name="tanggal_lahir" class="form-control @error('tanggal_lahir') is-invalid @enderror" value="{{ old('tanggal_lahir', $tenagaPendidikan->tanggal_lahir) }}">
                            @error('tanggal_lahir')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Email <span class="text-danger">*</span></label>
                            <input type="email" name="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email', $tenagaPendidikan->email) }}" required>
                            @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Telepon</label>
                            <input type="text" name="telepon" class="form-control @error('telepon') is-invalid @enderror" value="{{ old('telepon', $tenagaPendidikan->telepon) }}">
                            @error('telepon')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Alamat</label>
                        <textarea name="alamat" class="form-control @error('alamat') is-invalid @enderror" rows="3">{{ old('alamat', $tenagaPendidikan->alamat) }}</textarea>
                        @error('alamat')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Foto</label>
                        @if($tenagaPendidikan->foto)
                            <div class="mb-2">
                                <img src="{{ asset('storage/' . $tenagaPendidikan->foto) }}" alt="Foto" class="img-thumbnail" style="max-width: 200px;">
                            </div>
                        @endif
                        <input type="file" name="foto" class="form-control @error('foto') is-invalid @enderror" accept="image/*">
                        <small class="form-hint">Format: JPG, PNG, GIF. Ukuran max: 2MB. Biarkan kosong jika tidak ingin mengubah foto.</small>
                        @error('foto')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="mt-4">
                        <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
                        <a href="{{ route('tenaga_pendidikan.index') }}" class="btn btn-secondary">Batal</a>
                    </div>
                </form>

                <hr class="my-4">

                <h5 class="mb-3">Akun User</h5>
                @if($tenagaPendidikan->user)
                    <div class="alert alert-success">
                        <strong><i class="ti ti-check me-2"></i>Akun Sudah Tersedia</strong>
                        <table class="table table-sm mt-3 mb-0">
                            <tr>
                                <td style="width: 150px;"><strong>Username:</strong></td>
                                <td>{{ $tenagaPendidikan->user->username }}</td>
                            </tr>
                            <tr>
                                <td><strong>Email:</strong></td>
                                <td>{{ $tenagaPendidikan->user->email }}</td>
                            </tr>
                            <tr>
                                <td><strong>Role:</strong></td>
                                <td>{{ $tenagaPendidikan->user->role->role_name ?? '-' }}</td>
                            </tr>
                            <tr>
                                <td><strong>Status:</strong></td>
                                <td>
                                    @if($tenagaPendidikan->user->is_active)
                                        <span class="badge bg-success">Aktif</span>
                                    @else
                                        <span class="badge bg-danger">Tidak Aktif</span>
                                    @endif
                                </td>
                            </tr>
                        </table>
                    </div>
                @else
                    <div class="alert alert-warning">
                        <i class="ti ti-alert-circle me-2"></i>
                        <strong>Belum Ada Akun User</strong>
                        <p class="mt-2">Klik tombol di bawah untuk membuat akun user secara otomatis.</p>
                        <a href="{{ route('tenaga_pendidikan.generate-account', $tenagaPendidikan) }}" class="btn btn-success btn-sm">
                            <i class="ti ti-plus me-1"></i>Buat Akun User
                        </a>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
