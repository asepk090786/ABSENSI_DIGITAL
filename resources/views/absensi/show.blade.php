@extends('layouts.app')

@section('title', 'Detail Absensi Kelas')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <h3 class="card-title mb-0">Detail Absensi Kelas</h3>
                        <div>
                            <a href="{{ route('absensi.edit', $absensi->id) }}" class="btn btn-warning">
                                <i class="ti ti-edit me-1"></i> Edit
                            </a>
                            <a href="{{ route('absensi.index') }}" class="btn btn-secondary">
                                <i class="ti ti-arrow-left me-1"></i> Kembali
                            </a>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <table class="table table-borderless">
                                <tr>
                                    <th width="180">Tanggal</th>
                                    <td>: {{ $absensi->tanggal->format('d/m/Y') }}</td>
                                </tr>
                                <tr>
                                    <th>Kelas</th>
                                    <td>: {{ $absensi->kelas->nama_kelas ?? '-' }}</td>
                                </tr>
                                <tr>
                                    <th>Guru</th>
                                    <td>: {{ $absensi->guru->nama ?? '-' }} ({{ $absensi->guru->kode_guru ?? '-' }})</td>
                                </tr>
                                <tr>
                                    <th>Jam Belajar</th>
                                    <td>: Jam ke-{{ $absensi->jamBelajar->urutan ?? '-' }} ({{ $absensi->jamBelajar->jam_mulai ?? '-' }} - {{ $absensi->jamBelajar->jam_selesai ?? '-' }})</td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <table class="table table-borderless">
                                <tr>
                                    <th width="180">Status Kelas</th>
                                    <td>: {{ $absensi->status_kelas ?? '-' }}</td>
                                </tr>
                                <tr>
                                    <th>Tahun Ajaran</th>
                                    <td>: {{ $absensi->tahunAjaran->nama_tahun ?? '-' }}</td>
                                </tr>
                                <tr>
                                    <th>Semester</th>
                                    <td>: {{ $absensi->semester->nama_semester ?? '-' }}</td>
                                </tr>
                                <tr>
                                    <th>Jumlah Siswa</th>
                                    <td>: <span class="badge bg-info">{{ $absensi->absensiSiswa->count() }} Siswa</span></td>
                                </tr>
                            </table>
                        </div>
                    </div>

                    @if($absensi->absensiSiswa->isNotEmpty())
                    <hr>
                    <h5 class="mb-3">Daftar Absensi Siswa</h5>
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped">
                            <thead class="table-light">
                                <tr>
                                    <th>No</th>
                                    <th>NIS</th>
                                    <th>Nama Siswa</th>
                                    <th>Status</th>
                                    <th>Keterangan</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($absensi->absensiSiswa as $index => $abs)
                                <tr>
                                    <td>{{ $index + 1 }}</td>
                                    <td>{{ $abs->siswa->nis ?? '-' }}</td>
                                    <td>{{ $abs->siswa->nama ?? '-' }}</td>
                                    <td>
                                        @if($abs->status == 'hadir')
                                            <span class="badge bg-success">Hadir</span>
                                        @elseif($abs->status == 'sakit')
                                            <span class="badge bg-warning">Sakit</span>
                                        @elseif($abs->status == 'izin')
                                            <span class="badge bg-info">Izin</span>
                                        @elseif($abs->status == 'alpha')
                                            <span class="badge bg-danger">Alpha</span>
                                        @else
                                            <span class="badge bg-secondary">{{ $abs->status }}</span>
                                        @endif
                                    </td>
                                    <td>{{ $abs->keterangan ?? '-' }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    @else
                    <div class="alert alert-info">
                        <i class="ti ti-info-circle me-2"></i>
                        Belum ada data absensi siswa untuk kelas ini.
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
