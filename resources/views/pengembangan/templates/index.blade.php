@extends('layouts.app')

@section('title','Template Sertifikat')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h3>Template Sertifikat</h3>
        <a href="{{ route('pengembangan.templates.create') }}" class="btn btn-primary">Buat Template Baru</a>
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <div class="card">
        <div class="card-body">
            <table class="table">
                <thead>
                    <tr><th>Nama</th><th>Format</th><th>Dibuat</th><th>Aksi</th></tr>
                </thead>
                <tbody>
                    @foreach($items as $it)
                        <tr>
                            <td>{{ $it->nama }}</td>
                            <td>{{ strtoupper($it->output_format ?? 'pdf') }}</td>
                            <td>{{ \Carbon\Carbon::parse($it->created_at)->format('d-m-Y') }}</td>
                            <td>
                                <a href="{{ route('pengembangan.templates.edit', $it->id) }}" class="btn btn-sm btn-secondary">Edit</a>
                                <form method="POST" action="{{ route('pengembangan.templates.destroy', $it->id) }}" style="display:inline-block" onsubmit="return confirm('Hapus template?')">
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
@endsection
