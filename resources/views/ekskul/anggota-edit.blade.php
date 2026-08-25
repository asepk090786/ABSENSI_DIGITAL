@extends('layouts.app', ['pageSlug' => 'ekskul'])

@section('title', 'Edit Anggota - ' . $ekskul->nama)

@section('content')
<div class="row">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h4 class="card-title fw-semibold m-0">Edit Anggota: {{ $anggota->siswa->nama ?? '-' }}</h4>
                <a href="{{ route('ekskul.anggota', $ekskul->id) }}" class="btn btn-secondary btn-sm">Kembali</a>
            </div>
            <form method="POST" action="{{ route('ekskul.anggota.update', [$ekskul->id, $anggota->id]) }}">
                @csrf
                @method('PUT')
                <div class="card-body">
                    @if($errors->any())
                        <div class="alert alert-danger">
                            <ul class="mb-0">
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif
                    <div class="mb-3">
                        <label class="form-label">Siswa</label>
                        <input type="text" class="form-control" value="{{ ($anggota->siswa->nis ?? '-') . ' - ' . ($anggota->siswa->nama ?? '-') }}" disabled>
                    </div>
                    <div class="mb-3">
                        <label for="status_pendaftaran" class="form-label">Status</label>
                        <select name="status_pendaftaran" id="status_pendaftaran" class="form-select" required>
                            @foreach(['pending' => 'Pending', 'diterima' => 'Diterima', 'ditolak' => 'Ditolak'] as $value => $label)
                                <option value="{{ $value }}" @selected(old('status_pendaftaran', $anggota->status_pendaftaran) === $value)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="tanggal_daftar" class="form-label">Tanggal Daftar</label>
                        <input type="date" name="tanggal_daftar" id="tanggal_daftar" class="form-control" value="{{ old('tanggal_daftar', optional($anggota->tanggal_daftar)->format('Y-m-d')) }}">
                    </div>
                    <div class="mb-0">
                        <label for="keterangan" class="form-label">Keterangan</label>
                        <textarea name="keterangan" id="keterangan" class="form-control" rows="4" maxlength="1000">{{ old('keterangan', $anggota->keterangan) }}</textarea>
                    </div>
                </div>
                <div class="card-footer d-flex gap-2">
                    <button type="submit" class="btn btn-primary"><i class="ti ti-device-floppy me-1"></i>Simpan</button>
                    <a href="{{ route('ekskul.anggota', $ekskul->id) }}" class="btn btn-light">Batal</a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
