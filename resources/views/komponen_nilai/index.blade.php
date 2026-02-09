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
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        @endif

        <!-- Tab Navigation -->
        <ul class="nav nav-tabs mb-3" role="tablist">
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

        <!-- Tab Content -->
        <div class="tab-content">
            <!-- TAB 1: CAPAIAN PEMBELAJARAN -->
            <div class="tab-pane fade show active" id="capaian-pane" role="tabpanel">
                <div class="card mb-3">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="ti ti-plus me-2"></i>Tambah Capaian Pembelajaran
                        </h3>
                    </div>
                    <div class="card-body">
                        <form method="POST" action="{{ route('capaian_pembelajaran.store') }}">
                            @csrf
                            <div class="row g-3 mb-3">
                                <div class="col-md-6">
                                    <label class="form-label">Nama Capaian Pembelajaran <span class="text-danger">*</span></label>
                                    <input type="text" name="nama_capaian_pembelajaran" class="form-control @error('nama_capaian_pembelajaran') is-invalid @enderror" value="{{ old('nama_capaian_pembelajaran') }}" required placeholder="Contoh: Menganalisis dan mengevaluasi fenomena sosial">
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
                                    <button type="submit" class="btn btn-primary">
                                        <i class="ti ti-plus me-1"></i>Simpan CP
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>

                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Daftar Capaian Pembelajaran</h3>
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
                                        <!-- Edit Modal -->
                                        <div class="modal modal-blur fade" id="editCapaianModal{{ $cp->id }}" tabindex="-1" role="dialog">
                                            <div class="modal-dialog modal-dialog-centered" role="document">
                                                <div class="modal-content">
                                                    <form method="POST" action="{{ route('capaian_pembelajaran.update', $cp->id) }}">
                                                        @csrf
                                                        @method('PUT')
                                                        <div class="modal-header">
                                                            <h5 class="modal-title">Edit Capaian Pembelajaran</h5>
                                                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                        </div>
                                                        <div class="modal-body">
                                                            <div class="mb-3">
                                                                <label class="form-label">Nama</label>
                                                                <input type="text" name="nama_capaian_pembelajaran" class="form-control" value="{{ $cp->nama_capaian_pembelajaran }}" required>
                                                            </div>
                                                            <div class="mb-3">
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
                                                            <div class="mb-3">
                                                                <label class="form-label">Deskripsi</label>
                                                                <textarea name="deskripsi" class="form-control" rows="3">{{ $cp->deskripsi }}</textarea>
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
            </div>

            <!-- TAB 2: KOMPONEN PENILAIAN -->
            <div class="tab-pane fade" id="komponen-pane" role="tabpanel">
                <div class="card mb-3">
                    <div class="card-header">
                        <h3 class="card-title">Tambah Komponen Penilaian</h3>
                    </div>
                    <div class="card-body">
                        <form method="POST" action="{{ route('komponen_nilai.store') }}">
                            @csrf
                            <!-- Pilih Capaian Pembelajaran -->
                            <div class="row g-3 mb-4">
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

                            <!-- Informasi Dasar -->
                            <div class="row g-3 mb-4">
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

                            <!-- Struktur Pembelajaran Mendalam -->
                            <div class="accordion accordion-flush mb-4" id="learningAccordion">
                                <!-- 1. Capaian Pembelajaran -->
                                <div class="accordion-item">
                                    <h2 class="accordion-header">
                                        <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#cpSection">
                                            <i class="ti ti-target me-2"></i>
                                            <strong>1. Capaian Pembelajaran (CP)</strong>
                                            <small class="text-muted ms-2">Target kompetensi akhir pada fase</small>
                                        </button>
                                    </h2>
                                    <div id="cpSection" class="accordion-collapse collapse show" data-bs-parent="#learningAccordion">
                                        <div class="accordion-body">
                                            <small class="text-muted d-block mb-3">
                                                <strong>Catatan:</strong> Target kompetensi akhir pada fase (misalnya Fase E & F di SMA). Bersifat umum dan menyeluruh, menjadi acuan utama pembelajaran.
                                            </small>
                                            <textarea name="capaian_pembelajaran" class="form-control @error('capaian_pembelajaran') is-invalid @enderror" rows="4" placeholder="Contoh: Siswa dapat menganalisis dan mengevaluasi fenomena sosial dengan perspektif kritis...">{{ old('capaian_pembelajaran') }}</textarea>
                                            @error('capaian_pembelajaran')
                                                <div class="invalid-feedback d-block">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                </div>

                                <!-- 2. Tujuan Pembelajaran -->
                                <div class="accordion-item">
                                    <h2 class="accordion-header">
                                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#tpSection">
                                            <i class="ti ti-target me-2"></i>
                                            <strong>2. Tujuan Pembelajaran (TP)</strong>
                                            <small class="text-muted ms-2">Turunan spesifik dari CP</small>
                                        </button>
                                    </h2>
                                    <div id="tpSection" class="accordion-collapse collapse" data-bs-parent="#learningAccordion">
                                        <div class="accordion-body">
                                            <small class="text-muted d-block mb-3">
                                                <strong>Catatan:</strong> Turunan langsung dari CP, lebih spesifik, operasional, dan terukur. Menjadi sasaran pembelajaran harian/per pertemuan yang mendorong berpikir kritis, reflektif, dan bermakna.
                                            </small>
                                            <textarea name="tujuan_pembelajaran" class="form-control @error('tujuan_pembelajaran') is-invalid @enderror" rows="4" placeholder="Contoh: Peserta didik dapat mengidentifikasi penyebab masalah sosial dan merumuskan solusi alternatif...">{{ old('tujuan_pembelajaran') }}</textarea>
                                            @error('tujuan_pembelajaran')
                                                <div class="invalid-feedback d-block">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                </div>

                                <!-- 3. Alur Tujuan Pembelajaran -->
                                <div class="accordion-item">
                                    <h2 class="accordion-header">
                                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#atpSection">
                                            <i class="ti ti-arrows-shuffle me-2"></i>
                                            <strong>3. Alur Tujuan Pembelajaran (ATP)</strong>
                                            <small class="text-muted ms-2">Rangkaian TP yang progresif</small>
                                        </button>
                                    </h2>
                                    <div id="atpSection" class="accordion-collapse collapse" data-bs-parent="#learningAccordion">
                                        <div class="accordion-body">
                                            <small class="text-muted d-block mb-3">
                                                <strong>Catatan:</strong> Rangkaian TP yang logis, berkesinambungan, dan progresif. Menunjukkan kedalaman pemahaman, bukan sekadar urutan materi.
                                            </small>
                                            <textarea name="alur_tujuan_pembelajaran" class="form-control @error('alur_tujuan_pembelajaran') is-invalid @enderror" rows="4" placeholder="Contoh: Pertemuan 1-2: Pemahaman... → Pertemuan 3-4: Analisis... → Pertemuan 5-6: Evaluasi dan refleksi...">{{ old('alur_tujuan_pembelajaran') }}</textarea>
                                            @error('alur_tujuan_pembelajaran')
                                                <div class="invalid-feedback d-block">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                </div>

                                <!-- 4. Indikator / Kriteria Ketercapaian -->
                                <div class="accordion-item">
                                    <h2 class="accordion-header">
                                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#kkSection">
                                            <i class="ti ti-checks me-2"></i>
                                            <strong>4. Indikator / Kriteria Ketercapaian (KKTP)</strong>
                                            <small class="text-muted ms-2">Ukuran pencapaian TP</small>
                                        </button>
                                    </h2>
                                    <div id="kkSection" class="accordion-collapse collapse" data-bs-parent="#learningAccordion">
                                        <div class="accordion-body">
                                            <small class="text-muted d-block mb-3">
                                                <strong>Catatan:</strong> Kriteria Ketercapaian Tujuan Pembelajaran (KKTP). Ukuran apakah TP sudah tercapai, menekankan pemahaman konsep, transfer pengetahuan, dan refleksi.
                                            </small>
                                            <textarea name="indikator_kriteria" class="form-control @error('indikator_kriteria') is-invalid @enderror" rows="4" placeholder="Contoh: • Dapat menjelaskan min 3 penyebab... | • Dapat mengajukan 2 solusi yang realistis... | • Dapat merefleksikan pembelajaran dengan analisis kritis...">{{ old('indikator_kriteria') }}</textarea>
                                            @error('indikator_kriteria')
                                                <div class="invalid-feedback d-block">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <button type="submit" class="btn btn-primary">
                                <i class="ti ti-plus me-1"></i>Simpan Komponen Penilaian
                            </button>
                        </form>
                    </div>
                </div>

                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Daftar Komponen</h3>
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
                                            <th>Bobot (%)</th>
                                            <th class="text-center">Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($items as $index => $item)
                                        <tr>
                                            <td>{{ $index + 1 }}</td>
                                            <td>
                                                @if($item->capaianPembelajaran)
                                                    <span class="badge badge-primary">{{ $item->capaianPembelajaran->nama_capaian_pembelajaran }}</span>
                                                @else
                                                    <span class="badge badge-secondary">Tidak ada</span>
                                                @endif
                                            </td>
                                            <td>{{ $item->nama_komponen }}</td>
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
            </div>
        </div>
    </div>
