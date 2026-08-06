@extends('layouts.app', ['pageSlug' => 'rencana_pembelajaran'])

@section('title', 'Detail Rencana Pembelajaran')

@section('content')
@php
    $statusLabel = ucfirst($item->status ?? 'draft');
    $statusClass = match($item->status ?? 'draft') {
        'published' => 'success',
        'draft' => 'warning',
        default => 'secondary',
    };

    $sections = [
        ['title' => 'Capaian Pembelajaran', 'value' => $item->capaian_pembelajaran],
        ['title' => 'Tujuan Pembelajaran', 'value' => $item->tujuan],
        ['title' => 'Praktik Pedagogis', 'value' => $item->praktik_pedagogis],
        ['title' => 'Lingkungan Pembelajaran', 'value' => $item->lingkungan_pembelajaran],
        ['title' => 'Pemanfaatan Digital', 'value' => $item->pemanfaatan_digital],
        ['title' => 'Pengalaman Pembelajaran', 'value' => $item->pengalaman_pembelajaran],
        ['title' => 'Refleksi Pembelajaran', 'value' => $item->refleksi_pembelajaran],
        ['title' => 'Asesmen', 'value' => $item->penilaian],
    ];
@endphp

<style>
    .rpp-word-doc {
        background: #ffffff;
        border: 1px solid #d0d7de;
        border-radius: 12px;
        padding: 32px;
        box-shadow: 0 8px 24px rgba(15, 23, 42, 0.06);
        font-family: "Times New Roman", Georgia, serif;
        color: #111827;
    }

    .rpp-word-doc .doc-title {
        font-size: 22px;
        font-weight: 700;
        text-align: center;
        text-transform: uppercase;
        margin-bottom: 6px;
        letter-spacing: 0.02em;
    }

    .rpp-word-doc .doc-subtitle {
        text-align: center;
        font-size: 13px;
        margin-bottom: 18px;
        color: #374151;
    }

    .rpp-word-doc .word-table {
        width: 100%;
        border-collapse: collapse;
        margin-bottom: 18px;
        font-size: 12px;
    }

    .rpp-word-doc .word-table th,
    .rpp-word-doc .word-table td {
        border: 1px solid #111827;
        padding: 7px 8px;
        vertical-align: top;
    }

    .rpp-word-doc .word-table th {
        width: 28%;
        background: #f3f4f6;
        font-weight: 700;
        text-align: left;
    }

    .rpp-word-doc .section-block {
        margin-top: 14px;
    }

    .rpp-word-doc .section-title {
        font-size: 13px;
        font-weight: 700;
        margin-bottom: 6px;
        text-transform: uppercase;
        text-decoration: underline;
    }

    .rpp-word-doc .section-body {
        font-size: 12px;
        line-height: 1.8;
        white-space: pre-wrap;
    }

    .rpp-word-doc .section-body:empty::before {
        content: '-';
        color: #9ca3af;
    }

    .rpp-word-doc .list-table {
        width: 100%;
        border-collapse: collapse;
        font-size: 12px;
        margin-top: 8px;
    }

    .rpp-word-doc .list-table th,
    .rpp-word-doc .list-table td {
        border: 1px solid #111827;
        padding: 7px 8px;
    }

    .rpp-word-doc .list-table th {
        background: #f3f4f6;
        text-align: left;
        width: 40%;
    }

    @media print {
        .rpp-action-bar {
            display: none !important;
        }

        .rpp-word-doc {
            box-shadow: none;
            border: 0;
            padding: 0;
        }
    }
</style>

