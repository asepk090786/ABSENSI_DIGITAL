@extends('layouts.app', ['pageSlug' => 'jenis-pelanggaran'])

@section('title','Edit Jenis Pelanggaran')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header border-0 pt-3 pb-2">
                <h4 class="card-title fw-semibold m-0">Edit Jenis Pelanggaran</h4>
            </div>
            <div class="card-body">
                <form action="{{ route('jenis_pelanggaran.update', $item->id) }}" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="mb-2">
                        <label class="form-label">Kode</label>
                        <input type="text" name="kode" class="form-control @error('kode') is-invalid @enderror" value="{{ old('kode', $item->kode) }}" required>
                        @error('kode')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="mb-2">
                        <label class="form-label">Nama Jenis Pelanggaran</label>
                        <input type="text" name="nama" class="form-control @error('nama') is-invalid @enderror" value="{{ old('nama', $item->nama) }}" required>
                        @error('nama')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="mb-2">
                        <label class="form-label">Point Default</label>
                        <input type="number" name="poin_default" min="0" max="1000" class="form-control @error('poin_default') is-invalid @enderror" value="{{ old('poin_default', $item->poin_default) }}" required>
                        @error('poin_default')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="mb-2 form-check">
                        <input type="hidden" name="is_active" value="0">
                        <input type="checkbox" name="is_active" value="1" class="form-check-input" id="is_active" {{ old('is_active', $item->is_active) ? 'checked' : '' }}>
                        <label class="form-check-label" for="is_active">Aktif</label>
                    </div>
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary">Update</button>
                        <a href="{{ route('jenis_pelanggaran.index') }}" class="btn btn-secondary">Batal</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