</div>
@endsection
                <form method="POST" action="{{ route('komponen_nilai.store') }}">
                    @csrf
                    <!-- Informasi Dasar -->
                    <div class="row g-3 mb-4">
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

                    <!-- Struktur Pembelajaran Mendalam -->
                    <div class="accordion accordion-flush mb-4" id="learningAccordion">
                        <!-- 1. Capaian Pembelajaran -->
                        <div class="accordion-item">
                            <h2 class="accordion-header">
                                <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#cpSection">
                                    <i class="ti ti-target me-2"></i>
                                    <strong>1. Capaian Pembelajaran (CP)</strong>
                                    <small class="text-muted ms-2">Target kompetensi akhir pada fase</small>
                                </button>
                            </h2>
                            <div id="cpSection" class="accordion-collapse collapse show" data-bs-parent="#learningAccordion">
                                <div class="accordion-body">
                                    <small class="text-muted d-block mb-3">
                                        <strong>Catatan:</strong> Target kompetensi akhir pada fase (misalnya Fase E & F di SMA). Bersifat umum dan menyeluruh, menjadi acuan utama pembelajaran.
                                    </small>
                                    <textarea name="capaian_pembelajaran" class="form-control @error('capaian_pembelajaran') is-invalid @enderror" rows="4" placeholder="Contoh: Siswa dapat menganalisis dan mengevaluasi fenomena sosial dengan perspektif kritis...">{{ old('capaian_pembelajaran') }}</textarea>
                                    @error('capaian_pembelajaran')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <!-- 2. Tujuan Pembelajaran -->
                        <div class="accordion-item">
                            <h2 class="accordion-header">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#tpSection">
                                    <i class="ti ti-target me-2"></i>
                                    <strong>2. Tujuan Pembelajaran (TP)</strong>
                                    <small class="text-muted ms-2">Turunan spesifik dari CP</small>
                                </button>
                            </h2>
                            <div id="tpSection" class="accordion-collapse collapse" data-bs-parent="#learningAccordion">
                                <div class="accordion-body">
                                    <small class="text-muted d-block mb-3">
                                        <strong>Catatan:</strong> Turunan langsung dari CP, lebih spesifik, operasional, dan terukur. Menjadi sasaran pembelajaran harian/per pertemuan yang mendorong berpikir kritis, reflektif, dan bermakna.
                                    </small>
                                    <textarea name="tujuan_pembelajaran" class="form-control @error('tujuan_pembelajaran') is-invalid @enderror" rows="4" placeholder="Contoh: Peserta didik dapat mengidentifikasi penyebab masalah sosial dan merumuskan solusi alternatif...">{{ old('tujuan_pembelajaran') }}</textarea>
                                    @error('tujuan_pembelajaran')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <!-- 3. Alur Tujuan Pembelajaran -->
                        <div class="accordion-item">
                            <h2 class="accordion-header">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#atpSection">
                                    <i class="ti ti-arrows-shuffle me-2"></i>
                                    <strong>3. Alur Tujuan Pembelajaran (ATP)</strong>
                                    <small class="text-muted ms-2">Rangkaian TP yang progresif</small>
                                </button>
                            </h2>
                            <div id="atpSection" class="accordion-collapse collapse" data-bs-parent="#learningAccordion">
                                <div class="accordion-body">
                                    <small class="text-muted d-block mb-3">
                                        <strong>Catatan:</strong> Rangkaian TP yang logis, berkesinambungan, dan progresif. Menunjukkan kedalaman pemahaman, bukan sekadar urutan materi.
                                    </small>
                                    <textarea name="alur_tujuan_pembelajaran" class="form-control @error('alur_tujuan_pembelajaran') is-invalid @enderror" rows="4" placeholder="Contoh: Pertemuan 1-2: Pemahaman... → Pertemuan 3-4: Analisis... → Pertemuan 5-6: Evaluasi dan refleksi...">{{ old('alur_tujuan_pembelajaran') }}</textarea>
                                    @error('alur_tujuan_pembelajaran')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <!-- 4. Indikator / Kriteria Ketercapaian -->
                        <div class="accordion-item">
                            <h2 class="accordion-header">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#kkSection">
                                    <i class="ti ti-checks me-2"></i>
                                    <strong>4. Indikator / Kriteria Ketercapaian (KKTP)</strong>
                                    <small class="text-muted ms-2">Ukuran pencapaian TP</small>
                                </button>
                            </h2>
                            <div id="kkSection" class="accordion-collapse collapse" data-bs-parent="#learningAccordion">
                                <div class="accordion-body">
                                    <small class="text-muted d-block mb-3">
                                        <strong>Catatan:</strong> Kriteria Ketercapaian Tujuan Pembelajaran (KKTP). Ukuran apakah TP sudah tercapai, menekankan pemahaman konsep, transfer pengetahuan, dan refleksi.
                                    </small>
                                    <textarea name="indikator_kriteria" class="form-control @error('indikator_kriteria') is-invalid @enderror" rows="4" placeholder="Contoh: • Dapat menjelaskan min 3 penyebab... | • Dapat mengajukan 2 solusi yang realistis... | • Dapat merefleksikan pembelajaran dengan analisis kritis...">{{ old('indikator_kriteria') }}</textarea>
                                    @error('indikator_kriteria')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-primary">
                        <i class="ti ti-plus me-1"></i>Simpan Komponen Penilaian
                    </button>
                </form>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Daftar Komponen</h3>
            </div>
            <div class="card-body">
                @if($items->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-vcenter table-hover">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>Nama Komponen</th>
                                    <th>Bobot (%)</th>
                                    <th class="text-center">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($items as $index => $item)
                                <tr>
                                    <td>{{ $index + 1 }}</td>
                                    <td>{{ $item->nama_komponen }}</td>
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
                    <div class="text-muted">Belum ada komponen penilaian.</div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
