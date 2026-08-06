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
                            <a href="{{ route('rencana_pembelajaran.export_pdf', $item->id) }}" class="btn btn-danger btn-sm" target="_blank">
                                <i class="ti ti-file-pdf me-1"></i>Export PDF
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
                <div class="mb-4">
                    <div class="row">
                        <div class="col-md-8">
                            <h3 class="mb-1">{{ $item->judul }}</h3>
                            <p class="text-muted mb-0">RPP untuk mata pelajaran <strong>{{ $item->mataPelajaran->nama_mapel }}</strong> - kelas <strong>{{ $item->kelas->nama_kelas }}</strong></p>
                        </div>
                        <div class="col-md-4 text-md-end mt-3 mt-md-0">
                            <span class="badge bg-{{ $item->status === 'published' ? 'success' : 'warning' }} py-2 px-3">{{ ucfirst($item->status) }}</span>
                        </div>
                    </div>
                </div>

                <div class="row g-3">
                    <div class="col-12">
                        <div class="card card-body shadow-sm">
                            <h5 class="mb-3">1. Informasi Umum</h5>
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
                                    <p class="form-control-plaintext">{{ ucfirst($item->status) }}</p>
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
                            </div>
                        </div>
                    </div>

                    <div class="col-12">
                        <div class="card card-body shadow-sm">
                            <h5 class="mb-3">2. Capaian Pembelajaran</h5>
                            <div class="text-muted mb-2">Tuliskan Capaian pembelajaran untuk masing-masing mapel berdasarkan Kep BSKAP 046/2025 (bagi mapel umum) dan Kep BKPDM 020/2026 (bagi mapel PAI dan Budi Pekerti).</div>
                            <div class="preview-content">{!! nl2br(e($item->capaian_pembelajaran ?? '-')) !!}</div>
                        </div>
                    </div>

                    <div class="col-12">
                        <div class="card card-body shadow-sm">
                            <h5 class="mb-3">3. Tujuan Pembelajaran</h5>
                            <div class="text-muted mb-2">Sebutkan Tujuan pembelajaran yang mengacu pada capaian pembelajaran.</div>
                            <div class="preview-content">{!! nl2br(e($item->tujuan ?? '-')) !!}</div>
                        </div>
                    </div>

                    <div class="col-12">
                        <div class="card card-body shadow-sm">
                            <h5 class="mb-3">4. Praktik Pedagogis</h5>
                            <div class="text-muted mb-2">Jelaskan metode dan model pembelajaran yang akan digunakan.</div>
                            <div class="preview-content">{!! nl2br(e($item->praktik_pedagogis ?? '-')) !!}</div>
                        </div>
                    </div>

                    <div class="col-12">
                        <div class="card card-body shadow-sm">
                            <h5 class="mb-3">5. Lingkungan Pembelajaran</h5>
                            <div class="text-muted mb-2">Jelaskan ruang fisik, ruang virtual, dan budaya belajar.</div>
                            <div class="preview-content">{!! nl2br(e($item->lingkungan_pembelajaran ?? '-')) !!}</div>
                        </div>
                    </div>

                    <div class="col-12">
                        <div class="card card-body shadow-sm">
                            <h5 class="mb-3">6. Pemanfaatan Digital</h5>
                            <div class="text-muted mb-2">Sebutkan referensi buku, link, atau sumber lain yang digunakan.</div>
                            <div class="preview-content">{!! nl2br(e($item->pemanfaatan_digital ?? '-')) !!}</div>
                        </div>
                    </div>

                    <div class="col-12">
                        <div class="card card-body shadow-sm">
                            <h5 class="mb-3">7. Pengalaman Pembelajaran</h5>
                            <div class="text-muted mb-2">Sebutkan gambaran singkat kegiatan pendahuluan, inti, dan penutup.</div>
                            <div class="preview-content">{!! nl2br(e($item->pengalaman_pembelajaran ?? '-')) !!}</div>
                        </div>
                    </div>

                    <div class="col-12">
                        <div class="card card-body shadow-sm">
                            <h5 class="mb-3">8. Refleksi Pembelajaran</h5>
                            <div class="text-muted mb-2">Sebutkan refleksi pembelajaran (opsional) jika dilakukan refleksi.</div>
                            <div class="preview-content">{!! nl2br(e($item->refleksi_pembelajaran ?? '-')) !!}</div>
                        </div>
                    </div>

                    <div class="col-12">
                        <div class="card card-body shadow-sm">
                            <h5 class="mb-3">9. Asesmen</h5>
                            <div class="text-muted mb-2">Sebutkan bentuk instrumen (Lembar Kerja Murid) dan kriteria asesmen kognitif / psikomotorik / afektif.</div>
                            <div class="preview-content">{!! nl2br(e($item->penilaian ?? '-')) !!}</div>
                        </div>
                    </div>

                    @if($item->komponenNilai->count() > 0)
                    <div class="col-12">
                        <div class="card card-body shadow-sm">
                            <h5 class="mb-3">Komponen Penilaian</h5>
                            <div class="row gy-2">
                                @foreach($item->komponenNilai as $komponen)
                                    <div class="col-md-4">
                                        <div class="border rounded p-3 h-100">
                                            <strong>{{ $komponen->nama_komponen }}</strong>
                                            @if($komponen->bobot)
                                                <div class="text-muted small">Bobot: {{ $komponen->bobot }}%</div>
                                            @endif
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                    @endif

                    @if($item->html_content)
                    <div class="col-12">
                        <div class="card card-body shadow-sm">
                            <h5 class="mb-3">Preview Dokumen HTML</h5>
                            <div class="ratio ratio-16x9">
                                <iframe srcdoc="{!! htmlspecialchars($item->html_content, ENT_QUOTES, 'UTF-8') !!}" class="border"></iframe>
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
