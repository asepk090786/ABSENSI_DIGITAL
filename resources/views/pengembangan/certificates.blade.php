@extends('layouts.app')

@section('title','Sertifikat Saya')

@section('content')
    <h3>Sertifikat Saya</h3>
    <table class="table">
        <thead><tr><th>Kegiatan</th><th>Peserta</th><th>Nomor Sertifikat</th><th>Download</th><th>Verifikasi</th></tr></thead>
        <tbody>
        @foreach($certs as $c)
            <tr>
                <td>{{ optional($c->pengembangan)->nama_kegiatan ?? $c->pengembangan_id }}</td>
                <td>{{ $c->peserta_type }} #{{ $c->peserta_id }}</td>
                <td>{{ $c->nomor_sertifikat ?? '-' }}</td>
                <td><a href="{{ route('pengembangan.certificates.download',$c->id) }}" class="btn btn-sm btn-primary">Download</a></td>
                <td><a href="{{ route('pengembangan.verify',$c->barcode) }}" class="btn btn-sm btn-info">Verify</a></td>
            </tr>
        @endforeach
        </tbody>
    </table>
@endsection
