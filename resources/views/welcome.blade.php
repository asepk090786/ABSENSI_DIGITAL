@extends('layouts.app')

@section('title', 'Selamat Datang')

@section('content')
<div class="welcome-banner text-center">
    <h3>Selamat Datang di SIMADIS</h3>
    <p>Sistem Manajemen Absensi Digital — SMAN 1 Pontang</p>
</div>

<div class="row g-3">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title"><i class="ti ti-info-circle me-2 text-primary"></i>Tentang Sistem</h5>
            </div>
            <div class="card-body">
                <p>SIMADIS adalah sistem informasi manajemen absensi digital terintegrasi untuk mengelola data kehadiran siswa, guru, jadwal KBM, agenda pembelajaran, dan penilaian.</p>
                <p class="mb-0 text-muted small">Gunakan menu navigasi untuk mulai mengelola data.</p>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title"><i class="ti ti-bolt me-2 text-accent"></i>Akses Cepat</h5>
            </div>
            <div class="card-body">
                <div class="row g-2">
                    <div class="col-6">
                        <a href="{{ route('absensi.index') }}" class="quick-menu-card">
                            <div class="qm-icon bg-primary"><i class="ti ti-clipboard-check"></i></div>
                            <div class="qm-label">Absensi</div>
                        </a>
                    </div>
                    <div class="col-6">
                        <a href="{{ route('jadwal_kbm.index') }}" class="quick-menu-card">
                            <div class="qm-icon bg-accent"><i class="ti ti-calendar-time"></i></div>
                            <div class="qm-label">Jadwal KBM</div>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection