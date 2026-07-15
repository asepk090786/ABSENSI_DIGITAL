@extends('layouts.app')

@section('title','Tambah Tahun Ajaran')

@section('content')
    <h3>Tambah Tahun Ajaran</h3>

    <form method="POST" action="{{ route('setting.tahun_ajaran.store') }}">
        @csrf
        <div class="mb-2">
            <label>Nama Tahun (e.g., 2025/2026)</label>
            <input name="nama_tahun" class="form-control" value="{{ old('nama_tahun') }}" required>
            @error('nama_tahun')<div class="text-danger">{{ $message }}</div>@enderror
        </div>
        <button class="btn btn-primary">Simpan</button>
    </form>
@endsection
