@extends('layouts.app', ['pageSlug' => 'kegiatan'])

@section('title','Edit Kegiatan')

@section('content')
<div class="row">
    <div class="col-md-8 mx-auto">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h4 class="card-title mb-0">Edit Kegiatan Sekolah</h4>
                <a href="{{ route('kegiatan.index') }}" class="btn btn-secondary btn-sm">Kembali</a>
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('kegiatan.update', $kegiatan->id) }}">
                    @csrf
                    @method('PUT')
                    <div class="mb-3">
                        <label class="form-label">Nama Kegiatan <span class="text-danger">*</span></label>
                        <input type="text" name="nama_kegiatan" class="form-control @error('nama_kegiatan') is-invalid @enderror" value="{{ old('nama_kegiatan', $kegiatan->nama_kegiatan) }}" required>
                        @error('nama_kegiatan')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Kode Kegiatan</label>
                        <input type="text" name="kode_kegiatan" class="form-control @error('kode_kegiatan') is-invalid @enderror" value="{{ old('kode_kegiatan', $kegiatan->kode_kegiatan) }}">
                        @error('kode_kegiatan')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Kategori <span class="text-danger">*</span></label>
                        <select name="kategori" class="form-select @error('kategori') is-invalid @enderror" required>
                            <option value="">-- Pilih Kategori --</option>
                            @php $kategoriList = ['Umum','Jurusan','Pilihan','Tingkat lanjut','Mulok']; @endphp
                            @foreach($kategoriList as $kat)
                                <option value="{{ $kat }}" {{ old('kategori', $kegiatan->kategori) == $kat ? 'selected' : '' }}>{{ $kat }}</option>
                            @endforeach
                        </select>
                        @error('kategori')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="mt-4">
                        <button type="submit" class="btn btn-primary">
                            <i class="ti ti-device-floppy me-2"></i>Simpan
                        </button>
                        <a href="{{ route('kegiatan.index') }}" class="btn btn-secondary">Batal</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
