@extends('layouts.app', ['pageSlug' => 'rencana_pembelajaran'])

@section('title', 'Preview Import Rencana Pembelajaran')

@section('content')
<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header border-0 pt-3 pb-2">
                <div class="row align-items-center">
                    <div class="col">
                        <h4 class="card-title fw-semibold m-0">Preview Import Rencana Pembelajaran</h4>
                        <p class="text-muted mb-0">Periksa hasil parsing dari file Word sebelum menyimpannya ke sistem.</p>
                    </div>
                    <div class="col-auto">
                        <a href="{{ route('rencana_pembelajaran.import_form', ['mata_pelajaran_id' => $mataPelajaran->id, 'tingkat' => $selectedKelas->first()->tingkat_kelas ?? '']) }}" class="btn btn-secondary btn-sm">
                            <i class="ti ti-arrow-left me-1"></i>Kembali</a>
                    </div>
                </div>
            </div>
            <div class="card-body">
                <div class="mb-4">
                    <div class="alert alert-info">
                        <i class="ti ti-info-circle me-2"></i>
                        Periksa dan sunting data jika perlu. File DOCX asli disimpan sebagai sumber resmi.
                    </div>
                </div>

                <div class="mb-4">
                    <h5>File Asli</h5>
                    <p>{{ $fileName }}</p>
                </div>

                <div class="mb-4">
                    <h5>Preview HTML</h5>
                    <div class="card card-body bg-light" style="min-height: 500px;">
                        <iframe id="docxPreviewFrame" style="width:100%; min-height:500px; border:1px solid #d8dce0;" sandbox="allow-same-origin allow-scripts"></iframe>
                    </div>
                </div>

                <form action="{{ route('rencana_pembelajaran.import_confirm') }}" method="POST">
                    @csrf

                    <input type="hidden" name="mata_pelajaran_id" value="{{ $mataPelajaran->id }}">
                    <input type="hidden" name="original_docx_path" value="{{ $originalDocxPath }}">
                    @foreach($selectedKelasIds as $kelasId)
                    <input type="hidden" name="kelas_ids[]" value="{{ $kelasId }}">
                    @endforeach
                    <input type="hidden" name="html_content" value="{{ base64_encode($importData['html_content'] ?? '') }}">

                    <div class="row">
                        <div class="col-md-12 mb-2">
                            <label class="form-label">Judul <span class="text-danger">*</span></label>
                            <input type="text" name="judul" class="form-control" value="{{ old('judul', $importData['judul'] ?? '') }}" required>
                        </div>

                        <div class="col-md-12 mb-2">
                            <label class="form-label">Deskripsi / Capaian Pembelajaran</label>
                            <textarea name="capaian_pembelajaran" class="form-control tiny-editor" rows="3">{{ old('capaian_pembelajaran', $importData['capaian_pembelajaran'] ?? '') }}</textarea>
                        </div>

                        <div class="col-md-12 mb-2">
                            <label class="form-label">Tujuan Pembelajaran</label>
                            <textarea name="tujuan" class="form-control tiny-editor" rows="3">{{ old('tujuan', $importData['tujuan'] ?? '') }}</textarea>
                        </div>

                        <div class="col-md-12 mb-2">
                            <label class="form-label">Metode Pembelajaran</label>
                            <textarea name="metode" class="form-control tiny-editor" rows="2">{{ old('metode', $importData['metode'] ?? '') }}</textarea>
                        </div>

                        <div class="col-md-12 mb-2">
                            <label class="form-label">Media Pembelajaran</label>
                            <textarea name="media" class="form-control tiny-editor" rows="2">{{ old('media', $importData['media'] ?? '') }}</textarea>
                        </div>

                        <div class="col-md-12 mb-2">
                            <label class="form-label">Sumber Belajar</label>
                            <textarea name="sumber" class="form-control tiny-editor" rows="2">{{ old('sumber', $importData['sumber'] ?? '') }}</textarea>
                        </div>

                        <div class="col-md-12 mb-2">
                            <label class="form-label">Alokasi Waktu</label>
                            <input type="text" name="alokasi_waktu" class="form-control" value="{{ old('alokasi_waktu', $importData['alokasi_waktu'] ?? '') }}">
                        </div>

                        <div class="col-md-12 mb-2">
                            <label class="form-label">Praktik Pedagogis</label>
                            <textarea name="praktik_pedagogis" class="form-control tiny-editor" rows="3">{{ old('praktik_pedagogis', $importData['praktik_pedagogis'] ?? '') }}</textarea>
                        </div>

                        <div class="col-md-12 mb-2">
                            <label class="form-label">Lingkungan Pembelajaran</label>
                            <textarea name="lingkungan_pembelajaran" class="form-control tiny-editor" rows="3">{{ old('lingkungan_pembelajaran', $importData['lingkungan_pembelajaran'] ?? '') }}</textarea>
                        </div>

                        <div class="col-md-12 mb-2">
                            <label class="form-label">Pemanfaatan Digital</label>
                            <textarea name="pemanfaatan_digital" class="form-control tiny-editor" rows="3">{{ old('pemanfaatan_digital', $importData['pemanfaatan_digital'] ?? '') }}</textarea>
                        </div>

                        <div class="col-md-12 mb-2">
                            <label class="form-label">Pengalaman Pembelajaran</label>
                            <textarea name="pengalaman_pembelajaran" class="form-control tiny-editor" rows="3">{{ old('pengalaman_pembelajaran', $importData['pengalaman_pembelajaran'] ?? '') }}</textarea>
                        </div>

                        <div class="col-md-12 mb-2">
                            <label class="form-label">Refleksi Pembelajaran</label>
                            <textarea name="refleksi_pembelajaran" class="form-control tiny-editor" rows="3">{{ old('refleksi_pembelajaran', $importData['refleksi_pembelajaran'] ?? '') }}</textarea>
                        </div>

                        <div class="col-md-6 mb-2">
                            <label class="form-label">Status <span class="text-danger">*</span></label>
                            <select name="status" class="form-select" required>
                                <option value="draft" {{ old('status', $importData['status'] ?? 'draft') === 'draft' ? 'selected' : '' }}>Draft</option>
                                <option value="published" {{ old('status', $importData['status'] ?? '') === 'published' ? 'selected' : '' }}>Published</option>
                            </select>
                        </div>
                    </div>

                    <div class="form-footer mt-4">
                        <a href="{{ route('rencana_pembelajaran.import_form', ['mata_pelajaran_id' => $mataPelajaran->id, 'tingkat' => $selectedKelas->first()->tingkat_kelas ?? '']) }}" class="btn btn-link">Ubah File / Kelas</a>
                        <button type="submit" class="btn btn-success">
                            <i class="ti ti-check me-1"></i>Simpan RPP
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

@push('js')
<script src="https://cdn.jsdelivr.net/npm/tinymce@6.7.0/tinymce.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        var iframe = document.getElementById('docxPreviewFrame');
        var htmlContent = {!! json_encode($importData['html_content'] ?? '') !!};

        if (iframe && htmlContent) {
            iframe.srcdoc = htmlContent;
        }

        if (typeof tinymce !== 'undefined') {
            tinymce.init({
                selector: 'textarea.tiny-editor',
                plugins: 'lists link image table code help wordcount',
                toolbar: 'undo redo | formatselect | bold italic underline | alignleft aligncenter alignright | bullist numlist | link image table | code help',
                height: 320,
                menubar: false,
                statusbar: true,
                license_key: 'gpl',
                content_style: 'body { color: #212529; font-family: inherit; }'
            });
        }
    });
</script>
@endpush
@endsection
