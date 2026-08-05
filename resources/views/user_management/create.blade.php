@extends('layouts.app')

@section('title','Tambah Akun')

@section('content')
<div class="row">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h4 class="card-title fw-semibold m-0">{{ request()->query('role') === 'Admin' ? 'Tambah Akun Admin' : 'Tambah Akun' }}</h4>
                <a href="{{ request()->query('role') === 'Admin' ? route('users.admin') : route('users.index') }}" class="btn btn-secondary btn-sm">Kembali</a>
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('users.store') }}">
                    @csrf
                    <div class="mb-2">
                        <label class="form-label">Nama Lengkap <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name') }}" required>
                        @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="mb-2">
                        <label class="form-label">NIP</label>
                        <input type="text" name="nip" class="form-control @error('nip') is-invalid @enderror" value="{{ old('nip') }}">
                        @error('nip')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-2">
                            <label class="form-label">Username <span class="text-danger">*</span></label>
                            <input type="text" name="username" class="form-control @error('username') is-invalid @enderror" value="{{ old('username') }}" required>
                            @error('username')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6 mb-2">
                            <label class="form-label">Email</label>
                            <input type="email" name="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email') }}">
                            @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>
                    <div class="mb-2">
                        <label class="form-label">Password <span class="text-danger">*</span></label>
                        <input type="password" name="password" class="form-control @error('password') is-invalid @enderror">
                        <div id="passwordHelpText" class="form-text text-muted">Isi password jika membuat akun baru atau ingin mengganti password.</div>
                        @error('password')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="mb-2">
                        <label class="form-label">Jenis Kelamin <span class="text-danger">*</span></label>
                        <select name="jenis_kelamin" class="form-select @error('jenis_kelamin') is-invalid @enderror" required>
                            <option value="">Pilih</option>
                            <option value="L" {{ old('jenis_kelamin')=='L' ? 'selected' : '' }}>Laki-laki</option>
                            <option value="P" {{ old('jenis_kelamin')=='P' ? 'selected' : '' }}>Perempuan</option>
                        </select>
                        @error('jenis_kelamin')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="mb-2">
                        <label class="form-label">Peran <span class="text-danger">*</span></label>
                        <select name="role_id" class="form-select @error('role_id') is-invalid @enderror" required>
                            <option value="">Pilih Peran</option>
                            @foreach($roles as $role)
                                <option value="{{ $role->id }}" {{ old('role_id', $defaultRoleId) == $role->id ? 'selected' : '' }}>{{ $role->role_name }}</option>
                            @endforeach
                        </select>
                        @error('role_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-2">
                            <label class="form-label">Guru (untuk Admin / Kepala Sekolah / Guru BK / Guru / Pengawas Pembina)</label>
                            <select name="guru_id" class="form-select @error('guru_id') is-invalid @enderror">
                                <option value="">-- Pilih Guru --</option>
                                @foreach($guru as $g)
                                    @php
                                        $identifier = $g->kode_guru ?? $g->nip ?? $g->id;
                                        $autoUsername = $g->user->username ?? $g->username ?? 'guru' . $identifier;
                                        $autoPassword = $g->user ? '' : 'guru' . $identifier;
                                    @endphp
                                    <option value="{{ $g->id }}"
                                        data-name="{{ htmlspecialchars($g->nama, ENT_QUOTES, 'UTF-8') }}"
                                        data-nip="{{ htmlspecialchars($g->nip ?? '', ENT_QUOTES, 'UTF-8') }}"
                                        data-email="{{ htmlspecialchars($g->email ?? '', ENT_QUOTES, 'UTF-8') }}"
                                        data-jenis-kelamin="{{ htmlspecialchars($g->jenis_kelamin ?? '', ENT_QUOTES, 'UTF-8') }}"
                                        data-username="{{ htmlspecialchars($autoUsername, ENT_QUOTES, 'UTF-8') }}"
                                        data-password="{{ htmlspecialchars($autoPassword, ENT_QUOTES, 'UTF-8') }}"
                                        data-has-user="{{ $g->user ? '1' : '0' }}"
                                        {{ old('guru_id') == $g->id ? 'selected' : '' }}>
                                        {{ $g->nama }}
                                    </option>
                                @endforeach
                            </select>
                            @error('guru_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6 mb-2">
                            <label class="form-label">Kepala Sekolah (untuk peran Kepala Sekolah)</label>
                            <select name="kepala_sekolah_id" class="form-select @error('kepala_sekolah_id') is-invalid @enderror">
                                <option value="">-- Pilih Kepala Sekolah --</option>
                                @foreach($kepala as $k)
                                    <option value="{{ $k->id }}" {{ old('kepala_sekolah_id') == $k->id ? 'selected' : '' }}>
                                        {{ $k->nama }} @if($k->guru) - {{ $k->guru->nama }} @endif
                                    </option>
                                @endforeach
                            </select>
                            @error('kepala_sekolah_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6 mb-2">
                            <label class="form-label">Siswa (untuk akun siswa)</label>
                            <select name="siswa_id" class="form-select @error('siswa_id') is-invalid @enderror">
                                <option value="">-- Pilih Siswa --</option>
                                @foreach($siswa as $s)
                                    <option value="{{ $s->id }}" {{ old('siswa_id') == $s->id ? 'selected' : '' }}>{{ $s->nama }}</option>
                                @endforeach
                            </select>
                            @error('siswa_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>
                    <button type="submit" class="btn btn-primary">Simpan Akun</button>
                </form>
            </div>
        </div>
    </div>
</div>

@push('js')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const roleSelect = document.querySelector('select[name="role_id"]');
        const guruSelect = document.querySelector('select[name="guru_id"]');
        const guruField = guruSelect.closest('.mb-2');
        const kepalaField = document.querySelector('select[name="kepala_sekolah_id"]').closest('.mb-2');
        const siswaField = document.querySelector('select[name="siswa_id"]').closest('.mb-2');
        const nameInput = document.querySelector('input[name="name"]');
        const nipInput = document.querySelector('input[name="nip"]');
        const emailInput = document.querySelector('input[name="email"]');
        const jenisKelaminSelect = document.querySelector('select[name="jenis_kelamin"]');
        const usernameInput = document.querySelector('input[name="username"]');
        const passwordInput = document.querySelector('input[name="password"]');
        const passwordHelpText = document.querySelector('#passwordHelpText');

        function updateUserRoleFields() {
            const roleText = roleSelect.options[roleSelect.selectedIndex]?.text || '';
            const isGuruRole = /Guru|Admin|Wakil Kepala Sekolah|Pengawas Pembina/i.test(roleText);
            const isKepalaRole = /Kepala Sekolah/i.test(roleText);
            const isSiswaRole = /Siswa/i.test(roleText);

            if (guruField) {
                guruField.style.display = isGuruRole || isKepalaRole ? '' : 'none';
                const guruSelectElement = guruField.querySelector('select[name="guru_id"]');
                if (guruSelectElement) {
                    guruSelectElement.required = isGuruRole || isKepalaRole;
                }
            }
            if (kepalaField) {
                kepalaField.style.display = isKepalaRole ? '' : 'none';
                const kepalaSelectElement = kepalaField.querySelector('select[name="kepala_sekolah_id"]');
                if (kepalaSelectElement) {
                    kepalaSelectElement.required = isKepalaRole;
                }
            }
            if (siswaField) {
                siswaField.style.display = isSiswaRole ? '' : 'none';
                const siswaSelectElement = siswaField.querySelector('select[name="siswa_id"]');
                if (siswaSelectElement) {
                    siswaSelectElement.required = isSiswaRole;
                }
            }
        }

        function updateGuruFields() {
            const selectedOption = guruSelect.selectedOptions[0];
            if (!selectedOption) {
                return;
            }

            const selectedName = selectedOption.dataset.name || '';
            const selectedNip = selectedOption.dataset.nip || '';
            const selectedEmail = selectedOption.dataset.email || '';
            const selectedJenisKelamin = selectedOption.dataset.jenisKelamin || selectedOption.dataset['jenis-kelamin'] || '';
            const selectedUsername = selectedOption.dataset.username || '';
            const selectedPassword = selectedOption.dataset.password || '';
            const hasUser = selectedOption.dataset.hasUser === '1';

            if (!selectedOption.value) {
                nameInput.value = '';
                nipInput.value = '';
                emailInput.value = '';
                jenisKelaminSelect.value = '';
                usernameInput.value = '';
                passwordInput.value = '';
                passwordInput.required = true;
                if (passwordHelpText) {
                    passwordHelpText.textContent = 'Isi password jika membuat akun baru atau ingin mengganti password.';
                }
                return;
            }

            if (selectedName) {
                nameInput.value = selectedName;
            }
            if (selectedNip) {
                nipInput.value = selectedNip;
            }
            if (selectedEmail) {
                emailInput.value = selectedEmail;
            }
            if (selectedJenisKelamin) {
                jenisKelaminSelect.value = selectedJenisKelamin;
            }
            if (selectedUsername) {
                usernameInput.value = selectedUsername;
            }

            if (hasUser) {
                passwordInput.required = false;
                if (passwordHelpText) {
                    passwordHelpText.textContent = 'Guru sudah memiliki akun. Kosongkan password untuk tetap memakai password lama, atau isi untuk menggantinya.';
                }
            } else {
                passwordInput.required = true;
                passwordInput.value = selectedPassword;
                if (passwordHelpText) {
                    passwordHelpText.textContent = 'Isi password untuk membuat akun baru.';
                }
            }
        }

        if (roleSelect) {
            roleSelect.addEventListener('change', updateUserRoleFields);
            updateUserRoleFields();
        }

        if (guruSelect) {
            guruSelect.addEventListener('change', updateGuruFields);
            updateGuruFields();
        }
    });
</script>
@endpush
@endsection
