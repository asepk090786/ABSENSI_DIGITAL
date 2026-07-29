@extends('layouts.app', ['pageSlug' => 'agenda_guru'])

@section('title','Tambah Agenda Mengajar Guru')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12 col-lg-8 offset-lg-2">
            <div class="card">
                <div class="card-header border-0 pt-3 pb-2">
                    <h4 class="card-title">
                        <i class="ti ti-plus me-2"></i>Tambah Agenda Mengajar Guru
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
                            <button type="button" class="close" data-bs-dismiss="alert"><span aria-hidden="true">&times;</span></button>
                        </div>
                    @endif

                    <form id="agendaGuruForm" action="{{ route('agenda_guru.store') }}" method="POST">
                        @csrf

                        <div class="mb-2">
                            <label class="form-label">Tanggal <span class="text-danger">*</span></label>
                            <input type="date" class="form-control @error('tanggal') is-invalid @enderror" 
                                   name="tanggal" id="tanggalInput" value="{{ old('tanggal', $selectedTanggal) }}" required>
                            @error('tanggal')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-2">
                            <label class="form-label">Jam Pelajaran <span class="text-danger">*</span></label>
                            @if($jamBelajar->isEmpty())
                                <div class="alert alert-warning small mb-2">Tidak ada jam pelajaran tersedia untuk guru pada tanggal yang dipilih.</div>
                            @endif
                            <select class="form-select @error('jam_belajar_id') is-invalid @enderror" name="jam_belajar_id" required>
                                <option value="">-- Pilih Jam Pelajaran --</option>
                                @foreach($jamBelajar as $jam)
                                    <option value="{{ $jam->id }}" {{ old('jam_belajar_id') == $jam->id ? 'selected' : '' }}>
                                        {{ $jam->jam_mulai }} - {{ $jam->jam_selesai }}
                                        @if($jam->jenis)
                                            ({{ $jam->jenis }})
                                        @endif
                                    </option>
                                @endforeach
                            </select>
                            <small class="text-muted">Jam yang sudah terpakai oleh jadwal guru pada hari yang dipilih tidak ditampilkan.</small>
                            @error('jam_belajar_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-2">
                            <label class="form-label">Rencana Pembelajaran (opsional)</label>
                            <select class="form-select" name="rencana_pembelajaran_id">
                                <option value="">-- Pilih RPP --</option>
                                @foreach($rencanaPembelajaranList as $r)
                                    <option value="{{ $r->id }}" {{ old('rencana_pembelajaran_id') == $r->id ? 'selected' : '' }}>
                                        {{ $r->judul }}
                                    </option>
                                @endforeach
                            </select>
                            <small class="text-muted">Memilih RPP akan mengisi ringkasan kegiatan secara otomatis jika kegiatan kosong.</small>
                        </div>

                        <div class="mb-2">
                            <label class="form-label">Kegiatan / Materi Ajar <span class="text-danger">*</span></label>
                            <select class="form-select mb-2" id="kegiatanSelect">
                                <option value="">-- Pilih (opsional) --</option>
                                @foreach($kegiatanList as $keg)
                                    <option value="{{ $keg->nama_kegiatan }}" {{ old('kegiatan') == $keg->nama_kegiatan ? 'selected' : '' }}>
                                        {{ $keg->nama_kegiatan }} {{ $keg->kode_kegiatan ? '(' . $keg->kode_kegiatan . ')' : '' }}
                                    </option>
                                @endforeach
                                <option value="__other__">Lainnya (ketik sendiri)</option>
                            </select>
                             <textarea name="kegiatan" id="kegiatanOther" class="form-control @error('kegiatan') is-invalid @enderror" rows="7" placeholder="Ketik kegiatan atau materi ajar pada hari ini...">{{ old('kegiatan') }}</textarea>
                            @error('kegiatan')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="text-muted">Maksimal 1000 karakter</small>
                        </div>

                        <div class="d-grid gap-2 d-sm-flex gap-sm-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="ti ti-check me-2"></i>Simpan
                            </button>
                            <a href="{{ route('agenda_guru.index') }}" class="btn btn-outline-secondary btn-modern">
                                <i class="ti ti-x me-2"></i>Batal
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

@push('js')
<script src="https://cdn.ckeditor.com/ckeditor5/39.0.1/classic/ckeditor.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const tanggalInput = document.getElementById('tanggalInput');
    const kegiatanSelect = document.getElementById('kegiatanSelect');
    const kegiatanTextarea = document.getElementById('kegiatanOther');
    const createRoute = '{{ route('agenda_guru.create') }}';
    let kegiatanEditor = null;

    if (tanggalInput) {
        tanggalInput.addEventListener('change', function() {
            if (!this.value) return;
            window.location.href = createRoute + '?tanggal=' + encodeURIComponent(this.value);
        });
    }

    function initKegiatanEditor() {
        if (!kegiatanTextarea || kegiatanEditor) return;
        try {
            ClassicEditor.create(kegiatanTextarea, {
                toolbar: ['heading','bold','italic','link','bulletedList','numberedList','blockQuote','undo','redo']
            }).then(editor => { kegiatanEditor = editor; }).catch(e => console.error(e));
        } catch (e) {
            console.error('CKEditor init error', e);
        }
    }

    initKegiatanEditor();

    if (kegiatanSelect) {
        kegiatanSelect.addEventListener('change', function() {
            const val = this.value;
            if (!kegiatanEditor) return;
            if (val === '__other__' || val === '') {
                if (val === '') return;
                kegiatanEditor.setData('');
            } else {
                kegiatanEditor.setData(val);
            }
        });
    }

    const form = document.getElementById('agendaGuruForm');
    if (form) {
        form.addEventListener('submit', function(e) {
            if (kegiatanEditor) {
                kegiatanEditor.updateSourceElement();
            }
            const kegiatanVal = kegiatanTextarea ? kegiatanTextarea.value.trim() : '';
            const rppSelect = form.querySelector('select[name="rencana_pembelajaran_id"]');
            const rppValue = rppSelect ? rppSelect.value : '';
            
            if (kegiatanVal === '' && rppValue === '') {
                e.preventDefault();
                alert('Kegiatan harus diisi');
                if (kegiatanTextarea) kegiatanTextarea.focus();
            }
        });
    }
});
</script>
@endpush
@endsection
