@extends('layouts.app', ['pageSlug' => 'wali-kelas'])

@section('title', 'Laporan Guru ke Wali Kelas')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h3 class="card-title mb-0">Laporan Guru - {{ $kelasBinaan->nama_kelas ?? '-' }}</h3>
                    <a href="{{ route('wali_kelas.index') }}" class="btn btn-secondary btn-sm">
                        <i class="ti ti-arrow-left"></i> Kembali
                    </a>
                </div>
                <div class="card-body">
                    @if(session('success'))
                        <div class="alert alert-success">
                            <i class="ti ti-check me-1"></i>{{ session('success') }}
                        </div>
                    @endif

                    <form action="{{ route('wali_kelas.laporan_guru.store') }}" method="POST" class="mb-4">
                        @csrf
                        <div class="row g-2 align-items-end">
                            <div class="col-12 col-md-4">
                                <label class="form-label">Siswa</label>
                                <select name="siswa_id" class="form-select @error('siswa_id') is-invalid @enderror" required>
                                    <option value="">Pilih Siswa</option>
                                    @foreach(($siswaList ?? collect()) as $siswa)
                                        <option value="{{ $siswa->id }}" {{ old('siswa_id') == $siswa->id ? 'selected' : '' }}>
                                            {{ $siswa->nama }} ({{ $siswa->nis ?: '-' }})
                                        </option>
                                    @endforeach
                                </select>
                                @error('siswa_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-12 col-md-6">
                                <label class="form-label">Laporan untuk Guru BK</label>
                                <textarea name="deskripsi_permasalahan" class="form-control @error('deskripsi_permasalahan') is-invalid @enderror" rows="2" required placeholder="Tuliskan kondisi siswa yang perlu ditindaklanjuti BK...">{{ old('deskripsi_permasalahan') }}</textarea>
                                @error('deskripsi_permasalahan')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-12 col-md-2">
                                <button type="submit" class="btn btn-primary w-100">
                                    <i class="ti ti-send me-1"></i>Kirim
                                </button>
                            </div>
                        </div>
                    </form>

                    @if($laporanGuru->isEmpty())
                        <div class="alert alert-info mb-0">
                            <i class="ti ti-info-circle me-2"></i>Belum ada laporan dari guru untuk kelas ini.
                        </div>
                    @else
                        <div class="table-responsive">
                            <table class="table table-striped table-hover">
                                <thead>
                                    <tr>
                                        <th>Tanggal</th>
                                        <th>Siswa</th>
                                        <th>Sumber</th>
                                        <th>Guru Pelapor</th>
                                        <th>Permasalahan</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($laporanGuru as $laporan)
                                        <tr>
                                            <td>{{ \Carbon\Carbon::parse($laporan->created_at)->format('d/m/Y H:i') }}</td>
                                            <td>{{ $laporan->nama_siswa ?? '-' }}<br><small class="text-muted">NIS: {{ $laporan->nis_siswa ?? '-' }}</small></td>
                                            <td>
                                                @if((int) ($laporan->is_laporan_wali ?? 0) === 1)
                                                    <span class="badge bg-primary">Wali Kelas</span>
                                                @else
                                                    <span class="badge bg-secondary">Guru Mapel</span>
                                                @endif
                                            </td>
                                            <td>{{ $laporan->nama_guru_pelapor ?? '-' }}</td>
                                            <td>{{ $laporan->deskripsi_permasalahan }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
