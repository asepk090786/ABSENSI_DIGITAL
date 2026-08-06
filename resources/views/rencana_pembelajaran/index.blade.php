@extends('layouts.app', ['pageSlug' => 'rencana_pembelajaran'])

@section('title', 'Rencana Pembelajaran')

@php($isLanding = $isLanding ?? false)

@section('content')
<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header border-0 pt-3 pb-2">
                <div class="row align-items-center">
                    <div class="col">
                        @if($isLanding)
                            <h4 class="card-title fw-semibold m-0">Rencana Pembelajaran</h4>
                        @else
                            <h4 class="card-title fw-semibold m-0">
                                Rencana Pembelajaran - {{ $mataPelajaran->nama_mapel }} (Tingkat {{ $tingkat }})
                            </h4>
                        @endif
                    </div>
                    @if(!$isLanding)
                    <div class="col-auto">
                        <div >
                            <a href="{{ route('rencana_pembelajaran.template') }}" class="btn btn-outline-info btn-sm" title="Download Template">
                                <i class="ti ti-file-word me-1"></i>Template
                            </a>
                            <a href="{{ route('rencana_pembelajaran.import_form', ['mata_pelajaran_id' => $mataPelajaran->id, 'tingkat' => $tingkat]) }}" class="btn btn-sm btn-info btn-modern" title="Import dari Word">
                                <i class="ti ti-file-word me-1"></i>Import Word
                            </a>
                            <a href="{{ route('rencana_pembelajaran.create', ['mata_pelajaran_id' => $mataPelajaran->id, 'tingkat' => $tingkat]) }}" class="btn btn-primary btn-sm">
                                <i class="ti ti-plus me-1"></i>Tambah Rencana
                            </a>
                            <a href="{{ route('mata_pelajaran.guru') }}" class="btn btn-secondary btn-sm">
                                <i class="ti ti-arrow-left me-1"></i>Kembali
                            </a>
                        </div>
                    </div>
                    @endif
                </div>
            </div>
            <div class="card-body">
                @if(session('success'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <i class="ti ti-check me-2"></i>{{ session('success') }}
                        <button type="button" class="close" data-bs-dismiss="alert"><span aria-hidden="true">&times;</span></button>
                    </div>
                @endif

                @if(isset($previewItem) && $previewItem)
                    <div class="card mb-4">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <div>
                                <h5 class="card-title mb-1">Preview Rencana Pembelajaran</h5>
                                <p class="text-muted mb-0">{{ $previewItem->judul }}</p>
                            </div>
                            <a href="{{ route('rencana_pembelajaran.index', ['mata_pelajaran_id' => $mataPelajaran->id, 'tingkat' => $tingkat]) }}" class="btn btn-sm btn-secondary">Tutup Preview</a>
                        </div>
                        <div class="card-body">
                            <div class="row mb-3">
                                <div class="col-md-4">
                                    <label class="form-label text-muted">Mata Pelajaran</label>
                                    <p class="form-control-plaintext">{{ $previewItem->mataPelajaran->nama_mapel }}</p>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label text-muted">Kelas</label>
                                    <p class="form-control-plaintext">{{ $previewItem->kelas->nama_kelas }}</p>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label text-muted">Status</label>
                                    <p class="form-control-plaintext">
                                        <span class="badge bg-{{ $previewItem->status === 'published' ? 'success' : 'warning' }}">
                                            {{ ucfirst($previewItem->status) }}
                                        </span>
                                    </p>
                                </div>
                            </div>

                            @if($previewItem->html_content)
                                <div class="mb-3">
                                    <h6 class="mb-2">Preview Dokumen</h6>
                                    <div class="border bg-light" style="min-height:420px;">
                                        <iframe id="previewItemFrame" style="width:100%; min-height:420px; border:none;" sandbox="allow-same-origin allow-scripts"></iframe>
                                    </div>
                                </div>
                            @else
                                <div class="row mb-3">
                                    <div class="col-md-12">
                                        <h6 class="mb-2">Ringkasan Konten</h6>
                                    </div>
                                    <div class="col-md-12 mb-2">
                                        <strong>Capaian Pembelajaran</strong>
                                        <p>{{ $previewItem->capaian_pembelajaran ?? '-' }}</p>
                                    </div>
                                    <div class="col-md-12 mb-2">
                                        <strong>Tujuan Pembelajaran</strong>
                                        <p>{{ $previewItem->tujuan ?? '-' }}</p>
                                    </div>
                                    <div class="col-md-12 mb-2">
                                        <strong>Metode Pembelajaran</strong>
                                        <p>{{ $previewItem->metode ?? '-' }}</p>
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>
                @endif

                @if($isLanding)
                    <div class="alert alert-info">
                        <i class="ti ti-info-circle me-2"></i>Pilih mata pelajaran dan tingkat untuk melihat atau membuat rencana pembelajaran Anda.
                    </div>
                    <div class="table-responsive">
                        <table class="table table-vcenter table-hover table-tabler">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>Nama Mapel</th>
                                    <th>Tingkat</th>
                                    <th>Kelas</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                            @forelse($items as $index => $item)
                                <tr>
                                    <td>{{ $index + 1 }}</td>
                                    <td>{{ $item->nama_mapel }}</td>
                                    <td>{{ $item->tingkat }}</td>
                                    <td>{{ $item->kelas_names }}</td>
                                    <td>
                                        <a href="{{ route('rencana_pembelajaran.index', ['mata_pelajaran_id' => $item->id, 'tingkat' => $item->tingkat]) }}" class="btn btn-sm btn-outline-secondary">
                                            <i class="ti ti-book me-1"></i>Kelola
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center text-muted">
                                        <i class="ti ti-info-circle me-2"></i>Belum ada mata pelajaran tersimpan untuk Anda.
                                    </td>
                                </tr>
                            @endforelse
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="row mb-2">
                        <div class="col-md-6">
                            <div class="input-group">
                                <label class="input-group-text">Sortir</label>
                                <select id="sortBy" class="form-select" onchange="location.href='?mata_pelajaran_id={{ request()->query('mata_pelajaran_id') }}&tingkat={{ request()->query('tingkat') }}&sort=' + this.value">
                                    <option value="">-- Pilih Sortir --</option>
                                    <option value="judul_asc" {{ request()->query('sort') === 'judul_asc' ? 'selected' : '' }}>Judul (A-Z)</option>
                                    <option value="judul_desc" {{ request()->query('sort') === 'judul_desc' ? 'selected' : '' }}>Judul (Z-A)</option>
                                    <option value="status_asc" {{ request()->query('sort') === 'status_asc' ? 'selected' : '' }}>Status (Draft - Published)</option>
                                    <option value="terbaru" {{ request()->query('sort') === 'terbaru' ? 'selected' : '' }}>Terbaru</option>
                                    <option value="terlama" {{ request()->query('sort') === 'terlama' ? 'selected' : '' }}>Terlama</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6 text-end">
                            <button type="button" class="btn btn-sm btn-danger" id="bulkDeleteBtn" style="display:none;">
                                <i class="ti ti-trash me-1"></i>Hapus Terpilih (<span id="selectedCount">0</span>)
                            </button>
                        </div>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-vcenter table-hover table-tabler">
                            <thead>
                                <tr>
                                    <th style="width: 30px;">
                                        <input type="checkbox" id="selectAll" class="form-check-input">
                                    </th>
                                    <th>No</th>
                                    <th>Judul</th>
                                    <th>Kelas</th>
                                    <th>Status</th>
                                    <th>Tanggal</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                            @forelse($items as $index => $item)
                                <tr>
                                    <td>
                                        <input type="checkbox" name="selected_ids[]" value="{{ implode(',', $item->related_ids ?? [$item->id]) }}" class="form-check-input item-checkbox">
                                    </td>
                                    <td>{{ $index + 1 }}</td>
                                    <td>
                                        <a href="{{ route('rencana_pembelajaran.show', $item->id) }}" class="text-decoration-none">
                                            {{ $item->judul }}
                                        </a>
                                        @if(($item->jumlah_kelas ?? 1) > 1)
                                            <span class="badge bg-info ms-1">{{ $item->jumlah_kelas }} kelas</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if(!empty($item->kelas_nama) && count($item->kelas_nama) > 0)
                                            {{ $item->kelas_nama->join(', ') }}
                                        @else
                                            {{ $item->kelas->nama_kelas ?? '-' }}
                                        @endif
                                    </td>
                                    <td>
                                        <span class="badge bg-{{ $item->status === 'published' ? 'success' : 'warning' }}">
                                            {{ ucfirst($item->status) }}
                                        </span>
                                    </td>
                                    <td>
                                        @if($item->tanggal_mulai)
                                            {{ \Carbon\Carbon::parse($item->tanggal_mulai)->format('d/m/Y') }}
                                            @if($item->tanggal_selesai)
                                                - {{ \Carbon\Carbon::parse($item->tanggal_selesai)->format('d/m/Y') }}
                                            @endif
                                        @else
                                            -
                                        @endif
                                    </td>
                                    <td>
                                        <div >
                                            <a href="{{ route('rencana_pembelajaran.show', $item->id) }}" class="btn btn-sm btn-outline-info" title="Lihat">
                                                <i class="ti ti-eye"></i>
                                            </a>
                                            <a href="{{ route('rencana_pembelajaran.export_word', $item->id) }}" class="btn btn-sm btn-outline-success" title="Export Word">
                                                <i class="ti ti-download"></i>
                                            </a>
                                            <a href="{{ route('rencana_pembelajaran.export_pdf', $item->id) }}" class="btn btn-sm btn-outline-danger" title="Export PDF" target="_blank">
                                                <i class="ti ti-file-pdf"></i>
                                            </a>
                                            <a href="{{ route('rencana_pembelajaran.edit', $item->id) }}" class="btn btn-sm btn-outline-primary" title="Edit">
                                                <i class="ti ti-edit"></i>
                                            </a>
                                            <a href="{{ route('rencana_pembelajaran.index', ['mata_pelajaran_id' => $mataPelajaran->id, 'tingkat' => $tingkat, 'preview' => $item->id]) }}" class="btn btn-sm btn-outline-secondary" title="Preview">
                                                <i class="ti ti-eye-off"></i>
                                            </a>
                                            <button type="button" class="btn btn-sm btn-outline-danger" onclick="confirmDeleteGroup('{{ implode(',', $item->related_ids ?? [$item->id]) }}')" title="Hapus">
                                                <i class="ti ti-trash"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center text-muted">
                                        <i class="ti ti-info-circle me-2"></i>Belum ada rencana pembelajaran.
                                    </td>
                                </tr>
                            @endforelse
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

<form id="deleteForm" method="POST" style="display:none;">
    @csrf
    @method('DELETE')
</form>

<form id="bulkDeleteForm" method="POST" style="display:none;">
    @csrf
    @method('POST')
</form>
@endsection

@push('js')
<script>
// Select all checkbox functionality
const selectAllElement = document.getElementById('selectAll');
if (selectAllElement) {
    selectAllElement.addEventListener('change', function() {
        const checkboxes = document.querySelectorAll('.item-checkbox');
        checkboxes.forEach(checkbox => checkbox.checked = this.checked);
        updateBulkDeleteButton();
    });
}

// Individual checkbox change
const itemCheckboxElements = document.querySelectorAll('.item-checkbox');
if (itemCheckboxElements.length > 0) {
    itemCheckboxElements.forEach(checkbox => {
        checkbox.addEventListener('change', function() {
            updateBulkDeleteButton();
        });
    });
}

function updateBulkDeleteButton() {
    const selectedCount = document.querySelectorAll('.item-checkbox:checked').length;
    const btn = document.getElementById('bulkDeleteBtn');
    const selectedCountElement = document.getElementById('selectedCount');

    if (!btn || !selectedCountElement) {
        return;
    }

    selectedCountElement.textContent = selectedCount;
    
    if (selectedCount > 0) {
        btn.style.display = 'inline-block';
    } else {
        btn.style.display = 'none';
    }
}

// Load preview iframe content if any
var previewContent = {!! json_encode($previewItem?->html_content ?? null) !!};
if (previewContent) {
    document.addEventListener('DOMContentLoaded', function() {
        var previewFrame = document.getElementById('previewItemFrame');
        if (previewFrame) {
            previewFrame.srcdoc = previewContent;
        }
    });
}

// Bulk delete handler
const bulkDeleteBtn = document.getElementById('bulkDeleteBtn');
if (bulkDeleteBtn) {
    bulkDeleteBtn.addEventListener('click', function() {
        const selectedCheckboxes = document.querySelectorAll('.item-checkbox:checked');
        if (selectedCheckboxes.length === 0) {
            alert('Pilih minimal 1 item untuk dihapus');
            return;
        }
        
        if (!confirm('Hapus ' + selectedCheckboxes.length + ' rencana pembelajaran?')) {
            return;
        }
        
        const ids = Array.from(selectedCheckboxes).map(checkbox => checkbox.value);
        const form = document.getElementById('bulkDeleteForm');
        form.action = '{{ route("rencana_pembelajaran.bulkDelete") }}';
        
        // Add hidden inputs for IDs
        form.innerHTML = '@csrf @method("POST")<input type="hidden" name="ids" value="' + ids.join(',') + '">';
        form.submit();
    });
}

function confirmDeleteGroup(ids) {
    if (!ids) {
        return;
    }

    const idArray = String(ids).split(',').filter(Boolean);
    const jumlah = idArray.length;

    if (!confirm('Hapus rencana pembelajaran ini' + (jumlah > 1 ? ' untuk ' + jumlah + ' kelas' : '') + '?')) {
        return;
    }

    const form = document.getElementById('bulkDeleteForm');
    form.action = '{{ route("rencana_pembelajaran.bulkDelete") }}';
    form.innerHTML = '@csrf @method("POST")<input type="hidden" name="ids" value="' + idArray.join(',') + '">';
    form.submit();
}
</script>
@endpush
