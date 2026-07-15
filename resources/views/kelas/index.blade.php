@extends('layouts.app', ['pageSlug' => 'kelas'])

@section('title','Kelas')

@section('content')
@php
    $isStudentWithoutClassPosition = auth()->user()->hasRole('Siswa') && ! auth()->user()->hasClassPosition();
@endphp
<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header border-0 pt-3 pb-2">
                <div class="row align-items-center">
                    <div class="col">
                        <h4 class="card-title fw-semibold m-0">Data Kelas</h4>
                    </div>
                    <div class="col-auto">
                        @unless($isStudentWithoutClassPosition)
                            <div >
                                <button type="button" class="btn btn-sm btn-success btn-modern" data-bs-toggle="modal" data-bs-target="#modalImport">
                                    <i class="ti ti-upload me-1"></i>Import Excel
                                </button>
                                <a href="{{ route('kelas.export') }}" class="btn btn-sm btn-info btn-modern">
                                    <i class="ti ti-download me-1"></i>Export Excel
                                </a>
                                <a href="{{ route('kelas.create') }}" class="btn btn-primary btn-sm">
                                    <i class="ti ti-plus me-1"></i>Tambah Kelas
                                </a>
                            </div>
                        @endunless
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

                @if(session('warning'))
                    <div class="alert alert-warning alert-dismissible fade show" role="alert">
                        <i class="ti ti-alert-triangle me-2"></i>{{ session('warning') }}
                        <button type="button" class="close" data-bs-dismiss="alert"><span aria-hidden="true">&times;</span></button>
                        @if(session('import_errors'))
                            <hr>
                            <strong>Detail Error:</strong>
                            <ul class="mb-0">
                                @foreach(session('import_errors') as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        @endif
                    </div>
                @endif

                <div class="table-responsive">
                    <table class="table table-vcenter table-hover table-tabler">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>ID</th>
                                <th>Nama Kelas</th>
                                <th>Wali Kelas</th>
                                <th>Jumlah Siswa</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                        @forelse($items as $index => $it)
                            <tr>
                                <td>{{ $index + 1 }}</td>
                                <td><code>{{ $it->id }}</code></td>
                                <td>{{ $it->nama_kelas }}</td>
                                <td>{{ $it->waliKelas->user->name ?? $it->waliKelas->nama ?? '-' }}</td>
                                <td><span class="badge bg-blue-lt">{{ $it->siswa_count }}</span></td>
                                <td>
                                    @unless($isStudentWithoutClassPosition)
                                        <div >
                                            <a href="{{ route('kelas.edit', $it->id) }}" class="btn btn-sm btn-outline-primary">
                                                <i class="ti ti-edit"></i>
                                            </a>
                                            <button type="button" class="btn btn-sm btn-outline-danger" onclick="confirmDelete({{ $it->id }})">
                                                <i class="ti ti-trash"></i>
                                            </button>
                                        </div>
                                    @endunless
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center text-muted">
                                    <i class="ti ti-info-circle me-2"></i>Belum ada data kelas.
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

<div class="modal fade" id="modalImport" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Import Data Kelas dari Excel</h5>
                <button type="button" class="close" data-bs-dismiss="modal"><span aria-hidden="true">&times;</span></button>
            </div>
            <form action="{{ route('kelas.import') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="modal-body">
                    <div class="alert alert-info">
                        <i class="ti ti-info-circle me-2"></i>
                        <strong>Petunjuk:</strong>
                        <ol class="mb-0 mt-2">
                            <li>Download template Excel terlebih dahulu</li>
                            <li>Isi data sesuai format template</li>
                            <li>Kolom ID bisa diisi untuk update data</li>
                            <li>Upload file yang sudah diisi</li>
                        </ol>
                    </div>

                    <div class="mb-2">
                        <a href="{{ route('kelas.template') }}" class="btn btn-outline-primary btn-sm">
                            <i class="ti ti-download me-1"></i>Download Template Excel
                        </a>
                    </div>

                    <hr>

                    <div class="mb-2">
                        <label class="form-label">File Excel <span class="text-danger">*</span></label>
                        <input type="file" name="file" class="form-control" accept=".xlsx,.xls" required>
                        <small class="form-hint">Format: .xlsx atau .xls, maksimal 2MB</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="ti ti-upload me-1"></i>Upload & Import
                    </button>
                </div>
            </form>
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
    if (confirm('Apakah Anda yakin ingin menghapus data kelas ini?')) {
        const form = document.getElementById('deleteForm');
        form.action = `/kelas/${id}`;
        form.submit();
    }
}
</script>
@endpush
