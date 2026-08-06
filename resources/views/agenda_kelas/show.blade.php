@extends('layouts.app', ['pageSlug' => 'agenda'])

@section('title','Detail Agenda Kelas')

@section('content')
<script src="https://cdn.jsdelivr.net/npm/tinymce@6.7.0/tinymce.min.js"></script>
<script>
    tinymce.init({
        selector: 'textarea.tiny-editor',
        plugins: 'lists link image table code help wordcount',
        toolbar: 'undo redo | formatselect | bold italic underline | alignleft aligncenter alignright | bullist numlist | link image table | code help',
        height: 250,
        menubar: false,
        statusbar: true,
        license_key: 'gpl',
        content_style: 'body { color: #212529; font-family: inherit; }'
    });
</script>

<div class="container-fluid">
    <div class="row">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header bg-primary">
                    <h4 class="card-title mb-0 text-white">
                        <i class="ti ti-book-2 me-2"></i>Isi Agenda Kelas - {{ $kelas->nama_kelas ?? 'Pengembangan Diri' }}
                    </h4>
                </div>
                <div class="card-body">
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <div class="mb-2">
                                <label class="form-label fw-bold">Kelas</label>
                                <div class="alert alert-light border text-dark">{{ $kelas->nama_kelas ?? '-' }}</div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-2">
                                <label class="form-label fw-bold">Guru Pengajar</label>
                                <div class="alert alert-light border text-dark">{{ $guru->nama }}</div>
                            </div>
                        </div>
                    </div>

                    <div class="row mb-4">
                        <div class="col-md-6">
                            <div class="mb-2">
                                <label class="form-label fw-bold">Tanggal</label>
                                <div class="alert alert-light border text-dark">
                                    {{ \Carbon\Carbon::parse($agenda->tanggal)->locale('id')->translatedFormat('d/m/Y (l)') }}
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-2">
                                <label class="form-label fw-bold">Jam KBM</label>
                                <div class="alert alert-light border text-dark">
                                    {{ $jamBelajar->jam_mulai ?? '-' }} - {{ $jamBelajar->jam_selesai ?? '-' }} 
                                    @if($jamBelajar && !empty($jamBelajar->hari))
                                        <span class="badge bg-info ms-2">{{ $jamBelajar->hari }}</span>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row mb-4">
                        <div class="col-md-6">
                            <div class="mb-2">
                                <label class="form-label fw-bold">Jenis Kegiatan</label>
                                <div class="alert alert-light border text-dark">
                                    {{ ($agenda->jenis_kegiatan ?? 'kbm') === 'pengembangan_diri' ? 'Pengembangan Diri' : 'KBM' }}
                                </div>
                            </div>
                        </div>
                        @if(($agenda->jenis_kegiatan ?? 'kbm') === 'pengembangan_diri')
                        <div class="col-md-6">
                            <div class="mb-2">
                                <label class="form-label fw-bold">Nama Kegiatan</label>
                                <div class="alert alert-light border text-dark">{{ $agenda->nama_kegiatan ?? '-' }}</div>
                            </div>
                        </div>
                        @endif
                    </div>

                    <div class="mb-4">
                        <div class="mb-2">
                            <label class="form-label fw-bold">Kegiatan/Materi</label>
                            <div class="alert alert-light border text-dark" style="min-height: 100px; word-break: break-word;">
                                {!! $agenda->kegiatan ?? '-' !!}
                            </div>
                        </div>
                    </div>

                    <hr>

                    <div class="mb-4">
                        <h5 class="mb-2">
                            <i class="ti ti-clipboard-list me-2"></i>Template Agenda Pembelajaran
                        </h5>
                        
                        <form id="agendaForm" method="POST" action="{{ route('agenda_kelas.store') }}">
                            @csrf
                            <input type="hidden" name="agenda_id" value="{{ $agenda->id }}">

                            <div class="mb-2">
                                <label class="form-label fw-bold">A. Tujuan Pembelajaran</label>
                                <textarea class="form-control tiny-editor" name="tujuan_pembelajaran">{{ old('tujuan_pembelajaran', $agenda->tujuan_pembelajaran ?? '') }}</textarea>
                            </div>

                            <div class="mb-2">
                                <label class="form-label fw-bold">B. Strategi/Metode Pembelajaran</label>
                                <textarea class="form-control tiny-editor" name="strategi_pembelajaran">{{ old('strategi_pembelajaran', $agenda->strategi_pembelajaran ?? '') }}</textarea>
                            </div>

                            <div class="mb-2">
                                <label class="form-label fw-bold">C. Media/Alat Pembelajaran</label>
                                <textarea class="form-control tiny-editor" name="media_pembelajaran">{{ old('media_pembelajaran', $agenda->media_pembelajaran ?? '') }}</textarea>
                            </div>

                            <div class="mb-2">
                                <label class="form-label fw-bold">D. Sumber Belajar</label>
                                <textarea class="form-control tiny-editor" name="sumber_belajar">{{ old('sumber_belajar', $agenda->sumber_belajar ?? '') }}</textarea>
                            </div>

                            <div class="mb-2">
                                <label class="form-label fw-bold">E. Penilaian</label>
                                <textarea class="form-control tiny-editor" name="penilaian">{{ old('penilaian', $agenda->penilaian ?? '') }}</textarea>
                            </div>

                            <div class="mb-2">
                                <label class="form-label fw-bold">F. Catatan Tambahan</label>
                                <textarea class="form-control tiny-editor" name="catatan_tambahan">{{ old('catatan_tambahan', $agenda->catatan_tambahan ?? '') }}</textarea>
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
                    <div class="mb-2">
                        <h6 class="fw-bold text-primary">Tujuan Pembelajaran</h6>
                        <small class="text-muted">Apa yang ingin dicapai siswa setelah mengikuti pembelajaran ini?</small>
                    </div>

                    <div class="mb-2">
                        <h6 class="fw-bold text-primary">Strategi Pembelajaran</h6>
                        <small class="text-muted">Contoh: Ceramah interaktif, diskusi kelompok, pembelajaran berbasis proyek, dll.</small>
                    </div>

                    <div class="mb-2">
                        <h6 class="fw-bold text-primary">Media Pembelajaran</h6>
                        <small class="text-muted">Contoh: Proyektor, whiteboard, video, aplikasi, modul cetak, dll.</small>
                    </div>

                    <div class="mb-2">
                        <h6 class="fw-bold text-primary">Sumber Belajar</h6>
                        <small class="text-muted">Referensi yang digunakan: buku paket, LKS, artikel, website edukatif, dll.</small>
                    </div>

                    <div class="mb-2">
                        <h6 class="fw-bold text-primary">Penilaian</h6>
                        <small class="text-muted">Cara mengukur pencapaian: tes tulis, unjuk kerja, portofolio, dll.</small>
                    </div>

                    <hr>

                    <div class="alert alert-info">
                        <i class="ti ti-info-circle me-1"></i>
                        <small>Isi template ini untuk mendokumentasikan rencana pembelajaran Anda secara terstruktur.</small>
                    </div>

                    @if(($agenda->jenis_kegiatan ?? 'kbm') === 'pengembangan_diri')
                        @php
                            $kegiatanUmumGuru = config('kegiatan_guru.umum', []);
                        @endphp
                        <div class="mt-4">
                            <h6 class="fw-bold text-primary">
                                <i class="ti ti-list-check me-1"></i>Referensi Kegiatan Umum Guru
                            </h6>
                            <div class="d-flex flex-wrap gap-1">
                                @foreach($kegiatanUmumGuru as $kegiatanUmum)
                                    <span class="badge bg-light text-dark border">{{ $kegiatanUmum }}</span>
                                @endforeach
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
