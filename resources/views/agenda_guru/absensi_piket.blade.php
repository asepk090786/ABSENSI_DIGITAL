@extends('layouts.app', ['pageSlug' => 'agenda_guru'])

@section('title','Absensi Guru')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <div>
                        <h4 class="card-title mb-0">
                            <i class="ti ti-user-check me-2"></i>Absensi Guru
                        </h4>
                    </div>
                </div>
                <div class="card-body">
                    @if(session('success'))
                        <div class="alert alert-success alert-dismissible fade show">
                            <i class="ti ti-check me-2"></i>{{ session('success') }}
                            <button type="button" class="close" data-dismiss="alert"><span aria-hidden="true">&times;</span></button>
                        </div>
                    @endif

                    @if(session('error'))
                        <div class="alert alert-danger alert-dismissible fade show">
                            <i class="ti ti-alert-circle me-2"></i>{{ session('error') }}
                            <button type="button" class="close" data-dismiss="alert"><span aria-hidden="true">&times;</span></button>
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

                    <form method="GET" action="{{ route('agenda_guru.index') }}" class="row g-2 align-items-end mb-3">
                        <div class="col-12 col-md-4 col-lg-3">
                            <label class="form-label">Tanggal</label>
                            <input type="date" class="form-control" name="tanggal" value="{{ $selectedTanggal }}">
                        </div>
                        <div class="col-auto">
                            <button type="submit" class="btn btn-primary">
                                <i class="ti ti-search me-1"></i>Tampilkan
                            </button>
                        </div>
                    </form>

                    <div class="row mb-3 g-2">
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

                    <form method="POST" action="{{ route('agenda_guru.absensi.store') }}">
                        @csrf
                        <input type="hidden" name="tanggal" value="{{ $selectedTanggal }}">

                        <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
                            <div class="form-check form-switch m-0">
                                <input class="form-check-input" type="checkbox" id="checkAllHadir" onclick="handleCheckAllHadir(this)">
                                <label class="form-check-label" for="checkAllHadir">Ceklis hadir semua</label>
                            </div>
                            <div class="btn-group btn-group-sm" role="group">
                                <button type="button" class="btn btn-outline-danger" id="checkAllTidakHadir" onclick="applyBulkStatus('tidak_hadir'); document.getElementById('checkAllHadir').checked = false;">Tidak hadir semua</button>
                                <button type="button" class="btn btn-outline-secondary" id="clearAllStatus" onclick="applyBulkStatus(''); document.getElementById('checkAllHadir').checked = false;">Kosongkan semua</button>
                            </div>
                        </div>

                        <div class="table-responsive">
                            <table class="table table-bordered table-striped table-hover">
                                <thead class="table-light">
                                    <tr>
                                        <th style="width: 5%;">No</th>
                                        <th>Nama Guru</th>
                                        <th style="width: 20%;">NIP</th>
                                        <th style="width: 20%;">Status</th>
                                        <th style="width: 30%;">Keterangan</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($daftarGuru as $index => $item)
                                        @php
                                            $existing = $absensiHariIni->get($item->id);
                                        @endphp
                                        <tr>
                                            <td>{{ $index + 1 }}</td>
                                            <td>{{ $item->nama }}</td>
                                            <td>{{ $item->nip ?: '-' }}</td>
                                            <td>
                                                <select class="form-select form-select-sm status-select" name="attendance[{{ $item->id }}][status]">
                                                    <option value="hadir" {{ optional($existing)->status === 'hadir' ? 'selected' : '' }}>Hadir</option>
                                                    <option value="izin" {{ optional($existing)->status === 'izin' || !optional($existing)->status ? 'selected' : '' }}>Izin</option>
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
                                            <td colspan="5" class="text-center text-muted">Tidak ada data guru aktif.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>

                        <div class="d-flex justify-content-end">
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
</script>
@endsection
