@extends('layouts.app')

@section('title','Tambah Semester')

@section('content')
    <h3>Tambah Semester</h3>

    <form method="POST" action="{{ route('setting.semester.store') }}">
        @csrf
        <div class="mb-3">
            <label>Tahun Ajaran</label>
            <input type="text" class="form-control" value="{{ optional($active_tahun)->nama_tahun ?? 'N/A' }}" disabled>
            <input type="hidden" name="tahun_ajaran_id" value="{{ optional($active_tahun)->id }}">
        </div>
        <div class="mb-3">
            <label>Nama Semester</label>
            <select name="nama_semester" class="form-select" required>
                <option value="Semester 1 (Ganjil)">Semester 1 (Ganjil)</option>
                <option value="Semester 2 (Genap)">Semester 2 (Genap)</option>
            </select>
        </div>
        <button class="btn btn-primary">Simpan</button>
    </form>
@endsection
