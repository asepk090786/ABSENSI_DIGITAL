@extends('layouts.app', ['pageSlug' => 'kegiatan'])

@section('title','Kegiatan Sekolah')

@section('content')
<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header">
                <div class="row align-items-center">
                    <div class="col">
                        <h4 class="card-title mb-0">Data Kegiatan Sekolah</h4>
                    </div>
                    <div class="col-auto">
                        <div class="btn-list">
                            <a href="javascript:history.back()" class="btn btn-outline-secondary btn-sm me-2">
                                <i class="ti ti-arrow-left me-1"></i>Back
                            </a>
                            <a href="{{ route('kegiatan.create') }}" class="btn btn-primary btn-sm">
                                <i class="ti ti-plus me-1"></i>Tambah Kegiatan
                            </a>
                        </div>
                    </div>
                </div>
            </div>
            <div class="card-body">
                @if(session('success'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <i class="ti ti-check me-2"></i>{{ session('success') }}
                        <button type="button" class="close" data-dismiss="alert"><span aria-hidden="true">&times;</span></button>
                    </div>
                @endif

                @if(session('error'))
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <i class="ti ti-alert-circle me-2"></i>{{ session('error') }}
                        <button type="button" class="close" data-dismiss="alert"><span aria-hidden="true">&times;</span></button>
                    </div>
                @endif

                <div class="table-responsive">
                    <table class="table table-striped table-hover">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Nama Kegiatan</th>
                                <th>Kode</th>
                                <th>Kategori</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                        @forelse($items as $index => $it)
                            <tr>
                                <td>{{ $index + 1 }}</td>
                                <td>
                                    <a href="{{ route('kegiatan.show', $it->id) }}" class="text-decoration-none">{{ $it->nama_kegiatan }}</a>
                                </td>
                                <td>{{ $it->kode_kegiatan ?? '-' }}</td>
                                <td>{{ $it->kategori ?? '-' }}</td>
                                <td>
                                    <div class="btn-list">
                                        <a href="{{ route('kegiatan.edit', $it->id) }}" class="btn btn-sm btn-outline-primary">
                                            <i class="ti ti-edit"></i>
                                        </a>
                                        <button type="button" class="btn btn-sm btn-outline-danger" onclick="confirmDelete({{ $it->id }})">
                                            <i class="ti ti-trash"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center text-muted">
                                    <i class="ti ti-info-circle me-2"></i>Belum ada kegiatan.
                                </td>
                            </tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<form id="deleteForm" method="POST" style="display: none;">
    @csrf
    @method('DELETE')
</form>

@endsection

@push('scripts')
<script>
function confirmDelete(id) {
    if (confirm('Apakah Anda yakin ingin menghapus kegiatan ini?')) {
        const form = document.getElementById('deleteForm');
        form.action = '{{ route("kegiatan.destroy", "") }}' + '/' + id;
        form.submit();
    }
}
</script>
@endpush
