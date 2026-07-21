@extends('layouts.app', ['pageSlug' => 'agenda'])

@section('title', isset($agenda) ? 'Edit Agenda Kelas' : 'Tambah Agenda Kelas')

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

<div class="row justify-content-center">
    <div class="col-lg-8 col-md-10 mx-auto">
        <div class="card shadow-sm rounded-4 border-0">
            <div class="card-header bg-primary rounded-top-4 border-0">
                <h4 class="card-title mb-0 text-white">
                    <i class="ti ti-plus me-2"></i>{{ isset($agenda) ? 'Edit Agenda Kelas' : 'Tambah Agenda Kelas' }}
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
                    @if(isset($agenda))
                        <input type="hidden" name="agenda_id" value="{{ $agenda->id }}">
                    @endif
                    <input type="hidden" name="guru_id" id="hiddenGuruId" value="{{ old('guru_id', $agenda->guru_id ?? $selectedGuruId ?? $guru->id ?? '') }}">
                    <div class="row g-4">
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Jenis Kegiatan</label>
                            <div class="input-group rounded-3 border border-secondary overflow-hidden">
                                <span class="input-group-text bg-white border-0 px-3">
                                    <i class="ti ti-clipboard-list"></i>
                                </span>
                                <select name="jenis_kegiatan" id="jenisKegiatanSelect" class="form-control border-0 @error('jenis_kegiatan') is-invalid @enderror" required>
                                    <option value="kbm" {{ old('jenis_kegiatan', $agenda->jenis_kegiatan ?? $selectedJenisKegiatan ?? 'kbm') === 'kbm' ? 'selected' : '' }}>KBM</option>
                                    <option value="pengembangan_diri" {{ old('jenis_kegiatan', $agenda->jenis_kegiatan ?? $selectedJenisKegiatan ?? 'kbm') === 'pengembangan_diri' ? 'selected' : '' }}>Pengembangan Diri</option>
                                </select>
                            </div>
                            @error('jenis_kegiatan')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Tanggal</label>
                            <div class="input-group rounded-3 border border-secondary overflow-hidden">
                                <span class="input-group-text bg-white border-0 px-3">
                                    <i class="ti ti-calendar-event"></i>
                                </span>
                                <input type="date" name="tanggal" class="form-control border-0 @error('tanggal') is-invalid @enderror" value="{{ old('tanggal', $agenda->tanggal ?? $selectedDate ?? now()->format('Y-m-d')) }}" required id="tanggalInput">
                            </div>
                            @error('tanggal')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                        </div>
                    </div>

                    <div id="kbmFields" class="row g-4 mt-2">
                        <div class="col-md-3">
                            <label class="form-label fw-bold">Kelas</label>
                            <div class="input-group rounded-3 border border-secondary overflow-hidden">
                                <span class="input-group-text bg-white border-0 px-3">
                                    <i class="ti ti-users"></i>
                                </span>
                                <select name="kelas_id" class="form-control border-0 @error('kelas_id') is-invalid @enderror" required id="kelasSelect">
                                    <option value="">-- Pilih Kelas --</option>
                                    @forelse($kelas as $k)
                                        <option value="{{ $k->id }}" @if((string) old('kelas_id', $agenda->kelas_id ?? $selectedKelasId) === (string) $k->id) selected @endif>
                                            {{ $k->nama_kelas ?? 'Kelas '.$k->id }}
                                        </option>
                                    @empty
                                        <option disabled>Tidak ada kelas sesuai jadwal mengajar Anda</option>
                                    @endforelse
                                </select>
                            </div>
                            @error('kelas_id')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-5">
                            <label class="form-label fw-bold">Guru</label>
                            <div class="input-group rounded-3 border border-secondary overflow-hidden">
                                <span class="input-group-text bg-white border-0 px-3">
                                    <i class="ti ti-user"></i>
                                </span>
                                <select id="guruSelect" class="form-control border-0 @error('guru_id') is-invalid @enderror" required>
                                    @if(isset($guruList) && $guruList->isNotEmpty())
                                        <option value="">-- Pilih Guru --</option>
                                        @foreach($guruList as $gItem)
                                            <option value="{{ $gItem->id }}" @if((string) old('guru_id', $agenda->guru_id ?? $selectedGuruId ?? $guru->id ?? '') === (string) $gItem->id) selected @endif>
                                                {{ $gItem->nama }}
                                            </option>
                                        @endforeach
                                    @else
                                        <option value="{{ $guru->id }}">{{ $guru->nama ?? 'Guru '.$guru->id }} (Anda)</option>
                                    @endif
                                </select>
                            </div>
                            @error('guru_id')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-bold">Jam KBM</label>
                            <div class="input-group rounded-3 border border-secondary overflow-hidden">
                                <span class="input-group-text bg-white border-0 px-3">
                                    <i class="ti ti-clock"></i>
                                </span>
                                <select name="jam_belajar_id" class="form-control border-0 @error('jam_belajar_id') is-invalid @enderror" required id="jamSelect">
                                    <option value="">-- Pilih Jam KBM --</option>
                                    @if(!empty($initialJamOptions))
                                        @foreach($initialJamOptions as $item)
                                            <option value="{{ $item['jam_belajar_id'] }}" @if((string) old('jam_belajar_id', $agenda->jam_belajar_id ?? $selectedJamBelajarId) === (string) $item['jam_belajar_id']) selected @endif>
                                                {{ $item['label'] }}
                                            </option>
                                        @endforeach
                                    @endif
                                </select>
                            </div>
                            @error('jam_belajar_id')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                            <small class="text-muted d-block mt-2">Jam mengikuti hari yang dipilih.</small>
                        </div>
                    </div>

                        <div id="pengembanganDiriFields" class="row g-4 mt-2" style="display:none;">
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Kelas</label>
                                <div class="input-group rounded-3 border border-secondary overflow-hidden">
                                    <span class="input-group-text bg-white border-0 px-3">
                                        <i class="ti ti-users"></i>
                                    </span>
                                    <select name="kelas_id_pengembangan" id="kelasSelectPengembangan" class="form-control border-0">
                                        <option value="">-- Pilih Kelas --</option>
                                        @forelse($kelas as $k)
                                            <option value="{{ $k->id }}" @if((string) old('kelas_id', $agenda->kelas_id ?? $selectedKelasId) === (string) $k->id) selected @endif>
                                                {{ $k->nama_kelas ?? 'Kelas '.$k->id }}
                                            </option>
                                        @empty
                                            <option disabled>Tidak ada kelas</option>
                                        @endforelse
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Jam KBM</label>
                                <div class="input-group rounded-3 border border-secondary overflow-hidden">
                                    <span class="input-group-text bg-white border-0 px-3">
                                        <i class="ti ti-clock"></i>
                                    </span>
                                    <select id="jamSelectPengembangan" class="form-control border-0">
                                        <option value="">-- Pilih Jam KBM --</option>
                                    </select>
                                </div>
                                <small class="text-muted d-block mt-2">Otomatis terisi sesuai jadwal kelas dan hari yang dipilih.</small>
                            </div>
                            <div class="col-12">
                                <label class="form-label fw-bold">Nama Kegiatan</label>
                                <div class="input-group rounded-3 border border-secondary overflow-hidden">
                                    <span class="input-group-text bg-white border-0 px-3">
                                        <i class="ti ti-book"></i>
                                    </span>
                                    <select name="nama_kegiatan" id="namaKegiatanInput" class="form-control border-0 @error('nama_kegiatan') is-invalid @enderror" required>
                                        <option value="">-- Pilih Kegiatan --</option>
                                        @if(isset($kegiatanList) && $kegiatanList->isNotEmpty())
                                            @foreach($kegiatanList as $keg)
                                                <option value="{{ $keg->nama_kegiatan }}" @if(old('nama_kegiatan', $agenda->nama_kegiatan ?? '') === $keg->nama_kegiatan) selected @endif>
                                                    {{ $keg->nama_kegiatan }} {{ $keg->kode_kegiatan ? '(' . $keg->kode_kegiatan . ')' : '' }}
                                                </option>
                                            @endforeach
                                        @endif
                                    </select>
                                </div>
                                @error('nama_kegiatan')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                                <small class="text-muted d-block mt-2">Pilih kegiatan dari daftar di bawah ini.</small>
                            </div>
                        </div>
                        </div>

                        <div class="col-12" id="multipleJamInfo" style="display: none;">
                            <div class="alert alert-info mb-0">
                                <div class="d-flex align-items-start gap-2">
                                    <i class="ti ti-info-circle fs-4 mt-1"></i>
                                    <div>
                                        <strong>Info:</strong> Kelas ini memiliki lebih dari 1 jam KBM dengan guru Anda.
                                        <div id="jamCountInfo" class="small text-muted"></div>
                                    </div>
                                </div>
                            </div>
                            <div class="form-check mt-2">
                                <input class="form-check-input" type="checkbox" id="applyToAllJam" name="apply_to_all_jam" value="1">
                                <label class="form-check-label" for="applyToAllJam">
                                    Terapkan agenda ini ke semua jam KBM kelas yang sama pada hari yang sama.
                                </label>
                            </div>
                        </div>

                        <div class="col-12">
                            <label class="form-label fw-bold" id="kegiatanLabel">Kegiatan/Materi</label>
                            <textarea name="kegiatan" class="form-control tiny-editor @error('kegiatan') is-invalid @enderror" rows="4">{{ old('kegiatan', $agenda->kegiatan ?? '') }}</textarea>
                            @error('kegiatan')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                        </div>

                        <div class="col-12 d-flex justify-content-end gap-2">
                            <a href="{{ route('agenda_kelas.index') }}" class="btn btn-outline-secondary btn-modern">
                                <i class="ti ti-arrow-left me-1"></i>Batal
                            </a>
                            <button type="submit" class="btn btn-primary px-4">
                                <i class="ti ti-check me-1"></i>{{ isset($agenda) ? 'Simpan Perubahan Agenda' : 'Simpan Agenda' }}
                            </button>
                        </div>
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
    const jamSelectPengembangan = document.getElementById('jamSelectPengembangan');
    const kelasSelectPengembangan = document.getElementById('kelasSelectPengembangan');
    const selectedJamBelajarId = '{{ old('jam_belajar_id', $agenda->jam_belajar_id ?? $selectedJamBelajarId ?? '') }}';
    const initialSelectedHari = '{{ old('tanggal', $agenda->tanggal ?? $selectedDate ?? '') }}';
    const initialSelectedKelasId = '{{ old('kelas_id', $agenda->kelas_id ?? $selectedKelasId ?? '') }}';
    const initialSelectedGuruId = '{{ old('guru_id', $agenda->guru_id ?? $selectedGuruId ?? $guru->id ?? '') }}';
    
    const jadwalByKelas = @json($jadwalByKelas);

    const hiddenGuruId = document.getElementById('hiddenGuruId');
    const guruSelect = document.getElementById('guruSelect');

    function syncGuruId() {
        if (hiddenGuruId && guruSelect) {
            hiddenGuruId.value = guruSelect.value || hiddenGuruId.value;
        }
    }

    if (guruSelect) {
        guruSelect.addEventListener('change', syncGuruId);
    }

    function getHariIndonesia(dateString) {
        if (!dateString) return '';

        dateString = dateString.toString().trim();
        let dateObj = tanggalInput.valueAsDate || null;
        if (!dateObj) {
            const parts = dateString.split(/[-\/]/).map(part => Number(part));
            if (parts.length === 3 && parts.every(Number.isFinite)) {
                let year, month, day;
                if (parts[0] > 31 || String(parts[0]).length === 4) {
                    // yyyy-mm-dd or yyyy/mm/dd
                    year = parts[0];
                    month = parts[1];
                    day = parts[2];
                } else {
                    // dd/mm/yyyy or dd-mm-yyyy
                    year = parts[2];
                    month = parts[1];
                    day = parts[0];
                }
                dateObj = new Date(year, month - 1, day);
            } else {
                return '';
            }
        }

        if (Number.isNaN(dateObj.getTime())) {
            return '';
        }

        const hariList = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
        return hariList[dateObj.getDay()] || '';
    }

    function normalizeHari(hari) {
        if (!hari) return '';
        return hari.toString().trim().toLowerCase().replace(/\s+/g, ' ');
    }

    function renderJamOptions(preferredJamId = null) {
        const selectedKelasId = kelasSelect.value || initialSelectedKelasId;
        const selectedHariRaw = getHariIndonesia(tanggalInput.value) || initialSelectedHari;
        const selectedHari = normalizeHari(selectedHariRaw);
        const jadwalKelas = jadwalByKelas[selectedKelasId] || [];
        const selectedGuruId = (document.getElementById('guruSelect') ? document.getElementById('guruSelect').value : '') || initialSelectedGuruId;

        const jadwalHari = jadwalKelas
            .filter(item => normalizeHari(item.hari) === selectedHari)
            .filter(item => {
                if (!selectedGuruId) return true;
                return String(item.guru_id || '') === String(selectedGuruId);
            })
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

    // Re-render jam options when selected guru changes
    const guruSelectElem = document.getElementById('guruSelect');
    if (guruSelectElem) {
        guruSelectElem.addEventListener('change', function() {
            checkMultipleJam();
        });
    }

    // Sync pengembangan_diri kelas selection to main kelasSelect
    if (kelasSelectPengembangan) {
        kelasSelectPengembangan.addEventListener('change', function() {
            kelasSelect.value = this.value;
            checkMultipleJam();
        });
    }

    // Sync pengembangan_diri jam selection to main jamSelect
    if (jamSelectPengembangan) {
        jamSelectPengembangan.addEventListener('change', function() {
            jamSelect.value = this.value;
        });
    }

    // Sync main jamSelect to pengembangan_diri jam select
    jamSelect.addEventListener('change', function() {
        if (jamSelectPengembangan) {
            jamSelectPengembangan.value = this.value;
        }
    });

    function checkMultipleJam() {
        if (!kelasSelect.value) {
            multipleJamInfo.style.display = 'none';
            applyToAllJam.checked = false;
            jamSelect.innerHTML = '<option value="">-- Pilih Jam KBM --</option>';
            return;
        }

        const isKbm = jenisKegiatanSelect.value === 'kbm';
        if (isKbm) {
            renderJamOptions();
        } else {
            // For pengembangan_diri, populate jam options based on class schedule
            renderJamOptionsForPengembangan();
        }
    }

    function renderJamOptionsForPengembangan() {
        const selectedKelasId = kelasSelect.value || initialSelectedKelasId;
        const selectedHariRaw = getHariIndonesia(tanggalInput.value) || initialSelectedHari;
        const selectedHari = normalizeHari(selectedHariRaw);
        const jadwalKelas = jadwalByKelas[selectedKelasId] || [];

        // For pengembangan_diri, get all jam for the class/day (not filtered by guru)
        const jadwalHari = jadwalKelas
            .filter(item => normalizeHari(item.hari) === selectedHari)
            .sort((a, b) => a.jam_ke - b.jam_ke);

        jamSelect.innerHTML = '<option value="">-- Pilih Jam KBM --</option>';
        jamSelectPengembangan.innerHTML = '<option value="">-- Pilih Jam KBM --</option>';

        // Create a map to avoid duplicate jam entries
        const uniqueJams = {};
        jadwalHari.forEach(item => {
            const jamId = item.jam_belajar_id;
            if (!uniqueJams[jamId]) {
                uniqueJams[jamId] = item;
            }
        });

        // Sort by jam_ke to ensure consistent order
        const sortedJams = Object.values(uniqueJams).sort((a, b) => a.jam_ke - b.jam_ke);

        sortedJams.forEach(item => {
            const option = document.createElement('option');
            option.value = item.jam_belajar_id;
            option.textContent = item.label;
            jamSelect.appendChild(option);
            
            const optionPengembangan = document.createElement('option');
            optionPengembangan.value = item.jam_belajar_id;
            optionPengembangan.textContent = item.label;
            jamSelectPengembangan.appendChild(optionPengembangan);
        });

        if (sortedJams.length === 0) {
            const noDataOption = document.createElement('option');
            noDataOption.value = '';
            noDataOption.disabled = true;
            noDataOption.textContent = 'Tidak ada jam KBM aktif untuk hari ini';
            jamSelect.appendChild(noDataOption);
            
            const noDataOptionPengembangan = document.createElement('option');
            noDataOptionPengembangan.value = '';
            noDataOptionPengembangan.disabled = true;
            noDataOptionPengembangan.textContent = 'Tidak ada jam KBM aktif untuk hari ini';
            jamSelectPengembangan.appendChild(noDataOptionPengembangan);
        } else {
            // Auto-select first jam for pengembangan_diri (always)
            const firstJamId = String(sortedJams[0].jam_belajar_id);
            jamSelect.value = firstJamId;
            jamSelectPengembangan.value = firstJamId;
        }

        multipleJamInfo.style.display = 'none';
        applyToAllJam.checked = false;
    }

    function toggleJenisKegiatanForm() {
        const isKbm = jenisKegiatanSelect.value === 'kbm';

        kbmFields.style.display = isKbm ? 'block' : 'none';
        pengembanganDiriFields.style.display = isKbm ? 'none' : 'block';
        multipleJamInfo.style.display = isKbm ? multipleJamInfo.style.display : 'none';

        kelasSelect.required = isKbm;
        jamSelect.required = true; // jam is always required now for both types
        if (guruSelect) {
            guruSelect.required = isKbm;
        }
        namaKegiatanInput.required = !isKbm;
        kegiatanLabel.textContent = isKbm ? 'Kegiatan/Materi' : 'Uraian Kegiatan';

        if (isKbm) {
            checkMultipleJam();
        } else {
            // For pengembangan_diri, sync the kelas value and populate jam options
            if (kelasSelectPengembangan) {
                kelasSelectPengembangan.value = kelasSelect.value;
            }
            checkMultipleJam();
            applyToAllJam.checked = false;
        }

        syncGuruId();
    }
    
    jenisKegiatanSelect.addEventListener('change', toggleJenisKegiatanForm);
    kelasSelect.addEventListener('change', checkMultipleJam);
    tanggalInput.addEventListener('change', checkMultipleJam);
    
    // Sync pengembangan_diri kelas selection to main kelasSelect
    if (kelasSelectPengembangan) {
        kelasSelectPengembangan.addEventListener('change', function() {
            kelasSelect.value = this.value;
            checkMultipleJam();
        });
    }

    // Sync pengembangan_diri jam selection to main jamSelect
    if (jamSelectPengembangan) {
        jamSelectPengembangan.addEventListener('change', function() {
            jamSelect.value = this.value;
        });
    }

    // Sync main jamSelect to pengembangan_diri jam select
    jamSelect.addEventListener('change', function() {
        if (jamSelectPengembangan) {
            jamSelectPengembangan.value = this.value;
        }
    });
    
    // Check on page load
    if (kelasSelect.value && jenisKegiatanSelect.value === 'kbm') {
        renderJamOptions(selectedJamBelajarId);
    } else {
        checkMultipleJam();
    }

    syncGuruId();
    toggleJenisKegiatanForm();
});
</script>
@endsection
