@extends('layouts.app')

@section('title','Edit Semester')

@section('content')
    <h3>Edit Semester</h3>

    <form method="POST" action="{{ route('setting.semester.update', $semester->id) }}">
        @csrf
        @method('PUT')
        <div class="mb-3">
            <label>Tahun Ajaran</label>
            <input type="text" class="form-control" value="{{ optional($active_tahun)->nama_tahun ?? 'N/A' }}" disabled>
        </div>
        <div class="mb-3">
            <label>Nama Semester</label>
            <select name="nama_semester" class="form-select" required>
                <option value="Semester 1 (Ganjil)" {{ old('nama_semester', $semester->nama_semester) == 'Semester 1 (Ganjil)' ? 'selected' : '' }}>Semester 1 (Ganjil)</option>
                <option value="Semester 2 (Genap)" {{ old('nama_semester', $semester->nama_semester) == 'Semester 2 (Genap)' ? 'selected' : '' }}>Semester 2 (Genap)</option>
            </select>
            @error('nama_semester')<div class="text-danger">{{ $message }}</div>@enderror
        </div>
        <button class="btn btn-primary">Simpan</button>
        <a href="{{ route('setting.semester') }}" class="btn btn-link">Batal</a>
    </form>
@endsection
