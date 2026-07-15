@extends('layouts.app', ['pageSlug' => 'setting'])

@section('title','Edit Header')

@section('content')
<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header border-0 pt-3 pb-2">
                <h4 class="card-title fw-semibold m-0">Edit Header Print Jadwal</h4>
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
                        <button type="button" class="close" data-bs-dismiss="alert"><span aria-hidden="true">&times;</span></button>
                    </div>
                @endif

                @if(session('success'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <i class="ti ti-check me-2"></i>{{ session('success') }}
                        <button type="button" class="close" data-bs-dismiss="alert"><span aria-hidden="true">&times;</span></button>
                    </div>
                @endif

                @if(session('error'))
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <i class="ti ti-alert-circle me-2"></i>{{ session('error') }}
                        <button type="button" class="close" data-bs-dismiss="alert"><span aria-hidden="true">&times;</span></button>
                    </div>
                @endif

                <div class="row">
                    <div class="col-md-8">
                        <form action="{{ route('setting.header.update') }}" method="POST" enctype="multipart/form-data">
                            @csrf
                            @method('PUT')

                            
                            <div class="row mb-4">
                                <div class="col-md-6">
                                    <div class="mb-2">
                                        <label for="logo_header_kiri" class="form-label">Logo Kiri</label>
                                        @if($sekolah && $sekolah->logo_header_kiri && \Illuminate\Support\Facades\Storage::disk('public')->exists($sekolah->logo_header_kiri))
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
                                    <div class="mb-2">
                                        <label for="logo" class="form-label">Logo Sekolah</label>
                                        @if($sekolah && $sekolah->logo && \Illuminate\Support\Facades\Storage::disk('public')->exists($sekolah->logo))
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
                            
                            
                            <div class="mb-4">
                                <h5 class="mb-2">Text Header (4 Baris)</h5>
                                <p class="text-muted small mb-2">
                                    <i class="ti ti-info-circle"></i> 
                                    Gunakan editor untuk mengatur font, ukuran, warna, bold, italic, underline, dll.
                                </p>
                                
                                
                                <div class="mb-2">
                                    <label for="header_line1" class="form-label">Baris 1 (Header Atas)</label>
                                    <textarea id="header_line1" name="header_line1" class="summernote">{{ old('header_line1', $sekolah->header_line1 ?? 'PEMERINTAH PROVINSI BANTEN') }}</textarea>
                                    <div class="mt-2">
                                        <label for="header_line1_spacing" class="form-label small">Line Spacing</label>
                                        <input type="number" class="form-control form-control-sm" id="header_line1_spacing" name="header_line1_spacing" 
                                            value="{{ old('header_line1_spacing', $sekolah->header_line1_spacing ?? 1.0) }}" 
                                            min="0.1" max="5" step="0.1" style="max-width: 150px;">
                                        <small class="text-muted">Rentang: 0.1 - 5.0 (default: 1.0)</small>
                                    </div>
                                    @error('header_line1')
                                        <div class="text-danger small mt-1">{{ $message }}</div>
                                    @enderror
                                </div>

                                
                                <div class="mb-2">
                                    <label for="header_line2" class="form-label">Baris 2 (Nama Sekolah)</label>
                                    <textarea id="header_line2" name="header_line2" class="summernote">{{ old('header_line2', $sekolah->header_line2 ?? $sekolah->nama_sekolah ?? 'SMA NEGERI 1 PONTANG') }}</textarea>
                                    <div class="mt-2">
                                        <label for="header_line2_spacing" class="form-label small">Line Spacing</label>
                                        <input type="number" class="form-control form-control-sm" id="header_line2_spacing" name="header_line2_spacing" 
                                            value="{{ old('header_line2_spacing', $sekolah->header_line2_spacing ?? 1.0) }}" 
                                            min="0.1" max="5" step="0.1" style="max-width: 150px;">
                                        <small class="text-muted">Rentang: 0.1 - 5.0 (default: 1.0)</small>
                                    </div>
                                    @error('header_line2')
                                        <div class="text-danger small mt-1">{{ $message }}</div>
                                    @enderror
                                </div>

                                
                                <div class="mb-2">
                                    <label for="header_line3" class="form-label">Baris 3 (Nama Sekolah)</label>
                                    <textarea id="header_line3" name="header_line3" class="summernote">{{ old('header_line3', $sekolah->header_line3 ?? $sekolah->nama_sekolah ?? 'Nama Sekolah') }}</textarea>
                                    <div class="mt-2">
                                        <label for="header_line3_spacing" class="form-label small">Line Spacing</label>
                                        <input type="number" class="form-control form-control-sm" id="header_line3_spacing" name="header_line3_spacing" 
                                            value="{{ old('header_line3_spacing', $sekolah->header_line3_spacing ?? 1.0) }}" 
                                            min="0.1" max="5" step="0.1" style="max-width: 150px;">
                                        <small class="text-muted">Rentang: 0.1 - 5.0 (default: 1.0)</small>
                                    </div>
                                    @error('header_line3')
                                        <div class="text-danger small mt-1">{{ $message }}</div>
                                    @enderror
                                </div>

                                
                                <div class="mb-2">
                                    <label for="header_line4" class="form-label">Baris 4 (Info Tambahan)</label>
                                    <textarea id="header_line4" name="header_line4" class="summernote">{{ old('header_line4', $sekolah->header_line4 ?? '') }}</textarea>
                                    <div class="mt-2">
                                        <label for="header_line4_spacing" class="form-label small">Line Spacing</label>
                                        <input type="number" class="form-control form-control-sm" id="header_line4_spacing" name="header_line4_spacing" 
                                            value="{{ old('header_line4_spacing', $sekolah->header_line4_spacing ?? 1.0) }}" 
                                            min="0.1" max="5" step="0.1" style="max-width: 150px;">
                                        <small class="text-muted">Rentang: 0.1 - 5.0 (default: 1.0)</small>
                                    </div>
                                    @error('header_line4')
                                        <div class="text-danger small mt-1">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <hr class="my-4">

                            
                            <input type="hidden" name="header_html" value="">

                            
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

                    
                    <div class="col-md-4">
                        <div class="card sticky-top" style="top: 20px;">
                            <div class="card-header border-0 pt-3 pb-2">
                                <h5 class="card-title fw-semibold m-0">Preview Header</h5>
                            </div>
                            <div class="card-body">
                                <div style="border: 2px solid #333; padding: 10px; background: white;">
                                    
                                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 5px; gap: 10px;">
                                        
                                        <div style="width: 60px; height: 60px; display: flex; align-items: center; justify-content: center; overflow: hidden;" id="preview-logo-kiri-container">
                                            @if($sekolah && $sekolah->logo_header_kiri)
                                                <img id="preview-logo-kiri" src="{{ asset('storage/' . $sekolah->logo_header_kiri) }}" alt="Logo Kiri" style="max-height: 60px; max-width: 60px; object-fit: contain;">
                                            @else
                                                <div id="preview-logo-kiri-placeholder" style="width: 60px; height: 60px; background: #f0f0f0; display: flex; align-items: center; justify-content: center; font-size: 10px; color: #999;">Logo L</div>
                                            @endif
                                        </div>

                                        
                                        <div style="text-align: center; flex: 1; padding: 0 10px;" id="preview-header-text">
                                            <div style="margin: 0; font-size: 9px; line-height: {{ $sekolah->header_line1_spacing ?? 1.0 }};" id="preview-line1">
                                                {!! $sekolah->header_line1 ?? '<span>PEMERINTAH PROVINSI BANTEN</span>' !!}
                                            </div>
                                            <div style="margin: 2px 0 0 0; font-size: 11px; line-height: {{ $sekolah->header_line2_spacing ?? 1.0 }};" id="preview-line2">
                                                {!! $sekolah->header_line2 ?? '<strong>' . ($sekolah->nama_sekolah ?? 'NAMA SEKOLAH') . '</strong>' !!}
                                            </div>
                                            <div style="margin: 2px 0 0 0; font-size: 8px; line-height: {{ $sekolah->header_line3_spacing ?? 1.0 }}; color: #555;" id="preview-line3">
                                                {!! $sekolah->header_line3 ?? '<span>' . ($sekolah->alamat_jalan ?? 'Alamat Sekolah') . '</span>' !!}
                                            </div>
                                            @if($sekolah && $sekolah->header_line4)
                                            <div style="margin: 2px 0 0 0; padding: 0; font-size: 7px; line-height: {{ $sekolah->header_line4_spacing ?? 1.0 }}; color: #666; word-break: break-word;" id="preview-line4">
                                                {!! $sekolah->header_line4 !!}
                                            </div>
                                            @else
                                            <div style="margin: 2px 0 0 0; padding: 0; font-size: 7px; line-height: 1.0; color: #666; display: none; word-break: break-word;" id="preview-line4"></div>
                                            @endif
                                        </div>

                                        
                                        <div style="width: 60px; height: 60px; display: flex; align-items: center; justify-content: center; overflow: hidden;" id="preview-logo-kanan-container">
                                            @if($sekolah && $sekolah->logo)
                                                <img id="preview-logo-kanan" src="{{ asset('storage/' . $sekolah->logo) }}" alt="Logo Sekolah" style="max-height: 60px; max-width: 60px; object-fit: contain;">
                                            @else
                                                <div id="preview-logo-kanan-placeholder" style="width: 60px; height: 60px; background: #f0f0f0; display: flex; align-items: center; justify-content: center; font-size: 10px; color: #999;">Logo R</div>
                                            @endif
                                        </div>
                                    </div>
                                    <div style="border-top: 3px double #000; margin-top: 0;"></div>
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

@push('css')

<link href="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote-lite.min.css" rel="stylesheet">
<style>
    .note-editor.note-frame {
        border: 1px solid #dee2e6;
        border-radius: 0.25rem;
    }
    .note-editor.note-frame .note-editing-area .note-editable {
        min-height: 80px;
        font-family: Arial, sans-serif;
    }
    .note-editor.note-frame .note-editing-area .note-editable p,
    .note-editor.note-frame .note-editing-area .note-editable span,
    .note-editor.note-frame .note-editing-area .note-editable strong,
    .note-editor.note-frame .note-editing-area .note-editable em {
        font-family: Arial, sans-serif;
    }
</style>
@endpush

@push('js')

<script src="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote-lite.min.js"></script>
<script>
$(document).ready(function() {
    // Custom line spacing button untuk Summernote
    const lineSpacingValues = ['0.5', '0.8', '1.0', '1.2', '1.5', '1.8', '2.0', '2.5', '3.0', '3.5', '4.0', '4.5', '5.0'];
    
    // Create custom button untuk line spacing
    $.extend($.summernote.plugins, {
        'lineSpacing': function(context) {
            var ui = $.summernote.ui;
            
            context.memo('button.lineSpacing', function() {
                return ui.button({
                    className: 'note-btn btn-default btn-sm',
                    contents: '<i class="ti ti-line-height"></i>',
                    tooltip: 'Line Spacing',
                    click: function() {
                        var $menu = $('<div class="note-dropdown-menu" role="menu">');
                        lineSpacingValues.forEach(function(value) {
                            var $item = $('<a href="#" class="note-dropdown-item" data-line-spacing="' + value + '" style="padding: 5px 10px; display: block; text-decoration: none; color: #333;">Line ' + value + '</a>');
                            $item.click(function(e) {
                                e.preventDefault();
                                context.invoke('editor.createRange');
                                document.execCommand('styleWithCSS', false, true);
                                document.execCommand('insertHTML', false, '<span style="line-height: ' + value + ';">' + window.getSelection().toString() + '</span>');
                                context.invoke('editor.focus');
                                return false;
                            });
                            $menu.append($item);
                        });
                        
                        context.invoke('popover.show', $menu, $(this));
                    }
                }).render();
            });
        }
    });
    
    // Initialize Summernote untuk 4 baris header
    $('.summernote').summernote({
        height: 100,
        toolbar: [
            ['style', ['style']],
            ['font', ['bold', 'italic', 'underline', 'clear']],
            ['fontname', ['fontname']],
            ['fontsize', ['fontsize']],
            ['color', ['color']],
            ['lineSpacing', ['lineSpacing']],
            ['para', ['ul', 'ol', 'paragraph']],
            ['height', ['height']],
            ['view', ['codeview']]
        ],
        fontNames: ['Arial', 'Arial Black', 'Comic Sans MS', 'Courier New', 'Times New Roman', 'Calibri', 'Georgia', 'Verdana', 'Tahoma'],
        fontSizes: ['8', '9', '10', '11', '12', '14', '16', '18', '20', '24', '28', '32', '36'],
        placeholder: 'Ketik teks header di sini...',
        plugins: ['lineSpacing'],
        callbacks: {
            onChange: function(contents) {
                updatePreview();
            }
        }
    });
    
    // Function untuk update preview
    function updatePreview() {
        const line1 = $('#header_line1').summernote('code');
        const line2 = $('#header_line2').summernote('code');
        const line3 = $('#header_line3').summernote('code');
        const line4 = $('#header_line4').summernote('code');
        
        const spacing1 = $('#header_line1_spacing').val() || 1.0;
        const spacing2 = $('#header_line2_spacing').val() || 1.0;
        const spacing3 = $('#header_line3_spacing').val() || 1.0;
        const spacing4 = $('#header_line4_spacing').val() || 1.0;
        
        $('#preview-line1').html(line1 || '<span>PEMERINTAH PROVINSI BANTEN</span>').css('line-height', spacing1);
        $('#preview-line2').html(line2 || '<strong>NAMA SEKOLAH</strong>').css('line-height', spacing2);
        $('#preview-line3').html(line3 || '<span>Alamat Sekolah</span>').css('line-height', spacing3);
        
        if (line4 && line4.trim() !== '') {
            const $line4 = $('#preview-line4');
            $line4.html(line4).css('line-height', spacing4).show();
            $line4.find('*').css('line-height', spacing4);
            $line4.find('br').css('line-height', spacing4).css('display', 'block').css('height', (spacing4 * 0.75) + 'em');
        } else {
            $('#preview-line4').hide();
        }
    }
    
    // Event listener untuk line spacing inputs
    $('#header_line1_spacing, #header_line2_spacing, #header_line3_spacing, #header_line4_spacing').on('input change', function() {
        updatePreview();
    });
    
    // Initial preview update
    updatePreview();
    
    // Preview logo kiri ketika file dipilih
    document.getElementById('logo_header_kiri').addEventListener('change', function(e) {
        const file = e.target.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function(event) {
                const imgElement = document.getElementById('preview-logo-kiri');
                const placeholderElement = document.getElementById('preview-logo-kiri-placeholder');
                
                imgElement.src = event.target.result;
                imgElement.style.display = 'block';
                if (placeholderElement) {
                    placeholderElement.style.display = 'none';
                }
            };
            reader.readAsDataURL(file);
        }
    });
    
    // Preview logo kanan (sekolah) ketika file dipilih
    document.getElementById('logo').addEventListener('change', function(e) {
        const file = e.target.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function(event) {
                const imgElement = document.getElementById('preview-logo-kanan');
                const placeholderElement = document.getElementById('preview-logo-kanan-placeholder');
                
                imgElement.src = event.target.result;
                imgElement.style.display = 'block';
                if (placeholderElement) {
                    placeholderElement.style.display = 'none';
                }
            };
            reader.readAsDataURL(file);
        }
    });
});
</script>
@endpush
