@extends('layouts.app', ['pageSlug' => 'agenda'])

@section('title','Tambah Agenda Kelas')

@section('content')
<script src="https://cdn.jsdelivr.net/npm/tinymce@6.7.0/tinymce.min.js"></script>
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
                        <label class="form-label fw-bold">Jenis Kegiatan</label>
                        <select name="jenis_kegiatan" id="jenisKegiatanSelect" class="form-select @error('jenis_kegiatan') is-invalid @enderror" required>
                            <option value="kbm" {{ old('jenis_kegiatan', $selectedJenisKegiatan ?? 'kbm') === 'kbm' ? 'selected' : '' }}>KBM</option>
                            <option value="pengembangan_diri" {{ old('jenis_kegiatan', $selectedJenisKegiatan ?? 'kbm') === 'pengembangan_diri' ? 'selected' : '' }}>Pengembangan Diri</option>
                        </select>
                        @error('jenis_kegiatan')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                    </div>

                    <div id="kbmFields">
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
                        </select>
                        <small class="text-muted d-block mt-1">Daftar jam otomatis mengikuti hari pada tanggal yang dipilih.</small>
                        @error('jam_belajar_id')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                    </div>
                    </div>

                    <div id="pengembanganDiriFields" style="display:none;">
                        <div class="mb-3">
                            <label class="form-label fw-bold">Nama Kegiatan</label>
                            <input type="text" name="nama_kegiatan" id="namaKegiatanInput" class="form-control @error('nama_kegiatan') is-invalid @enderror" value="{{ old('nama_kegiatan') }}" placeholder="Contoh: Pembinaan OSIS, Ekstrakurikuler, Literasi">
                            @error('nama_kegiatan')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                        </div>
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
                               value="{{ old('tanggal', $selectedDate ?? now()->format('Y-m-d')) }}" required id="tanggalInput">
                        @error('tanggal')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold" id="kegiatanLabel">Kegiatan/Materi</label>
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
    const jenisKegiatanSelect = document.getElementById('jenisKegiatanSelect');
    const kelasSelect = document.getElementById('kelasSelect');
    const jamSelect = document.getElementById('jamSelect');
    const tanggalInput = document.getElementById('tanggalInput');
    const multipleJamInfo = document.getElementById('multipleJamInfo');
    const jamCountInfo = document.getElementById('jamCountInfo');
    const applyToAllJam = document.getElementById('applyToAllJam');
    const kbmFields = document.getElementById('kbmFields');
    const pengembanganDiriFields = document.getElementById('pengembanganDiriFields');
    const namaKegiatanInput = document.getElementById('namaKegiatanInput');
    const kegiatanLabel = document.getElementById('kegiatanLabel');
    const selectedJamBelajarId = '{{ old('jam_belajar_id', $selectedJamBelajarId ?? '') }}';
    
    const jadwalByKelas = @json($jadwalByKelas);

    function getHariIndonesia(dateString) {
        if (!dateString) return '';

        const hariList = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
        const dateObj = new Date(dateString + 'T00:00:00');

        return hariList[dateObj.getDay()] || '';
    }

    function renderJamOptions(preferredJamId = null) {
        const selectedKelasId = kelasSelect.value;
        const selectedHari = getHariIndonesia(tanggalInput.value);
        const jadwalKelas = jadwalByKelas[selectedKelasId] || [];

        const jadwalHari = jadwalKelas
            .filter(item => item.hari === selectedHari)
            .sort((a, b) => a.jam_ke - b.jam_ke);

        jamSelect.innerHTML = '<option value="">-- Pilih Jam KBM --</option>';

        jadwalHari.forEach(item => {
            const option = document.createElement('option');
            option.value = item.jam_belajar_id;
            option.textContent = item.label;
            jamSelect.appendChild(option);
        });

        if (jadwalHari.length === 0) {
            const noDataOption = document.createElement('option');
            noDataOption.value = '';
            noDataOption.disabled = true;
            noDataOption.textContent = 'Tidak ada jam KBM aktif untuk hari ini';
            jamSelect.appendChild(noDataOption);
            multipleJamInfo.style.display = 'none';
            applyToAllJam.checked = false;
            return;
        }

        const targetJamId = preferredJamId || jamSelect.value;
        if (targetJamId && jadwalHari.some(item => String(item.jam_belajar_id) === String(targetJamId))) {
            jamSelect.value = targetJamId;
        } else {
            jamSelect.value = String(jadwalHari[0].jam_belajar_id);
        }

        if (jadwalHari.length > 1) {
            multipleJamInfo.style.display = 'block';
            jamCountInfo.textContent = `Hari ${selectedHari} memiliki ${jadwalHari.length} jam KBM aktif untuk kelas ini.`;
        } else {
            multipleJamInfo.style.display = 'none';
            applyToAllJam.checked = false;
        }
    }

    function checkMultipleJam() {
        if (jenisKegiatanSelect.value !== 'kbm') {
            multipleJamInfo.style.display = 'none';
            applyToAllJam.checked = false;
            jamSelect.innerHTML = '<option value="">-- Pilih Jam KBM --</option>';
            return;
        }

        if (!kelasSelect.value) {
            multipleJamInfo.style.display = 'none';
            applyToAllJam.checked = false;
            jamSelect.innerHTML = '<option value="">-- Pilih Jam KBM --</option>';
            return;
        }

        renderJamOptions();
    }

    function toggleJenisKegiatanForm() {
        const isKbm = jenisKegiatanSelect.value === 'kbm';

        kbmFields.style.display = isKbm ? 'block' : 'none';
        pengembanganDiriFields.style.display = isKbm ? 'none' : 'block';
        multipleJamInfo.style.display = isKbm ? multipleJamInfo.style.display : 'none';

        kelasSelect.required = isKbm;
        jamSelect.required = isKbm;
        namaKegiatanInput.required = !isKbm;
        kegiatanLabel.textContent = isKbm ? 'Kegiatan/Materi' : 'Uraian Kegiatan';

        if (isKbm) {
            checkMultipleJam();
        } else {
            applyToAllJam.checked = false;
        }
    }
    
    jenisKegiatanSelect.addEventListener('change', toggleJenisKegiatanForm);
    kelasSelect.addEventListener('change', checkMultipleJam);
    tanggalInput.addEventListener('change', checkMultipleJam);
    
    // Check on page load
    if (kelasSelect.value && jenisKegiatanSelect.value === 'kbm') {
        renderJamOptions(selectedJamBelajarId);
    } else {
        checkMultipleJam();
    }

    toggleJenisKegiatanForm();
});
</script>
@endsection
