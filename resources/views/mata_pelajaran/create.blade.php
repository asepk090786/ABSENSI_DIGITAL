@extends('layouts.app', ['pageSlug' => 'mata_pelajaran'])

@section('title','Tambah Mata Pelajaran')

@section('content')
<div class="row">
    <div class="col-md-8 mx-auto">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h4 class="card-title fw-semibold m-0">Tambah Mata Pelajaran</h4>
                <a href="{{ route('mata_pelajaran.index') }}" class="btn btn-secondary btn-sm">Kembali</a>
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('mata_pelajaran.store') }}">
                    @csrf
                    <div class="mb-2">
                        <label class="form-label">Mata Pelajaran <span class="text-danger">*</span></label>
                        <input type="text" name="nama_mapel" class="form-control @error('nama_mapel') is-invalid @enderror" value="{{ old('nama_mapel') }}" required>
                        @error('nama_mapel')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="mb-2">
                        <label class="form-label">Kode Pelajaran</label>
                        <input type="text" name="kode_mapel" class="form-control @error('kode_mapel') is-invalid @enderror" value="{{ old('kode_mapel') }}">
                        @error('kode_mapel')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="mb-2">
                        <label class="form-label">Kategori <span class="text-danger">*</span></label>
                        <select name="kategori" class="form-select @error('kategori') is-invalid @enderror" required>
                            <option value="">-- Pilih Kategori --</option>
                            @php $kategoriList = ['Umum','Jurusan','Pilihan','Tingkat lanjut','Mulok']; @endphp
                            @foreach($kategoriList as $kat)
                                <option value="{{ $kat }}" {{ old('kategori') == $kat ? 'selected' : '' }}>{{ $kat }}</option>
                            @endforeach
                        </select>
                        @error('kategori')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="mb-2">
                        <label class="form-label">Jenis Kegiatan <span class="text-muted">(Opsional)</span></label>
                        <select name="jenis_kegiatan_id" class="form-select @error('jenis_kegiatan_id') is-invalid @enderror">
                            <option value="">-- Pilih Jenis Kegiatan --</option>
                            @if(isset($jenisKegiatanList))
                                @foreach($jenisKegiatanList as $jk)
                                    <option value="{{ $jk->id }}" {{ old('jenis_kegiatan_id') == $jk->id ? 'selected' : '' }}>{{ $jk->nama }}</option>
                                @endforeach
                            @endif
                        </select>
                        @error('jenis_kegiatan_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="mt-4">
                        <button type="submit" class="btn btn-primary">
                            <i class="ti ti-device-floppy me-2"></i>Simpan
                        </button>
                        <a href="{{ route('mata_pelajaran.index') }}" class="btn btn-secondary">Batal</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
