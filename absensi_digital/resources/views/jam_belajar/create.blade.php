@extends('layouts.app', ['pageSlug' => 'jam_belajar'])

@section('title','Tambah Jam Belajar')

@section('content')
<div class="row">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h4 class="card-title mb-0">Tambah Jam Belajar</h4>
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('jam_belajar.store') }}">
                    @csrf
                    <div class="mb-3">
                        <label>Hari</label>
                        <input name="hari" class="form-control" value="{{ old('hari') }}" required>
                    </div>
                    <div class="mb-3">
                        <label>Jam Mulai (HH:MM)</label>
                        <input name="jam_mulai" class="form-control" value="{{ old('jam_mulai') }}" required>
                    </div>
                    <div class="mb-3">
                        <label>Jam Selesai (HH:MM)</label>
                        <input name="jam_selesai" class="form-control" value="{{ old('jam_selesai') }}" required>
                    </div>
                    <div class="mb-3">
                        <label>Jenis</label>
                        <input name="jenis" class="form-control" value="{{ old('jenis','KBM') }}" required>
                    </div>
                    <button class="btn btn-primary">Simpan</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
