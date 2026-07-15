@extends('layouts.app', ['pageSlug' => 'kegiatan'])

@section('title','Detail Kegiatan')

@section('content')
<div class="row">
    <div class="col-md-8 mx-auto">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h4 class="card-title fw-semibold m-0">Detail Kegiatan</h4>
                <a href="{{ route('kegiatan.index') }}" class="btn btn-secondary btn-sm">Kembali</a>
            </div>
            <div class="card-body">
                <table class="table table-borderless">
                    <tr>
                        <td style="width: 30%"><strong>Nama Kegiatan</strong></td>
                        <td>{{ $kegiatan->nama_kegiatan }}</td>
                    </tr>
                    <tr>
                        <td><strong>Kode Kegiatan</strong></td>
                        <td>{{ $kegiatan->kode_kegiatan ?? '-' }}</td>
                    </tr>
                    <tr>
                        <td><strong>Kategori</strong></td>
                        <td>{{ $kegiatan->kategori }}</td>
                    </tr>
                </table>

                <div class="mt-4">
                    <a href="{{ route('kegiatan.edit', $kegiatan->id) }}" class="btn btn-primary">
                        <i class="ti ti-edit me-2"></i>Edit
                    </a>
                    <button type="button" class="btn btn-danger" onclick="confirmDelete({{ $kegiatan->id }})">
                        <i class="ti ti-trash me-2"></i>Hapus
                    </button>
                    <a href="{{ route('kegiatan.index') }}" class="btn btn-secondary">
                        <i class="ti ti-arrow-left me-2"></i>Kembali
                    </a>
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
