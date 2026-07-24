@extends('layouts.app')

@section('title','Detail Pengembangan')

@section('content')
    <h3>{{ $item->nama_kegiatan }}</h3>
    <p><strong>Jenis:</strong> {{ \App\Models\JenisKegiatan::where('kode', $item->jenis_kegiatan)->value('nama') ?? $item->jenis_kegiatan }}</p>
    <p><strong>Pemateri:</strong>
        @if(is_array($item->pemateri))
            {{ implode(', ', $item->pemateri) }}
        @else
            {{ $item->pemateri }}
        @endif
    </p>
    <p>{{ $item->deskripsi }}</p>
    <h5>Peserta</h5>
    <ul>
        @foreach($item->peserta as $p)
            <li>{{ $p->peserta_type }} - {{ $p->peserta_id }}</li>
        @endforeach
    </ul>
    <form method="POST" action="{{ route('pengembangan.generate_certificates',$item->id) }}">
        @csrf
        <button class="btn btn-success">Generate Certificates</button>
    </form>
@endsection
