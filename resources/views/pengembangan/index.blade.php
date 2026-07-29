@extends('layouts.app')

@section('title','Pengembangan Diri')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h3 class="m-0">Pengembangan Diri</h3>
        <div>
            <a href="{{ route('pengembangan.create') }}" class="btn btn-primary">Buat Kegiatan</a>
            <a href="{{ route('pengembangan.templates.index') }}" class="btn btn-outline-secondary ms-2">Template Sertifikat</a>
        </div>
    </div>
    <table class="table">
        <thead><tr><th>Nama</th><th>Jenis</th><th>Tanggal</th><th>Aksi</th></tr></thead>
        <tbody>
        @foreach($items as $it)
            <tr>
                <td>{{ $it->nama_kegiatan }}</td>
                <td>{{ \App\Models\JenisKegiatan::where('kode', $it->jenis_kegiatan)->value('nama') ?? $it->jenis_kegiatan }}</td>
                <td>{{ $it->tanggal_mulai ? $it->tanggal_mulai->format('Y-m-d') : '-' }}</td>
                <td>
                    <a href="{{ route('pengembangan.show',$it->id) }}" class="btn btn-sm btn-secondary">Preview</a>
                    <a href="{{ route('pengembangan.edit',$it->id) }}" class="btn btn-sm btn-primary">Edit</a>
                    <form method="POST" action="{{ route('pengembangan.destroy', $it->id) }}" style="display:inline" onsubmit="return confirm('Hapus kegiatan ini?');">
                        @csrf
                        @method('DELETE')
                        <button class="btn btn-sm btn-danger">Hapus</button>
                    </form>
                </td>
            </tr>
        @endforeach
        </tbody>
    </table>
    {{ $items->links() }}
@endsection
