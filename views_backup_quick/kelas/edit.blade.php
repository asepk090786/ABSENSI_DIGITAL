@extends('layouts.app', ['pageSlug' => 'kelas'])

@section('title','Edit Kelas')

@section('content')
<div class="row">
    <div class="col-md-10 mx-auto">
        <div class="card mb-4">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h4 class="card-title mb-0">Edit Kelas</h4>
                <a href="{{ route('kelas.index') }}" class="btn btn-secondary btn-sm">Kembali</a>
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

                <form method="POST" action="{{ route('kelas.update', $kelas->id) }}">
                    @csrf
                    @method('PUT')

                    <div class="alert alert-info">
                        <i class="ti ti-info-circle me-2"></i>
                        <strong>Informasi:</strong> Perbarui data kelas dan wali kelas.
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Nama Kelas <span class="text-danger">*</span></label>
                        <input type="text" name="nama_kelas" class="form-control @error('nama_kelas') is-invalid @enderror" value="{{ old('nama_kelas', $kelas->nama_kelas) }}" required>
                        @error('nama_kelas')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Tingkatan Kelas <span class="text-danger">*</span></label>
                        <select name="tingkat_kelas" class="form-select @error('tingkat_kelas') is-invalid @enderror" required>
                            <option value="">-- Pilih Tingkatan --</option>
                            @php
                                $tingkatanByJenjang = [
                                    'SD' => ['I', 'II', 'III', 'IV', 'V', 'VI'],
                                    'SMP' => ['VII', 'VIII', 'IX'],
                                    'SMA' => ['X', 'XI', 'XII'],
                                    'SMK' => ['X', 'XI', 'XII'],
                                ];
                                $jenjang = $sekolah->jenjang ?? '';
                                $tingkatan = $tingkatanByJenjang[$jenjang] ?? [];
                            @endphp
                            @forelse($tingkatan as $tingkat)
                                <option value="{{ $tingkat }}" {{ old('tingkat_kelas', $kelas->tingkat_kelas) == $tingkat ? 'selected' : '' }}>
                                    Tingkat {{ $tingkat }}
                                </option>
                            @empty
                                <option value="">Jenjang sekolah belum diatur</option>
                            @endforelse
                        </select>
                        <small class="form-hint">Pilihan disesuaikan berdasarkan jenjang sekolah: {{ $jenjang ?? 'Belum diatur' }}</small>
                        @error('tingkat_kelas')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Jurusan</label>
                        <select name="jurusan" class="form-select @error('jurusan') is-invalid @enderror">
                            <option value="">-- Pilih Jurusan --</option>
                            @php $jurusanList = ['IPA','IPS','MIA','IIS','UMUM']; @endphp
                            @foreach($jurusanList as $jur)
                                <option value="{{ $jur }}" {{ old('jurusan', $kelas->jurusan) == $jur ? 'selected' : '' }}>{{ $jur }}</option>
                            @endforeach
                        </select>
                        <small class="form-hint">Kosongkan jika kelas tidak ber-jurusan.</small>
                        @error('jurusan')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Wali Kelas</label>
                        <select name="wali_kelas_id" class="form-select @error('wali_kelas_id') is-invalid @enderror">
                            <option value="">Pilih Wali Kelas</option>
                            @foreach($guruList as $guru)
                                <option value="{{ $guru->id }}" {{ old('wali_kelas_id', $kelas->wali_kelas_id)==$guru->id ? 'selected' : '' }}>{{ $guru->user->name ?? $guru->nama }}</option>
                            @endforeach
                        </select>
                        @error('wali_kelas_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="mt-4">
                        <button type="submit" class="btn btn-primary">
                            <i class="ti ti-device-floppy me-2"></i>Perbarui
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <div class="card">
            <div class="card-header d-flex align-items-center justify-content-between">
                <div>
                    <h4 class="card-title mb-0">Siswa di Kelas Ini</h4>
                    <div class="text-muted small">Tambahkan, eksport, atau import siswa khusus kelas ini.</div>
                </div>
                <div class="btn-list">
                    <a href="{{ route('kelas.siswa.export', ['kela' => $kelas->id]) }}" class="btn btn-info btn-sm">
                        <i class="ti ti-download me-1"></i>Export Siswa
                    </a>
                    <button type="button" class="btn btn-success btn-sm" data-toggle="modal" data-target="#modalImportSiswa">
                        <i class="ti ti-upload me-1"></i>Import Siswa
                    </button>
                    <a href="{{ route('kelas.siswa.template', ['kela' => $kelas->id]) }}" class="btn btn-outline-primary btn-sm">
                        <i class="ti ti-file-spreadsheet me-1"></i>Template Siswa
                    </a>
                </div>
            </div>
            <div class="card-body">
                <div class="border rounded p-3 mb-4">
                    <h6 class="mb-3">Tambah Siswa ke {{ $kelas->nama_kelas }}</h6>
                    <form method="POST" action="{{ route('kelas.siswa.add', ['kela' => $kelas->id]) }}">
                        @csrf
                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label class="form-label">NIS <span class="text-danger">*</span></label>
                                <input type="text" name="nis" class="form-control @error('nis') is-invalid @enderror" value="{{ old('nis') }}" required>
                                @error('nis')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label">NISN <span class="text-danger">*</span></label>
                                <input type="text" name="nisn" class="form-control @error('nisn') is-invalid @enderror" value="{{ old('nisn') }}" required>
                                @error('nisn')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Nama <span class="text-danger">*</span></label>
                                <input type="text" name="nama" class="form-control @error('nama') is-invalid @enderror" value="{{ old('nama') }}" required>
                                @error('nama')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Jenis Kelamin <span class="text-danger">*</span></label>
                                <select name="jenis_kelamin" class="form-select @error('jenis_kelamin') is-invalid @enderror" required>
                                    <option value="">Pilih</option>
                                    <option value="L" {{ old('jenis_kelamin')=='L' ? 'selected' : '' }}>Laki-laki</option>
                                    <option value="P" {{ old('jenis_kelamin')=='P' ? 'selected' : '' }}>Perempuan</option>
                                </select>
                                @error('jenis_kelamin')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Email <span class="text-danger">*</span></label>
                                <input type="email" name="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email') }}" required>
                                @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Username <span class="text-danger">*</span></label>
                                <input type="text" name="username" class="form-control @error('username') is-invalid @enderror" value="{{ old('username') }}" required>
                                @error('username')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Password <span class="text-danger">*</span></label>
                                <input type="password" name="password" class="form-control @error('password') is-invalid @enderror" required>
                                <small class="form-hint">Minimal 6 karakter</small>
                                @error('password')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Konfirmasi Password <span class="text-danger">*</span></label>
                                <input type="password" name="password_confirmation" class="form-control" required>
                            </div>
                        </div>

                        <div class="mt-3">
                            <button type="submit" class="btn btn-primary">
                                <i class="ti ti-user-plus me-2"></i>Tambah Siswa
                            </button>
                        </div>
                    </form>
                </div>

                {{-- Tambah Siswa dari Database --}}
                @if($siswaWithoutClass->count() > 0)
                <div class="border rounded p-3 mb-4 bg-light">
                    <h6 class="mb-3">Tambah Siswa dari Database</h6>
                    <p class="text-muted small mb-3">Pilih siswa yang sudah terdaftar di database tapi belum memiliki kelas.</p>
                    <form method="POST" action="{{ route('kelas.siswa.assign', ['kela' => $kelas->id]) }}" id="formAssignExisting">
                        @csrf
                        <div class="mb-3">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <label class="form-label mb-0">Pilih Siswa <span class="text-danger">*</span></label>
                                <div class="form-check">
                                    <input type="checkbox" id="checkAll" class="form-check-input">
                                    <label class="form-check-label" for="checkAll">Pilih Semua</label>
                                </div>
                            </div>
                            <div class="form-selectgroup form-selectgroup-boxes d-flex flex-column gap-2" style="max-height: 300px; overflow-y: auto;" id="siswaList">
                                @foreach($siswaWithoutClass as $siswa)
                                <label class="form-selectgroup-item flex-fill">
                                    <input type="checkbox" name="siswa_ids[]" value="{{ $siswa->id }}" class="form-selectgroup-input siswa-checkbox">
                                    <div class="form-selectgroup-label d-flex align-items-center justify-content-between p-3">
                                        <div class="me-3">
                                            <div class="fw-bold">{{ $siswa->nama }}</div>
                                            <div class="text-muted small">NIS: {{ $siswa->nis }} | NISN: {{ $siswa->nisn }}</div>
                                        </div>
                                        <span class="badge bg-secondary">{{ $siswa->jenis_kelamin == 'L' ? 'Laki-laki' : 'Perempuan' }}</span>
                                    </div>
                                </label>
                                @endforeach
                            </div>
                            @error('siswa_ids')<div class="text-danger small mt-2">{{ $message }}</div>@enderror
                        </div>
                        <div>
                            <button type="submit" class="btn btn-success">
                                <i class="ti ti-user-check me-2"></i>Tambahkan Siswa yang Dipilih
                            </button>
                        </div>
                    </form>
                </div>
                @endif

                <!-- Search and Bulk Actions -->
                <div class="mb-3 d-flex gap-2 align-items-center flex-wrap">
                    <input type="text" id="searchSiswa" class="form-control" placeholder="Cari nama atau NIS..." style="max-width: 250px;">
                    <div id="bulkActionsContainer" style="display: none;">
                        <form id="bulkActionForm" method="POST" style="display: inline;">
                            @csrf
                            <input type="hidden" id="bulkSiswaIds" name="siswa_ids" value="">
                            <div class="btn-group" role="group">
                                <button type="button" class="btn btn-sm btn-outline-danger" onclick="submitBulkAction('{{ route('kelas.siswa.bulk-delete', ['kela' => $kelas->id]) }}', 'Apakah Anda yakin ingin menghapus siswa terpilih dari kelas?')">
                                    <i class="ti ti-trash me-1"></i>Hapus dari Kelas
                                </button>
                                <button type="button" class="btn btn-sm btn-outline-warning" onclick="submitBulkAction('{{ route('kelas.siswa.bulk-deactivate', ['kela' => $kelas->id]) }}', 'Apakah Anda yakin ingin menonaktifkan akun siswa terpilih?')">
                                    <i class="ti ti-lock me-1"></i>Nonaktifkan
                                </button>
                                <button type="button" class="btn btn-sm btn-outline-success" onclick="submitBulkAction('{{ route('kelas.siswa.bulk-activate', ['kela' => $kelas->id]) }}', 'Apakah Anda yakin ingin mengaktifkan akun siswa terpilih?')">
                                    <i class="ti ti-lock-open me-1"></i>Aktifkan
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table table-striped table-hover" id="siswaTabel">
                        <thead>
                            <tr>
                                <th style="width: 40px;">
                                    <input type="checkbox" id="checkAllSiswa" class="form-check-input">
                                </th>
                                <th>No</th>
                                <th>ID</th>
                                <th>NIS</th>
                                <th>NISN</th>
                                <th>Nama</th>
                                <th>Jenis Kelamin</th>
                                <th>Jabatan Kelas</th>
                                <th>Username</th>
                                <th>Email</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                        @forelse($kelas->siswa as $index => $s)
                            <tr class="siswa-row">
                                <td>
                                    <input type="checkbox" class="form-check-input siswa-checkbox" value="{{ $s->id }}" data-nama="{{ $s->nama }}">
                                </td>
                                <td>{{ $index + 1 }}</td>
                                <td><code>{{ $s->id }}</code></td>
                                <td>{{ $s->nis }}</td>
                                <td>{{ $s->nisn }}</td>
                                <td class="nama-siswa">{{ $s->nama }}</td>
                                <td>
                                    @if($s->jenis_kelamin == 'L')
                                        <span class="badge bg-blue-lt">Laki-laki</span>
                                    @elseif($s->jenis_kelamin == 'P')
                                        <span class="badge bg-pink-lt">Perempuan</span>
                                    @else
                                        -
                                    @endif
                                </td>
                                <td>
                                    @if($s->jabatan_kelas === 'ketua')
                                        <span class="badge bg-primary">Ketua Kelas</span>
                                    @elseif($s->jabatan_kelas === 'wakil')
                                        <span class="badge bg-info">Wakil Ketua Kelas</span>
                                    @elseif($s->jabatan_kelas === 'sekretaris')
                                        <span class="badge bg-warning">Sekretaris Kelas</span>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td>{{ $s->user->username ?? '-' }}</td>
                                <td>{{ $s->user->email ?? $s->email ?? '-' }}</td>
                                <td>
                                    <div class="btn-group" role="group">
                                        <a href="{{ route('siswa.edit', $s->id) }}" class="btn btn-sm btn-outline-primary" title="Edit">
                                            <i class="ti ti-edit"></i>
                                        </a>
                                        <form method="POST" action="{{ route('kelas.siswa.delete', ['kela' => $kelas->id, 'siswa' => $s->id]) }}" style="display: inline;" onsubmit="return confirm('Apakah Anda yakin ingin menghapus siswa ' + '{{ $s->nama }}' + ' dari kelas ini?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-outline-danger" title="Hapus dari kelas">
                                                <i class="ti ti-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="11" class="text-center text-muted">
                                    <i class="ti ti-info-circle me-2"></i>Belum ada siswa di kelas ini.
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

<div class="modal fade" id="modalImportSiswa" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Import Siswa Kelas</h5>
                <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span></button>
            </div>
            <form action="{{ route('kelas.siswa.import', ['kela' => $kelas->id]) }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="modal-body">
                    <div class="alert alert-info">
                        <i class="ti ti-info-circle me-2"></i>
                        <strong>Petunjuk:</strong>
                        <ol class="mb-0 mt-2">
                            <li>Gunakan template khusus kelas ini</li>
                            <li>Kolom ID dapat diisi untuk update siswa</li>
                            <li>kelas_id otomatis dikunci ke kelas ini</li>
                        </ol>
                    </div>

                    <div class="mb-3">
                        <a href="{{ route('kelas.siswa.template', ['kela' => $kelas->id]) }}" class="btn btn-outline-primary btn-sm">
                            <i class="ti ti-file-spreadsheet me-1"></i>Download Template
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

<script>
// Script untuk checkbox dan bulk actions
document.addEventListener('DOMContentLoaded', function() {
    const checkAllSiswa = document.getElementById('checkAllSiswa');
    const siswaCheckboxes = document.querySelectorAll('.siswa-checkbox');
    const bulkActionsContainer = document.getElementById('bulkActionsContainer');
    const searchInput = document.getElementById('searchSiswa');
    const siswaTabel = document.getElementById('siswaTabel');
    const bulkSiswaIds = document.getElementById('bulkSiswaIds');

    // Handle Pilih Semua checkbox
    if (checkAllSiswa) {
        checkAllSiswa.addEventListener('change', function() {
            const isChecked = this.checked;
            siswaCheckboxes.forEach(checkbox => {
                const row = checkbox.closest('.siswa-row');
                // Only check checkboxes for visible rows
                if (row && row.style.display !== 'none') {
                    checkbox.checked = isChecked;
                }
            });
            updateBulkActionsVisibility();
        });
    }

    // Handle individual siswa checkbox
    siswaCheckboxes.forEach(checkbox => {
        checkbox.addEventListener('change', function() {
            updateCheckAllStatus();
            updateBulkActionsVisibility();
        });
    });

    // Search/Filter functionality
    if (searchInput) {
        searchInput.addEventListener('keyup', function() {
            const searchTerm = this.value.toLowerCase();
            const rows = document.querySelectorAll('.siswa-row');
            
            rows.forEach(row => {
                const nama = row.querySelector('.nama-siswa')?.textContent.toLowerCase() || '';
                const nis = row.querySelector('td:nth-child(4)')?.textContent.toLowerCase() || '';
                
                if (nama.includes(searchTerm) || nis.includes(searchTerm)) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                    row.querySelector('.siswa-checkbox').checked = false;
                }
            });
            
            updateCheckAllStatus();
            updateBulkActionsVisibility();
        });
    }

    function updateCheckAllStatus() {
        if (!checkAllSiswa) return;
        
        const visibleCheckboxes = Array.from(siswaCheckboxes).filter(cb => {
            const row = cb.closest('.siswa-row');
            return row && row.style.display !== 'none';
        });
        
        if (visibleCheckboxes.length === 0) {
            checkAllSiswa.checked = false;
            checkAllSiswa.indeterminate = false;
            return;
        }
        
        const checkedCount = visibleCheckboxes.filter(cb => cb.checked).length;
        
        if (checkedCount === 0) {
            checkAllSiswa.checked = false;
            checkAllSiswa.indeterminate = false;
        } else if (checkedCount === visibleCheckboxes.length) {
            checkAllSiswa.checked = true;
            checkAllSiswa.indeterminate = false;
        } else {
            checkAllSiswa.checked = false;
            checkAllSiswa.indeterminate = true;
        }
    }

    function updateBulkActionsVisibility() {
        const checkedCount = Array.from(siswaCheckboxes).filter(cb => cb.checked).length;
        bulkActionsContainer.style.display = checkedCount > 0 ? 'block' : 'none';
        
        if (checkedCount > 0) {
            const selectedIds = Array.from(siswaCheckboxes)
                .filter(cb => cb.checked)
                .map(cb => cb.value);
            bulkSiswaIds.value = JSON.stringify(selectedIds);
        }
    }

    window.submitBulkAction = function(action, message) {
        if (!confirm(message)) return;
        
        const selectedIds = Array.from(siswaCheckboxes)
            .filter(cb => cb.checked)
            .map(cb => cb.value);
        
        if (selectedIds.length === 0) {
            alert('Silakan pilih minimal satu siswa');
            return;
        }

        const form = document.getElementById('bulkActionForm');
        form.action = action;
        
        // Remove old siswa_ids inputs
        const oldInputs = form.querySelectorAll('input[name="siswa_ids"]');
        oldInputs.forEach(input => {
            if (input.id !== 'bulkSiswaIds') {
                input.remove();
            }
        });
        
        // Update the hidden input with selected IDs
        bulkSiswaIds.value = JSON.stringify(selectedIds);
        
        form.submit();
    };
});
</script>
@endsection
