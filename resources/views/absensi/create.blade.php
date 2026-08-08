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

    /* List thumbnail: portrait 2:3 for clearer visibility */
    .student-photo {
        width: 60px; /* 2 */
        height: 90px; /* 3 */
        object-fit: cover;
        border-radius: 6px;
        border: 1px solid #e5e7eb;
        box-shadow: 0 1px 2px rgba(0, 0, 0, 0.08);
    }

    .student-photo-placeholder {
        width: 60px;
        height: 90px;
        border-radius: 6px;
        border: 1px dashed #cbd5e1;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        color: #64748b;
        background: #f8fafc;
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

    /* Grid/card styles for siswa grid view */
    .student-card { border: 1px solid #e9ecef; border-radius: 6px; }
    /* Grid/card thumbnails set to 2:3 ratio as well */
    .student-photo-grid { width: 80px; height: 120px; object-fit: cover; border-radius: 6px; }
</style>

<div class="container-fluid">
    @php
        $isAdminOrKepala = auth()->user()->hasAnyRole(['Admin', 'Kepala Sekolah']);
        $isGuruBk = auth()->user()->hasRole('Guru BK');
        $isWaliKelas = auth()->user()->hasRole('Wali Kelas');
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
                        @php
                            $backUrl = request()->get('back');
                            if (empty($backUrl)) {
                                $backUrl = url()->previous();
                            }
                            if (empty($backUrl)) {
                                $backUrl = route('absensi.index');
                            }
                        @endphp
                        <a href="{{ $backUrl }}" class="btn btn-secondary">
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
                                    <input class="form-check-input" type="checkbox" id="loadClassAttendanceToggle" name="load_class_attendance" value="1" {{ old('load_class_attendance') ? 'checked' : '' }}>
                                    <label class="form-check-label" for="loadClassAttendanceToggle">Ambil data absensi kelas</label>
                                </div>
                                <div class="form-text text-muted">
                                    Jika aktif, tabel kehadiran akan terisi otomatis dari absensi kelas yang sudah diisi oleh siswa pada tanggal ini.
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-check form-switch mt-3 pt-2">
                                    <div class="d-flex align-items-center gap-3">
                                        <div class="form-check form-switch m-0">
                                            <input class="form-check-input" type="checkbox" id="verifikasiToggle" name="verifikasi_aktif" value="1" {{ old('verifikasi_aktif', ($verificationActive ?? false)) ? 'checked' : '' }}>
                                            <label class="form-check-label" for="verifikasiToggle">Aktifkan Kode Verifikasi</label>
                                        </div>
                                        <button type="button" id="saveVerificationConfigBtn" class="btn btn-sm btn-primary">Simpan</button>
                                        <div>
                                            <label class="form-label mb-0 small">Masa berlaku</label>
                                            <div class="d-flex gap-2 align-items-center">
                                                <input type="time" id="verificationValidFrom" name="verification_valid_from" class="form-control form-control-sm" value="{{ old('verification_valid_from', $verificationValidFrom ?? '') }}" placeholder="Dari">
                                                <span class="text-muted">s.d.</span>
                                                <input type="time" id="verificationValidTo" name="verification_valid_to" class="form-control form-control-sm" value="{{ old('verification_valid_to', $verificationValidTo ?? '') }}" placeholder="Sampai">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="form-text text-muted">
                                    Jika aktif, sistem akan menampilkan kode verifikasi yang harus digunakan siswa saat absen.
                                </div>
                                <div class="form-text text-muted mt-1">
                                    Kode akan otomatis direfresh setelah waktu habis.
                                </div>
                                <div id="verificationSaveAlert" class="alert d-none mt-2" role="alert"></div>
                                <div id="verificationCodeBox" class="alert alert-success mt-3 p-2" style="display:none;">
                                    <div><strong>Kode Verifikasi:</strong> <span id="verificationCodeLabel" class="fs-3 fw-bold" style="font-size:1.75rem; letter-spacing:0.12em;">-</span></div>
                                    <div class="small text-muted mt-2" id="verificationCountdown">Kode akan kadaluarsa dalam --:--.</div>
                                </div>
                                <div id="verificationStatusMessage" class="form-text text-success mt-2" style="display:none;"></div>
                                <input type="hidden" name="kode_verifikasi" id="kode_verifikasi" value="{{ old('kode_verifikasi', $verificationCode ?? '') }}">
                                <input type="hidden" name="kode_verifikasi_expires_at" id="kode_verifikasi_expires_at" value="{{ old('kode_verifikasi_expires_at', $verificationExpiresAt ?? '') }}">
                                <input type="hidden" id="kode_verifikasi_expires_at_timestamp" value="{{ $verificationExpiresAtTimestamp ?? '' }}">
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
                                        })->unique()->implode(', ');
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

                        
                        @if($isGuruPiket ?? false)
                        <div class="d-flex gap-2 mb-3">
                            <div class="btn-group" role="group" aria-label="Absensi Type">
                                <button type="button" id="tabSiswaBtn" class="btn btn-sm btn-primary active">Siswa</button>
                                <button type="button" id="tabGuruBtn" class="btn btn-sm btn-outline-secondary">Guru</button>
                            </div>
                        </div>
                        @endif

                        <div id="siswaContainer" style="display: none;">
                            <div class="card mt-4">
                                <div class="card-header bg-success-subtle d-flex flex-wrap align-items-center gap-3">
                                    <h5 class="mb-0"><i class="ti ti-users me-2"></i>Daftar Siswa & Absensi</h5>
                                    <div class="ms-auto d-flex flex-wrap gap-2">
                                                <button type="button" class="btn btn-sm btn-success btn-modern" onclick="setAllStatus('hadir')">Ceklis Semua Hadir</button>
                                            <button type="button" class="btn btn-sm btn-orange" onclick="setAllStatus('terlambat')" style="background:#f59e0b;color:#fff;">Ceklis Semua Terlambat</button>
                                            <button type="button" class="btn btn-sm btn-warning" onclick="setAllStatus('sakit')">Ceklis Semua Sakit</button>
                                            <button type="button" class="btn btn-sm btn-info btn-modern" onclick="setAllStatus('izin')">Ceklis Semua Izin</button>
                                            <div class="btn-group btn-group-sm" role="group" aria-label="View toggle">
                                                <button type="button" id="viewListBtn" class="btn btn-outline-secondary active">List</button>
                                                <button type="button" id="viewGridBtn" class="btn btn-outline-secondary">Grid</button>
                                            </div>
                                            <input type="text" id="searchSiswa" class="form-control form-control-sm" placeholder="Cari nama / NIS" style="min-width: 200px;">
                                    </div>
                                </div>
                                <div class="card-body">
                                    <div class="table-responsive siswa-list">
                                        <div id="siswaListWrapper">
                                            <table id="siswaTable" class="table table-bordered table-hover table-absensi">
                                            <thead class="table-light sticky-top">
                                                <tr>
                                                    <th rowspan="2" class="align-middle text-center" width="3%">No</th>
                                                    <th rowspan="2" class="align-middle text-center" width="8%">NIS</th>
                                                    <th rowspan="2" class="align-middle text-center" width="8%">NISN</th>
                                                    <th rowspan="2" class="align-middle text-center" width="8%">FOTO</th>
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
                                                    <td colspan="12" class="text-center text-muted">
                                                        <i class="ti ti-info-circle me-1"></i>Pilih kelas untuk menampilkan daftar siswa
                                                    </td>
                                                </tr>
                                            </tbody>
                                            </table>

                                            <div id="siswaGrid" class="row g-3 mt-2" style="display:none;">
                                                <!-- grid cards will be injected here -->
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        @if($isGuruPiket ?? false)
                        <div id="guruContainer" style="display: none;">
                            <div class="card mt-4">
                                <div class="card-header bg-info-subtle d-flex flex-wrap align-items-center gap-3">
                                    <h5 class="mb-0"><i class="ti ti-chalkboard-teacher me-2"></i>Daftar Guru Piket</h5>
                                    <div class="ms-auto d-flex flex-wrap gap-2">
                                        <button type="button" id="guruViewListBtn" class="btn btn-outline-secondary btn-sm active">List</button>
                                        <button type="button" id="guruViewGridBtn" class="btn btn-outline-secondary btn-sm">Grid</button>
                                        <input type="text" id="searchGuru" class="form-control form-control-sm" placeholder="Cari nama guru" style="min-width: 200px;">
                                    </div>
                                </div>
                                <div class="card-body">
                                    <div class="table-responsive">
                                        <table id="guruTable" class="table table-bordered table-hover">
                                            <thead class="table-light">
                                                <tr>
                                                    <th style="width:4%">No</th>
                                                    <th style="width:12%">Foto</th>
                                                    <th>Nama Guru</th>
                                                    <th style="width:10%" class="text-center">Hadir</th>
                                                    <th style="width:10%" class="text-center">Sakit</th>
                                                    <th style="width:10%" class="text-center">Izin</th>
                                                    <th style="width:10%" class="text-center">Alpa</th>
                                                    <th style="width:20%">Keterangan</th>
                                                </tr>
                                            </thead>
                                            <tbody id="guruTableBody">
                                                <tr><td colspan="8" class="text-center text-muted">Memuat daftar guru piket...</td></tr>
                                            </tbody>
                                        </table>
                                    </div>
                                    <div id="guruGrid" class="row g-3 mt-2" style="display:none;"></div>
                                </div>
                            </div>
                        </div>
                        @endif

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
        var formAbsensi = document.getElementById('formAbsensi');
        var attendanceDraftStoragePrefix = 'absensi-create-draft';

        function getAttendanceDraftStorageKey() {
            var kelasId = document.getElementById('kelas_id') ? document.getElementById('kelas_id').value : '';
            var tanggalVal = document.getElementById('tanggal') ? document.getElementById('tanggal').value : '';
            var guruId = document.getElementById('guru_id') ? document.getElementById('guru_id').value : '';
            var jamId = document.getElementById('jam_belajar_id') ? document.getElementById('jam_belajar_id').value : '';
            return attendanceDraftStoragePrefix + ':' + [kelasId, tanggalVal, guruId, jamId].join('|');
        }

        function saveAttendanceDraft() {
            if (!formAbsensi || !window.localStorage) return;
            var payload = { radios: {}, fields: {} };
            var inputs = formAbsensi.querySelectorAll('input, select, textarea');
            Array.prototype.forEach.call(inputs, function(input) {
                if (!input.name) return;
                if (input.type === 'radio') {
                    if (input.checked) payload.radios[input.name] = input.value;
                } else if (input.type === 'checkbox') {
                    payload.fields[input.name] = input.checked ? '1' : '0';
                } else if (input.tagName === 'SELECT' || input.tagName === 'TEXTAREA' || input.type === 'text' || input.type === 'search' || input.type === 'number' || input.type === 'date') {
                    payload.fields[input.name] = input.value;
                }
            });
            try { window.localStorage.setItem(getAttendanceDraftStorageKey(), JSON.stringify(payload)); } catch (e) {}
        }

        function restoreAttendanceDraft() {
            if (!formAbsensi || !window.localStorage) return;
            var stored = null;
            try { stored = window.localStorage.getItem(getAttendanceDraftStorageKey()); } catch (e) { return; }
            if (!stored) return;
            try { stored = JSON.parse(stored); } catch (e) { return; }
            if (!stored) return;

            if (stored.fields) {
                Object.keys(stored.fields).forEach(function(name) {
                    var matches = formAbsensi.querySelectorAll('[name="' + name + '"]');
                    Array.prototype.forEach.call(matches, function(el) {
                        if (el.type === 'checkbox') {
                            el.checked = stored.fields[name] === '1' || stored.fields[name] === true || stored.fields[name] === 'true';
                        } else {
                            el.value = stored.fields[name] || '';
                        }
                    });
                });
            }

            if (stored.radios) {
                Object.keys(stored.radios).forEach(function(name) {
                    var matches = formAbsensi.querySelectorAll('input[type="radio"][name="' + name + '"]');
                    Array.prototype.forEach.call(matches, function(radio) {
                        var isMatch = String(radio.value) === String(stored.radios[name]);
                        if (isMatch) {
                            radio.checked = true;
                            radio.dispatchEvent(new Event('change', { bubbles: true }));
                        } else {
                            radio.checked = false;
                        }
                    });
                });
            }

            if (typeof refreshAllStatusButtonStates === 'function') {
                refreshAllStatusButtonStates();
            }
        }

        function clearAttendanceDraft() {
            if (!window.localStorage) return;
            try { window.localStorage.removeItem(getAttendanceDraftStorageKey()); } catch (e) {}
        }

        // Ensure query params (kelas_id, tanggal, jam_belajar_id) populate inputs when present
        try {
            var params = new URLSearchParams(window.location.search);
            var qKelas = params.get('kelas_id');
            var qTanggal = params.get('tanggal');
            var qJam = params.get('jam_belajar_id');

            if (qTanggal && tanggalInput && !tanggalInput.value) {
                tanggalInput.value = qTanggal;
            }

            if (qKelas && kelasSelect && !kelasSelect.value) {
                // set selected option if exists
                if (kelasSelect.querySelector('option[value="' + qKelas + '"]')) {
                    kelasSelect.value = qKelas;
                }
            }

            // store requested jam to apply after jam options are rendered
            if (qJam) {
                window._requestedJamBelajarId = qJam;
            }
        } catch (e) {
            console.warn('Failed to apply URL params to form', e);
        }

        console.log('Initializing form...', { kelasValue: kelasSelect ? kelasSelect.value : null, isQuickAccess: isQuickAccess });

        var verificationFromInput = document.getElementById('verificationValidFrom');
        if (verificationFromInput && !verificationFromInput.value) {
            var now = new Date();
            var hours = String(now.getHours()).padStart(2, '0');
            var minutes = String(now.getMinutes()).padStart(2, '0');
            verificationFromInput.value = hours + ':' + minutes;
        }

        // Trigger load siswa jika ada kelas yang sudah dipilih saat page load
        if (kelasSelect && kelasSelect.value) {
            console.log('Preselected kelas detected, refreshing guru/jam and loading siswa for kelas:', kelasSelect.value);
            setTimeout(function() {
                renderGuruOptionsByKelasTanggal();
                renderJamOptionsByGuru();
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

        // guru piket list (for guru piket users)
        var guruPiketList = @json($guruPiketList ?? collect());
        var tabSiswaBtn = document.getElementById('tabSiswaBtn');
        var tabGuruBtn = document.getElementById('tabGuruBtn');
        var guruContainerElem = document.getElementById('guruContainer');
        var guruTableBody = document.getElementById('guruTableBody');
        var guruGrid = document.getElementById('guruGrid');
        var guruViewListBtn = document.getElementById('guruViewListBtn');
        var guruViewGridBtn = document.getElementById('guruViewGridBtn');
        var searchGuru = document.getElementById('searchGuru');

        if (tabSiswaBtn) tabSiswaBtn.addEventListener('click', function(){
            tabSiswaBtn.classList.add('btn-primary'); tabSiswaBtn.classList.remove('btn-outline-secondary');
            tabGuruBtn.classList.remove('btn-primary'); tabGuruBtn.classList.add('btn-outline-secondary');
            document.getElementById('siswaContainer').style.display = '';
            if (guruContainerElem) guruContainerElem.style.display = 'none';
        });

        if (tabGuruBtn) tabGuruBtn.addEventListener('click', function(){
            tabGuruBtn.classList.add('btn-primary'); tabGuruBtn.classList.remove('btn-outline-secondary');
            tabSiswaBtn.classList.remove('btn-primary'); tabSiswaBtn.classList.add('btn-outline-secondary');
            document.getElementById('siswaContainer').style.display = 'none';
            if (guruContainerElem) {
                guruContainerElem.style.display = '';
                renderGuruItems();
            }
        });

        if (guruViewListBtn) guruViewListBtn.addEventListener('click', function(){
            guruViewListBtn.classList.add('active'); guruViewGridBtn.classList.remove('active');
            if (guruGrid) guruGrid.style.display = 'none';
            var gTable = document.getElementById('guruTable'); if (gTable) gTable.style.display = '';
        });
        if (guruViewGridBtn) guruViewGridBtn.addEventListener('click', function(){
            guruViewGridBtn.classList.add('active'); guruViewListBtn.classList.remove('active');
            if (guruGrid) guruGrid.style.display = ''; var gTable = document.getElementById('guruTable'); if (gTable) gTable.style.display = 'none';
        });

        if (searchGuru && !searchGuru.dataset.bound) {
            searchGuru.dataset.bound = '1';
            searchGuru.addEventListener('input', function(){ filterGuruRows(this.value); });
        }

        function renderGuruItems() {
            var data = guruPiketList || [];
            if (!Array.isArray(data)) data = (data || []).slice ? data : [];
            var storageBase = '{{ asset('storage') }}';
            // render table
            if (guruTableBody) {
                var html = '';
                data.forEach(function(guru, idx){
                    var fotoSrc = null;
                    if (guru.foto) {
                        fotoSrc = /^https?:\/\//i.test(guru.foto) ? guru.foto : storageBase + '/' + guru.foto;
                    }
                    var foto = fotoSrc ? '<img src="'+escapeHtml(fotoSrc)+'" class="student-photo" alt="Foto">' : '<div class="student-photo-placeholder"><i class="ti ti-user"></i></div>';
                    html += '<tr data-guru-id="'+guru.id+'">';
                    html += '<td class="text-center">'+(idx+1)+'</td>';
                    html += '<td class="text-center">'+foto+'</td>';
                    html += '<td>'+escapeHtml(guru.nama || '-')+'</td>';
                    ['hadir','sakit','izin','alpa'].forEach(function(val){
                        html += '<td class="text-center"><input type="radio" name="absensi_guru['+guru.id+']" value="'+val+'"></td>';
                    });
                    html += '<td><input type="text" name="keterangan_guru['+guru.id+']" class="form-control form-control-sm" placeholder="Keterangan"></td>';
                    html += '</tr>';
                });
                if (data.length === 0) html = '<tr><td colspan="8" class="text-center text-muted">Tidak ada guru piket untuk hari ini.</td></tr>';
                guruTableBody.innerHTML = html;
            }

            // render grid
            if (guruGrid) {
                guruGrid.innerHTML = '';
                data.forEach(function(guru, idx){
                    var fotoSrc = null;
                    if (guru.foto) {
                        fotoSrc = /^https?:\/\//i.test(guru.foto) ? guru.foto : storageBase + '/' + guru.foto;
                    }
                    var foto = fotoSrc ? ('<img src="'+escapeHtml(fotoSrc)+'" class="student-photo-grid" alt="Foto">') : '<div class="student-photo-placeholder" style="width:80px;height:120px;border-radius:6px;display:flex;align-items:center;justify-content:center;color:#64748b;background:#f8fafc;"></div>';
                    var col = document.createElement('div'); col.className = 'col-6 col-sm-4 col-md-3';
                    col.innerHTML = '<div class="card student-card p-2 h-100" data-guru-id="'+guru.id+'">' +
                        '<div class="d-flex flex-column align-items-center text-center">'+foto + '<div class="mt-2 fw-semibold">'+escapeHtml(guru.nama||'-')+'</div></div>' +
                        '<div class="mt-2 d-flex justify-content-around">' +
                        '<label class="btn btn-sm btn-outline-success"><input type="radio" name="absensi_guru['+guru.id+']" value="hadir">H</label>' +
                        '<label class="btn btn-sm btn-outline-secondary"><input type="radio" name="absensi_guru['+guru.id+']" value="sakit">S</label>' +
                        '<label class="btn btn-sm btn-outline-info"><input type="radio" name="absensi_guru['+guru.id+']" value="izin">I</label>' +
                        '<label class="btn btn-sm btn-outline-danger"><input type="radio" name="absensi_guru['+guru.id+']" value="alpa">A</label>' +
                        '</div>' + '<div class="mt-2"><input type="text" name="keterangan_guru['+guru.id+']" class="form-control form-control-sm" placeholder="Keterangan"></div>' +
                    '</div>';
                    guruGrid.appendChild(col);
                });
            }
            restoreAttendanceDraft();
        }

        function filterGuruRows(keyword) {
            const lower = (keyword || '').toLowerCase();
            const rows = document.querySelectorAll('#guruTableBody tr');
            rows.forEach(r => { r.style.display = r.textContent.toLowerCase().includes(lower) ? '' : 'none'; });
            const cards = document.querySelectorAll('#guruGrid .card');
            cards.forEach(c => { c.parentElement.style.display = c.textContent.toLowerCase().includes(lower) ? '' : 'none'; });
        }

        function renderJamOptionsByGuru() {
            if (!jamBelajarSelect) {
                return;
            }

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
                var itemKelasId = item.kelas_id || item.kelasId || (item.kelas && item.kelas.id) || '';
                var itemGuruId = item.guru_id || (item.guru && item.guru.id) || item.guruId || '';
                if (kelasId && String(itemKelasId) !== String(kelasId)) return false;
                if (hariName && String((item.hari || '').trim()) !== String(hariName)) return false;
                if (guruId && String(guruId) !== 'all') {
                    return String(itemGuruId) === String(guruId);
                }
                return true;
            });

            // Sort and populate select
            filtered.sort(function(a,b){ return (a.jam_ke || 0) - (b.jam_ke || 0); });
            jamBelajarSelect.innerHTML = '<option value="">Pilih Jam Belajar</option>';
            var added = {};

            if (filtered.length === 0 && guruId && String(guruId) !== 'all') {
                // Fallback: if no schedule exists for the selected date, show all schedule slots
                // for this guru and class across any day so the admin can still choose a jam.
                var fallbackFiltered = jadwalList.filter(function(item) {
                    var itemKelasId = item.kelas_id || item.kelasId || (item.kelas && item.kelas.id) || '';
                    var itemGuruId = item.guru_id || (item.guru && item.guru.id) || item.guruId || '';
                    if (kelasId && String(itemKelasId) !== String(kelasId)) return false;
                    if (String(itemGuruId) !== String(guruId)) return false;
                    return true;
                });
                fallbackFiltered.sort(function(a,b){ return (a.jam_ke || 0) - (b.jam_ke || 0); });
                fallbackFiltered.forEach(function(item){
                    var jamId = item.jam_belajar_id || (item.jamBelajar && item.jamBelajar.id) || item.jam_belajar;
                    if (!jamId || added[jamId]) return;
                    added[jamId] = true;
                    var urutan = item.jam_ke || (item.jamBelajar && item.jamBelajar.urutan) || item.urutan || '?';
                    var jamMulai = item.jam_mulai || (item.jamBelajar && item.jamBelajar.jam_mulai) || (jamBelajarServer[jamId] && jamBelajarServer[jamId].jam_mulai) || '';
                    var jamSelesai = item.jam_selesai || (item.jamBelajar && item.jamBelajar.jam_selesai) || (jamBelajarServer[jamId] && jamBelajarServer[jamId].jam_selesai) || '';
                    var label = 'Jam ke-' + urutan + ' (' + jamMulai + ' - ' + jamSelesai + ')';
                    var opt = document.createElement('option');
                    opt.value = jamId;
                    opt.textContent = label;
                    jamBelajarSelect.appendChild(opt);
                });
            }

            if (filtered.length > 0) {
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
            }

            // expose added jam ids for other logic
            window._lastAddedJamIds = Object.keys(added);

            var jamInfoBox = document.getElementById('jamInfoBox');
            if (Object.keys(added).length === 0) {
                jamBelajarSelect.innerHTML = '<option value="">Tidak ada jam KBM aktif untuk pilihan ini</option>';
                if (jamInfoBox) jamInfoBox.innerHTML = '';
            } else {                if (jamInfoBox) {
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

            // If a requested jam id was passed via URL, apply it after options are rendered
            if (window._requestedJamBelajarId) {
                setTimeout(function(){
                    try {
                        if (jamBelajarSelect && jamBelajarSelect.querySelector('option[value="' + window._requestedJamBelajarId + '"]')) {
                            jamBelajarSelect.value = window._requestedJamBelajarId;
                        }
                    } catch (e) {}
                }, 150);
            }

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
        formAbsensi.addEventListener('submit', function(e){
            clearAttendanceDraft();
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
        if (tanggalInput) tanggalInput.addEventListener('change', function(){
            renderJamOptionsByGuru();
            if (loadClassAttendanceToggle && loadClassAttendanceToggle.checked && kelasSelect && kelasSelect.value) {
                loadSiswaByKelas(kelasSelect.value);
            }
        });

        var loadClassAttendanceToggle = document.getElementById('loadClassAttendanceToggle');
        if (loadClassAttendanceToggle) {
            loadClassAttendanceToggle.addEventListener('change', function() {
                if (kelasSelect && kelasSelect.value) {
                    loadSiswaByKelas(kelasSelect.value);
                }
            });
        }
        var viewListBtn = document.getElementById('viewListBtn');
        var viewGridBtn = document.getElementById('viewGridBtn');
        var siswaGrid = document.getElementById('siswaGrid');
        var siswaTable = document.getElementById('siswaTable');
        var currentView = 'list';

        // initialize view from query param
        try {
            var qv = (new URLSearchParams(window.location.search)).get('view');
            if (qv === 'grid') currentView = 'grid';
        } catch (e) {}
        applyViewMode(currentView);

        if (viewListBtn) viewListBtn.addEventListener('click', function(){ applyViewMode('list', true); });
        if (viewGridBtn) viewGridBtn.addEventListener('click', function(){ applyViewMode('grid', true); });

        if (kelasSelect) {
            kelasSelect.addEventListener('change', function() {
                console.log('Kelas changed to:', this.value);
                renderGuruOptionsByKelasTanggal();
                renderJamOptionsByGuru();
                updateVerificationUi();
                if (this.value) {
                    loadSiswaByKelas(this.value);
                } else {
                    siswaContainer.style.display = 'none';
                    siswaTableBody.innerHTML = '<tr><td colspan="' + (isGuruPiket ? '10' : '11') + '" class="text-center text-muted"><i class="ti ti-info-circle me-1"></i>Pilih kelas untuk menampilkan daftar siswa</td></tr>';
                    btnSubmit.disabled = true;
                }
            });
        }

        var verificationToggle = document.getElementById('verifikasiToggle');
        var verificationCodeBox = document.getElementById('verificationCodeBox');
        var verificationCodeLabel = document.getElementById('verificationCodeLabel');
        var verificationCountdown = document.getElementById('verificationCountdown');
        var verificationHidden = document.getElementById('kode_verifikasi');
        var verificationSaveAlert = document.getElementById('verificationSaveAlert');
        var verificationStatusMessage = document.getElementById('verificationStatusMessage');
        var saveVerificationConfigBtn = document.getElementById('saveVerificationConfigBtn');
        var verificationTimeoutSeconds = {{ $verificationTimeoutSeconds ?? 300 }};
        var verificationRemainingSeconds = verificationTimeoutSeconds;
        var verificationTimerInterval = null;
        var verificationIsRefreshing = false;
        window._pendingAttendanceUpdates = window._pendingAttendanceUpdates || {};
        window._attendanceStatuses = window._attendanceStatuses || {};

        function formatCountdown(seconds) {
            var mins = Math.floor(seconds / 60);
            var secs = seconds % 60;
            return String(mins).padStart(2, '0') + ':' + String(secs).padStart(2, '0');
        }
        function parseServerDateTime(value) {
            if (!value) return null;
            var s = String(value).trim();
            var match = s.match(/^(\d{4})-(\d{2})-(\d{2})[ T](\d{2}):(\d{2})(?::(\d{2}))?(?:([+-])(\d{2}):?(\d{2})|Z)?$/);
            if (!match) {
                var parsedFallback = new Date(s.replace(/ /g, 'T'));
                return isNaN(parsedFallback.getTime()) ? null : parsedFallback;
            }

            var year = parseInt(match[1], 10);
            var month = parseInt(match[2], 10) - 1;
            var day = parseInt(match[3], 10);
            var hour = parseInt(match[4], 10);
            var minute = parseInt(match[5], 10);
            var second = parseInt(match[6] || '0', 10);
            var tzSign = match[7];
            var tzHour = parseInt(match[8] || '0', 10);
            var tzMinute = parseInt(match[9] || '0', 10);

            if (!tzSign && !s.endsWith('Z')) {
                return new Date(year, month, day, hour, minute, second);
            }

            var utc = Date.UTC(year, month, day, hour, minute, second);
            if (tzSign) {
                var offset = (tzHour * 60 + tzMinute) * 60000;
                utc += tzSign === '+' ? -offset : offset;
            }
            return new Date(utc);
        }

        function getVerificationTimeRangeText() {
            var from = document.getElementById('verificationValidFrom') ? document.getElementById('verificationValidFrom').value : '';
            var to = document.getElementById('verificationValidTo') ? document.getElementById('verificationValidTo').value : '';
            var countdown = formatCountdown(verificationRemainingSeconds);
            if (from && to) {
                return 'Kode aktif dari ' + from + ' sampai ' + to + ' (' + countdown + ' tersisa).';
            }
            if (to) {
                return 'Kode aktif sampai ' + to + ' (' + countdown + ' tersisa).';
            }
            return 'Kode akan kadaluarsa dalam ' + countdown + '.';
        }

        function updateVerificationCountdownMessage() {
            if (!verificationCountdown) return;
            verificationCountdown.textContent = getVerificationTimeRangeText();
        }

        function startVerificationTimer() {
            if (verificationTimerInterval) {
                clearInterval(verificationTimerInterval);
            }
            verificationRemainingSeconds = Math.max(0, parseInt(verificationRemainingSeconds ?? verificationTimeoutSeconds, 10));
            updateVerificationCountdownMessage();
            verificationTimerInterval = setInterval(function() {
                verificationRemainingSeconds -= 1;
                if (verificationRemainingSeconds <= 0) {
                    if (verificationTimerInterval) {
                        clearInterval(verificationTimerInterval);
                        verificationTimerInterval = null;
                    }
                    if (verificationCodeBox) {
                        verificationCodeBox.style.display = 'none';
                    }
                    if (verificationHidden) {
                        verificationHidden.value = '';
                    }
                    if (verificationCountdown) {
                        verificationCountdown.textContent = 'Kode sudah tidak berlaku.';
                    }
                    return;
                }
                updateVerificationCountdownMessage();
            }, 1000);
        }

        function refreshVerificationCode() {
            // call server endpoint to generate and persist code
            var kelasId = document.getElementById('kelas_id') ? document.getElementById('kelas_id').value : null;
            var jamId = document.getElementById('jam_belajar_id') ? document.getElementById('jam_belajar_id').value : null;
            var tanggalVal = document.getElementById('tanggal') ? document.getElementById('tanggal').value : null;
            var guruId = document.getElementById('guru_id') ? document.getElementById('guru_id').value : null;
            var timeoutSelect = document.getElementById('verificationTimeoutSelect');
            var timeoutSeconds = timeoutSelect ? parseInt(timeoutSelect.value, 10) : verificationTimeoutSeconds;

            if (!kelasId || !tanggalVal) {
                // fallback to client generation if not enough context
                var fallback = '';
                var chars = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789';
                for (var i = 0; i < 6; i++) fallback += chars.charAt(Math.floor(Math.random() * chars.length));
                setVerificationCodeLocal(fallback, verificationTimeoutSeconds);
                return;
            }

            var payload = {
                kelas_id: kelasId,
                jam_belajar_id: jamId || null,
                tanggal: tanggalVal,
                guru_id: guruId || null,
                verification_valid_from: document.getElementById('verificationValidFrom') ? document.getElementById('verificationValidFrom').value : null,
                verification_valid_to: document.getElementById('verificationValidTo') ? document.getElementById('verificationValidTo').value : null,
                timeout_seconds: timeoutSeconds
            };

            verificationIsRefreshing = true;
            fetch('{{ route('absensi.verification.refresh') }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify(payload)
            }).then(function(res){ return res.json(); }).then(function(data){
                if (data && data.success) {
                    setVerificationCodeLocal(data.kode, data.timeout_seconds, data.expires_at_timestamp);
                } else {
                    console.warn('Failed to refresh verification code', data);
                }
            }).catch(function(err){
                console.error('Error refreshing verification code', err);
            });
        }

        function setVerificationCodeLocal(code, timeoutSeconds, expiresAt) {
            verificationIsRefreshing = false;
            if (verificationHidden) verificationHidden.value = code;
            if (verificationCodeLabel) verificationCodeLabel.textContent = code;
            if (verificationCodeBox) verificationCodeBox.style.display = 'block';
            if (typeof expiresAt !== 'undefined' && document.getElementById('kode_verifikasi_expires_at')) {
                var expiresAtDate = new Date(parseInt(expiresAt, 10));
                if (!isNaN(expiresAtDate.getTime())) {
                    document.getElementById('kode_verifikasi_expires_at').value = expiresAtDate.toISOString().slice(0, 19).replace('T', ' ');
                }
            }
            if (typeof expiresAt !== 'undefined' && document.getElementById('kode_verifikasi_expires_at_timestamp')) {
                document.getElementById('kode_verifikasi_expires_at_timestamp').value = expiresAt;
            }
            verificationTimeoutSeconds = parseInt(timeoutSeconds || verificationTimeoutSeconds, 10);
            if (typeof expiresAt !== 'undefined' && expiresAt) {
                var now = Date.now();
                var expiresAtTimestamp = parseInt(expiresAt, 10);
                if (!isNaN(expiresAtTimestamp)) {
                    verificationRemainingSeconds = Math.max(0, Math.round((expiresAtTimestamp - now) / 1000));
                } else {
                    verificationRemainingSeconds = verificationTimeoutSeconds;
                }
            } else {
                verificationRemainingSeconds = verificationTimeoutSeconds;
            }
            startVerificationTimer();
        }

        function showVerificationSaveAlert(message, type) {
            if (!verificationSaveAlert) return;
            verificationSaveAlert.classList.remove('d-none','alert-success','alert-danger','alert-warning','alert-info');
            verificationSaveAlert.classList.add('alert-' + (type || 'info'));
            verificationSaveAlert.textContent = message;
        }

        function isGuruAttendanceChecked() {
            var guruRadios = Array.from(document.querySelectorAll('input[type="radio"][name^="absensi_guru"]'));
            return guruRadios.some(function(radio) { return radio.checked; });
        }

        function setVerificationDisabledByGuruAttendance(disabled) {
            if (verificationToggle) {
                if (disabled) {
                    verificationToggle.checked = false;
                }
                verificationToggle.disabled = disabled;
            }
            if (saveVerificationConfigBtn) {
                saveVerificationConfigBtn.disabled = disabled;
                if (disabled) {
                    saveVerificationConfigBtn.classList.add('disabled');
                } else {
                    saveVerificationConfigBtn.classList.remove('disabled');
                }
            }
            if (verificationCodeBox) {
                if (disabled) {
                    verificationCodeBox.style.display = 'none';
                }
            }
            if (verificationHidden && disabled) {
                verificationHidden.value = '';
            }
            if (verificationCountdown && disabled) {
                verificationCountdown.textContent = '';
            }
            if (verificationStatusMessage) {
                if (disabled) {
                    verificationStatusMessage.style.display = 'block';
                    verificationStatusMessage.textContent = 'Sudah ter verifikasi';
                } else {
                    verificationStatusMessage.style.display = 'none';
                    verificationStatusMessage.textContent = '';
                }
            }
        }

        function updateVerificationStatusFromGuruAttendance() {
            var guruAttendanceSelected = isGuruAttendanceChecked();
            setVerificationDisabledByGuruAttendance(guruAttendanceSelected);
            return guruAttendanceSelected;
        }

        function updateVerificationUi() {
            if (!verificationToggle) {
                return;
            }

            var guruAttendanceSelected = isGuruAttendanceChecked();
            setVerificationDisabledByGuruAttendance(guruAttendanceSelected);
            if (guruAttendanceSelected) {
                return;
            }

            if (verificationToggle.checked) {
                if (!verificationHidden || !verificationHidden.value) {
                    refreshVerificationCode();
                } else {
                    if (verificationCodeLabel) {
                        verificationCodeLabel.textContent = verificationHidden.value;
                    }
                    if (verificationCodeBox) {
                        verificationCodeBox.style.display = 'block';
                    }
                    var expiresAtTimestampInput = document.getElementById('kode_verifikasi_expires_at_timestamp');
                    if (expiresAtTimestampInput && expiresAtTimestampInput.value) {
                        var now = Date.now();
                        var expiresAtTimestamp = parseInt(expiresAtTimestampInput.value, 10);
                        if (!isNaN(expiresAtTimestamp)) {
                            verificationRemainingSeconds = Math.max(0, Math.round((expiresAtTimestamp - now) / 1000));
                        }
                    }
                    startVerificationTimer();
                }
            } else {
                if (verificationTimerInterval) {
                    clearInterval(verificationTimerInterval);
                    verificationTimerInterval = null;
                }
                if (verificationCodeBox) {
                    verificationCodeBox.style.display = 'none';
                }
                if (verificationHidden) {
                    verificationHidden.value = '';
                }
                if (verificationCountdown) {
                    verificationCountdown.textContent = '';
                }
            }
        }

        function saveVerificationConfig() {
            if (!saveVerificationConfigBtn) return;
            saveVerificationConfigBtn.disabled = true;
            showVerificationSaveAlert('Menyimpan konfigurasi...', 'info');

            var kelasId = document.getElementById('kelas_id') ? document.getElementById('kelas_id').value : null;
            var tanggalVal = document.getElementById('tanggal') ? document.getElementById('tanggal').value : null;
            var jamId = document.getElementById('jam_belajar_id') ? document.getElementById('jam_belajar_id').value : null;
            var active = verificationToggle ? verificationToggle.checked : false;
            var timeout = document.getElementById('verificationTimeoutSelect') ? parseInt(document.getElementById('verificationTimeoutSelect').value, 10) : verificationTimeoutSeconds;
            var validFrom = document.getElementById('verificationValidFrom') ? document.getElementById('verificationValidFrom').value : null;
            var validTo = document.getElementById('verificationValidTo') ? document.getElementById('verificationValidTo').value : null;

            fetch('{{ route('absensi.verification.save') }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({
                    kelas_id: kelasId,
                    tanggal: tanggalVal,
                    jam_belajar_id: jamId || null,
                    verifikasi_aktif: active ? 1 : 0,
                    verification_valid_from: validFrom || null,
                    verification_valid_to: validTo || null,
                    timeout_seconds: timeout
                })
            }).then(function(resp){
                return resp.json().then(function(json) {
                    return { ok: resp.ok, status: resp.status, body: json };
                }).catch(function() {
                    return { ok: resp.ok, status: resp.status, body: null };
                });
            }).then(function(result){
                saveVerificationConfigBtn.disabled = false;
                var json = result.body || {};
                if (result.ok && json.success) {
                    showVerificationSaveAlert(json.message || 'Konfigurasi verifikasi berhasil disimpan.', 'success');
                    if (active) {
                        if (json.kode) {
                            if (document.getElementById('verificationValidFrom')) {
                                document.getElementById('verificationValidFrom').value = json.valid_from || validFrom || document.getElementById('verificationValidFrom').value;
                            }
                            if (document.getElementById('verificationValidTo')) {
                                document.getElementById('verificationValidTo').value = json.valid_to || validTo || document.getElementById('verificationValidTo').value;
                            }
                            setVerificationCodeLocal(json.kode, json.timeout_seconds || timeout, json.expires_at_timestamp);
                        } else {
                            refreshVerificationCode();
                        }
                    } else {
                        if (verificationTimerInterval) {
                            clearInterval(verificationTimerInterval);
                            verificationTimerInterval = null;
                        }
                        if (verificationCodeBox) {
                            verificationCodeBox.style.display = 'none';
                        }
                        if (verificationHidden) {
                            verificationHidden.value = '';
                        }
                        if (verificationCountdown) {
                            verificationCountdown.textContent = '';
                        }
                    }
                } else {
                    var message = json.message || 'Gagal menyimpan konfigurasi verifikasi.';
                    if (json.errors) {
                        message = Object.values(json.errors).flat().join(' ');
                    }
                    showVerificationSaveAlert(message, 'danger');
                }
            }).catch(function(err){
                saveVerificationConfigBtn.disabled = false;
                console.error(err);
                showVerificationSaveAlert('Terjadi kesalahan saat menyimpan konfigurasi.', 'danger');
            });
        }

        if (verificationToggle) {
            verificationToggle.addEventListener('change', updateVerificationUi);
        }
        if (saveVerificationConfigBtn) {
            saveVerificationConfigBtn.addEventListener('click', saveVerificationConfig);
        }
        if (formAbsensi) {
            formAbsensi.addEventListener('change', function(event) {
                if (event.target && (event.target.matches('input[type="radio"]') || event.target.matches('select') || event.target.matches('textarea') || event.target.matches('input[type="checkbox"]'))) {
                    saveAttendanceDraft();
                }
                if (event.target && event.target.matches('input[type="radio"][name^="absensi_guru"]')) {
                    updateVerificationStatusFromGuruAttendance();
                }
            });
            formAbsensi.addEventListener('input', function(event) {
                if (event.target && (event.target.matches('input[type="text"]') || event.target.matches('input[type="number"]') || event.target.matches('input[type="search"]') || event.target.matches('textarea'))) {
                    saveAttendanceDraft();
                }
            });
        }
        document.addEventListener('change', function(event) {
            if (event.target && event.target.matches('input[type="radio"][name^="absensi_guru"]')) {
                updateVerificationStatusFromGuruAttendance();
            }
        });
        updateVerificationUi();

        // Teacher-side polling: refresh existing absensi statuses periodically
        var teacherPollingInterval = null;
        var teacherPollingIntervalMs = 7000; // 7 seconds

        function getExistingStatuses(existing) {
            if (!existing || typeof existing !== 'object') {
                return {};
            }
            if (existing.daily && existing.daily.statuses && Object.keys(existing.daily.statuses).length > 0) {
                return existing.daily.statuses;
            }
            var selJam = (document.getElementById('jam_belajar_id') && document.getElementById('jam_belajar_id').value) || null;
            if (selJam && existing[selJam] && existing[selJam].statuses && Object.keys(existing[selJam].statuses).length > 0) {
                return existing[selJam].statuses;
            }
            for (var k in existing) {
                if (existing[k] && existing[k].statuses && Object.keys(existing[k].statuses).length > 0) {
                    return existing[k].statuses;
                }
            }
            return {};
        }

        function applyPendingAttendanceUpdates() {
            if (!window._pendingAttendanceUpdates || !window._siswaCache) {
                return;
            }
            Object.keys(window._pendingAttendanceUpdates).forEach(function(sid) {
                var status = window._pendingAttendanceUpdates[sid];
                var norm = normalizeAttendanceStatus(status);
                var row = document.querySelector('#siswaTableBody tr[data-siswa-id="'+sid+'"]');
                var radios = [];
                if (row) {
                    radios = Array.from(row.querySelectorAll('.status-radio[value="'+norm+'"]'));
                }
                if (radios.length === 0) {
                    var card = document.querySelector('.student-card[data-siswa-id="'+sid+'"]');
                    if (card) {
                        radios = Array.from(card.querySelectorAll('.status-radio[value="'+norm+'"]'));
                    }
                }
                radios.forEach(function(radio) {
                    if (!radio.checked) {
                        radio.checked = true;
                        radio.dispatchEvent(new Event('change', { bubbles: true }));
                    }
                    var rrow = radio.closest('tr');
                    if (rrow) updateStatusBadge(norm, rrow);
                    var label = radio.closest('label');
                    if (label) label.classList.add('active');
                });
                if (radios.length > 0) {
                    delete window._pendingAttendanceUpdates[sid];
                }
            });
            refreshAllStatusButtonStates();
        }

        function applyPolledStatuses(existing) {
            try {
                if (!existing) return;

                if (!window._pendingAttendanceUpdates) {
                    window._pendingAttendanceUpdates = {};
                }

                var statuses = getExistingStatuses(existing);
                Object.keys(statuses).forEach(function(sid) {
                    var norm = normalizeAttendanceStatus(statuses[sid]);
                    var radios = [];
                    var row = document.querySelector('#siswaTableBody tr[data-siswa-id="'+sid+'"]');
                    if (row) {
                        radios = Array.from(row.querySelectorAll('.status-radio[value="'+norm+'"]'));
                    }
                    if (radios.length === 0) {
                        var card = document.querySelector('.student-card[data-siswa-id="'+sid+'"]');
                        if (card) {
                            radios = Array.from(card.querySelectorAll('.status-radio[value="'+norm+'"]'));
                        }
                    }
                    if (radios.length === 0) {
                        radios = Array.from(document.querySelectorAll('input.status-radio[data-siswa-id="'+sid+'"]')).filter(function(radio) {
                            return radio.value === norm;
                        });
                    }
                    if (radios.length === 0) {
                        radios = Array.from(document.querySelectorAll('input.status-radio[name="absensi_siswa['+sid+']"][value="'+norm+'"]'));
                    }

                    if (radios.length === 0) {
                        window._pendingAttendanceUpdates[sid] = norm;
                        return;
                    }

                    radios.forEach(function(radio) {
                        if (!radio.checked) {
                            radio.checked = true;
                            radio.dispatchEvent(new Event('change', { bubbles: true }));
                        }
                        var row = radio.closest('tr');
                        if (row) updateStatusBadge(norm, row);
                        var label = radio.closest('label');
                        if (label) label.classList.add('active');
                    });
                });

                refreshAllStatusButtonStates();
            } catch (e) { console.warn('applyPolledStatuses error', e); }
        }

        function startTeacherPolling() {
            if (teacherPollingInterval) return;
            teacherPollingInterval = setInterval(function(){
                try {
                    var kelasId = document.getElementById('kelas_id') ? document.getElementById('kelas_id').value : null;
                    var tanggalVal = document.getElementById('tanggal') ? document.getElementById('tanggal').value : null;
                    if (!kelasId || !tanggalVal) return;
                    var url = '{{ route("absensi.get-siswa") }}?kelas_id='+encodeURIComponent(kelasId)+'&tanggal='+encodeURIComponent(tanggalVal)+'&load_existing=1';
                    fetch(url).then(r=>r.json()).then(function(json){ if (json && json.existing_absensi) applyPolledStatuses(json.existing_absensi); }).catch(function(e){ /*ignore*/ });
                } catch (e) {}
            }, teacherPollingIntervalMs);
        }

        function stopTeacherPolling() { if (teacherPollingInterval) { clearInterval(teacherPollingInterval); teacherPollingInterval = null; } }

        // Start/stop polling based on verification toggle state
        if (verificationToggle) {
            verificationToggle.addEventListener('change', function(){ if (verificationToggle.checked) startTeacherPolling(); else stopTeacherPolling(); });
        }
        // If verification is currently active on load, start polling
        if (verificationToggle && verificationToggle.checked) startTeacherPolling();

        // Real-time push via Laravel Echo (Pusher / Echo server). If Echo is available, subscribe to class channel.
        try {
            if (window.Echo) {
                var subscribeToRealtime = function() {
                    try {
                        var kelasIdRt = document.getElementById('kelas_id') ? document.getElementById('kelas_id').value : null;
                        var tanggalRt = document.getElementById('tanggal') ? document.getElementById('tanggal').value : null;
                        if (!kelasIdRt || !tanggalRt) return;
                        var channelName = 'absensi-kelas.' + kelasIdRt + '.' + tanggalRt;
                        // listen for StudentVerified events
                        window.Echo.channel(channelName).listen('.StudentVerified', function(e) {
                            try {
                                var payload = {};
                                payload['daily'] = { statuses: {} };
                                payload['daily'].statuses[e.siswa_id] = e.status;
                                applyPolledStatuses(payload);
                            } catch (er) { console.warn('echo event handler error', er); }
                        });
                    } catch (e) { console.warn('Realtime subscribe failed', e); }
                };
                // subscribe initially and re-subscribe on kelas/tanggal change
                subscribeToRealtime();
                ['kelas_id','tanggal'].forEach(function(id){ var el = document.getElementById(id); if (el) el.addEventListener('change', function(){ subscribeToRealtime(); }); });
            }
        } catch (e) { /* ignore */ }

        function loadSiswaByKelas(kelasId) {
            console.log('Fetching siswa for kelas:', kelasId);
            var tanggalVal = tanggalInput ? tanggalInput.value : '';
            var loadExisting = loadClassAttendanceToggle ? loadClassAttendanceToggle.checked : false;
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
                        window._siswaCache = data.siswa;
                        window._existingAbsensiCache = data.existing_absensi || {};
                        window._activeIzinKegiatanCache = data.active_izin_kegiatan || {};
                        siswaContainer.style.display = 'block';
                        btnSubmit.disabled = false;
                        renderSiswaItems();

                        const searchInput = document.getElementById('searchSiswa');
                        if (searchInput && !searchInput.dataset.bound) {
                            searchInput.dataset.bound = '1';
                            searchInput.addEventListener('input', function() {
                                filterSiswaRows(this.value);
                            });
                        }
                        setTimeout(function() {
                            applyExistingAttendanceStatuses(window._existingAbsensiCache);
                            applyIzinKegiatanLocks();
                            applyPendingAttendanceUpdates();
                        }, 50);
                        console.log('Siswa loaded successfully, count:', data.siswa.length);
                    } else {
                        siswaContainer.style.display = 'block';
                        var tblBody = document.getElementById('siswaTableBody');
                        if (tblBody) tblBody.innerHTML = '<tr><td colspan="12" class="text-center text-warning"><i class="ti ti-alert-circle me-1"></i>Tidak ada siswa di kelas ini</td></tr>';
                        if (siswaGrid) siswaGrid.innerHTML = '<div class="col-12 text-center text-warning"><i class="ti ti-alert-circle me-1"></i>Tidak ada siswa di kelas ini</div>';
                        btnSubmit.disabled = true;
                    }
                })
                .catch(error => {
                    console.error('Error fetching siswa:', error);
                    siswaTableBody.innerHTML = '<tr><td colspan="12" class="text-center text-danger"><i class="ti ti-alert-triangle me-1"></i>Terjadi kesalahan saat memuat data siswa</td></tr>';
                    btnSubmit.disabled = true;
                });
        }
    });

    function normalizeAttendanceStatus(status) {
        var norm = String(status || '').toLowerCase().trim();
        if (norm === 'telat') norm = 'terlambat';
        if (norm === 'ijin') norm = 'izin';
        if (!['hadir','terlambat','sakit','izin','alpa'].includes(norm)) norm = 'alpa';
        return norm;
    }

    function escapeHtml(value) {
        return String(value ?? '')
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/\"/g, '&quot;')
            .replace(/'/g, '&#39;');
    }

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

    function applyViewMode(mode, replaceUrl) {
        currentView = (mode === 'grid') ? 'grid' : 'list';
        if (currentView === 'grid') {
            if (viewGridBtn) viewGridBtn.classList.add('active');
            if (viewListBtn) viewListBtn.classList.remove('active');
            if (siswaGrid) siswaGrid.style.display = '';
            if (siswaTable) siswaTable.style.display = 'none';
        } else {
            if (viewGridBtn) viewGridBtn.classList.remove('active');
            if (viewListBtn) viewListBtn.classList.add('active');
            if (siswaGrid) siswaGrid.style.display = 'none';
            if (siswaTable) siswaTable.style.display = '';
        }
        if (replaceUrl) {
            try {
                var params = new URLSearchParams(window.location.search);
                params.set('view', currentView);
                var newUrl = window.location.pathname + '?' + params.toString();
                history.replaceState({}, '', newUrl);
            } catch (e) {}
        }
        // re-render using cached data if present
        if (window._siswaCache) renderSiswaItems();
    }

    function getPreferredExistingStatuses(existingData) {
        if (!existingData || typeof existingData !== 'object') {
            return {};
        }

        if (existingData.daily && existingData.daily.statuses && Object.keys(existingData.daily.statuses).length > 0) {
            return existingData.daily.statuses;
        }

        var selectedJamId = document.getElementById('jam_belajar_id') ? document.getElementById('jam_belajar_id').value : '';
        if (selectedJamId && existingData[selectedJamId] && existingData[selectedJamId].statuses) {
            return existingData[selectedJamId].statuses;
        }

        var selectedGuruId = document.getElementById('guru_id') ? document.getElementById('guru_id').value : '';
        if (selectedGuruId && selectedGuruId !== 'all') {
            for (var jid in existingData) {
                if (existingData[jid] && String(existingData[jid].guru_id || '') === String(selectedGuruId)) {
                    return existingData[jid].statuses || {};
                }
            }
        }

        var lowest = null;
        var lowestJ = null;
        for (var jid2 in existingData) {
            if (!existingData[jid2]) continue;
            var ur = parseInt(existingData[jid2].jam_urutan, 10);
            if (isNaN(ur)) ur = 999;
            if (lowest === null || ur < lowest) {
                lowest = ur;
                lowestJ = jid2;
            }
        }

        if (lowestJ && existingData[lowestJ] && existingData[lowestJ].statuses) {
            return existingData[lowestJ].statuses;
        }

        return {};
    }

    function applyExistingAttendanceStatuses(existingData) {
        var statuses = getPreferredExistingStatuses(existingData || window._existingAbsensiCache || {});
        var appliedAny = false;

        function findPreferredRadio(groupName, value) {
            var radios = Array.from(document.getElementsByName(groupName)).filter(function(radio) {
                return !radio.disabled;
            });
            if (!radios.length) return null;
            return radios.find(function(radio) { return radio.value === value && isVisibleElement(radio); })
                || radios.find(function(radio) { return radio.value === value; })
                || radios.find(function(radio) { return isVisibleElement(radio); })
                || radios[0];
        }

        Object.keys(statuses).forEach(function(sid){
            var status = statuses[sid];
            if (!status) return;
            var norm = normalizeAttendanceStatus(status);
            var groupName = 'absensi_siswa[' + sid + ']';
            var radio = findPreferredRadio(groupName, norm);
            if (!radio) return;

            if (!radio.checked) {
                radio.checked = true;
            }
            window._attendanceStatuses[sid] = norm;

            syncStatusButtonStates(groupName);
            var row = radio.closest('tr');
            if (row) updateStatusBadge(norm, row);
            appliedAny = true;
        });

        if (appliedAny) {
            refreshAllStatusButtonStates();
        }
    }

    function applyIzinKegiatanLocks() {
        var izinMap = window._activeIzinKegiatanCache || {};
        if (!Object.keys(izinMap).length) return;

        Object.keys(izinMap).forEach(function(siswaId) {
            var izin = izinMap[siswaId];
            var groupName = 'absensi_siswa[' + siswaId + ']';
            var radios = Array.from(document.getElementsByName(groupName));
            var hadirRadio = radios.find(function(r) { return r.value === 'hadir'; });
            if (hadirRadio && !hadirRadio.checked) {
                hadirRadio.checked = true;
                hadirRadio.dispatchEvent(new Event('change', { bubbles: true }));
            }
            radios.forEach(function(r) { r.disabled = true; });
            syncStatusButtonStates(groupName);

            var row = hadirRadio ? hadirRadio.closest('tr') : null;
            if (row) {
                row.classList.add('bg-success-subtle');
                var badgeCell = row.querySelector('.izin-lock-badge');
                if (!badgeCell) {
                    var keteranganCell = row.querySelector('input[name="keterangan_siswa['+siswaId+']"]');
                    if (keteranganCell && !keteranganCell.parentElement.querySelector('.izin-lock-badge')) {
                        var badge = document.createElement('span');
                        badge.className = 'badge bg-info ms-1 izin-lock-badge';
                        badge.textContent = 'Izin Kegiatan';
                        keteranganCell.parentElement.appendChild(badge);
                    }
                }
            }

            var keteranganInputs = document.querySelectorAll('input[name="keterangan_siswa['+siswaId+']"]');
            keteranganInputs.forEach(function(input) {
                if (izin && izin.keterangan_kegiatan) {
                    input.value = izin.keterangan_kegiatan;
                }
                input.readOnly = true;
                input.classList.add('bg-light');
            });

            var gridCard = document.querySelector('.student-card[data-siswa-id="'+siswaId+'"]');
            if (gridCard) {
                var gridKeterangan = gridCard.querySelector('input[name="keterangan_siswa['+siswaId+']"]');
                if (gridKeterangan) {
                    if (izin && izin.keterangan_kegiatan) {
                        gridKeterangan.value = izin.keterangan_kegiatan;
                    }
                    gridKeterangan.readOnly = true;
                    gridKeterangan.classList.add('bg-light');
                }
                var lockBadge = gridCard.querySelector('.izin-lock-badge');
                if (!lockBadge) {
                    var info = gridCard.querySelector('.text-muted');
                    if (info && !gridCard.querySelector('.izin-lock-badge')) {
                        var badge = document.createElement('span');
                        badge.className = 'badge bg-info ms-1 izin-lock-badge';
                        badge.textContent = 'Izin Kegiatan';
                        info.appendChild(badge);
                    }
                }
            }
        });
    }

    function renderListSiswaItems(data, existingStatuses, isTableMode) {
        var tbody = document.getElementById('siswaTableBody');
        if (!tbody) return;

        var html = '';
        data.forEach(function(siswa, index){
            var selectedStatus = window._attendanceStatuses[siswa.id] || (existingStatuses[siswa.id] ? normalizeAttendanceStatus(existingStatuses[siswa.id]) : null);
            var disabledAttr = isTableMode ? '' : ' disabled';
            var nameAttr = isTableMode ? ' name="absensi_siswa['+siswa.id+']"' : '';
            html += '<tr data-siswa-id="' + siswa.id + '">';
            html += '<td class="text-center">' + (index+1) + '</td>';
            html += '<td class="text-center">' + escapeHtml(siswa.nis || '-') + '</td>';
            html += '<td class="text-center">' + escapeHtml(siswa.nisn || '-') + '</td>';
            html += '<td class="text-center">' + (siswa.foto_url ? '<img src="' + escapeHtml(siswa.foto_url) + '" alt="Foto ' + escapeHtml(siswa.nama || '-') + '" class="student-photo">' : '<div class="student-photo-placeholder"><i class="ti ti-user"></i></div>') + '</td>';
            html += '<td>' + escapeHtml(siswa.nama || '-') + '</td>';
            html += '<td class="text-center">' + escapeHtml(siswa.jenis_kelamin || '-') + '</td>';
            ['hadir','terlambat','sakit','izin','alpa'].forEach(function(val){
                var checkedAttr = isTableMode && selectedStatus === val ? ' checked' : '';
                html += '<td class="text-center"><input class="status-radio" type="radio"' + nameAttr + ' value="'+val+'" data-siswa-id="'+siswa.id+'"' + checkedAttr + disabledAttr + '></td>';
            });
            html += '<td><input type="text" name="keterangan_siswa['+siswa.id+']" class="form-control form-control-sm" placeholder="Keterangan (opsional)"' + disabledAttr + '></td>';
            html += '</tr>';
        });

        tbody.innerHTML = html;
    }

    function renderGridSiswaItems(data, existingStatuses, isGridMode) {
        if (!siswaGrid) return;

        siswaGrid.innerHTML = '';
        data.forEach(function(siswa){
            var selectedStatus = window._attendanceStatuses[siswa.id] || (existingStatuses[siswa.id] ? normalizeAttendanceStatus(existingStatuses[siswa.id]) : null);
            var disabledAttr = isGridMode ? '' : ' disabled';
            var nameAttr = isGridMode ? ' name="absensi_siswa['+siswa.id+']"' : '';
            var foto = siswa.foto_url ? ('<img src="'+escapeHtml(siswa.foto_url)+'" class="student-photo-grid" alt="Foto">') : '<div class="student-photo-placeholder" style="width:90px;height:120px;border-radius:6px;display:flex;align-items:center;justify-content:center;color:#64748b;background:#f8fafc;"><i class="ti ti-user"></i></div>';
            var card = document.createElement('div');
            card.className = 'col-6 col-sm-4 col-md-3';
            card.innerHTML = '<div class="card student-card p-2 h-100" data-siswa-id="'+siswa.id+'">' +
                '<div class="d-flex flex-column align-items-center text-center">' + foto +
                '<div class="mt-2 fw-semibold">'+escapeHtml(siswa.nama || '-')+'</div>' +
                '<div class="text-muted small">'+escapeHtml(siswa.nis || '')+'</div>' +
                '</div>' +
                '<div class="mt-2 d-flex justify-content-around">' +
                    '<label class="btn btn-sm btn-outline-success"><input type="radio" class="status-radio"' + nameAttr + ' value="hadir" data-siswa-id="'+siswa.id+'"' + (isGridMode && selectedStatus === 'hadir' ? ' checked' : '') + disabledAttr + '>H</label>' +
                    '<label class="btn btn-sm btn-outline-warning"><input type="radio" class="status-radio"' + nameAttr + ' value="terlambat" data-siswa-id="'+siswa.id+'"' + (isGridMode && selectedStatus === 'terlambat' ? ' checked' : '') + disabledAttr + '>T</label>' +
                    '<label class="btn btn-sm btn-outline-secondary"><input type="radio" class="status-radio"' + nameAttr + ' value="sakit" data-siswa-id="'+siswa.id+'"' + (isGridMode && selectedStatus === 'sakit' ? ' checked' : '') + disabledAttr + '>S</label>' +
                '</div>' +
                '<div class="mt-2 d-flex justify-content-around">' +
                    '<label class="btn btn-sm btn-outline-info"><input type="radio" class="status-radio"' + nameAttr + ' value="izin" data-siswa-id="'+siswa.id+'"' + (isGridMode && selectedStatus === 'izin' ? ' checked' : '') + disabledAttr + '>I</label>' +
                    '<label class="btn btn-sm btn-outline-danger"><input type="radio" class="status-radio"' + nameAttr + ' value="alpa" data-siswa-id="'+siswa.id+'"' + (isGridMode && selectedStatus === 'alpa' ? ' checked' : '') + disabledAttr + '>A</label>' +
                '</div>' +
                '<div class="mt-2"><input type="text" name="keterangan_siswa['+siswa.id+']" class="form-control form-control-sm" placeholder="Keterangan"' + disabledAttr + '></div>' +
            '</div>';
            siswaGrid.appendChild(card);
        });
    }

    function renderSiswaItems() {
        var data = window._siswaCache || [];
        var existing = window._existingAbsensiCache || {};
        var existingStatuses = getPreferredExistingStatuses(existing);
        var isGridMode = currentView === 'grid';
        var isTableMode = !isGridMode;

        renderListSiswaItems(data, existingStatuses, isTableMode);
        renderGridSiswaItems(data, existingStatuses, isGridMode);

        bindStatusRadioHandlers();
        refreshAllStatusButtonStates();

        document.querySelectorAll('.status-radio').forEach(function(radio){
            radio.removeEventListener('change', statusRadioHandler);
            radio.addEventListener('change', statusRadioHandler);
        });
        refreshAllStatusButtonStates();

        applyIzinKegiatanLocks();
    }

    function statusRadioHandler() {
        var siswaId = this.dataset.siswaId || this.name.replace(/^absensi_siswa\[(\d+)\]$/, '$1');
        if (siswaId) {
            window._attendanceStatuses[siswaId] = this.value;
        }
        var row = this.closest('tr');
        if (row) updateStatusBadge(this.value, row);
        syncStatusButtonStates(this.name);
    }

    function bindStatusRadioHandlers() {
        document.querySelectorAll('.status-radio').forEach(function(radio) {
            radio.removeEventListener('change', statusRadioHandler);
            radio.addEventListener('change', statusRadioHandler);
        });
    }

    function syncStatusButtonStates(groupName) {
        var radios = document.getElementsByName(groupName);
        radios.forEach(function(radio) {
            var label = radio.closest('label');
            if (!label) return;
            if (radio.checked) {
                label.classList.add('active');
            } else {
                label.classList.remove('active');
            }
        });
    }

    function refreshAllStatusButtonStates() {
        document.querySelectorAll('.status-radio').forEach(function(radio) {
            var label = radio.closest('label');
            if (!label) return;
            if (radio.checked) {
                label.classList.add('active');
            } else {
                label.classList.remove('active');
            }
        });
    }

    function isVisibleElement(el) {
        if (!el) return false;
        if (el.offsetWidth || el.offsetHeight || el.getClientRects().length) return true;
        var label = el.closest('label');
        return !!(label && (label.offsetWidth || label.offsetHeight || label.getClientRects().length));
    }

    function setAllStatus(status) {
        var radios = Array.from(document.querySelectorAll('.status-radio'));
        if (!radios.length) return;

        var handledNames = {};
        radios.forEach(function(radio) {
            var groupName = radio.name;
            if (handledNames[groupName] || radio.value !== status) return;

            var group = Array.from(document.getElementsByName(groupName));
            // Prefer a radio with the requested value that is visible in the current view (table or grid).
            // Fallback to any radio with the requested value, then any visible radio, then first in group.
            var target = group.find(function(r) { return r.value === status && isVisibleElement(r); })
                      || group.find(function(r) { return r.value === status; })
                      || group.find(function(r) { return isVisibleElement(r); })
                      || group[0];
            if (!target) return;

            if (!target.checked) {
                target.checked = true;
                target.dispatchEvent(new Event('change', { bubbles: true }));
                syncStatusButtonStates(groupName);
            } else {
                var row = target.closest('tr');
                if (row) updateStatusBadge(status, row);
                syncStatusButtonStates(groupName);
            }
            handledNames[groupName] = true;
        });
    }

    function filterSiswaRows(keyword) {
        const lower = (keyword || '').toLowerCase();
        if (currentView === 'grid') {
            // filter grid cards
            const cards = document.querySelectorAll('#siswaGrid .student-card');
            cards.forEach(card => {
                const text = card.textContent.toLowerCase();
                card.parentElement.style.display = text.includes(lower) ? '' : 'none';
            });
        } else {
            const rows = document.querySelectorAll('#siswaTableBody tr');
            rows.forEach(row => {
                const text = row.textContent.toLowerCase();
                row.style.display = text.includes(lower) ? '' : 'none';
            });
        }
    }
</script>
@endsection
