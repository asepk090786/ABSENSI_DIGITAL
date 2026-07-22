@extends('layouts.app')

@section('title', 'Tambah Absensi Kelas')

@section('content')
<style>
    .status-badge {
        font-size: 11px;
        padding: 4px 8px;
    }
    .status-hadir { background-color: #d4edda; color: #155724; }
    .status-sakit { background-color: #fff3cd; color: #856404; }
    .status-izin { background-color: #cfe2ff; color: #084298; }
    .status-terlambat { background-color: #ffe5b4; color: #8a5a00; }
    .status-alpa { background-color: #f8d7da; color: #842029; }
    
    .table-absensi tbody tr {
        transition: all 0.3s;
    }
    .table-absensi tbody tr:hover {
        background-color: #f5f5f5;
    }
    
    .siswa-list {
        max-height: 500px;
        overflow-y: auto;
    }

    /* Custom Combo Box Styling */
    .combo-box-wrapper {
        position: relative;
        width: 100%;
        display: block;
    }

    /* IMPORTANT: Hide the native select */
    .combo-box-select {
        display: none !important;
        visibility: hidden !important;
        position: absolute !important;
        left: -9999px !important;
    }

    .combo-box-input {
            display: block;
        width: 100%;
        padding: 0.375rem 2rem 0.375rem 0.75rem;
        font-size: 0.875rem;
        border: 1px solid #dee2e6;
        border-radius: 0.375rem;
        background-color: #fff;
        cursor: pointer;
        transition: all 0.2s;
        user-select: none;
    }

    .combo-box-input:hover {
        border-color: #86b7fe;
        box-shadow: 0 0 0 0.1rem rgba(13, 110, 253, 0.15);
    }

    .combo-box-input:focus,
    .combo-box-input.active {
        border-color: #86b7fe;
        box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.25);
        outline: 0;
    }

    .combo-box-arrow {
        position: absolute;
        right: 0.75rem;
        top: 50%;
        transform: translateY(-50%);
        pointer-events: none;
        transition: transform 0.2s;
        color: #6c757d;
    }

    .combo-box-wrapper.active .combo-box-arrow {
        transform: translateY(-50%) rotate(180deg);
    }

    .combo-box-dropdown {
        position: absolute;
        top: calc(100% + 4px);
        left: 0;
        right: 0;
        background: #fff;
        border: 1px solid #dee2e6;
        border-radius: 0.375rem;
        box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
        max-height: 200px;
        overflow-y: auto;
        z-index: 1000;
        display: none;
    }

    .combo-box-wrapper.active .combo-box-dropdown {
        display: block;
    }

    .combo-box-option {
        padding: 0.5rem 0.75rem;
        cursor: pointer;
        transition: all 0.15s;
        font-size: 0.875rem;
        border-bottom: 1px solid #f0f0f0;
    }

    .combo-box-option:last-child {
        border-bottom: none;
    }

    .combo-box-option:hover,
    .combo-box-option.highlighted {
        background-color: #e7f1ff;
        color: #0d6efd;
    }

    .combo-box-option.selected {
        background-color: #0d6efd;
        color: white;
        font-weight: 500;
    }

    .combo-box-option.placeholder {
        color: #6c757d;
        font-style: italic;
    }

    /* Hidden select for form submission */
    .combo-box-select {
        display: none;
    }
</style>

<div class="container-fluid">
    @php
        $isAdminOrKepala = auth()->user()->hasAnyRole(['Admin', 'Kepala Sekolah']);
        $isSiswaOfficer = auth()->user()->hasRole('Siswa') && auth()->user()->hasClassPosition();
        // Keep quick-access locked for non-admins, non-guru-piket, but allow SiswaOfficer to choose jam
        $lockQuickAccessField = ($isQuickAccess ?? false) && !$isAdminOrKepala && !($isGuruPiket ?? false) && !$isSiswaOfficer;
    @endphp
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header border-0 pt-3 pb-2">
                    <div class="d-flex justify-content-between align-items-center">
                        <h3 class="card-title fw-semibold m-0">Tambah Absensi Kelas</h3>
                        <a href="{{ route('absensi.index') }}" class="btn btn-secondary">
                            <i class="ti ti-arrow-left me-1"></i> Kembali
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    @if ($errors->any())
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <strong><i class="ti ti-alert-circle me-2"></i>Terjadi Kesalahan:</strong>
                        <ul class="mb-0 mt-2">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                        <button type="button" class="close" data-bs-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    </div>
                    @endif

                    @if(session('warning'))
                    <div class="alert alert-warning alert-dismissible fade show" role="alert">
                        <strong><i class="ti ti-alert-triangle me-2"></i>Perhatian:</strong>
                        {{ session('warning') }}
                        <button type="button" class="close" data-bs-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    </div>
                    @endif

                    @if(auth()->user()->guru_id && !($isGuruPiket ?? false) && !$isAdminOrKepala)
                    <div class="alert alert-info">
                        <i class="ti ti-info-circle me-2"></i>
                        <strong>Info:</strong> Anda dapat menginput absensi untuk {{ $kelasList->count() }} kelas yang Anda ajar.
                        @if($jadwalList->isNotEmpty())
                        <br><strong>Jadwal Hari Ini:</strong> Anda memiliki {{ $jadwalList->count() }} jam mengajar hari ini.
                        @endif
                    </div>
                    @endif

                    @if($isQuickAccess ?? false)
                    <div class="alert alert-success">
                        <i class="ti ti-check-circle me-2"></i>
                        @if($isAdminOrKepala)
                            <strong>Mode Akses Cepat:</strong> Kelas telah dipilih dari menu cepat, silakan pilih guru dan jam belajar sesuai kebutuhan.
                        @else
                            <strong>Mode Akses Cepat:</strong> Kelas dan jam belajar telah diisi otomatis sesuai jadwal hari ini.
                        @endif
                    </div>
                    @endif

                    <form action="{{ route('absensi.store') }}" method="POST" id="formAbsensi">
                        @csrf

                        <input type="hidden" name="tahun_ajaran_id" value="{{ $tahunAjaran->id }}">
                        <input type="hidden" name="semester_id" value="{{ $semester->id }}">

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-2">
                                    <label for="tanggal" class="form-label">Tanggal <span class="text-danger">*</span></label>
                                    <input type="date" class="form-control @error('tanggal') is-invalid @enderror" 
                                         id="tanggal" name="tanggal" value="{{ old('tanggal', $selectedDate ?? date('Y-m-d')) }}" required>
                                    @error('tanggal')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="mb-2">
                                    <label for="kelas_id" class="form-label">Kelas <span class="text-danger">*</span></label>
                                    <select class="form-control @error('kelas_id') is-invalid @enderror" 
                                            id="kelas_id" name="kelas_id" required
                                            {{ $lockQuickAccessField ? 'disabled' : '' }}>
                                        <option value="">Pilih Kelas</option>
                                        @foreach($kelasList as $kelas)
                                            <option value="{{ $kelas->id }}" 
                                                {{ old('kelas_id', $selectedKelasId ?? '') == $kelas->id ? 'selected' : '' }}>
                                                {{ $kelas->nama_kelas }}
                                            </option>
                                        @endforeach
                                    </select>
                                    <div id="jamInfoBox" class="mt-2"></div>
                                    @if($lockQuickAccessField)
                                    <input type="hidden" name="kelas_id" value="{{ $selectedKelasId }}">
                                    @endif
                                    @error('kelas_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            @if(auth()->user()->guru_id && !($isAdminOrKepala ?? false))
                            <div class="col-md-6">
                                <div class="form-check form-switch mt-3 pt-2">
                                    <input class="form-check-input" type="checkbox" id="ambilAbsensiToggle" name="ambil_absensi_dari_kelas" value="1" {{ old('ambil_absensi_dari_kelas') ? 'checked' : '' }}>
                                    <label class="form-check-label" for="ambilAbsensiToggle">
                                        Ambil Absensi dari Kelas
                                    </label>
                                </div>
                                <div class="form-text text-muted">
                                    Jika aktif, sistem akan memuat data absensi kelas yang sudah diinput oleh siswa dengan jabatan.
                                </div>
                                <div class="form-text text-muted mt-1">
                                    Jika nonaktif, guru akan menginput absensi sendiri secara manual. Pada mode manual, guru hanya dapat menyimpan jika memiliki jadwal mengajar di kelas ini pada tanggal tersebut.
                                </div>
                            </div>
                            @endif

                            @if($isGuruPiket ?? false)
                                <input type="hidden" name="guru_id" value="{{ old('guru_id', auth()->user()->guru_id) }}">
                                <input type="hidden" name="jam_belajar_id" value="{{ old('jam_belajar_id', $selectedJamBelajarId ?? ($jamBelajarList->first()->id ?? '')) }}">
                            @endif

                            @if(!($isGuruPiket ?? false))
                            <div class="col-md-6">
                                <div class="mb-2">
                                    <label for="guru_id" class="form-label">Guru <span class="text-danger">*</span></label>
                                    <select class="form-control @error('guru_id') is-invalid @enderror" 
                                            id="guru_id" name="guru_id" required>
                                        <option value="">Pilih Guru</option>
                                        @foreach($guruList as $guru)
                                            <option value="{{ $guru->id }}" {{ old('guru_id', auth()->user()->guru_id) == $guru->id ? 'selected' : '' }}>
                                                {{ $guru->nama }} ({{ $guru->kode_guru }})
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('guru_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="mb-2">
                                    <label for="jam_belajar_id" class="form-label">Jam Belajar <span class="text-danger">*</span></label>
                                    <select class="form-control @error('jam_belajar_id') is-invalid @enderror" 
                                            id="jam_belajar_id" name="jam_belajar_id" required
                                            {{ $lockQuickAccessField ? 'disabled' : '' }}>
                                        <option value="">Pilih Jam Belajar</option>
                                        @foreach($jamBelajarList as $jam)
                                            <option value="{{ $jam->id }}" {{ old('jam_belajar_id', $selectedJamBelajarId ?? '') == $jam->id ? 'selected' : '' }}>
                                                Jam ke-{{ $jam->urutan }} ({{ $jam->jam_mulai }} - {{ $jam->jam_selesai }})
                                            </option>
                                        @endforeach
                                    </select>
                                    @if($lockQuickAccessField)
                                    <input type="hidden" name="jam_belajar_id" value="{{ $selectedJamBelajarId }}">
                                    @endif
                                    @php
                                        $jamSlotText = ($multiSlotJadwal ?? collect())->map(function($item) {
                                            return 'Jam ke-' . ($item->jamBelajar->urutan ?? $item->jam_ke);
                                        })->implode(', ');
                                    @endphp
                                    @if(($multiSlotJadwal ?? collect())->count() > 1)
                                    <div class="alert alert-warning mt-2 mb-0 py-2">
                                        <i class="ti ti-clock-hour-3 me-2"></i>Anda memiliki {{ ($multiSlotJadwal ?? collect())->count() }} jadwal di kelas ini pada hari tersebut. Absensi akan otomatis diterapkan ke: {{ $jamSlotText }}.
                                    </div>
                                    @elseif(($multiSlotJadwal ?? collect())->count() === 1)
                                    <div class="alert alert-info mt-2 mb-0 py-2">
                                        <i class="ti ti-clock-hour-3 me-2"></i>Absensi akan diterapkan ke {{ $jamSlotText }} untuk hari ini.
                                    </div>
                                    @endif
                                    @error('jam_belajar_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            @endif

                            @if(!($isGuruPiket ?? false))
                            <div class="col-md-12">
                                <div class="mb-2">
                                    <label for="status_kelas" class="form-label">Status Kelas</label>
                                    <select class="form-control @error('status_kelas') is-invalid @enderror" id="status_kelas" name="status_kelas">
                                        <option value="">-- Pilih Status Kelas (opsional) --</option>
                                        <option value="Sangat Kondusif" {{ old('status_kelas') === 'Sangat Kondusif' ? 'selected' : '' }}>Sangat Kondusif</option>
                                        <option value="Kondusif" {{ old('status_kelas') === 'Kondusif' ? 'selected' : '' }}>Kondusif</option>
                                        <option value="Normal" {{ old('status_kelas') === 'Normal' ? 'selected' : '' }}>Normal</option>
                                        <option value="Kurang Kondusif" {{ old('status_kelas') === 'Kurang Kondusif' ? 'selected' : '' }}>Kurang Kondusif</option>
                                        <option value="Tidak Kondusif" {{ old('status_kelas') === 'Tidak Kondusif' ? 'selected' : '' }}>Tidak Kondusif</option>
                                    </select>
                                    @error('status_kelas')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <small class="text-muted">Opsional - Kondisi atau keterangan kelas</small>
                                </div>
                            </div>
                            @endif
                        </div>

                        <div class="alert alert-info">
                            <i class="ti ti-info-circle me-2"></i>
                            <strong>Tahun Ajaran:</strong> {{ $tahunAjaran->nama_tahun ?? '-' }} | 
                            <strong>Semester:</strong> {{ $semester->nama_semester ?? '-' }}
                        </div>

                        
                        <div id="siswaContainer" style="display: none;">
                            <div class="card mt-4">
                                <div class="card-header bg-success-subtle d-flex flex-wrap align-items-center gap-3">
                                    <h5 class="mb-0"><i class="ti ti-users me-2"></i>Daftar Siswa & Absensi</h5>
                                    <div class="ms-auto d-flex flex-wrap gap-2">
                                            <button type="button" class="btn btn-sm btn-success btn-modern" onclick="setAllStatus('hadir')">Ceklis Semua Hadir</button>
                                        <button type="button" class="btn btn-sm btn-orange" onclick="setAllStatus('terlambat')" style="background:#f59e0b;color:#fff;">Ceklis Semua Terlambat</button>
                                        <button type="button" class="btn btn-sm btn-warning" onclick="setAllStatus('sakit')">Ceklis Semua Sakit</button>
                                        <button type="button" class="btn btn-sm btn-info btn-modern" onclick="setAllStatus('izin')">Ceklis Semua Izin</button>
                                        <input type="text" id="searchSiswa" class="form-control form-control-sm" placeholder="Cari nama / NIS" style="min-width: 200px;">
                                    </div>
                                </div>
                                <div class="card-body">
                                    <div class="table-responsive siswa-list">
                                        <table class="table table-bordered table-hover table-absensi">
                                            <thead class="table-light sticky-top">
                                                <tr>
                                                    <th rowspan="2" class="align-middle text-center" width="3%">No</th>
                                                    <th rowspan="2" class="align-middle text-center" width="8%">NIS</th>
                                                    <th rowspan="2" class="align-middle text-center" width="8%">NISN</th>
                                                    <th rowspan="2" class="align-middle text-center" width="20%">NAMA</th>
                                                    <th rowspan="2" class="align-middle text-center" width="10%">JENIS KELAMIN</th>
                                                    <th colspan="5" class="text-center">STATUS</th>
                                                    <th rowspan="2" class="align-middle text-center" width="15%">KETERANGAN</th>
                                                </tr>
                                                <tr>
                                                        <th class="text-center" width="5%">Hadir</th>
                                                    <th class="text-center" width="7%">Terlambat</th>
                                                    <th class="text-center" width="5%">Sakit</th>
                                                    <th class="text-center" width="5%">Izin</th>
                                                    <th class="text-center" width="8%">Alpa/Tanpa Keterangan</th>
                                                </tr>
                                            </thead>
                                            <tbody id="siswaTableBody">
                                                <tr>
                                                    <td colspan="{{ ($isGuruPiket ?? false) ? 10 : 11 }}" class="text-center text-muted">
                                                        <i class="ti ti-info-circle me-1"></i>Pilih kelas untuk menampilkan daftar siswa
                                                    </td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="mt-4 d-flex gap-2">
                            <button type="submit" class="btn btn-primary" id="btnSubmit" disabled>
                                <i class="ti ti-check me-1"></i> Simpan Absensi
                            </button>
                            <a href="{{ route('absensi.index') }}" class="btn btn-secondary">
                                <i class="ti ti-arrow-left me-1"></i> Batal
                            </a>
                        </div>

                        <script>
    document.addEventListener('DOMContentLoaded', function() {
        var kelasSelect = document.getElementById('kelas_id');
        var tanggalInput = document.getElementById('tanggal');
        var jamBelajarSelect = document.getElementById('jam_belajar_id');
        var guruSelect = document.getElementById('guru_id');
        var siswaContainer = document.getElementById('siswaContainer');
        var siswaTableBody = document.getElementById('siswaTableBody');
        var btnSubmit = document.getElementById('btnSubmit');
        var isQuickAccess = '{{ $isQuickAccess ?? 0 }}' === '1';
        var isGuruPiket = '{{ ($isGuruPiket ?? false) ? 1 : 0 }}' === '1';

        console.log('Initializing form...', {
            kelasValue: kelasSelect ? kelasSelect.value : null,
            isQuickAccess: isQuickAccess
        });

        // Trigger load siswa jika ada kelas yang sudah dipilih saat page load
        if (kelasSelect && kelasSelect.value && isQuickAccess) {
            console.log('Quick access mode detected, loading siswa for kelas:', kelasSelect.value);
            setTimeout(function() {
                loadSiswaByKelas(kelasSelect.value);
            }, 100);
        }

        // Prepare jadwal and guru data from server
        var jadwalList = @json($jadwalList ?? collect());
        var guruListRaw = @json($guruList ?? collect());
        // Prepare jamBelajar mapping from server to use as fallback
        var jamBelajarServerRaw = @json($jamBelajarList ?? collect());
        var jamBelajarServer = {};
        jamBelajarServerRaw.forEach(function(j){ jamBelajarServer[j.id] = j; });
        console.log('jadwalList (from server):', jadwalList);
        console.log('jamBelajarServerRaw (from server):', jamBelajarServerRaw);
        console.log('jamBelajarServer (mapped by id):', jamBelajarServer);

        function renderJamOptionsByGuru() {
            // Determine selected values
            var kelasId = kelasSelect ? kelasSelect.value : '';
            var guruId = guruSelect ? guruSelect.value : '';
            var tanggalVal = tanggalInput ? tanggalInput.value : '';

            // Compute hari name in Indonesian
            function getHariIndonesiaLocal(dateString) {
                if (!dateString) return '';
                var hariList = ['Minggu','Senin','Selasa','Rabu','Kamis','Jumat','Sabtu'];
                var d = new Date(dateString + 'T00:00:00');
                return hariList[d.getDay()] || '';
            }

            var hariName = getHariIndonesiaLocal(tanggalVal);

            // Filter jadwalList for kelas, hari and optionally guru
            var filtered = jadwalList.filter(function(item) {
                if (kelasId && String(item.kelas_id || item.kelasId || '') !== String(kelasId)) return false;
                if (hariName && String((item.hari || '').trim()) !== String(hariName)) return false;
                if (guruId && String(guruId) !== 'all') {
                        return String(item.guru_id || '') === String(guruId);
                    }
                    return true;
            });

            // Sort and populate select
            filtered.sort(function(a,b){ return (a.jam_ke||0) - (b.jam_ke||0); });
            jamBelajarSelect.innerHTML = '<option value="">Pilih Jam Belajar</option>';
            var added = {};
            filtered.forEach(function(item){
                var jamId = item.jam_belajar_id || (item.jamBelajar && item.jamBelajar.id) || item.jam_belajar;
                if (!jamId) return;
                if (added[jamId]) return;
                added[jamId] = true;
                var urutan = item.jam_ke || (item.jamBelajar && item.jamBelajar.urutan) || item.urutan || '?';
                var jamMulai = item.jam_mulai || (item.jamBelajar && item.jamBelajar.jam_mulai) || (jamBelajarServer[jamId] && jamBelajarServer[jamId].jam_mulai) || '';
                var jamSelesai = item.jam_selesai || (item.jamBelajar && item.jamBelajar.jam_selesai) || (jamBelajarServer[jamId] && jamBelajarServer[jamId].jam_selesai) || '';
                var urutan = urutan || (jamBelajarServer[jamId] && jamBelajarServer[jamId].urutan) || urutan;
                var label = 'Jam ke-' + urutan + ' (' + jamMulai + ' - ' + jamSelesai + ')';
                var opt = document.createElement('option');
                opt.value = jamId;
                opt.textContent = label;
                jamBelajarSelect.appendChild(opt);
            });

            // expose added jam ids for other logic
            window._lastAddedJamIds = Object.keys(added);

            if (Object.keys(added).length === 0) {
                jamBelajarSelect.innerHTML = '<option value="">Tidak ada jam KBM aktif untuk pilihan ini</option>';
                var jamInfoBox = document.getElementById('jamInfoBox');
                if (jamInfoBox) jamInfoBox.innerHTML = '';
            } else {
                var jamInfoBox = document.getElementById('jamInfoBox');
                if (jamInfoBox) {
                    var keys = Object.keys(added);
                    var labels = keys.map(function(k){
                        var jb = jamBelajarServer[k] || {};
                        return 'Jam ke-' + (jb.urutan || '?');
                    });
                    if (labels.length > 1) {
                        jamInfoBox.innerHTML = '<div class="alert alert-warning mt-2 mb-0 py-2"><i class="ti ti-clock-hour-3 me-2"></i>Anda memiliki ' + labels.length + ' jadwal di kelas ini pada hari tersebut. Absensi akan otomatis diterapkan ke: ' + labels.join(', ') + '.</div>';
                    } else {
                        jamInfoBox.innerHTML = '<div class="alert alert-info mt-2 mb-0 py-2"><i class="ti ti-clock-hour-3 me-2"></i>Absensi akan diterapkan ke ' + labels[0] + ' untuk hari ini.</div>';
                    }
                }
            }
            // if guru 'all' is selected, reflect that jam selection represents all jam
            if (guruSelect && guruSelect.value === 'all') {
                applyAllJamUi();
            }
        }

            // Initial render of jam options on page load
            renderJamOptionsByGuru();

        // Render guru options based on selected kelas and tanggal
        var isSiswaOfficer = '{{ $isSiswaOfficer ? 1 : 0 }}' === '1';
        function renderGuruOptionsByKelasTanggal() {
            if (!guruSelect) return;
            var kelasId = kelasSelect ? kelasSelect.value : '';
            var tanggalVal = tanggalInput ? tanggalInput.value : '';
            function getHariIndonesiaLocal(dateString) {
                if (!dateString) return '';
                var hariList = ['Minggu','Senin','Selasa','Rabu','Kamis','Jumat','Sabtu'];
                var d = new Date(dateString + 'T00:00:00');
                return hariList[d.getDay()] || '';
            }
            var hariName = getHariIndonesiaLocal(tanggalVal);

            // find guru ids from jadwal matching kelas and hari
            var guruIds = {};
            jadwalList.forEach(function(item){
                if (kelasId && String(item.kelas_id || item.kelasId || '') !== String(kelasId)) return;
                if (hariName && String((item.hari || '').trim()) !== String(hariName)) return;
                var gid = item.guru_id || (item.guru && item.guru.id) || item.guruId;
                if (gid) guruIds[gid] = true;
            });

            // Build options
            guruSelect.innerHTML = '';
            var optPlaceholder = document.createElement('option');
            optPlaceholder.value = '';
            optPlaceholder.text = 'Pilih Guru';
            guruSelect.appendChild(optPlaceholder);

            if (isSiswaOfficer) {
                var optAll = document.createElement('option');
                optAll.value = 'all';
                optAll.text = 'Pilih semua guru';
                guruSelect.appendChild(optAll);
            }

            var added = {};
            // if any guruIds found, list them
            if (Object.keys(guruIds).length > 0) {
                guruListRaw.forEach(function(g){
                    if (guruIds[g.id]) {
                        var o = document.createElement('option');
                        o.value = g.id;
                        o.text = g.nama + (g.kode_guru ? (' ('+g.kode_guru+')') : '');
                        guruSelect.appendChild(o);
                        added[g.id] = true;
                    }
                });
            } else {
                // fallback: list all guru
                guruListRaw.forEach(function(g){
                    var o = document.createElement('option');
                    o.value = g.id;
                    o.text = g.nama + (g.kode_guru ? (' ('+g.kode_guru+')') : '');
                    guruSelect.appendChild(o);
                    added[g.id] = true;
                });
            }

            // Preserve old value if present
            var oldVal = '{{ old('guru_id', auth()->user()->guru_id ?? '') }}';
            if (oldVal && guruSelect.querySelector('option[value="' + oldVal + '"]')) {
                guruSelect.value = oldVal;
            }
        }

        // Initial render of guru options
        renderGuruOptionsByKelasTanggal();

        // Re-render guru and jam when kelas or tanggal changes
        if (kelasSelect) kelasSelect.addEventListener('change', function(){ renderGuruOptionsByKelasTanggal(); renderJamOptionsByGuru(); });
        if (tanggalInput) tanggalInput.addEventListener('change', function(){ renderGuruOptionsByKelasTanggal(); renderJamOptionsByGuru(); });
        if (guruSelect) guruSelect.addEventListener('change', function(){
            renderJamOptionsByGuru();
            // when selecting 'all', update jam select UI
            if (this.value === 'all') {
                applyAllJamUi();
            } else {
                restoreJamUi();
            }
        });

        function applyAllJamUi() {
            var jamIds = window._lastAddedJamIds || [];
            var jamInfoBox = document.getElementById('jamInfoBox');
            // build label
            var labels = jamIds.map(function(k){ var jb = jamBelajarServer[k] || {}; return 'Jam ke-' + (jb.urutan || '?'); });
            // set jamBelajarSelect to single disabled option
            if (jamBelajarSelect) {
                jamBelajarSelect.innerHTML = '';
                var opt = document.createElement('option');
                opt.value = 'all';
                opt.text = labels.length ? ('Semua Jam: ' + labels.join(', ')) : 'Semua Jam';
                jamBelajarSelect.appendChild(opt);
                jamBelajarSelect.value = 'all';
                jamBelajarSelect.disabled = true;
            }
            // set hidden fallback jam_belajar_id to first jam id if present
            var firstId = jamIds.length ? jamIds[0] : '';
            var existingHidden = document.querySelector('input[name="jam_belajar_id_hidden"]');
            if (!existingHidden) {
                var hid = document.createElement('input'); hid.type='hidden'; hid.name='jam_belajar_id_hidden'; hid.id='jam_belajar_id_hidden'; hid.value = firstId; formAbsensi.appendChild(hid);
            } else {
                existingHidden.value = firstId;
            }
            if (jamInfoBox) {
                jamInfoBox.innerHTML = '<div class="alert alert-warning mt-2 mb-0 py-2"><i class="ti ti-clock-hour-3 me-2"></i>Anda memilih semua guru; absensi akan diterapkan ke: ' + (labels.length ? labels.join(', ') : 'semua jam') + '.</div>';
            }
        }

        function restoreJamUi() {
            if (jamBelajarSelect) {
                jamBelajarSelect.disabled = false;
                renderJamOptionsByGuru();
            }
            var existingHidden = document.querySelector('input[name="jam_belajar_id_hidden"]');
            if (existingHidden) existingHidden.remove();
        }

        // Handle form submit when 'Pilih semua guru' selected
        var formAbsensi = document.getElementById('formAbsensi');
        formAbsensi.addEventListener('submit', function(e){
            // if guruSelect is 'all', create hidden input apply_to_all_guru=1 and clear guru_id so backend treats as not set
            if (guruSelect && guruSelect.value === 'all') {
                var existing = document.querySelector('input[name="apply_to_all_guru"]');
                if (!existing) {
                    var hid = document.createElement('input');
                    hid.type = 'hidden';
                    hid.name = 'apply_to_all_guru';
                    hid.value = '1';
                    formAbsensi.appendChild(hid);
                }
                // ensure guru_id submitted as empty
                guruSelect.value = '';
            } else {
                var existing = document.querySelector('input[name="apply_to_all_guru"]');
                if (existing) existing.remove();
            }
        });

        // When guru, kelas or tanggal changes, re-render jam options
        if (guruSelect) guruSelect.addEventListener('change', function(){ renderJamOptionsByGuru(); });
        if (tanggalInput) tanggalInput.addEventListener('change', function(){ renderJamOptionsByGuru(); });

        var ambilAbsensiToggle = document.getElementById('ambilAbsensiToggle');

        if (kelasSelect) {
            kelasSelect.addEventListener('change', function() {
                console.log('Kelas changed to:', this.value);
                if (this.value) {
                    loadSiswaByKelas(this.value);
                } else {
                    siswaContainer.style.display = 'none';
                    siswaTableBody.innerHTML = '<tr><td colspan="' + (isGuruPiket ? '10' : '11') + '" class="text-center text-muted"><i class="ti ti-info-circle me-1"></i>Pilih kelas untuk menampilkan daftar siswa</td></tr>';
                    btnSubmit.disabled = true;
                }
            });
        }

        if (ambilAbsensiToggle) {
            ambilAbsensiToggle.addEventListener('change', function() {
                if (kelasSelect && kelasSelect.value) {
                    loadSiswaByKelas(kelasSelect.value);
                }
            });
        }

        function loadSiswaByKelas(kelasId) {
            console.log('Fetching siswa for kelas:', kelasId);
            var tanggalVal = tanggalInput ? tanggalInput.value : '';
            var loadExisting = ambilAbsensiToggle ? ambilAbsensiToggle.checked : false;
            const url = '{{ route("absensi.get-siswa") }}?kelas_id=' + kelasId + (tanggalVal ? '&tanggal=' + tanggalVal : '') + '&load_existing=' + (loadExisting ? '1' : '0');
            console.log('Fetch URL:', url, 'loadExisting:', loadExisting);
            
            fetch(url)
                .then(response => {
                    console.log('Response status:', response.status);
                    return response.json();
                })
                .then(data => {
                    console.log('Response data:', data);
                    if (data.siswa && data.siswa.length > 0) {
                        let html = '';
                        data.siswa.forEach((siswa, index) => {
                            let statusHadirCell =
                                '<td class="text-center">' +
                                    '<input class="status-radio" type="radio" name="absensi_siswa[' + siswa.id + ']" value="hadir" data-siswa-id="' + siswa.id + '">' +
                                '</td>';

                            html += '<tr data-siswa-id="' + siswa.id + '">' +
                                '<td class="text-center">' + (index + 1) + '</td>' +
                                '<td class="text-center">' + (siswa.nis || '-') + '</td>' +
                                '<td class="text-center">' + (siswa.nisn || '-') + '</td>' +
                                '<td>' + siswa.nama + '</td>' +
                                '<td class="text-center">' + (siswa.jenis_kelamin || '-') + '</td>' +
                                statusHadirCell +
                                '<td class="text-center">' +
                                    '<input class="status-radio" type="radio" name="absensi_siswa[' + siswa.id + ']" value="terlambat" data-siswa-id="' + siswa.id + '">' +
                                '</td>' +
                                '<td class="text-center">' +
                                    '<input class="status-radio" type="radio" name="absensi_siswa[' + siswa.id + ']" value="sakit" data-siswa-id="' + siswa.id + '">' +
                                '</td>' +
                                '<td class="text-center">' +
                                    '<input class="status-radio" type="radio" name="absensi_siswa[' + siswa.id + ']" value="izin" data-siswa-id="' + siswa.id + '">' +
                                '</td>' +
                                '<td class="text-center">' +
                                    '<input class="status-radio" type="radio" name="absensi_siswa[' + siswa.id + ']" value="alpa" data-siswa-id="' + siswa.id + '">' +
                                '</td>' +
                                '<td>' +
                                    '<input type="text" name="keterangan_siswa[' + siswa.id + ']" class="form-control form-control-sm" placeholder="Keterangan (opsional)">' +
                                '</td>' +
                            '</tr>';
                        });
                        siswaTableBody.innerHTML = html;
                        siswaContainer.style.display = 'block';
                        btnSubmit.disabled = false;

                        var loadExisting = ambilAbsensiToggle ? ambilAbsensiToggle.checked : false;
                        // If there is existing_absensi returned and toggle is enabled, prefill statuses
                        if (loadExisting && data.existing_absensi) {
                            // choose appropriate jam to use for prefilling
                            var preferJamId = null;
                            var selGuru = guruSelect ? guruSelect.value : '';
                            // if a specific guru selected, prefer absensi by that guru
                            if (selGuru && selGuru !== 'all') {
                                // find absensi entry where guru_id == selGuru
                                for (var jid in data.existing_absensi) {
                                    if (String(data.existing_absensi[jid].guru_id) === String(selGuru)) {
                                        preferJamId = jid; break;
                                    }
                                }
                            }
                            // fallback: choose lowest jam urutan
                            if (!preferJamId) {
                                var lowest = null; var lowestJid = null;
                                for (var jid in data.existing_absensi) {
                                    var ur = data.existing_absensi[jid].jam_urutan || 999;
                                    if (lowest === null || ur < lowest) { lowest = ur; lowestJid = jid; }
                                }
                                preferJamId = lowestJid;
                            }

                            if (preferJamId) {
                                var map = data.existing_absensi[preferJamId].statuses || {};
                                // iterate rows and set radios
                                document.querySelectorAll('#siswaTableBody tr').forEach(function(row){
                                    var sid = row.getAttribute('data-siswa-id');
                                    if (!sid) return;
                                    var status = map[sid];
                                    if (!status) return;
                                    // find radio with value matching normalized status
                                    var norm = status.toLowerCase();
                                    if (norm === 'hadir') norm = 'hadir';
                                    else if (['terlambat','telat'].includes(norm)) norm = 'terlambat';
                                    else if (norm === 'sakit') norm = 'sakit';
                                    else if (['izin','ijin'].includes(norm)) norm = 'izin';
                                    else norm = 'alpa';

                                    var radio = row.querySelector('.status-radio[value="' + norm + '"]');
                                    if (radio) {
                                        radio.checked = true;
                                        updateStatusBadge(norm, row);
                                    }
                                });
                            }
                        }

                        // Attach event listeners to update row highlight
                        document.querySelectorAll('.status-radio').forEach(radio => {
                            radio.addEventListener('change', function() {
                                const row = this.closest('tr');
                                updateStatusBadge(this.value, row);
                            });
                        });

                        // Attach search filter
                        const searchInput = document.getElementById('searchSiswa');
                        if (searchInput && !searchInput.dataset.bound) {
                            searchInput.dataset.bound = '1';
                            searchInput.addEventListener('input', function() {
                                filterSiswaRows(this.value);
                            });
                        }

                        console.log('Siswa loaded successfully, count:', data.siswa.length);
                    } else {
                        siswaTableBody.innerHTML = '<tr><td colspan="' + (isGuruPiket ? '10' : '11') + '" class="text-center text-warning"><i class="ti ti-alert-circle me-1"></i>Tidak ada siswa di kelas ini</td></tr>';
                        btnSubmit.disabled = true;
                    }
                })
                .catch(error => {
                    console.error('Error fetching siswa:', error);
                    siswaTableBody.innerHTML = '<tr><td colspan="' + (isGuruPiket ? '10' : '11') + '" class="text-center text-danger"><i class="ti ti-alert-triangle me-1"></i>Terjadi kesalahan saat memuat data siswa</td></tr>';
                    btnSubmit.disabled = true;
                });
        }
    });

    function updateStatusBadge(value, row) {
        if (!row) return;

        // Remove all status classes
        row.classList.remove('bg-success-subtle', 'bg-warning-subtle', 'bg-info-subtle', 'bg-danger-subtle', 'status-terlambat');
        
        // Add new status class
        if (value === 'hadir') {
            row.classList.add('bg-success-subtle');
        } else if (value === 'terlambat') {
            row.classList.add('status-terlambat');
        } else if (value === 'sakit') {
            row.classList.add('bg-warning-subtle');
        } else if (value === 'izin') {
            row.classList.add('bg-info-subtle');
        } else if (value === 'alpa') {
            row.classList.add('bg-danger-subtle');
        }
    }

    function setAllStatus(status) {
        document.querySelectorAll('.status-radio').forEach(radio => {
            if (radio.value === status) {
                radio.checked = true;
                const row = radio.closest('tr');
                updateStatusBadge(status, row);
            }
        });
    }

    function filterSiswaRows(keyword) {
        const rows = document.querySelectorAll('#siswaTableBody tr');
        const lower = (keyword || '').toLowerCase();
        rows.forEach(row => {
            const text = row.textContent.toLowerCase();
            row.style.display = text.includes(lower) ? '' : 'none';
        });
    }
</script>
@endsection
