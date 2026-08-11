@extends('layouts.app')

@section('title', 'Komponen Penilaian & Capaian Pembelajaran')

@section('content')
<div class="page-header d-print-none">
    <div class="container-xl">
        <div class="row g-2 align-items-center">
            <div class="col">
                <h2 class="page-title">
                    <i class="ti ti-checklist me-2"></i>Komponen Penilaian & Capaian Pembelajaran
                </h2>
                <div class="text-muted mt-1">Kelola komponen penilaian dalam konteks capaian pembelajaran</div>
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
        
        @if(session('warning'))
        <div class="alert alert-warning alert-dismissible fade show" role="alert">
            <i class="ti ti-alert-triangle me-2"></i>{{ session('warning') }}
            <button type="button" class="close" data-bs-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        </div>
        @endif
        
        @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="ti ti-circle-x me-2"></i>{{ session('error') }}
            <button type="button" class="close" data-bs-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        </div>
        @endif
        
        @if(session('import_errors') && count(session('import_errors', [])) > 0)
        <div class="alert alert-warning alert-dismissible fade show" role="alert">
            <i class="ti ti-alert-triangle me-2"></i>
            <strong>Kesalahan Import:</strong>
            <ul class="mb-0 mt-2">
                @foreach(session('import_errors', []) as $error)
                <li>{{ $error }}</li>
                @endforeach
            </ul>
            <button type="button" class="close" data-bs-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        </div>
        @endif

        
        <ul class="nav nav-tabs mb-2" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active" id="capaian-tab" data-bs-toggle="tab" data-bs-target="#capaian-pane" type="button" role="tab">
                    <i class="ti ti-book me-2"></i>Capaian Pembelajaran
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="komponen-tab" data-bs-toggle="tab" data-bs-target="#komponen-pane" type="button" role="tab">
                    <i class="ti ti-checklist-2 me-2"></i>Komponen Penilaian
                </button>
            </li>
        </ul>

        
        <div class="tab-content">
            
            <div class="tab-pane fade show active" id="capaian-pane" role="tabpanel">
                <div class="card mb-2">
                    <div class="card-header border-0 pt-3 pb-2">
                        <h3 class="card-title">
                            <i class="ti ti-plus me-2"></i>Tambah Capaian Pembelajaran
                        </h3>
                    </div>
                    <div class="card-body">
                        <form method="POST" action="{{ route('capaian_pembelajaran.store') }}">
                            @csrf
                            <div class="row g-2 mb-2">
                                <div class="col-md-6">
                                    <label class="form-label">Nama Capaian Pembelajaran <span class="text-danger">*</span></label>
                                    <input type="text" name="nama_capaian_pembelajaran" class="form-control @error('nama_capaian_pembelajaran') is-invalid @enderror" value="{{ old('nama_capaian_pembelajaran') }}" required maxlength="191" placeholder="Contoh: Menganalisis dan mengevaluasi fenomena sosial">
                                    @error('nama_capaian_pembelajaran')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Fase</label>
                                    <select name="fase" class="form-select @error('fase') is-invalid @enderror">
                                        <option value="">-- Pilih Fase --</option>
                                        <option value="A" {{ old('fase') == 'A' ? 'selected' : '' }}>Fase A (SD Kelas 1-2)</option>
                                        <option value="B" {{ old('fase') == 'B' ? 'selected' : '' }}>Fase B (SD Kelas 3-4)</option>
                                        <option value="C" {{ old('fase') == 'C' ? 'selected' : '' }}>Fase C (SD Kelas 5-6)</option>
                                        <option value="D" {{ old('fase') == 'D' ? 'selected' : '' }}>Fase D (SMP Kelas 7-9)</option>
                                        <option value="E" {{ old('fase') == 'E' ? 'selected' : '' }}>Fase E (SMA Kelas 10)</option>
                                        <option value="F" {{ old('fase') == 'F' ? 'selected' : '' }}>Fase F (SMA Kelas 11-12)</option>
                                    </select>
                                    @error('fase')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-12">
                                    <label class="form-label">Deskripsi</label>
                                    <textarea name="deskripsi" class="form-control @error('deskripsi') is-invalid @enderror" rows="3" placeholder="Jelaskan capaian pembelajaran secara detail...">{{ old('deskripsi') }}</textarea>
                                    @error('deskripsi')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-12">
                                    <label class="form-label">Tujuan Pembelajaran (TP)</label>
                                    <textarea name="tujuan_pembelajaran" class="form-control @error('tujuan_pembelajaran') is-invalid @enderror" rows="3" placeholder="Turunan spesifik dari CP, lebih spesifik, operasional, dan terukur...">{{ old('tujuan_pembelajaran') }}</textarea>
                                    @error('tujuan_pembelajaran')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-12">
                                    <label class="form-label">Alur Tujuan Pembelajaran (ATP)</label>
                                    <textarea name="alur_tujuan_pembelajaran" class="form-control @error('alur_tujuan_pembelajaran') is-invalid @enderror" rows="3" placeholder="Rangkaian TP yang logis, berkesinambungan, dan progresif...">{{ old('alur_tujuan_pembelajaran') }}</textarea>
                                    @error('alur_tujuan_pembelajaran')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-12">
                                    <label class="form-label">Indikator / Kriteria Ketercapaian (KKTP)</label>
                                    <textarea name="indikator_kriteria" class="form-control @error('indikator_kriteria') is-invalid @enderror" rows="3" placeholder="Kriteria ketercapaian TP, ukuran apakah TP sudah tercapai...">{{ old('indikator_kriteria') }}</textarea>
                                    @error('indikator_kriteria')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-12">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="ti ti-plus me-1"></i>Simpan CP
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>

                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h3 class="card-title fw-semibold m-0">Daftar Capaian Pembelajaran</h3>
                        <div class="btn-group" role="group">
                            <a href="{{ route('capaian_pembelajaran.export') }}" class="btn btn-sm btn-info btn-modern" title="Download data CP">
                                <i class="ti ti-download me-1"></i>Export
                            </a>
                            <a href="{{ route('capaian_pembelajaran.template') }}" class="btn btn-sm btn-secondary btn-modern" title="Download template">
                                <i class="ti ti-file-download me-1"></i>Template
                            </a>
                            <button type="button" class="btn btn-sm btn-success btn-modern" data-bs-toggle="modal" data-bs-target="#importCapaianModal" title="Upload file CP">
                                <i class="ti ti-upload me-1"></i>Import
                            </button>
                        </div>
                    </div>
                    <div class="card-body">
                        @if($capaianList->count() > 0)
                            <div class="table-responsive">
                                <table class="table table-vcenter table-hover">
                                    <thead>
                                        <tr>
                                            <th>No</th>
                                            <th>Nama Capaian Pembelajaran</th>
                                            <th>Fase</th>
                                            <th>Deskripsi</th>
                                            <th class="text-center">Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($capaianList as $index => $cp)
                                        <tr>
                                            <td>{{ $index + 1 }}</td>
                                            <td>{{ $cp->nama_capaian_pembelajaran }}</td>
                                            <td><span class="badge badge-blue">Fase {{ $cp->fase ?? '-' }}</span></td>
                                            <td><small>{{ Str::limit($cp->deskripsi, 50) }}</small></td>
                                            <td class="text-center">
                                                <div class="btn-group" role="group">
                                                    <button type="button" class="btn btn-sm btn-warning" data-bs-toggle="modal" data-bs-target="#editCapaianModal{{ $cp->id }}" title="Edit">
                                                        <i class="ti ti-edit"></i>
                                                    </button>
                                                    <form action="{{ route('capaian_pembelajaran.destroy', $cp->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Hapus capaian ini?')">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="btn btn-sm btn-danger" title="Hapus">
                                                            <i class="ti ti-trash"></i>
                                                        </button>
                                                    </form>
                                                </div>
                                            </td>
                                        </tr>
                                        
                                        <div class="modal modal-blur fade" id="editCapaianModal{{ $cp->id }}" tabindex="-1" role="dialog">
                                            <div class="modal-dialog modal-dialog-centered" role="document">
                                                <div class="modal-content">
                                                    <form method="POST" action="{{ route('capaian_pembelajaran.update', $cp->id) }}">
                                                        @csrf
                                                        @method('PUT')
                                                        <div class="modal-header">
                                                            <h5 class="modal-title">Edit Capaian Pembelajaran</h5>
                                                            <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                                                        </div>
                                                        <div class="modal-body">
                                                            <div class="mb-2">
                                                                <label class="form-label">Nama</label>
                                                                <input type="text" name="nama_capaian_pembelajaran" class="form-control" value="{{ $cp->nama_capaian_pembelajaran }}" required maxlength="191">
                                                            </div>
                                                            <div class="mb-2">
                                                                <label class="form-label">Fase</label>
                                                                <select name="fase" class="form-select">
                                                                    <option value="">-- Pilih Fase --</option>
                                                                    <option value="A" {{ $cp->fase == 'A' ? 'selected' : '' }}>Fase A</option>
                                                                    <option value="B" {{ $cp->fase == 'B' ? 'selected' : '' }}>Fase B</option>
                                                                    <option value="C" {{ $cp->fase == 'C' ? 'selected' : '' }}>Fase C</option>
                                                                    <option value="D" {{ $cp->fase == 'D' ? 'selected' : '' }}>Fase D</option>
                                                                    <option value="E" {{ $cp->fase == 'E' ? 'selected' : '' }}>Fase E</option>
                                                                    <option value="F" {{ $cp->fase == 'F' ? 'selected' : '' }}>Fase F</option>
                                                                </select>
                                                            </div>
                                                            <div class="mb-2">
                                                                <label class="form-label">Deskripsi</label>
                                                                <textarea name="deskripsi" class="form-control" rows="3">{{ $cp->deskripsi }}</textarea>
                                                            </div>
                                                            <div class="mb-2">
                                                                <label class="form-label">Tujuan Pembelajaran (TP)</label>
                                                                <textarea name="tujuan_pembelajaran" class="form-control" rows="3">{{ $cp->tujuan_pembelajaran }}</textarea>
                                                            </div>
                                                            <div class="mb-2">
                                                                <label class="form-label">Alur Tujuan Pembelajaran (ATP)</label>
                                                                <textarea name="alur_tujuan_pembelajaran" class="form-control" rows="3">{{ $cp->alur_tujuan_pembelajaran }}</textarea>
                                                            </div>
                                                            <div class="mb-2">
                                                                <label class="form-label">Indikator / Kriteria Ketercapaian (KKTP)</label>
                                                                <textarea name="indikator_kriteria" class="form-control" rows="3">{{ $cp->indikator_kriteria }}</textarea>
                                                            </div>
                                                        </div>
                                                        <div class="modal-footer">
                                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                                                            <button type="submit" class="btn btn-primary">Update</button>
                                                        </div>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @else
                            <div class="alert alert-info">
                                <i class="ti ti-info-circle me-2"></i>Belum ada capaian pembelajaran. Mulai dengan membuat capaian pembelajaran terlebih dahulu.
                            </div>
                        @endif
                    </div>
                </div>
                
                
                <div class="modal modal-blur fade" id="importCapaianModal" tabindex="-1" role="dialog">
                    <div class="modal-dialog modal-dialog-centered" role="document">
                        <div class="modal-content">
                            <form method="POST" action="{{ route('capaian_pembelajaran.import') }}" enctype="multipart/form-data">
                                @csrf
                                <div class="modal-header">
                                    <h5 class="modal-title">Import Capaian Pembelajaran</h5>
                                    <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                                </div>
                                <div class="modal-body">
                                    <div class="alert alert-info">
                                        <i class="ti ti-info-circle me-2"></i>
                                        <strong>Panduan:</strong> Upload file Excel (xlsx, xls, atau csv) berisi data Capaian Pembelajaran. 
                                        <a href="{{ route('capaian_pembelajaran.template') }}">Download template</a> untuk melihat format.
                                    </div>
                                    <div class="mb-2">
                                        <label class="form-label">Pilih File <span class="text-danger">*</span></label>
                                        <input type="file" name="file" class="form-control @error('file') is-invalid @enderror" accept=".xlsx,.xls,.csv" required>
                                        @error('file')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                                    <button type="submit" class="btn btn-primary">
                                        <i class="ti ti-upload me-1"></i>Upload
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            
            <div class="tab-pane fade" id="komponen-pane" role="tabpanel">
                <div class="card mb-2">
                    <div class="card-header border-0 pt-3 pb-2">
                        <h3 class="card-title">Tambah Komponen Penilaian</h3>
                    </div>
                    <div class="card-body">
                        <form method="POST" action="{{ route('komponen_nilai.store') }}">
                            @csrf
                            
                            <div class="row g-2 mb-4">
                                <div class="col-md-12">
                                    <label class="form-label">Capaian Pembelajaran (Opsional)</label>
                                    <select name="capaian_pembelajaran_id" class="form-select @error('capaian_pembelajaran_id') is-invalid @enderror">
                                        <option value="">-- Pilih Capaian Pembelajaran --</option>
                                        @foreach($capaianList as $cp)
                                        <option value="{{ $cp->id }}" {{ old('capaian_pembelajaran_id') == $cp->id ? 'selected' : '' }}>
                                            [{{ $cp->fase ?? 'N/A' }}] {{ $cp->nama_capaian_pembelajaran }}
                                        </option>
                                        @endforeach
                                    </select>
                                    @error('capaian_pembelajaran_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            
                            <div class="row g-2 mb-4">
                                <div class="col-md-6">
                                    <label class="form-label">Nama Komponen <span class="text-danger">*</span></label>
                                    <input type="text" name="nama_komponen" class="form-control @error('nama_komponen') is-invalid @enderror" value="{{ old('nama_komponen') }}" required>
                                    @error('nama_komponen')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Bobot (%)</label>
                                    <input type="number" name="bobot" class="form-control @error('bobot') is-invalid @enderror" value="{{ old('bobot') }}" min="0" max="100" step="0.01">
                                    @error('bobot')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <button type="submit" class="btn btn-primary">
                                <i class="ti ti-plus me-1"></i>Simpan Komponen Penilaian
                            </button>
                        </form>
                    </div>
                </div>

                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h3 class="card-title fw-semibold m-0">Daftar Komponen</h3>
                        <div class="btn-group" role="group">
                            <a href="{{ route('komponen_nilai.export') }}" class="btn btn-sm btn-info btn-modern" title="Download data Komponen">
                                <i class="ti ti-download me-1"></i>Export
                            </a>
                            <a href="{{ route('komponen_nilai.template') }}" class="btn btn-sm btn-secondary btn-modern" title="Download template">
                                <i class="ti ti-file-download me-1"></i>Template
                            </a>
                            <button type="button" class="btn btn-sm btn-success btn-modern" data-bs-toggle="modal" data-bs-target="#importKomponenModal" title="Upload file Komponen">
                                <i class="ti ti-upload me-1"></i>Import
                            </button>
                        </div>
                    </div>
                    <div class="card-body">
                        @if($items->count() > 0)
                            <div class="table-responsive">
                                <table class="table table-vcenter table-hover">
                                    <thead>
                                        <tr>
                                            <th>No</th>
                                            <th>CP</th>
                                            <th>Nama Komponen</th>
                                            <th>Nama Guru</th>
                                            <th>Mata Pelajaran</th>
                                            <th>Bobot (%)</th>
                                            <th class="text-center">Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($items as $index => $item)
                                        @php
                                            $guruNames = $item->rencanaPembelajaran->pluck('guru.nama')->filter()->unique()->values()->all();
                                            $mapelNames = $item->rencanaPembelajaran->pluck('mataPelajaran.nama_mapel')->filter()->unique()->values()->all();
                                        @endphp
                                        <tr>
                                            <td>{{ $index + 1 }}</td>
                                            <td>
                                                @if($item->capaianPembelajaran)
                                                    <span class="badge bg-primary text-white">{{ $item->capaianPembelajaran->nama_capaian_pembelajaran }}</span>
                                                @else
                                                    <span class="badge bg-secondary text-white">Tidak ada</span>
                                                @endif
                                            </td>
                                            <td>{{ $item->nama_komponen }}</td>
                                            <td>{{ count($guruNames) ? implode(', ', $guruNames) : '-' }}</td>
                                            <td>{{ count($mapelNames) ? implode(', ', $mapelNames) : '-' }}</td>
                                            <td>{{ $item->bobot ?? '-' }}</td>
                                            <td class="text-center">
                                                <div class="btn-group" role="group">
                                                    <a href="{{ route('komponen_nilai.edit', $item->id) }}" class="btn btn-sm btn-warning" title="Edit">
                                                        <i class="ti ti-edit"></i>
                                                    </a>
                                                    <form action="{{ route('komponen_nilai.destroy', $item->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Hapus komponen ini?')">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="btn btn-sm btn-danger" title="Hapus">
                                                            <i class="ti ti-trash"></i>
                                                        </button>
                                                    </form>
                                                </div>
                                            </td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @else
                            <div class="alert alert-info">
                                <i class="ti ti-info-circle me-2"></i>Belum ada komponen penilaian.
                            </div>
                        @endif
                    </div>
                </div>
                
                
                <div class="modal modal-blur fade" id="importKomponenModal" tabindex="-1" role="dialog">
                    <div class="modal-dialog modal-dialog-centered" role="document">
                        <div class="modal-content">
                            <form method="POST" action="{{ route('komponen_nilai.import') }}" enctype="multipart/form-data">
                                @csrf
                                <div class="modal-header">
                                    <h5 class="modal-title">Import Komponen Penilaian</h5>
                                    <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                                </div>
                                <div class="modal-body">
                                    <div class="alert alert-info">
                                        <i class="ti ti-info-circle me-2"></i>
                                        <strong>Panduan:</strong> Upload file Excel (xlsx, xls, atau csv) berisi data Komponen Penilaian. 
                                        <a href="{{ route('komponen_nilai.template') }}">Download template</a> untuk melihat format.
                                    </div>
                                    <div class="mb-2">
                                        <label class="form-label">Pilih File <span class="text-danger">*</span></label>
                                        <input type="file" name="file" class="form-control @error('file') is-invalid @enderror" accept=".xlsx,.xls,.csv" required>
                                        @error('file')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                                    <button type="submit" class="btn btn-primary">
                                        <i class="ti ti-upload me-1"></i>Upload
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
