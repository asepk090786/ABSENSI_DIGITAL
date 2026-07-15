@extends('layouts.app')

@section('title','Edit Tahun Ajaran')

@section('content')
    <h3>Edit Tahun Ajaran</h3>

    <form method="POST" action="{{ route('setting.tahun_ajaran.update', $tahunAjaran->id) }}">
        @csrf
        @method('PUT')
        <div class="mb-2">
            <label>Nama Tahun</label>
            <input name="nama_tahun" class="form-control" value="{{ old('nama_tahun', $tahunAjaran->nama_tahun) }}" required>
            @error('nama_tahun')<div class="text-danger">{{ $message }}</div>@enderror
        </div>
        <button class="btn btn-primary">Simpan</button>
        <a href="{{ route('setting.tahun_ajaran') }}" class="btn btn-link">Batal</a>
    </form>
@endsection
