@extends('layouts.app', ['pageSlug' => 'supervisi'])

@section('title', 'Tambah Indikator Supervisi')

@section('content')
<div class="row">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h4 class="card-title mb-0">Tambah Indikator Supervisi</h4>
            </div>
            <div class="card-body">
                <form action="{{ route('supervisi.indikator.store') }}" method="POST">
                    @csrf

                    <div class="mb-3">
                        <label class="form-label">Instrumen</label>
                        <select name="instrument_id" class="form-select @error('instrument_id') is-invalid @enderror" required>
                            <option value="">Pilih Instrumen</option>
                            @foreach($instruments as $instrument)
                                <option value="{{ $instrument->id }}" {{ old('instrument_id') == $instrument->id ? 'selected' : '' }}>
                                    {{ $instrument->nama }}
                                </option>
                            @endforeach
                        </select>
                        @error('instrument_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Kategori</label>
                        <select name="kategori" class="form-select @error('kategori') is-invalid @enderror" required>
                            <option value="">Pilih Kategori</option>
                            @foreach($kategoris as $kat)
                                <option value="{{ $kat }}" {{ old('kategori') == $kat ? 'selected' : '' }}>
                                    {{ $kat }}
                                </option>
                            @endforeach
                        </select>
                        @error('kategori') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Indikator</label>
                        <textarea name="indikator" class="form-control @error('indikator') is-invalid @enderror" rows="2" 
                                  placeholder="Tuliskan indikator yang akan diamati" required>{{ old('indikator') }}</textarea>
                        @error('indikator') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Deskripsi</label>
                        <textarea name="deskripsi" class="form-control @error('deskripsi') is-invalid @enderror" rows="3" 
                                  placeholder="Jelaskan deskripsi indikator (opsional)">{{ old('deskripsi') }}</textarea>
                        @error('deskripsi') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Bobot</label>
                                <input type="number" name="bobot" class="form-control @error('bobot') is-invalid @enderror" 
                                       value="{{ old('bobot', 1) }}" min="1">
                                @error('bobot') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Urutan</label>
                                <input type="number" name="urutan" class="form-control @error('urutan') is-invalid @enderror" 
                                       value="{{ old('urutan', 0) }}" min="0">
                                @error('urutan') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-primary">Simpan</button>
                    <a href="{{ route('supervisi.indikator.index') }}" class="btn btn-secondary">Batal</a>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
