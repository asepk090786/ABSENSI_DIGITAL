@extends('layouts.app', ['pageSlug' => 'supervisi'])

@section('title', 'Detail Supervisi')

@section('content')
<div class="row">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h4 class="card-title mb-0">Detail Pengaturan Supervisi</h4>
                <div>
                    <a href="{{ route('akademik.supervisi.edit', $supervisi) }}" class="btn btn-sm btn-warning">Edit</a>
                    <a href="{{ route('akademik.supervisi') }}" class="btn btn-sm btn-secondary">Kembali</a>
                </div>
            </div>
            <div class="card-body">
                <table class="table table-borderless">
                    <tbody>
                        <tr>
                            <th style="width: 200px;">Nama Guru</th>
                            <td>{{ $supervisi->guru->user->name ?? $supervisi->guru->nama }}</td>
                        </tr>
                        <tr>
                            <th>Mata Pelajaran</th>
                            <td>{{ $supervisi->mataPelajaran->nama_mapel ?? '-' }}</td>
                        </tr>
                        <tr>
                            <th>Kelas</th>
                            <td>{{ $supervisi->kelas->nama_kelas ?? '-' }}</td>
                        </tr>
                        <tr>
                            <th>Tanggal Supervisi</th>
                            <td>{{ $supervisi->tanggal?->format('d-m-Y') ?? '-' }}</td>
                        </tr>
                        <tr>
                            <th>Jam KBM ke</th>
                            <td>{{ $supervisi->jam_ke }}</td>
                        </tr>
                        <tr>
                            <th>Keterangan</th>
                            <td>{{ $supervisi->keterangan ?? '-' }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
