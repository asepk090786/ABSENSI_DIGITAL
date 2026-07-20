@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h3 class="card-title">Edit SK Tugas</h3>
                    <a href="{{ route('sk_tugas.index') }}" class="btn btn-secondary">Kembali</a>
                </div>
                <div class="card-body">
                    @if($errors->any())
                        <div class="alert alert-danger">
                            <ul class="mb-0">
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form action="{{ route('sk_tugas.update', $sk_tugas->id) }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')

                        <div class="mb-3">
                            <label class="form-label">Judul SK Tugas</label>
                            <input type="text" name="judul" class="form-control" value="{{ old('judul', $sk_tugas->judul) }}" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">File SK Tugas <small class="text-muted">(biarkan kosong untuk mempertahankan file saat ini)</small></label>
                            <input type="file" name="file" class="form-control" accept=".pdf,.doc,.docx">
                            <div class="mt-2">
                                <strong>File saat ini:</strong> {{ basename($sk_tugas->file) }}
                            </div>
                        </div>

                        <div class="mb-3 form-check">
                            <input type="checkbox" name="is_visible_to_guru" value="1" id="is_visible" class="form-check-input" {{ old('is_visible_to_guru', $sk_tugas->is_visible_to_guru) ? 'checked' : '' }}>
                            <label class="form-check-label" for="is_visible">Tampilkan ke guru</label>
                        </div>

                        <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
