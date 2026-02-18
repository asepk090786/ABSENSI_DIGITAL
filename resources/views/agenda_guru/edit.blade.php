@extends('layouts.app', ['pageSlug' => 'agenda_guru'])

@section('title','Edit Agenda Mengajar Guru')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12 col-lg-8 offset-lg-2">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title">
                        <i class="ti ti-edit me-2"></i>Edit Agenda Mengajar Guru
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
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    <form action="{{ route('agenda_guru.update', $agendaGuru->id) }}" method="POST">
                        @csrf
                        @method('PUT')

                        <div class="mb-3">
                            <label class="form-label">Tanggal <span class="text-danger">*</span></label>
                            <input type="date" class="form-control @error('tanggal') is-invalid @enderror" 
                                   name="tanggal" value="{{ old('tanggal', $agendaGuru->tanggal->format('Y-m-d')) }}" required>
                            @error('tanggal')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Jam Pelajaran <span class="text-danger">*</span></label>
                            <select class="form-select @error('jam_belajar_id') is-invalid @enderror" name="jam_belajar_id" required>
                                <option value="">-- Pilih Jam Pelajaran --</option>
                                @foreach($jamBelajar as $jam)
                                    <option value="{{ $jam->id }}" {{ old('jam_belajar_id', $agendaGuru->jam_belajar_id) == $jam->id ? 'selected' : '' }}>
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

                        <div class="mb-3">
                            <label class="form-label">Kegiatan / Materi Ajar <span class="text-danger">*</span></label>
                            <textarea class="form-control @error('kegiatan') is-invalid @enderror" 
                                      name="kegiatan" rows="5" placeholder="Tuliskan kegiatan atau materi ajar pada hari ini..." required>{{ old('kegiatan', $agendaGuru->kegiatan) }}</textarea>
                            @error('kegiatan')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="text-muted">Maksimal 1000 karakter</small>
                        </div>

                        <div class="d-grid gap-2 d-sm-flex gap-sm-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="ti ti-check me-2"></i>Simpan Perubahan
                            </button>
                            <a href="{{ route('agenda_guru.index') }}" class="btn btn-outline-secondary">
                                <i class="ti ti-x me-2"></i>Batal
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
