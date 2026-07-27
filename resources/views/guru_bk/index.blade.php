@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex flex-wrap justify-content-between align-items-center gap-2">
                    <div class="d-flex flex-wrap align-items-center gap-2">
                        <h3 class="card-title mb-0">Data Guru BK</h3>
                        <div class="btn-group btn-group-sm" role="group" aria-label="View toggle">
                            <button type="button" id="bkListViewBtn" class="btn btn-outline-secondary active">List</button>
                            <button type="button" id="bkGridViewBtn" class="btn btn-outline-secondary">Grid</button>
                        </div>
                    </div>
                    <a href="{{ route('guru_bk.create') }}" class="btn btn-primary">
                        <i class="ti ti-plus"></i> Tambah Guru BK
                    </a>
                </div>
                <div class="card-body">
                    @if(session('success'))
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            {{ session('success') }}
                            <button type="button" class="close" data-bs-dismiss="alert"><span aria-hidden="true">&times;</span></button>
                        </div>
                    @endif

                    @if($gurubk->isEmpty())
                        <div class="alert alert-info">
                            <i class="ti ti-info-circle"></i> Belum ada data Guru BK.
                        </div>
                    @else
                        <div id="bkListViewContainer" class="table-responsive">
                            <table class="table table-vcenter table-hover table-tabler">
                                <thead>
                                    <tr>
                                        <th>No</th>
                                        <th>Foto</th>
                                        <th>Nama</th>
                                        <th>NIP</th>
                                        <th>Guru Terpilih</th>
                                        <th>Kelas Binaan</th>
                                        <th>Email</th>
                                        <th>Telepon</th>
                                        <th>Status</th>
                                        <th>Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($gurubk as $index => $item)
                                        <tr>
                                            <td>{{ $index + 1 }}</td>
                                            <td>
                                                @if($item->foto)
                                                    <img src="{{ asset('storage/' . $item->foto) }}" alt="Foto" class="rounded" style="width: 50px; height: 50px; object-fit: cover;">
                                                @else
                                                    <div class="bg-light rounded d-flex align-items-center justify-content-center" style="width: 50px; height: 50px;">
                                                        <i class="ti ti-user" style="font-size: 24px; color: #999;"></i>
                                                    </div>
                                                @endif
                                            </td>
                                            <td>{{ $item->nama }}</td>
                                            <td>{{ $item->nip ?? '-' }}</td>
                                            <td>
                                                @if($item->guru_id && $item->guru)
                                                    <span class="badge bg-info">
                                                        <i class="ti ti-link"></i> {{ $item->guru->nama }}
                                                    </span>
                                                @elseif($item->guru_id)
                                                    <span class="badge bg-warning">
                                                        <i class="ti ti-alert-triangle"></i> Guru (ID: {{ $item->guru_id }}) - Tidak Ditemukan
                                                    </span>
                                                @else
                                                    <span class="badge bg-light text-dark border border-secondary">
                                                        <i class="ti ti-minus"></i> Input Manual
                                                    </span>
                                                @endif
                                            </td>
                                            <td>
                                                @if(!($hasGuruBkKelasColumn ?? false))
                                                    <span class="text-muted">-</span>
                                                @elseif($item->kelasBinaanBk->isEmpty())
                                                    <span class="text-muted">Belum ada</span>
                                                @else
                                                    @foreach($item->kelasBinaanBk as $kelasBinaan)
                                                        <span class="badge bg-primary text-white me-1 mb-1">{{ $kelasBinaan->nama_kelas }}</span>
                                                    @endforeach
                                                @endif
                                            </td>
                                            <td>{{ $item->email ?? '-' }}</td>
                                            <td>{{ $item->telepon ?? '-' }}</td>
                                            <td>
                                                @if($item->is_active)
                                                    <span class="badge bg-success">Aktif</span>
                                                @else
                                                    <span class="badge bg-secondary">Tidak Aktif</span>
                                                @endif
                                            </td>
                                            <td>
                                                <div class="btn-group" role="group">
                                                    <a href="{{ route('guru_bk.show', $item->id) }}" class="btn btn-sm btn-info btn-modern">
                                                        <i class="ti ti-eye"></i>
                                                    </a>
                                                    <a href="{{ route('guru_bk.edit', $item->id) }}" class="btn btn-sm btn-warning">
                                                        <i class="ti ti-edit"></i>
                                                    </a>
                                                    <form action="{{ route('guru_bk.destroy', $item->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Yakin ingin menghapus data ini?')">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="btn btn-sm btn-danger">
                                                            <i class="ti ti-trash"></i>
                                                        </button>
                                                    </form>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        <div id="bkGridViewContainer" class="row row-cols-1 row-cols-md-2 row-cols-xl-3 g-3 d-none">
                            @foreach($gurubk as $item)
                                <div class="col">
                                    <div class="card h-100 shadow-sm">
                                        <div class="card-body d-flex flex-column">
                                            <div class="overflow-hidden rounded mb-3" style="width:100%;aspect-ratio:3/4;">
                                                @if($item->foto)
                                                    <img src="{{ asset('storage/' . $item->foto) }}" alt="Foto {{ $item->nama }}" class="w-100 h-100" style="object-fit:cover;">
                                                @else
                                                    <div class="bg-light d-flex align-items-center justify-content-center text-muted" style="width:100%;height:100%;font-size:2rem;">
                                                        {{ mb_substr($item->nama, 0, 1) }}
                                                    </div>
                                                @endif
                                            </div>
                                            <h5 class="mb-1">{{ $item->nama }}</h5>
                                            <small class="text-muted">{{ $item->nip ?? '-' }}</small>
                                            <div class="mt-3 mb-3">
                                                @if($item->guru_id && $item->guru)
                                                    <span class="badge bg-info"><i class="ti ti-link"></i> {{ $item->guru->nama }}</span>
                                                @elseif($item->guru_id)
                                                    <span class="badge bg-warning"><i class="ti ti-alert-triangle"></i> Guru (ID: {{ $item->guru_id }})</span>
                                                @else
                                                    <span class="badge bg-light text-dark border">Input Manual</span>
                                                @endif
                                            </div>
                                            <div class="mb-3">
                                                <div><strong>Email:</strong> {{ $item->email ?? '-' }}</div>
                                                <div><strong>Telepon:</strong> {{ $item->telepon ?? '-' }}</div>
                                            </div>
                                            <div class="mt-auto d-flex justify-content-between align-items-center">
                                                <span class="badge bg-{{ $item->is_active ? 'success' : 'secondary' }}">{{ $item->is_active ? 'Aktif' : 'Tidak Aktif' }}</span>
                                                <div class="btn-group" role="group">
                                                    <a href="{{ route('guru_bk.show', $item->id) }}" class="btn btn-sm btn-info">
                                                        <i class="ti ti-eye"></i>
                                                    </a>
                                                    <a href="{{ route('guru_bk.edit', $item->id) }}" class="btn btn-sm btn-warning">
                                                        <i class="ti ti-edit"></i>
                                                    </a>
                                                    <form action="{{ route('guru_bk.destroy', $item->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Yakin ingin menghapus data ini?')">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="btn btn-sm btn-danger">
                                                            <i class="ti ti-trash"></i>
                                                        </button>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

