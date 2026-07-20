@extends('layouts.app', ['pageSlug' => 'wali-kelas'])

@section('title', 'Data Siswa - Wali Kelas')

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <div>
                        <h4 class="card-title fw-semibold mb-1">
                            <i class="ti ti-users me-2"></i>Data Siswa
                        </h4>
                        <p class="text-muted mb-0">Kelas: <strong>{{ $kelasBinaan->nama_kelas ?? '-' }}</strong></p>
                    </div>
                    <a href="{{ route('wali_kelas.index') }}" class="btn btn-secondary">
                        <i class="ti ti-arrow-left me-1"></i>Kembali
                    </a>
                </div>
                <div class="card-body">
                    @if($siswa->isEmpty())
                        <div class="alert alert-info">
                            <i class="ti ti-info-circle me-2"></i>Belum ada siswa di kelas ini.
                        </div>
                    @else
                        <div class="row mb-3">
                            <div class="col-md-4">
                                <label for="jabatanFilter" class="form-label">Filter Jabatan</label>
                                <select id="jabatanFilter" class="form-select form-select-sm">
                                    <option value="">Semua Jabatan</option>
                                    <option value="ketua">Ketua Kelas</option>
                                    <option value="wakil">Wakil Ketua Kelas</option>
                                    <option value="sekretaris">Sekretaris</option>
                                    <option value="bendahara">Bendahara</option>
                                </select>
                            </div>
                            <div class="col-md-4 d-flex align-items-end">
                                <button id="clearJabatanFilter" type="button" class="btn btn-sm btn-outline-secondary">Reset Filter</button>
                            </div>
                        </div>
                        <div class="table-responsive">
                            <table id="waliKelasSiswaTable" class="table table-vcenter table-hover table-tabler">
                                <thead>
                                    <tr>
                                        <th width="5%">No</th>
                                        <th>NIS</th>
                                        <th>NISN</th>
                                        <th>Nama Siswa</th>
                                        <th>Jenis Kelamin</th>
                                        <th>Email</th>
                                        <th>Jabatan</th>
                                        <th>Status</th>
                                        <th width="12%">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($siswa as $index => $s)
                                    <tr>
                                        <td>{{ $index + 1 }}</td>
                                        <td>{{ $s->nis ?? '-' }}</td>
                                        <td>{{ $s->nisn ?? '-' }}</td>
                                        <td>{{ $s->nama ?? '-' }}</td>
                                        <td>{{ $s->jenis_kelamin ?? '-' }}</td>
                                        <td>{{ $s->email ?? '-' }}</td>
                                        <td class="jabatan-cell" data-jabatan="{{ $s->jabatan_kelas }}">
                                            @if($s->jabatan_kelas === 'ketua')
                                                Ketua Kelas
                                            @elseif($s->jabatan_kelas === 'wakil')
                                                Wakil Ketua Kelas
                                            @elseif($s->jabatan_kelas === 'sekretaris')
                                                Sekretaris
                                            @elseif($s->jabatan_kelas === 'bendahara')
                                                Bendahara
                                            @else
                                                -
                                            @endif
                                        </td>
                                        <td>{{ ($s->status_aktif ?? 0) ? 'Aktif' : 'Nonaktif' }}</td>
                                        <td>
                                            <a href="{{ route('siswa.edit', $s->id) }}" class="btn btn-sm btn-outline-primary me-1" title="Edit Data Siswa">
                                                <i class="ti ti-edit"></i>
                                            </a>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <div class="mt-2">
                            <p class="text-muted mb-0">
                                <strong>Total Siswa:</strong> {{ $siswa->count() }} siswa
                            </p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const filter = document.getElementById('jabatanFilter');
    const clearFilter = document.getElementById('clearJabatanFilter');
    const table = document.getElementById('waliKelasSiswaTable');

    if (!filter || !table) {
        return;
    }

    const rows = Array.from(table.querySelectorAll('tbody tr'));

    function updateFilter() {
        const selected = filter.value;

        rows.forEach(row => {
            const jabatanCell = row.querySelector('.jabatan-cell');
            const jabatan = jabatanCell ? jabatanCell.dataset.jabatan || '' : '';
            const match = selected === '' || jabatan === selected;

            row.style.display = match ? '' : 'none';
            row.classList.toggle('table-success', selected !== '' && match && jabatan !== '');
        });
    }

    filter.addEventListener('change', updateFilter);
    clearFilter.addEventListener('click', function () {
        filter.value = '';
        updateFilter();
    });
});
</script>
@endpush


