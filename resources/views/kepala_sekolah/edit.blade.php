@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">{{ isset($kepalaSekolah) ? 'Edit' : 'Tambah' }} Data Kepala Sekolah</h3>
                </div>
                <div class="card-body">
                    <form action="{{ isset($kepalaSekolah) ? route('kepala_sekolah.update', $kepalaSekolah->id) : route('kepala_sekolah.store') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        @if(isset($kepalaSekolah))
                            @method('PUT')
                        @endif

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Nama <span class="text-danger">*</span></label>
                                    <input type="text" name="nama" class="form-control @error('nama') is-invalid @enderror" value="{{ old('nama', $kepalaSekolah->nama ?? '') }}" required>
                                    @error('nama')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">NIP</label>
                                    <input type="text" name="nip" class="form-control @error('nip') is-invalid @enderror" value="{{ old('nip', $kepalaSekolah->nip ?? '') }}">
                                    @error('nip')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Guru</label>
                                    <select name="guru_id" class="form-select @error('guru_id') is-invalid @enderror">
                                        <option value="">Pilih Guru (Opsional)</option>
                                        @foreach($guru as $g)
                                            <option value="{{ $g->id }}" {{ old('guru_id', $kepalaSekolah->guru_id ?? '') == $g->id ? 'selected' : '' }}>
                                                {{ $g->nama }}
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
                                    <label class="form-label">Pangkat/Golongan</label>
                                    <input type="text" name="pangkat_golongan" class="form-control @error('pangkat_golongan') is-invalid @enderror" value="{{ old('pangkat_golongan', $kepalaSekolah->pangkat_golongan ?? '') }}" placeholder="Contoh: IV/a">
                                    @error('pangkat_golongan')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Tanggal Mulai Jabatan <span class="text-danger">*</span></label>
                                    <input type="date" name="tanggal_mulai_jabatan" class="form-control @error('tanggal_mulai_jabatan') is-invalid @enderror" value="{{ old('tanggal_mulai_jabatan', isset($kepalaSekolah) ? $kepalaSekolah->tanggal_mulai_jabatan->format('Y-m-d') : '') }}" required>
                                    @error('tanggal_mulai_jabatan')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Tanggal Selesai Jabatan</label>
                                    <input type="date" name="tanggal_selesai_jabatan" class="form-control @error('tanggal_selesai_jabatan') is-invalid @enderror" value="{{ old('tanggal_selesai_jabatan', isset($kepalaSekolah) && $kepalaSekolah->tanggal_selesai_jabatan ? $kepalaSekolah->tanggal_selesai_jabatan->format('Y-m-d') : '') }}">
                                    @error('tanggal_selesai_jabatan')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Status <span class="text-danger">*</span></label>
                                    <select name="status" class="form-select @error('status') is-invalid @enderror" required>
                                        <option value="">Pilih Status</option>
                                        <option value="Aktif" {{ old('status', $kepalaSekolah->status ?? '') == 'Aktif' ? 'selected' : '' }}>Aktif</option>
                                        <option value="Tidak Aktif" {{ old('status', $kepalaSekolah->status ?? '') == 'Tidak Aktif' ? 'selected' : '' }}>Tidak Aktif</option>
                                    </select>
                                    @error('status')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Foto</label>
                                    <input type="file" name="foto" class="form-control @error('foto') is-invalid @enderror" accept="image/*">
                                    @error('foto')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    @if(isset($kepalaSekolah) && $kepalaSekolah->foto)
                                        <img src="{{ asset('storage/' . $kepalaSekolah->foto) }}" alt="Foto" class="mt-2 rounded" style="max-height: 100px;">
                                    @endif
                                </div>
                            </div>

                            <div class="col-12">
                                <div class="mb-3">
                                    <label class="form-label">Alamat</label>
                                    <textarea name="alamat" class="form-control @error('alamat') is-invalid @enderror" rows="3">{{ old('alamat', $kepalaSekolah->alamat ?? '') }}</textarea>
                                    @error('alamat')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Telepon</label>
                                    <input type="text" name="telepon" class="form-control @error('telepon') is-invalid @enderror" value="{{ old('telepon', $kepalaSekolah->telepon ?? '') }}">
                                    @error('telepon')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Email</label>
                                    <input type="email" name="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email', $kepalaSekolah->email ?? '') }}">
                                    @error('email')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="mt-3">
                            <button type="submit" class="btn btn-primary">
                                <i class="ti ti-device-floppy"></i> Simpan
                            </button>
                            <a href="{{ route('kepala_sekolah.index') }}" class="btn btn-secondary">
                                <i class="ti ti-arrow-left"></i> Kembali
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
