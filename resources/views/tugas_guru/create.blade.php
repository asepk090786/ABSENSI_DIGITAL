@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Tambah Tugas Guru</h3>
                </div>
                <div class="card-body">
                    @if(session('error'))
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            {{ session('error') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    <form action="{{ route('tugas_guru.store') }}" method="POST">
                        @csrf
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="guru_id" class="form-label">Guru <span class="text-danger">*</span></label>
                                    <select name="guru_id" id="guru_id" class="form-select @error('guru_id') is-invalid @enderror" required>
                                        <option value="">Pilih Guru</option>
                                        @foreach($guruList as $guru)
                                            <option value="{{ $guru->id }}" {{ old('guru_id') == $guru->id ? 'selected' : '' }}>
                                                {{ $guru->user->name ?? $guru->nama }} {{ $guru->nip ? ' - ' . $guru->nip : '' }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('guru_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="mata_pelajaran_id" class="form-label">Mata Pelajaran <span class="text-danger">*</span></label>
                                    <select name="mata_pelajaran_id" id="mata_pelajaran_id" class="form-select @error('mata_pelajaran_id') is-invalid @enderror" required>
                                        <option value="">Pilih Mata Pelajaran</option>
                                        @foreach($mataPelajaranList as $mapel)
                                            <option value="{{ $mapel->id }}" {{ old('mata_pelajaran_id') == $mapel->id ? 'selected' : '' }}>
                                                {{ $mapel->nama_mapel }} {{ $mapel->kode_mapel ? '(' . $mapel->kode_mapel . ')' : '' }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('mata_pelajaran_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="tingkat_kelas" class="form-label">Tingkat Kelas <span class="text-danger">*</span></label>
                                    <select name="tingkat_kelas" id="tingkat_kelas" class="form-select @error('tingkat_kelas') is-invalid @enderror" required>
                                        <option value="">Pilih Tingkat</option>
                                        @foreach($tingkatList as $tingkat)
                                            <option value="{{ $tingkat }}" {{ old('tingkat_kelas') == $tingkat ? 'selected' : '' }}>
                                                {{ $tingkat }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('tingkat_kelas')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="kelas_id" class="form-label">Kelas Spesifik <small class="text-muted">(Optional)</small></label>
                                    <select name="kelas_id" id="kelas_id" class="form-select @error('kelas_id') is-invalid @enderror">
                                        <option value="">Semua kelas di tingkat ini</option>
                                        @foreach($kelasList as $kelas)
                                            <option value="{{ $kelas->id }}" data-tingkat="{{ $kelas->tingkat_kelas }}" {{ old('kelas_id') == $kelas->id ? 'selected' : '' }}>
                                                {{ $kelas->nama_kelas }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('kelas_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <small class="form-text text-muted">Kosongkan jika guru mengajar di semua kelas pada tingkat yang dipilih</small>
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" name="is_active" id="is_active" value="1" {{ old('is_active', true) ? 'checked' : '' }}>
                                <label class="form-check-label" for="is_active">
                                    Status Aktif
                                </label>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="keterangan" class="form-label">Keterangan</label>
                            <textarea name="keterangan" id="keterangan" class="form-control @error('keterangan') is-invalid @enderror" rows="3">{{ old('keterangan') }}</textarea>
                            @error('keterangan')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="d-flex justify-content-end gap-2">
                            <a href="{{ route('tugas_guru.index') }}" class="btn btn-secondary">
                                <i class="ti ti-arrow-left"></i> Kembali
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="ti ti-device-floppy"></i> Simpan
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const tingkatSelect = document.getElementById('tingkat_kelas');
    const kelasSelect = document.getElementById('kelas_id');
    
    // Filter kelas berdasarkan tingkat yang dipilih
    tingkatSelect.addEventListener('change', function() {
        const selectedTingkat = this.value;
        const kelasOptions = kelasSelect.querySelectorAll('option');
        
        kelasOptions.forEach(option => {
            if (option.value === '') {
                option.style.display = 'block';
                return;
            }
            
            const tingkatOption = option.getAttribute('data-tingkat');
            if (tingkatOption === selectedTingkat || !selectedTingkat) {
                option.style.display = 'block';
            } else {
                option.style.display = 'none';
            }
        });
        
        // Reset kelas selection if current is hidden
        if (kelasSelect.value && kelasSelect.selectedOptions[0].style.display === 'none') {
            kelasSelect.value = '';
        }
    });
    
    // Trigger initial filter
    tingkatSelect.dispatchEvent(new Event('change'));
});
</script>
@endsection
