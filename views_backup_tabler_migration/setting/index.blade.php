@extends('layouts.app', ['pageSlug' => 'setting'])

@section('title','Pengaturan')

@section('content')
    <h3>Pengaturan Sistem</h3>

    <div class="row mt-4">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <strong>Tahun Ajaran Aktif</strong>
                </div>
                <div class="card-body">
                    @if($active_tahun)
                        <p><strong>{{ $active_tahun->nama_tahun }}</strong></p>
                    @else
                        <p class="text-danger">Tidak ada tahun ajaran aktif</p>
                    @endif
                    <a href="{{ route('setting.tahun_ajaran') }}" class="btn btn-sm btn-outline-primary">Manage</a>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <strong>Semester Aktif</strong>
                </div>
                <div class="card-body">
                    @if($active_semester)
                        <p><strong>{{ $active_semester->nama_semester }}</strong></p>
                    @else
                        <p class="text-danger">Tidak ada semester aktif</p>
                    @endif
                    <a href="{{ route('setting.semester') }}" class="btn btn-sm btn-outline-primary">Manage</a>
                </div>
            </div>
        </div>
    </div>
@endsection
