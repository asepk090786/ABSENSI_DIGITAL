@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h3 class="card-title">Detail Tugas Guru</h3>
                    <div>
                        <a href="{{ route('tugas_guru.edit', $tugas_guru->id) }}" class="btn btn-warning">
                            <i class="ti ti-edit"></i> Edit
                        </a>
                        <a href="{{ route('tugas_guru.index') }}" class="btn btn-secondary">
                            <i class="ti ti-arrow-left"></i> Kembali
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <table class="table table-bordered">
                                <tr>
                                    <th width="40%">Nama Guru</th>
                                    <td>{{ $tugas_guru->guru->user->name ?? $tugas_guru->guru->nama }}</td>
                                </tr>
                                <tr>
                                    <th>NIP</th>
                                    <td>{{ $tugas_guru->guru->nip ?? '-' }}</td>
                                </tr>
                                <tr>
                                    <th>Email</th>
                                    <td>{{ $tugas_guru->guru->email ?? '-' }}</td>
                                </tr>
                                <tr>
                                    <th>Telepon</th>
                                    <td>{{ $tugas_guru->guru->telepon ?? '-' }}</td>
                                </tr>
                            </table>
                        </div>

                        <div class="col-md-6">
                            <table class="table table-bordered">
                                <tr>
                                    <th width="40%">Mata Pelajaran</th>
                                    <td>{{ $tugas_guru->mataPelajaran->nama_mapel ?? '-' }}</td>
                                </tr>
                                <tr>
                                    <th>Kode Mapel</th>
                                    <td>{{ $tugas_guru->mataPelajaran->kode_mapel ?? '-' }}</td>
                                </tr>
                                <tr>
                                    <th>Tingkat Kelas</th>
                                    <td><span class="badge bg-primary">{{ $tugas_guru->tingkat_kelas }}</span></td>
                                </tr>
                                <tr>
                                    <th>Kelas</th>
                                    <td>{{ $tugas_guru->kelas->nama_kelas ?? 'Semua kelas tingkat ' . $tugas_guru->tingkat_kelas }}</td>
                                </tr>
                                <tr>
                                    <th>Status</th>
                                    <td>
                                        @if($tugas_guru->is_active)
                                            <span class="badge bg-success">Aktif</span>
                                        @else
                                            <span class="badge bg-secondary">Tidak Aktif</span>
                                        @endif
                                    </td>
                                </tr>
                            </table>
                        </div>
                    </div>

                    @if($tugas_guru->keterangan)
                    <div class="row mt-2">
                        <div class="col-12">
                            <div class="card bg-light">
                                <div class="card-body">
                                    <h5 class="card-title">Keterangan</h5>
                                    <p class="card-text">{{ $tugas_guru->keterangan }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endif

                    <div class="row mt-2">
                        <div class="col-12">
                            <table class="table table-sm">
                                <tr>
                                    <th width="20%">Dibuat pada</th>
                                    <td>{{ $tugas_guru->created_at->format('d/m/Y H:i:s') }}</td>
                                </tr>
                                <tr>
                                    <th>Terakhir diupdate</th>
                                    <td>{{ $tugas_guru->updated_at->format('d/m/Y H:i:s') }}</td>
                                </tr>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
