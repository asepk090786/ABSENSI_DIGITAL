@extends('layouts.app', ['pageSlug' => 'mata_pelajaran'])

@section('title','Detail Mata Pelajaran')

@section('content')
<div class="row">
    <div class="col-md-8 mx-auto">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h4 class="card-title fw-semibold m-0">Detail Mata Pelajaran</h4>
                <a href="{{ route('mata_pelajaran.index') }}" class="btn btn-secondary btn-sm">Kembali</a>
            </div>
            <div class="card-body">
                <dl class="row">
                    <dt class="col-sm-3">Nama Mapel</dt>
                    <dd class="col-sm-9">{{ $mata_pelajaran->nama_mapel }}</dd>

                    <dt class="col-sm-3">Kode</dt>
                    <dd class="col-sm-9">{{ $mata_pelajaran->kode_mapel ?? '-' }}</dd>

                    <dt class="col-sm-3">Kategori</dt>
                    <dd class="col-sm-9">{{ $mata_pelajaran->kategori ?? '-' }}</dd>

                    <dt class="col-sm-3">Dibuat</dt>
                    <dd class="col-sm-9">{{ $mata_pelajaran->created_at }}</dd>

                    <dt class="col-sm-3">Diperbarui</dt>
                    <dd class="col-sm-9">{{ $mata_pelajaran->updated_at }}</dd>
                </dl>
            </div>
        </div>
    </div>
</div>
@endsection
