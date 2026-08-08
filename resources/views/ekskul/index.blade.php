@extends('layouts.app', ['pageSlug' => 'ekskul'])

@section('title', 'Data Ekstrakurikuler')

@section('content')
<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header">
                <div class="row align-items-center">
                    <div class="col">
                        <h4 class="card-title fw-semibold m-0">Data Ekstrakurikuler</h4>
                    </div>
                    <div class="col-auto">
                        @if(auth()->user()->hasRole('Admin'))
                        <a href="{{ route('ekskul.create') }}" class="btn btn-primary btn-sm">
                            <i class="ti ti-plus me-1"></i>Tambah Baru
                        </a>
                        @endif
                    </div>
                </div>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-vcenter table-hover">
                        <thead>
                            <tr>
                                <th width="50">No</th>
                                <th>Nama</th>
                                <th>Pembina</th>
                                <th class="text-center">Anggota</th>
                                <th class="text-center">Jadwal</th>
                                <th>Status</th>
                                <th width="320">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                        @forelse($items as $index => $item)
                            <tr>
                                <td>{{ $index + 1 }}</td>
                                <td class="fw-semibold">{{ $item->nama }}</td>
                                <td>{{ $item->guru->nama ?? '-' }}</td>
                                <td class="text-center">
                                    <span class="badge bg-primary-light text-primary">
                                        {{ $item->anggota_diterima_count ?? 0 }} Anggota
                                    </span>
                                </td>
                                <td class="text-center">
                                    <span class="badge bg-accent-light text-accent">
                                        {{ $item->jadwal_count ?? 0 }} Jadwal
                                    </span>
                                </td>
                                <td>
                                    @if($item->status === 'aktif')
                                        <span class="badge bg-success">Aktif</span>
                                    @else
                                        <span class="badge bg-secondary">Nonaktif</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="btn-group btn-group-sm">
                                        @if(auth()->user()->hasRole('Admin'))
                                        <a href="{{ route('ekskul.edit', $item->id) }}" class="btn btn-outline-primary" title="Edit">
                                            <i class="ti ti-edit"></i>
                                        </a>
                                        @endif
                                        <a href="{{ route('ekskul.anggota', $item->id) }}" class="btn btn-outline-info" title="Kelola Anggota">
                                            <i class="ti ti-users"></i>
                                        </a>
                                        <a href="{{ route('ekskul.jadwal', $item->id) }}" class="btn btn-outline-warning" title="Jadwal">
                                            <i class="ti ti-calendar"></i>
                                        </a>
                                        <a href="{{ route('ekskul.agenda', $item->id) }}" class="btn btn-outline-secondary" title="Agenda">
                                            <i class="ti ti-list-check"></i>
                                        </a>
                                        <a href="{{ route('ekskul.absensi', $item->id) }}" class="btn btn-outline-success" title="Absensi">
                                            <i class="ti ti-clipboard-check"></i>
                                        </a>
                                        <a href="{{ route('ekskul.bukti', $item->id) }}" class="btn btn-outline-dark" title="Bukti Kegiatan">
                                            <i class="ti ti-photo"></i>
                                        </a>
                                        @if(auth()->user()->hasRole('Admin') || auth()->user()->hasAnyRole(['Pembina','Guru']))
                                        <button type="button" class="btn btn-outline-info" title="Izin Kegiatan" data-bs-toggle="modal" data-bs-target="#modalIzinKegiatan{{ $item->id }}">
                                            <i class="ti ti-file-description"></i>
                                        </button>
                                        @endif
                                        <a href="{{ route('ekskul.rekap', $item->id) }}" class="btn btn-outline-purple" title="Rekap">
                                            <i class="ti ti-report-analytics"></i>
                                        </a>
                                        @if(auth()->user()->hasRole('Admin'))
                                        <form method="POST" action="{{ route('ekskul.destroy', $item->id) }}" class="d-inline" onsubmit="return confirm('Yakin hapus ekstrakurikuler ini?')">
                                            @csrf @method('DELETE')
                                            <button class="btn btn-outline-danger" title="Hapus">
                                                <i class="ti ti-trash"></i>
                                            </button>
                                        </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center text-muted py-4">
                                    <i class="ti ti-info-circle me-2"></i>Belum ada data ekstrakurikuler.
                                </td>
                            </tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@foreach($items as $item)
    @if(auth()->user()->hasRole('Admin') || auth()->user()->hasAnyRole(['Pembina','Guru']))
    <div class="modal fade" id="modalIzinKegiatan{{ $item->id }}" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <form method="POST" action="{{ route('ekskul.izin-kegiatan.store', $item->id) }}" enctype="multipart/form-data">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title">Tambah Izin Kegiatan / Dispensasi</h5>
                        <button type="button" class="close" data-bs-dismiss="modal"><span aria-hidden="true">&times;</span></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Pilih Siswa <span class="text-danger">*</span></label>
                            <div class="border rounded p-2" style="max-height:220px;overflow-y:auto;">
                                @foreach($item->anggota as $anggota)
                                    @if($anggota->status_pendaftaran === 'diterima' && $anggota->siswa)
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" name="siswa_ids[]" value="{{ $anggota->siswa->id }}" id="izin_siswa_{{ $item->id }}_{{ $anggota->siswa->id }}">
                                            <label class="form-check-label" for="izin_siswa_{{ $item->id }}_{{ $anggota->siswa->id }}">
                                                {{ $anggota->siswa->nama ?? '-' }}
                                            </label>
                                        </div>
                                    @endif
                                @endforeach
                            </div>
                        </div>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Jenis Kegiatan <span class="text-danger">*</span></label>
                                <select name="jenis_kegiatan" class="form-select" required>
                                    <option value="internal">Kegiatan Internal</option>
                                    <option value="external">Kegiatan Eksternal</option>
                                    <option value="dispensasi">Dispensasi</option>
                                    <option value="keterangan">Keterangan Kegiatan</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Keterangan</label>
                                <input type="text" name="keterangan_kegiatan" class="form-control" placeholder="Opsional">
                            </div>
                        </div>
                        <div class="row g-3 mt-1">
                            <div class="col-md-6">
                                <label class="form-label">Tanggal Mulai <span class="text-danger">*</span></label>
                                <input type="date" name="tanggal_mulai" class="form-control" value="{{ now()->toDateString() }}" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Tanggal Selesai <span class="text-danger">*</span></label>
                                <input type="date" name="tanggal_selesai" class="form-control" value="{{ now()->toDateString() }}" required>
                            </div>
                        </div>
                        <div class="mt-3">
                            <label class="form-label">Surat Tugas / Dispensasi</label>
                            <input type="file" name="surat_tugas" class="form-control" accept=".pdf,.doc,.docx,.jpg,.jpeg,.png">
                            <div class="form-text">Opsional: Upload surat tugas atau dispensasi (PDF, DOC, DOCX, JPG, PNG, maks 2MB).</div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary">Simpan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    @endif
@endforeach
@endsection