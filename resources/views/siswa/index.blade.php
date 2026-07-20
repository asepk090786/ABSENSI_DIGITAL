@extends('layouts.app', ['pageSlug' => 'siswa'])

@section('title','Siswa')

@section('content')
<div class="page-header d-print-none">
    <div class="container-fluid">
        <div class="row g-2 align-items-center">
            <div class="col">
                <h2 class="page-title">
                    <i class="ti ti-users me-2"></i>Siswa
                </h2>
                <div class="text-muted mt-1">Kelola data siswa sekolah</div>
            </div>
            <div class="col-auto ms-auto d-print-none">
                <div >
                    <button type="button" id="deleteSelectedBtn" class="btn btn-outline-danger d-none d-sm-inline-block me-2" style="display:none;">
                        <i class="ti ti-trash me-1"></i>Hapus Terpilih (<span id="selectedCount">0</span>)
                    </button>
                    <button type="button" class="btn btn-outline-primary d-none d-sm-inline-block" data-bs-toggle="modal" data-bs-target="#modalImport">
                        <i class="ti ti-file-import me-1"></i>Import Excel
                    </button>
                    <a href="{{ route('siswa.export') }}" class="btn btn-outline-primary d-none d-sm-inline-block">
                        <i class="ti ti-download me-1"></i>Export Excel
                    </a>
                    <a href="{{ route('siswa.create') }}" class="btn btn-primary d-none d-sm-inline-block">
                        <i class="ti ti-plus me-1"></i>Tambah Siswa
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="page-body">
    <div class="container-fluid">
        @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="ti ti-check me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        @endif
        @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="ti ti-alert-circle me-2"></i>{{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        @endif
        @if(session('warning'))
        <div class="alert alert-warning alert-dismissible fade show" role="alert">
            <i class="ti ti-alert-triangle me-2"></i>{{ session('warning') }}
            @if(session('import_errors'))
                <ul class="mb-0 mt-2">
                    @foreach(session('import_errors') as $err)
                        <li>{{ $err }}</li>
                    @endforeach
                </ul>
            @endif
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        @endif

        <div class="card mb-2">
            <div class="card-body">
                <div class="input-group">
                    <span class="input-group-text"><i class="ti ti-search"></i></span>
                    <input type="search" class="form-control" id="searchInput" placeholder="Cari nama, NIS, NISN, kelas...">
                </div>
            </div>
        </div>

        <div class="card">
            <div class="table-responsive">
                <table class="table table-vcenter table-hover" id="dataTable">
                    <thead>
                        <tr>
                            <th style="width:40px"><input type="checkbox" id="selectAllCheckbox" class="form-check-input"></th>
                            <th style="width:40px">#</th>
                            <th>Nama</th>
                            <th>NIS</th>
                            <th>NISN</th>
                            <th>Kelas</th>
                            <th>Jabatan</th>
                            <th>Status Akun</th>
                            <th class="w-1"></th>
                        </tr>
                    </thead>
                    <tbody>
                        @php $no = 1; @endphp
                        @forelse($items as $item)
                            <tr>
                                <td><input type="checkbox" class="form-check-input siswa-checkbox" data-siswa-id="{{ $item->id }}"></td>
                                <td>{{ $no++ }}</td>
                                <td>
                                    <div class="d-flex align-items-center gap-2">
                                        <span class="avatar avatar-sm" style="background-color: #206bc4; color: #fff; border-radius: 50%; width: 28px; height: 28px; display: inline-flex; align-items: center; justify-content: center; font-size: 12px; font-weight: 600;">{{ mb_substr($item->nama, 0, 1) }}</span>
                                        <div>
                                            <div class="fw-medium">{{ $item->nama }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td><code class="text-primary">{{ $item->nis }}</code></td>
                                <td>{{ $item->nisn ?: '-' }}</td>
                                <td>{{ $item->kelas->nama_kelas ?? '-' }}</td>
                                <td>
                                    @if($item->jabatan_kelas === 'ketua')
                                        <span class="badge bg-primary me-1">Ketua</span>
                                    @elseif($item->jabatan_kelas === 'wakil')
                                            <span class="badge bg-info me-1">Wakil Ketua Kelas</span>
                                    @elseif($item->jabatan_kelas === 'sekretaris')
                                        <span class="badge bg-warning me-1">Sekretaris</span>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td>
                                    @if($item->user)
                                        @if($item->user->is_active)
                                            <span class="badge bg-success">Aktif</span>
                                        @else
                                            <span class="badge bg-secondary">Nonaktif</span>
                                        @endif
                                    @else
                                        <span class="badge bg-warning">Tanpa Akun</span>
                                    @endif
                                </td>
                                <td class="text-end">
                                    <div >
                                        <a href="{{ route('siswa.edit', $item->id) }}" class="btn btn-outline-primary btn-sm">
                                            <i class="ti ti-edit"></i>
                                        </a>
                                        <button class="btn btn-outline-danger btn-sm" onclick="confirmDelete('{{ $item->id }}')">
                                            <i class="ti ti-trash"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center text-muted py-6">
                                    <i class="ti ti-inbox d-block mb-2" style="font-size: 2.5rem; opacity: 0.4;"></i>
                                    Belum ada data siswa.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if(isset($items) && method_exists($items, 'hasPages') && $items->hasPages())
            <div class="card-footer d-flex align-items-center justify-content-between">
                <div class="text-muted small">Menampilkan {{ $items->firstItem() }} - {{ $items->lastItem() }} dari {{ $items->total() }} data</div>
                <div>{{ $items->links('pagination::tabler') }}</div>
            </div>
            @endif
        </div>
    </div>
</div>

<div class="modal fade" id="modalImport" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Import Data Siswa dari Excel</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('siswa.import') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="modal-body">
                    <div class="alert alert-info">
                        <i class="ti ti-info-circle me-2"></i>Petunjuk: Download template, isi data sesuai format, dan upload.
                    </div>
                    <div class="mb-2">
                        <a href="{{ route('siswa.template') }}" class="btn btn-outline-primary btn-sm">
                            <i class="ti ti-download me-1"></i>Download Template Excel
                        </a>
                    </div>
                    <hr>
                    <div class="mb-2">
                        <label class="form-label">File Excel <span class="text-danger">*</span></label>
                        <input type="file" name="file" class="form-control" accept=".xlsx,.xls" required>
                        <small class="form-hint">Format .xlsx atau .xls, maksimal 2MB</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Upload & Import</button>
                </div>
            </form>
        </div>
    </div>
</div>

<form id="deleteForm" method="POST" style="display:none;">@csrf @method('DELETE')</form>
@endsection

@push('js')
<script>
function confirmDelete(id) {
    if (confirm('Apakah Anda yakin ingin menghapus data siswa ini? Akun terkait juga akan dihapus.')) {
        const f = document.getElementById('deleteForm');
        f.action = '/siswa/' + id;
        f.submit();
    }
}
document.addEventListener('DOMContentLoaded', function() {
    const s = document.getElementById('searchInput');
    if (!s) return;
    s.addEventListener('keyup', function() {
        const q = this.value.toLowerCase();
        document.querySelectorAll('#dataTable tbody tr').forEach(function(row) {
            if (row.cells.length <= 1) return;
            row.style.display = row.textContent.toLowerCase().includes(q) ? '' : 'none';
        });
    });
    const selAll = document.getElementById('selectAllCheckbox');
    const boxes = document.querySelectorAll('.siswa-checkbox');
    const delBtn = document.getElementById('deleteSelectedBtn');
    const cntSpan = document.getElementById('selectedCount');
    function updateCount() {
        const n = document.querySelectorAll('.siswa-checkbox:checked').length;
        if (cntSpan) cntSpan.textContent = n;
        if (delBtn) delBtn.style.display = n > 0 ? 'inline-block' : 'none';
    }
    if (selAll) selAll.addEventListener('change', function() {
        boxes.forEach(c => c.checked = this.checked);
        updateCount();
    });
    boxes.forEach(c => c.addEventListener('change', function() {
        const all = Array.from(boxes).every(b => b.checked);
        const some = Array.from(boxes).some(b => b.checked);
        selAll.checked = all;
        selAll.indeterminate = some && !all;
        updateCount();
    }));
    if (delBtn) delBtn.addEventListener('click', function() {
        const ids = Array.from(document.querySelectorAll('.siswa-checkbox:checked')).map(c => c.getAttribute('data-siswa-id'));
        if (!ids.length) { alert('Pilih minimal satu siswa untuk dihapus!'); return; }
        if (!confirm('Apakah Anda yakin ingin menghapus ' + ids.length + ' data siswa ini? Akun terkait juga akan dihapus.')) return;
        const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
        fetch('/siswa/bulk-delete', {
            method: 'POST', headers: {'Content-Type':'application/json','X-CSRF-TOKEN':token,'X-Requested-With':'XMLHttpRequest'},
            body: JSON.stringify({siswa_ids: ids})
        }).then(async r => { const d = await r.json(); if (!r.ok) throw new Error(d?.message || 'Gagal'); if (d.success) { alert(d.message); location.reload(); } else { alert('Error: ' + d.message); } })
        .catch(e => { console.error(e); alert('Terjadi kesalahan saat menghapus data siswa.'); });
    });
});
</script>
@endpush
