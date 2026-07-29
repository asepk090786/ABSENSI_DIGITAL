@extends('layouts.app')

@section('title','Verifikasi Sertifikat')

@section('content')
    @if(!$valid)
        <div class="alert alert-danger">Kode tidak valid</div>
    @else
        <h3>Sertifikat Valid</h3>
        <p><strong>Kegiatan ID:</strong> {{ $cert->pengembangan_id }}</p>
        <p><strong>Peserta:</strong> {{ $cert->peserta_type }} #{{ $cert->peserta_id }}</p>
        <p><strong>Diterbitkan:</strong> {{ $cert->created_at }}</p>
        <p><strong>Terverifikasi:</strong> {{ $cert->verified_at ?? 'Belum' }}</p>
    @endif
@endsection
