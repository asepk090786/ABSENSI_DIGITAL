@extends('layouts.app')

@section('content')
<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3 class="mb-0">Edit API Key</h3>
        <a href="{{ route('setting.api') }}" class="btn btn-sm btn-outline-secondary">Kembali</a>
    </div>

    <div class="card">
        <div class="card-body">
            <form method="POST" action="{{ route('setting.api.keys.update', $apiKey) }}">
                @csrf
                @method('PUT')
                <div class="mb-3">
                    <label for="name" class="form-label">Nama API Key</label>
                    <input type="text" id="name" name="name" class="form-control" value="{{ old('name', $apiKey->name) }}" required maxlength="255">
                    @error('name')
                        <div class="text-danger small mt-1">{{ $message }}</div>
                    @enderror
                </div>
                <button type="submit" class="btn btn-primary btn-sm">Simpan Perubahan</button>
                <a href="{{ route('setting.api') }}" class="btn btn-outline-secondary btn-sm">Batal</a>
            </form>
        </div>
    </div>
</div>
@endsection
