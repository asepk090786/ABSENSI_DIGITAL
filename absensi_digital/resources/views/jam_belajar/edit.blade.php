@extends('layouts.app')

@section('title','Edit Jam Belajar')

@section('content')
    <h3>Edit Jam Belajar</h3>

    <form method="POST" action="{{ route('jam_belajar.update',$item->id) }}">
        @csrf
        @method('PUT')
        <div class="mb-3">
            <label>Hari</label>
            <input name="hari" class="form-control" value="{{ old('hari',$item->hari) }}" required>
        </div>
        <div class="mb-3">
            <label>Jam Mulai (HH:MM)</label>
            <input name="jam_mulai" class="form-control" value="{{ old('jam_mulai',\Carbon\Carbon::parse($item->jam_mulai)->format('H:i')) }}" required>
        </div>
        <div class="mb-3">
            <label>Jam Selesai (HH:MM)</label>
            <input name="jam_selesai" class="form-control" value="{{ old('jam_selesai',\Carbon\Carbon::parse($item->jam_selesai)->format('H:i')) }}" required>
        </div>
        <div class="mb-3">
            <label>Jenis</label>
            <input name="jenis" class="form-control" value="{{ old('jenis',$item->jenis) }}" required>
        </div>
        <button class="btn btn-primary">Simpan</button>
    </form>
@endsection
