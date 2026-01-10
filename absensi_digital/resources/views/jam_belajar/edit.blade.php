@extends('layouts.app')

@section('title','Edit Jam KBM')

@section('content')
<div class="row mb-3">
    <div class="col-12">
        <a href="{{ route('jam_belajar.index') }}" class="btn btn-light">
            <i class="ti ti-arrow-left me-2"></i>Kembali
        </a>
    </div>
</div>

<div class="row">
    <div class="col-lg-6 mx-auto">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-light">
                <h4 class="card-title mb-0">
                    <i class="ti ti-clock-edit me-2"></i>Edit Jam KBM
                </h4>
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('jam_belajar.update', $item->id) }}">
                    @csrf
                    @method('PUT')
                    <div class="mb-3">
                        <label class="form-label">Hari <span class="text-danger">*</span></label>
                        <select name="hari" class="form-select" required>
                            <option value="">-- Pilih Hari --</option>
                            @foreach($days as $day)
                                <option value="{{ $day }}" {{ old('hari', $item->hari) == $day ? 'selected' : '' }}>{{ $day }}</option>
                            @endforeach
                        </select>
                        @error('hari')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Jam Ke (Nomor Urut) <span class="text-danger">*</span></label>
                        <input type="number" name="urutan" class="form-control" value="{{ old('urutan', $item->urutan) }}" min="1" required>
                        <small class="text-muted">Jam ke-1, Jam ke-2, dst</small>
                        @error('urutan')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Jam Mulai <span class="text-danger">*</span></label>
                                <input type="time" name="jam_mulai" class="form-control" value="{{ old('jam_mulai', $item->jam_mulai) }}" required>
                                <small class="text-muted">Format: HH:MM</small>
                                @error('jam_mulai')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Jam Selesai <span class="text-danger">*</span></label>
                                <input type="time" name="jam_selesai" class="form-control" value="{{ old('jam_selesai', $item->jam_selesai) }}" required>
                                <small class="text-muted">Format: HH:MM</small>
                                @error('jam_selesai')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Jenis <span class="text-danger">*</span></label>
                        <input type="text" name="jenis" class="form-control" value="{{ old('jenis', $item->jenis) }}" placeholder="Contoh: KBM, Istirahat" required>
                        @error('jenis')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                    </div>

                    <div class="d-flex gap-2 pt-3">
                        <button type="submit" class="btn btn-primary">
                            <i class="ti ti-check me-2"></i>Simpan Perubahan
                        </button>
                        <a href="{{ route('jam_belajar.index') }}" class="btn btn-light">Batal</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
