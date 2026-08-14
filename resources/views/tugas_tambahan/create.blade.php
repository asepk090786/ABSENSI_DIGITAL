@extends('layouts.app')

@section('title','Tambah Tugas Tambahan')

@section('content')
<div class="container-fluid">
    <div class="page-header d-print-none">
        <div class="row align-items-center">
            <div class="col">
                <h2 class="page-title">
                    <i class="ti ti-briefcase me-2"></i>Tambah Tugas Tambahan
                </h2>
            </div>
        </div>
    </div>
</div>

<div class="page-wrapper">
    <div class="container-xl">
        <div class="row">
            <div class="col-md-8">
                <div class="card">
                    <form action="{{ route('tugas_tambahan.store') }}" method="POST">
                        @csrf

                        <div class="card-body">
                            <div class="alert alert-info">
                                <i class="ti ti-info-circle me-2"></i>Data bertanda <strong>*</strong> wajib diisi
                            </div>

                            <!-- Tenaga Pendidikan Selection -->
                            <div class="mb-3">
                                <label class="form-label" for="tenaga_pendidikan_id">
                                    Tenaga Pendidikan <span class="text-danger">*</span>
                                </label>
                                <select class="form-control @error('tenaga_pendidikan_id') is-invalid @enderror" 
                                        id="tenaga_pendidikan_id" name="tenaga_pendidikan_id" required>
                                    <option value="">-- Pilih Tenaga Pendidikan --</option>
                                    @foreach ($tenagaPendidikan as $tp)
                                        <option value="{{ $tp->id }}" @selected(old('tenaga_pendidikan_id') == $tp->id)>
                                            {{ $tp->nama }} ({{ $tp->nip }})
                                        </option>
                                    @endforeach
                                </select>
                                @error('tenaga_pendidikan_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Tugas -->
                            <div class="mb-3">
                                <label class="form-label" for="tugas">
                                    Tugas <span class="text-danger">*</span>
                                </label>
                                <input type="text" class="form-control @error('tugas') is-invalid @enderror" 
                                       id="tugas" name="tugas" placeholder="Contoh: Piket Perpustakaan" 
                                       value="{{ old('tugas') }}" required>
                                <small class="form-hint">Nama tugas yang akan ditugaskan (max 255 karakter)</small>
                                @error('tugas')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Keterangan -->
                            <div class="mb-3">
                                <label class="form-label" for="keterangan">Keterangan</label>
                                <textarea class="form-control @error('keterangan') is-invalid @enderror" 
                                          id="keterangan" name="keterangan" rows="4" 
                                          placeholder="Deskripsi detail tentang tugas ini">{{ old('keterangan') }}</textarea>
                                <small class="form-hint">Opsional - Tambahkan penjelasan detail tentang tugas</small>
                                @error('keterangan')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Status -->
                            <div class="mb-3">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="is_active" name="is_active" 
                                           value="1" @checked(old('is_active', true))>
                                    <label class="form-check-label" for="is_active">
                                        Aktif
                                    </label>
                                </div>
                                <small class="form-hint">Tandai untuk mengaktifkan tugas ini</small>
                            </div>
                        </div>

                        <div class="card-footer text-end">
                            <a href="{{ route('tugas_tambahan.index') }}" class="btn btn-link">Batal</a>
                            <button type="submit" class="btn btn-primary">
                                <i class="ti ti-device-floppy me-1"></i>Simpan Tugas
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
