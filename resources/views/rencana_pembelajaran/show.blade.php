@extends('layouts.app', ['pageSlug' => 'rencana_pembelajaran'])

@section('title', 'Detail Rencana Pembelajaran')

@section('content')
<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header border-0 pt-3 pb-2">
                <div class="row align-items-center">
                    <div class="col">
                        <h4 class="card-title fw-semibold m-0">{{ $item->judul }}</h4>
                    </div>
                    <div class="col-auto">
                        <div >
                            <a href="{{ route('rencana_pembelajaran.edit', $item->id) }}" class="btn btn-primary btn-sm">
                                <i class="ti ti-edit me-1"></i>Edit
                            </a>
                            @if($item->html_content)
                                <button type="button" class="btn btn-info btn-sm" data-bs-toggle="collapse" data-bs-target="#previewDocContent">
                                    <i class="ti ti-eye me-1"></i>Preview Dokumen
                                </button>
                            @endif
                            <a href="{{ route('rencana_pembelajaran.index', ['mata_pelajaran_id' => $item->mata_pelajaran_id, 'tingkat' => $item->kelas->tingkat_kelas]) }}" class="btn btn-secondary btn-sm">
                                <i class="ti ti-arrow-left me-1"></i>Kembali
                            </a>
                        </div>
                    </div>
                </div>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6 mb-2">
                        <label class="form-label text-muted">Mata Pelajaran</label>
                        <p class="form-control-plaintext">{{ $item->mataPelajaran->nama_mapel }}</p>
                    </div>
                    <div class="col-md-6 mb-2">
                        <label class="form-label text-muted">Kelas</label>
                        <p class="form-control-plaintext">{{ $item->kelas->nama_kelas }}</p>
                    </div>

                    <div class="col-md-6 mb-2">
                        <label class="form-label text-muted">Status</label>
                        <p class="form-control-plaintext">
                            <span class="badge bg-{{ $item->status === 'published' ? 'success' : 'warning' }}">
                                {{ ucfirst($item->status) }}
                            </span>
                        </p>
                    </div>

                    @if($item->tanggal_mulai || $item->tanggal_selesai)
                    <div class="col-md-6 mb-2">
                        <label class="form-label text-muted">Periode</label>
                        <p class="form-control-plaintext">
                            @if($item->tanggal_mulai)
                                {{ $item->tanggal_mulai->format('d/m/Y') }}
                                @if($item->tanggal_selesai)
                                    - {{ $item->tanggal_selesai->format('d/m/Y') }}
                                @endif
                            @else
                                -
                            @endif
                        </p>
                    </div>
                    @endif

                    @if($item->capaian_pembelajaran)
                    <div class="col-md-12 mb-2">
                        <label class="form-label text-muted">Capaian Pembelajaran</label>
                        <p class="form-control-plaintext">{!! nl2br(e($item->capaian_pembelajaran)) !!}</p>
                    </div>
                    @endif

                    @if($item->tujuan)
                    <div class="col-md-12 mb-2">
                        <label class="form-label text-muted">Tujuan Pembelajaran</label>
                        <p class="form-control-plaintext">{!! nl2br(e($item->tujuan)) !!}</p>
                    </div>
                    @endif

                    @if($item->metode)
                    <div class="col-md-12 mb-2">
                        <label class="form-label text-muted">Metode Pembelajaran</label>
                        <p class="form-control-plaintext">{!! nl2br(e($item->metode)) !!}</p>
                    </div>
                    @endif

                    @if($item->media)
                    <div class="col-md-12 mb-2">
                        <label class="form-label text-muted">Media Pembelajaran</label>
                        <p class="form-control-plaintext">{!! nl2br(e($item->media)) !!}</p>
                    </div>
                    @endif

                    @if($item->sumber)
                    <div class="col-md-12 mb-2">
                        <label class="form-label text-muted">Sumber Belajar</label>
                        <p class="form-control-plaintext">{!! nl2br(e($item->sumber)) !!}</p>
                    </div>
                    @endif

                    @if($item->alokasi_waktu)
                    <div class="col-md-12 mb-2">
                        <label class="form-label text-muted">Alokasi Waktu</label>
                        <p class="form-control-plaintext">{!! nl2br(e($item->alokasi_waktu)) !!}</p>
                    </div>
                    @endif

                    @if($item->praktik_pedagogis)
                    <div class="col-md-12 mb-2">
                        <label class="form-label text-muted">Praktik Pedagogis</label>
                        <p class="form-control-plaintext">{!! nl2br(e($item->praktik_pedagogis)) !!}</p>
                    </div>
                    @endif

                    @if($item->lingkungan_pembelajaran)
                    <div class="col-md-12 mb-2">
                        <label class="form-label text-muted">Lingkungan Pembelajaran</label>
                        <p class="form-control-plaintext">{!! nl2br(e($item->lingkungan_pembelajaran)) !!}</p>
                    </div>
                    @endif

                    @if($item->pemanfaatan_digital)
                    <div class="col-md-12 mb-2">
                        <label class="form-label text-muted">Pemanfaatan Digital</label>
                        <p class="form-control-plaintext">{!! nl2br(e($item->pemanfaatan_digital)) !!}</p>
                    </div>
                    @endif

                    @if($item->pengalaman_pembelajaran)
                    <div class="col-md-12 mb-2">
                        <label class="form-label text-muted">Pengalaman Pembelajaran</label>
                        <p class="form-control-plaintext">{!! nl2br(e($item->pengalaman_pembelajaran)) !!}</p>
                    </div>
                    @endif

                    @if($item->refleksi_pembelajaran)
                    <div class="col-md-12 mb-2">
                        <label class="form-label text-muted">Refleksi Pembelajaran</label>
                        <p class="form-control-plaintext">{!! nl2br(e($item->refleksi_pembelajaran)) !!}</p>
                    </div>
                    @endif

                    @if($item->penilaian)
                    <div class="col-md-12 mb-2">
                        <label class="form-label text-muted">Penilaian</label>
                        <p class="form-control-plaintext">{!! nl2br(e($item->penilaian)) !!}</p>
                    </div>
                    @endif

                    @if($item->komponenNilai->count() > 0)
                    <div class="col-md-12 mb-2">
                        <label class="form-label text-muted">Komponen Penilaian</label>
                        <div class="form-control-plaintext">
                            <div class="list-group list-group-flush">
                                @foreach($item->komponenNilai as $komponen)
                                    <div class="list-group-item px-0 py-2">
                                        <div class="row align-items-center">
                                            <div class="col">
                                                <strong>{{ $komponen->nama_komponen }}</strong>
                                            </div>
                                            @if($komponen->bobot)
                                                <div class="col-auto">
                                                    <span class="badge bg-blue">{{ $komponen->bobot }}%</span>
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                    @endif

                    @if($item->html_content)
                    <div class="col-md-12 mb-2">
                        <div class="collapse" id="previewDocContent">
                            <div class="card mt-3">
                                <div class="card-body p-0">
                                    <iframe srcdoc="{!! htmlspecialchars($item->html_content, ENT_QUOTES, 'UTF-8') !!}" style="width:100%;min-height:600px;border:none;"></iframe>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
