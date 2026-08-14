@extends('layouts.app')

@section('title','Tenaga Pendidikan')

@section('content')
<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
                <h4 class="card-title fw-semibold m-0">Daftar Tenaga Pendidikan</h4>
                <div class="d-flex gap-2 flex-wrap">
                    <a href="{{ route('tenaga_pendidikan.create') }}" class="btn btn-primary btn-sm">
                        <i class="ti ti-plus me-1"></i>Tambah Data
                    </a>
                    <a href="{{ route('tenaga_pendidikan.export') }}" class="btn btn-success btn-sm" title="Export ke Excel">
                        <i class="ti ti-download me-1"></i>Export
                    </a>
                    <a href="{{ route('tenaga_pendidikan.template', ['mode' => 'create']) }}" class="btn btn-info btn-sm" title="Download template import">
                        <i class="ti ti-template me-1"></i>Template
                    </a>
                    <button class="btn btn-warning btn-sm" data-bs-toggle="modal" data-bs-target="#importModal" title="Import dari Excel">
                        <i class="ti ti-upload me-1"></i>Import
                    </button>
                </div>
            </div>
            <div class="card-body">
                @if($items->count() > 0)
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Nama</th>
                            <th>NIP</th>
                            <th>Jabatan</th>
                            <th>Email</th>
                            <th>Akun User</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($items as $item)
                        <tr>
                            <td>{{ $loop->iteration + ($items->currentPage() - 1) * $items->perPage() }}</td>
                            <td>{{ $item->nama }}</td>
                            <td>{{ $item->nip ?? '-' }}</td>
                            <td>{{ $item->jabatan ?? '-' }}</td>
                            <td>{{ $item->email ?? '-' }}</td>
                            <td>
                                @if($item->user)
                                    <span class="badge bg-success">{{ $item->user->username }}</span>
                                @else
                                    <span class="badge bg-secondary">Belum</span>
                                @endif
                            </td>
                            <td>
                                <a href="{{ route('tenaga_pendidikan.show', $item) }}" class="btn btn-sm btn-info">Lihat</a>
                                <a href="{{ route('tenaga_pendidikan.edit', $item) }}" class="btn btn-sm btn-warning">Edit</a>
                                @if(!$item->user)
                                    <a href="{{ route('tenaga_pendidikan.generate-account', $item) }}" class="btn btn-sm btn-success" title="Buat Akun User">Buat Akun</a>
                                @endif
                                <form method="POST" action="{{ route('tenaga_pendidikan.destroy', $item) }}" style="display:inline;">
                                    @csrf
                                    @method('DELETE')
                                    <button class="btn btn-sm btn-danger" onclick="return confirm('Yakin hapus?')">Hapus</button>
                                </form>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
                {{ $items->links() }}
                @else
                <div class="alert alert-info">Belum ada data tenaga pendidikan.</div>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Import Modal -->
<div class="modal fade" id="importModal" tabindex="-1" role="dialog" aria-labelledby="importModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="importModalLabel">Import Data Tenaga Pendidikan</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="POST" action="{{ route('tenaga_pendidikan.import') }}" enctype="multipart/form-data">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Mode <span class="text-danger">*</span></label>
                        <select name="mode" class="form-select" required>
                            <option value="create">Buat Data Baru (Create)</option>
                            <option value="update">Update Data Existing (Update)</option>
                        </select>
                        <small class="form-hint">Pilih mode sesuai kebutuhan Anda</small>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">File Excel <span class="text-danger">*</span></label>
                        <input type="file" name="file" class="form-control" accept=".xlsx,.xls,.csv" required>
                        <small class="form-hint">Format: XLSX, XLS, atau CSV</small>
                    </div>
                    <div class="alert alert-info mb-0">
                        <i class="ti ti-info-circle me-2"></i>
                        <strong>Petunjuk:</strong>
                        <ul class="mb-0 mt-2">
                            <li>Download template terlebih dahulu</li>
                            <li>Isi data sesuai dengan kolom yang tersedia</li>
                            <li>Username dan password bisa dikosongkan (akan di-generate otomatis)</li>
                            <li>Jenis kelamin harus L atau P</li>
                        </ul>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="ti ti-upload me-1"></i>Import
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@if($errors->has('import_errors'))
<div class="modal fade show" id="errorModal" tabindex="-1" role="dialog" aria-labelledby="errorModalLabel" aria-hidden="true" style="display: block;">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="errorModalLabel">Error Import</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-danger mb-3">
                    <strong>Terdapat {{ count($errors->get('import_errors')) }} error(s) saat import:</strong>
                </div>
                <div class="table-responsive">
                    <table class="table table-sm table-bordered">
                        <thead>
                            <tr>
                                <th>Baris</th>
                                <th>Error</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($errors->get('import_errors') as $row => $message)
                            <tr>
                                <td>{{ $row }}</td>
                                <td>{{ $message }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const errorModal = new bootstrap.Modal(document.getElementById('errorModal'));
    errorModal.show();
});
</script>
@endif

@endsection
