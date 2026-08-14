@extends('layouts.app')

@section('title','Detail Tenaga Pendidikan')

@section('content')
<div class="row">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h4 class="card-title fw-semibold m-0">Detail Tenaga Pendidikan</h4>
                <div>
                    <a href="{{ route('tenaga_pendidikan.edit', $tenagaPendidikan) }}" class="btn btn-warning btn-sm">Edit</a>
                    <a href="{{ route('tenaga_pendidikan.index') }}" class="btn btn-secondary btn-sm">Kembali</a>
                </div>
            </div>
            <div class="card-body">
                <div class="row mb-3">
                    <div class="col-md-3"><strong>Nama:</strong></div>
                    <div class="col-md-9">{{ $tenagaPendidikan->nama }}</div>
                </div>
                <div class="row mb-3">
                    <div class="col-md-3"><strong>NIP:</strong></div>
                    <div class="col-md-9">{{ $tenagaPendidikan->nip ?? '-' }}</div>
                </div>
                <div class="row mb-3">
                    <div class="col-md-3"><strong>Jabatan:</strong></div>
                    <div class="col-md-9">{{ $tenagaPendidikan->jabatan ?? '-' }}</div>
                </div>
                <div class="row mb-3">
                    <div class="col-md-3"><strong>Jenis Kelamin:</strong></div>
                    <div class="col-md-9">{{ $tenagaPendidikan->jenis_kelamin === 'L' ? 'Laki-laki' : ($tenagaPendidikan->jenis_kelamin === 'P' ? 'Perempuan' : '-') }}</div>
                </div>
                <div class="row mb-3">
                    <div class="col-md-3"><strong>Tanggal Lahir:</strong></div>
                    <div class="col-md-9">{{ $tenagaPendidikan->tanggal_lahir ? \Carbon\Carbon::parse($tenagaPendidikan->tanggal_lahir)->format('d-m-Y') : '-' }}</div>
                </div>
                <div class="row mb-3">
                    <div class="col-md-3"><strong>Email:</strong></div>
                    <div class="col-md-9">{{ $tenagaPendidikan->email ?? '-' }}</div>
                </div>
                <div class="row mb-3">
                    <div class="col-md-3"><strong>Telepon:</strong></div>
                    <div class="col-md-9">{{ $tenagaPendidikan->telepon ?? '-' }}</div>
                </div>
                <div class="row mb-3">
                    <div class="col-md-3"><strong>Alamat:</strong></div>
                    <div class="col-md-9">{{ $tenagaPendidikan->alamat ?? '-' }}</div>
                </div>
                <hr>
                <h5 class="mb-3">Akun User</h5>
                @if($tenagaPendidikan->user)
                    <div class="alert alert-success">
                        <strong>Akun Sudah Tersedia</strong>
                        <table class="table table-sm mt-2">
                            <tr>
                                <td><strong>Username:</strong></td>
                                <td>{{ $tenagaPendidikan->user->username }}</td>
                            </tr>
                            <tr>
                                <td><strong>Email:</strong></td>
                                <td>{{ $tenagaPendidikan->user->email }}</td>
                            </tr>
                            <tr>
                                <td><strong>Role:</strong></td>
                                <td>{{ $tenagaPendidikan->user->role->role_name ?? '-' }}</td>
                            </tr>
                            <tr>
                                <td><strong>Status:</strong></td>
                                <td>
                                    @if($tenagaPendidikan->user->is_active)
                                        <span class="badge bg-success">Aktif</span>
                                    @else
                                        <span class="badge bg-danger">Tidak Aktif</span>
                                    @endif
                                </td>
                            </tr>
                        </table>
                    </div>
                @else
                    <div class="alert alert-warning">
                        <strong>Belum Ada Akun User</strong>
                        <p class="mt-2">Klik tombol di bawah untuk membuat akun user secara otomatis.</p>
                        <a href="{{ route('tenaga_pendidikan.generate-account', $tenagaPendidikan) }}" class="btn btn-success btn-sm">Buat Akun User</a>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
