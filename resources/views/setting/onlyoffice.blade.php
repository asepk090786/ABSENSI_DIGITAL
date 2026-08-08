@extends('layouts.app', ['pageSlug' => 'setting-onlyoffice'])

@section('title','Pengaturan OnlyOffice')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h3 class="mb-0">Pengaturan OnlyOffice</h3>
            <p class="text-muted mb-0">Atur URL server OnlyOffice yang digunakan untuk mengedit dokumen RPP.</p>
        </div>
        <a href="{{ route('tahun_ajaran.index') }}" class="btn btn-outline-secondary">Kembali</a>
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <div class="card">
        <div class="card-body">
            <form action="{{ route('setting.onlyoffice.update') }}" method="POST">
                @csrf
                @method('PUT')

                <div class="mb-3">
                    <label for="server_url" class="form-label">URL Server OnlyOffice</label>
                    <input type="url" id="server_url" name="server_url" class="form-control @error('server_url') is-invalid @enderror" value="{{ old('server_url', $settings['server_url']) }}" placeholder="https://onlyoffice.example.com">
                    @error('server_url')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                    <div class="form-text">Gunakan URL lengkap ke instalasi OnlyOffice Document Server, misalnya https://onlyoffice.example.com.</div>
                </div>

                <div class="mb-3">
                    <label for="server_secret" class="form-label">Secret JWT Server OnlyOffice</label>
                    <input type="text" id="server_secret" name="server_secret" class="form-control @error('server_secret') is-invalid @enderror" value="{{ old('server_secret', $settings['server_secret']) }}" placeholder="Masukkan secret JWT jika diperlukan">
                    @error('server_secret')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                    <div class="form-text">Jika server OnlyOffice Anda diatur dengan JWT security, masukkan secret yang sama di sini.</div>
                </div>

                <div class="alert alert-warning small">
                    Jika situs Anda diakses melalui HTTPS, pastikan URL OnlyOffice juga menggunakan HTTPS. Jika masih menggunakan HTTP, browser akan memblokir konten karena mixed content.
                </div>

                <button type="submit" class="btn btn-primary">Simpan Pengaturan</button>
            </form>
        </div>
    </div>
@endsection