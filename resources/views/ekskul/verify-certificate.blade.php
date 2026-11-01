@extends('layouts.public')

@section('title', 'Verifikasi Sertifikat')

@section('content')
    @if(!$certificate)
        <div class="alert alert-danger">Kode sertifikat tidak valid.</div>
    @else
        <h3>Sertifikat Valid</h3>
        <div class="table-responsive">
            <table class="table table-bordered mt-3">
                <tbody>
                    <tr>
                        <th>Nama</th>
                        <td>{{ $certificate->siswa->nama ?? '-' }}</td>
                    </tr>
                    <tr>
                        <th>Nama Kegiatan</th>
                        <td>{{ $certificate->ekskulAgenda->judul ?? '-' }}</td>
                    </tr>
                    <tr>
                        <th>Ekstrakurikuler</th>
                        <td>{{ $certificate->ekstrakurikuler->nama ?? '-' }}</td>
                    </tr>
                    <tr>
                        <th>Tanggal Kegiatan</th>
                        <td>{{ $certificate->ekskulAgenda?->tanggal?->format('d-m-Y') ?? '-' }}</td>
                    </tr>
                    <tr>
                        <th>Nomor Sertifikat</th>
                        <td>{{ $certificate->nomor_sertifikat ?? '-' }}</td>
                    </tr>
                    <tr>
                        <th>Diterbitkan</th>
                        <td>{{ $certificate->created_at?->format('d-m-Y H:i') ?? '-' }}</td>
                    </tr>
                    <tr>
                        <th>Terverifikasi</th>
                        <td>{{ $certificate->verified_at?->format('d-m-Y H:i:s') ?? '-' }}</td>
                    </tr>
                </tbody>
            </table>
        </div>
    @endif
@endsection