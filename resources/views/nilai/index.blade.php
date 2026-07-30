@extends('layouts.app')

@section('title', 'Daftar Nilai')

@section('content')
<div class="page-header d-print-none">
    <div class="container-xl">
        <div class="row g-2 align-items-center">
            <div class="col">
                <h2 class="page-title">
                    <i class="ti ti-report-analytics me-2"></i>Daftar Nilai Harian
                </h2>
                <div class="text-muted mt-1">Kelola data nilai harian siswa per mata pelajaran</div>
                @if($tahunAjaranAktif && $semesterAktif)
                <div class="text-muted small mt-2">
                    <i class="ti ti-calendar me-1"></i>{{ $tahunAjaranAktif->tahun_ajaran }} - <strong>{{ $semesterAktif->nama_semester }}</strong>
                </div>
                @endif
            </div>
            <div class="col-auto ms-auto d-print-none">
                @php
                    $isSiswaWithoutClassPosition = auth()->user()->hasRole('Siswa') && ! auth()->user()->hasClassPosition();
                @endphp
                @unless($isSiswaWithoutClassPosition)
                <div >
                    <a href="#" class="btn btn-outline-primary d-none d-sm-inline-block" data-bs-toggle="modal" data-bs-target="#modalImportNilai">
                        <i class="ti ti-file-import"></i>
                        Import Excel
                    </a>
                    <a href="#" class="btn btn-primary d-none d-sm-inline-block" data-bs-toggle="modal" data-bs-target="#modalTambahNilai">
                        <i class="ti ti-plus"></i>
                        Tambah Nilai
                    </a>
                </div>
                @endunless
            </div>
        </div>
    </div>
</div>

