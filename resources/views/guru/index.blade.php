@extends('layouts.app', ['pageSlug' => 'guru'])

@section('title','Guru')

@section('content')
<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header">
                <div class="row align-items-center">
                    <div class="col">
                        <h4 class="card-title mb-0">Data Guru</h4>
                    </div>
                    <div class="col-auto">
                        <div class="btn-list">
                            <!-- Import Button -->
                            <button type="button" class="btn btn-success btn-sm" data-bs-toggle="modal" data-bs-target="#modalImport">
                                <i class="ti ti-upload me-1"></i>Import Excel
                            </button>
                            
                            <!-- Export Button -->
                            <a href="{{ route('guru.export') }}" class="btn btn-info btn-sm">
                                <i class="ti ti-download me-1"></i>Export Excel
                            </a>
                            
                            <!-- Tambah Button -->
                            <a href="{{ route('guru.create') }}" class="btn btn-primary btn-sm">
                                <i class="ti ti-plus me-1"></i>Tambah Guru
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
                
                @if(session('error'))
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <i class="ti ti-alert-circle me-2"></i>{{ session('error') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif
                
                @if(session('warning'))
                    <div class="alert alert-warning alert-dismissible fade show" role="alert">
                        <i class="ti ti-alert-triangle me-2"></i>{{ session('warning') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
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

                <!-- Search Input -->
                <div class="mb-3">
                    <input type="text" id="searchInput" class="form-control" placeholder="Cari berdasarkan nama, kode guru, NIP, email, atau telepon...">
                </div>

                <!-- Bulk Action Controls -->
                <div class="mb-3">
                    <div class="d-flex gap-2 align-items-center">
                        <select id="bulkActionSelect" class="form-select" style="width: 200px;">
                            <option value="">-- Pilih Aksi --</option>
                            <option value="select-all">Pilih Semua</option>
                            <option value="select-none">Batal Pilih Semua</option>
                            <option value="delete">Hapus Terpilih</option>
                        </select>
                        <button type="button" class="btn btn-sm btn-outline-danger" id="deleteSelectedBtn" style="display: none;">
                            <i class="ti ti-trash me-1"></i>Hapus Terpilih (<span id="selectedCount">0</span>)
                        </button>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table table-striped table-hover">
                        <thead>
                            <tr>
                                <th style="width: 40px;"><input type="checkbox" id="selectAllCheckbox" class="form-check-input"></th>
                                <th>No</th>
                                <th>Nama</th>
                                <th>Kode Guru</th>
                                <th>NIP</th>
                                <th>Email</th>
                                <th>Telepon</th>
                                <th>Jenis Kelamin</th>
                                <th>Username</th>
                                <th>Status Akun</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                        @forelse($items as $index => $it)
                            <tr>
                                <td><input type="checkbox" class="form-check-input guru-checkbox" data-guru-id="{{ $it->id }}"></td>
                                <td>{{ $index + 1 }}</td>
                                <td>{{ $it->nama }}</td>
                                <td><code>{{ $it->kode_guru ?? '-' }}</code></td>
                                <td>{{ $it->nip ?? '-' }}</td>
                                <td>{{ $it->email ?? '-' }}</td>
                                <td>{{ $it->telepon ?? '-' }}</td>
                                <td>
                                    @if($it->jenis_kelamin == 'L')
                                        <span class="badge bg-blue-lt">Laki-laki</span>
                                    @elseif($it->jenis_kelamin == 'P')
                                        <span class="badge bg-pink-lt">Perempuan</span>
                                    @else
                                        -
                                    @endif
                                </td>
                                <td>
                                    @if($it->user)
                                        <code>{{ $it->user->username }}</code>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td>
                                    @if($it->user && $it->user->is_active)
                                        <span class="badge bg-success">Aktif</span>
                                    @elseif($it->user)
                                        <span class="badge bg-secondary">Tidak Aktif</span>
                                    @else
                                        <span class="badge bg-warning">Belum Ada Akun</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="btn-list">
                                        <a href="{{ route('guru.edit', $it->id) }}" class="btn btn-sm btn-outline-primary">
                                            <i class="ti ti-edit"></i>
                                        </a>
                                        <button type="button" class="btn btn-sm btn-outline-danger" onclick="confirmDelete({{ $it->id }})">
                                            <i class="ti ti-trash"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="11" class="text-center text-muted">
                                    <i class="ti ti-info-circle me-2"></i>Belum ada data guru.
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

<!-- Modal Import -->
<div class="modal fade" id="modalImport" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Import Data Guru dari Excel</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('guru.import') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="modal-body">
                    <div class="alert alert-info">
                        <i class="ti ti-info-circle me-2"></i>
                        <strong>Petunjuk:</strong>
                        <ol class="mb-0 mt-2">
                            <li>Download template Excel terlebih dahulu</li>
                            <li>Isi data sesuai format template</li>
                            <li>Upload file yang sudah diisi</li>
                        </ol>
                    </div>
                    
                    <div class="mb-3">
                        <a href="{{ route('guru.template') }}" class="btn btn-outline-primary btn-sm">
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
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="ti ti-upload me-1"></i>Upload & Import
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Form Delete (Hidden) -->
<form id="deleteForm" method="POST" style="display:none;">
    @csrf
    @method('DELETE')
</form>

@endsection

@push('js')
<script>
function confirmDelete(id) {
    if (confirm('Apakah Anda yakin ingin menghapus data guru ini? Akun terkait juga akan dihapus.')) {
        const form = document.getElementById('deleteForm');
        form.action = `/guru/${id}`;
        form.submit();
    }
}

// Bulk selection functionality
document.addEventListener('DOMContentLoaded', function() {
    const selectAllCheckbox = document.getElementById('selectAllCheckbox');
    const guruCheckboxes = document.querySelectorAll('.guru-checkbox');
    const bulkActionSelect = document.getElementById('bulkActionSelect');
    const deleteSelectedBtn = document.getElementById('deleteSelectedBtn');
    const selectedCount = document.getElementById('selectedCount');
    const searchInput = document.getElementById('searchInput');

    // Search functionality
    searchInput.addEventListener('keyup', function() {
        const searchTerm = this.value.toLowerCase();
        const tableRows = document.querySelectorAll('tbody tr');
        
        tableRows.forEach(row => {
            // Skip empty message row
            if (row.cells.length === 1) {
                return;
            }
            
            const text = row.textContent.toLowerCase();
            const matches = searchTerm === '' || text.includes(searchTerm);
            row.style.display = matches ? '' : 'none';
        });

        // Uncheck all when search is active
        if (searchTerm !== '') {
            selectAllCheckbox.checked = false;
            selectAllCheckbox.indeterminate = false;
        }
    });

    // Update selected count
    function updateSelectedCount() {
        const count = document.querySelectorAll('.guru-checkbox:checked').length;
        selectedCount.textContent = count;
        deleteSelectedBtn.style.display = count > 0 ? 'block' : 'none';
    }

    // Select all functionality
    selectAllCheckbox.addEventListener('change', function() {
        guruCheckboxes.forEach(checkbox => {
            checkbox.checked = this.checked;
        });
        updateSelectedCount();
    });

    // Update select all checkbox when individual checkboxes change
    guruCheckboxes.forEach(checkbox => {
        checkbox.addEventListener('change', function() {
            const allChecked = Array.from(guruCheckboxes).every(cb => cb.checked);
            const someChecked = Array.from(guruCheckboxes).some(cb => cb.checked);
            selectAllCheckbox.checked = allChecked;
            selectAllCheckbox.indeterminate = someChecked && !allChecked;
            updateSelectedCount();
        });
    });

    // Bulk action select
    bulkActionSelect.addEventListener('change', function() {
        const action = this.value;
        
        if (action === 'select-all') {
            selectAllCheckbox.checked = true;
            guruCheckboxes.forEach(checkbox => checkbox.checked = true);
            updateSelectedCount();
        } else if (action === 'select-none') {
            selectAllCheckbox.checked = false;
            guruCheckboxes.forEach(checkbox => checkbox.checked = false);
            updateSelectedCount();
        } else if (action === 'delete') {
            deleteSelectedGurus();
        }
        
        // Reset select
        this.value = '';
    });

    // Delete selected gurus
    function deleteSelectedGurus() {
        const selectedIds = Array.from(document.querySelectorAll('.guru-checkbox:checked'))
            .map(checkbox => checkbox.getAttribute('data-guru-id'));
        
        if (selectedIds.length === 0) {
            alert('Pilih minimal satu guru untuk dihapus!');
            return;
        }

        if (!confirm(`Apakah Anda yakin ingin menghapus ${selectedIds.length} data guru ini? Akun terkait juga akan dihapus.`)) {
            return;
        }

        // Delete one by one
        let deleted = 0;
        selectedIds.forEach((id, index) => {
            setTimeout(() => {
                const form = document.getElementById('deleteForm');
                form.action = `/guru/${id}`;
                
                // Create a hidden form for each deletion
                const tempForm = document.createElement('form');
                tempForm.method = 'POST';
                tempForm.action = `/guru/${id}`;
                tempForm.style.display = 'none';
                
                const csrfInput = document.createElement('input');
                csrfInput.type = 'hidden';
                csrfInput.name = '_token';
                csrfInput.value = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
                
                const methodInput = document.createElement('input');
                methodInput.type = 'hidden';
                methodInput.name = '_method';
                methodInput.value = 'DELETE';
                
                tempForm.appendChild(csrfInput);
                tempForm.appendChild(methodInput);
                document.body.appendChild(tempForm);
                tempForm.submit();
            }, index * 500); // Delay to avoid race conditions
        });
    }

    // Delete selected button
    deleteSelectedBtn.addEventListener('click', deleteSelectedGurus);
});
</script>
@endpush