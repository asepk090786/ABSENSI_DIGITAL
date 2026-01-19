@extends('layouts.app', ['pageSlug' => 'rencana_pembelajaran'])

@section('title', 'Rencana Pembelajaran')

@section('content')
<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header">
                <div class="row align-items-center">
                    <div class="col">
                        <h4 class="card-title mb-0">
                            Rencana Pembelajaran - {{ $mataPelajaran->nama_mapel }} (Tingkat {{ $tingkat }})
                        </h4>
                    </div>
                    <div class="col-auto">
                        <div class="btn-list">
                            <a href="{{ route('rencana_pembelajaran.template') }}" class="btn btn-outline-info btn-sm" title="Download Template">
                                <i class="ti ti-file-word me-1"></i>Template
                            </a>
                            <a href="{{ route('rencana_pembelajaran.import_form', ['mata_pelajaran_id' => $mataPelajaran->id, 'tingkat' => $tingkat]) }}" class="btn btn-info btn-sm" title="Import dari Word">
                                <i class="ti ti-file-word me-1"></i>Import Word
                            </a>
                            <a href="{{ route('rencana_pembelajaran.create', ['mata_pelajaran_id' => $mataPelajaran->id, 'tingkat' => $tingkat]) }}" class="btn btn-primary btn-sm">
                                <i class="ti ti-plus me-1"></i>Tambah Rencana
                            </a>
                            <a href="{{ route('mata_pelajaran.guru') }}" class="btn btn-secondary btn-sm">
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
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif

                <div class="table-responsive">
                    <table class="table table-striped table-hover">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Judul</th>
                                <th>Kelas</th>
                                <th>Status</th>
                                <th>Tanggal</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                        @forelse($items as $index => $item)
                            <tr>
                                <td>{{ $index + 1 }}</td>
                                <td>
                                    <a href="{{ route('rencana_pembelajaran.show', $item->id) }}" class="text-decoration-none">
                                        {{ $item->judul }}
                                    </a>
                                </td>
                                <td>{{ $item->kelas->nama_kelas }}</td>
                                <td>
                                    <span class="badge bg-{{ $item->status === 'published' ? 'success' : 'warning' }}">
                                        {{ ucfirst($item->status) }}
                                    </span>
                                </td>
                                <td>
                                    @if($item->tanggal_mulai)
                                        {{ \Carbon\Carbon::parse($item->tanggal_mulai)->format('d/m/Y') }}
                                        @if($item->tanggal_selesai)
                                            - {{ \Carbon\Carbon::parse($item->tanggal_selesai)->format('d/m/Y') }}
                                        @endif
                                    @else
                                        -
                                    @endif
                                </td>
                                <td>
                                    <div class="btn-list">
                                        <a href="{{ route('rencana_pembelajaran.show', $item->id) }}" class="btn btn-sm btn-outline-info" title="Lihat">
                                            <i class="ti ti-eye"></i>
                                        </a>
                                        <a href="{{ route('rencana_pembelajaran.export_word', $item->id) }}" class="btn btn-sm btn-outline-success" title="Export Word">
                                            <i class="ti ti-download"></i>
                                        </a>
                                        <a href="{{ route('rencana_pembelajaran.edit', $item->id) }}" class="btn btn-sm btn-outline-primary" title="Edit">
                                            <i class="ti ti-edit"></i>
                                        </a>
                                        <button type="button" class="btn btn-sm btn-outline-danger" onclick="confirmDelete({{ $item->id }})" title="Hapus">
                                            <i class="ti ti-trash"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center text-muted">
                                    <i class="ti ti-info-circle me-2"></i>Belum ada rencana pembelajaran.
                                </td>
                            </tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<form id="deleteForm" method="POST" style="display:none;">
    @csrf
    @method('DELETE')
</form>
@endsection

@push('js')
<script>
function confirmDelete(id) {
    if (confirm('Hapus rencana pembelajaran ini?')) {
        const form = document.getElementById('deleteForm');
        form.action = `/rencana_pembelajaran/${id}`;
        form.submit();
    }
}
</script>
@endpush
