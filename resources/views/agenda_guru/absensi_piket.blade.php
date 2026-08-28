@extends('layouts.app', ['pageSlug' => 'agenda_guru'])

@section('title','Absensi Guru')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <div>
                        <h4 class="card-title fw-semibold m-0">
                            <i class="ti ti-user-check me-2"></i>Absensi Guru
                        </h4>
                    </div>
                </div>
                <div class="card-body">
                    @if(session('success'))
                        <div class="alert alert-success alert-dismissible fade show">
                            <i class="ti ti-check me-2"></i>{{ session('success') }}
                            <button type="button" class="close" data-bs-dismiss="alert"><span aria-hidden="true">&times;</span></button>
                        </div>
                    @endif

                    @if(session('error'))
                        <div class="alert alert-danger alert-dismissible fade show">
                            <i class="ti ti-alert-circle me-2"></i>{{ session('error') }}
                            <button type="button" class="close" data-bs-dismiss="alert"><span aria-hidden="true">&times;</span></button>
                        </div>
                    @endif

                    @if($errors->any())
                        <div class="alert alert-danger">
                            <ul class="mb-0">
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form method="GET" action="{{ route('agenda_guru.index') }}" class="row g-2 align-items-end mb-2">
                        <div class="col-12 col-md-4 col-lg-3">
                            <label class="form-label">Tanggal</label>
                            <input type="date" class="form-control" name="tanggal" value="{{ $selectedTanggal }}">
                        </div>
                            <div class="col-auto">
                                <button type="submit" class="btn btn-primary">
                                    <i class="ti ti-search me-1"></i>Tampilkan
                                </button>
                            </div>
                            <div class="col-auto ms-auto d-flex gap-2">
                                <button type="button" class="btn btn-outline-secondary btn-sm" id="btnListView" onclick="setView('list')">
                                    <i class="ti ti-list me-1"></i>List
                                </button>
                                <button type="button" class="btn btn-outline-secondary btn-sm" id="btnGridView" onclick="setView('grid')">
                                    <i class="ti ti-layout-grid me-1"></i>Grid
                                </button>
                            </div>
                        </form>

                        <div class="row mb-2 g-2">
                        <div class="col-12 col-md-6 col-lg">
                            <div class="card card-sm border-primary">
                                <div class="card-body">
                                    <div class="text-muted">Total Guru</div>
                                    <div class="h3 mb-0 text-primary">{{ $totalGuru }}</div>
                                </div>
                            </div>
                        </div>
                        <div class="col-12 col-md-6 col-lg">
                            <div class="card card-sm border-success">
                                <div class="card-body">
                                    <div class="text-muted">Hadir</div>
                                    <div class="h3 mb-0 text-success">{{ $hadirCount }}</div>
                                </div>
                            </div>
                        </div>
                        <div class="col-12 col-md-6 col-lg">
                            <div class="card card-sm border-warning">
                                <div class="card-body">
                                    <div class="text-muted">Izin</div>
                                    <div class="h3 mb-0 text-warning">{{ $izinCount }}</div>
                                </div>
                            </div>
                        </div>
                        <div class="col-12 col-md-6 col-lg">
                            <div class="card card-sm border-info">
                                <div class="card-body">
                                    <div class="text-muted">Sakit</div>
                                    <div class="h3 mb-0 text-info">{{ $sakitCount }}</div>
                                </div>
                            </div>
                        </div>
                        <div class="col-12 col-md-6 col-lg">
                            <div class="card card-sm border-danger">
                                <div class="card-body">
                                    <div class="text-muted">Tidak Hadir</div>
                                    <div class="h3 mb-0 text-danger">{{ $tidakHadirCount }}</div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="d-flex justify-content-end mb-2 gap-2">
                        <button type="button" class="btn btn-sm btn-outline-danger" data-bs-toggle="modal" data-bs-target="#exportModal" data-export-type="pdf">
                            <i class="ti ti-file-pdf me-1"></i>Export PDF
                        </button>
                        <button type="button" class="btn btn-sm btn-outline-success" data-bs-toggle="modal" data-bs-target="#exportModal" data-export-type="excel">
                            <i class="ti ti-file-spreadsheet me-1"></i>Export Excel
                        </button>
                    </div>

                    <div class="modal fade" id="exportModal" tabindex="-1" aria-hidden="true">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title">Pilih Jenis Export</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                </div>
                                <div class="modal-body">
                                    <form id="exportForm">
                                        <div class="mb-3">
                                            <label class="form-label">Jenis Export</label>
                                            <select id="exportType" class="form-select">
                                                <option value="single">Tanggal Tertentu</option>
                                                <option value="range">Rentang Waktu</option>
                                                <option value="month">Bulan</option>
                                            </select>
                                        </div>

                                        <div class="mb-3" id="singleDateField">
                                            <label class="form-label">Tanggal</label>
                                            <input type="date" class="form-control" id="exportSingleDate" value="{{ $selectedTanggal }}">
                                        </div>

                                        <div class="mb-3 d-none" id="rangeDateField">
                                            <label class="form-label">Dari Tanggal</label>
                                            <input type="date" class="form-control" id="exportRangeStart" value="{{ $selectedTanggal }}">
                                            <label class="form-label mt-2">Sampai Tanggal</label>
                                            <input type="date" class="form-control" id="exportRangeEnd" value="{{ $selectedTanggal }}">
                                        </div>

                                        <div class="mb-3 d-none" id="monthField">
                                            <label class="form-label">Bulan</label>
                                            <select class="form-select" id="exportMonth">
                                                <option value="1">Januari</option>
                                                <option value="2">Februari</option>
                                                <option value="3">Maret</option>
                                                <option value="4">April</option>
                                                <option value="5">Mei</option>
                                                <option value="6">Juni</option>
                                                <option value="7">Juli</option>
                                                <option value="8">Agustus</option>
                                                <option value="9">September</option>
                                                <option value="10">Oktober</option>
                                                <option value="11">November</option>
                                                <option value="12">Desember</option>
                                            </select>
                                            <label class="form-label mt-2">Tahun</label>
                                            <input type="number" class="form-control" id="exportYear" value="{{ now()->year }}" min="2000" max="2100">
                                        </div>
                                    </form>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                                    <button type="button" class="btn btn-primary" id="confirmExport">Export</button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <form method="POST" action="{{ route('agenda_guru.absensi.store') }}">
                        @csrf
                        <input type="hidden" name="tanggal" value="{{ $selectedTanggal }}">

                        <div class="d-flex justify-content-between align-items-center mb-2 flex-wrap gap-2">
                            <div class="form-check form-switch m-0">
                                <input class="form-check-input" type="checkbox" id="checkAllHadir" onchange="handleCheckAllHadir(this)">
                                <label class="form-check-label" for="checkAllHadir">Ceklis hadir semua</label>
                            </div>
                            <div class="btn-group btn-group-sm" role="group">
                                <button type="button" class="btn btn-outline-danger btn-modern" id="checkAllTidakHadir" onclick="applyBulkStatus('tidak_hadir'); document.getElementById('checkAllHadir').checked = false;">Tidak hadir semua</button>
                                <button type="button" class="btn btn-outline-secondary btn-modern" id="clearAllStatus" onclick="applyBulkStatus(''); document.getElementById('checkAllHadir').checked = false;">Kosongkan semua</button>
                            </div>
                        </div>

                        <div id="listViewContainer" class="table-responsive">
                            <table class="table table-bordered table-striped table-hover">
                                <thead class="table-light">
                                    <tr>
                                        <th style="width: 5%;">No</th>
                                        <th style="width: 10%;">Foto</th>
                                        <th>Nama Guru</th>
                                        <th style="width: 18%;">NIP</th>
                                        <th style="width: 18%;">Status</th>
                                        <th style="width: 24%;">Keterangan</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($daftarGuru as $index => $item)
                                        @php
                                            $existing = $absensiHariIni->get($item->id);
                                        @endphp
                                        <tr>
                                            <td>{{ $index + 1 }}</td>
                                            <td>
                                                <div class="overflow-hidden d-flex align-items-center justify-content-center bg-light" style="width: 45px; height: 60px; aspect-ratio: 3/4; border-radius: 0.35rem;">
                                                    @if($item->user && $item->user->foto)
                                                        <img src="{{ asset('storage/' . $item->user->foto) }}" alt="Foto" class="w-100 h-100" style="object-fit: cover;">
                                                    @else
                                                        <div class="d-flex align-items-center justify-content-center text-white fw-bold" style="width: 100%; height: 100%; background: #dc3545; font-size: 0.9rem;">{{ mb_substr($item->nama, 0, 1) }}</div>
                                                    @endif
                                                </div>
                                            </td>
                                            <td>{{ $item->nama }}</td>
                                            <td>{{ $item->nip ?: '-' }}</td>
                                            <td>
                                                <select class="form-select form-select-sm status-select" name="attendance[{{ $item->id }}][status]">
                                                    <option value="" {{ !optional($existing)->status ? 'selected' : '' }}>Pilih status</option>
                                                    <option value="hadir" {{ optional($existing)->status === 'hadir' ? 'selected' : '' }}>Hadir</option>
                                                    <option value="izin" {{ optional($existing)->status === 'izin' ? 'selected' : '' }}>Izin</option>
                                                    <option value="sakit" {{ optional($existing)->status === 'sakit' ? 'selected' : '' }}>Sakit</option>
                                                    <option value="tidak_hadir" {{ optional($existing)->status === 'tidak_hadir' ? 'selected' : '' }}>Tidak Hadir</option>
                                                </select>
                                            </td>
                                            <td>
                                                <input
                                                    type="text"
                                                    class="form-control form-control-sm"
                                                    name="attendance[{{ $item->id }}][keterangan]"
                                                    value="{{ optional($existing)->keterangan }}"
                                                    placeholder="Opsional"
                                                >
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="6" class="text-center text-muted">Tidak ada data guru aktif.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>

                            <div id="gridViewContainer" class="d-none">
                                <div class="row g-3">
                                    @forelse($daftarGuru as $index => $item)
                                        @php
                                            $existing = $absensiHariIni->get($item->id);
                                            $statusColors = [
                                                'hadir' => 'success',
                                                'izin' => 'warning',
                                                'sakit' => 'info',
                                                'tidak_hadir' => 'danger',
                                            ];
                                            $statusColor = $statusColors[optional($existing)->status] ?? 'secondary';
                                        @endphp
                                        <div class="col-6 col-md-4 col-lg-3">
                                            <div class="card h-100">
                                                <div class="card-body text-center">
                                                    <div class="mx-auto mb-2 overflow-hidden d-flex align-items-center justify-content-center bg-light" style="width: 100%; aspect-ratio: 3/4; border-radius: 0.5rem;">
                                                        @if($item->user && $item->user->foto)
                                                            <img src="{{ asset('storage/' . $item->user->foto) }}" alt="Foto" class="w-100 h-100" style="object-fit: cover;">
                                                        @else
                                                            <div class="d-flex align-items-center justify-content-center text-white fw-bold w-100 h-100" style="background: #dc3545; font-size: 2rem;">{{ mb_substr($item->nama, 0, 1) }}</div>
                                                        @endif
                                                    </div>
                                                    <h6 class="fw-semibold mb-1">{{ $item->nama }}</h6>
                                                    <small class="text-muted d-block mb-2">{{ $item->nip ?: '-' }}</small>
                                                    <span class="badge status-badge bg-{{ $statusColor }} text-white mb-2">{{ $existing ? ucfirst(str_replace('_', ' ', $existing->status)) : 'Belum diisi' }}</span>
                                                    <div class="mt-2">
                                                        <select class="form-select form-select-sm status-select" name="attendance[{{ $item->id }}][status]">
                                                            <option value="" {{ !optional($existing)->status ? 'selected' : '' }}>Pilih status</option>
                                                            <option value="hadir" {{ optional($existing)->status === 'hadir' ? 'selected' : '' }}>Hadir</option>
                                                            <option value="izin" {{ optional($existing)->status === 'izin' ? 'selected' : '' }}>Izin</option>
                                                            <option value="sakit" {{ optional($existing)->status === 'sakit' ? 'selected' : '' }}>Sakit</option>
                                                            <option value="tidak_hadir" {{ optional($existing)->status === 'tidak_hadir' ? 'selected' : '' }}>Tidak Hadir</option>
                                                        </select>
                                                    </div>
                                                    <input
                                                        type="text"
                                                        class="form-control form-control-sm mt-2"
                                                        name="attendance[{{ $item->id }}][keterangan]"
                                                        value="{{ optional($existing)->keterangan }}"
                                                        placeholder="Keterangan"
                                                    >
                                                </div>
                                            </div>
                                        </div>
                                    @empty
                                        <div class="col-12 text-center text-muted py-6">Tidak ada data guru aktif.</div>
                                    @endforelse
                                </div>
                            </div>

                            <div class="d-flex justify-content-end mt-3">
                                <button type="submit" class="btn btn-primary">
                                    <i class="ti ti-device-floppy me-1"></i>Simpan Absensi
                                </button>
                            </div>
                        </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    let currentExportType = 'pdf';

    document.addEventListener('DOMContentLoaded', function() {
        const exportModal = document.getElementById('exportModal');
        const exportTypeSelect = document.getElementById('exportType');
        const singleDateField = document.getElementById('singleDateField');
        const rangeDateField = document.getElementById('rangeDateField');
        const monthField = document.getElementById('monthField');
        const confirmExportBtn = document.getElementById('confirmExport');

        document.querySelectorAll('[data-export-type]').forEach(btn => {
            btn.addEventListener('click', function() {
                currentExportType = this.getAttribute('data-export-type');
            });
        });

        exportTypeSelect.addEventListener('change', function() {
            const val = this.value;
            singleDateField.classList.toggle('d-none', val !== 'single');
            rangeDateField.classList.toggle('d-none', val !== 'range');
            monthField.classList.toggle('d-none', val !== 'month');
        });

        confirmExportBtn.addEventListener('click', function() {
            const exportType = exportTypeSelect.value;
            const tanggal = document.getElementById('exportSingleDate').value;
            const rangeStart = document.getElementById('exportRangeStart').value;
            const rangeEnd = document.getElementById('exportRangeEnd').value;
            const month = document.getElementById('exportMonth').value;
            const year = document.getElementById('exportYear').value;

            let url = '';

            if (exportType === 'single') {
                const baseUrl = currentExportType === 'pdf'
                    ? '{{ route('agenda_guru.absensi.export.pdf') }}'
                    : '{{ route('agenda_guru.absensi.export.excel') }}';
                url = baseUrl + '?tanggal=' + encodeURIComponent(tanggal);
            } else if (exportType === 'range') {
                const baseUrl = currentExportType === 'pdf'
                    ? '{{ route('agenda_guru.absensi.export.range', ['type' => 'pdf']) }}'
                    : '{{ route('agenda_guru.absensi.export.range', ['type' => 'excel']) }}';
                url = baseUrl + '?start=' + encodeURIComponent(rangeStart) + '&end=' + encodeURIComponent(rangeEnd);
            } else if (exportType === 'month') {
                const baseUrl = currentExportType === 'pdf'
                    ? '{{ route('agenda_guru.absensi.export.month', ['type' => 'pdf']) }}'
                    : '{{ route('agenda_guru.absensi.export.month', ['type' => 'excel']) }}';
                url = baseUrl + '?month=' + encodeURIComponent(month) + '&year=' + encodeURIComponent(year);
            }

            if (url) {
                const link = document.createElement('a');
                link.href = url;
                link.target = '_blank';
                document.body.appendChild(link);
                link.click();
                document.body.removeChild(link);
            }

            setTimeout(() => {
                const modalEl = document.getElementById('exportModal');
                if (modalEl && typeof bootstrap !== 'undefined' && bootstrap.Modal) {
                    const instance = bootstrap.Modal.getOrCreateInstance(modalEl);
                    if (instance) {
                        instance.hide();
                    }
                    document.querySelectorAll('.modal-backdrop').forEach(el => el.remove());
                    document.body.classList.remove('modal-open');
                }
            }, 100);
        });
    });
