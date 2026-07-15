@extends('layouts.app', ['pageSlug' => 'jadwal-kbm'])

@section('title','Info Jadwal')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header border-0 pt-3 pb-2">
                <h4 class="card-title fw-semibold m-0">Jadwal Dalam Proses Perbaikan</h4>
            </div>
            <div class="card-body">
                <div class="alert alert-warning">
                    <h5><i class="ti ti-alert-circle me-2"></i>Jadwal tidak tersedia</h5>
                    <p>Jadwal mengajar saat ini sedang dalam proses perbaikan. Silakan cek kembali nanti atau hubungi administrator untuk informasi lebih lanjut.</p>
                    <p class="mb-0"><strong>Guru:</strong> {{ $guru->nama }}</p>
                </div>
                <a href="{{ route('home') }}" class="btn btn-secondary btn-sm">
                    <i class="ti ti-arrow-left me-1"></i> Kembali ke Dashboard
                </a>
            </div>
        </div>
    </div>
</div>
@endsection
