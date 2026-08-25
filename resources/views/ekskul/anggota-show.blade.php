@extends('layouts.app', ['pageSlug' => 'ekskul'])

@section('title', 'Detail Anggota - ' . $ekskul->nama)

@section('content')
<div class="row">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h4 class="card-title fw-semibold m-0">Detail Anggota</h4>
                <a href="{{ route('ekskul.anggota', $ekskul->id) }}" class="btn btn-secondary btn-sm">Kembali</a>
            </div>
            <div class="card-body">
                <dl class="row mb-0">
                    <dt class="col-sm-4">Ekstrakurikuler</dt>
                    <dd class="col-sm-8">{{ $ekskul->nama }}</dd>
                    <dt class="col-sm-4">NIS</dt>
                    <dd class="col-sm-8">{{ $anggota->siswa->nis ?? '-' }}</dd>
                    <dt class="col-sm-4">Nama Siswa</dt>
                    <dd class="col-sm-8">{{ $anggota->siswa->nama ?? '-' }}</dd>
                    <dt class="col-sm-4">Kelas</dt>
                    <dd class="col-sm-8">{{ $anggota->siswa->kelas->nama_kelas ?? '-' }}</dd>
                    <dt class="col-sm-4">Tanggal Daftar</dt>
                    <dd class="col-sm-8">{{ $anggota->tanggal_daftar ? $anggota->tanggal_daftar->format('d/m/Y') : '-' }}</dd>
                    <dt class="col-sm-4">Status</dt>
                    <dd class="col-sm-8">{{ ucfirst($anggota->status_pendaftaran) }}</dd>
                    <dt class="col-sm-4">Keterangan</dt>
                    <dd class="col-sm-8">{{ $anggota->keterangan ?: '-' }}</dd>
                </dl>
            </div>
            <div class="card-footer d-flex gap-2">
                <a href="{{ route('ekskul.anggota.edit', [$ekskul->id, $anggota->id]) }}" class="btn btn-primary btn-sm">
                    <i class="ti ti-edit me-1"></i>Edit
                </a>
                <form method="POST" action="{{ route('ekskul.anggota.destroy', [$ekskul->id, $anggota->id]) }}" onsubmit="return confirm('Yakin hapus anggota ini dari ekstrakurikuler?')">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-outline-danger btn-sm"><i class="ti ti-trash me-1"></i>Hapus</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
