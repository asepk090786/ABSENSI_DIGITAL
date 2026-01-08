@extends('layouts.app', ['pageSlug' => 'jam_belajar'])

@section('title','Edit Jam Belajar')

@section('content')
<div class="row">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h4 class="card-title mb-0">Edit Jam Belajar</h4>
            </div>
            <div class="card-body">
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
            </div>
        </div>
    </div>
</div>
@endsection
