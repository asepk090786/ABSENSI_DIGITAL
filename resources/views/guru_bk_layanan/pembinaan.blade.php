@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h3 class="card-title mb-0">Pembinaan BK - {{ $kelas->nama_kelas }}</h3>
                    <a href="{{ route('guru_bk_layanan.menu', ['kelas' => $kelas->id]) }}" class="btn btn-secondary btn-sm">
                        <i class="ti ti-arrow-left"></i> Kembali ke Menu
                    </a>
                </div>
                <div class="card-body">
                    <div class="alert alert-info mb-0">
                        <i class="ti ti-info-circle me-2"></i>Halaman pembinaan BK untuk kelas binaan <strong>{{ $kelas->nama_kelas }}</strong>.
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
