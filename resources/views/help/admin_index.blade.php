@extends('layouts.app')

@section('title','Help Pages')

@section('page-header')
    <div class="page-header">
        <h2 class="page-title">Manage Help Pages</h2>
    </div>
@endsection

@section('content')
    <div class="mb-3">
        <a href="{{ route('help.admin.create') }}" class="btn btn-primary">Buat Halaman Help Baru</a>
    </div>

    <div class="card">
        <div class="card-body">
            @if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
            @if(session('error'))<div class="alert alert-danger">{{ session('error') }}</div>@endif

            @if(count($items) === 0)
                <div class="text-muted">Belum ada halaman help.</div>
            @else
                <table class="table">
                    <thead>
                        <tr><th>Judul</th><th>Slug</th><th>Dibuat</th><th>Terakhir</th><th>Aksi</th></tr>
                    </thead>
                    <tbody>
                        @foreach($items as $it)
                            <tr>
                                <td>{{ $it['title'] }}</td>
                                <td>{{ $it['slug'] }}</td>
                                <td>{{ $it['created_at'] ?? '-' }}</td>
                                <td>{{ $it['updated_at'] ?? '-' }}</td>
                                <td>
                                    <a href="{{ url('/help/' . $it['slug']) }}" class="btn btn-sm btn-outline-success" target="_blank" title="Lihat">Lihat</a>
                                    <a href="{{ route('help.admin.edit', $it['slug']) }}" class="btn btn-sm btn-outline-primary" title="Edit">Edit</a>
                                    <form method="POST" action="{{ route('help.admin.destroy', $it['slug']) }}" style="display:inline-block;margin-left:.5rem;" onsubmit="return confirm('Hapus halaman ini?');">
                                        @csrf @method('DELETE')
                                        <button class="btn btn-sm btn-outline-danger">Hapus</button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif
        </div>
    </div>
@endsection
