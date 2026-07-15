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
                    
                    <div class="row g-2 mb-4">
                        <div class="col-md-12">
                            <label class="form-label">Capaian Pembelajaran (Opsional)</label>
                            <select name="capaian_pembelajaran_id" class="form-select @error('capaian_pembelajaran_id') is-invalid @enderror">
                                <option value="">-- Pilih Capaian Pembelajaran --</option>
                                @foreach($capaianList as $cp)
                                <option value="{{ $cp->id }}" {{ old('capaian_pembelajaran_id', $item->capaian_pembelajaran_id) == $cp->id ? 'selected' : '' }}>
                                    [{{ $cp->fase ?? 'N/A' }}] {{ $cp->nama_capaian_pembelajaran }}
                                </option>
                                @endforeach
                            </select>
                            @error('capaian_pembelajaran_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    
                    <div class="row g-2 mb-4">
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
                    </div>

                    
                    <div class="accordion accordion-flush mb-4" id="learningAccordion">
                        
                        <div class="accordion-item">
                            <h2 class="accordion-header">
                                <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#cpSection">
                                    <i class="ti ti-target me-2"></i>
                                    <strong>1. Capaian Pembelajaran (CP)</strong>
                                    <small class="text-muted ms-2">Target kompetensi akhir pada fase</small>
                                </button>
                            </h2>
                            <div id="cpSection" class="accordion-collapse collapse show" data-parent="#learningAccordion">
                                <div class="accordion-body">
                                    <small class="text-muted d-block mb-2">
                                        <strong>Catatan:</strong> Target kompetensi akhir pada fase (misalnya Fase E & F di SMA). Bersifat umum dan menyeluruh, menjadi acuan utama pembelajaran.
                                    </small>
                                    <textarea name="capaian_pembelajaran" class="form-control @error('capaian_pembelajaran') is-invalid @enderror" rows="4" placeholder="Contoh: Siswa dapat menganalisis dan mengevaluasi fenomena sosial dengan perspektif kritis...">{{ old('capaian_pembelajaran', $item->capaian_pembelajaran) }}</textarea>
                                    @error('capaian_pembelajaran')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        
                        <div class="accordion-item">
                            <h2 class="accordion-header">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#tpSection">
                                    <i class="ti ti-target me-2"></i>
                                    <strong>2. Tujuan Pembelajaran (TP)</strong>
                                    <small class="text-muted ms-2">Turunan spesifik dari CP</small>
                                </button>
                            </h2>
                            <div id="tpSection" class="accordion-collapse collapse" data-parent="#learningAccordion">
                                <div class="accordion-body">
                                    <small class="text-muted d-block mb-2">
                                        <strong>Catatan:</strong> Turunan langsung dari CP, lebih spesifik, operasional, dan terukur. Menjadi sasaran pembelajaran harian/per pertemuan yang mendorong berpikir kritis, reflektif, dan bermakna.
                                    </small>
                                    <textarea name="tujuan_pembelajaran" class="form-control @error('tujuan_pembelajaran') is-invalid @enderror" rows="4" placeholder="Contoh: Peserta didik dapat mengidentifikasi penyebab masalah sosial dan merumuskan solusi alternatif...">{{ old('tujuan_pembelajaran', $item->tujuan_pembelajaran) }}</textarea>
                                    @error('tujuan_pembelajaran')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        
                        <div class="accordion-item">
                            <h2 class="accordion-header">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#atpSection">
                                    <i class="ti ti-arrows-shuffle me-2"></i>
                                    <strong>3. Alur Tujuan Pembelajaran (ATP)</strong>
                                    <small class="text-muted ms-2">Rangkaian TP yang progresif</small>
                                </button>
                            </h2>
                            <div id="atpSection" class="accordion-collapse collapse" data-parent="#learningAccordion">
                                <div class="accordion-body">
                                    <small class="text-muted d-block mb-2">
                                        <strong>Catatan:</strong> Rangkaian TP yang logis, berkesinambungan, dan progresif. Menunjukkan kedalaman pemahaman, bukan sekadar urutan materi.
                                    </small>
                                    <textarea name="alur_tujuan_pembelajaran" class="form-control @error('alur_tujuan_pembelajaran') is-invalid @enderror" rows="4" placeholder="Contoh: Pertemuan 1-2: Pemahaman... → Pertemuan 3-4: Analisis... → Pertemuan 5-6: Evaluasi dan refleksi...">{{ old('alur_tujuan_pembelajaran', $item->alur_tujuan_pembelajaran) }}</textarea>
                                    @error('alur_tujuan_pembelajaran')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        
                        <div class="accordion-item">
                            <h2 class="accordion-header">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#kkSection">
                                    <i class="ti ti-checks me-2"></i>
                                    <strong>4. Indikator / Kriteria Ketercapaian (KKTP)</strong>
                                    <small class="text-muted ms-2">Ukuran pencapaian TP</small>
                                </button>
                            </h2>
                            <div id="kkSection" class="accordion-collapse collapse" data-parent="#learningAccordion">
                                <div class="accordion-body">
                                    <small class="text-muted d-block mb-2">
                                        <strong>Catatan:</strong> Kriteria Ketercapaian Tujuan Pembelajaran (KKTP). Ukuran apakah TP sudah tercapai, menekankan pemahaman konsep, transfer pengetahuan, dan refleksi.
                                    </small>
                                    <textarea name="indikator_kriteria" class="form-control @error('indikator_kriteria') is-invalid @enderror" rows="4" placeholder="Contoh: • Dapat menjelaskan min 3 penyebab... | • Dapat mengajukan 2 solusi yang realistis... | • Dapat merefleksikan pembelajaran dengan analisis kritis...">{{ old('indikator_kriteria', $item->indikator_kriteria) }}</textarea>
                                    @error('indikator_kriteria')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-primary">
                        <i class="ti ti-device-floppy me-1"></i>Update Komponen Penilaian
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
