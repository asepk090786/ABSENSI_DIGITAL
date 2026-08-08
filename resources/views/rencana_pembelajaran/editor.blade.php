@extends('layouts.app', ['pageSlug' => 'modul_ajar'])

@section('title', 'Editor Modul')

@section('content')
<div class="card shadow-sm border-0">
    <div class="card-header border-0">
        <div class="d-flex justify-content-between align-items-start gap-3 flex-wrap">
            <div>
                <h3 class="fw-semibold mb-1">Editor Modul</h3>
                <p class="text-muted mb-0">Edit dokumen modul ajar menggunakan Collabora Online dari server terpasang.</p>
            </div>
            <a href="{{ route('rencana_pembelajaran.index') }}" class="btn btn-outline-secondary btn-sm">
                <i class="ti ti-arrow-left me-1"></i>Kembali ke Daftar
            </a>
        </div>
    </div>
    <div class="card-body p-0">
        <div class="border-bottom bg-light px-3 py-2">
            <div class="small text-muted">
                Server Collabora: <span class="fw-semibold">{{ $collaboraServerUrl ?? 'http://collabora.sman1pontang.sch.id' }}</span>
            </div>
        </div>
        <div class="p-0" style="min-height:760px;">
            @if(!empty($collaboraEditorUrl))
                <iframe
                    src="{{ $collaboraEditorUrl }}"
                    title="Collabora Editor"
                    style="width:100%; height:760px; border:0; background:#fff;"
                    allowfullscreen
                ></iframe>
            @else
                <div class="alert alert-warning m-4">
                    Collabora server belum dikonfigurasi. Tambahkan <code>COLLABORA_SERVER_URL</code> di <code>.env</code>.
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