@push('js')
<script>
function setBkView(mode) {
    const listView = document.getElementById('bkListViewContainer');
    const gridView = document.getElementById('bkGridViewContainer');
    const listBtn = document.getElementById('bkListViewBtn');
    const gridBtn = document.getElementById('bkGridViewBtn');
    if (!listView || !gridView || !listBtn || !gridBtn) return;
    if (mode === 'grid') {
        listView.classList.add('d-none');
        gridView.classList.remove('d-none');
        gridBtn.classList.remove('btn-outline-secondary');
        gridBtn.classList.add('btn-primary');
        listBtn.classList.remove('btn-primary');
        listBtn.classList.add('btn-outline-secondary');
    } else {
        gridView.classList.add('d-none');
        listView.classList.remove('d-none');
        listBtn.classList.remove('btn-outline-secondary');
        listBtn.classList.add('btn-primary');
        gridBtn.classList.remove('btn-primary');
        gridBtn.classList.add('btn-outline-secondary');
    }
}

document.addEventListener('DOMContentLoaded', function() {
    const listBtn = document.getElementById('bkListViewBtn');
    const gridBtn = document.getElementById('bkGridViewBtn');
    if (listBtn) listBtn.addEventListener('click', function() { setBkView('list'); });
    if (gridBtn) gridBtn.addEventListener('click', function() { setBkView('grid'); });
    setBkView('list');
});
</script>
@endpush
@endsection
