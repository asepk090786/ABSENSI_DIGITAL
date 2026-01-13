@extends('layouts.app', ['pageSlug' => 'setting'])

@section('title','Edit Header')

@section('content')
<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header">
                <h4 class="card-title mb-0">Edit Header Print Jadwal</h4>
            </div>
            <div class="card-body">
                @if($errors->any())
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <strong>Terjadi kesalahan:</strong>
                        <ul class="mb-0">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif

                @if(session('success'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <i class="ti ti-check me-2"></i>{{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif

                @if(session('error'))
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <i class="ti ti-alert-circle me-2"></i>{{ session('error') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif

                <div class="row">
                    <div class="col-md-8">
                        <form action="{{ route('setting.header.update') }}" method="POST" enctype="multipart/form-data">
                            @csrf
                            @method('PUT')

                            <!-- Logo Upload Section -->
                            <div class="row mb-4">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="logo_header_kiri" class="form-label">Logo Kiri</label>
                                        @if($sekolah && $sekolah->logo_header_kiri && file_exists(public_path('storage/' . $sekolah->logo_header_kiri)))
                                            <div class="mb-2">
                                                <img src="{{ asset('storage/' . $sekolah->logo_header_kiri) }}" alt="Logo Kiri" style="height: 80px; width: auto;">
                                            </div>
                                        @endif
                                        <input type="file" class="form-control @error('logo_header_kiri') is-invalid @enderror" 
                                            id="logo_header_kiri" name="logo_header_kiri" accept="image/*">
                                        <small class="text-muted d-block mt-2">Format: JPG, PNG, GIF. Ukuran maksimal: 2MB</small>
                                        @error('logo_header_kiri')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="logo" class="form-label">Logo Sekolah</label>
                                        @if($sekolah && $sekolah->logo && file_exists(public_path('storage/' . $sekolah->logo)))
                                            <div class="mb-2">
                                                <img src="{{ asset('storage/' . $sekolah->logo) }}" alt="Logo Sekolah" style="height: 80px; width: auto;">
                                            </div>
                                        @endif
                                        <input type="file" class="form-control @error('logo') is-invalid @enderror" 
                                            id="logo" name="logo" accept="image/*">
                                        <small class="text-muted d-block mt-2">Format: JPG, PNG, GIF. Ukuran maksimal: 2MB</small>
                                        @error('logo')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            <hr class="my-4">

                            <!-- Header Editor Section -->
                            <div class="mb-3">
                                <label for="editor" class="form-label">Edit Header (WYSIWYG)</label>
                                <div id="editor">{!! old('header_html', $sekolah->header_html ?? '') !!}</div>
                                <input type="hidden" id="header_html" name="header_html">
                                <small class="text-muted d-block mt-2">Edit header secara manual seperti di Microsoft Word</small>
                                
                                <!-- Live Preview -->
                                <label class="form-label mt-3 mb-2">Preview Live:</label>
                                <div id="headerPreviewLive">
                                    {!! old('header_html', $sekolah->header_html ?? '<p style="text-align: center; color: #999;">Preview akan tampil di sini</p>') !!}
                                </div>
                            </div>

                            <hr class="my-4">
                            <div class="mb-3">
                                <label for="nama_sekolah" class="form-label">Nama Sekolah / Institusi</label>
                                <input type="text" class="form-control @error('nama_sekolah') is-invalid @enderror" 
                                    id="nama_sekolah" name="nama_sekolah" 
                                    value="{{ old('nama_sekolah', $sekolah->nama_sekolah ?? '') }}" 
                                    placeholder="Contoh: SMA NEGERI 1 PONTANG" required>
                                @error('nama_sekolah')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="alamat_jalan" class="form-label">Alamat / Jalan</label>
                                        <input type="text" class="form-control @error('alamat_jalan') is-invalid @enderror" 
                                            id="alamat_jalan" name="alamat_jalan" 
                                            value="{{ old('alamat_jalan', $sekolah->alamat_jalan ?? '') }}" 
                                            placeholder="Contoh: Jalan Kubang Puji Kec. Pontang">
                                        @error('alamat_jalan')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="telepon" class="form-label">Telepon</label>
                                        <input type="text" class="form-control @error('telepon') is-invalid @enderror" 
                                            id="telepon" name="telepon" 
                                            value="{{ old('telepon', $sekolah->telepon ?? '') }}" 
                                            placeholder="Contoh: (0254) 7931780">
                                        @error('telepon')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="website" class="form-label">Website</label>
                                        <input type="url" class="form-control @error('website') is-invalid @enderror" 
                                            id="website" name="website" 
                                            value="{{ old('website', $sekolah->website ?? '') }}" 
                                            placeholder="Contoh: https://sman1pontang.sch.id">
                                        @error('website')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="email" class="form-label">Email</label>
                                        <input type="email" class="form-control @error('email') is-invalid @enderror" 
                                            id="email" name="email" 
                                            value="{{ old('email', $sekolah->email ?? '') }}" 
                                            placeholder="Contoh: info@sman1pontang.sch.id">
                                        @error('email')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            <hr class="my-4">

                            <!-- Action Buttons -->
                            <div class="d-flex gap-2">
                                <button type="submit" class="btn btn-primary">
                                    <i class="ti ti-check me-1"></i>Simpan Perubahan
                                </button>
                                <a href="{{ route('tahun_ajaran.index') }}" class="btn btn-secondary">
                                    <i class="ti ti-x me-1"></i>Batal
                                </a>
                            </div>
                        </form>
                    </div>

                    <!-- Preview Section -->
                    <div class="col-md-4">
                        <div class="card sticky-top" style="top: 20px;">
                            <div class="card-header">
                                <h5 class="card-title mb-0">Preview Header</h5>
                            </div>
                            <div class="card-body">
                                <div style="border: 2px solid #333; padding: 15px; background: white;">
                                    <!-- Logo Row -->
                                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px; gap: 10px;">
                                        <!-- Logo Kiri -->
                                        <div style="width: 60px; height: 60px; display: flex; align-items: center; justify-content: center;">
                                            @if($sekolah && $sekolah->logo_header_kiri && file_exists(public_path('storage/' . $sekolah->logo_header_kiri)))
                                                <img src="{{ asset('storage/' . $sekolah->logo_header_kiri) }}" alt="Logo Kiri" style="max-height: 60px; max-width: 60px;">
                                            @else
                                                <div style="width: 60px; height: 60px; background: #f0f0f0; display: flex; align-items: center; justify-content: center; font-size: 10px; color: #999;">Logo L</div>
                                            @endif
                                        </div>

                                        <!-- Center Info -->
                                        <div style="text-align: center; flex: 1; padding: 0 10px;">
                                            <h6 style="margin: 0; font-weight: bold; font-size: 11px; line-height: 1.2;">
                                                {{ $sekolah->nama_sekolah ?? 'NAMA SEKOLAH' }}
                                            </h6>
                                            <p style="margin: 5px 0 0 0; font-size: 8px; color: #555;">
                                                {{ $sekolah->alamat_jalan ?? 'Jalan Sekolah' }}
                                            </p>
                                            <p style="margin: 2px 0 0 0; font-size: 8px; color: #555;">
                                                @if($sekolah && ($sekolah->website || $sekolah->email))
                                                    <span>Website: {{ $sekolah->website ?? '-' }}</span><br>
                                                    <span>Email: {{ $sekolah->email ?? '-' }}</span>
                                                @endif
                                            </p>
                                        </div>

                                        <!-- Logo Kanan -->
                                        <div style="width: 60px; height: 60px; display: flex; align-items: center; justify-content: center;">
                                            @if($sekolah && $sekolah->logo && file_exists(public_path('storage/' . $sekolah->logo)))
                                                <img src="{{ asset('storage/' . $sekolah->logo) }}" alt="Logo Sekolah" style="max-height: 60px; max-width: 60px;">
                                            @else
                                                <div style="width: 60px; height: 60px; background: #f0f0f0; display: flex; align-items: center; justify-content: center; font-size: 10px; color: #999;">Logo R</div>
                                            @endif
                                        </div>
                                    </div>
                                    <div style="border-top: 3px double #000; margin-top: 10px;"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('js')
<!-- CKEditor 5 -->
<script src="https://cdn.ckeditor.com/ckeditor5/41.4.2/classic/ckeditor.js"></script>

<style>
    .ck-editor__main {
        min-height: auto;
        max-height: 200px;
        overflow-y: auto;
    }

    .ck.ck-editor__top .ck-sticky-panel__content {
        background: #f8f9fa;
        border-bottom: 1px solid #dee2e6;
    }

    .ck.ck-editor__top .ck-toolbar {
        background: #f8f9fa;
        padding: 8px;
        border-radius: 4px 4px 0 0;
    }

    .ck.ck-content {
        background: white;
        border: 1px solid #dee2e6;
        border-top: none;
        padding: 10px;
        font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
        min-height: auto;
    }

    .ck.ck-editor {
        border: 1px solid #dee2e6;
        border-radius: 4px;
    }

    #headerPreviewLive {
        border: 2px solid #333;
        padding: 15px;
        background: white;
        margin-top: 15px;
    }
</style>

<script>
    // Initialize CKEditor after page fully loaded
    setTimeout(function() {
        const editorElement = document.querySelector('#editor');
        
        if (!editorElement) {
            console.error('Editor element not found');
            return;
        }
        
        if (typeof ClassicEditor === 'undefined') {
            console.error('CKEditor not loaded');
            return;
        }
        
        ClassicEditor
            .create(editorElement, {
                language: 'id',
                toolbar: [
                    'heading',
                    '|',
                    'bold',
                    'italic',
                    'underline',
                    'strikethrough',
                    '|',
                    'alignment',
                    '|',
                    'numberedList',
                    'bulletedList',
                    '|',
                    'fontSize',
                    'fontColor',
                    'fontBackgroundColor',
                    '|',
                    'link',
                    'blockQuote',
                    'insertTable',
                    '|',
                    'undo',
                    'redo',
                    'sourceEditing'
                ],
                htmlSupport: {
                    allow: [{name: /^/, attributes: true, classes: true, styles: true}]
                }
            })
            .then(editor => {
                window.headerEditor = editor;
                console.log('✓ CKEditor initialized successfully');
                
                // Update preview live saat user mengetik
                editor.model.document.on('change:data', function() {
                    const preview = document.querySelector('#headerPreviewLive');
                    if (preview) {
                        preview.innerHTML = editor.getData();
                    }
                    // Juga update hidden input
                    document.querySelector('#header_html').value = editor.getData();
                });
            })
            .catch(error => {
                console.error('CKEditor error:', error);
            });

        // Sync content to hidden input before form submission
        const form = document.querySelector('form');
        if (form) {
            form.addEventListener('submit', function(e) {
                if (window.headerEditor) {
                    document.querySelector('#header_html').value = window.headerEditor.getData();
                }
            });
        }
    }, 500);
</script>
@endpush