</script>
<script>
    function applyBulkStatus(statusValue) {
        const selects = document.querySelectorAll('.status-select');
        if (!selects.length) return;

        selects.forEach(function (selectEl) {
            selectEl.value = statusValue;
        });
    }

    function handleCheckAllHadir(checkboxEl) {
        if (!checkboxEl) return;
        if (checkboxEl.checked) {
            applyBulkStatus('hadir');
        }
    }

    function updateCheckAllHadirStatus() {
        const checkbox = document.getElementById('checkAllHadir');
        if (!checkbox) return;

        const selects = Array.from(document.querySelectorAll('.status-select'));
        if (!selects.length) return;

        const allPresent = selects.every(select => select.value === 'hadir');
        const nonePresent = selects.every(select => select.value !== 'hadir');

        checkbox.checked = allPresent;
        checkbox.indeterminate = !allPresent && !nonePresent;
    }

    document.addEventListener('DOMContentLoaded', function() {
        setView('list');
        document.querySelectorAll('.status-select').forEach(function(select) {
            select.addEventListener('change', function() {
                updateStatusBadge(this);
                updateCheckAllHadirStatus();
            });
        });
        updateCheckAllHadirStatus();
    });

    function updateStatusBadge(select) {
        const card = select.closest('.card');
        const badge = card ? card.querySelector('.status-badge') : null;
        if (!badge) return;

        const labels = {
            hadir: 'Hadir',
            izin: 'Izin',
            sakit: 'Sakit',
            tidak_hadir: 'Tidak hadir',
            '': 'Belum diisi'
        };
        const colors = {
            hadir: 'success',
            izin: 'warning',
            sakit: 'info',
            tidak_hadir: 'danger',
            '': 'secondary'
        };

        badge.textContent = labels[select.value] || 'Belum diisi';
        badge.classList.remove('bg-success', 'bg-warning', 'bg-info', 'bg-danger', 'bg-secondary');
        badge.classList.add('bg-' + (colors[select.value] || 'secondary'));
    }

    function setView(mode) {
        const listContainer = document.getElementById('listViewContainer');
        const gridContainer = document.getElementById('gridViewContainer');
        const btnList = document.getElementById('btnListView');
        const btnGrid = document.getElementById('btnGridView');

        if (mode === 'grid') {
            listContainer.classList.add('d-none');
            gridContainer.classList.remove('d-none');
            btnGrid.classList.remove('btn-outline-secondary');
            btnGrid.classList.add('btn-primary');
            btnList.classList.remove('btn-primary');
            btnList.classList.add('btn-outline-secondary');
        } else {
            gridContainer.classList.add('d-none');
            listContainer.classList.remove('d-none');
            btnList.classList.remove('btn-outline-secondary');
            btnList.classList.add('btn-primary');
            btnGrid.classList.remove('btn-primary');
            btnGrid.classList.add('btn-outline-secondary');
        }
    }

    document.addEventListener('DOMContentLoaded', function() {
        setView('list');
    });
</script>
@endsection
