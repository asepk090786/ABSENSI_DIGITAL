@extends('layouts.app', ['pageSlug' => 'ekskul'])

@section('title', 'Kelola Anggota - ' . $ekskul->nama)

@section('content')
<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h4 class="card-title fw-semibold m-0">Kelola Anggota: {{ $ekskul->nama }}</h4>
                <div>
                    <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#tambahAnggotaModal">
                        <i class="ti ti-plus me-1"></i>Tambah Anggota
                    </button>
                    <a href="{{ route('ekskul.index') }}" class="btn btn-secondary btn-sm">Kembali</a>
                </div>
            </div>
            <div class="card-body">
                @if(session('success'))
                    <div class="alert alert-success alert-dismissible">{{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif
                @if(session('error'))
                    <div class="alert alert-danger alert-dismissible">{{ session('error') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif
                <div class="table-responsive">
                    <table class="table table-vcenter table-hover">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>NIS</th>
                                <th>Nama Siswa</th>
                                <th>Kelas</th>
                                <th>Tgl Daftar</th>
                                <th>Status</th>
                                <th width="180">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                        @forelse($anggota as $index => $a)
                            <tr>
                                <td>{{ $index + 1 }}</td>
                                <td>{{ $a->siswa->nis ?? '-' }}</td>
                                <td>{{ $a->siswa->nama ?? '-' }}</td>
                                <td>{{ $a->siswa->kelas->nama_kelas ?? '-' }}</td>
                                <td>{{ $a->tanggal_daftar ? \Carbon\Carbon::parse($a->tanggal_daftar)->format('d/m/Y') : '-' }}</td>
                                <td>
                                    @if($a->status_pendaftaran === 'diterima')
                                        <span class="badge bg-success">Diterima</span>
                                    @elseif($a->status_pendaftaran === 'ditolak')
                                        <span class="badge bg-danger">Ditolak</span>
                                    @else
                                        <span class="badge bg-warning">Pending</span>
                                    @endif
                                </td>
                                <td>
                                    @if($a->status_pendaftaran === 'pending')
                                    <form method="POST" action="{{ route('ekskul.anggota.status', $ekskul->id) }}" class="d-inline">
                                        @csrf
                                        <input type="hidden" name="siswa_id" value="{{ $a->siswa_id }}">
                                        <button type="submit" name="status" value="diterima" class="btn btn-success btn-sm">
                                            <i class="ti ti-check"></i> Terima
                                        </button>
                                        <button type="submit" name="status" value="ditolak" class="btn btn-danger btn-sm">
                                            <i class="ti ti-x"></i> Tolak
                                        </button>
                                    </form>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="7" class="text-center text-muted py-4">Belum ada pendaftar.</td></tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Tambah Anggota -->
<div class="modal fade" id="tambahAnggotaModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Tambah Anggota {{ $ekskul->nama }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="{{ route('ekskul.anggota.bulk', $ekskul->id) }}">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Pilih Kelas</label>
                        <select id="kelasFilter" class="form-select">
                            <option value="">-- Pilih Kelas --</option>
                            @foreach($kelasList as $kelas)
                                <option value="{{ $kelas->id }}">{{ $kelas->nama_kelas }} ({{ $kelas->tingkat_kelas ?? '' }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div id="siswaContainer" class="mb-3" style="max-height:400px;overflow-y:auto;">
                        <div class="text-muted text-center py-4">Pilih kelas untuk menampilkan daftar siswa.</div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary" id="btnTambahAnggota" disabled>
                        <i class="ti ti-plus me-1"></i>Tambah Anggota Terpilih
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('js')
<script>
document.addEventListener('DOMContentLoaded', function() {
    var kelasFilter = document.getElementById('kelasFilter');
    var siswaContainer = document.getElementById('siswaContainer');
    var btnTambah = document.getElementById('btnTambahAnggota');
    var existingIds = @json($existingSiswaIds);

    kelasFilter.addEventListener('change', function() {
        var kelasId = this.value;
        if (!kelasId) {
            siswaContainer.innerHTML = '<div class="text-muted text-center py-4">Pilih kelas untuk menampilkan daftar siswa.</div>';
            btnTambah.disabled = true;
            return;
        }

        siswaContainer.innerHTML = '<div class="text-center py-4"><i class="ti ti-loader spinner me-2"></i>Memuat data siswa...</div>';

        fetch('{{ route('absensi.get-siswa') }}?kelas_id=' + kelasId)
            .then(function(res){ return res.json(); })
            .then(function(data){
                var siswa = data.siswa || data.data || data || [];
                if (!Array.isArray(siswa) || siswa.length === 0) {
                    siswaContainer.innerHTML = '<div class="text-muted text-center py-4">Tidak ada siswa di kelas ini.</div>';
                    btnTambah.disabled = true;
                    return;
                }

                var html = '<div class="list-group">';
                siswa.forEach(function(s) {
                    var sId = s.id || s.siswa_id || '';
                    var sNis = s.nis || '';
                    var sNama = s.nama || s.nama_siswa || '';
                    var isExisting = existingIds.indexOf(sId) !== -1;
                    html += '<label class="list-group-item list-group-item-action d-flex align-items-center gap-3 ' + (isExisting ? 'bg-light text-muted' : '') + '">';
                    html += '<input type="checkbox" name="siswa_ids[]" value="' + sId + '" class="form-check-input siswa-check" ' + (isExisting ? 'disabled' : '') + '>';
                    html += '<div><strong>' + sNama + '</strong><br><small class="text-muted">NIS: ' + sNis + '</small></div>';
                    if (isExisting) html += '<span class="badge bg-secondary ms-auto">Sudah terdaftar</span>';
                    html += '</label>';
                });
                html += '</div>';
                siswaContainer.innerHTML = html;
                updateButtonState();
            })
            .catch(function(){
                siswaContainer.innerHTML = '<div class="text-danger text-center py-4">Gagal memuat data siswa.</div>';
            });
    });

    function updateButtonState() {
        var checked = document.querySelectorAll('.siswa-check:checked');
        btnTambah.disabled = checked.length === 0;
    }

    document.addEventListener('change', function(e) {
        if (e.target.classList.contains('siswa-check')) {
            updateButtonState();
        }
    });
});
</script>
@endpush