@extends('layouts.app')

@section('title', 'Help Center')

@section('content')
<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-lg-10 col-xl-9">
            <div class="card mb-3">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h3 class="card-title fw-semibold m-0">Help Center</h3>
                </div>
                <div class="card-body">
                    @if(empty($items))
                        <div class="alert alert-info">
                            <i class="ti ti-info-circle me-2"></i>Belum ada halaman help tersedia.
                        </div>
                    @else
                        <div class="row g-3">
                            @foreach($items as $item)
                                <div class="col-md-6 col-lg-4">
                                    <a href="{{ url('/help/' . $item['slug']) }}" class="text-decoration-none">
                                        <div class="card h-100 border">
                                            <div class="card-body">
                                                <h5 class="fw-semibold text-dark">{{ $item['title'] }}</h5>
                                                <p class="text-muted small mb-0">
                                                    @if(!empty($item['video_link']))
                                                        <span class="badge bg-danger me-1">Video</span>
                                                    @endif
                                                    Halaman bantuan
                                                </p>
                                            </div>
                                        </div>
                                    </a>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
