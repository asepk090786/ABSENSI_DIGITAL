@extends('layouts.app')

@section('content')
<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3 class="mb-0">Detail API Key</h3>
        <a href="{{ route('setting.api') }}" class="btn btn-sm btn-outline-secondary">Kembali</a>
    </div>

    <div class="card">
        <div class="card-body">
            <dl class="row mb-0">
                <dt class="col-sm-3">Nama</dt>
                <dd class="col-sm-9">{{ $apiKey->name }}</dd>
                <dt class="col-sm-3">Prefix Key</dt>
                <dd class="col-sm-9"><code>{{ $apiKey->key_prefix }}...</code></dd>
                <dt class="col-sm-3">API Key Lengkap</dt>
                <dd class="col-sm-9">
                    @if($apiKey->plain_key)
                        <div class="input-group input-group-sm">
                            <input type="text" class="form-control" value="{{ $apiKey->plain_key }}" readonly id="apiKeyDetail">
                            <button type="button" class="btn btn-outline-primary" onclick="copyToClipboard('apiKeyDetail', this)">Salin</button>
                        </div>
                    @else
                        <span class="text-muted">Tidak tersedia. Buat API key baru.</span>
                    @endif
                </dd>
                <dt class="col-sm-3">Dibuat Oleh</dt>
                <dd class="col-sm-9">{{ $apiKey->generatedBy?->name ?? '-' }}</dd>
                <dt class="col-sm-3">Status</dt>
                <dd class="col-sm-9">{{ $apiKey->revoked_at ? 'Dicabut' : 'Aktif' }}</dd>
                <dt class="col-sm-3">Terakhir Digunakan</dt>
                <dd class="col-sm-9">{{ $apiKey->last_used_at?->format('d-m-Y H:i') ?? 'Belum digunakan' }}</dd>
                <dt class="col-sm-3">Dibuat</dt>
                <dd class="col-sm-9">{{ $apiKey->created_at?->format('d-m-Y H:i') ?? '-' }}</dd>
            </dl>
            <div class="alert alert-warning mt-4 mb-0">
                API key lengkap tidak dapat dilihat kembali. Buat key baru jika key asli hilang.
            </div>
        </div>
    </div>
</div>
@endsection

@push('js')
<script>
    function copyToClipboard(inputId, button) {
        const input = document.getElementById(inputId);
        input.focus();
        input.select();
        document.execCommand('copy');
        button.textContent = 'Tersalin';
    }
</script>
@endpush
