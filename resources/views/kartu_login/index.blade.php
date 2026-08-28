@extends('layouts.app')

@section('title', 'Kartu Login')

@section('content')
<div class="container-fluid kartu-login-page">
    <div class="page-header d-print-none mb-3">
        <div class="row align-items-center">
            <div class="col">
                <h2 class="page-title"><i class="ti ti-id-badge me-2"></i>Kartu Login</h2>
                <div class="text-muted">Pilih role dan kelompok pengguna untuk menyiapkan kartu login.</div>
            </div>
            <div class="col-auto">
                <button type="button" class="btn btn-primary" id="printLoginCards">
                    <i class="ti ti-printer me-1"></i>Cetak Kartu
                </button>
            </div>
        </div>
    </div>

    @if(!$personalOnly)
    <div class="card d-print-none mb-4">
        <div class="card-body">
            <div class="row g-3 align-items-end">
                <div class="col-md-5">
                    <label for="loginRole" class="form-label">Role</label>
                    <select id="loginRole" class="form-select">
                        <option value="">Pilih role</option>
                        @foreach($roles as $role)
                            <option value="{{ strtolower($role->role_name) }}">{{ $role->role_name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-5" id="classFilterWrapper" hidden>
                    <label for="loginClass" class="form-label">Kelas siswa</label>
                    <select id="loginClass" class="form-select">
                        <option value="">Semua kelas</option>
                        @foreach($kelas as $item)
                            <option value="{{ $item->id }}">{{ $item->nama_kelas }} ({{ $item->siswa_count }} siswa)</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <div class="small text-muted mb-2">Kartu terpilih</div>
                    <strong id="cardCount">0</strong>
                </div>
            </div>
        </div>
    </div>
    @endif

    <div id="emptyPreview" class="card d-print-none" @if($personalOnly) hidden @endif>
        <div class="card-body text-center py-5 text-muted">
            <i class="ti ti-adjustments-horizontal fs-1 d-block mb-2"></i>
            Pilih role untuk menampilkan preview kartu login.
        </div>
    </div>

    <div id="loginCardsPreview" class="login-cards-grid">
        @foreach($users as $user)
            @php
                $roleName = $user->role->role_name ?? '-';
                $studentClass = $user->siswa->kelas ?? null;
                $identityLabel = $user->siswa ? 'NISN' : 'NIP';
                $identityNumber = $user->siswa?->nisn ?: ($user->guru?->nip ?: ($user->nip ?: '-'));
                $fotoPath = $user->foto;
                $schoolName = $sekolah->nama_sekolah ?? 'SMAN 1 Pontang';
                $schoolLeftLogo = $sekolah->logo_header_kiri ?: null;
                $schoolLeftLogoUrl = $schoolLeftLogo
                    ? (str_starts_with($schoolLeftLogo, 'http') ? $schoolLeftLogo : asset('storage/' . ltrim($schoolLeftLogo, '/')))
                    : asset('images/logo_depan.png');
                $schoolLogo = $sekolah->logo_kanan ?: ($sekolah->logo ?: null);
                $schoolLogoUrl = $schoolLogo
                    ? (str_starts_with($schoolLogo, 'http') ? $schoolLogo : asset('storage/' . ltrim($schoolLogo, '/')))
                    : asset('images/logo_depan.png');
                $fotoUrl = $fotoPath && \Storage::disk('public')->exists($fotoPath)
                    ? asset('storage/' . $fotoPath)
                    : (($user->jenis_kelamin ?? 'L') === 'P'
                        ? asset('images/default-avatar-female.svg')
                        : asset('images/default-avatar-male.svg'));
            @endphp
            <article class="login-card" data-role="{{ strtolower($roleName) }}" data-class="{{ $studentClass?->id ?? '' }}">
                <div class="login-card-accent"></div>
                <div class="login-card-body">
                    <header class="login-card-header">
                        <div class="login-card-branding">
                            <img class="login-card-school-logo login-card-school-logo-left" src="{{ $schoolLeftLogoUrl }}" alt="Logo kiri {{ $schoolName }}">
                            <div>
                                <div class="login-card-school-name-top">{{ $schoolName }}</div>
                                <div class="login-card-brand">SIMADIS</div>
                                <div class="login-card-label">KARTU LOGIN</div>
                            </div>
                        </div>
                        <img class="login-card-school-logo" src="{{ $schoolLogoUrl }}" alt="Logo {{ $schoolName }}">
                    </header>
                    <section class="login-card-profile">
                        <div class="login-card-avatar">
                            <img src="{{ $fotoUrl }}" alt="Foto {{ $user->name }}">
                        </div>
                        <div class="login-card-identity">
                            <h3>{{ $user->name }}</h3>
                            <div class="login-card-identifier"><span>{{ $identityLabel }}</span>{{ $identityNumber }}</div>
                            <div class="login-card-meta">
                                <span class="badge bg-blue-lt"><i class="ti ti-school me-1"></i>{{ $roleName }}</span>
                                @if($studentClass)
                                    <span class="login-card-class">{{ $studentClass->nama_kelas }}</span>
                                @endif
                            </div>
                        </div>
                    </section>
                    <section class="login-card-qr">
                        <div class="login-card-qr-image">
                            <img src="{{ $user->login_qr }}" alt="QR login SIMADIS">
                        </div>
                        <div class="login-card-qr-copy">
                            <div class="login-card-scan-icon"><i class="ti ti-scan"></i></div>
                            <h4>Scan QR Code untuk Login</h4>
                            <p>Gunakan kamera untuk masuk ke aplikasi SIMADIS.</p>
                        </div>
                    </section>
                    <footer class="login-card-footer">
                        <div class="login-card-security-icon"><i class="ti ti-shield-check"></i></div>
                        <div><strong>Simpan kartu login ini dengan baik</strong><span>Jangan membagikan QR Code kepada siapa pun.</span></div>
                    </footer>
                </div>
            </article>
        @endforeach
    </div>
</div>
@endsection

@push('css')
<style>
    .login-cards-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(70mm, 1fr)); gap: 1.5rem; align-items: start; }
    .login-card { display: none; overflow: hidden; width: 80mm; height: 105mm; min-height: 105mm; background: #fff; border: 1px solid #e2e8f0; border-radius: 24px; box-shadow: 0 18px 45px rgba(15, 23, 42, .11), 0 3px 10px rgba(37, 99, 235, .05); }
    .login-card-accent { height: 2.5mm; background: linear-gradient(100deg, #2563eb 0%, #3b82f6 48%, #10b981 100%); }
    .login-card-body { display: flex; flex-direction: column; height: calc(105mm - 2.5mm); padding: 2mm; }
    .login-card-header { display: flex; align-items: flex-start; justify-content: space-between; margin-bottom: 1mm; }
    .login-card-branding { display: flex; align-items: flex-start; gap: 2mm; min-width: 0; }
    .login-card-school-logo { display: block; width: 12mm; height: 12mm; object-fit: contain; }
    .login-card-school-logo-left { flex: 0 0 12mm; }
    .login-card-brand { color: #2563eb; font-size: 18px; line-height: 1; font-weight: 800; letter-spacing: .02em; }
    .login-card-school-name-top { max-width: 38mm; margin-bottom: .7mm; color: #64748b; font-size: 11px; line-height: 1.15; font-weight: 700; letter-spacing: .02em; text-transform: uppercase; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
    .login-card-label { margin-top: .25rem; color: #64748b; font-size: 8px; font-weight: 600; letter-spacing: .18em; }
    .login-card-id-icon { display: flex; align-items: center; justify-content: center; width: 30px; height: 30px; color: #10b981; background: #ecfdf5; border: 1px solid #a7f3d0; border-radius: 9px; font-size: 1.15rem; }
    .login-card-profile { display: flex; flex-direction: column; align-items: center; gap: .5mm; min-width: 0; margin: 0 0 1mm; padding: 1mm; background: #f8fafc; border: 1px solid #eef2f7; border-radius: 12px; text-align: center; }
    .login-card-avatar { flex: 0 0 30mm; width: 20mm; height: 30mm; padding: 2px; background: #dbeafe; border-radius: 6px; }
    .login-card-profile img { display: block; width: 100%; height: 100%; object-fit: cover; border: 2px solid #fff; border-radius: 4px; }
    .login-card-identity { min-width: 0; width: 100%; }
    .login-card h3 { margin: 0 0 .25rem; color: #0f172a; font-size: .95rem; line-height: 1.2; font-weight: 800; overflow-wrap: anywhere; }
    .login-card-identifier { margin-bottom: .45rem; color: #0f172a; font-size: .62rem; font-weight: 700; overflow-wrap: anywhere; }
    .login-card-identifier span { margin-right: .35rem; color: #64748b; font-size: .55rem; font-weight: 600; letter-spacing: .04em; }
    .login-card-meta { display: flex; align-items: center; justify-content: center; flex-wrap: wrap; gap: .35rem; min-height: 20px; font-size: .65rem; }
    .login-card-meta .badge.bg-blue-lt { display: inline-flex; align-items: center; color: #1e40af !important; background: #dbeafe !important; border-radius: 8px; font-weight: 700; }
    .login-card-class { color: #64748b; font-size: .65rem; }
    .login-card-qr { display: flex; align-items: center; gap: 2mm; min-width: 0; margin: 0 0 1mm; padding: 1mm; background: linear-gradient(135deg, #f8fbff 0%, #eff6ff 100%); border: 1px solid #dbeafe; border-radius: 12px; }
    .login-card-qr-image { flex: 0 0 22mm; width: 22mm; height: 22mm; padding: 1mm; background: #fff; border: 1px solid #e2e8f0; border-radius: 8px; box-shadow: 0 5px 15px rgba(37, 99, 235, .08); }
    .login-card-qr img { display: block; width: 100%; height: 100%; }
    .login-card-qr-copy { min-width: 0; }
    .login-card-scan-icon { display: flex; align-items: center; justify-content: center; width: 26px; height: 26px; margin-bottom: .35rem; color: #2563eb; background: #dbeafe; border-radius: 50%; font-size: .9rem; }
    .login-card-qr-copy h4 { margin: 0 0 .25rem; color: #0f172a; font-size: .72rem; line-height: 1.25; font-weight: 800; }
    .login-card-qr-copy p { margin: 0; color: #64748b; font-size: .58rem; line-height: 1.35; }
    .login-card-school-name { margin: 0 0 2mm; color: #0f172a; font-size: .62rem; font-weight: 800; line-height: 1.2; text-align: center; overflow-wrap: anywhere; }
    .login-card-footer { display: flex; align-items: center; gap: 1.5mm; margin-top: auto; padding: 1mm 1.5mm; color: #1e40af; background: #eff6ff; border-radius: 10px; font-size: .5rem; }
    .login-card-footer strong, .login-card-footer span { display: block; }
    .login-card-footer strong { font-size: .52rem; font-weight: 800; }
    .login-card-footer span { margin-top: .12rem; color: #64748b; }
    .login-card-security-icon { display: flex; align-items: center; justify-content: center; flex: 0 0 6mm; width: 6mm; height: 6mm; color: #fff; background: #2563eb; border-radius: 7px; font-size: .8rem; }
    @media screen and (min-width: 576px) {
        .login-cards-grid { grid-template-columns: repeat(auto-fill, minmax(80mm, 1fr)); }
        .login-card { zoom: 1.22; }
    }
    @media print {
        @page { size: A4 portrait; margin: 10mm; }
        body { background: #fff !important; }
        body * { visibility: hidden !important; }
        #loginCardsPreview,
        #loginCardsPreview * { visibility: visible !important; }
        #loginCardsPreview { position: absolute; top: 0; left: 0; display: grid; width: auto; margin: 0; }
        .login-cards-grid { grid-template-columns: repeat(2, 80mm); gap: 8mm; }
        .login-card { width: 80mm; height: 105mm; min-height: 105mm; zoom: 1 !important; break-inside: avoid; box-shadow: none; }
        .login-card-body { height: calc(105mm - 2.5mm); padding: 2mm; }
        .login-card-accent { -webkit-print-color-adjust: exact; print-color-adjust: exact; }
    }
    @media (max-width: 575px) {
        .login-cards-grid { grid-template-columns: 1fr; gap: 1rem; }
        .login-card { width: 100%; height: auto; min-height: 105mm; }
        .login-card-body { display: block; height: auto; }
        .login-card-header { margin-bottom: 2mm; }
        .login-card-profile { margin-bottom: 2mm; }
        .login-card-qr { margin-bottom: 2mm; }
    }
</style>
@endpush

@push('js')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const roleSelect = document.getElementById('loginRole');
    const classSelect = document.getElementById('loginClass');
    const classWrapper = document.getElementById('classFilterWrapper');
    const personalOnly = @json($personalOnly);
    const cards = Array.from(document.querySelectorAll('.login-card'));
    const count = document.getElementById('cardCount');
    const emptyPreview = document.getElementById('emptyPreview');

    function updatePreview() {
        if (personalOnly) {
            cards.forEach(function (card) { card.style.display = 'block'; });
            if (count) count.textContent = cards.length;
            emptyPreview.hidden = true;
            return;
        }

        const role = roleSelect.value;
        const classId = classSelect.value;
        const isStudent = role === 'siswa';
        let visibleCards = 0;

        classWrapper.hidden = !isStudent;
        if (!isStudent) classSelect.value = '';

        cards.forEach(function (card) {
            const visible = role !== '' && card.dataset.role === role && (!isStudent || classId === '' || card.dataset.class === classId);
            card.style.display = visible ? 'block' : 'none';
            if (visible) visibleCards++;
        });

        count.textContent = visibleCards;
        emptyPreview.hidden = visibleCards > 0;
    }

    if (roleSelect) roleSelect.addEventListener('change', updatePreview);
    if (classSelect) classSelect.addEventListener('change', updatePreview);
    document.getElementById('printLoginCards').addEventListener('click', function () {
        if ((count ? Number(count.textContent) : cards.length) === 0) {
            alert('Pilih role yang memiliki akun untuk dicetak.');
            return;
        }
        window.print();
    });
    updatePreview();
});
</script>
@endpush
