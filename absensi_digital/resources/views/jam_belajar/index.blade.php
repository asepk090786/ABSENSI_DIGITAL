@extends('layouts.app', ['pageSlug' => 'jam_belajar'])

@section('title','Jam Belajar')

@section('content')
<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h4 class="card-title mb-0">Jam Belajar</h4>
                <a href="{{ route('jam_belajar.create') }}" class="btn btn-primary btn-sm">Tambah</a>
            </div>
            <div class="card-body">

                @if(session('success'))
                    <div class="alert alert-success">{{ session('success') }}</div>
                @endif

                <div class="table-responsive">
                    <table class="table table-sm">
                        <thead>
                            <tr><th>Hari</th><th>Mulai</th><th>Selesai</th><th>Jenis</th><th>Aksi</th></tr>
                        </thead>
                        <tbody>
                        @foreach($items as $it)
                            <tr>
                                <td>{{ $it->hari }}</td>
                                <td>{{ \Carbon\Carbon::parse($it->jam_mulai)->format('H:i') }}</td>
                                <td>{{ \Carbon\Carbon::parse($it->jam_selesai)->format('H:i') }}</td>
                                <td>{{ $it->jenis }}</td>
                                <td>
                                    <a href="{{ route('jam_belajar.edit',$it->id) }}" class="btn btn-sm btn-outline-secondary">Edit</a>
                                    <form action="{{ route('jam_belajar.destroy',$it->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Hapus?')">
                                        @csrf
                                        @method('DELETE')
                                        <button class="btn btn-sm btn-danger">Hapus</button>
                                    </form>
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
