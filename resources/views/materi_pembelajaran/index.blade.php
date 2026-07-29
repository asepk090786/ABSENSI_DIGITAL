@extends('layouts.app', ['pageSlug' => 'materi_pembelajaran'])

@section('title', 'Materi Pembelajaran')

@section('content')
<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header border-0 pt-3 pb-2">
                <div class="row align-items-center">
                    <div class="col">
                        <h4 class="card-title fw-semibold m-0">Materi Pembelajaran</h4>
                        <p class="text-muted mt-1">{{ $rencanaPembelajaran->judul }}</p>
                    </div>
                    <div class="col-auto">
                        <div>
                            <a href="{{ route('materi_pembelajaran.create', ['rencana_pembelajaran_id' => $rencanaPembelajaran->id]) }}" class="btn btn-primary btn-sm">
                                <i class="ti ti-plus me-1"></i>Tambah Materi
                            </a>
                            <a href="{{ route('materi_pembelajaran.index') }}" class="btn btn-secondary btn-sm">
                                <i class="ti ti-arrow-left me-1"></i>Kembali
                            </a>
                        </div>
                    </div>
                </div>
            </div>
            <div class="card-body">
                @if(session('success'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <i class="ti ti-check me-2"></i>{{ session('success') }}
                        <button type="button" class="close" data-bs-dismiss="alert"><span aria-hidden="true">&times;</span></button>
                    </div>
                @endif

                @if(session('error'))
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <i class="ti ti-alert-circle me-2"></i>{{ session('error') }}
                        <button type="button" class="close" data-bs-dismiss="alert"><span aria-hidden="true">&times;</span></button>
                    </div>
                @endif

                @if($items->isEmpty())
                    <div class="alert alert-info">
                        <i class="ti ti-info-circle me-2"></i>Belum ada materi pembelajaran. Klik tombol "Tambah Materi" untuk membuat materi pembelajaran baru.
                    </div>
                @else
                    <div class="table-responsive">
                        <table class="table table-vcenter table-hover table-tabler">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>Nama Kegiatan</th>
                                    <th>Status</th>
                                    <th>Tanggal</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                            @forelse($items as $index => $item)
                                <tr>
                                    <td>{{ ($items->currentPage() - 1) * $items->perPage() + $index + 1 }}</td>
                                    <td>
                                        <a href="{{ route('materi_pembelajaran.show', $item->id) }}" class="text-decoration-none">
                                            {{ $item->nama_kegiatan }}
                                        </a>
                                    </td>
                                    <td>
                                        <span class="badge bg-{{ $item->status === 'published' ? 'success' : 'warning' }}">
                                            {{ ucfirst($item->status) }}
                                        </span>
                                    </td>
                                    <td>
                                        {{ $item->created_at->format('d/m/Y H:i') }}
                                    </td>
                                    <td>
                                        <div>
                                            <a href="{{ route('materi_pembelajaran.show', $item->id) }}" class="btn btn-sm btn-outline-info" title="Lihat">
                                                <i class="ti ti-eye"></i>
                                            </a>
                                            <a href="{{ route('materi_pembelajaran.edit', $item->id) }}" class="btn btn-sm btn-outline-primary" title="Edit">
                                                <i class="ti ti-edit"></i>
                                            </a>
                                            <form action="{{ route('materi_pembelajaran.destroy', $item->id) }}" method="POST" style="display:inline">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-outline-danger" title="Hapus" onclick="return confirm('Hapus materi pembelajaran ini?')">
                                                    <i class="ti ti-trash"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center text-muted">
                                        <i class="ti ti-info-circle me-2"></i>Belum ada materi pembelajaran.
                                    </td>
                                </tr>
                            @endforelse
                            </tbody>
                        </table>
                    </div>

                    @if($items->hasPages())
                        <div class="d-flex justify-content-center mt-3">
                            {{ $items->links() }}
                        </div>
                    @endif
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
