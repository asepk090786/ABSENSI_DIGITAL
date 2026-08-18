@extends('layouts.app', ['pageSlug' => 'editor_modul'])

@section('title', 'Edit Modul Ajar - Only Office')

@section('content')
<div class="card shadow-sm border-0">
    <div class="card-header border-0 d-flex justify-content-between align-items-center">
        <div>
            <h3 class="fw-semibold mb-1">Edit Modul Ajar</h3>
            <p class="text-muted mb-0">Pilih modul ajar untuk diedit menggunakan Only Office.</p>
        </div>
    </div>
    <div class="card-body">
        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif
        @if(session('error'))
            <div class="alert alert-danger">{{ session('error') }}</div>
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

        <div class="mb-4">
            <form action="{{ route('akademik.editor_modul.store') }}" method="POST" enctype="multipart/form-data" class="row g-3 align-items-end">
                @csrf
                <div class="col-md-3">
                    <label class="form-label">Mata Pelajaran</label>
                    <select name="mata_pelajaran_id" class="form-select">
                        <option value="">-- Pilih Mata Pelajaran --</option>
                        @foreach($mataPelajaranList as $mp)
                            <option value="{{ $mp->id }}">{{ $mp->nama_mapel ?? ($mp['nama_mapel'] ?? $mp->name ?? '') }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Kelas</label>
                    <select name="kelas_id" class="form-select">
                        <option value="">-- Pilih Kelas --</option>
                        @foreach($kelasList as $kelas)
                            <option value="{{ $kelas->id }}">{{ $kelas->nama_kelas ?? ($kelas['nama_kelas'] ?? $kelas->nama ?? '') }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Judul Modul (opsional)</label>
                    <input type="text" name="title" class="form-control" placeholder="Judul modul baru" />
                </div>
                <div class="col-md-3">
                    <label class="form-label">File DOCX</label>
                    <input type="file" name="document" accept=".doc,.docx" class="form-control" />
                </div>
                <div class="col-12 col-md-3">
                    <button type="submit" class="btn btn-success w-100">
                        <i class="ti ti-upload me-1"></i> Upload & Buat Modul
                    </button>
                </div>
            </form>
        </div>

        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead class="table-light">
                    <tr>
                        <th>No</th>
                        <th>Judul Modul</th>
                        <th>Mata Pelajaran</th>
                        <th>Kelas</th>
                        <th>Status</th>
                        <th>Dokumen Tersimpan</th>
                        <th>Tanggal Update</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($modules as $module)
                    @php
                        $activeDocument = $module->document;
                        $docFile = $activeDocument?->filename ?: ($activeDocument?->filepath ? basename($activeDocument->filepath) : null);
                    @endphp
                    <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td>{{ $module->judul }}</td>
                        <td>{{ $module->mataPelajaran->nama_mapel ?? '-' }}</td>
                        <td>{{ $module->kelas->nama_kelas ?? '-' }}</td>
                        <td>
                            <span class="badge {{ $module->status === 'published' ? 'bg-success' : 'bg-secondary' }}">
                                {{ ucfirst($module->status) }}
                            </span>
                        </td>
                        <td>
                            @if($docFile)
                                <span class="badge bg-info text-white">{{ $docFile }}</span>
                            @else
                                <span class="text-muted">Belum ada DOCX</span>
                            @endif
                        </td>
                        <td>{{ $module->updated_at?->format('d/m/Y H:i') ?? '-' }}</td>
                        <td>
                            @if($module->isCreatedViaModulAjar())
                                <a href="{{ route('rencana_pembelajaran.edit', $module->id) }}" class="btn btn-sm btn-warning">
                                    <i class="ti ti-edit me-1"></i>Edit di Modul Ajar
                                </a>
                            @else
                                <a href="{{ route('akademik.editor_modul.edit', $module->id) }}" class="btn btn-sm btn-primary">
                                    <i class="ti ti-edit me-1"></i>Edit dengan Only Office
                                </a>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="text-center text-muted">Belum ada modul ajar.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection

