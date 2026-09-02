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
            <div>
                <h3 class="m-0">Hasil Generate <span class="badge bg-blue-lt">{{ $generatedUsers->count() }} akun</span></h3>
                <small class="text-success"><i class="ti ti-check me-1"></i>Token QR tersimpan di database. Berlaku hingga {{ now()->addYear()->format('d M Y') }}</small>
                @if(isset($newTokenCount) && isset($reusedTokenCount))
                    @if($newTokenCount > 0 && $reusedTokenCount > 0)
                        <div class="small text-muted mt-1">
                            <i class="ti ti-info-circle"></i>
                            {{ $newTokenCount }} akun baru dengan token baru · {{ $reusedTokenCount }} akun dengan token yang sudah ada
                        </div>
                    @elseif($reusedTokenCount > 0)
                        <div class="small text-muted mt-1">
                            <i class="ti ti-info-circle"></i>
                            Semua akun menggunakan token yang sudah ada (tidak ada token baru dibuat)
                        </div>
                    @endif
                @endif
            </div>
            <div class="btn-group d-print-none" role="group">
                <a href="{{ route('kartu_login.index') }}?role={{ $validated['role'] ?? '' }}" class="btn btn-outline-info" title="Lihat di halaman Kartu Login">
                    <i class="ti ti-eye me-1"></i>Lihat di Kartu Login
                </a>
                <button type="button" class="btn btn-outline-secondary" onclick="window.print()">
                    <i class="ti ti-printer me-1"></i>Cetak QR
                </button>
            </div>
        </div>
        @if($generatedUsers->isEmpty())
            <div class="alert alert-warning">Tidak ada akun yang sesuai dengan pilihan.</div>
        @else
            <div class="alert alert-info d-print-none">
                <i class="ti ti-info-circle me-2"></i>
                <strong>QR Token Reusable:</strong> QR Code dapat di-scan berkali-kali selama belum expired (1 tahun ajaran). Tidak perlu generate ulang untuk setiap penggunaan. Token baru hanya dibuat untuk akun yang belum memiliki token.
            </div>
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
    const form = document.getElementById('generateQrForm');
    const submitBtn = form.querySelector('button[type="submit"]');
    
    // Toggle kelas field visibility dan auto-submit saat role berubah
    role.addEventListener('change', function () {
        const isStudent = role.value === 'Siswa';
        classField.hidden = !isStudent;
        classSelect.required = isStudent;
        if (!isStudent) classSelect.value = '';
        
        // Auto-submit form setelah role berubah
        if (role.value) {
            setTimeout(() => {
                submitBtn.click();
            }, 300);
        }
    });
    
    // Auto-submit saat kelas dipilih jika role adalah Siswa
    classSelect.addEventListener('change', function () {
        if (role.value === 'Siswa' && this.value) {
            setTimeout(() => {
                submitBtn.click();
            }, 300);
        }
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
