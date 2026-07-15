@extends('layouts.app')

@section('title', 'Preview Import ASC Time Table')

@section('content')
<div class="container-xl">
    <div class="page-header d-print-none">
        <div class="row align-items-center">
            <div class="col">
                <h2 class="page-title">Preview Import ASC Time Table</h2>
                <div class="text-muted mt-1">Periksa data dan pilih kategori yang akan diimport</div>
            </div>
            <div class="col-auto ms-auto">
                <div >
                    <a href="{{ route('asc_timetable.index') }}" class="btn btn-outline-secondary btn-modern">
                        <i class="ti ti-x me-2"></i>Batal
                    </a>
                </div>
            </div>
        </div>
    </div>

    
    <form action="{{ route('asc_timetable.confirm_import') }}" method="POST" id="importForm">
        @csrf
        
        
        @if(collect($preview['teachers'])->whereIn('status', ['exists_kode', 'exists_nama'])->count() > 0)
        <div class="alert alert-warning alert-dismissible fade show" role="alert">
            <div class="d-flex">
                <div>
                    <i class="ti ti-alert-triangle icon alert-icon"></i>
                </div>
                <div>
                    <h4 class="alert-title">Peringatan: Data Guru Duplikat Ditemukan!</h4>
                    <div class="text-secondary">
                        Ditemukan <strong>{{ collect($preview['teachers'])->whereIn('status', ['exists_kode', 'exists_nama'])->count() }}</strong> guru dengan data yang sama.
                        <br>Silakan pilih aksi untuk setiap guru duplikat di tab "Guru" di bawah:
                        <ul class="mb-0 mt-2">
                            <li><strong>Lewati</strong> - Abaikan data baru, tetap gunakan data lama</li>
                            <li><strong>Replace Data Lama</strong> - Timpa data lama dengan data baru</li>
                            <li><strong>Tambah Sebagai Baru</strong> - Tambahkan dengan kode guru yang berbeda (hanya untuk duplikat kode)</li>
                        </ul>
                    </div>
                </div>
            </div>
            <button type="button" class="close" data-bs-dismiss="alert"><span aria-hidden="true">&times;</span></button>
        </div>
        @endif
        
        
        <div class="card mb-2">
            <div class="card-header border-0 pt-3 pb-2">
                <h3 class="card-title">
                    <i class="ti ti-checkbox me-2"></i>Pilih Data yang Akan Diimport
                </h3>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-12">
                        <div class="form-check form-switch mb-2">
                            <input class="form-check-input" type="checkbox" id="check_all" checked>
                            <label class="form-check-label" for="check_all">
                                <strong>Pilih Semua</strong>
                            </label>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-3">
                        <div class="form-check form-switch mb-2">
                            <input class="form-check-input import-check" type="checkbox" name="import_types[]" value="periods" id="check_periods" checked>
                            <label class="form-check-label" for="check_periods">
                                <i class="ti ti-clock text-primary me-1"></i>
                                Jam Belajar ({{ count($preview['periods']) }})
                            </label>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-check form-switch mb-2">
                            <input class="form-check-input import-check" type="checkbox" name="import_types[]" value="subjects" id="check_subjects" checked>
                            <label class="form-check-label" for="check_subjects">
                                <i class="ti ti-book text-success me-1"></i>
                                Mata Pelajaran 
                                <span class="badge bg-success">{{ collect($preview['subjects'])->where('status', 'new')->count() }}</span>
                            </label>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-check form-switch mb-2">
                            <input class="form-check-input import-check" type="checkbox" name="import_types[]" value="teachers" id="check_teachers" checked>
                            <label class="form-check-label" for="check_teachers">
                                <i class="ti ti-user text-info me-1"></i>
                                Guru 
                                <span class="badge bg-success">{{ collect($preview['teachers'])->where('status', 'new')->count() }}</span>
                                @if(collect($preview['teachers'])->whereIn('status', ['exists_kode', 'exists_nama'])->count() > 0)
                                    <span class="badge bg-warning">{{ collect($preview['teachers'])->whereIn('status', ['exists_kode', 'exists_nama'])->count() }} duplikat</span>
                                @endif
                            </label>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-check form-switch mb-2">
                            <input class="form-check-input import-check" type="checkbox" name="import_types[]" value="classes" id="check_classes" checked>
                            <label class="form-check-label" for="check_classes">
                                <i class="ti ti-building text-warning me-1"></i>
                                Kelas 
                                <span class="badge bg-success">{{ collect($preview['classes'])->where('status', 'new')->count() }}</span>
                            </label>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-check form-switch mb-2">
                            <input class="form-check-input import-check" type="checkbox" name="import_types[]" value="lessons" id="check_lessons" checked>
                            <label class="form-check-label" for="check_lessons">
                                <i class="ti ti-calendar text-danger me-1"></i>
                                Jadwal KBM ({{ count($preview['lessons']) }})
                            </label>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    
    <div class="row mb-2">
        <div class="col-md-2">
            <div class="card">
                <div class="card-body p-3 text-center">
                    <div class="text-muted mb-1">Jam Belajar</div>
                    <div class="h2 mb-0">{{ count($preview['periods']) }}</div>
                </div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card">
                <div class="card-body p-3 text-center">
                    <div class="text-muted mb-1">Mata Pelajaran</div>
                    <div class="h2 mb-0 text-success">{{ collect($preview['subjects'])->where('status', 'new')->count() }}</div>
                    <small class="text-muted">{{ collect($preview['subjects'])->where('status', 'exists')->count() }} sudah ada</small>
                </div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card">
                <div class="card-body p-3 text-center">
                    <div class="text-muted mb-1">Guru</div>
                    <div class="h2 mb-0 text-success">{{ collect($preview['teachers'])->where('status', 'new')->count() }}</div>
                    <small class="text-muted">
                        {{ collect($preview['teachers'])->whereIn('status', ['exists_kode', 'exists_nama'])->count() }} duplikat
                    </small>
                </div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card">
                <div class="card-body p-3 text-center">
                    <div class="text-muted mb-1">Kelas</div>
                    <div class="h2 mb-0 text-success">{{ collect($preview['classes'])->where('status', 'new')->count() }}</div>
                    <small class="text-muted">{{ collect($preview['classes'])->where('status', 'exists')->count() }} sudah ada</small>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card">
                <div class="card-body p-3 text-center">
                    <div class="text-muted mb-1">Jadwal KBM</div>
                    <div class="h2 mb-0 text-info">{{ count($preview['lessons']) }}</div>
                    <small class="text-muted">Total jadwal dalam file</small>
                </div>
            </div>
        </div>
    </div>

    
    <div class="card">
        <div class="card-header border-0 pt-3 pb-2">
            <ul class="nav nav-tabs card-header-tabs" data-bs-toggle="tabs">
                <li class="nav-item">
                    <a href="#periods" class="nav-link active" data-bs-toggle="tab">
                        <i class="ti ti-clock me-2"></i>Jam Belajar
                    </a>
                </li>
                <li class="nav-item">
                    <a href="#subjects" class="nav-link" data-bs-toggle="tab">
                        <i class="ti ti-book me-2"></i>Mata Pelajaran
                    </a>
                </li>
                <li class="nav-item">
                    <a href="#teachers" class="nav-link" data-bs-toggle="tab">
                        <i class="ti ti-user me-2"></i>Guru
                    </a>
                </li>
                <li class="nav-item">
                    <a href="#classes" class="nav-link" data-bs-toggle="tab">
                        <i class="ti ti-building me-2"></i>Kelas
                    </a>
                </li>
                <li class="nav-item">
                    <a href="#lessons" class="nav-link" data-bs-toggle="tab">
                        <i class="ti ti-calendar me-2"></i>Jadwal KBM (Sample)
                    </a>
                </li>
            </ul>
        </div>
        <div class="card-body">
            <div class="tab-content">
                
                <div class="tab-pane active show" id="periods">
                    <div class="table-responsive">
                        <table class="table table-vcenter table-hover">
                            <thead>
                                <tr>
                                    <th>Urutan</th>
                                    <th>Nama</th>
                                    <th>Jam Mulai</th>
                                    <th>Jam Selesai</th>
                                    <th>Hari</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($preview['periods'] as $period)
                                <tr>
                                    <td>{{ $period['urutan'] }}</td>
                                    <td>{{ $period['nama'] }}</td>
                                    <td>{{ $period['jam_mulai'] }}</td>
                                    <td>{{ $period['jam_selesai'] }}</td>
                                    <td><span class="badge bg-info">Semua Hari</span></td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="5" class="text-center text-muted">Tidak ada data</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                
                <div class="tab-pane" id="subjects">
                    <div class="table-responsive">
                        <table class="table table-vcenter table-hover">
                            <thead>
                                <tr>
                                    <th>Kode</th>
                                    <th>Nama Mata Pelajaran</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($preview['subjects'] as $subject)
                                <tr>
                                    <td><strong>{{ $subject['kode_mapel'] }}</strong></td>
                                    <td>{{ $subject['nama_mapel'] }}</td>
                                    <td>
                                        @if($subject['status'] === 'new')
                                        <span class="badge bg-success">Baru</span>
                                        @else
                                        <span class="badge bg-secondary">Sudah Ada</span>
                                        @endif
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="3" class="text-center text-muted">Tidak ada data</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                
                <div class="tab-pane" id="teachers">
                    <div class="table-responsive">
                        <table class="table table-vcenter table-hover">
                            <thead>
                                <tr>
                                    <th>Kode Guru</th>
                                    <th>Nama Guru</th>
                                    <th>Jenis Kelamin</th>
                                    <th>Status</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($preview['teachers'] as $index => $teacher)
                                <tr>
                                    <td><strong>{{ $teacher['kode_guru'] }}</strong></td>
                                    <td>{{ $teacher['nama'] }}</td>
                                    <td>{{ $teacher['jenis_kelamin'] }}</td>
                                    <td>
                                        @if($teacher['status'] === 'new')
                                            <span class="badge bg-success">Baru</span>
                                        @elseif($teacher['status'] === 'exists_kode')
                                            <span class="badge bg-warning">Kode Guru Sama</span>
                                            @if($teacher['existing_data'])
                                                <br><small class="text-muted">Data lama: {{ $teacher['existing_data']->nama }}</small>
                                            @endif
                                        @elseif($teacher['status'] === 'exists_nama')
                                            <span class="badge bg-danger">Nama Guru Sama</span>
                                            @if($teacher['existing_data'])
                                                <br><small class="text-muted">Kode lama: {{ $teacher['existing_data']->kode_guru ?? '-' }}</small>
                                            @endif
                                        @endif
                                    </td>
                                    <td>
                                        @if($teacher['status'] !== 'new')
                                            <select name="teacher_action[{{ $index }}]" class="form-select form-select-sm">
                                                <option value="skip">Lewati</option>
                                                <option value="replace">Replace Data Lama</option>
                                                @if($teacher['status'] === 'exists_kode')
                                                <option value="add_new">Tambah Sebagai Baru</option>
                                                @endif
                                            </select>
                                            <input type="hidden" name="teacher_kode[{{ $index }}]" value="{{ $teacher['kode_guru'] }}">
                                            <input type="hidden" name="teacher_nama[{{ $index }}]" value="{{ $teacher['nama'] }}">
                                            <input type="hidden" name="teacher_gender[{{ $index }}]" value="{{ $teacher['jenis_kelamin'] }}">
                                            <input type="hidden" name="teacher_existing_id[{{ $index }}]" value="{{ $teacher['existing_id'] }}">
                                        @else
                                            <span class="text-success">Akan ditambahkan</span>
                                        @endif
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="5" class="text-center text-muted">Tidak ada data</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                
                <div class="tab-pane" id="classes">
                    <div class="table-responsive">
                        <table class="table table-vcenter table-hover">
                            <thead>
                                <tr>
                                        <th>Nama Kelas</th>
                                        <th>Tingkat</th>
                                        <th>Jurusan</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($preview['classes'] as $class)
                                    <tr>
                                        <td><strong>{{ $class['nama_kelas'] }}</strong></td>
                                        <td><span class="badge bg-info">{{ $class['tingkat_kelas'] ?? '-' }}</span></td>
                                        <td>{{ $class['jurusan'] ?? '-' }}</td>
                                        <td>
                                            @if($class['status'] === 'new')
                                            <span class="badge bg-success">Baru</span>
                                            @else
                                            <span class="badge bg-secondary">Sudah Ada</span>
                                            @endif
                                        </td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="4" class="text-center text-muted">Tidak ada data</td>
                                    </tr>
                                    @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                
                <div class="tab-pane" id="lessons">
                    @php
                        $dayNames = ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat'];
                        $allClasses = collect($preview['lessons'])->pluck('kelas_kode')->unique()->sort()->values();
                        $maxJam = collect($preview['lessons'])->max('jam_ke') ?? 10;
                        // Build lessonsByDayClassJam[hari][kelas][jam_ke]
                        $lessonsByDayClassJam = [];
                        foreach($preview['lessons'] as $lesson) {
                            $hari = $lesson['hari'] ?? 'Unknown';
                            $kelas = $lesson['kelas_kode'];
                            $jamKe = $lesson['jam_ke'];
                            $lessonsByDayClassJam[$hari][$kelas][$jamKe] = $lesson;
                        }
                    @endphp
                    @foreach($dayNames as $dayName)
                        <h4 class="mt-4 mb-2">{{ $dayName }}</h4>
                        <div class="table-responsive">
                            <table class="table table-bordered table-sm" style="font-size: 11px;">
                                <thead>
                                    <tr class="bg-light">
                                        <th width="80" class="text-center">Jam Ke</th>
                                        @foreach($allClasses as $kelas)
                                            <th class="text-center" style="min-width: 140px; word-wrap: break-word;">{{ $kelas }}</th>
                                        @endforeach
                                    </tr>
                                </thead>
                                <tbody>
                                    @for($jam = 1; $jam <= $maxJam; $jam++)
                                        <tr>
                                            <td class="text-center bg-light"><strong>{{ $jam }}</strong></td>
                                            @foreach($allClasses as $kelas)
                                                @php
                                                    $lesson = $lessonsByDayClassJam[$dayName][$kelas][$jam] ?? null;
                                                @endphp
                                                <td class="text-center" style="padding: 6px; vertical-align: middle;">
                                                    @if($lesson)
                                                        <div style="font-size: 10px; line-height: 1.4;">
                                                            <strong class="text-primary" style="display: block;">{{ $lesson['mapel_kode'] }}</strong>
                                                            <span class="text-muted small" style="display: block;">{{ $lesson['guru_nip'] }}</span>
                                                        </div>
                                                    @else
                                                        <span class="text-muted">-</span>
                                                    @endif
                                                </td>
                                            @endforeach
                                        </tr>
                                    @endfor
                                </tbody>
                            </table>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        
        <div class="row mt-4 mb-4">
            <div class="col text-center">
                <a href="{{ route('asc_timetable.index') }}" class="btn btn-outline-secondary btn-lg me-2">
                    <i class="ti ti-x me-2"></i>Batal
                </a>
                <button type="submit" class="btn btn-success btn-lg" onclick="return confirm('Apakah Anda yakin ingin melanjutkan import data yang dipilih ke database?')">
                    <i class="ti ti-check me-2"></i>Konfirmasi & Import ke Database
                </button>
            </div>
        </div>
    </form>
</div>

@endsection

@push('scripts')
<script>
    // Handle check all functionality
    document.getElementById('check_all').addEventListener('change', function() {
        const checkboxes = document.querySelectorAll('.import-check');
        checkboxes.forEach(cb => {
            cb.checked = this.checked;
        });
    });
    
    // Update check all when individual checkboxes change
    document.querySelectorAll('.import-check').forEach(cb => {
        cb.addEventListener('change', function() {
            const allChecked = Array.from(document.querySelectorAll('.import-check'))
                .every(checkbox => checkbox.checked);
            document.getElementById('check_all').checked = allChecked;
        });
    });
</script>
@endpush
