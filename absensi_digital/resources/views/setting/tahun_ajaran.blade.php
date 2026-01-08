@extends('layouts.app', ['pageSlug' => 'setting'])

@section('title','Tahun Ajaran')

@section('content')
<div class="row">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h4 class="card-title mb-0">Tahun Ajaran</h4>
                <a href="{{ route('setting.tahun_ajaran.create') }}" class="btn btn-primary btn-sm">Tambah</a>
            </div>
            <div class="card-body">
                @if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif

                <div class="table-responsive">
                    <table class="table table-sm">
                        <thead>
                            <tr><th>Tahun</th><th>Status</th><th>Aksi</th></tr>
                        </thead>
                        <tbody>
                        @foreach($tahuns as $t)
                            <tr>
                                <td>{{ $t->nama_tahun }}</td>
                                <td>
                                    @if($t->is_active)
                                        <span class="badge bg-success">Aktif</span>
                                    @else
                                        <span class="badge bg-secondary">Tidak Aktif</span>
                                    @endif
                                </td>
                                <td>
                                    @if(!$t->is_active)
                                        <form action="{{ route('setting.tahun_ajaran.activate', $t->id) }}" method="POST" class="d-inline">
                                            @csrf
                                            <button class="btn btn-sm btn-success">Aktifkan</button>
                                        </form>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
