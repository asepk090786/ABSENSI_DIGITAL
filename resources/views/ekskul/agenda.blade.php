@extends('layouts.app', ['pageSlug' => 'ekskul'])

@section('title', 'Agenda - ' . $ekskul->nama)

@section('content')
<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h4 class="card-title fw-semibold m-0">Agenda Kegiatan: {{ $ekskul->nama }}</h4>
                <a href="{{ route('ekskul.index') }}" class="btn btn-secondary btn-sm">Kembali</a>
            </div>
            <div class="card-body">
                @if(session('success'))
                    <div class="alert alert-success alert-dismissible">{{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif

                <button type="button" class="btn btn-primary mb-3" data-bs-toggle="collapse" data-bs-target="#formAgenda">
                    <i class="ti ti-plus me-1"></i>Tambah Agenda Baru
                </button>

                <div class="collapse mb-4" id="formAgenda">
                    <div class="card card-body border">
                        <form method="POST" action="{{ route('ekskul.agenda.store', $ekskul->id) }}">
                            @csrf
                            <div class="row">
                                <div class="col-md-8 mb-3">
                                    <label class="form-label">Judul <span class="text-danger">*</span></label>
                                    <input type="text" name="judul" class="form-control" required maxlength="200">
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label class="form-label">Jenis <span class="text-danger">*</span></label>
                                    <select name="jenis" class="form-select" required>
                                        <option value="rutin">Rutin</option>
                                        <option value="khusus">Khusus</option>
                                    </select>
                                </div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Deskripsi</label>
                                <textarea name="deskripsi" class="form-control" rows="2"></textarea>
                            </div>
                            <div class="row mb-3">
                                <div class="col-md-4">
                                    <label class="form-label">Tanggal <span class="text-danger">*</span></label>
                                    <input type="date" name="tanggal" class="form-control" required>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Jam Mulai <span class="text-danger">*</span></label>
                                    <input type="time" name="jam_mulai" class="form-control" required>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Jam Selesai <span class="text-danger">*</span></label>
                                    <input type="time" name="jam_selesai" class="form-control" required>
                                </div>
                                <div class="col-md-2">
                                    <label class="form-label">Lokasi</label>
                                    <input type="text" name="lokasi" class="form-control" maxlength="200">
                                </div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Materi / Rencana Kegiatan</label>
                                <textarea name="materi" class="form-control" rows="3"></textarea>
                            </div>
                            <button type="submit" class="btn btn-primary"><i class="ti ti-device-floppy me-1"></i>Simpan</button>
                        </form>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table table-vcenter table-hover">
                        <thead>
                            <tr><th>No</th><th>Judul</th><th>Tanggal</th><th>Jam</th><th>Jenis</th><th>Status</th><th>Pembuat</th></tr>
                        </thead>
                        <tbody>
                        @forelse($agenda as $index => $a)
                            <tr>
                                <td>{{ $index + 1 }}</td>
                                <td class="fw-semibold">{{ $a->judul }}</td>
                                <td>{{ \Carbon\Carbon::parse($a->tanggal)->format('d/m/Y') }}</td>
                                <td>{{ substr($a->jam_mulai, 0, 5) }} - {{ substr($a->jam_selesai, 0, 5) }}</td>
                                <td><span class="badge bg-{{ $a->jenis === 'khusus' ? 'warning' : 'info' }}">{{ ucfirst($a->jenis) }}</span></td>
                                <td>
                                    @if($a->status === 'terlaksana')
                                        <span class="badge bg-success">Terlaksana</span>
                                    @elseif($a->status === 'dibatalkan')
                                        <span class="badge bg-danger">Dibatalkan</span>
                                    @else
                                        <span class="badge bg-secondary">Direncanakan</span>
                                    @endif
                                </td>
                                <td>{{ $a->dibuatOleh->nama ?? '-' }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="7" class="text-center text-muted py-4">Belum ada agenda.</td></tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection