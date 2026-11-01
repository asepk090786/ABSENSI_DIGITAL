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
                @if($errors->any())
                    <div class="alert alert-danger">
                        @foreach($errors->all() as $error)
                            <div>{{ $error }}</div>
                        @endforeach
                    </div>
                @endif
                @if($canGenerateCertificates)
                    <h5 class="fw-semibold mt-3">Sertifikat Kegiatan</h5>
                    @if($agendas->isEmpty())
                        <div class="alert alert-info">Belum ada agenda dengan peserta yang tercatat hadir.</div>
                    @else
                        <form method="POST" action="{{ route('ekskul.certificates.generate', $ekskul->id) }}" class="row g-3 mb-4">
                            @csrf
                            <div class="col-lg-5">
                                <label class="form-label" for="certificateAgenda">Agenda Kegiatan</label>
                                <select id="certificateAgenda" name="agenda_id" class="form-select" required>
                                    <option value="">-- Pilih agenda --</option>
                                    @foreach($agendas as $agenda)
                                        <option value="{{ $agenda->id }}" @selected((string) old('agenda_id') === (string) $agenda->id)>
                                            {{ $agenda->tanggal?->format('d/m/Y') }} - {{ $agenda->judul }} ({{ $agenda->peserta_hadir_count }} hadir)
                                        </option>
                                    @endforeach
                                </select>
                                <small class="form-text">Sertifikat dibuat untuk semua anggota yang hadir pada agenda terpilih.</small>
                            </div>
                            <div class="col-lg-3">
                                <label class="form-label" for="certificateTemplate">Template</label>
                                <select id="certificateTemplate" name="template_id" class="form-select">
                                    <option value="">Template default</option>
                                    @foreach($templates as $template)
                                        <option value="{{ $template->id }}" @selected((string) old('template_id') === (string) $template->id)>
                                            {{ $template->nama }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-lg-2">
                                <label class="form-label" for="certificateNumber">Nomor Sertifikat</label>
                                <input id="certificateNumber" name="nomor_sertifikat" type="text" class="form-control" maxlength="255" value="{{ old('nomor_sertifikat') }}">
                            </div>
                            <div class="col-lg-2 d-flex align-items-end">
                                <button type="submit" class="btn btn-primary w-100">
                                    <i class="ti ti-file-certificate me-1"></i>Buat Sertifikat
                                </button>
                            </div>
                        </form>
                    @endif
                @endif

                <h5 class="fw-semibold mt-4">Sertifikat Terbit</h5>
                <div class="table-responsive mb-4">
                    <table class="table table-vcenter table-hover">
                        <thead>
                            <tr><th>Kegiatan</th><th>Tanggal</th><th>Nama Siswa</th><th>Nomor Sertifikat</th><th>Dibuat</th><th>Aksi</th></tr>
                        </thead>
                        <tbody>
                            @forelse($certificates as $certificate)
                                <tr>
                                    <td>{{ $certificate->ekskulAgenda->judul ?? 'Sertifikat lama (keanggotaan)' }}</td>
                                    <td>{{ $certificate->ekskulAgenda?->tanggal?->format('d/m/Y') ?? '-' }}</td>
                                    <td>{{ $certificate->siswa->nama ?? '-' }}</td>
                                    <td>{{ $certificate->nomor_sertifikat ?? '-' }}</td>
                                    <td>{{ $certificate->created_at?->format('d/m/Y') ?? '-' }}</td>
                                    <td>
                                        <a href="{{ route('ekskul.certificates.download', [$ekskul->id, $certificate->id]) }}" class="btn btn-outline-success btn-sm" title="Unduh sertifikat" aria-label="Unduh sertifikat">
                                            <i class="ti ti-download"></i>
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="6" class="text-center text-muted">Belum ada sertifikat.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
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
                                <th width="220">Aksi</th>
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
                                    <div class="btn-group btn-group-sm">
                                        <a href="{{ route('ekskul.anggota.show', [$ekskul->id, $a->id]) }}" class="btn btn-outline-info" title="Lihat anggota">
                                            <i class="ti ti-eye"></i>
                                        </a>
                                        <a href="{{ route('ekskul.anggota.edit', [$ekskul->id, $a->id]) }}" class="btn btn-outline-primary" title="Edit anggota">
                                            <i class="ti ti-edit"></i>
                                        </a>
                                        <form method="POST" action="{{ route('ekskul.anggota.destroy', [$ekskul->id, $a->id]) }}" class="d-inline" onsubmit="return confirm('Yakin hapus anggota ini dari ekstrakurikuler?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-outline-danger" title="Hapus anggota">
                                                <i class="ti ti-trash"></i>
                                            </button>
                                        </form>
                                    </div>
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

        fetch('{{ route('ekskul.get-siswa', $ekskul->id) }}?kelas_id=' + kelasId)
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
                    var sNama = s.nama || '';
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