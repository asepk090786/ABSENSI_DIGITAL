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
                        <p class="text-muted mt-1">Pilih rencana pembelajaran untuk mengelola materi</p>
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

                @if($rencanaPembelajarans->isEmpty())
                    <div class="alert alert-info">
                        <i class="ti ti-info-circle me-2"></i>Belum ada rencana pembelajaran. Silakan buat rencana pembelajaran terlebih dahulu di menu Rencana Pembelajaran.
                    </div>
                @else
                    <div class="table-responsive">
                        <table class="table table-vcenter table-hover table-tabler">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>Mata Pelajaran</th>
                                    <th>Kelas</th>
                                    <th>Judul Rencana</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                            @forelse($rencanaPembelajarans as $index => $rencana)
                                <tr>
                                    <td>{{ $index + 1 }}</td>
                                    <td>{{ $rencana->mataPelajaran->nama_mapel ?? '-' }}</td>
                                    <td>{{ $rencana->kelas->nama_kelas ?? '-' }}</td>
                                    <td>{{ $rencana->judul }}</td>
                                    <td>
                                        <a href="{{ route('materi_pembelajaran.index', ['rencana_pembelajaran_id' => $rencana->id]) }}" class="btn btn-sm btn-outline-primary" title="Kelola Materi">
                                            <i class="ti ti-book-2"></i>
                                        </a>
                                        <a href="{{ route('rencana_pembelajaran.edit', $rencana->id) }}" class="btn btn-sm btn-outline-info" title="Edit Rencana">
                                            <i class="ti ti-edit"></i>
                                        </a>
                                        <form action="{{ route('rencana_pembelajaran.destroy', $rencana->id) }}" method="POST" style="display:inline" onsubmit="return confirm('Hapus rencana pembelajaran ini? Semua materi di dalamnya juga akan terhapus.')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-outline-danger" title="Hapus">
                                                <i class="ti ti-trash"></i>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center text-muted">
                                        <i class="ti ti-info-circle me-2"></i>Belum ada rencana pembelajaran.
                                    </td>
                                </tr>
                            @endforelse
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
