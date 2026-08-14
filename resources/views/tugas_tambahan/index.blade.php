@extends('layouts.app')

@section('title','Tugas Tambahan')

@section('content')
<div class="container-fluid">
    <div class="page-header d-print-none">
        <div class="row align-items-center justify-content-between">
            <div class="col">
                <h2 class="page-title">
                    <i class="ti ti-briefcase me-2"></i>Tugas Tambahan
                </h2>
            </div>
            <div class="col-auto">
                <a href="{{ route('tugas_tambahan.create') }}" class="btn btn-primary">
                    <i class="ti ti-plus me-1"></i> Tambah Data
                </a>
            </div>
        </div>
    </div>
</div>

<div class="page-wrapper">
    <div class="container-xl">
        @if ($message = Session::get('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="ti ti-check me-2"></i>{{ $message }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="table-responsive">
                        <table class="table card-table table-vcenter table-hover">
                            <thead>
                                <tr>
                                    <th style="width: 50px;">No</th>
                                    <th>Nama Tenaga Pendidikan</th>
                                    <th>NIP</th>
                                    <th>Tugas</th>
                                    <th>Keterangan</th>
                                    <th style="width: 150px;">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($items as $index => $item)
                                    <tr>
                                        <td>{{ ($items->currentPage() - 1) * $items->perPage() + $loop->iteration }}</td>
                                        <td>
                                            <strong>{{ $item->tenagaPendidikan->nama ?? 'N/A' }}</strong>
                                        </td>
                                        <td>{{ $item->tenagaPendidikan->nip ?? '-' }}</td>
                                        <td>{{ $item->tugas }}</td>
                                        <td>
                                            @if ($item->keterangan)
                                                <span class="badge bg-secondary">{{ Str::limit($item->keterangan, 50) }}</span>
                                            @else
                                                <span class="text-muted">-</span>
                                            @endif
                                        </td>
                                        <td>
                                            <div class="btn-group" role="group">
                                                <a href="{{ route('tugas_tambahan.edit', $item->id) }}" class="btn btn-sm btn-icon btn-warning" title="Edit">
                                                    <i class="ti ti-edit"></i>
                                                </a>
                                                <form action="{{ route('tugas_tambahan.destroy', $item->id) }}" method="POST" style="display:inline;">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-sm btn-icon btn-danger" title="Delete" onclick="return confirm('Yakin ingin menghapus?')">
                                                        <i class="ti ti-trash"></i>
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-center text-muted py-4">
                                            <i class="ti ti-inbox me-2"></i>Tidak ada data tugas tambahan
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                @if ($items->hasPages())
                    <div class="d-flex justify-content-center mt-4">
                        {{ $items->links() }}
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
