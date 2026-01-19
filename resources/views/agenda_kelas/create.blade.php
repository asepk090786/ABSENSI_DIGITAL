@extends('layouts.app', ['pageSlug' => 'agenda'])

@section('title','Tambah Agenda Kelas')

@section('content')
<script src="https://cdn.tiny.cloud/1/4ctq7tixbpx5atyue5htuo32gh3znc6tn98y7jfmhdzrp5q9/tinymce/6/tinymce.min.js"></script>
<script>
    tinymce.init({
        selector: 'textarea.tiny-editor',
        plugins: 'lists link image table code help wordcount',
        toolbar: 'undo redo | formatselect | bold italic underline | alignleft aligncenter alignright | bullist numlist | link image table | code help',
        language: 'id',
        height: 300,
        menubar: false,
        statusbar: true,
        license_key: 'gpl',
        content_style: 'body { color: #212529; font-family: inherit; }'
    });
</script>

<div class="row">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header bg-primary">
                <h4 class="card-title mb-0 text-white">
                    <i class="ti ti-plus me-2"></i>Tambah Agenda Kelas
                </h4>
            </div>
            <div class="card-body">
                @if($errors->any())
                    <div class="alert alert-danger">
                        <ul class="mb-0">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form method="POST" action="{{ route('agenda_kelas.store') }}">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label fw-bold">Kelas</label>
                        <select name="kelas_id" class="form-select @error('kelas_id') is-invalid @enderror" required id="kelasSelect">
                            <option value="">-- Pilih Kelas --</option>
                            @forelse($kelas as $k)
                                <option value="{{ $k->id }}" @if($selectedKelasId == $k->id || request('kelas_id') == $k->id || old('kelas_id') == $k->id) selected @endif>
                                    {{ $k->nama_kelas ?? 'Kelas '.$k->id }}
                                </option>
                            @empty
                                <option disabled>Tidak ada kelas sesuai jadwal mengajar Anda</option>
                            @endforelse
                        </select>
                        @error('kelas_id')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">Guru</label>
                        <select name="guru_id" class="form-select @error('guru_id') is-invalid @enderror" required>
                            <option value="{{ $guru->id }}">{{ $guru->nama ?? 'Guru '.$guru->id }} (Anda)</option>
                        </select>
                        @error('guru_id')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">Jam KBM</label>
                        <select name="jam_belajar_id" class="form-select @error('jam_belajar_id') is-invalid @enderror" required id="jamSelect">
                            <option value="">-- Pilih Jam KBM --</option>
                            @foreach($jam as $j)
                                <option value="{{ $j->id }}" data-hari="{{ $j->hari }}" 
                                    @if($selectedJamData && $selectedJamData->id == $j->id) selected @endif
                                    @if(old('jam_belajar_id') == $j->id) selected @endif>
                                    {{ $j->hari }} - Jam Ke-{{ $j->urutan }} ({{ $j->jam_mulai }} - {{ $j->jam_selesai }} | {{ $j->jenis }})
                                </option>
                            @endforeach
                        </select>
                        @error('jam_belajar_id')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">Tanggal</label>
                        <input type="date" name="tanggal" class="form-control @error('tanggal') is-invalid @enderror" 
                               value="{{ old('tanggal', $suggestedDate ?? now()->format('Y-m-d')) }}" required id="tanggalInput">
                        @error('tanggal')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">Kegiatan/Materi</label>
                        <textarea name="kegiatan" class="form-control tiny-editor @error('kegiatan') is-invalid @enderror">{{ old('kegiatan') }}</textarea>
                        @error('kegiatan')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                    </div>

                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="ti ti-check me-1"></i>Lanjut Isi Template Agenda
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
@endsection
