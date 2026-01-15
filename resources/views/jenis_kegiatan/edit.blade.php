@extends('layouts.app', ['pageSlug' => 'jenis-kegiatan'])

@section('title','Edit Jenis Kegiatan')

@section('content')
<div class="row">
    <div class="col-md-6 offset-md-3">
        <div class="card">
            <div class="card-header">
                <h4 class="card-title mb-0">Edit Jenis Kegiatan</h4>
            </div>
            <div class="card-body">
                <form action="{{ route('jenis_kegiatan.update', $item->id) }}" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="mb-3">
                        <label for="nama" class="form-label">Nama Jenis Kegiatan</label>
                        <input type="text" name="nama" id="nama" class="form-control" required value="{{ old('nama', $item->nama) }}">
                    </div>
                    <div class="mb-3">
                        <label for="kode" class="form-label">Kode</label>
                        <input type="text" name="kode" id="kode" class="form-control" required value="{{ old('kode', $item->kode) }}">
                    </div>
                    <div class="d-flex justify-content-between">
                        <a href="{{ route('jenis_kegiatan.index') }}" class="btn btn-secondary">Kembali</a>
                        <button type="submit" class="btn btn-primary">Update</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
