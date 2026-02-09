@extends('layouts.app')

@section('title', 'Edit Komponen Penilaian')

@section('content')
<div class="page-header d-print-none">
    <div class="container-xl">
        <div class="row g-2 align-items-center">
            <div class="col">
                <h2 class="page-title">
                    <i class="ti ti-edit me-2"></i>Edit Komponen Penilaian
                </h2>
            </div>
            <div class="col-auto ms-auto d-print-none">
                <a href="{{ route('komponen_nilai.index') }}" class="btn btn-secondary">
                    <i class="ti ti-arrow-left me-1"></i>Kembali
                </a>
            </div>
        </div>
    </div>
</div>

<div class="page-body">
    <div class="container-xl">
        <div class="card">
            <div class="card-body">
                <form method="POST" action="{{ route('komponen_nilai.update', $item->id) }}">
                    @csrf
                    @method('PUT')
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Nama Komponen <span class="text-danger">*</span></label>
                            <input type="text" name="nama_komponen" class="form-control @error('nama_komponen') is-invalid @enderror" value="{{ old('nama_komponen', $item->nama_komponen) }}" required>
                            @error('nama_komponen')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Bobot (%)</label>
                            <input type="number" name="bobot" class="form-control @error('bobot') is-invalid @enderror" value="{{ old('bobot', $item->bobot) }}" min="0" max="100" step="0.01">
                            @error('bobot')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-12">
                            <label class="form-label">Capaian Pembelajaran</label>
                            <textarea name="capaian_pembelajaran" class="form-control @error('capaian_pembelajaran') is-invalid @enderror" rows="3">{{ old('capaian_pembelajaran', $item->capaian_pembelajaran) }}</textarea>
                            @error('capaian_pembelajaran')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-12">
                            <label class="form-label">Tujuan Pembelajaran</label>
                            <textarea name="tujuan_pembelajaran" class="form-control @error('tujuan_pembelajaran') is-invalid @enderror" rows="3">{{ old('tujuan_pembelajaran', $item->tujuan_pembelajaran) }}</textarea>
                            @error('tujuan_pembelajaran')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-12">
                            <label class="form-label">Alur Tujuan Pembelajaran</label>
                            <textarea name="alur_tujuan_pembelajaran" class="form-control @error('alur_tujuan_pembelajaran') is-invalid @enderror" rows="3">{{ old('alur_tujuan_pembelajaran', $item->alur_tujuan_pembelajaran) }}</textarea>
                            @error('alur_tujuan_pembelajaran')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-12">
                            <label class="form-label">Indikator / Kriteria Ketercapaian</label>
                            <textarea name="indikator_kriteria" class="form-control @error('indikator_kriteria') is-invalid @enderror" rows="3">{{ old('indikator_kriteria', $item->indikator_kriteria) }}</textarea>
                            @error('indikator_kriteria')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-12">
                            <button type="submit" class="btn btn-primary">
                                <i class="ti ti-device-floppy me-1"></i>Simpan
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
