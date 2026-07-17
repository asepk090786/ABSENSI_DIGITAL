@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h3 class="card-title">SK TUGAS</h3>
                    @if(auth()->user()->hasRole('Admin'))
                        <a href="{{ route('sk_tugas.create') }}" class="btn btn-primary">Unggah SK TUGAS</a>
                    @endif
                </div>
                <div class="card-body">
                    @if(session('success'))
                        <div class="alert alert-success">{{ session('success') }}</div>
                    @endif

                    @if($items->isEmpty())
                        <div class="alert alert-info">Belum ada SK TUGAS tersedia.</div>
                    @else
                        <div class="table-responsive">
                            <table class="table table-bordered table-hover">
                                <thead>
                                    <tr>
                                        <th>No</th>
                                        <th>Judul</th>
                                        <th>Diunggah</th>
                                        <th>Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($items as $index => $item)
                                        <tr>
                                            <td>{{ $index + 1 }}</td>
                                            <td>{{ $item->judul }}</td>
                                            <td>{{ $item->created_at->format('d-m-Y H:i') }}</td>
                                            <td>
                                                @php
                                                    $extension = strtolower(pathinfo($item->file, PATHINFO_EXTENSION));
                                                @endphp
                                                @if($extension === 'pdf')
                                                    <button type="button" class="btn btn-sm btn-info me-1" data-bs-toggle="modal" data-bs-target="#previewSkTugasModal" onclick="loadSkTugasPreview({{ json_encode($item->judul) }}, {{ json_encode(route('sk_tugas.preview', $item->id)) }}, {{ json_encode(route('sk_tugas.download', $item->id)) }})">
                                                        <i class="ti ti-eye me-1"></i>Preview
                                                    </button>
                                                @endif
                                                <a href="{{ route('sk_tugas.download', $item->id) }}" class="btn btn-sm btn-success">Download</a>
                                                @if(auth()->user()->hasRole('Admin'))
                                                    <form action="{{ route('sk_tugas.toggle_visibility', $item->id) }}" method="POST" class="d-inline me-1">
                                                        @csrf
                                                        <button type="submit" class="btn btn-sm btn-warning" title="{{ $item->is_visible_to_guru ? 'Sembunyikan dari guru' : 'Tampilkan ke guru' }}">
                                                            <i class="ti ti-eye{{ $item->is_visible_to_guru ? '' : '-off' }} me-1"></i>
                                                            {{ $item->is_visible_to_guru ? 'Sembunyikan' : 'Tampilkan' }}
                                                        </button>
                                                    </form>
                                                    <form action="{{ route('sk_tugas.destroy', $item->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Yakin ingin menghapus SK Tugas ini?')">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="btn btn-sm btn-danger">
                                                            <i class="ti ti-trash me-1"></i>Hapus
                                                        </button>
                                                    </form>
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

<div class="modal fade" id="previewSkTugasModal" tabindex="-1" role="dialog" aria-labelledby="previewSkTugasLabel" aria-hidden="true">
    <div class="modal-dialog modal-fullscreen-lg-down modal-xl" role="document">
        <div class="modal-content">
            <div class="modal-header bg-primary">
                <h5 class="modal-title text-white" id="previewSkTugasLabel">
                    <i class="ti ti-file-pdf me-2"></i>Preview SK Tugas
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-0" style="height: 650px;">
                <div id="skTugasPdfContainer" class="w-100 h-100">
                    <div class="d-flex justify-content-center align-items-center h-100">
                        <div class="text-center text-muted">
                            <div class="spinner-border text-primary mb-2" role="status">
                                <span class="visually-hidden">Loading...</span>
                            </div>
                            <p>Memuat preview PDF...</p>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <a href="#" id="skTugasDownloadLink" class="btn btn-success" target="_blank" style="display: none;">
                    <i class="ti ti-download me-1"></i>Unduh PDF
                </a>
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>

<script>
function loadSkTugasPreview(judul, previewUrl, downloadUrl) {
    const container = document.getElementById('skTugasPdfContainer');
    const downloadLink = document.getElementById('skTugasDownloadLink');
    const title = document.getElementById('previewSkTugasLabel');

    title.textContent = 'Preview ' + judul;
    container.innerHTML = `
        <div class="d-flex justify-content-center align-items-center h-100">
            <div class="text-center text-muted">
                <div class="spinner-border text-primary mb-2" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
                <p>Memuat preview PDF...</p>
            </div>
        </div>
    `;

    downloadLink.href = downloadUrl;
    downloadLink.style.display = 'inline-block';

    setTimeout(() => {
        container.innerHTML = `
            <iframe src="${previewUrl}" class="w-100 h-100" style="border: none;" onerror="handleSkTugasPreviewError()"></iframe>
        `;
    }, 400);
}

function handleSkTugasPreviewError() {
    const container = document.getElementById('skTugasPdfContainer');
    container.innerHTML = `
        <div class="d-flex justify-content-center align-items-center h-100">
            <div class="alert alert-warning text-center">
                <i class="ti ti-alert-circle me-2"></i>
                <strong>Tidak dapat menampilkan preview</strong><br>
                <small>Silakan unduh file untuk melihatnya</small>
            </div>
        </div>
    `;
}
</script>
@endsection
