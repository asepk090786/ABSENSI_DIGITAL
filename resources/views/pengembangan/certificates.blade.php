@extends('layouts.app')

@section('title','Sertifikat Saya')

@section('content')
    <div class="container-fluid">
        <div class="row justify-content-center">
            <div class="col-lg-10">
                <div class="card mb-3">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h3 class="card-title fw-semibold m-0">Sertifikat Pengembangan Diri</h3>
                    </div>
                    <div class="card-body">
                        @if($certs->isEmpty())
                            <div class="text-center text-muted py-4">
                                <i class="ti ti-certificate-off fs-1 d-block mb-2"></i>
                                Belum ada sertifikat Pengembangan Diri untuk akun Anda.
                            </div>
                        @else
                            <div class="table-responsive">
                                <table class="table table-striped table-hover align-middle mb-0">
                                    <thead>
                                        <tr>
                                            <th style="width:40px">No</th>
                                            <th>Nama Kegiatan</th>
                                            <th>Jenis Kegiatan</th>
                                            <th>Tanggal Pelaksanaan</th>
                                            <th>Tema Kegiatan</th>
                                            <th>Status Sertifikat</th>
                                            <th style="width:240px">Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($certs as $idx => $c)
                                            <tr>
                                                <td>{{ $idx + 1 }}</td>
                                                <td>{{ optional($c->pengembangan)->nama_kegiatan ?? '-' }}</td>
                                                <td>{{ optional($c->pengembangan)->jenis_kegiatan ? (\App\Models\JenisKegiatan::where('kode', optional($c->pengembangan)->jenis_kegiatan)->value('nama') ?? optional($c->pengembangan)->jenis_kegiatan) : '-' }}</td>
                                                <td>
                                                    @if(optional($c->pengembangan)->tanggal_mulai)
                                                        {{ optional($c->pengembangan)->tanggal_mulai->format('d-m-Y') }}
                                                        @if(optional($c->pengembangan)->tanggal_selesai && optional($c->pengembangan)->tanggal_selesai != optional($c->pengembangan)->tanggal_mulai)
                                                            - {{ optional($c->pengembangan)->tanggal_selesai->format('d-m-Y') }}
                                                        @endif
                                                    @else
                                                        -
                                                    @endif
                                                </td>
                                                <td>{{ optional($c->pengembangan)->tema_kegiatan ?? '-' }}</td>
                                                <td>
                                                    @if(!$c->is_visible)
                                                        <span class="badge bg-warning text-dark">On proses</span>
                                                    @elseif(empty($c->file_path))
                                                        <span class="badge bg-secondary text-white">Belum tersedia</span>
                                                    @else
                                                        <span class="badge bg-success text-white">Tersedia</span>
                                                    @endif
                                                </td>
                                                <td>
                                                    @if($c->is_visible && !empty($c->file_path))
                                                        <a href="{{ route('pengembangan.certificates.preview', $c->id) }}" target="_blank" class="btn btn-sm btn-outline-secondary">Preview</a>
                                                        <a href="{{ route('pengembangan.certificates.download', $c->id) }}" class="btn btn-sm btn-outline-primary">Download</a>
                                                        <a href="{{ route('pengembangan.verify', $c->barcode) }}" target="_blank" class="btn btn-sm btn-outline-info">Public Link</a>
                                                    @else
                                                        <span class="text-muted">Tidak dapat diakses</span>
                                                    @endif
                                                </td>
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
