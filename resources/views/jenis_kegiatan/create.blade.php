@extends('layouts.app', ['pageSlug' => 'jenis-kegiatan'])

@section('title','Tambah Jenis Kegiatan')

@section('content')
<div class="row">
    <div class="col-md-6 offset-md-3">
        <div class="card">
            <div class="card-header border-0 pt-3 pb-2">
                <h4 class="card-title fw-semibold m-0">Tambah Jenis Kegiatan</h4>
            </div>
            <div class="card-body">
                <form action="{{ route('jenis_kegiatan.store') }}" method="POST">
                    @csrf
                    <div class="mb-2">
                        <label for="nama" class="form-label">Nama Jenis Kegiatan</label>
                        <input type="text" name="nama" id="nama" class="form-control" required value="{{ old('nama') }}">
                    </div>
                    <div class="mb-2">
                        <label for="kode" class="form-label">Kode</label>
                        <input type="text" name="kode" id="kode" class="form-control" required value="{{ old('kode') }}">
                    </div>
                    <div class="d-flex justify-content-between">
                        <a href="{{ route('jenis_kegiatan.index') }}" class="btn btn-secondary">Kembali</a>
                        <button type="submit" class="btn btn-primary">Simpan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
