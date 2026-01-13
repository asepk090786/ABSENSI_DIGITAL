@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h3 class="card-title">Data Kepala Sekolah</h3>
                    <a href="{{ route('kepala_sekolah.create') }}" class="btn btn-primary">
                        <i class="ti ti-plus"></i> Tambah Kepala Sekolah
                    </a>
                </div>
                <div class="card-body">
                    @if(session('success'))
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            {{ session('success') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    @if($kepalaSekolah->isEmpty())
                        <div class="alert alert-info">
                            <i class="ti ti-info-circle"></i> Belum ada data kepala sekolah.
                        </div>
                    @else
                        <div class="table-responsive">
                            <table class="table table-striped table-hover">
                                <thead>
                                    <tr>
                                        <th>No</th>
                                        <th>Foto</th>
                                        <th>Nama</th>
                                        <th>NIP</th>
                                        <th>Pangkat/Golongan</th>
                                        <th>Periode Jabatan</th>
                                        <th>Status</th>
                                        <th>Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($kepalaSekolah as $index => $kepsek)
                                        <tr>
                                            <td>{{ $index + 1 }}</td>
                                            <td>
                                                @if($kepsek->foto)
                                                    <img src="{{ asset('storage/' . $kepsek->foto) }}" alt="Foto" class="rounded" style="width: 50px; height: 50px; object-fit: cover;">
                                                @else
                                                    <div class="bg-light rounded d-flex align-items-center justify-content-center" style="width: 50px; height: 50px;">
                                                        <i class="ti ti-user" style="font-size: 24px; color: #999;"></i>
                                                    </div>
                                                @endif
                                            </td>
                                            <td>{{ $kepsek->nama }}</td>
                                            <td>{{ $kepsek->nip ?? '-' }}</td>
                                            <td>{{ $kepsek->pangkat_golongan ?? '-' }}</td>
                                            <td>
                                                {{ $kepsek->tanggal_mulai_jabatan->format('d/m/Y') }}
                                                @if($kepsek->tanggal_selesai_jabatan)
                                                    - {{ $kepsek->tanggal_selesai_jabatan->format('d/m/Y') }}
                                                @else
                                                    - Sekarang
                                                @endif
                                            </td>
                                            <td>
                                                @if($kepsek->status == 'Aktif')
                                                    <span class="badge bg-success">Aktif</span>
                                                @else
                                                    <span class="badge bg-secondary">Tidak Aktif</span>
                                                @endif
                                            </td>
                                            <td>
                                                <div class="btn-group" role="group">
                                                    <a href="{{ route('kepala_sekolah.show', $kepsek->id) }}" class="btn btn-sm btn-info">
                                                        <i class="ti ti-eye"></i>
                                                    </a>
                                                    <a href="{{ route('kepala_sekolah.edit', $kepsek->id) }}" class="btn btn-sm btn-warning">
                                                        <i class="ti ti-edit"></i>
                                                    </a>
                                                    <form action="{{ route('kepala_sekolah.destroy', $kepsek->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Yakin ingin menghapus data ini?')">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="btn btn-sm btn-danger">
                                                            <i class="ti ti-trash"></i>
                                                        </button>
                                                    </form>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
