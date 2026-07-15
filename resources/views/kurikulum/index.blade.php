@extends('layouts.app', ['pageSlug' => 'kurikulum'])

@section('title','Struktur Kurikulum')

@section('content')
<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h4 class="card-title fw-semibold m-0">Struktur Kurikulum</h4>
                <a href="{{ route('jadwal-kbm.index') }}" class="btn btn-secondary btn-sm">
                    <i class="ti ti-arrow-left me-1"></i>Kembali ke Jadwal KBM
                </a>
            </div>
            <div class="card-body">
                @if(session('success'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <i class="ti ti-check me-2"></i>{{ session('success') }}
                        <button type="button" class="close" data-bs-dismiss="alert"><span aria-hidden="true">&times;</span></button>
                    </div>
                @endif

                @if(session('error'))
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <i class="ti ti-alert-circle me-2"></i>{{ session('error') }}
                        <button type="button" class="close" data-bs-dismiss="alert"><span aria-hidden="true">&times;</span></button>
                    </div>
                @endif

                <div class="alert alert-info">
                    <i class="ti ti-info-circle me-2"></i>
                    Pilih tingkat dan jurusan, lalu tentukan mata pelajaran yang berlaku. Pengaturan ini menjadi dasar jadwal per kelas dan guru.
                </div>

                <div class="row g-2 mb-2 align-items-end">
                    <div class="col-md-3">
                        <label class="form-label">Tingkat</label>
                        <form method="GET" action="{{ route('kurikulum.index') }}" id="filterForm">
                            <div class="input-group">
                                <select name="tingkat" class="form-select" onchange="document.getElementById('filterForm').submit();">
                                    @foreach($tingkatList as $tingkat)
                                        <option value="{{ $tingkat }}" {{ $tingkat == $selectedTingkat ? 'selected' : '' }}>{{ $tingkat }}</option>
                                    @endforeach
                                </select>
                                <select name="jurusan" class="form-select" onchange="document.getElementById('filterForm').submit();">
                                    @foreach($jurusanList as $jur)
                                        <option value="{{ $jur }}" {{ $jur == $selectedJurusan ? 'selected' : '' }}>{{ $jur }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </form>
                    </div>

                    <div class="col-md-6">
                        <form class="row g-2" method="POST" action="{{ route('kurikulum.add-item') }}">
                            @csrf
                            <input type="hidden" name="tingkat" value="{{ $selectedTingkat }}">
                            <input type="hidden" name="jurusan" value="{{ $selectedJurusan }}">
                            <div class="col-md-6">
                                <label class="form-label">Mata Pelajaran</label>
                                <select name="mata_pelajaran_id" class="form-select" required>
                                    <option value="">-- Pilih Mapel --</option>
                                    @foreach($mapel as $m)
                                        <option value="{{ $m->id }}">{{ $m->kode_mapel }} - {{ $m->nama_mapel }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">JP</label>
                                <input type="number" name="jp" class="form-control" min="0" max="50" required>
                            </div>
                            <div class="col-md-3 d-grid">
                                <label class="form-label">&nbsp;</label>
                                <button type="submit" class="btn btn-primary">
                                    <i class="ti ti-plus me-1"></i>Tambah
                                </button>
                            </div>
                        </form>
                    </div>

                    <div class="col-md-3 d-flex gap-2 justify-content-end">
                        <a class="btn btn-sm btn-success btn-modern" href="{{ route('kurikulum.export', ['tingkat' => $selectedTingkat, 'jurusan' => $selectedJurusan]) }}">
                            <i class="ti ti-download me-1"></i>Export
                        </a>
                        <form method="POST" action="{{ route('kurikulum.import') }}" enctype="multipart/form-data" class="d-flex gap-1">
                            @csrf
                            <input type="hidden" name="tingkat" value="{{ $selectedTingkat }}">
                            <input type="hidden" name="jurusan" value="{{ $selectedJurusan }}">
                            <input type="file" name="file" class="form-control form-control-sm" accept=".xlsx,.xls,.csv" required>
                            <button type="submit" class="btn btn-sm btn-info btn-modern">
                                <i class="ti ti-upload me-1"></i>Import
                            </button>
                        </form>
                    </div>
                </div>

                <hr class="my-4">

                <h5 class="mb-2">Tabel Kurikulum per Tingkat</h5>
                @forelse($kurikulumByTingkat as $tingkat => $items)
                <div class="card mb-2">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <div>
                            <strong>Tingkat {{ $tingkat }}</strong>
                        </div>
                        <span class="badge bg-primary">Total JP: {{ $totalJpPerTingkat[$tingkat] ?? 0 }}</span>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-bordered mb-0 align-middle">
                                <thead class="table-light">
                                    <tr>
                                        <th class="text-center" width="6%">No</th>
                                        <th width="15%">Jurusan</th>
                                        <th width="20%">Kode</th>
                                        <th>Nama Mapel</th>
                                        <th width="10%" class="text-end">JP</th>
                                        <th width="12%" class="text-center">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($items as $idx => $row)
                                    <tr>
                                        <td class="text-center">{{ $idx + 1 }}</td>
                                        <td>{{ $row->jurusan ?? '-' }}</td>
                                        <td>{{ $row->kode_mapel }}</td>
                                        <td>{{ $row->nama_mapel }}</td>
                                        <td class="text-end">
                                            <form class="d-flex justify-content-end gap-2" method="POST" action="{{ route('kurikulum.update-item', $row->id) }}">
                                                @csrf
                                                @method('PUT')
                                                <input type="number" name="jp" class="form-control form-control-sm text-end" style="width:90px" min="0" max="50" value="{{ $row->jp }}">
                                                <button class="btn btn-sm btn-outline-primary" type="submit" title="Update JP">
                                                    <i class="ti ti-device-floppy"></i>
                                                </button>
                                            </form>
                                        </td>
                                        <td class="text-center">
                                            <form method="POST" action="{{ route('kurikulum.delete-item', $row->id) }}" onsubmit="return confirm('Hapus mapel ini dari struktur?');" class="d-inline">
                                                @csrf
                                                @method('DELETE')
                                                <button class="btn btn-sm btn-outline-danger" type="submit">
                                                    <i class="ti ti-trash"></i>
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="6" class="text-center text-muted">Belum ada data.</td>
                                    </tr>
                                    @endforelse
                                </tbody>
                                <tfoot>
                                    <tr>
                                        <th colspan="4" class="text-end">Total JP</th>
                                        <th class="text-end">{{ $totalJpPerTingkat[$tingkat] ?? 0 }}</th>
                                        <th></th>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                </div>
                @empty
                <div class="alert alert-secondary">Belum ada struktur kurikulum tersimpan.</div>
                @endforelse
            </div>
        </div>
    </div>
</div>
@endsection
