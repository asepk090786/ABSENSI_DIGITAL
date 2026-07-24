@extends('layouts.app')

@section('title','Pengembangan Diri')

@section('content')
    <h3>Pengembangan Diri</h3>
    <a href="{{ route('pengembangan.create') }}" class="btn btn-primary mb-3">Buat Kegiatan</a>
    <table class="table">
        <thead><tr><th>Nama</th><th>Jenis</th><th>Tanggal</th><th></th></tr></thead>
        <tbody>
        @foreach($items as $it)
            <tr>
                <td>{{ $it->nama_kegiatan }}</td>
                <td>{{ $it->jenis_kegiatan }}</td>
                <td>{{ $it->tanggal_mulai ? $it->tanggal_mulai->format('Y-m-d') : '-' }}</td>
                <td><a href="{{ route('pengembangan.show',$it->id) }}" class="btn btn-sm btn-secondary">Detail</a></td>
            </tr>
        @endforeach
        </tbody>
    </table>
    {{ $items->links() }}
@endsection
