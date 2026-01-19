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
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <h3 class="card-title mb-0">Tambah Absensi Kelas</h3>
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
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                    @endif

                    @if(session('warning'))
                    <div class="alert alert-warning alert-dismissible fade show" role="alert">
                        <strong><i class="ti ti-alert-triangle me-2"></i>Perhatian:</strong>
                        {{ session('warning') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                    @endif

                    @if(auth()->user()->guru_id)
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
                        <strong>Mode Akses Cepat:</strong> Kelas dan jam belajar telah diisi otomatis sesuai jadwal hari ini.
                    </div>
                    @endif

                    <form action="{{ route('absensi.store') }}" method="POST" id="formAbsensi">
                        @csrf

                        <input type="hidden" name="tahun_ajaran_id" value="{{ $tahunAjaran->id }}">
                        <input type="hidden" name="semester_id" value="{{ $semester->id }}">

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="tanggal" class="form-label">Tanggal <span class="text-danger">*</span></label>
                                    <input type="date" class="form-control @error('tanggal') is-invalid @enderror" 
                                           id="tanggal" name="tanggal" value="{{ old('tanggal', date('Y-m-d')) }}" required>
                                    @error('tanggal')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="kelas_id" class="form-label">Kelas <span class="text-danger">*</span></label>
                                    <select class="form-select @error('kelas_id') is-invalid @enderror" 
                                            id="kelas_id" name="kelas_id" required
                                            {{ ($isQuickAccess ?? false) ? 'disabled' : '' }}>
                                        <option value="">Pilih Kelas</option>
                                        @foreach($kelasList as $kelas)
                                            <option value="{{ $kelas->id }}" 
                                                {{ old('kelas_id', $selectedKelasId ?? '') == $kelas->id ? 'selected' : '' }}>
                                                {{ $kelas->nama_kelas }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @if($isQuickAccess ?? false)
                                    <input type="hidden" name="kelas_id" value="{{ $selectedKelasId }}">
                                    @endif
                                    @error('kelas_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="guru_id" class="form-label">Guru <span class="text-danger">*</span></label>
                                    <select class="form-select @error('guru_id') is-invalid @enderror" 
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
                                <div class="mb-3">
                                    <label for="jam_belajar_id" class="form-label">Jam Belajar <span class="text-danger">*</span></label>
                                    <select class="form-select @error('jam_belajar_id') is-invalid @enderror" 
                                            id="jam_belajar_id" name="jam_belajar_id" required
                                            {{ ($isQuickAccess ?? false) ? 'disabled' : '' }}>
                                        <option value="">Pilih Jam Belajar</option>
                                        @foreach($jamBelajarList as $jam)
                                            <option value="{{ $jam->id }}" {{ old('jam_belajar_id', $selectedJamBelajarId ?? '') == $jam->id ? 'selected' : '' }}>
                                                Jam ke-{{ $jam->urutan }} ({{ $jam->jam_mulai }} - {{ $jam->jam_selesai }})
                                            </option>
                                        @endforeach
                                    </select>
                                    @if($isQuickAccess ?? false)
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

                            <div class="col-md-12">
                                <div class="mb-3">
                                    <label for="status_kelas" class="form-label">Status Kelas</label>
                                    <input type="text" class="form-control @error('status_kelas') is-invalid @enderror" 
                                           id="status_kelas" name="status_kelas" value="{{ old('status_kelas') }}" 
                                           placeholder="Contoh: Normal, Kondusif, dll">
                                    @error('status_kelas')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <small class="text-muted">Opsional - Kondisi atau keterangan kelas</small>
                                </div>
                            </div>
                        </div>

                        <div class="alert alert-info">
                            <i class="ti ti-info-circle me-2"></i>
                            <strong>Tahun Ajaran:</strong> {{ $tahunAjaran->nama_tahun ?? '-' }} | 
                            <strong>Semester:</strong> {{ $semester->nama_semester ?? '-' }}
                        </div>

                        <!-- Data Siswa -->
                        <div id="siswaContainer" style="display: none;">
                            <div class="card mt-4">
                                <div class="card-header bg-success-subtle d-flex flex-wrap align-items-center gap-3">
                                    <h5 class="mb-0"><i class="ti ti-users me-2"></i>Daftar Siswa & Absensi</h5>
                                    <div class="ms-auto d-flex flex-wrap gap-2">
                                        <button type="button" class="btn btn-sm btn-success" onclick="setAllStatus('hadir')">Ceklis Semua Hadir</button>
                                        <button type="button" class="btn btn-sm btn-warning" onclick="setAllStatus('sakit')">Ceklis Semua Sakit</button>
                                        <button type="button" class="btn btn-sm btn-info" onclick="setAllStatus('izin')">Ceklis Semua Izin</button>
                                        <button type="button" class="btn btn-sm btn-danger" onclick="setAllStatus('alpa')">Ceklis Semua Alpa</button>
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
                                                    <th colspan="4" class="text-center">STATUS</th>
                                                    <th rowspan="2" class="align-middle text-center" width="15%">KETERANGAN</th>
                                                </tr>
                                                <tr>
                                                    <th class="text-center" width="5%">Hadir</th>
                                                    <th class="text-center" width="5%">Sakit</th>
                                                    <th class="text-center" width="5%">Izin</th>
                                                    <th class="text-center" width="8%">Alpa/Tanpa Keterangan</th>
                                                </tr>
                                            </thead>
                                            <tbody id="siswaTableBody">
                                                <tr>
                                                    <td colspan="9" class="text-center text-muted">
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
        var siswaContainer = document.getElementById('siswaContainer');
        var siswaTableBody = document.getElementById('siswaTableBody');
        var btnSubmit = document.getElementById('btnSubmit');
        var isQuickAccess = '{{ $isQuickAccess ?? 0 }}' === '1';

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

        if (kelasSelect) {
            kelasSelect.addEventListener('change', function() {
                console.log('Kelas changed to:', this.value);
                if (this.value) {
                    loadSiswaByKelas(this.value);
                } else {
                    siswaContainer.style.display = 'none';
                    siswaTableBody.innerHTML = '<tr><td colspan="9" class="text-center text-muted"><i class="ti ti-info-circle me-1"></i>Pilih kelas untuk menampilkan daftar siswa</td></tr>';
                    btnSubmit.disabled = true;
                }
            });
        }

        function loadSiswaByKelas(kelasId) {
            console.log('Fetching siswa for kelas:', kelasId);
            const url = '{{ route("absensi.get-siswa") }}?kelas_id=' + kelasId;
            console.log('Fetch URL:', url);
            
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
                            html += '<tr data-siswa-id="' + siswa.id + '">' +
                                '<td class="text-center">' + (index + 1) + '</td>' +
                                '<td class="text-center">' + (siswa.nis || '-') + '</td>' +
                                '<td class="text-center">' + (siswa.nisn || '-') + '</td>' +
                                '<td>' + siswa.nama + '</td>' +
                                '<td class="text-center">' + (siswa.jenis_kelamin || '-') + '</td>' +
                                '<td class="text-center">' +
                                    '<input class="form-check-input status-radio" type="radio" name="absensi_siswa[' + siswa.id + ']" value="hadir" data-siswa-id="' + siswa.id + '">' +
                                '</td>' +
                                '<td class="text-center">' +
                                    '<input class="form-check-input status-radio" type="radio" name="absensi_siswa[' + siswa.id + ']" value="sakit" data-siswa-id="' + siswa.id + '">' +
                                '</td>' +
                                '<td class="text-center">' +
                                    '<input class="form-check-input status-radio" type="radio" name="absensi_siswa[' + siswa.id + ']" value="izin" data-siswa-id="' + siswa.id + '">' +
                                '</td>' +
                                '<td class="text-center">' +
                                    '<input class="form-check-input status-radio" type="radio" name="absensi_siswa[' + siswa.id + ']" value="alpa" data-siswa-id="' + siswa.id + '">' +
                                '</td>' +
                                '<td>' +
                                    '<input type="text" name="keterangan_siswa[' + siswa.id + ']" class="form-control form-control-sm" placeholder="Keterangan (opsional)">' +
                                '</td>' +
                            '</tr>';
                        });
                        siswaTableBody.innerHTML = html;
                        siswaContainer.style.display = 'block';
                        btnSubmit.disabled = false;

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
                        siswaTableBody.innerHTML = '<tr><td colspan="9" class="text-center text-warning"><i class="ti ti-alert-circle me-1"></i>Tidak ada siswa di kelas ini</td></tr>';
                        btnSubmit.disabled = true;
                    }
                })
                .catch(error => {
                    console.error('Error fetching siswa:', error);
                    siswaTableBody.innerHTML = '<tr><td colspan="9" class="text-center text-danger"><i class="ti ti-alert-triangle me-1"></i>Terjadi kesalahan saat memuat data siswa</td></tr>';
                    btnSubmit.disabled = true;
                });
        }
    });

    function updateStatusBadge(value, row) {
        if (!row) return;

        // Remove all status classes
        row.classList.remove('bg-success-subtle', 'bg-warning-subtle', 'bg-info-subtle', 'bg-danger-subtle');
        
        // Add new status class
        if (value === 'hadir') {
            row.classList.add('bg-success-subtle');
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
