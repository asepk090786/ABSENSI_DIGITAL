@extends('layouts.app', ['pageSlug' => 'dashboard-admin'])

@section('title','Dashboard Admin')

@section('content')
<div class="alert alert-info">Selamat datang di Dashboard <b>Admin</b>. Gunakan menu di samping untuk mengelola data master, pengguna, dan pengaturan sistem.</div>
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-1">Menu Cepat</h5>
            </div>
            <div class="card-body">
                <div class="row g-2">
                    <div class="col-6 col-md-4 col-lg-3">
                        <a class="btn btn-outline-primary w-100" href="{{ route('sekolah.index') }}">
                            <i class="ti ti-building-bank me-2"></i>Data Sekolah
                        </a>
                    </div>
                    <div class="col-6 col-md-4 col-lg-3">
                        <a class="btn btn-outline-primary w-100" href="{{ route('guru.index') }}">
                            <i class="ti ti-users me-2"></i>Guru
                        </a>
                    </div>
                    <div class="col-6 col-md-4 col-lg-3">
                        <a class="btn btn-outline-primary w-100" href="{{ route('siswa.index') }}">
                            <i class="ti ti-school me-2"></i>Siswa
                        </a>
                    </div>
                    <div class="col-6 col-md-4 col-lg-3">
                        <a class="btn btn-outline-primary w-100" href="{{ route('kelas.index') }}">
                            <i class="ti ti-building me-2"></i>Kelas
                        </a>
                    </div>
                    <div class="col-6 col-md-4 col-lg-3">
                        <a class="btn btn-outline-primary w-100" href="{{ route('mata_pelajaran.index') }}">
                            <i class="ti ti-books me-2"></i>Mata Pelajaran
                        </a>
                    </div>
                    <div class="col-6 col-md-4 col-lg-3">
                        <a class="btn btn-outline-primary w-100" href="{{ route('jam_belajar.index') }}">
                            <i class="ti ti-clock me-2"></i>Jam Belajar
                        </a>
                    </div>
                    <div class="col-6 col-md-4 col-lg-3">
                        <a class="btn btn-outline-primary w-100" href="{{ route('users.index') }}">
                            <i class="ti ti-lock me-2"></i>Akun Pengguna
                        </a>
                    </div>
                    <div class="col-6 col-md-4 col-lg-3">
                        <a class="btn btn-outline-primary w-100" href="{{ route('tahun_ajaran.index') }}">
                            <i class="ti ti-settings me-2"></i>Pengaturan
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