<div class="page-body">
    <div class="container-xl">
        @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="ti ti-check me-2"></i>{{ session('success') }}
            <button type="button" class="close" data-bs-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        </div>
        @endif
        @if(session('warning') && session('import_errors'))
        <div class="alert alert-warning alert-dismissible fade show" role="alert">
            <div class="fw-bold mb-2">{{ session('warning') }}</div>
            <ul class="mb-0">
                @foreach(session('import_errors') as $err)
                    <li>{{ $err }}</li>
                @endforeach
            </ul>
            <button type="button" class="close" data-bs-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        </div>
        @endif

        @if(request()->get('debug') === '1')
        <div class="card mb-2">
            <div class="card-header border-0 pt-3 pb-2">
                <h3 class="card-title">Debug Rencana Pembelajaran (Guru Login)</h3>
            </div>
            <div class="card-body">
                @if($debugRencana)
                    <div class="mb-2">Total rencana ditemukan: <strong>{{ $debugRencana['total'] }}</strong></div>
                    @if($debugRencana['total'] > 0)
                        <div class="table-responsive">
                            <table class="table table-sm table-vcenter">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Judul</th>
                                        <th>Kelas</th>
                                        <th>Mapel</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($debugRencana['sample'] as $rp)
                                        <tr>
                                            <td>{{ $rp->id }}</td>
                                            <td>{{ $rp->judul }}</td>
                                            <td>{{ $rp->kelas->nama_kelas ?? '-' }}</td>
                                            <td>{{ $rp->mataPelajaran->nama_mapel ?? '-' }}</td>
                                            <td>{{ $rp->status ?? '-' }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                @else
                    <div class="text-muted">Tidak ada data debug untuk guru login.</div>
                @endif
            </div>
        </div>
        @endif

        @if(!($isAdminOrKepala ?? false) && isset($quickMenus) && $quickMenus->count())
        <div class="card mb-2">
            <div class="card-header border-0 pt-3 pb-2">
                <h3 class="card-title">Menu Cepat Penilaian</h3>
                <div class="card-actions">
                    @if($kelasId || $mapelId)
                        <a href="{{ route('nilai.index') }}" class="btn btn-sm btn-outline-secondary">
                            Reset Filter
                        </a>
                    @endif
                </div>
            </div>
            <div class="card-body">
                <div class="d-flex flex-wrap gap-2">
                    @foreach($quickMenus as $menu)
                        <a href="{{ route('nilai.index', ['kelas_id' => $menu->kelas_id, 'mapel_id' => $menu->mata_pelajaran_id]) }}" class="btn btn-outline-primary btn-modern">
                            {{ $menu->kelas->nama_kelas ?? 'Kelas' }} - {{ $menu->mataPelajaran->nama_mapel ?? 'Mapel' }}
                        </a>
                    @endforeach
                </div>
                @if($kelasId || $mapelId)
                    <div class="mt-2 text-muted">
                        Filter aktif:
                        {{ $filterKelasName ? 'Kelas ' . $filterKelasName : '-' }}
                        {{ $filterMapelName ? ' | Mapel ' . $filterMapelName : '' }}
                    </div>
                @endif
            </div>
        </div>
        @endif

        @if($isAdminOrKepala ?? false)
        <div class="card mb-2">
            <div class="card-header border-0 pt-3 pb-2">
                <h3 class="card-title">Lihat Nilai (Sederhana)</h3>
            </div>
            <div class="card-body">
                <form method="GET" action="{{ route('nilai.index') }}" class="row g-2 align-items-end">
                    <div class="col-12 col-md-4 col-lg-3">
                        <label class="form-label">Kelas</label>
                        <select class="form-select" name="kelas_id">
                            <option value="">Semua Kelas</option>
                            @foreach(($kelasOptions ?? collect()) as $kelas)
                                <option value="{{ $kelas->id }}" {{ (string) $kelasId === (string) $kelas->id ? 'selected' : '' }}>
                                    {{ $kelas->nama_kelas }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-12 col-md-4 col-lg-4">
                        <label class="form-label">Mata Pelajaran</label>
                        <select class="form-select" name="mapel_id">
                            <option value="">Semua Mata Pelajaran</option>
                            @foreach(($mapelOptions ?? collect()) as $mapel)
                                <option value="{{ $mapel->id }}" {{ (string) $mapelId === (string) $mapel->id ? 'selected' : '' }}>
                                    {{ $mapel->nama_mapel }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-auto">
                        <button type="submit" class="btn btn-primary">
                            <i class="ti ti-search me-1"></i>Tampilkan
                        </button>
                    </div>
                    <div class="col-auto">
                        <a href="{{ route('nilai.index') }}" class="btn btn-outline-secondary btn-modern">Reset</a>
                    </div>
                </form>
            </div>
        </div>
        @endif

        @if($isAdminOrKepala ?? false)
        <div class="card mb-2">
            <div class="card-header border-0 pt-3 pb-2">
                <h3 class="card-title">Rekap Nilai Diinput Guru</h3>
            </div>
            <div class="card-body">
                <form method="GET" action="{{ route('nilai.index') }}" class="row g-2 align-items-end mb-2">
                    @if($kelasId)
                        <input type="hidden" name="kelas_id" value="{{ $kelasId }}">
                    @endif
                    @if($mapelId)
                        <input type="hidden" name="mapel_id" value="{{ $mapelId }}">
                    @endif
                    <div class="col-12 col-md-4 col-lg-3">
                        <label for="tanggal_nilai" class="form-label">Tanggal Input Nilai</label>
                        <input
                            type="date"
                            class="form-control"
                            id="tanggal_nilai"
                            name="tanggal_nilai"
                            value="{{ $selectedTanggalNilai ?? now()->format('Y-m-d') }}"
                        >
                    </div>
                    <div class="col-auto">
                        <button type="submit" class="btn btn-primary">
                            <i class="ti ti-search me-1"></i>Tampilkan
                        </button>
                    </div>
                </form>

                @if(($rekapInputGuru ?? collect())->isEmpty())
                    <div class="alert alert-info mb-0">
                        <i class="ti ti-info-circle me-1"></i>Belum ada input nilai guru pada tanggal dipilih.
                    </div>
                @else
                    <div class="table-responsive">
                        <table class="table table-vcenter table-striped table-hover">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>Guru</th>
                                    <th>Total Record</th>
                                    <th>Nilai Terisi</th>
                                    <th>Rata-Rata Nilai</th>
                                    <th>Kelas</th>
                                    <th>Mapel</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach(($rekapInputGuru ?? collect()) as $index => $rekap)
                                    <tr>
                                        <td>{{ $index + 1 }}</td>
                                        <td>{{ $rekap->guru_nama }}</td>
                                        <td><span class="badge bg-primary">{{ $rekap->total_record }}</span></td>
                                        <td><span class="badge bg-success">{{ $rekap->total_terisi }}</span></td>
                                        <td>{{ $rekap->rata_nilai !== null ? number_format((float) $rekap->rata_nilai, 2) : '-' }}</td>
                                        <td>{{ $rekap->total_kelas }}</td>
                                        <td>{{ $rekap->total_mapel }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        </div>

        <div class="card mb-2">
            <div class="card-header border-0 pt-3 pb-2">
                <h3 class="card-title">Daftar Nilai Sudah Diinput Guru</h3>
                <div class="card-actions text-muted small">
                    Maksimal 300 data terbaru
                </div>
            </div>
            <div class="card-body">
                @if(($daftarInputNilaiGuru ?? collect())->isEmpty())
                    <div class="alert alert-info mb-0">
                        <i class="ti ti-info-circle me-1"></i>Belum ada nilai terisi oleh guru pada filter yang dipilih.
                    </div>
                @else
                    <div class="table-responsive">
                        <table class="table table-vcenter table-striped table-hover">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>Tanggal</th>
                                    <th>Guru</th>
                                    <th>Kelas</th>
                                    <th>Mata Pelajaran</th>
                                    <th>Siswa</th>
                                    <th>Komponen</th>
                                    <th>Nilai</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach(($daftarInputNilaiGuru ?? collect()) as $index => $row)
                                    <tr>
                                        <td>{{ $index + 1 }}</td>
                                        <td>{{ \Carbon\Carbon::parse($row->tanggal)->format('d/m/Y') }}</td>
                                        <td>{{ $row->guru_nama }}</td>
                                        <td>{{ $row->nama_kelas }}</td>
                                        <td>{{ $row->nama_mapel }}</td>
                                        <td>{{ $row->nama_siswa }}</td>
                                        <td>{{ $row->nama_komponen ?: 'Harian' }}</td>
                                        <td><span class="badge bg-success">{{ rtrim(rtrim(number_format((float) $row->nilai, 2, '.', ''), '0'), '.') }}</span></td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        </div>
        @endif

        <div class="card">
            <div class="card-header border-0 pt-3 pb-2">
                <h3 class="card-title">Data Nilai Harian</h3>
                @if($kelasId || $mapelId)
                    <div class="text-muted ms-3">
                        Tanggal: {{ optional($items->first())->tanggal ? \Carbon\Carbon::parse($items->first()->tanggal)->format('d/m/Y') : '-' }}
                        | Kelas: {{ $filterKelasName ?? '-' }}
                        | Mapel: {{ $filterMapelName ?? '-' }}
                    </div>
                @endif
                <div class="card-actions">
                    <input type="text" class="form-control form-control-sm" placeholder="Cari..." id="searchInput">
                </div>
            </div>
            <div class="card-body">
                @if($kelasId || $mapelId)
                    @if(($nilaiTableRows ?? collect())->count() > 0)
                    <form method="POST" action="{{ route('nilai.update-batch') }}">
                        @csrf
                        <div class="d-flex justify-content-end mb-2">
                            <button type="submit" class="btn btn-sm btn-primary">
                                <i class="ti ti-device-floppy me-1"></i>Simpan Nilai
                            </button>
                        </div>
                        <div class="table-responsive">
                        <table class="table table-vcenter table-hover" id="tableNilai">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>Nama Siswa</th>
                                    @forelse(($nilaiKomponenColumns ?? collect()) as $komponen)
                                        <th class="text-center">{{ strtoupper($komponen->nama) }}</th>
                                    @empty
                                        <th class="text-center">KOMPONEN</th>
                                    @endforelse
                                    <th class="text-center">JUMLAH</th>
                                    <th class="text-center">RATA-RATA</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach(($nilaiTableRows ?? collect()) as $index => $row)
                                <tr>
                                    <td>{{ $index + 1 }}</td>
                                    <td>{{ $row->nama_siswa }}</td>
                                    @forelse(($nilaiKomponenColumns ?? collect()) as $komponen)
                                        @php
                                            $komponenId = (int) $komponen->id;
                                            $nilaiId = $row->nilai_id_by_komponen[$komponenId] ?? null;
                                            $nilai = $row->nilai_by_komponen[$komponenId] ?? null;
                                        @endphp
                                        <td class="text-center">
                                            @if($nilaiId)
                                                <input type="number" name="nilai[{{ $nilaiId }}]" class="form-control form-control-sm text-center" min="0" max="100" step="0.01" value="{{ $nilai }}" style="width: 100px; margin: 0 auto;">
                                            @else
                                                <span class="text-muted">-</span>
                                            @endif
                                        </td>
                                    @empty
                                        <td class="text-center">-</td>
                                    @endforelse
                                    <td class="text-center fw-bold">{{ $row->jumlah !== null ? number_format($row->jumlah, 2) : '-' }}</td>
                                    <td class="text-center fw-bold">{{ $row->rata_rata !== null ? number_format($row->rata_rata, 2) : '-' }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                        </div>
                    </form>
                    @else
                    <div class="empty">
                        <div class="empty-img"><img src="{{ asset('tabler/static/illustrations/undraw_printing_invoices_5r4r.svg') }}" height="128" alt="">
                        </div>
                        <p class="empty-title">Belum ada data nilai</p>
                        <p class="empty-subtitle text-muted">
                            Tambahkan nilai siswa dengan mengklik tombol "Tambah Nilai" di atas.
                        </p>
                        @unless($isSiswaWithoutClassPosition)
                        <div class="empty-action">
                            <a href="#" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalTambahNilai">
                                <i class="ti ti-plus"></i>
                                Tambah Nilai Pertama
                            </a>
                        </div>
                        @endunless
                    </div>
                    @endif
                @else
                    @if($isAdminOrKepala ?? false)
                        <div class="text-muted">Pilih filter Kelas dan/atau Mata Pelajaran pada panel "Lihat Nilai (Sederhana)", lalu klik Tampilkan.</div>
                    @else
                        <div class="text-muted">Silakan klik Menu Cepat Penilaian untuk menampilkan daftar nilai harian.</div>
                    @endif
                @endif
            </div>
        </div>

        
        <div class="row mt-2">
            <div class="col-md-12">
                <div class="card card-sm">
                    <div class="card-body">
                        <div class="row align-items-center">
                            <div class="col-auto">
                                <i class="ti ti-info-circle text-blue" style="font-size: 32px;"></i>
                            </div>
                            <div class="col">
                                <div class="font-weight-medium">Informasi</div>
                                <div class="text-muted">
                                    Sistem nilai menggunakan tabel <strong>nilai_harian</strong> dan terhubung ke <strong>rencana pembelajaran</strong>.
                                    Pastikan data guru, kelas, mata pelajaran, dan rencana pembelajaran sudah tersedia sebelum membuat nilai harian.
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>


<div class="modal fade" id="modalTambahNilai" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Tambah Nilai Harian</h5>
                <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
            </div>
            <div class="modal-body">
                <form method="POST" action="{{ route('nilai.store') }}" id="nilaiForm">
                    @csrf
                    <div class="mb-2">
                        <label class="form-label">Tanggal</label>
                        <input type="date" class="form-control" name="tanggal" required>
                    </div>
                    <div class="mb-2">
                        <label class="form-label">Kelas</label>
                        <select class="form-select" name="kelas_id" id="nilaiKelasSelect" required @if(empty($kelasOptions) || $kelasOptions->isEmpty()) disabled @endif>
                            <option value="">Pilih Kelas...</option>
                            @foreach($kelasOptions as $kelas)
                                <option value="{{ $kelas->id }}">{{ $kelas->nama_kelas }}</option>
                            @endforeach
                        </select>
                        @if(empty($kelasOptions) || $kelasOptions->isEmpty())
                            <small class="text-muted d-block mt-1">Tidak ada kelas sesuai jadwal Anda.</small>
                        @endif
                    </div>
                    <div class="mb-2">
                        <label class="form-label">Mata Pelajaran</label>
                        <select class="form-select" name="mapel_id" id="nilaiMapelSelect" required @if(empty($mapelOptions) || $mapelOptions->isEmpty()) disabled @endif>
                            <option value="">Pilih Mata Pelajaran...</option>
                            @foreach($mapelOptions as $mapel)
                                <option value="{{ $mapel->id }}">{{ $mapel->nama_mapel }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-2">
                        <label class="form-label">Rencana Pembelajaran</label>
                        <select class="form-select" name="rencana_pembelajaran_id" id="nilaiRencanaSelect" required @if(empty($kelasOptions) || $kelasOptions->isEmpty()) disabled @endif>
                            <option value="">Pilih Rencana...</option>
                        </select>
                        <small class="text-muted d-block mt-1">Rencana diambil dari data guru sesuai mata pelajaran.</small>
                    </div>
                    <div class="mb-2">
                        <label class="form-label">Komponen Penilaian</label>
                        <select class="form-select" name="komponen_id">
                            <option value="">Pilih Komponen...</option>
                            @foreach($komponenList as $komponen)
                                <option value="{{ $komponen->id }}">{{ $komponen->nama_komponen }}</option>
                            @endforeach
                        </select>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                <button type="submit" class="btn btn-primary" form="nilaiForm">Simpan</button>
            </div>
        </div>
    </div>
</div>


<div class="modal fade" id="modalImportNilai" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Import Nilai Harian (Excel)</h5>
                <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
            </div>
            <div class="modal-body">
                <form method="POST" action="{{ route('nilai.import') }}" enctype="multipart/form-data" id="nilaiImportForm">
                    @csrf
                    <div class="mb-2">
                        <a
                            href="{{ $kelasId ? route('nilai.template', ['kelas_id' => $kelasId]) : '#' }}"
                            data-template-base="{{ route('nilai.template') }}"
                            id="nilaiTemplateDownloadBtn"
                            class="btn btn-outline-secondary btn-sm {{ $kelasId ? '' : 'disabled' }}"
                            @if(!$kelasId) aria-disabled="true" @endif
                        >
                            <i class="ti ti-download me-1"></i>Download Template
                        </a>
                        @if(!$kelasId)
                            <small class="text-muted d-block mt-1">Pilih Menu Cepat Penilaian dulu untuk menentukan kelas.</small>
                        @endif
                    </div>
                    <div class="mb-2">
                        <label class="form-label">Tanggal</label>
                        <input type="date" class="form-control" name="tanggal" required>
                    </div>
                    <div class="mb-2">
                        <label class="form-label">Kelas</label>
                        <select class="form-select" name="kelas_id" id="nilaiImportKelasSelect" required>
                            <option value="">Pilih Kelas...</option>
                            @foreach($kelasOptions as $kelas)
                                <option value="{{ $kelas->id }}" {{ (string) $kelasId === (string) $kelas->id ? 'selected' : '' }}>{{ $kelas->nama_kelas }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-2">
                        <label class="form-label">Mata Pelajaran</label>
                        <select class="form-select" name="mapel_id" id="nilaiImportMapelSelect" required>
                            <option value="">Pilih Mata Pelajaran...</option>
                            @foreach($mapelOptions as $mapel)
                                <option value="{{ $mapel->id }}">{{ $mapel->nama_mapel }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-2">
                        <label class="form-label">Rencana Pembelajaran</label>
                        <select class="form-select" name="rencana_pembelajaran_id" id="nilaiImportRencanaSelect" required>
                            <option value="">Pilih Rencana...</option>
                        </select>
                        <small class="text-muted d-block mt-1">Kolom wajib: nis/nisn dan nilai.</small>
                    </div>
                    <div class="mb-2">
                        <label class="form-label">File Excel</label>
                        <input type="file" class="form-control" name="file" accept=".xlsx,.xls" required>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                <button type="submit" class="btn btn-primary" form="nilaiImportForm">Import</button>
            </div>
        </div>
    </div>
</div>

@endsection

@push('js')
<script>
    // Search functionality
    document.getElementById('searchInput')?.addEventListener('keyup', function() {
        let filter = this.value.toLowerCase();
        let rows = document.querySelectorAll('#tableNilai tbody tr');
        
        rows.forEach(row => {
            let text = row.textContent.toLowerCase();
            row.style.display = text.includes(filter) ? '' : 'none';
        });
    });

    var nilaiKelasSelect = document.getElementById('nilaiKelasSelect');
    var nilaiMapelSelect = document.getElementById('nilaiMapelSelect');
    var nilaiRencanaSelect = document.getElementById('nilaiRencanaSelect');
    var nilaiImportKelasSelect = document.getElementById('nilaiImportKelasSelect');
    var nilaiImportMapelSelect = document.getElementById('nilaiImportMapelSelect');
    var nilaiImportRencanaSelect = document.getElementById('nilaiImportRencanaSelect');
    var nilaiTemplateDownloadBtn = document.getElementById('nilaiTemplateDownloadBtn');
    var mapelByKelas = @json(($mapelByKelas ?? collect())->toArray());
    var rencanaByMapel = @json($rencanaByMapel ?? []);
    var allMapelOptions = @json(($mapelOptions ?? collect())->map(function($mapel) {
        return ['id' => $mapel->id, 'nama' => $mapel->nama_mapel];
    })->values()->toArray());

    // Initialize tooltips (letakkan setelah semua var declaration untuk menghindari ReferenceError)
    if (typeof $ !== 'undefined') {
        $('[data-bs-toggle="tooltip"]').tooltip();
    }

    function resetSelect(selectEl, placeholder) {
        if (!selectEl) return;
        selectEl.innerHTML = '';
        const option = document.createElement('option');
        option.value = '';
        option.textContent = placeholder;
        selectEl.appendChild(option);
    }

    function fillOptions(selectEl, items, placeholder) {
        resetSelect(selectEl, placeholder);
        if (!items || !items.length) return;
        items.forEach(function(item) {
            const option = document.createElement('option');
            option.value = item.id;
            option.textContent = item.nama;
            selectEl.appendChild(option);
        });
    }

    function getRencanaItems(kelasId, mapelId) {
        // Coba cari berdasarkan kelas + mapel dulu
        if (kelasId && mapelId && rencanaByMapel[kelasId] && rencanaByMapel[kelasId][mapelId]) {
            return rencanaByMapel[kelasId][mapelId].map(function(item) {
                return { id: item.id, nama: item.judul };
            });
        }

        // Fallback: jika tidak ada untuk kelas tertentu, tampilkan semua rencana untuk mapel yang dipilih
        if (mapelId && rencanaByMapel['*'] && rencanaByMapel['*'][mapelId]) {
            return rencanaByMapel['*'][mapelId].map(function(item) {
                return { id: item.id, nama: item.judul };
            });
        }

        return [];
    }

    function updateRencanaOptions() {
        const kelasId = nilaiKelasSelect ? nilaiKelasSelect.value : '';
        const mapelId = nilaiMapelSelect ? nilaiMapelSelect.value : '';
        const rencanaItems = getRencanaItems(kelasId, mapelId);
        fillOptions(nilaiRencanaSelect, rencanaItems, 'Pilih Rencana...');
    }

    function updateImportRencanaOptions() {
        const kelasId = nilaiImportKelasSelect ? nilaiImportKelasSelect.value : '';
        const mapelId = nilaiImportMapelSelect ? nilaiImportMapelSelect.value : '';
        const rencanaItems = getRencanaItems(kelasId, mapelId);
        fillOptions(nilaiImportRencanaSelect, rencanaItems, 'Pilih Rencana...');
    }

    function onKelasChange() {
        const kelasId = nilaiKelasSelect ? nilaiKelasSelect.value : '';
        const mapelItems = kelasId && mapelByKelas[kelasId] ? mapelByKelas[kelasId] : allMapelOptions;
        fillOptions(nilaiMapelSelect, mapelItems, 'Pilih Mata Pelajaran...');
        updateRencanaOptions();
    }

    function updateTemplateDownloadLink() {
        if (!nilaiTemplateDownloadBtn) return;
        const kelasId = nilaiImportKelasSelect ? nilaiImportKelasSelect.value : '';
        const baseUrl = nilaiTemplateDownloadBtn.dataset.templateBase || '#';

        if (!kelasId) {
            nilaiTemplateDownloadBtn.setAttribute('href', '#');
            nilaiTemplateDownloadBtn.classList.add('disabled');
            nilaiTemplateDownloadBtn.setAttribute('aria-disabled', 'true');
            return;
        }

        nilaiTemplateDownloadBtn.setAttribute('href', baseUrl + '?kelas_id=' + encodeURIComponent(kelasId));
        nilaiTemplateDownloadBtn.classList.remove('disabled');
        nilaiTemplateDownloadBtn.removeAttribute('aria-disabled');
    }

    function onImportKelasChange() {
        const kelasId = nilaiImportKelasSelect ? nilaiImportKelasSelect.value : '';
        const mapelItems = kelasId && mapelByKelas[kelasId] ? mapelByKelas[kelasId] : allMapelOptions;

        fillOptions(nilaiImportMapelSelect, mapelItems, 'Pilih Mata Pelajaran...');
        updateImportRencanaOptions();
        updateTemplateDownloadLink();
    }

    if (nilaiKelasSelect) {
        nilaiKelasSelect.addEventListener('change', onKelasChange);
    }
    if (nilaiMapelSelect) {
        nilaiMapelSelect.addEventListener('change', updateRencanaOptions);
    }
    if (nilaiImportKelasSelect) {
        nilaiImportKelasSelect.addEventListener('change', onImportKelasChange);
    }
    if (nilaiImportMapelSelect) {
        nilaiImportMapelSelect.addEventListener('change', updateImportRencanaOptions);
    }

    const nilaiModal = document.getElementById('modalTambahNilai');
    const nilaiImportModal = document.getElementById('modalImportNilai');
    if (nilaiModal) {
        nilaiModal.addEventListener('shown.bs.modal', function() {
            onKelasChange();
            updateRencanaOptions();
            if (window && window.console) {
                console.log('rencanaByMapel keys', Object.keys(rencanaByMapel || {}));
            }
        });
    }

    if (nilaiMapelSelect && nilaiMapelSelect.value) {
        updateRencanaOptions();
    }

    if (nilaiImportModal) {
        nilaiImportModal.addEventListener('shown.bs.modal', function() {
            onImportKelasChange();
        });
    }

    onImportKelasChange();
</script>
@endpush
