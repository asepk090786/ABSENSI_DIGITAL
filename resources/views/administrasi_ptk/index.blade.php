@extends('layouts.app')

@section('title','Administrasi PTK')

@section('content')
<div class="container-fluid">
    <div class="page-header d-print-none">
        <div class="row align-items-center justify-content-between">
            <div class="col">
                <h2 class="page-title">
                    <i class="ti ti-folder me-2"></i>Administrasi PTK
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
                    <div class="card-body text-center py-5">
                        <i class="ti ti-folder" style="font-size: 3rem; color: #999;"></i>
                        <p class="mt-3 text-muted">
                            <strong>Administrasi PTK (Pendidik dan Tenaga Kependidikan)</strong><br>
                            Halaman ini merupakan central hub untuk mengelola segala aspek administratif PTK.
                        </p>
                        <div class="mt-4">
                            <p>Menu yang tersedia:</p>
                            <ul class="list-unstyled">
                                <li><a href="{{ route('dokumen_kepegawaian.index') }}" class="btn btn-outline-primary btn-sm">Dokumen Kepegawaian</a></li>
                                <li><a href="{{ route('template_dokumen.index') }}" class="btn btn-outline-primary btn-sm mt-2">Template Dokumen</a></li>
                                <li><a href="{{ route('pengembangan.index') }}" class="btn btn-outline-primary btn-sm mt-2">Pengembangan Diri</a></li>
                                <li><a href="{{ route('pengajuan.index') }}" class="btn btn-outline-primary btn-sm mt-2">Pengajuan</a></li>
                                <li><a href="{{ route('verifikasi.index') }}" class="btn btn-outline-primary btn-sm mt-2">Verifikasi</a></li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
