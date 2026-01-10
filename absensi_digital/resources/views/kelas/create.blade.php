@extends('layouts.app', ['pageSlug' => 'kelas'])

@section('title','Tambah Kelas')

@section('content')
<div class="row">
    <div class="col-md-10 mx-auto">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h4 class="card-title mb-0">Tambah Kelas</h4>
                <a href="{{ route('kelas.index') }}" class="btn btn-secondary btn-sm">Kembali</a>
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('kelas.store') }}">
                    @csrf

                    <div class="alert alert-info">
                        <i class="ti ti-info-circle me-2"></i>
                        <strong>Informasi:</strong> Data yang bertanda <span class="text-danger">*</span> wajib diisi.
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Nama Kelas <span class="text-danger">*</span></label>
                        <input type="text" name="nama_kelas" class="form-control @error('nama_kelas') is-invalid @enderror" value="{{ old('nama_kelas') }}" required>
                        @error('nama_kelas')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Tingkatan Kelas <span class="text-danger">*</span></label>
                        <select name="tingkat_kelas" class="form-select @error('tingkat_kelas') is-invalid @enderror" required>
                            <option value="">-- Pilih Tingkatan --</option>
                            @php
                                $tingkatanByJenjang = [
                                    'SD' => ['I', 'II', 'III', 'IV', 'V', 'VI'],
                                    'SMP' => ['VII', 'VIII', 'IX'],
                                    'SMA' => ['X', 'XI', 'XII'],
                                    'SMK' => ['X', 'XI', 'XII'],
                                ];
                                $jenjang = $sekolah->jenjang ?? '';
                                $tingkatan = $tingkatanByJenjang[$jenjang] ?? [];
                            @endphp
                            @forelse($tingkatan as $tingkat)
                                <option value="{{ $tingkat }}" {{ old('tingkat_kelas') == $tingkat ? 'selected' : '' }}>
                                    Tingkat {{ $tingkat }}
                                </option>
                            @empty
                                <option value="">Jenjang sekolah belum diatur</option>
                            @endforelse
                        </select>
                        <small class="form-hint">Pilihan disesuaikan berdasarkan jenjang sekolah: {{ $jenjang ?? 'Belum diatur' }}</small>
                        @error('tingkat_kelas')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Wali Kelas</label>
                        <select name="wali_kelas_id" class="form-select @error('wali_kelas_id') is-invalid @enderror">
                            <option value="">Pilih Wali Kelas</option>
                            @foreach($guruList as $guru)
                                <option value="{{ $guru->id }}" {{ old('wali_kelas_id')==$guru->id ? 'selected' : '' }}>{{ $guru->nama }}</option>
                            @endforeach
                        </select>
                        @error('wali_kelas_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="mt-4">
                        <button type="submit" class="btn btn-primary">
                            <i class="ti ti-device-floppy me-2"></i>Simpan
                        </button>
                        <a href="{{ route('kelas.index') }}" class="btn btn-secondary">Batal</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
