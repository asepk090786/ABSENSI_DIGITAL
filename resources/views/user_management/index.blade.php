@extends('layouts.app')

@section('title','Kelola Akun')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h4 class="card-title mb-0">Daftar Akun Pengguna</h4>
                <div class="btn-group" role="group">
                    <a href="{{ route('users.create') }}" class="btn btn-primary btn-sm">
                        <i class="ti ti-plus"></i> Tambah Akun
                    </a>
                    <a href="{{ route('users.export') }}" class="btn btn-success btn-sm" title="Export ke Excel">
                        <i class="ti ti-download"></i> Export
                    </a>
                    <button class="btn btn-info btn-sm" data-bs-toggle="modal" data-bs-target="#importModal" title="Import dari Excel">
                        <i class="ti ti-upload"></i> Import
                    </button>
                    <button type="button" class="btn btn-danger btn-sm" id="bulkDeleteBtn" style="display: none;">
                        <i class="ti ti-trash"></i> Hapus Dipilih
                    </button>
                </div>
            </div>
            <div class="card-body">
                @if(session('success'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        {{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif

                @if(session('import_errors') && count(session('import_errors')) > 0)
                    <div class="alert alert-warning alert-dismissible fade show" role="alert">
                        <strong>Beberapa baris tidak berhasil diimpor:</strong>
                        <ul class="mt-2 mb-0">
                            @foreach(session('import_errors') as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif

                <div class="table-responsive">
                    <table class="table table-sm align-middle" id="usersTable">
                        <thead>
                            <tr>
                                <th style="width: 30px;">
                                    <input type="checkbox" id="selectAllCheckbox" class="form-check-input">
                                </th>
                                <th>Nama</th>
                                <th>Username</th>
                                <th>Peran</th>
                                <th>Status</th>
                                <th>Terhubung</th>
                                <th class="text-end">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($users as $user)
                                <tr>
                                    <td>
                                        <input type="checkbox" class="form-check-input user-checkbox" value="{{ $user->id }}">
                                    </td>
                                    <td>{{ $user->name }}</td>
                                    <td>{{ $user->username }}</td>
                                    <td>{{ $user->role->role_name ?? '-' }}</td>
                                    <td>
                                        @if($user->is_active)
                                            <span class="badge bg-success">Aktif</span>
                                        @else
                                            <span class="badge bg-secondary">Nonaktif</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($user->guru_id)
                                            Guru (ID: {{ $user->guru_id }})
                                        @elseif($user->siswa_id)
                                            Siswa (ID: {{ $user->siswa_id }})
                                        @else
                                            -
                                        @endif
                                    </td>
                                    <td class="text-end">
                                        <div class="btn-group" role="group">
                                            <a href="{{ route('users.show', $user->id) }}" class="btn btn-sm btn-info">Detail</a>
                                            <a href="{{ route('users.edit', $user->id) }}" class="btn btn-sm btn-warning">Edit</a>
                                            <form action="{{ route('users.activate', $user->id) }}" method="POST" class="d-inline">
                                                @csrf
                                                @method('PATCH')
                                                <button type="submit" class="btn btn-sm btn-warning">
                                                    {{ $user->is_active ? 'Nonaktifkan' : 'Aktifkan' }}
                                                </button>
                                            </form>
                                            <form action="{{ route('users.destroy', $user->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Yakin hapus akun ini?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-danger">Hapus</button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center text-muted">Belum ada akun.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="mt-3">
                    {{ $users->links() }}
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Import Modal -->
<div class="modal fade" id="importModal" tabindex="-1" role="dialog" aria-labelledby="importModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="importModalLabel">Import Akun Pengguna dari Excel</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('users.import') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">File Excel <span class="text-danger">*</span></label>
                        <input type="file" name="file" class="form-control @error('file') is-invalid @enderror" accept=".xlsx,.xls" required>
                        @error('file')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                        <small class="text-muted d-block mt-2">
                            Gunakan template: <a href="{{ route('users.template') }}" download>import_akun_pengguna.xlsx</a>
                        </small>
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

<!-- Bulk Delete Modal -->
<div class="modal fade" id="bulkDeleteModal" tabindex="-1" role="dialog" aria-labelledby="bulkDeleteModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="bulkDeleteModalLabel">Hapus Akun Terpilih</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="bulkDeleteForm" method="POST">
                @csrf
                @method('DELETE')
                <div class="modal-body">
                    <p>Apakah Anda yakin ingin menghapus <strong id="selectedCount">0</strong> akun terpilih?</p>
                    <div id="selectedUsersList" style="max-height: 300px; overflow-y: auto;"></div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-danger">Hapus Sekarang</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const selectAllCheckbox = document.getElementById('selectAllCheckbox');
    const userCheckboxes = document.querySelectorAll('.user-checkbox');
    const bulkDeleteBtn = document.getElementById('bulkDeleteBtn');
    const bulkDeleteForm = document.getElementById('bulkDeleteForm');
    const bulkDeleteModal = new bootstrap.Modal(document.getElementById('bulkDeleteModal'));

    // Select all checkbox
    selectAllCheckbox.addEventListener('change', function() {
        userCheckboxes.forEach(checkbox => {
            checkbox.checked = this.checked;
        });
        updateBulkDeleteButton();
    });

    // Individual checkboxes
    userCheckboxes.forEach(checkbox => {
        checkbox.addEventListener('change', function() {
            updateSelectAllCheckbox();
            updateBulkDeleteButton();
        });
    });

    function updateSelectAllCheckbox() {
        const allChecked = Array.from(userCheckboxes).every(cb => cb.checked);
        const someChecked = Array.from(userCheckboxes).some(cb => cb.checked);
        selectAllCheckbox.checked = allChecked;
        selectAllCheckbox.indeterminate = someChecked && !allChecked;
    }

    function updateBulkDeleteButton() {
        const checkedCount = document.querySelectorAll('.user-checkbox:checked').length;
        bulkDeleteBtn.style.display = checkedCount > 0 ? 'inline-block' : 'none';
    }

    bulkDeleteBtn.addEventListener('click', function() {
        const selectedCheckboxes = document.querySelectorAll('.user-checkbox:checked');
        const selectedIds = Array.from(selectedCheckboxes).map(cb => cb.value);
        
        if (selectedIds.length === 0) {
            alert('Pilih minimal 1 akun untuk dihapus');
            return;
        }

        // Update modal content
        document.getElementById('selectedCount').textContent = selectedIds.length;
        
        // List selected users
        const usersList = document.getElementById('selectedUsersList');
        usersList.innerHTML = '<ul class="list-unstyled">';
        selectedCheckboxes.forEach(checkbox => {
            const row = checkbox.closest('tr');
            const nameCell = row.cells[1];
            const usernameCell = row.cells[2];
            usersList.innerHTML += `<li>${nameCell.textContent} (${usernameCell.textContent})</li>`;
        });
        usersList.innerHTML += '</ul>';

        // Create hidden inputs for form submission
        const existingInputs = bulkDeleteForm.querySelectorAll('input[name="ids[]"]');
        existingInputs.forEach(input => input.remove());
        
        selectedIds.forEach(id => {
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = 'ids[]';
            input.value = id;
            bulkDeleteForm.appendChild(input);
        });

        // Set form action
        bulkDeleteForm.action = '{{ route("users.bulkDelete") }}';

        // Show modal
        bulkDeleteModal.show();
    });
});
</script>
@endsection
