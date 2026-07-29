@extends('layouts.app', ['pageSlug' => 'ekskul'])

@section('title', 'Kelola Anggota - ' . $ekskul->nama)

@section('content')
<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h4 class="card-title fw-semibold m-0">Kelola Anggota: {{ $ekskul->nama }}</h4>
                <a href="{{ route('ekskul.index') }}" class="btn btn-secondary btn-sm">Kembali</a>
            </div>
            <div class="card-body">
                @if(session('success'))
                    <div class="alert alert-success alert-dismissible">{{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif
                @if(session('error'))
                    <div class="alert alert-danger alert-dismissible">{{ session('error') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif
                <div class="table-responsive">
                    <table class="table table-vcenter table-hover">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>NIS</th>
                                <th>Nama Siswa</th>
                                <th>Kelas</th>
                                <th>Tgl Daftar</th>
                                <th>Status</th>
                                <th width="180">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                        @forelse($anggota as $index => $a)
                            <tr>
                                <td>{{ $index + 1 }}</td>
                                <td>{{ $a->siswa->nis ?? '-' }}</td>
                                <td>{{ $a->siswa->nama ?? '-' }}</td>
                                <td>{{ $a->siswa->kelas->nama_kelas ?? '-' }}</td>
                                <td>{{ $a->tanggal_daftar ? \Carbon\Carbon::parse($a->tanggal_daftar)->format('d/m/Y') : '-' }}</td>
                                <td>
                                    @if($a->status_pendaftaran === 'diterima')
                                        <span class="badge bg-success">Diterima</span>
                                    @elseif($a->status_pendaftaran === 'ditolak')
                                        <span class="badge bg-danger">Ditolak</span>
                                    @else
                                        <span class="badge bg-warning">Pending</span>
                                    @endif
                                </td>
                                <td>
                                    @if($a->status_pendaftaran === 'pending')
                                    <form method="POST" action="{{ route('ekskul.anggota.status', $ekskul->id) }}" class="d-inline">
                                        @csrf
                                        <input type="hidden" name="siswa_id" value="{{ $a->siswa_id }}">
                                        <button type="submit" name="status" value="diterima" class="btn btn-success btn-sm">
                                            <i class="ti ti-check"></i> Terima
                                        </button>
                                        <button type="submit" name="status" value="ditolak" class="btn btn-danger btn-sm">
                                            <i class="ti ti-x"></i> Tolak
                                        </button>
                                    </form>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="7" class="text-center text-muted py-4">Belum ada pendaftar.</td></tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection