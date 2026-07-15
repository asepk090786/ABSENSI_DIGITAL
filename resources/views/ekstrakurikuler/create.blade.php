@extends('layouts.app', ['pageSlug' => 'ekstrakurikuler'])

@section('title','Tambah Ekstrakurikuler')

@section('content')
<div class="row">
    <div class="col-md-8 mx-auto">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h4 class="card-title fw-semibold m-0">Tambah Ekstrakurikuler</h4>
                <a href="{{ route('ekstrakurikuler.index') }}" class="btn btn-secondary btn-sm">Kembali</a>
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('ekstrakurikuler.store') }}">
                    @csrf
                    <div class="mb-2">
                        <label class="form-label">Nama Ekstrakurikuler <span class="text-danger">*</span></label>
                        <input type="text" name="nama_ekskul" class="form-control @error('nama_ekskul') is-invalid @enderror" value="{{ old('nama_ekskul') }}" required>
                        @error('nama_ekskul')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="mb-2">
                        <label class="form-label">Deskripsi</label>
                        <textarea name="deskripsi" class="form-control @error('deskripsi') is-invalid @enderror" rows="3">{{ old('deskripsi') }}</textarea>
                        @error('deskripsi')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="mb-2">
                        <label class="form-label">Nama Pembina <span class="text-danger">*</span></label>
                        <select name="pembina_id" class="form-select @error('pembina_id') is-invalid @enderror" required>
                            <option value="">-- Pilih Pembina --</option>
                            @forelse($pembina as $p)
                                <option value="{{ $p->id }}" {{ old('pembina_id') == $p->id ? 'selected' : '' }}>
                                    {{ $p->nama }} @if($p->nip)({{ $p->nip }})@endif
                                </option>
                            @empty
                                <option value="" disabled>Data pembina belum tersedia</option>
                            @endforelse
                        </select>
                        @error('pembina_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="mt-4">
                        <button type="submit" class="btn btn-primary">
                            <i class="ti ti-device-floppy me-2"></i>Simpan
                        </button>
                        <a href="{{ route('ekstrakurikuler.index') }}" class="btn btn-secondary">Batal</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
