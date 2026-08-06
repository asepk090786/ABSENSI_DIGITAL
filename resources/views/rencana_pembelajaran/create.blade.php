@extends('layouts.app', ['pageSlug' => 'rencana_pembelajaran'])

@section('title', 'Tambah Rencana Pembelajaran')

@section('content')
<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header border-0 pt-3 pb-2">
                <h4 class="card-title">Tambah Rencana Pembelajaran</h4>
            </div>
            <div class="card-body">
                <form action="{{ route('rencana_pembelajaran.store') }}" method="POST">
                    @csrf
                    <input type="hidden" name="mata_pelajaran_id" value="{{ $mataPelajaran->id }}">

                    <div class="row">
                        <div class="col-md-12 mb-3">
                            <h5 class="mb-2">1. Informasi Umum</h5>
                            <div class="mb-2">
                                <label class="form-label">Mata Pelajaran</label>
                                <input type="text" class="form-control" value="{{ $mataPelajaran->nama_mapel }}" disabled>
                            </div>
                            <div class="mb-2 @error('kelas_ids') is-invalid @enderror">
                                <label class="form-label">Kelas <span class="text-danger">*</span></label>
                                @forelse($kelas as $k)
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="kelas_ids[]" value="{{ $k->id }}" id="kelas_{{ $k->id }}" {{ in_array($k->id, old('kelas_ids', [])) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="kelas_{{ $k->id }}">{{ $k->nama_kelas }}</label>
                                    </div>
                                @empty
                                    <div class="alert alert-info alert-sm mb-0">
                                        <i class="ti ti-info-circle me-2"></i>Belum ada kelas untuk mata pelajaran ini
                                    </div>
                                @endforelse
                                @error('kelas_ids')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="mb-2">
                                <label class="form-label">Judul <span class="text-danger">*</span></label>
                                <input type="text" name="judul" class="form-control @error('judul') is-invalid @enderror" value="{{ old('judul') }}" required>
                                @error('judul')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="col-md-12 mb-3">
                            <div class="card card-body p-3 border-0 shadow-sm">
                                <h5 class="mb-2">2. Editor Dokumen</h5>
                                <div class="form-text text-muted mb-3">
                                    Editor sederhana: masukkan isi RPP di bawah ini. (OnlyOffice telah dinonaktifkan.)
                                </div>
                                <div class="mb-2">
                                    <label class="form-label">Isi Rencana Pembelajaran</label>
                                    <textarea name="capaian_pembelajaran" class="form-control" rows="18">{{ old('capaian_pembelajaran') }}</textarea>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-12 mb-3">
                            <label class="form-label">Komponen Penilaian</label>
                            <div class="@error('komponen_nilai_ids') is-invalid @enderror">
                                @forelse($komponenNilai as $komponen)
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="komponen_nilai_ids[]" value="{{ $komponen->id }}" id="komponen_{{ $komponen->id }}" {{ in_array($komponen->id, old('komponen_nilai_ids', [])) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="komponen_{{ $komponen->id }}">
                                            {{ $komponen->nama_komponen }}
                                            @if($komponen->bobot)
                                                <span class="text-muted">({{ $komponen->bobot }}%)</span>
                                            @endif
                                        </label>
                                    </div>
                                @empty
                                    <div class="alert alert-info alert-sm mb-0">
                                        <i class="ti ti-info-circle me-2"></i>Belum ada komponen penilaian
                                    </div>
                                @endforelse
                            </div>
                            @error('komponen_nilai_ids')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6 mb-2">
                            <label class="form-label">Status <span class="text-danger">*</span></label>
                            <select name="status" class="form-select @error('status') is-invalid @enderror" required>
                                <option value="draft" {{ old('status') === 'draft' ? 'selected' : '' }}>Draft</option>
                                <option value="published" {{ old('status') === 'published' ? 'selected' : '' }}>Published</option>
                            </select>
                            @error('status')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="form-footer">
                        <a href="{{ route('rencana_pembelajaran.index', ['mata_pelajaran_id' => $mataPelajaran->id, 'tingkat' => $tingkat]) }}" class="btn btn-link">Batal</a>
                        <button type="submit" class="btn btn-primary">
                            <i class="ti ti-check me-1"></i>Simpan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

@push('js')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const editorContainer = document.getElementById('onlyoffice-editor');
        const docKey = '{{ $docKey }}';
        const docTitle = '{{ $docTitle }}';
        const originHost = window.location.origin;
        const fileUrl = originHost + '{{ route('rencana_pembelajaran.onlyoffice_file', ['docKey' => $docKey], false) }}';
        const callbackUrl = originHost + '{{ route('rencana_pembelajaran.onlyoffice_callback', [], false) }}';

        const onlyOfficeHost = @json(env('ONLYOFFICE_SERVER_HOST', '')) || originHost;
        const onlyOfficeScriptUrl = onlyOfficeHost + '/web-apps/apps/api/documents/api.js';

        console.log('OnlyOffice script URL:', onlyOfficeScriptUrl);
        console.log('OnlyOffice fileUrl:', fileUrl);
        console.log('OnlyOffice callbackUrl:', callbackUrl);

        function showOnlyOfficeError(message) {
            if (!editorContainer) return;
            editorContainer.innerHTML = `
                <div class="alert alert-danger m-0" style="height:100%; display:flex; flex-direction:column; justify-content:center; align-items:center; text-align:center; padding:40px;">
                    <i class="ti ti-alert-triangle" style="font-size:48px; margin-bottom:16px;"></i>
                    <h4 class="mb-2">Gagal memuat Editor Dokumen</h4>
                    <p class="mb-3">${message}</p>
                    <a href="${fileUrl}" class="btn btn-outline-primary" download>
                        <i class="ti ti-download me-1"></i>Download Template DOCX
                    </a>
                    <p class="text-muted small mt-3">Pastikan server OnlyOffice berjalan dan dapat diakses, lalu muat ulang halaman ini.</p>
                </div>
            `;
        }

        function loadOnlyOfficeScript(src, callback) {
            const script = document.createElement('script');
            script.src = src;
            script.async = true;
            script.onload = callback;
            script.onerror = function () {
                console.error('Gagal memuat OnlyOffice API dari', src);
                showOnlyOfficeError('OnlyOffice API tidak berhasil dimuat dari: ' + src);
            };
            document.head.appendChild(script);
        }

        function initOnlyOffice() {
            if (typeof DocsAPI === 'undefined') {
                showOnlyOfficeError('OnlyOffice API tidak berhasil dimuat setelah script selesai.');
                return;
            }

            const config = {
                width: '100%',
                height: '800px',
                type: 'desktop',
                documentType: 'word',
                document: {
                    title: docTitle,
                    url: fileUrl,
                    fileType: 'docx',
                    key: docKey
                },
                editorConfig: {
                    callbackUrl: callbackUrl,
                    lang: 'id',
                    mode: 'edit',
                    customization: {
                        forcesave: true,
                        chat: false,
                        comments: false,
                        toolbarNoTabs: false
                    },
                    permissions: {
                        edit: true,
                        download: true,
                        print: true
                    }
                },
                events: {
                    onError: function(event) {
                        console.error('OnlyOffice error:', event);
                        showOnlyOfficeError('Terjadi kesalahan pada editor OnlyOffice: ' + (event.data ? event.data.message : 'Unknown error'));
                    }
                }
            };

            try {
                new DocsAPI.DocEditor('onlyoffice-editor', config);
            } catch (e) {
                console.error('Gagal inisialisasi OnlyOffice:', e);
                showOnlyOfficeError('Gagal menginisialisasi editor: ' + e.message);
            }
        }

        loadOnlyOfficeScript(onlyOfficeScriptUrl, initOnlyOffice);
    });
</script>
@endpush
@endsection
