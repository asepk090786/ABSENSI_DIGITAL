@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h3 class="card-title">Data Guru Piket</h3>
                    @if(auth()->check() && auth()->user()->role && auth()->user()->role->role_name === 'Admin')
                    <div class="d-flex gap-2">
                        <form action="{{ route('guru_piket.generate') }}" method="POST" onsubmit="return confirm('Generate jadwal piket otomatis? Guru akan ditugaskan hanya pada hari tanpa jadwal KBM.');">
                            @csrf
                            <button type="submit" class="btn btn-success">
                                <i class="ti ti-rotate-cw"></i> Generate Jadwal Piket Otomatis
                            </button>
                        </form>
                        <a href="{{ route('guru_piket.create') }}" class="btn btn-primary">
                            <i class="ti ti-plus"></i> Tambah Guru Piket
                        </a>
                        <a href="{{ route('guru_piket.download') }}" class="btn btn-info">
                            <i class="ti ti-download"></i> Download Jadwal Piket
                        </a>
                    </div>
                    @endif
                </div>
                <div class="card-body">
                    @if(session('success'))
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            {{ session('success') }}
                            <button type="button" class="close" data-bs-dismiss="alert"><span aria-hidden="true">&times;</span></button>
                        </div>
                    @endif

                    @if(! $hasAny)
                        <div class="alert alert-info">
                            <i class="ti ti-info-circle"></i> Belum ada data Guru Piket.
                        </div>
                    @else
                        <form id="bulkDeleteForm" action="{{ route('guru_piket.bulk_destroy') }}" method="POST">
                            @csrf

                            <div class="row mb-3">
                                <div class="col-md-4">
                                    <input type="text" id="filterNama" class="form-control form-control-sm" placeholder="Cari nama guru...">
                                </div>
                                <div class="col-md-3">
                                    <select id="filterStatus" class="form-select form-select-sm">
                                        <option value="">Semua Status</option>
                                        <option value="1">Aktif</option>
                                        <option value="0">Tidak Aktif</option>
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <select id="filterHari" class="form-select form-select-sm">
                                        <option value="">Semua Hari Piket</option>
                                        @foreach($workDays as $hari)
                                            <option value="{{ $hari }}">{{ $hari }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-2 d-flex align-items-end">
                                    @if(auth()->check() && auth()->user()->role && auth()->user()->role->role_name === 'Admin')
                                    <button type="submit" id="btnHapusTerpilih" class="btn btn-sm btn-danger d-none">
                                        <i class="ti ti-trash me-1"></i> Hapus Terpilih (<span id="selectedCount">0</span>)
                                    </button>
                                    @endif
                                </div>
                            </div>

                            <div class="mb-4">
                                <h5 class="mb-2"><i class="ti ti-calendar me-1"></i> Preview Jadwal Piket</h5>
                                <div class="table-responsive">
                                    <table class="table table-bordered table-hover table-sm" id="previewTable">
                                        <thead class="table-light">
                                            <tr>
                                                @if(auth()->check() && auth()->user()->role && auth()->user()->role->role_name === 'Admin')
                                                <th class="text-center" style="width: 40px;">
                                                    <input type="checkbox" id="selectAll" class="form-check-input" onchange="toggleSelectAll(this)">
                                                </th>
                                                @endif
                                                <th class="text-center" style="width: 50px;">No</th>
                                                <th style="width: 60px;">Foto</th>
                                                <th>Nama</th>
                                                <th>NIP</th>
                                                <th>Email</th>
                                                <th>Telepon</th>
                                                <th>Hari Piket</th>
                                                <th class="text-center" style="width: 80px;">Status</th>
                                                <th class="text-center" style="width: 100px;">Aksi</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @php $no = 0; @endphp
                                            @foreach($gurupiket as $item)
                                                @php
                                                    $hariPiket = collect((array) ($item->hari_piket ?? []))
                                                        ->sort()
                                                        ->values()
                                                        ->all();
                                                @endphp
                                                <tr data-nama="{{ strtolower($item->nama) }}" data-status="{{ $item->is_active ? '1' : '0' }}" data-hari="{{ implode(',', $hariPiket) }}">
                                                    @if(auth()->check() && auth()->user()->role && auth()->user()->role->role_name === 'Admin')
                                                    <td class="text-center">
                                                        <input type="checkbox" name="ids[]" value="{{ $item->id }}" class="form-check-input row-checkbox" onchange="updateBulkDeleteButton()">
                                                    </td>
                                                    @endif
                                                    <td class="text-center">{{ ++$no }}</td>
                                                    <td>
                                                        @php
                                                            $fotoPath = $item->foto ?: ($item->user->foto ?? null);
                                                        @endphp
                                                        @if($fotoPath)
                                                            <img src="{{ asset('storage/' . $fotoPath) }}" alt="Foto {{ $item->nama }}" class="rounded" style="width: 40px; height: 40px; object-fit: cover;">
                                                        @else
                                                            <div class="bg-light rounded d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                                                                <i class="ti ti-user" style="font-size: 18px; color: #999;"></i>
                                                            </div>
                                                        @endif
                                                    </td>
                                                    <td><strong>{{ $item->nama }}</strong></td>
                                                    <td>{{ $item->nip ?? '-' }}</td>
                                                    <td>{{ $item->email ?? '-' }}</td>
                                                    <td>{{ $item->telepon ?? '-' }}</td>
                                                    <td>
                                                        @if(!empty($hariPiket))
                                                            @foreach($hariPiket as $hari)
                                                                <span class="badge bg-info text-white me-1 mb-1">{{ $hari }}</span>
                                                            @endforeach
                                                        @else
                                                            <span class="text-muted">-</span>
                                                        @endif
                                                    </td>
                                                    <td class="text-center">
                                                        @if($item->is_active)
                                                            <span class="badge bg-success">Aktif</span>
                                                        @else
                                                            <span class="badge bg-secondary">Tidak Aktif</span>
                                                        @endif
                                                    </td>
                                                    <td class="text-center">
                                                        <div class="btn-group" role="group">
                                                            <a href="{{ route('guru_piket.show', $item->id) }}" class="btn btn-sm btn-info action-btn" title="Lihat">
                                                                <i class="ti ti-eye"></i>
                                                            </a>
                                                            @if(auth()->check() && auth()->user()->role && auth()->user()->role->role_name === 'Admin')
                                                            <a href="{{ route('guru_piket.edit', $item->id) }}" class="btn btn-sm btn-warning action-btn" title="Edit">
                                                                <i class="ti ti-edit"></i>
                                                            </a>
                                                            <button type="button" class="btn btn-sm btn-danger action-btn" title="Hapus" onclick="confirmRowDelete('{{ route('guru_piket.destroy', $item->id) }}')">
                                                                <i class="ti ti-trash"></i>
                                                            </button>
                                                            @endif
                                                        </div>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </form>

                        <hr class="my-4">

                        <h5 class="mb-3"><i class="ti ti-list me-1"></i> Jadwal Piket per Hari</h5>
                        @foreach($workDays as $hari)
                            <div class="mb-4">
                                <h5 class="mb-2">{{ $hari }}</h5>
                                @php
                                    $items = $guruByHari[$hari] ?? collect();
                                @endphp
                                @if($items->isEmpty())
                                    <div class="alert alert-light border">
                                        Tidak ada guru piket untuk hari {{ $hari }}.
                                    </div>
                                @else
                                    <div class="table-responsive">
                                        <table class="table table-vcenter table-hover table-tabler">
                                            <thead>
                                                <tr>
                                                    <th>No</th>
                                                    <th>Foto</th>
                                                    <th>Nama</th>
                                                    <th>NIP</th>
                                                    <th>Email</th>
                                                    <th>Telepon</th>
                                                    <th>Status</th>
                                                    <th>Aksi</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($items as $index => $item)
                                                    <tr>
                                                        <td>{{ $index + 1 }}</td>
                                                        <td>
                                                            @php
                                                                $fotoPath = $item->foto ?: ($item->user->foto ?? null);
                                                            @endphp
                                                            @if($fotoPath)
                                                                <img src="{{ asset('storage/' . $fotoPath) }}" alt="Foto {{ $item->nama }}" class="rounded" style="width: 50px; height: 50px; object-fit: cover;">
                                                            @else
                                                                <div class="bg-light rounded d-flex align-items-center justify-content-center" style="width: 50px; height: 50px;">
                                                                    <i class="ti ti-user" style="font-size: 24px; color: #999;"></i>
                                                                </div>
                                                            @endif
                                                        </td>
                                                        <td>{{ $item->nama }}</td>
                                                        <td>{{ $item->nip ?? '-' }}</td>
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
                                                                <a href="{{ route('guru_piket.show', $item->id) }}" class="btn btn-sm btn-info action-btn">
                                                                    <i class="ti ti-eye"></i>
                                                                </a>
                                                                @if(auth()->check() && auth()->user()->role && auth()->user()->role->role_name === 'Admin')
                                                                <a href="{{ route('guru_piket.edit', $item->id) }}" class="btn btn-sm btn-warning action-btn">
                                                                    <i class="ti ti-edit"></i>
                                                                </a>
                                                                <form action="{{ route('guru_piket.destroy', $item->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Yakin ingin menghapus data ini?')">
                                                                    @csrf
                                                                    @method('DELETE')
                                                                    <button type="submit" class="btn btn-sm btn-danger action-btn">
                                                                        <i class="ti ti-trash"></i>
                                                                    </button>
                                                                </form>
                                                                @endif
                                                            </div>
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                @endif
                            </div>
                        @endforeach
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
<script>
    function toggleSelectAll(checkbox) {
        document.querySelectorAll('.row-checkbox').forEach(function(cb) {
            cb.checked = checkbox.checked;
        });
        updateBulkDeleteButton();
    }

    function updateBulkDeleteButton() {
        const checked = document.querySelectorAll('.row-checkbox:checked');
        const btn = document.getElementById('btnHapusTerpilih');
        const count = document.getElementById('selectedCount');
        count.textContent = checked.length;
        if (checked.length > 0) {
            btn.classList.remove('d-none');
        } else {
            btn.classList.add('d-none');
        }
    }

    document.addEventListener('DOMContentLoaded', function() {
        const filterNama = document.getElementById('filterNama');
        const filterStatus = document.getElementById('filterStatus');
        const filterHari = document.getElementById('filterHari');
        const selectAll = document.getElementById('selectAll');
        const bulkForm = document.getElementById('bulkDeleteForm');
        const rows = document.querySelectorAll('#previewTable tbody tr');

        function applyFilters() {
            const nama = filterNama.value.toLowerCase().trim();
            const status = filterStatus.value;
            const hari = filterHari.value;

            rows.forEach(function(row) {
                const rowNama = row.getAttribute('data-nama') || '';
                const rowStatus = row.getAttribute('data-status') || '';
                const rowHari = row.getAttribute('data-hari') || '';

                const matchNama = nama === '' || rowNama.includes(nama);
                const matchStatus = status === '' || rowStatus === status;
                const matchHari = hari === '' || rowHari.split(',').includes(hari);

                row.style.display = (matchNama && matchStatus && matchHari) ? '' : 'none';
            });

            if (selectAll) selectAll.checked = false;
            updateBulkDeleteButton();
        }

        if (filterNama) filterNama.addEventListener('input', applyFilters);
        if (filterStatus) filterStatus.addEventListener('change', applyFilters);
        if (filterHari) filterHari.addEventListener('change', applyFilters);

        if (bulkForm) {
            bulkForm.addEventListener('submit', function(e) {
                const checked = document.querySelectorAll('.row-checkbox:checked');
                if (checked.length === 0) {
                    e.preventDefault();
                    alert('Pilih minimal satu data untuk dihapus.');
                    return false;
                }

                if (!confirm('Yakin ingin menghapus ' + checked.length + ' data Guru Piket yang dipilih?')) {
                    e.preventDefault();
                    return false;
                }
            });
        }
    });
    function confirmRowDelete(actionUrl) {
        if (confirm('Yakin ingin menghapus data ini?')) {
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = actionUrl;
            form.style.display = 'none';

            const tokenInput = document.createElement('input');
            tokenInput.type = 'hidden';
            tokenInput.name = '_token';
            tokenInput.value = '{{ csrf_token() }}';
            form.appendChild(tokenInput);

            const methodInput = document.createElement('input');
            methodInput.type = 'hidden';
            methodInput.name = '_method';
            methodInput.value = 'DELETE';
            form.appendChild(methodInput);

            document.body.appendChild(form);
            form.submit();
        }
    }
</script>

<style>
.action-btn {
    width: 34px;
    height: 34px;
    padding: 0;
    display: inline-flex;
    align-items: center;
    justify-content: center;
}
</style>
@endsection
