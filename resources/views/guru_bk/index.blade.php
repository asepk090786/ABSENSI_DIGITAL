@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h3 class="card-title">Data Guru BK</h3>
                    <a href="{{ route('guru_bk.create') }}" class="btn btn-primary">
                        <i class="ti ti-plus"></i> Tambah Guru BK
                    </a>
                </div>
                <div class="card-body">
                    @if(session('success'))
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            {{ session('success') }}
                            <button type="button" class="close" data-dismiss="alert"><span aria-hidden="true">&times;</span></button>
                        </div>
                    @endif

                    @if($gurubk->isEmpty())
                        <div class="alert alert-info">
                            <i class="ti ti-info-circle"></i> Belum ada data Guru BK.
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
                                        <th>Guru Terpilih</th>
                                        <th>Kelas Binaan</th>
                                        <th>Email</th>
                                        <th>Telepon</th>
                                        <th>Status</th>
                                        <th>Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($gurubk as $index => $item)
                                        <tr>
                                            <td>{{ $index + 1 }}</td>
                                            <td>
                                                @if($item->foto)
                                                    <img src="{{ asset('storage/' . $item->foto) }}" alt="Foto" class="rounded" style="width: 50px; height: 50px; object-fit: cover;">
                                                @else
                                                    <div class="bg-light rounded d-flex align-items-center justify-content-center" style="width: 50px; height: 50px;">
                                                        <i class="ti ti-user" style="font-size: 24px; color: #999;"></i>
                                                    </div>
                                                @endif
                                            </td>
                                            <td>{{ $item->nama }}</td>
                                            <td>{{ $item->nip ?? '-' }}</td>
                                            <td>
                                                @if($item->guru_id && $item->guru)
                                                    <span class="badge bg-info">
                                                        <i class="ti ti-link"></i> {{ $item->guru->nama }}
                                                    </span>
                                                @elseif($item->guru_id)
                                                    <span class="badge bg-warning">
                                                        <i class="ti ti-alert-triangle"></i> Guru (ID: {{ $item->guru_id }}) - Tidak Ditemukan
                                                    </span>
                                                @else
                                                    <span class="badge bg-light text-dark border border-secondary">
                                                        <i class="ti ti-minus"></i> Input Manual
                                                    </span>
                                                @endif
                                            </td>
                                            <td>
                                                @if(!($hasGuruBkKelasColumn ?? false))
                                                    <span class="text-muted">-</span>
                                                @elseif($item->kelasBinaanBk->isEmpty())
                                                    <span class="text-muted">Belum ada</span>
                                                @else
                                                    @foreach($item->kelasBinaanBk as $kelasBinaan)
                                                        <span class="badge bg-primary me-1 mb-1">{{ $kelasBinaan->nama_kelas }}</span>
                                                    @endforeach
                                                @endif
                                            </td>
                                            <td>{{ $item->email ?? '-' }}</td>
                                            <td>{{ $item->telepon ?? '-' }}</td>
                                            <td>
                                                @if($item->is_active)
                                                    <span class="badge bg-success">Aktif</span>
                                                @else
                                                    <span class="badge bg-secondary">Tidak Aktif</span>
                                                @endif
                                            </td>
                                            <td>
                                                <div class="btn-group" role="group">
                                                    <a href="{{ route('guru_bk.show', $item->id) }}" class="btn btn-sm btn-info">
                                                        <i class="ti ti-eye"></i>
                                                    </a>
                                                    <a href="{{ route('guru_bk.edit', $item->id) }}" class="btn btn-sm btn-warning">
                                                        <i class="ti ti-edit"></i>
                                                    </a>
                                                    <form action="{{ route('guru_bk.destroy', $item->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Yakin ingin menghapus data ini?')">
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
