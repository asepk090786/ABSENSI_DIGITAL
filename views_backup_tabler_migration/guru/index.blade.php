@extends('layouts.app')

@section('title','Data Guru')

@section('page-header')
<div class="page-header">
    <div class="page-pretitle">Master Data</div>
    <h2 class="page-title">Data Guru</h2>
</div>
@endsection

@section('content')
<div class="card">
    <div class="card-header border-0 pt-3 pb-2 d-flex align-items-center justify-content-between flex-wrap gap-2">
        <div class="d-flex align-items-center gap-3">
            <div class="input-icon">
                <span class="input-icon-addon"><i class="ti ti-search"></i></span>
                <input type="search" class="form-control form-control-sm" id="searchInput" placeholder="Cari nama, kode, NIP, email, telepon...">
            </div>
        </div>
        <div class="btn-list">
            <button type="button" class="btn btn-sm btn-success btn-modern" data-bs-toggle="modal" data-bs-target="#modalImport">
                <i class="ti ti-upload me-1"></i>Import Excel
            </button>
            <a href="{{ route('guru.export') }}" class="btn btn-sm btn-info btn-modern">
                <i class="ti ti-download me-1"></i>Export Excel
            </a>
            <a href="{{ route('guru.create') }}" class="btn btn-sm btn-primary btn-modern">
                <i class="ti ti-plus me-1"></i>Tambah Guru
            </a>
        </div>
    </div>

    @if(session('generated_credentials'))
    <div class="px-3 pt-2">
        @php $cred = session('generated_credentials'); @endphp
        <div class="alert alert-info alert-dismissible fade show" role="alert">
            <i class="ti ti-key me-2"></i>Akun untuk <strong>{{ $cred['nama'] ?? '-' }}</strong> berhasil dibuat. Username: <strong>{{ $cred['username'] ?? '-' }}</strong>, Password: <strong>{{ $cred['password'] ?? '-' }}</strong>, Email: <strong>{{ $cred['email'] ?? '-' }}</strong>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    </div>
    @endif

    <div class="table-responsive table-tabler">
        <table class="table card-table table-vcenter table-hover" id="dataTable">
            <thead>
                <tr>
                    <th style="width:36px;"><input type="checkbox" class="form-check-input" id="selectAllCheckbox"></th>
                    <th style="width:50px;">No</th>
                    <th>Nama Lengkap</th>
                    <th>Kode Guru</th>
                    <th>NIP</th>
                    <th>Email</th>
                    <th>Telepon</th>
                    <th>Jenis Kelamin</th>
                    <th>Status Akun</th>
                    <th style="width:180px;" class="text-end">Aksi</th>
                </tr>
            </thead>
            <tbody>
            @forelse($items as $idx => $it)
                <tr>
                    <td><input type="checkbox" class="form-check-input guru-checkbox" data-guru-id="{{ $it->id }}"></td>
                    <td>{{ $idx + 1 }}</td>
                    <td>
                        <div class="d-flex align-items-center gap-2">
                            <span class="avatar avatar-sm" style="background:var(--tblr-success-light);color:var(--tblr-success);border-radius:50%;width:2.2rem;height:2.2rem;display:inline-flex;align-items:center;justify-content:center;font-weight:600;font-size:.8rem;">
                                {{ mb_substr($it->nama, 0, 1) }}
                            </span>
                            <span class="fw-medium">{{ $it->nama }}</span>
                        </div>
                    </td>
                    <td><code>{{ $it->kode_guru ?? '-' }}</code></td>
                    <td>{{ $it->nip ?? '-' }}</td>
                    <td>{{ $it->email ?? '-' }}</td>
                    <td>{{ $it->telepon ?? '-' }}</td>
                    <td>
                        @if($it->jenis_kelamin == 'L')
                            <span class="badge bg-blue-lt text-blue">Laki-laki</span>
                        @elseif($it->jenis_kelamin == 'P')
                            <span class="badge bg-pink-lt text-pink">Perempuan</span>
                        @else
                            <span class="text-muted">-</span>
                        @endif
                    </td>
                    <td>
                        @if($it->user)
                            @if($it->user->is_active)
                                <span class="badge bg-success">Aktif</span>
                            @else
                                <span class="badge bg-secondary">Non-Aktif</span>
                            @endif
                        @else
                            <span class="badge bg-warning">Belum Ada Akun</span>
                        @endif
                    </td>
                    <td class="text-end">
                        <div class="btn-list justify-content-end">
                            @if(!$it->user)
                                <form action="{{ route('guru.generate-account', $it->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Generate akun otomatis untuk guru ini?')">
                                    @csrf
                                    <button type="submit" class="btn btn-sm btn-outline-success btn-modern" title="Generate Akun">
                                        <i class="ti ti-key"></i>
                                    </button>
                                </form>
                            @endif
                            <a href="{{ route('guru.edit', $it->id) }}" class="btn btn-sm btn-outline-primary btn-modern">
                                <i class="ti ti-edit me-1"></i>Edit
                            </a>
                            <button type="button" class="btn btn-sm btn-outline-danger btn-modern" onclick="confirmDelete('{{ $it->id }}')">
                                <i class="ti ti-trash"></i>
                            </button>
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="10" class="text-center py-8 text-muted">
                        <i class="ti ti-inbox ti-3x d-block mb-2 opacity-50"></i>Belum ada data guru.
                    </td>
                </tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>

