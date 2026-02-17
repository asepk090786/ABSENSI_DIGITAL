@extends('layouts.app')

@section('title','Kelola Akun')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h4 class="card-title mb-0">Daftar Akun Pengguna</h4>
                @if(!auth()->user()->hasAnyRole(['Guru', 'Guru Mapel','Guru Kelas','Wali Kelas','Guru BK','Guru Piket']))
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
                @endif
            </div>
            <div class="card-body">
                <form method="GET" action="" class="mb-3">
                    <div class="input-group">
                        <input type="text" name="search" class="form-control" placeholder="Cari nama, username, email, atau peran..." value="{{ request('search') }}">
                        <button class="btn btn-primary" type="submit">Cari</button>
                        @if(request('search'))
                            <a href="{{ route('users.index') }}" class="btn btn-secondary">Reset</a>
                        @endif
                    </div>
                </form>
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

                @php
                    // Define role groups
                    $staffRoles = ['Admin', 'Kepala Sekolah', 'Guru Kelas', 'Wali Kelas', 'Guru Mapel', 'Guru BK', 'Guru Piket', 'Petugas Keamanan', 'Wakil Kepala Sekolah', 'Pembina'];
                    
                    // Separate users by staff and siswa
                    $staffUsers = $users->filter(function($user) use ($staffRoles) {
                        return in_array($user->role->role_name ?? '', $staffRoles);
                    });
                    
                    $siswaUsers = $users->filter(function($user) {
                        return ($user->role->role_name ?? '') === 'Siswa';
                    });
                @endphp

                @if($users->count() > 0)
                    <!-- TABEL 1: STAFF & GURU -->
                    @if($staffUsers->count() > 0)
                        <div class="card mb-4">
                            <div class="card-header bg-primary text-white">
                                <h5 class="mb-0">
                                    <i class="ti ti-users"></i>
                                    <strong>Daftar Pengguna - Staf & Guru</strong>
                                    <span class="badge bg-light text-primary ms-2">{{ $staffUsers->count() }} pengguna</span>
                                </h5>
                            </div>
                            <div class="table-responsive">
                                <table class="table table-sm align-middle mb-0">
                                    <thead class="table-light">
                                        <tr>
                                            <th style="width: 30px;">
                                                <input type="checkbox" class="form-check-input staff-select-all">
                                            </th>
                                            <th>Nama</th>
                                            <th>Username</th>
                                            <th>Peran</th>
                                            <th>Email</th>
                                            <th>Status</th>
                                            <th>Terhubung</th>
                                            <th class="text-end" style="width: 280px;">Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($staffUsers as $user)
                                            <tr>
                                                <td>
                                                    <input type="checkbox" class="form-check-input staff-checkbox" value="{{ $user->id }}">
                                                </td>
                                                <td><strong>{{ $user->name }}</strong></td>
                                                <td>{{ $user->username }}</td>
                                                <td><span class="badge bg-secondary">{{ $user->role->role_name ?? '-' }}</span></td>
                                                <td>{{ $user->email ?? '-' }}</td>
                                                <td>
                                                    @if($user->is_active)
                                                        <span class="badge bg-success">Aktif</span>
                                                    @else
                                                        <span class="badge bg-secondary">Nonaktif</span>
                                                    @endif
                                                </td>
                                                <td>
                                                    @if($user->guru_id)
                                                        <small>Guru (ID: {{ $user->guru_id }})</small>
                                                    @else
                                                        <small>-</small>
                                                    @endif
                                                </td>
                                                <td class="text-end">
                                                    <div class="btn-group btn-group-sm" role="group">
                                                        <a href="{{ route('users.show', $user->id) }}" class="btn btn-info" title="Lihat detail">Detail</a>
                                                        <a href="{{ route('users.edit', $user->id) }}" class="btn btn-warning" style="background-color: #ffc107; color: #212529; border-color: #ffc107;" title="Edit akun">Edit</a>
                                                        <form action="{{ route('users.activate', $user->id) }}" method="POST" class="d-inline">
                                                            @csrf
                                                            @method('PATCH')
                                                            <button type="submit" class="btn btn-warning btn-sm" title="{{ $user->is_active ? 'Nonaktifkan' : 'Aktifkan' }}">
                                                                {{ $user->is_active ? 'Nonaktif' : 'Aktif' }}
                                                            </button>
                                                        </form>
                                                        <form action="{{ route('users.destroy', $user->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Yakin hapus akun ini?')">
                                                            @csrf
                                                            @method('DELETE')
                                                            <button type="submit" class="btn btn-danger btn-sm" title="Hapus akun">Hapus</button>
                                                        </form>
                                                    </div>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    @endif

                    <!-- TABEL 2: SISWA -->
                    @if($siswaUsers->count() > 0)
                        <div class="card mb-4">
                            <div class="card-header bg-success text-white">
                                <h5 class="mb-0">
                                    <i class="ti ti-school"></i>
                                    <strong>Daftar Pengguna - Siswa</strong>
                                    <span class="badge bg-light text-success ms-2">{{ $siswaUsers->count() }} pengguna</span>
                                </h5>
                            </div>
                            <div class="table-responsive">
                                <table class="table table-sm align-middle mb-0">
                                    <thead class="table-light">
                                        <tr>
                                            <th style="width: 30px;">
                                                <input type="checkbox" class="form-check-input siswa-select-all">
                                            </th>
                                            <th>Nama</th>
                                            <th>Username</th>
                                            <th>Email</th>
                                            <th>Status</th>
                                            <th>Terhubung</th>
                                            <th class="text-end" style="width: 280px;">Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($siswaUsers as $user)
                                            <tr>
                                                <td>
                                                    <input type="checkbox" class="form-check-input siswa-checkbox" value="{{ $user->id }}">
                                                </td>
                                                <td><strong>{{ $user->name }}</strong></td>
                                                <td>{{ $user->username }}</td>
                                                <td>{{ $user->email ?? '-' }}</td>
                                                <td>
                                                    @if($user->is_active)
                                                        <span class="badge bg-success">Aktif</span>
                                                    @else
                                                        <span class="badge bg-secondary">Nonaktif</span>
                                                    @endif
                                                </td>
                                                <td>
                                                    @if($user->siswa_id)
                                                        <small>Siswa (ID: {{ $user->siswa_id }})</small>
                                                    @else
                                                        <small>-</small>
                                                    @endif
                                                </td>
                                                <td class="text-end">
                                                    <div class="btn-group btn-group-sm" role="group">
                                                        <a href="{{ route('users.show', $user->id) }}" class="btn btn-info" title="Lihat detail">Detail</a>
                                                        <a href="{{ route('users.edit', $user->id) }}" class="btn btn-warning" style="background-color: #ffc107; color: #212529; border-color: #ffc107;" title="Edit akun">Edit</a>
                                                        <form action="{{ route('users.activate', $user->id) }}" method="POST" class="d-inline">
                                                            @csrf
                                                            @method('PATCH')
                                                            <button type="submit" class="btn btn-warning btn-sm" title="{{ $user->is_active ? 'Nonaktifkan' : 'Aktifkan' }}">
                                                                {{ $user->is_active ? 'Nonaktif' : 'Aktif' }}
                                                            </button>
                                                        </form>
                                                        <form action="{{ route('users.destroy', $user->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Yakin hapus akun ini?')">
                                                            @csrf
                                                            @method('DELETE')
                                                            <button type="submit" class="btn btn-danger btn-sm" title="Hapus akun">Hapus</button>
                                                        </form>
                                                    </div>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    @endif
                @else
                    <div class="alert alert-info text-center">
                        <p class="mb-0">Belum ada akun.</p>
                    </div>
                @endif

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
    const staffCheckboxes = document.querySelectorAll('.staff-checkbox');
    const siswaCheckboxes = document.querySelectorAll('.siswa-checkbox');
    const staffSelectAll = document.querySelector('.staff-select-all');
    const siswaSelectAll = document.querySelector('.siswa-select-all');
    const bulkDeleteBtn = document.getElementById('bulkDeleteBtn');
    const bulkDeleteForm = document.getElementById('bulkDeleteForm');
    const bulkDeleteModal = new bootstrap.Modal(document.getElementById('bulkDeleteModal'));

    // Staff select all
    if (staffSelectAll) {
        staffSelectAll.addEventListener('change', function() {
            staffCheckboxes.forEach(checkbox => {
                checkbox.checked = this.checked;
            });
            updateBulkDeleteButton();
        });
    }

    // Siswa select all
    if (siswaSelectAll) {
        siswaSelectAll.addEventListener('change', function() {
            siswaCheckboxes.forEach(checkbox => {
                checkbox.checked = this.checked;
            });
            updateBulkDeleteButton();
        });
    }

    // Staff checkboxes
    staffCheckboxes.forEach(checkbox => {
        checkbox.addEventListener('change', function() {
            updateStaffSelectAll();
            updateBulkDeleteButton();
        });
    });

    // Siswa checkboxes
    siswaCheckboxes.forEach(checkbox => {
        checkbox.addEventListener('change', function() {
            updateSiswaSelectAll();
            updateBulkDeleteButton();
        });
    });

    function updateStaffSelectAll() {
        if (staffSelectAll && staffCheckboxes.length > 0) {
            const allChecked = Array.from(staffCheckboxes).every(cb => cb.checked);
            const someChecked = Array.from(staffCheckboxes).some(cb => cb.checked);
            staffSelectAll.checked = allChecked;
            staffSelectAll.indeterminate = someChecked && !allChecked;
        }
    }

    function updateSiswaSelectAll() {
        if (siswaSelectAll && siswaCheckboxes.length > 0) {
            const allChecked = Array.from(siswaCheckboxes).every(cb => cb.checked);
            const someChecked = Array.from(siswaCheckboxes).some(cb => cb.checked);
            siswaSelectAll.checked = allChecked;
            siswaSelectAll.indeterminate = someChecked && !allChecked;
        }
    }

    function updateBulkDeleteButton() {
        const allCheckboxes = document.querySelectorAll('.staff-checkbox:checked, .siswa-checkbox:checked');
        const checkedCount = allCheckboxes.length;
        bulkDeleteBtn.style.display = checkedCount > 0 ? 'inline-block' : 'none';
    }

    bulkDeleteBtn.addEventListener('click', function() {
        const selectedCheckboxes = document.querySelectorAll('.staff-checkbox:checked, .siswa-checkbox:checked');
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
