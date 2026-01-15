@extends('layouts.app')

@section('content')
@php
    use Illuminate\Support\Facades\Storage;
@endphp
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h3 class="card-title">Data Sekolah</h3>
                    @if(!$sekolah)
                        <a href="{{ route('sekolah.create') }}" class="btn btn-primary">
                            <i class="ti ti-plus"></i> Tambah Data Sekolah
                        </a>
                    @endif
                </div>
                <div class="card-body">
                    @if(session('success'))
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            {{ session('success') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    @if($sekolah)
                        <div class="row">
                            <div class="col-md-3 text-center">
                                @if($sekolah->logo)
                                    <img src="{{ asset('images/' . $sekolah->logo) }}" alt="Logo Sekolah" class="img-fluid rounded mb-3" style="max-height: 200px;"
                                         onerror="this.onerror=null;this.src='/images/default-school.png';">
                                @else
                                    <div class="bg-light rounded p-4 mb-3">
                                        <i class="ti ti-school" style="font-size: 100px; color: #ddd;"></i>
                                    </div>
                                @endif
                            </div>
                            <div class="col-md-9">
                                <table class="table table-bordered">
                                    <tbody>
                                        <tr>
                                            <th width="200">Nama Sekolah</th>
                                            <td>{{ $sekolah->nama_sekolah }}</td>
                                        </tr>
                                        <tr>
                                            <th>NPSN</th>
                                            <td>{{ $sekolah->npsn ?? '-' }}</td>
                                        </tr>
                                        <tr>
                                            <th>Jenjang</th>
                                            <td><span class="badge bg-primary">{{ $sekolah->jenjang }}</span></td>
                                        </tr>
                                        <tr>
                                            <th>Status</th>
                                            <td><span class="badge bg-info">{{ $sekolah->status }}</span></td>
                                        </tr>
                                        <tr>
                                            <th>Akreditasi</th>
                                            <td>{{ $sekolah->akreditasi ?? '-' }}</td>
                                        </tr>
                                        <tr>
                                            <th>Alamat</th>
                                            <td>{{ $sekolah->alamat }}</td>
                                        </tr>
                                        <tr>
                                            <th>Kelurahan</th>
                                            <td>{{ $sekolah->kelurahan ?? '-' }}</td>
                                        </tr>
                                        <tr>
                                            <th>Kecamatan</th>
                                            <td>{{ $sekolah->kecamatan ?? '-' }}</td>
                                        </tr>
                                        <tr>
                                            <th>Kota</th>
                                            <td>{{ $sekolah->kota }}</td>
                                        </tr>
                                        <tr>
                                            <th>Provinsi</th>
                                            <td>{{ $sekolah->provinsi }}</td>
                                        </tr>
                                        <tr>
                                            <th>Kode Pos</th>
                                            <td>{{ $sekolah->kode_pos ?? '-' }}</td>
                                        </tr>
                                        <tr>
                                            <th>Telepon</th>
                                            <td>{{ $sekolah->telepon ?? '-' }}</td>
                                        </tr>
                                        <tr>
                                            <th>Email</th>
                                            <td>{{ $sekolah->email ?? '-' }}</td>
                                        </tr>
                                        <tr>
                                            <th>Website</th>
                                            <td>
                                                @if($sekolah->website)
                                                    <a href="{{ $sekolah->website }}" target="_blank">{{ $sekolah->website }}</a>
                                                @else
                                                    -
                                                @endif
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                                <div class="mt-3">
                                    <a href="{{ route('sekolah.edit', $sekolah->id) }}" class="btn btn-warning">
                                        <i class="ti ti-edit"></i> Edit Data
                                    </a>
                                </div>
                            </div>
                        </div>
                    @else
                        <div class="alert alert-info">
                            <i class="ti ti-info-circle"></i> Belum ada data sekolah. Silakan tambahkan data sekolah terlebih dahulu.
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
