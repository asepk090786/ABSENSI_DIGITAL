@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header border-0 pt-3 pb-2">
                    <h3 class="card-title">Detail Guru Piket</h3>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4 text-center">
                            @if($gurupiket->foto)
                                <img src="{{ asset('storage/' . $gurupiket->foto) }}" alt="Foto" class="rounded" style="max-height: 300px; max-width: 100%;">
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
                                    <td>{{ $gurupiket->nama }}</td>
                                </tr>
                                <tr>
                                    <th>NIP</th>
                                    <td>{{ $gurupiket->nip ?? '-' }}</td>
                                </tr>
                                <tr>
                                    <th>Email</th>
                                    <td>{{ $gurupiket->email ?? '-' }}</td>
                                </tr>
                                <tr>
                                    <th>Telepon</th>
                                    <td>{{ $gurupiket->telepon ?? '-' }}</td>
                                </tr>
                                <tr>
                                    <th>Status</th>
                                    <td>
                                        @if($gurupiket->is_active)
                                            <span class="badge bg-success">Aktif</span>
                                        @else
                                            <span class="badge bg-secondary">Tidak Aktif</span>
                                        @endif
                                    </td>
                                </tr>
                            </table>
                        </div>
                    </div>

                    @if($gurupiket->alamat)
                    <div class="row mt-4">
                        <div class="col-12">
                            <h5>Alamat</h5>
                            <p>{{ $gurupiket->alamat }}</p>
                        </div>
                    </div>
                    @endif

                    <div class="mt-2">
                        <a href="{{ route('guru_piket.edit', $gurupiket->id) }}" class="btn btn-warning">
                            <i class="ti ti-edit"></i> Edit
                        </a>
                        <a href="{{ route('guru_piket.index') }}" class="btn btn-secondary">
                            <i class="ti ti-arrow-left"></i> Kembali
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
