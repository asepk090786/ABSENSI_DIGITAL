@extends('layouts.app', ['pageSlug' => 'wali-kelas'])

@section('title', 'Dashboard Wali Kelas')

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-12">
            <div class="card bg-primary text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h4 class="card-title text-white mb-2">
                                <i class="ti ti-school me-2"></i>Dashboard Wali Kelas
                            </h4>
                            <p class="mb-0">Kelola data kelas binaan Anda</p>
                        </div>
                        <div class="text-end">
                            <h3 class="mb-0">{{ $kelasBinaan->nama_kelas ?? '-' }}</h3>
                            <small>Kelas Binaan</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="ti ti-check-circle me-2"></i>{{ session('success') }}
            <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="ti ti-alert-circle me-2"></i>{{ session('error') }}
            <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        </div>
    @endif

    <div class="row">
        <!-- Card Info Kelas -->
        <div class="col-md-3 mb-4">
            <div class="card border-primary">
                <div class="card-body text-center">
                    <div class="mb-3">
                        <i class="ti ti-users" style="font-size: 48px; color: #3b82f6;"></i>
                    </div>
                    <h3 class="mb-0">{{ $jumlahSiswa }}</h3>
                    <p class="text-muted mb-0">Jumlah Siswa</p>
                </div>
            </div>
        </div>

        <!-- Card Tahun Ajaran -->
        <div class="col-md-3 mb-4">
            <div class="card border-success">
                <div class="card-body text-center">
                    <div class="mb-3">
                        <i class="ti ti-calendar" style="font-size: 48px; color: #10b981;"></i>
                    </div>
                    <h5 class="mb-0">{{ $tahunAjaran->nama_tahun ?? '-' }}</h5>
                    <p class="text-muted mb-0">Tahun Ajaran</p>
                </div>
            </div>
        </div>

        <!-- Card Semester -->
        <div class="col-md-3 mb-4">
            <div class="card border-warning">
                <div class="card-body text-center">
                    <div class="mb-3">
                        <i class="ti ti-book" style="font-size: 48px; color: #f59e0b;"></i>
                    </div>
                    <h5 class="mb-0">Semester {{ $semester->nama_semester ?? '-' }}</h5>
                    <p class="text-muted mb-0">Semester Aktif</p>
                </div>
            </div>
        </div>

        <!-- Card Wali Kelas -->
        <div class="col-md-3 mb-4">
            <div class="card border-info">
                <div class="card-body text-center">
                    <div class="mb-3">
                        <i class="ti ti-user-check" style="font-size: 48px; color: #06b6d4;"></i>
                    </div>
                    <h6 class="mb-0">{{ $guru->nama ?? '-' }}</h6>
                    <p class="text-muted mb-0">Wali Kelas</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Menu Quick Access -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header bg-light">
                    <h5 class="card-title mb-0">
                        <i class="ti ti-layout-grid me-2"></i>Menu Cepat
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6 col-lg-3">
                            <a href="{{ route('wali_kelas.siswa') }}" class="text-decoration-none">
                                <div class="card border-primary h-100 hover-card">
                                    <div class="card-body text-center">
                                        <i class="ti ti-users" style="font-size: 48px; color: #3b82f6;"></i>
                                        <h6 class="mt-3 mb-0">Data Siswa</h6>
                                        <small class="text-muted">Kelola data siswa kelas binaan</small>
                                    </div>
                                </div>
                            </a>
                        </div>

                        <div class="col-md-6 col-lg-3">
                            <a href="{{ route('wali_kelas.absensi') }}" class="text-decoration-none">
                                <div class="card border-success h-100 hover-card">
                                    <div class="card-body text-center">
                                        <i class="ti ti-calendar-check" style="font-size: 48px; color: #10b981;"></i>
                                        <h6 class="mt-3 mb-0">Absensi Kelas</h6>
                                        <small class="text-muted">Lihat rekap absensi siswa</small>
                                    </div>
                                </div>
                            </a>
                        </div>

                        <div class="col-md-6 col-lg-3">
                            <a href="{{ route('wali_kelas.nilai') }}" class="text-decoration-none">
                                <div class="card border-warning h-100 hover-card">
                                    <div class="card-body text-center">
                                        <i class="ti ti-chart-bar" style="font-size: 48px; color: #f59e0b;"></i>
                                        <h6 class="mt-3 mb-0">Nilai Siswa</h6>
                                        <small class="text-muted">Lihat rekap nilai siswa</small>
                                    </div>
                                </div>
                            </a>
                        </div>

                        <div class="col-md-6 col-lg-3">
                            <a href="{{ route('agenda_kelas.index') }}" class="text-decoration-none">
                                <div class="card border-info h-100 hover-card">
                                    <div class="card-body text-center">
                                        <i class="ti ti-book" style="font-size: 48px; color: #06b6d4;"></i>
                                        <h6 class="mt-3 mb-0">Agenda Kelas</h6>
                                        <small class="text-muted">Lihat agenda pembelajaran</small>
                                    </div>
                                </div>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .hover-card {
        transition: all 0.3s ease;
        cursor: pointer;
    }

    .hover-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15) !important;
    }
</style>
@endsection
