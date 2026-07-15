@extends('layouts.app')

@section('title', 'Update Sistem')

@section('content')
<div class="page-header d-print-none mb-4">
    <div class="row align-items-center">
        <div class="col">
            <h2 class="page-title">Update Program &amp; Database</h2>
            <div class="text-muted">Fitur dimatikan sementara karena program masih dalam tahap pengembangan.</div>
        </div>
    </div>
</div>

@if(session('error'))
    <div class="alert alert-warning">{{ session('error') }}</div>
@endif

<div class="card">
    <div class="card-body">
        <div class="alert alert-info mb-2">
            Fitur update otomatis dari GitHub saat ini dinonaktifkan untuk menjaga stabilitas selama pengembangan.
        </div>
        <button class="btn btn-secondary" type="button" disabled>
            <i class="ti ti-cloud-off me-2"></i>Update Dinonaktifkan
        </button>
    </div>
</div>
@endsection
