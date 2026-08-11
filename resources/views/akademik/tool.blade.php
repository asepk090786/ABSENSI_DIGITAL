@extends('layouts.app', ['pageSlug' => 'akademik_tool'])

@section('title', 'Tool Akademik')

@section('content')
@php
    $wopiUrl = $wopiUrl ?? '';
@endphp
<div class="card shadow-sm border-0">

    {{-- HEADER --}}
    <div
        class="card-header border-0 d-flex justify-content-between align-items-center"
        style="background: linear-gradient(115deg, #174780, #2d6ab6); color:#fff;"
    >
        <div>
            <h3 class="fw-semibold mb-1">Tool Akademik</h3>

            <p class="mb-0" style="font-size:12px; opacity:.9;">
                Editor dokumen kolaboratif Collabora Online
            </p>
        </div>

        <div class="d-flex gap-2">
            <a
                href="{{ route('akademik.tool') }}"
                class="btn btn-sm btn-light text-primary"
            >
                <i class="ti ti-refresh me-1"></i>
                Reset
            </a>
        </div>
    </div>

    {{-- COLLABORA EDITOR --}}
    <div class="card-body p-0">

        @if(empty($wopiUrl))
            <div class="alert alert-danger m-3">
                <strong>Error:</strong>
                WOPI URL tidak tersedia.
            </div>
        @else

            <iframe
                id="collabora-editor"
                src="{{ $wopiUrl }}"
                style="
                    width: 100%;
                    height: calc(100vh - 180px);
                    min-height: 700px;
                    border: none;
                    display: block;
                "
                allow="
                    fullscreen;
                    clipboard-read;
                    clipboard-write;
                    downloads
                "
                title="Collabora Online Editor">
            </iframe>

        @endif

    </div>

</div>
@endsection