@extends('layouts.app', ['pageSlug' => 'agenda'])

@section('title','Detail Agenda Kelas')

@section('content')
<!-- Summernote CSS -->
<link href="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote-bs5.min.css" rel="stylesheet">
<!-- Summernote JS -->
<script src="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote-bs5.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/summernote@0.8.18/lang/summernote-id-ID.js"></script>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        $('#tujuanEditor, #strategiEditor, #mediaEditor, #sumberEditor, #penilaianEditor, #catatanEditor').summernote({
            placeholder: 'Masukkan konten...',
            height: 250,
            lang: 'id-ID',
            toolbar: [
                ['style', ['style']],
                ['font', ['bold', 'italic', 'underline', 'clear']],
                ['fontname', ['fontname']],
                ['color', ['color']],
                ['para', ['ul', 'ol', 'paragraph']],
                ['table', ['table']],
                ['insert', ['link']],
                ['view', ['fullscreen', 'codeview', 'help']]
            ]
        });
    });
</script>

<div class="container-fluid">
    <div class="row">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header bg-primary">
                    <h4 class="card-title mb-0 text-white">
                        <i class="ti ti-book-2 me-2"></i>Isi Agenda Kelas - {{ $kelas->nama_kelas }}
                    </h4>
                </div>
                <div class="card-body">
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label fw-bold">Kelas</label>
                                <div class="alert alert-light border">{{ $kelas->nama_kelas }}</div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label fw-bold">Guru Pengajar</label>
                                <div class="alert alert-light border">{{ $guru->nama }}</div>
                            </div>
                        </div>
                    </div>

                    <div class="row mb-4">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label fw-bold">Tanggal</label>
                                <div class="alert alert-light border">
                                    {{ \Carbon\Carbon::parse($agenda->tanggal)->format('d/m/Y (l)', locale: 'id') }}
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label fw-bold">Jam KBM</label>
                                <div class="alert alert-light border">
                                    {{ $jamBelajar->jam_mulai }} - {{ $jamBelajar->jam_selesai }} 
                                    <span class="badge bg-info ms-2">{{ $jamBelajar->hari }}</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="mb-4">
                        <div class="mb-3">
                            <label class="form-label fw-bold">Kegiatan/Materi</label>
                            <div class="alert alert-light border" style="min-height: 100px; word-break: break-word;">
                                {!! $agenda->kegiatan ?? '-' !!}
                            </div>
                        </div>
                    </div>

                    <hr>

                    <div class="mb-4">
                        <h5 class="mb-3">
                            <i class="ti ti-clipboard-list me-2"></i>Template Agenda Pembelajaran
                        </h5>
                        
                        <form id="agendaForm" method="POST" action="{{ route('agenda_kelas.store') }}">
                            @csrf
                            <input type="hidden" name="agenda_id" value="{{ $agenda->id }}">

                            <div class="mb-3">
                                <label class="form-label fw-bold">A. Tujuan Pembelajaran</label>
                                <textarea id="tujuanEditor" class="form-control" name="tujuan_pembelajaran">{{ old('tujuan_pembelajaran', $agenda->tujuan_pembelajaran ?? '') }}</textarea>
                            </div>

                            <div class="mb-3">
                                <label class="form-label fw-bold">B. Strategi/Metode Pembelajaran</label>
                                <textarea id="strategiEditor" class="form-control" name="strategi_pembelajaran">{{ old('strategi_pembelajaran', $agenda->strategi_pembelajaran ?? '') }}</textarea>
                            </div>

                            <div class="mb-3">
                                <label class="form-label fw-bold">C. Media/Alat Pembelajaran</label>
                                <textarea id="mediaEditor" class="form-control" name="media_pembelajaran">{{ old('media_pembelajaran', $agenda->media_pembelajaran ?? '') }}</textarea>
                            </div>

                            <div class="mb-3">
                                <label class="form-label fw-bold">D. Sumber Belajar</label>
                                <textarea id="sumberEditor" class="form-control" name="sumber_belajar">{{ old('sumber_belajar', $agenda->sumber_belajar ?? '') }}</textarea>
                            </div>

                            <div class="mb-3">
                                <label class="form-label fw-bold">E. Penilaian</label>
                                <textarea id="penilaianEditor" class="form-control" name="penilaian">{{ old('penilaian', $agenda->penilaian ?? '') }}</textarea>
                            </div>

                            <div class="mb-3">
                                <label class="form-label fw-bold">F. Catatan Tambahan</label>
                                <textarea id="catatanEditor" class="form-control" name="catatan_tambahan">{{ old('catatan_tambahan', $agenda->catatan_tambahan ?? '') }}</textarea>
                            </div>

                            <div class="d-flex gap-2">
                                <button type="submit" class="btn btn-primary">
                                    <i class="ti ti-check me-1"></i>Simpan Agenda
                                </button>
                                <a href="{{ route('agenda_kelas.index') }}" class="btn btn-secondary">
                                    <i class="ti ti-arrow-left me-1"></i>Kembali
                                </a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card bg-light">
                <div class="card-header bg-info">
                    <h5 class="card-title mb-0 text-white">
                        <i class="ti ti-help-circle me-2"></i>Panduan Pengisian
                    </h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <h6 class="fw-bold text-primary">Tujuan Pembelajaran</h6>
                        <small class="text-muted">Apa yang ingin dicapai siswa setelah mengikuti pembelajaran ini?</small>
                    </div>

                    <div class="mb-3">
                        <h6 class="fw-bold text-primary">Strategi Pembelajaran</h6>
                        <small class="text-muted">Contoh: Ceramah interaktif, diskusi kelompok, pembelajaran berbasis proyek, dll.</small>
                    </div>

                    <div class="mb-3">
                        <h6 class="fw-bold text-primary">Media Pembelajaran</h6>
                        <small class="text-muted">Contoh: Proyektor, whiteboard, video, aplikasi, modul cetak, dll.</small>
                    </div>

                    <div class="mb-3">
                        <h6 class="fw-bold text-primary">Sumber Belajar</h6>
                        <small class="text-muted">Referensi yang digunakan: buku paket, LKS, artikel, website edukatif, dll.</small>
                    </div>

                    <div class="mb-3">
                        <h6 class="fw-bold text-primary">Penilaian</h6>
                        <small class="text-muted">Cara mengukur pencapaian: tes tulis, unjuk kerja, portofolio, dll.</small>
                    </div>

                    <hr>

                    <div class="alert alert-info">
                        <i class="ti ti-info-circle me-1"></i>
                        <small>Isi template ini untuk mendokumentasikan rencana pembelajaran Anda secara terstruktur.</small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
