@extends('layouts.app', ['pageSlug' => 'ekskul'])

@section('title', 'Bukti Kegiatan - ' . $ekskul->nama)

@section('content')
<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h4 class="card-title fw-semibold m-0">Bukti Kegiatan: {{ $ekskul->nama }}</h4>
                <a href="{{ route('ekskul.index') }}" class="btn btn-secondary btn-sm">Kembali</a>
            </div>
            <div class="card-body">
                @if(session('success'))
                    <div class="alert alert-success alert-dismissible">{{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif

                <button type="button" class="btn btn-primary mb-3" data-bs-toggle="collapse" data-bs-target="#formBukti">
                    <i class="ti ti-upload me-1"></i>Upload Bukti Baru
                </button>

                <div class="collapse mb-4" id="formBukti">
                    <div class="card card-body border">
                        <form method="POST" action="{{ route('ekskul.bukti.store', $ekskul->id) }}" enctype="multipart/form-data">
                            @csrf
                            <div class="mb-3">
                                <label class="form-label">Judul <span class="text-danger">*</span></label>
                                <input type="text" name="judul" class="form-control" required maxlength="200">
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Deskripsi</label>
                                <textarea name="deskripsi" class="form-control" rows="2"></textarea>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">File (Foto/Dokumen) <span class="text-danger">*</span></label>
                                <input type="file" name="file" class="form-control" required>
                                <div class="form-text">Maksimal 10MB.</div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Kaitkan dengan Agenda (Opsional)</label>
                                <select name="ekskul_agenda_id" class="form-select">
                                    <option value="">-- Pilih Agenda --</option>
                                    @foreach($ekskul->agenda as $ag)
                                        <option value="{{ $ag->id }}">{{ $ag->judul }} ({{ \Carbon\Carbon::parse($ag->tanggal)->format('d/m/Y') }})</option>
                                    @endforeach
                                </select>
                            </div>
                            <button type="submit" class="btn btn-primary"><i class="ti ti-upload me-1"></i>Upload</button>
                        </form>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table table-vcenter table-hover">
                        <thead>
                            <tr><th>No</th><th>Judul</th><th>Deskripsi</th><th>File</th><th>Agenda</th><th>Diupload</th><th>Status</th></tr>
                        </thead>
                        <tbody>
                        @forelse($bukti as $index => $b)
                            <tr>
                                <td>{{ $index + 1 }}</td>
                                <td class="fw-semibold">{{ $b->judul }}</td>
                                <td>{{ Str::limit($b->deskripsi, 50) }}</td>
                                <td>
                                    <a href="{{ asset('storage/' . $b->file_path) }}" target="_blank" class="btn btn-sm btn-outline-primary">
                                        <i class="ti ti-download"></i> Lihat
                                    </a>
                                </td>
                                <td>{{ $b->ekskulAgenda->judul ?? '-' }}</td>
                                <td>{{ $b->diuploadOleh->nama ?? '-' }}<br><small class="text-muted">{{ $b->created_at->format('d/m/Y') }}</small></td>
                                <td>
                                    @if($b->verified_at)
                                        <span class="badge bg-success">Terverifikasi</span>
                                    @else
                                        <span class="badge bg-warning">Menunggu</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="7" class="text-center text-muted py-4">Belum ada bukti kegiatan.</td></tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection