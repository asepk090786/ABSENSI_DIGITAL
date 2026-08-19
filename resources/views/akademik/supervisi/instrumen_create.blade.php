@extends('layouts.app', ['pageSlug' => 'supervisi'])

@section('title', 'Tambah Instrumen Supervisi')

@section('content')
<div class="row">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h4 class="card-title mb-0">Tambah Instrumen Supervisi</h4>
            </div>
            <div class="card-body">
                <form action="{{ route('supervisi.instrumen.store') }}" method="POST">
                    @csrf

                    <div class="mb-3">
                        <label class="form-label">Nama Instrumen</label>
                        <input type="text" name="nama" class="form-control @error('nama') is-invalid @enderror" 
                               value="{{ old('nama') }}" required>
                        <small class="text-muted">Contoh: Lembar Observasi Kelas, Checklist Guru, etc.</small>
                        @error('nama') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Tipe Instrumen</label>
                        <select name="tipe" class="form-select @error('tipe') is-invalid @enderror" required>
                            <option value="">Pilih Tipe</option>
                            @foreach($tipeInstrumen as $tipe)
                                <option value="{{ $tipe }}" {{ old('tipe') == $tipe ? 'selected' : '' }}>
                                    {{ ucfirst($tipe) }}
                                </option>
                            @endforeach
                        </select>
                        <small class="text-muted">Checklist: daftar item ya/tidak | Skala: rating 1-5 | Deskriptif: narasi bebas</small>
                        @error('tipe') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Kategori</label>
                        <input type="text" name="kategori" class="form-control @error('kategori') is-invalid @enderror" 
                               value="{{ old('kategori') }}">
                        <small class="text-muted">Contoh: Observasi Kelas, Checklist Guru, dll</small>
                        @error('kategori') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Deskripsi</label>
                        <textarea name="deskripsi" class="form-control @error('deskripsi') is-invalid @enderror" rows="4" placeholder="Jelaskan tujuan dan penggunaan instrumen ini">{{ old('deskripsi') }}</textarea>
                        @error('deskripsi') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <button type="submit" class="btn btn-primary">Simpan</button>
                    <a href="{{ route('supervisi.instrumen.index') }}" class="btn btn-secondary">Batal</a>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