<div class="container-xl py-3">
    <div class="rpp-action-bar d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
        <div>
            <div class="d-flex flex-wrap align-items-center gap-2 mb-2">
                <span class="badge bg-primary-lt text-primary">Rencana Pembelajaran</span>
                <span class="badge bg-{{ $statusClass }}-lt text-{{ $statusClass }}">{{ $statusLabel }}</span>
            </div>
            <h2 class="fw-bold mb-1">{{ $item->judul ?? 'Tanpa Judul' }}</h2>
        </div>

        <div class="d-flex flex-wrap gap-2">
            <a href="{{ route('rencana_pembelajaran.edit', $item->id) }}" class="btn btn-primary btn-sm">
                <i class="ti ti-edit me-1"></i>Edit
            </a>
            <a href="{{ route('rencana_pembelajaran.export_pdf', $item->id) }}" class="btn btn-danger btn-sm" target="_blank">
                <i class="ti ti-file-pdf me-1"></i>Export PDF
            </a>
            @if($item->html_content)
                <button type="button" class="btn btn-info btn-sm" data-bs-toggle="collapse" data-bs-target="#previewDocContent" aria-expanded="false" aria-controls="previewDocContent">
                    <i class="ti ti-eye me-1"></i>Preview Dokumen
                </button>
            @endif
            <a href="{{ route('rencana_pembelajaran.index', ['mata_pelajaran_id' => $item->mata_pelajaran_id, 'tingkat' => optional($item->kelas)->tingkat_kelas]) }}" class="btn btn-secondary btn-sm">
                <i class="ti ti-arrow-left me-1"></i>Kembali
            </a>
        </div>
    </div>

    <div class="rpp-word-doc">
        <div class="doc-title">Rencana Pembelajaran</div>
        <div class="doc-subtitle">
            {{ optional($item->mataPelajaran)->nama_mapel ?? '-' }} • {{ optional($item->kelas)->nama_kelas ?? '-' }}
        </div>

        <table class="word-table">
            <tr>
                <th>Judul</th>
                <td>{{ $item->judul ?? '-' }}</td>
            </tr>
            <tr>
                <th>Mata Pelajaran</th>
                <td>{{ optional($item->mataPelajaran)->nama_mapel ?? '-' }}</td>
            </tr>
            <tr>
                <th>Kelas</th>
                <td>{{ optional($item->kelas)->nama_kelas ?? '-' }}</td>
            </tr>
            <tr>
                <th>Status</th>
                <td>{{ $statusLabel }}</td>
            </tr>
            <tr>
                <th>Periode</th>
                <td>
                    @if($item->tanggal_mulai)
                        {{ $item->tanggal_mulai->format('d/m/Y') }}
                        @if($item->tanggal_selesai)
                            - {{ $item->tanggal_selesai->format('d/m/Y') }}
                        @endif
                    @else
                        -
                    @endif
                </td>
            </tr>
        </table>

        <div class="section-block">
            <div class="section-title">1. Informasi Umum</div>
            <div class="section-body">
                Dokumen ini memuat rencana pembelajaran yang disusun secara sistematis dengan format yang menyerupai dokumen resmi Word.
            </div>
        </div>

        @foreach($sections as $index => $section)
            <div class="section-block">
                <div class="section-title">{{ $index + 2 }}. {{ $section['title'] }}</div>
                <div class="section-body">
                    @if(!empty($section['value']))
                        {!! $section['value'] !!}
                    @else
                        -
                    @endif
                </div>
            </div>
        @endforeach

        @if($item->komponenNilai->count() > 0)
            <div class="section-block">
                <div class="section-title">Komponen Penilaian</div>
                <table class="list-table">
                    <thead>
                        <tr>
                            <th>Komponen</th>
                            <th>Bobot</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($item->komponenNilai as $komponen)
                            <tr>
                                <td>{{ $komponen->nama_komponen }}</td>
                                <td>{{ $komponen->bobot ? $komponen->bobot . '%' : '-' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif

        @if($item->html_content)
            <div class="section-block">
                <div class="section-title">Preview Dokumen HTML</div>
                <div class="collapse mt-2" id="previewDocContent">
                    <div class="border rounded overflow-hidden bg-white" style="min-height: 420px;">
                        <iframe id="previewDocFrame" style="width:100%; min-height:420px; border:none;" sandbox="allow-same-origin"></iframe>
                    </div>
                </div>
            </div>
        @endif
    </div>
</div>

@push('js')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        var previewContent = {!! json_encode($item->html_content ?? '') !!};
        var previewFrame = document.getElementById('previewDocFrame');
        if (previewFrame && previewContent) {
            previewFrame.srcdoc = previewContent;
        }
    });
</script>
@endpush
@endsection
