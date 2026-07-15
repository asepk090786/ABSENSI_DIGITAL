@extends('layouts.app', ['pageSlug' => 'setting'])

@section('title','Tahun Ajaran')

@section('content')
<div class="row">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h4 class="card-title fw-semibold m-0">Tahun Ajaran</h4>
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
                                    <div class="btn-group btn-group-sm" role="group">
                                        <a href="{{ route('setting.tahun_ajaran.show', $t->id) }}" class="btn btn-info">Detail</a>
                                        <a href="{{ route('setting.tahun_ajaran.edit', $t->id) }}" class="btn btn-warning">Edit</a>
                                        <form action="{{ route('setting.tahun_ajaran.destroy', $t->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Hapus tahun ajaran ini?')">
                                            @csrf
                                            @method('DELETE')
                                            <button class="btn btn-danger">Hapus</button>
                                        </form>
                                        @if(!$t->is_active)
                                            <form action="{{ route('setting.tahun_ajaran.activate', $t->id) }}" method="POST" class="d-inline">
                                                @csrf
                                                <button class="btn btn-success">Aktifkan</button>
                                            </form>
                                        @else
                                            <form action="{{ route('setting.tahun_ajaran.deactivate', $t->id) }}" method="POST" class="d-inline">
                                                @csrf
                                                <button class="btn btn-secondary">Nonaktifkan</button>
                                            </form>
                                        @endif
                                    </div>
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
