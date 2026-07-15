@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h3 class="card-title">Detail Kepala Sekolah</h3>
                    <a href="{{ route('kepala_sekolah.index') }}" class="btn btn-secondary">
                        <i class="ti ti-arrow-left"></i> Kembali
                    </a>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3 text-center">
                            @if($kepalaSekolah->foto)
                                <img src="{{ asset('storage/' . $kepalaSekolah->foto) }}" alt="Foto" class="img-fluid rounded mb-2" style="max-height: 300px;">
                            @else
                                <div class="bg-light rounded p-5 mb-2">
                                    <i class="ti ti-user" style="font-size: 150px; color: #ddd;"></i>
                                </div>
                            @endif
                        </div>
                        <div class="col-md-9">
                            <table class="table table-bordered">
                                <tbody>
                                    <tr>
                                        <th width="200">Nama</th>
                                        <td>{{ $kepalaSekolah->nama }}</td>
                                    </tr>
                                    <tr>
                                        <th>NIP</th>
                                        <td>{{ $kepalaSekolah->nip ?? '-' }}</td>
                                    </tr>
                                    <tr>
                                        <th>Guru</th>
                                        <td>{{ $kepalaSekolah->guru->nama ?? '-' }}</td>
                                    </tr>
                                    <tr>
                                        <th>Pangkat/Golongan</th>
                                        <td>{{ $kepalaSekolah->pangkat_golongan ?? '-' }}</td>
                                    </tr>
                                    <tr>
                                        <th>Periode Jabatan</th>
                                        <td>
                                            {{ $kepalaSekolah->tanggal_mulai_jabatan->format('d F Y') }}
                                            @if($kepalaSekolah->tanggal_selesai_jabatan)
                                                - {{ $kepalaSekolah->tanggal_selesai_jabatan->format('d F Y') }}
                                            @else
                                                - Sekarang
                                            @endif
                                        </td>
                                    </tr>
                                    <tr>
                                        <th>Status</th>
                                        <td>
                                            @if($kepalaSekolah->status == 'Aktif')
                                                <span class="badge bg-success">Aktif</span>
                                            @else
                                                <span class="badge bg-secondary">Tidak Aktif</span>
                                            @endif
                                        </td>
                                    </tr>
                                    <tr>
                                        <th>Alamat</th>
                                        <td>{{ $kepalaSekolah->alamat ?? '-' }}</td>
                                    </tr>
                                    <tr>
                                        <th>Telepon</th>
                                        <td>{{ $kepalaSekolah->telepon ?? '-' }}</td>
                                    </tr>
                                    <tr>
                                        <th>Email</th>
                                        <td>{{ $kepalaSekolah->email ?? '-' }}</td>
                                    </tr>
                                </tbody>
                            </table>
                            <div class="mt-2">
                                <a href="{{ route('kepala_sekolah.edit', $kepalaSekolah->id) }}" class="btn btn-warning">
                                    <i class="ti ti-edit"></i> Edit Data
                                </a>
                                <form action="{{ route('kepala_sekolah.destroy', $kepalaSekolah->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Yakin ingin menghapus data ini?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-danger">
                                        <i class="ti ti-trash"></i> Hapus
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