<!-- Import Modal -->
<div class="modal fade" id="modalImport" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title fw-semibold"><i class="ti ti-upload me-2"></i>Import Data Guru dari Excel</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('guru.import') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="modal-body">
                    <div class="alert alert-info">
                        <i class="ti ti-info-circle me-2"></i><strong>Petunjuk:</strong>
                        <ol class="mb-0 mt-2 ps-3">
                            <li>Download template Excel terlebih dahulu</li>
                            <li>Isi data sesuai format template</li>
                            <li>Upload file yang sudah diisi</li>
                        </ol>
                    </div>
                    <div class="mb-3">
                        <a href="{{ route('guru.template') }}" class="btn btn-outline-primary btn-sm btn-modern">
                            <i class="ti ti-download me-1"></i>Download Template Excel
                        </a>
                    </div>
                    <hr>
                    <div class="mb-3">
                        <label class="form-label fw-medium">File Excel <span class="text-danger">*</span></label>
                        <input type="file" name="file" class="form-control" accept=".xlsx,.xls" required>
                        <small class="form-hint">Format: .xlsx atau .xls, maksimal 2MB</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary btn-modern" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary btn-modern">
                        <i class="ti ti-upload me-1"></i>Upload & Import
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<form id="deleteForm" method="POST" class="d-none">@csrf @method('DELETE')</form>
@endsection

@push('js')
<script>
function confirmDelete(id) {
    if (confirm('Apakah Anda yakin ingin menghapus data guru ini? Akun terkait juga akan dihapus.')) {
        const f = document.getElementById('deleteForm');
        f.action = '/guru/' + id;
        f.submit();
    }
}
document.addEventListener('DOMContentLoaded', function() {
    const selAll = document.getElementById('selectAllCheckbox');
    const boxes = document.querySelectorAll('.guru-checkbox');
    const delBtn = document.getElementById('deleteSelectedBtn');
    const cntSpan = document.getElementById('selectedCount');
    const searchInput = document.getElementById('searchInput');
    function updateCount() {
        const n = document.querySelectorAll('.guru-checkbox:checked').length;
        cntSpan.textContent = n;
        delBtn.style.display = n > 0 ? 'block' : 'none';
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
    if (searchInput) searchInput.addEventListener('keyup', function() {
        const q = this.value.toLowerCase();
        document.querySelectorAll('#dataTable tbody tr').forEach(function(r) {
            if (r.cells.length <= 1) return;
            r.style.display = r.textContent.toLowerCase().includes(q) ? '' : 'none';
        });
        if (q !== '') { selAll.checked = false; selAll.indeterminate = false; }
    });
    if (delBtn) delBtn.addEventListener('click', function() {
        const ids = Array.from(document.querySelectorAll('.guru-checkbox:checked')).map(c => c.getAttribute('data-guru-id'));
        if (!ids.length) { alert('Pilih minimal satu guru untuk dihapus!'); return; }
        if (!confirm('Apakah Anda yakin ingin menghapus ' + ids.length + ' data guru ini? Akun terkait juga akan dihapus.')) return;
        const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
        fetch('/guru/bulk-delete', {
            method: 'POST', headers: {'Content-Type':'application/json','X-CSRF-TOKEN':token,'X-Requested-With':'XMLHttpRequest'},
            body: JSON.stringify({guru_ids: ids})
        }).then(async r => { const d = await r.json(); if (!r.ok) throw new Error(d?.message || 'Gagal'); if (d.success) { alert(d.message); location.reload(); } else { alert('Error: ' + d.message); } })
        .catch(e => { console.error(e); alert('Terjadi kesalahan saat menghapus data guru.'); });
    });
});
</script>
@endpush