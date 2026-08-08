@extends('layouts.app', ['pageSlug' => 'modul_ajar'])

@section('title', 'Editor Modul')

@section('content')
<div class="card shadow-sm border-0">
    <div class="card-header border-0">
        <div class="d-flex justify-content-between align-items-start gap-3 flex-wrap">
            <div>
                <h3 class="fw-semibold mb-1">Editor Modul</h3>
                <p class="text-muted mb-0">Integrasi editor eksternal telah dinonaktifkan. Halaman ini hanya menampilkan placeholder sementara.</p>
            </div>
            <a href="{{ route('rencana_pembelajaran.index') }}" class="btn btn-outline-secondary btn-sm">
                <i class="ti ti-arrow-left me-1"></i>Kembali ke Daftar
            </a>
        </div>
    </div>
    <div class="card-body d-flex align-items-center justify-content-center" style="min-height:760px;">
        <div class="text-center">
            <i class="ti ti-block text-warning" style="font-size:48px;"></i>
            <h4 class="mt-3">Editor Modul Dinonaktifkan</h4>
            <p class="text-muted mb-0">Editor dokumen eksternal telah dihapus dari halaman ini.</p>
        </div>
    </div>
</div>
@endsection
