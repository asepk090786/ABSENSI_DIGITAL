@extends('layouts.app', ['pageSlug' => 'agenda_guru'])

@section('title','Tambah Agenda Mengajar Guru')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12 col-lg-8 offset-lg-2">
            <div class="card">
                <div class="card-header border-0 pt-3 pb-2">
                    <h4 class="card-title">
                        <i class="ti ti-plus me-2"></i>Tambah Agenda Mengajar Guru
                    </h4>
                </div>
                <div class="card-body">
                    @if($errors->any())
                        <div class="alert alert-danger alert-dismissible fade show">
                            <strong>Gagal!</strong> Terjadi kesalahan:
                            <ul class="mb-0 mt-2">
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                            <button type="button" class="close" data-bs-dismiss="alert"><span aria-hidden="true">&times;</span></button>
                        </div>
                    @endif

                    <form action="{{ route('agenda_guru.store') }}" method="POST">
                        @csrf

                        <div class="mb-2">
                            <label class="form-label">Tanggal <span class="text-danger">*</span></label>
                            <input type="date" class="form-control @error('tanggal') is-invalid @enderror" 
                                   name="tanggal" value="{{ old('tanggal', $selectedTanggal) }}" required>
                            @error('tanggal')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-2">
                            <label class="form-label">Jam Pelajaran <span class="text-danger">*</span></label>
                            <select class="form-select @error('jam_belajar_id') is-invalid @enderror" name="jam_belajar_id" required>
                                <option value="">-- Pilih Jam Pelajaran --</option>
                                @foreach($jamBelajar as $jam)
                                    <option value="{{ $jam->id }}" {{ old('jam_belajar_id') == $jam->id ? 'selected' : '' }}>
                                        {{ $jam->jam_mulai }} - {{ $jam->jam_selesai }}
                                        @if($jam->jenis)
                                            ({{ $jam->jenis }})
                                        @endif
                                    </option>
                                @endforeach
                            </select>
                            @error('jam_belajar_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-2">
                            <label class="form-label">Rencana Pembelajaran (opsional)</label>
                            <select class="form-select" name="rencana_pembelajaran_id">
                                <option value="">-- Pilih RPP --</option>
                                @foreach($rencanaPembelajaranList as $r)
                                    <option value="{{ $r->id }}" {{ old('rencana_pembelajaran_id') == $r->id ? 'selected' : '' }}>
                                        {{ $r->judul }}
                                    </option>
                                @endforeach
                            </select>
                            <small class="text-muted">Memilih RPP akan mengisi ringkasan kegiatan secara otomatis jika kegiatan kosong.</small>
                        </div>

                        <div class="mb-2">
                            <label class="form-label">Kegiatan / Materi Ajar <span class="text-danger">*</span></label>
                            <select class="form-select @error('kegiatan') is-invalid @enderror" name="kegiatan_select" required id="kegiatanSelect">
                                <option value="">-- Pilih Kegiatan --</option>
                                @foreach($kegiatanList as $keg)
                                    <option value="{{ $keg->nama_kegiatan }}" {{ old('kegiatan') == $keg->nama_kegiatan ? 'selected' : '' }}>
                                        {{ $keg->nama_kegiatan }} {{ $keg->kode_kegiatan ? '(' . $keg->kode_kegiatan . ')' : '' }}
                                    </option>
                                @endforeach
                                <option value="__other__" {{ !in_array(old('kegiatan'), $kegiatanList->pluck('nama_kegiatan')->toArray()) && old('kegiatan') ? 'selected' : '' }}>Lainnya (ketik sendiri)</option>
                            </select>
                            <input type="text" name="kegiatan" id="kegiatanOther" class="form-control mt-2 @error('kegiatan') is-invalid @enderror" value="{{ old('kegiatan') }}" placeholder="Ketik kegiatan atau materi ajar pada hari ini..." style="display: none;" maxlength="1000">
                            @error('kegiatan')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="text-muted">Maksimal 1000 karakter</small>
                        </div>

                        <div class="d-grid gap-2 d-sm-flex gap-sm-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="ti ti-check me-2"></i>Simpan
                            </button>
                            <a href="{{ route('agenda_guru.index') }}" class="btn btn-outline-secondary btn-modern">
                                <i class="ti ti-x me-2"></i>Batal
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const kegiatanSelect = document.getElementById('kegiatanSelect');
    const kegiatanOther = document.getElementById('kegiatanOther');
    if (kegiatanSelect && kegiatanOther) {
        function toggleKegiatanOther() {
            if (kegiatanSelect.value === '__other__') {
                kegiatanOther.style.display = 'block';
                kegiatanOther.required = true;
                kegiatanOther.focus();
            } else {
                kegiatanOther.style.display = 'none';
                kegiatanOther.required = false;
                kegiatanOther.value = '';
            }
        }
        kegiatanSelect.addEventListener('change', toggleKegiatanOther);
        toggleKegiatanOther();
    }
});
</script>
@endsection
