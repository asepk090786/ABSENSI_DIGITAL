@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Detail Wakil Kepala Sekolah</h3>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4 text-center">
                            @if($wakil->foto)
                                <img src="{{ asset('storage/' . $wakil->foto) }}" alt="Foto" class="rounded" style="max-height: 300px; max-width: 100%;">
                            @else
                                <div class="bg-light rounded d-flex align-items-center justify-content-center" style="height: 300px;">
                                    <i class="ti ti-user" style="font-size: 100px; color: #999;"></i>
                                </div>
                            @endif
                        </div>
                        <div class="col-md-8">
                            <table class="table table-borderless">
                                <tr>
                                    <th style="width: 30%;">Nama</th>
                                    <td>{{ $wakil->nama }}</td>
                                </tr>
                                <tr>
                                    <th>NIP</th>
                                    <td>{{ $wakil->nip ?? '-' }}</td>
                                </tr>
                                <tr>
                                    <th>Email</th>
                                    <td>{{ $wakil->email ?? '-' }}</td>
                                </tr>
                                <tr>
                                    <th>Telepon</th>
                                    <td>{{ $wakil->telepon ?? '-' }}</td>
                                </tr>
                                <tr>
                                    <th>Status</th>
                                    <td>
                                        @if($wakil->status == 'Aktif')
                                            <span class="badge bg-success">Aktif</span>
                                        @else
                                            <span class="badge bg-secondary">Tidak Aktif</span>
                                        @endif
                                    </td>
                                </tr>
                            </table>
                        </div>
                    </div>

                    @if($wakil->alamat)
                    <div class="row mt-4">
                        <div class="col-12">
                            <h5>Alamat</h5>
                            <p>{{ $wakil->alamat }}</p>
                        </div>
                    </div>
                    @endif

                    <div class="mt-3">
                        <a href="{{ route('wakil_kepala_sekolah.edit', $wakil->id) }}" class="btn btn-warning">
                            <i class="ti ti-edit"></i> Edit
                        </a>
                        <a href="{{ route('wakil_kepala_sekolah.index') }}" class="btn btn-secondary">
                            <i class="ti ti-arrow-left"></i> Kembali
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
