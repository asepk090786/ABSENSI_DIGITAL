@extends('layouts.app', ['pageSlug' => 'mata_pelajaran'])

@section('title','Mata Pelajaran')

@php($isGuruView = $isGuruView ?? false)
@php($canManageMapel = auth()->check() ? !auth()->user()->hasAnyRole(['Guru', 'Guru Mapel', 'Guru Kelas', 'Wali Kelas']) : false)

@section('content')
<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header">
                <div class="row align-items-center">
                    <div class="col">
                        <h4 class="card-title mb-0">Data Mata Pelajaran</h4>
                    </div>
                    @unless($isGuruView)
                        @if($canManageMapel)
                        <div class="col-auto">
                            <div class="btn-list">
                                <button type="button" class="btn btn-success btn-sm" data-toggle="modal" data-target="#modalImport">
                                    <i class="ti ti-upload me-1"></i>Import Excel
                                </button>
                                <a href="{{ route('mata_pelajaran.export') }}" class="btn btn-info btn-sm">
                                    <i class="ti ti-download me-1"></i>Export Excel
                                </a>
                                <a href="{{ route('kegiatan.index') }}" class="btn btn-warning btn-sm">
                                    <i class="ti ti-activity me-1"></i>Kegiatan
                                </a>
                                <a href="{{ route('mata_pelajaran.create') }}" class="btn btn-primary btn-sm">
                                    <i class="ti ti-plus me-1"></i>Tambah Mapel
                                </a>
                            </div>
                        </div>
                        @endif
                    @endunless
                </div>
            </div>
            <div class="card-body">
                @if(session('success'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <i class="ti ti-check me-2"></i>{{ session('success') }}
                        <button type="button" class="close" data-dismiss="alert"><span aria-hidden="true">&times;</span></button>
                    </div>
                @endif

                @if(session('error'))
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <i class="ti ti-alert-circle me-2"></i>{{ session('error') }}
                        <button type="button" class="close" data-dismiss="alert"><span aria-hidden="true">&times;</span></button>
                    </div>
                @endif

                @if(session('warning'))
                    <div class="alert alert-warning alert-dismissible fade show" role="alert">
                        <i class="ti ti-alert-triangle me-2"></i>{{ session('warning') }}
                        <button type="button" class="close" data-dismiss="alert"><span aria-hidden="true">&times;</span></button>
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
                    <table class="table table-striped table-hover">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Nama Mapel</th>
                                <th>Kode</th>
                                <th>Tingkat</th>
                                <th>Kelas</th>
                                <th>Rencana Pembelajaran</th>
                                @unless($isGuruView)
                                    <th>Kategori</th>
                                @endunless
                                @if(!$isGuruView && $canManageMapel)
                                    <th>Aksi</th>
                                @endif
                            </tr>
                        </thead>
                        <tbody>
                        @forelse($items as $index => $it)
                            <tr>
                                <td>{{ $index + 1 }}</td>
                                <td>
                                    <a href="{{ route('mata_pelajaran.show', $it->id) }}" class="text-decoration-none">{{ $it->nama_mapel }}</a>
                                </td>
                                <td>{{ $it->kode_mapel ?? '-' }}</td>
                                <td>{{ $it->tingkat ?? '-' }}</td>
                                <td>{{ $it->kelas_names ?? '-' }}</td>
                                @if($isGuruView)
                                    <td>
                                        <a href="{{ route('rencana_pembelajaran.index', ['mata_pelajaran_id' => $it->id, 'tingkat' => $it->tingkat]) }}" class="btn btn-sm btn-outline-secondary" title="Rencana Pembelajaran">
                                            <i class="ti ti-book me-1"></i>Kelola
                                        </a>
                                    </td>
                                @endif
                                @unless($isGuruView)
                                    <td>{{ $it->kategori ?? '-' }}</td>
                                @endunless
                                @if(!$isGuruView && $canManageMapel)
                                    <td>
                                        <div class="btn-list">
                                            <a href="{{ route('mata_pelajaran.edit', $it->id) }}" class="btn btn-sm btn-outline-primary">
                                                <i class="ti ti-edit"></i>
                                            </a>
                                            <button type="button" class="btn btn-sm btn-outline-danger" onclick="confirmDelete({{ $it->id }})">
                                                <i class="ti ti-trash"></i>
                                            </button>
                                        </div>
                                    </td>
                                @endif
                            </tr>
                        @empty
                            <tr>
                                <td colspan="{{ $isGuruView ? 6 : 8 }}" class="text-center text-muted">
                                    <i class="ti ti-info-circle me-2"></i>Belum ada mata pelajaran.
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

@if(!$isGuruView && $canManageMapel)
    <div class="modal fade" id="modalImport" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Import Mata Pelajaran dari Excel</h5>
                    <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span></button>
                </div>
                <form action="{{ route('mata_pelajaran.import') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="modal-body">
                        <div class="alert alert-info">
                            <i class="ti ti-info-circle me-2"></i>
                            <strong>Petunjuk:</strong>
                            <ol class="mb-0 mt-2">
                                <li>Download template Excel terlebih dahulu</li>
                                <li>Isi data sesuai format template</li>
                                <li>Jika Kode Pelajaran sudah ada, nama akan diperbarui</li>
                                <li>Upload file yang sudah diisi</li>
                            </ol>
                        </div>

                        <div class="mb-3">
                            <a href="{{ route('mata_pelajaran.template') }}" class="btn btn-outline-primary btn-sm">
                                <i class="ti ti-download me-1"></i>Download Template Excel
                            </a>
                        </div>

                        <hr>

                        <div class="mb-3">
                            <label class="form-label">File Excel <span class="text-danger">*</span></label>
                            <input type="file" name="file" class="form-control" accept=".xlsx,.xls" required>
                            <small class="form-hint">Format: .xlsx atau .xls, maksimal 2MB</small>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
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
@endif
@endsection

@if(!$isGuruView && $canManageMapel)
    @push('js')
    <script>
    function confirmDelete(id) {
        if (confirm('Hapus mata pelajaran ini?')) {
            const form = document.getElementById('deleteForm');
            form.action = `/mata_pelajaran/${id}`;
            form.submit();
        }
    }
    </script>
    @endpush
@endif
