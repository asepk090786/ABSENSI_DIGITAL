@extends('layouts.app')

@section('title', $pageTitle ?? 'Help')

@section('content')
<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-lg-10 col-xl-9">
            <div class="card mb-3">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h3 class="card-title fw-semibold m-0">Help</h3>
                    <a href="{{ url()->previous() }}" class="btn btn-secondary btn-sm">Kembali</a>
                </div>
                <div class="card-body">
                    <h4 class="fw-bold">{{ $pageTitle ?? 'Halaman Help' }}</h4>
                    <hr>
                    @if(!empty($video_link))
                        @php
                            $videoEmbedUrl = null;
                            $videoHost = null;

                            if (str_contains($video_link, 'youtube.com/watch') || str_contains($video_link, 'youtu.be/')) {
                                preg_match('/[?&]v=([^&]+)/', $video_link, $matches);
                                if (!empty($matches[1])) {
                                    $videoEmbedUrl = 'https://www.youtube.com/embed/' . $matches[1];
                                    $videoHost = 'youtube';
                                }
                            } elseif (str_contains($video_link, 'vimeo.com/')) {
                                preg_match('#vimeo\.com/(\d+)#', $video_link, $matches);
                                if (!empty($matches[1])) {
                                    $videoEmbedUrl = 'https://player.vimeo.com/video/' . $matches[1];
                                    $videoHost = 'vimeo';
                                }
                            }

                            if (!$videoEmbedUrl && filter_var($video_link, FILTER_VALIDATE_URL)) {
                                $videoEmbedUrl = $video_link;
                                $videoHost = 'generic';
                            }
                        @endphp

                        @if($videoEmbedUrl)
                            <div class="ratio ratio-16x9 mb-4">
                                <iframe src="{{ $videoEmbedUrl }}" allowfullscreen allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"></iframe>
                            </div>
                        @else
                            <div class="alert alert-warning">
                                Link video tidak dikenali. <a href="{{ $video_link }}" target="_blank">Buka link</a>
                            </div>
                        @endif
                    @endif

                    <div class="help-content">
                        {!! $content ?? '<p class="text-muted">Konten belum tersedia.</p>' !!}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
