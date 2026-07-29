@extends('layouts.app')

@section('title','Detail Pengembangan')

@section('content')
<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-lg-10 col-xl-9">
            <div class="card mb-3">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h3 class="card-title fw-semibold m-0">Detail Kegiatan Pengembangan</h3>
                    <a href="{{ url()->previous() }}" class="btn btn-secondary btn-sm">Kembali</a>
                </div>
                <div class="card-body">
                    <h4 class="fw-bold">{{ $item->nama_kegiatan }}</h4>
                    <hr>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <div class="p-3 border rounded bg-light h-100">
                                <small class="text-muted">Jenis Kegiatan</small>
                                <div class="fw-semibold">{{ \App\Models\JenisKegiatan::where('kode', $item->jenis_kegiatan)->value('nama') ?? $item->jenis_kegiatan }}</div>
                                @if(!empty($item->tema_kegiatan))
                                    <div class="text-muted small mt-1">Tema: <span class="fw-semibold">{{ $item->tema_kegiatan }}</span></div>
                                @endif
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="p-3 border rounded bg-light h-100">
                                <small class="text-muted">Pemateri</small>
                                <div class="fw-semibold">
                                    @if(is_array($item->pemateri))
                                        {{ implode(', ', $item->pemateri) }}
                                    @else
                                        {{ $item->pemateri }}
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row g-3 mt-3">
                        <div class="col-md-6">
                            <div class="p-3 border rounded bg-light h-100">
                                <small class="text-muted">Tanggal Mulai</small>
                                <div class="fw-semibold">{{ optional($item->tanggal_mulai)->format('d-m-Y') ?? '-' }}</div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="p-3 border rounded bg-light h-100">
                                <small class="text-muted">Tanggal Selesai</small>
                                <div class="fw-semibold">{{ optional($item->tanggal_selesai)->format('d-m-Y') ?? '-' }}</div>
                            </div>
                        </div>
                    </div>

                    @if(!empty($item->deskripsi))
                    <div class="mt-3">
                        <div class="p-3 border rounded bg-light">
                            <small class="text-muted">Deskripsi</small>
                            <div>{!! nl2br(e($item->deskripsi)) !!}</div>
                        </div>
                    </div>
                    @endif
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <h5 class="card-title fw-semibold m-0">Peserta</h5>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('pengembangan.generate_certificates',$item->id) }}">
                        @csrf
                        <div class="row g-3">
                            <div class="col-md-7">
                                <div class="mb-2">
                                    <label class="form-label">Pilih Peserta untuk Sertifikat</label>
                                    <select multiple class="form-select" name="participant_ids[]" size="8">
                                        @foreach($participants as $p)
                                            <option value="{{ $p['id'] }}">{{ $p['name'] }} ({{ strtoupper($p['type']) }})</option>
                                        @endforeach
                                    </select>
                                    <small class="text-muted">Pilih beberapa peserta (Ctrl/Cmd + klik) atau biarkan kosong untuk semua.</small>
                                </div>
                            </div>
                            <div class="col-md-5">
                                <div class="mb-2">
                                    <label class="form-label">Template Sertifikat</label>
                                        <div class="d-flex justify-content-between align-items-center mb-1">
                                            <label class="form-label mb-0">Template Sertifikat</label>
                                            <a href="{{ route('pengembangan.templates.index') }}" class="small">Kelola Template</a>
                                        </div>
                                        <select name="template_id" class="form-select">
                                        <option value="">-- Default Template --</option>
                                        @foreach($templates as $t)
                                            <option value="{{ $t->id }}">{{ $t->nama }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="d-grid gap-2 mt-2">
                                    <button type="submit" class="btn btn-success">
                                        <i class="ti ti-file-certificate me-1"></i> Generate Certificates
                                    </button>
                                </div>
                            </div>
                        </div>
                    </form>

                    <hr class="my-4">

                    <div class="row g-3">
                        <div class="col-md-7">
                            <label class="form-label">Preview Sertifikat untuk peserta</label>
                            <div class="d-flex gap-2">
                                <select id="previewParticipant" class="form-select">
                                    <option value="">-- Pilih peserta --</option>
                                    @foreach($participants as $p)
                                        <option value="{{ $p['id'] }}">{{ $p['name'] }} ({{ strtoupper($p['type']) }})</option>
                                    @endforeach
                                </select>
                                <a id="previewBtn" href="#" target="_blank" class="btn btn-outline-secondary">Preview</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('js')
<script>
document.addEventListener('DOMContentLoaded', function(){
    const previewSelect = document.getElementById('previewParticipant');
    const previewBtn = document.getElementById('previewBtn');
    const templateSelect = document.querySelector('select[name="template_id"]');
    function updatePreviewHref(){
        const pid = previewSelect.value;
        if(!pid){ previewBtn.href = '#'; previewBtn.classList.add('disabled'); return; }
        const tid = templateSelect ? templateSelect.value : '';
        let url = '/pengembangan/{{ $item->id }}/preview-certificate?participant_id=' + pid;
        if(tid) url += '&template_id=' + tid;
        previewBtn.classList.remove('disabled');
        previewBtn.href = url;
    }
    previewSelect?.addEventListener('change', updatePreviewHref);
    templateSelect?.addEventListener('change', updatePreviewHref);
});
</script>
@endpush
@endsection
