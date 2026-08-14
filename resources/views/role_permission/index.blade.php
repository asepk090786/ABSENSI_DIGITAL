@extends('layouts.app')

@section('title','Role & Permission')

@section('content')
<div class="container-fluid">
    <div class="page-header d-print-none">
        <div class="row align-items-center justify-content-between">
            <div class="col">
                <h2 class="page-title">
                    <i class="ti ti-shield me-2"></i>Role & Permission
                </h2>
            </div>
        </div>
    </div>
</div>

<div class="page-wrapper">
    <div class="container-xl">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <div class="alert alert-info">
                            <i class="ti ti-info-circle me-2"></i>
                            <strong>Manajemen Role & Permission</strong> sedang dalam tahap pengembangan.
                            Fitur ini akan digunakan untuk mengelola role, permission, dan hak akses pengguna dalam sistem.
                        </div>
                        
                        <div class="mt-4">
                            <p><strong>Sistem yang Tersedia:</strong></p>
                            <ul>
                                <li>Role-based Access Control (RBAC) melalui middleware</li>
                                <li>Role: Admin, Kepala Sekolah, Guru, Siswa, Tenaga Pendidikan, dan lainnya</li>
                                <li>User dapat memiliki satu atau lebih role</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
