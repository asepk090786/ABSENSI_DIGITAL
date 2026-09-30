@extends('layouts.app')

@section('title', 'Asesmen Formatif')

@section('content')
<div class="page-header d-print-none">
    <div class="container-xl">
        <div class="row g-2 align-items-center">
            <div class="col">
                <h2 class="page-title">
                    <i class="ti ti-clipboard-list me-2"></i>Asesmen Formatif
                </h2>
                <div class="text-muted mt-1">Kelola daftar nilai tugas atau latihan siswa</div>
                @if($tahunAjaranAktif && $semesterAktif)
                    <div class="text-muted small mt-2">
                        <i class="ti ti-calendar me-1"></i>{{ $tahunAjaranAktif->tahun_ajaran }} - <strong>{{ $semesterAktif->nama_semester }}</strong>
                    </div>
                @endif
            </div>
            <div class="col-auto ms-auto d-print-none">
                <a href="{{ route('nilai.index', array_filter([
                    'kelas_id' => $selectedKelasId,
                    'mapel_id' => $selectedMapelId,
                ])) }}" class="btn btn-outline-secondary">
                    <i class="ti ti-arrow-left me-1"></i>Kembali
                </a>
            </div>
        </div>
    </div>
</div>
<div class="page-body">
    <div class="container-xl">
        <div class="card">
            <div class="card-header border-0 pt-3 pb-2">
                <h3 class="card-title">Daftar Nilai Tugas / Latihan</h3>
                <div class="card-actions">
                    <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#modalTambahFormatif">
                        <i class="ti ti-plus me-1"></i>Tambah Data
                    </button>
                </div>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-vcenter table-striped table-hover">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Kelas</th>
                                <th>Mata Pelajaran</th>
                                <th>Judul</th>
                                <th>Tanggal</th>
                                <th>Nilai</th>
                                <th>Keterangan</th>
                                <th class="text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($daftarTugas as $item)
                                <tr>
                                    <td>{{ $item->no }}</td>
                                    <td>{{ $item->kelas }}</td>
                                    <td>{{ $item->mapel }}</td>
                                    <td>{{ $item->judul }}</td>
                                    <td>{{ $item->tanggal_display ?? $item->tanggal ?? '-' }}</td>
                                    <td>{{ $item->nilai }}</td>
                                    <td>{{ $item->keterangan }}</td>
                                    <td class="text-center">
                                        <div class="d-flex justify-content-center gap-2">
                                            <a href="{{ route('nilai.index', array_filter([
                                                'kelas_id' => $item->kelas_id,
                                                'mapel_id' => $item->mapel_id,
                                                'tanggal_nilai' => $item->tanggal,
                                                'source' => 'asesmen-formatif',
                                                'view' => 1,
                                            ])) }}" class="btn btn-sm btn-info text-white action-view" title="Lihat" data-title="Lihat tugas / latihan">
                                                <i class="ti ti-eye"></i>
                                            </a>
                                            <a href="{{ route('nilai.index', array_filter([
                                                'kelas_id' => $item->kelas_id,
                                                'mapel_id' => $item->mapel_id,
                                                'tanggal_nilai' => $item->tanggal,
                                                'rencana_pembelajaran_id' => $item->rencana_pembelajaran_id,
                                                'komponen_id' => $item->komponen_id ?? 0,
                                                'assessment_uuid' => $item->assessment_uuid,
                                                'edit' => 1,
                                                'source' => 'asesmen-formatif',
                                            ])) }}" class="btn btn-sm btn-primary action-edit" title="Edit" data-title="Edit tugas / latihan">
                                                <i class="ti ti-edit"></i>
                                            </a>
                                            <form action="{{ route('nilai.penilaian.destroy') }}" method="POST" class="d-inline" onsubmit="return confirm('Hapus data penilaian ini?')">
                                                @csrf
                                                @method('DELETE')
                                                <input type="hidden" name="kelas_id" value="{{ $item->kelas_id }}">
                                                <input type="hidden" name="mapel_id" value="{{ $item->mapel_id }}">
                                                <input type="hidden" name="tanggal" value="{{ $item->tanggal }}">
                                                <input type="hidden" name="komponen_id" value="{{ $item->komponen_id ?? '' }}">
                                                <input type="hidden" name="assessment_uuid" value="{{ $item->assessment_uuid ?? '' }}">
                                                <input type="hidden" name="rencana_pembelajaran_id" value="{{ $item->rencana_pembelajaran_id ?? '' }}">
                                                <input type="hidden" name="return_to" value="asesmen-formatif">
                                                <button type="submit" class="btn btn-sm btn-danger" title="Hapus" data-title="Hapus tugas / latihan">
                                                    <i class="ti ti-trash"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="text-center text-muted py-4">
                                        Belum ada data tugas atau latihan untuk asesmen formatif.
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
@endsection

<div class="modal fade" id="modalTambahFormatif" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Tambah Data Nilai Harian</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form method="POST" action="{{ route('nilai.store') }}" id="formTambahFormatif">
                    @csrf
                    <input type="hidden" name="return_to" value="asesmen-formatif">
                    <input type="hidden" name="kategori_asesmen" value="formatif">

                    <div class="mb-3">
                        <label class="form-label">Tanggal</label>
                        <input type="date" class="form-control" name="tanggal" value="{{ now()->format('Y-m-d') }}" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Kelas</label>
                        <select class="form-select" name="kelas_id" id="formatifKelasSelect" required>
                            <option value="">Pilih Kelas</option>
                            @foreach($kelasOptions as $kelas)
                                <option value="{{ $kelas->id }}" {{ (string)($selectedKelasId ?? '') === (string)$kelas->id ? 'selected' : '' }}>
                                    {{ $kelas->nama_kelas }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Mata Pelajaran</label>
                        <select class="form-select" name="mapel_id" id="formatifMapelSelect" required>
                            <option value="">Pilih Mata Pelajaran</option>
                            @foreach($mapelOptions as $mapel)
                                <option value="{{ $mapel->id }}" {{ (string)($selectedMapelId ?? '') === (string)$mapel->id ? 'selected' : '' }}>
                                    {{ $mapel->nama_mapel }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Materi Pelajaran</label>
                        <input type="text" class="form-control" name="materi_pelajaran" placeholder="Contoh: Menjelaskan teks deskripsi / latihan soal bilangan" value="{{ old('materi_pelajaran') }}">
                        <small class="text-muted">Opsional. Jika kosong, sistem akan membuat penilaian harian umum.</small>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Jenis Penilaian</label>
                        <select class="form-select" name="jenis_penilaian">
                            <option value="">Harian / Umum</option>
                            @php
                                $jenisPenilaianList = [
                                    'Asesmen awal/diagnostik',
                                    'Observasi',
                                    'Tanya jawab',
                                    'Diskusi',
                                    'Kuis',
                                    'Latihan/tugas',
                                    'Praktik/unjuk kerja',
                                    'Penilaian diri',
                                    'Penilaian antarteman',
                                    'Refleksi',
                                    'Umpan balik',
                                    'Tindak lanjut (remedial/pengayaan)',
                                ];
                            @endphp
                            @foreach($jenisPenilaianList as $jenis)
                                <option value="{{ $jenis }}">{{ $jenis }}</option>
                            @endforeach
                        </select>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                <button type="submit" class="btn btn-primary" form="formTambahFormatif">Simpan Nilai</button>
            </div>
        </div>
    </div>
</div>



