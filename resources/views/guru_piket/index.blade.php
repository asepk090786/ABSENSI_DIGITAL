@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h3 class="card-title">Data Guru Piket</h3>
                    <a href="{{ route('guru_piket.create') }}" class="btn btn-primary">
                        <i class="ti ti-plus"></i> Tambah Guru Piket
                    </a>
                </div>
                <div class="card-body">
                    @if(session('success'))
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            {{ session('success') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    @if(! $hasAny)
                        <div class="alert alert-info">
                            <i class="ti ti-info-circle"></i> Belum ada data Guru Piket.
                        </div>
                    @else
                        @foreach($workDays as $hari)
                            <div class="mb-4">
                                <h5 class="mb-3">{{ $hari }}</h5>
                                @php
                                    $items = $guruByHari[$hari] ?? collect();
                                @endphp
                                @if($items->isEmpty())
                                    <div class="alert alert-light border">
                                        Tidak ada guru piket untuk hari {{ $hari }}.
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
                                                    <th>Email</th>
                                                    <th>Telepon</th>
                                                    <th>Status</th>
                                                    <th>Aksi</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($items as $index => $item)
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
                                                                <a href="{{ route('guru_piket.show', $item->id) }}" class="btn btn-sm btn-info action-btn">
                                                                    <i class="ti ti-eye"></i>
                                                                </a>
                                                                <a href="{{ route('guru_piket.edit', $item->id) }}" class="btn btn-sm btn-warning action-btn">
                                                                    <i class="ti ti-edit"></i>
                                                                </a>
                                                                <form action="{{ route('guru_piket.destroy', $item->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Yakin ingin menghapus data ini?')">
                                                                    @csrf
                                                                    @method('DELETE')
                                                                    <button type="submit" class="btn btn-sm btn-danger action-btn">
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
                        @endforeach
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
<style>
.action-btn {
    width: 34px;
    height: 34px;
    padding: 0;
    display: inline-flex;
    align-items: center;
    justify-content: center;
}
</style>
@endsection
