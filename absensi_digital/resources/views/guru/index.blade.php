@extends('layouts.app', ['pageSlug' => 'guru'])

@section('title','Guru')

@section('content')
<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h4 class="card-title mb-0">Guru</h4>
                <a href="{{ route('guru.create') }}" class="btn btn-primary btn-sm">Tambah</a>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-sm">
                        <thead>
                            <tr><th>Nama</th><th>Email</th><th>Telepon</th><th>Aksi</th></tr>
                        </thead>
                        <tbody>
                        @foreach($items as $it)
                            <tr>
                                <td>{{ $it->nama }}</td>
                                <td>{{ $it->email ?? '-' }}</td>
                                <td>{{ $it->telepon ?? '-' }}</td>
                                <td>
                                    <a href="{{ route('guru.edit',$it->id) }}" class="btn btn-sm btn-outline-secondary">Edit</a>
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