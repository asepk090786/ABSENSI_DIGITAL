@extends('layouts.app', ['pageSlug' => 'supervisi'])

@section('title', 'Edit Instrumen Supervisi')

@section('content')
<div class="row">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h4 class="card-title mb-0">Edit Instrumen Supervisi</h4>
            </div>
            <div class="card-body">
                <form action="{{ route('supervisi.instrumen.update', $instrumen) }}" method="POST">
                    @csrf
                    @method('PUT')

                    <div class="mb-3">
                        <label class="form-label">Nama Instrumen</label>
                        <input type="text" name="nama" class="form-control @error('nama') is-invalid @enderror" 
                               value="{{ old('nama', $instrumen->nama) }}" required>
                        @error('nama') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Tipe Instrumen</label>
                        <select name="tipe" class="form-select @error('tipe') is-invalid @enderror" required>
                            <option value="">Pilih Tipe</option>
                            @foreach($tipeInstrumen as $tipe)
                                <option value="{{ $tipe }}" {{ old('tipe', $instrumen->tipe) == $tipe ? 'selected' : '' }}>
                                    {{ ucfirst($tipe) }}
                                </option>
                            @endforeach
                        </select>
                        @error('tipe') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Kategori</label>
                        <input type="text" name="kategori" class="form-control @error('kategori') is-invalid @enderror" 
                               value="{{ old('kategori', $instrumen->kategori) }}">
                        <small class="text-muted">Contoh: Observasi Kelas, Checklist Guru, dll</small>
                        @error('kategori') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Deskripsi</label>
                        <textarea name="deskripsi" class="form-control @error('deskripsi') is-invalid @enderror" rows="4">{{ old('deskripsi', $instrumen->deskripsi) }}</textarea>
                        @error('deskripsi') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="mb-3">
                        <div class="form-check">
                            <input type="checkbox" name="is_active" value="1" class="form-check-input" 
                                   {{ old('is_active', $instrumen->is_active) ? 'checked' : '' }}>
                            <label class="form-check-label">Aktif</label>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
                    <a href="{{ route('supervisi.instrumen.index') }}" class="btn btn-secondary">Batal</a>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
