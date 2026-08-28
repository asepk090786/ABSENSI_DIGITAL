@extends('layouts.app')

@section('title', 'Generate Login QR')

@section('content')
<div class="container-fluid qr-generator-page">
    <div class="page-header d-print-none mb-4">
        <div class="row align-items-center">
            <div class="col">
                <h2 class="page-title"><i class="ti ti-qrcode me-2"></i>Generate Login QR</h2>
                <div class="text-muted">Buat link dan QR code login otomatis untuk pengguna SIMADIS.</div>
            </div>
            <div class="col-auto">
                <a href="{{ route('kartu_login.index') }}" class="btn btn-outline-primary"><i class="ti ti-id-badge me-1"></i>Kartu Login</a>
            </div>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success d-print-none"><i class="ti ti-check me-2"></i>{{ session('success') }}</div>
    @endif

    <div class="card d-print-none mb-4">
        <div class="card-header"><h3 class="card-title">Pengaturan QR Login</h3></div>
        <div class="card-body">
            <form method="POST" action="{{ route('kartu_login.generate.submit') }}" id="generateQrForm">
                @csrf
                <div class="row g-3 align-items-end">
                    <div class="col-md-5">
                        <label class="form-label" for="role">Role pengguna</label>
                        <select class="form-select @error('role') is-invalid @enderror" name="role" id="role" required>
                            <option value="">Pilih role</option>
                            @foreach($roles as $role)
                                <option value="{{ $role->role_name }}" @selected(old('role', $validated['role'] ?? '') === $role->role_name)>{{ $role->role_name }}</option>
                            @endforeach
                        </select>
                        @error('role')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-5" id="classField" @if(old('role', $validated['role'] ?? '') !== 'Siswa') hidden @endif>
                        <label class="form-label" for="kelas_id">Kelas siswa</label>
                        <select class="form-select @error('kelas_id') is-invalid @enderror" name="kelas_id" id="kelas_id">
                            <option value="">Semua kelas</option>
                            @foreach($kelas as $item)
                                <option value="{{ $item->id }}" @selected((string) old('kelas_id', $validated['kelas_id'] ?? '') === (string) $item->id)>{{ $item->nama_kelas }} ({{ $item->siswa_count }} siswa)</option>
                            @endforeach
                        </select>
                        @error('kelas_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-2">
                        <button class="btn btn-primary w-100" type="submit"><i class="ti ti-qrcode me-1"></i>Generate</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    @isset($generatedUsers)
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h3 class="m-0">Hasil Generate <span class="badge bg-blue-lt">{{ $generatedUsers->count() }} akun</span></h3>
            <button type="button" class="btn btn-outline-secondary d-print-none" onclick="window.print()"><i class="ti ti-printer me-1"></i>Cetak QR</button>
        </div>
        @if($generatedUsers->isEmpty())
            <div class="alert alert-warning">Tidak ada akun yang sesuai dengan pilihan.</div>
        @else
            <div class="qr-results-grid">
                @foreach($generatedUsers as $user)
                    <article class="qr-result-card">
                        <img class="qr-result-image" src="{{ $user->login_qr }}" alt="QR login {{ $user->name }}">
                        <div class="flex-fill min-width-0">
                            <h4>{{ $user->name }}</h4>
                            <div class="text-muted small">{{ $user->role->role_name ?? '-' }}@if($user->siswa?->kelas) · {{ $user->siswa->kelas->nama_kelas }}@endif</div>
                            <div class="input-group input-group-sm mt-2">
                                <input class="form-control" value="{{ $user->login_qr_url }}" readonly>
                                <button class="btn btn-outline-secondary copy-qr-link" type="button" data-link="{{ $user->login_qr_url }}" title="Salin link"><i class="ti ti-copy"></i></button>
                            </div>
                        </div>
                    </article>
                @endforeach
            </div>
        @endif
    @else
        <div class="card d-print-none">
            <div class="card-body text-center text-muted py-5"><i class="ti ti-qrcode fs-1 d-block mb-2"></i>Pilih role atau kelas, kemudian tekan Generate.</div>
        </div>
    @endisset
</div>
@endsection

@push('css')
<style>
    .qr-results-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(360px, 1fr)); gap: 1rem; }
    .qr-result-card { display: flex; align-items: center; gap: 1rem; padding: 1rem; background: #fff; border: 1px solid #dbe4f0; border-radius: 8px; }
    .qr-result-image { width: 112px; height: 112px; flex: 0 0 112px; }
    .qr-result-card h4 { margin: 0; font-size: .95rem; }
    .min-width-0 { min-width: 0; }
    @media print { @page { size: A4; margin: 10mm; } .qr-results-grid { grid-template-columns: repeat(2, 1fr); gap: 8mm; } .qr-result-card { break-inside: avoid; box-shadow: none; } .qr-result-image { width: 35mm; height: 35mm; } }
    @media (max-width: 575px) { .qr-results-grid { grid-template-columns: 1fr; } }
</style>
@endpush

@push('js')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const role = document.getElementById('role');
    const classField = document.getElementById('classField');
    const classSelect = document.getElementById('kelas_id');
    role.addEventListener('change', function () {
        const isStudent = role.value === 'Siswa';
        classField.hidden = !isStudent;
        classSelect.required = isStudent;
        if (!isStudent) classSelect.value = '';
    });
    document.querySelectorAll('.copy-qr-link').forEach(function (button) {
        button.addEventListener('click', function () {
            navigator.clipboard.writeText(button.dataset.link).then(function () {
                button.innerHTML = '<i class="ti ti-check"></i>';
                setTimeout(function () { button.innerHTML = '<i class="ti ti-copy"></i>'; }, 1500);
            });
        });
    });
});
</script>
@endpush
