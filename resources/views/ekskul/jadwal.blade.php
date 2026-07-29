@extends('layouts.app', ['pageSlug' => 'ekskul'])

@section('title', 'Jadwal - ' . $ekskul->nama)

@section('content')
<div class="row">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h4 class="card-title fw-semibold m-0">Tambah Jadwal {{ $ekskul->nama }}</h4>
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('ekskul.jadwal.store', $ekskul->id) }}">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label">Hari <span class="text-danger">*</span></label>
                        <select name="hari" class="form-select" required>
                            <option value="">Pilih Hari</option>
                            @foreach(['Senin','Selasa','Rabu','Kamis','Jumat','Sabtu','Minggu'] as $hari)
                                <option value="{{ $hari }}">{{ $hari }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Jam Mulai <span class="text-danger">*</span></label>
                            <input type="time" name="jam_mulai" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Jam Selesai <span class="text-danger">*</span></label>
                            <input type="time" name="jam_selesai" class="form-control" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Lokasi</label>
                        <input type="text" name="lokasi" class="form-control" maxlength="200">
                    </div>
                    <button type="submit" class="btn btn-primary"><i class="ti ti-plus me-1"></i>Tambah</button>
                </form>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h4 class="card-title fw-semibold m-0">Daftar Jadwal</h4>
                <a href="{{ route('ekskul.index') }}" class="btn btn-secondary btn-sm">Kembali</a>
            </div>
            <div class="card-body">
                @if(session('success'))
                    <div class="alert alert-success alert-dismissible">{{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif
                <div class="table-responsive">
                    <table class="table table-vcenter">
                        <thead>
                            <tr><th>Hari</th><th>Jam</th><th>Lokasi</th><th>Aksi</th></tr>
                        </thead>
                        <tbody>
                        @forelse($jadwal as $j)
                            <tr>
                                <td>{{ $j->hari }}</td>
                                <td>{{ substr($j->jam_mulai, 0, 5) }} - {{ substr($j->jam_selesai, 0, 5) }}</td>
                                <td>{{ $j->lokasi ?? '-' }}</td>
                                <td>
                                    <form method="POST" action="{{ route('ekskul.jadwal.delete', [$ekskul->id, $j->id]) }}" class="d-inline" onsubmit="return confirm('Yakin hapus?')">
                                        @csrf @method('DELETE')
                                        <button class="btn btn-danger btn-sm"><i class="ti ti-trash"></i></button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="4" class="text-center text-muted">Belum ada jadwal.</td></tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection