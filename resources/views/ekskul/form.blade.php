@extends('layouts.app', ['pageSlug' => 'ekskul'])

@section('title', isset($data) ? 'Edit Ekstrakurikuler' : 'Tambah Ekstrakurikuler')

@section('content')
<div class="row">
    <div class="col-md-8 mx-auto">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h4 class="card-title fw-semibold m-0">{{ isset($data) ? 'Edit Ekstrakurikuler' : 'Tambah Ekstrakurikuler' }}</h4>
                <a href="{{ route('ekskul.index') }}" class="btn btn-secondary btn-sm">Kembali</a>
            </div>
            <div class="card-body">
                <form method="POST" action="{{ isset($data) ? route('ekskul.update', $data->id) : route('ekskul.store') }}">
                    @csrf
                    @if(isset($data)) @method('PUT') @endif

                    <div class="mb-3">
                        <label class="form-label">Nama Ekstrakurikuler <span class="text-danger">*</span></label>
                        <input type="text" name="nama" class="form-control @error('nama') is-invalid @enderror"
                               value="{{ old('nama', $data->nama ?? '') }}" required maxlength="150">
                        @error('nama')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Deskripsi</label>
                        <textarea name="deskripsi" class="form-control @error('deskripsi') is-invalid @enderror" rows="3">{{ old('deskripsi', $data->deskripsi ?? '') }}</textarea>
                        @error('deskripsi')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Lokasi</label>
                            <input type="text" name="lokasi" class="form-control @error('lokasi') is-invalid @enderror"
                                   value="{{ old('lokasi', $data->lokasi ?? '') }}" maxlength="200">
                            @error('lokasi')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Kuota Maksimal Anggota</label>
                            <input type="number" name="kuota_max" class="form-control @error('kuota_max') is-invalid @enderror"
                                   value="{{ old('kuota_max', $data->kuota_max ?? '') }}" min="1">
                            @error('kuota_max')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Pembina Utama</label>
                        <select name="guru_id" class="form-select @error('guru_id') is-invalid @enderror">
                            <option value="">-- Pilih Guru --</option>
                            @forelse($guruList as $guru)
                                <option value="{{ $guru->id }}" {{ old('guru_id', $data->guru_id ?? '') == $guru->id ? 'selected' : '' }}>
                                    {{ $guru->nama }} @if($guru->kode_guru)({{ $guru->kode_guru }})@endif
                                </option>
                            @empty
                                <option value="">Tidak ada guru</option>
                            @endforelse
                        </select>
                        @error('guru_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="d-flex justify-content-end gap-2">
                        <a href="{{ route('ekskul.index') }}" class="btn btn-secondary">Batal</a>
                        <button type="submit" class="btn btn-primary">
                            <i class="ti ti-device-floppy me-1"></i>Simpan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection