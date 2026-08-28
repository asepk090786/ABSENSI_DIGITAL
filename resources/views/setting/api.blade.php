@extends('layouts.app')

@section('content')
<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h3 class="mb-1">Pengaturan Endpoint API</h3>
            <p class="text-muted mb-0">Informasi akses sinkronisasi master data aplikasi.</p>
        </div>
        <span class="badge bg-success">Aktif</span>
    </div>

        @if(session('generated_api_key'))
        <div class="alert alert-success">
            <strong>API key berhasil dibuat.</strong>
            <p class="mb-2">Salin key ini sekarang. Demi keamanan, key lengkap hanya ditampilkan satu kali.</p>
            <div class="input-group">
                <input type="text" class="form-control" value="{{ session('generated_api_key') }}" readonly id="generatedApiKey">
                <button type="button" class="btn btn-sm btn-outline-success api-action-button" onclick="copyToClipboard('generatedApiKey', this)">
                    <i class="ti ti-copy" aria-hidden="true"></i>
                    Salin Key
                </button>
            </div>
        </div>
        @endif

        @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
        @endif

    <div class="card">
        <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5 class="mb-0">API Key</h5>
                    <form method="POST" action="{{ route('setting.api.keys.generate') }}" class="d-flex gap-2">
                        @csrf
                        <input type="text" name="name" class="form-control" placeholder="Nama key (opsional)" maxlength="255">
                        <button type="submit" class="btn btn-sm btn-primary text-nowrap api-action-button">
                            <i class="ti ti-key" aria-hidden="true"></i>
                            Generate Key
                        </button>
                    </form>
                </div>
                <div class="table-responsive mb-4">
                    <table class="table table-vcenter">
                        <thead>
                            <tr>
                                <th>Nama</th>
                                <th>Key</th>
                                <th>Dibuat Oleh</th>
                                <th>Status</th>
                                <th>Terakhir Digunakan</th>
                                <th class="text-end">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($apiKeys as $apiKey)
                            <tr>
                                <td>{{ $apiKey->name }}</td>
                                <td>
                                    @if($apiKey->plain_key)
                                        <div class="input-group input-group-sm api-key-display">
                                            <input type="text" class="form-control" value="{{ $apiKey->plain_key }}" readonly id="apiKey{{ $apiKey->id }}">
                                            <button type="button" class="btn btn-outline-primary api-action-button" onclick="copyToClipboard('apiKey{{ $apiKey->id }}', this)" title="Salin API key">
                                                <i class="ti ti-copy" aria-hidden="true"></i> Salin
                                            </button>
                                        </div>
                                    @else
                                        <code>{{ $apiKey->key_prefix }}...</code>
                                        <small class="d-block text-muted">Buat ulang untuk melihat lengkap</small>
                                    @endif
                                </td>
                                <td>{{ $apiKey->generatedBy?->name ?? '-' }}</td>
                                <td>
                                    @if($apiKey->revoked_at)
                                        <span class="badge bg-secondary">Dicabut</span>
                                    @else
                                        <span class="badge bg-success">Aktif</span>
                                    @endif
                                </td>
                                <td>{{ $apiKey->last_used_at?->format('d-m-Y H:i') ?? 'Belum digunakan' }}</td>
                                <td class="text-end">
                                    <div class="d-flex justify-content-end gap-1">
                                        <a href="{{ route('setting.api.keys.show', $apiKey) }}" class="btn btn-sm btn-outline-secondary api-action-button" title="Lihat API key">
                                            <i class="ti ti-eye" aria-hidden="true"></i> View
                                        </a>
                                        <a href="{{ route('setting.api.keys.edit', $apiKey) }}" class="btn btn-sm btn-outline-primary api-action-button" title="Edit nama API key">
                                            <i class="ti ti-edit" aria-hidden="true"></i> Edit
                                        </a>
                                        <form method="POST" action="{{ route('setting.api.keys.delete', $apiKey) }}" onsubmit="return confirm('Hapus API key ini secara permanen?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-outline-danger api-action-button" title="Hapus API key">
                                                <i class="ti ti-trash" aria-hidden="true"></i> Hapus
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr><td colspan="6" class="text-muted">Belum ada API key.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

            <div class="mb-4">
                <label class="form-label fw-semibold">Endpoint Master Data</label>
                <div class="mb-2">
                    <a href="{{ $masterDataEndpoint }}" target="_blank" rel="noopener" class="text-primary">
                        {{ $masterDataEndpoint }}
                    </a>
                </div>
                <div class="input-group">
                    <input type="text" class="form-control" value="{{ $masterDataEndpoint }}" readonly id="masterDataEndpoint">
                    <button type="button" class="btn btn-sm btn-outline-primary api-action-button" onclick="copyToClipboard('masterDataEndpoint', this)">
                        <i class="ti ti-copy" aria-hidden="true"></i>
                        Salin
                    </button>
                </div>
            </div>

            <dl class="row mb-0">
                <dt class="col-sm-3">Metode</dt>
                <dd class="col-sm-9"><span class="badge bg-primary">GET</span></dd>
                <dt class="col-sm-3">Autentikasi</dt>
                <dd class="col-sm-9"><code>X-API-Key: API_KEY</code></dd>
                <dt class="col-sm-3">Hak akses</dt>
                <dd class="col-sm-9">Admin</dd>
                <dt class="col-sm-3">Data</dt>
                <dd class="col-sm-9">Mata pelajaran, guru, siswa, user, kelas, wali kelas, dan penugasan guru</dd>
            </dl>

            <div class="alert alert-warning mt-4 mb-0">
                Password akun tidak pernah dikirim oleh endpoint, termasuk dalam bentuk hash. Simpan API key di server atau environment variable dan jangan masukkan ke source code frontend.
            </div>
        </div>
    </div>
</div>
@endsection

@push('js')
<style>
    .api-action-button {
        font-size: .875rem;
        line-height: 1.25rem;
        white-space: nowrap;
    }

    .api-action-button i {
        font-size: 1rem;
        vertical-align: -0.125rem;
    }
</style>
<script>
    async function copyToClipboard(inputId, button) {
        const input = document.getElementById(inputId);
        const originalText = button.innerHTML;

        try {
            if (navigator.clipboard && window.isSecureContext) {
                await navigator.clipboard.writeText(input.value);
            } else {
                input.focus();
                input.select();
                input.setSelectionRange(0, input.value.length);
                if (!document.execCommand('copy')) {
                    throw new Error('Copy command failed');
                }
                input.setSelectionRange(0, 0);
            }

            button.innerHTML = '<i class="ti ti-check" aria-hidden="true"></i> Tersalin';
            setTimeout(() => { button.innerHTML = originalText; }, 1500);
        } catch (error) {
            button.innerHTML = '<i class="ti ti-alert-circle" aria-hidden="true"></i> Gagal menyalin';
            setTimeout(() => { button.innerHTML = originalText; }, 2000);
        }
    }
</script>
@endpush