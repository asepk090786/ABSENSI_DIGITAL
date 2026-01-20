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

                    <div class="mb-3" id="multipleJamInfo" style="display: none;">
                        <div class="alert alert-info">
                            <i class="ti ti-info-circle me-2"></i>
                            <strong>Info:</strong> Kelas ini memiliki lebih dari 1 jam KBM dengan guru Anda. 
                            <span id="jamCountInfo"></span>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="applyToAllJam" name="apply_to_all_jam" value="1">
                            <label class="form-check-label" for="applyToAllJam">
                                <strong>Terapkan agenda ini ke SEMUA jam KBM kelas yang sama pada hari yang sama</strong>
                                <br><small class="text-muted">Jika dicentang, agenda akan otomatis disalin ke semua jam KBM lainnya untuk kelas ini pada tanggal yang dipilih</small>
                            </label>
                        </div>
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

<script>
document.addEventListener('DOMContentLoaded', function() {
    const kelasSelect = document.getElementById('kelasSelect');
    const jamSelect = document.getElementById('jamSelect');
    const multipleJamInfo = document.getElementById('multipleJamInfo');
    const jamCountInfo = document.getElementById('jamCountInfo');
    const applyToAllJam = document.getElementById('applyToAllJam');
    
    // Data jadwal dari server (kelas_id -> [jam_belajar_id, jam_belajar_id, ...])
    const jadwalMap = {
        @foreach($kelas as $k)
            @php
                $jamForKelas = \Illuminate\Support\Facades\DB::table('jadwal_kbm')
                    ->where('guru_id', auth()->user()->guru->id)
                    ->where('kelas_id', $k->id)
                    ->pluck('jam_belajar_id')
                    ->toArray();
            @endphp
            '{{ $k->id }}': {!! json_encode($jamForKelas) !!},
        @endforeach
    };

    function checkMultipleJam() {
        const selectedKelasId = kelasSelect.value;
        
        if (!selectedKelasId) {
            multipleJamInfo.style.display = 'none';
            applyToAllJam.checked = false;
            return;
        }
        
        const jamList = jadwalMap[selectedKelasId] || [];
        
        if (jamList.length > 1) {
            multipleJamInfo.style.display = 'block';
            jamCountInfo.textContent = `Kelas ini memiliki ${jamList.length} jam KBM.`;
        } else {
            multipleJamInfo.style.display = 'none';
            applyToAllJam.checked = false;
        }
    }
    
    kelasSelect.addEventListener('change', checkMultipleJam);
    
    // Check on page load
    checkMultipleJam();
});
</script>
@endsection
