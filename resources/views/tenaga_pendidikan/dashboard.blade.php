@extends('layouts.app')

@section('title','Dashboard Tenaga Pendidikan')

@section('content')
<div class="welcome-banner">
    <h3><i class="ti ti-users-group me-2"></i>Dashboard Tenaga Pendidikan</h3>
    <p>Selamat datang di dashboard Tenaga Pendidikan. Fitur ini sedang dalam tahap pengembangan.</p>
</div>

<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-body text-center py-5">
                <h5 class="card-title mb-3">Fitur Dalam Pengembangan</h5>
                <p class="text-muted">Dashboard Tenaga Pendidikan sedang disiapkan dengan berbagai fitur menarik. Silahkan kembali ke halaman utama atau hubungi administrator.</p>
                <div class="mt-4">
                    <a href="{{ route('home') }}" class="btn btn-primary">Kembali ke Halaman Utama</a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
