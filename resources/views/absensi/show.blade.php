@extends('layouts.app')

@section('title', 'Detail Absensi Kelas')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header border-0 pt-3 pb-2">
                    <div class="d-flex justify-content-between align-items-center">
                        <h3 class="card-title fw-semibold m-0">Detail Absensi Kelas</h3>
                        <div>
                            <a href="{{ route('absensi.edit', $absensi->id) }}" class="btn btn-warning">
                                <i class="ti ti-edit me-1"></i> Edit
                            </a>
                            <a href="{{ route('absensi.index') }}" class="btn btn-secondary">
                                <i class="ti ti-arrow-left me-1"></i> Kembali
                            </a>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <table class="table table-borderless">
                                <tr>
                                    <th width="180">Tanggal</th>
                                    <td>: {{ $absensi->tanggal->format('d/m/Y') }}</td>
                                </tr>
                                <tr>
                                    <th>Kelas</th>
                                    <td>: {{ $absensi->kelas->nama_kelas ?? '-' }}</td>
                                </tr>
                                <tr>
                                    <th>Guru</th>
                                    <td>: {{ $absensi->guru->nama ?? '-' }} ({{ $absensi->guru->kode_guru ?? '-' }})</td>
                                </tr>
                                <tr>
                                    <th>Jam Belajar</th>
                                    <td>: Jam ke-{{ $absensi->jamBelajar->urutan ?? '-' }} ({{ $absensi->jamBelajar->jam_mulai ?? '-' }} - {{ $absensi->jamBelajar->jam_selesai ?? '-' }})</td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <table class="table table-borderless">
                                <tr>
                                    <th width="180">Status Kelas</th>
                                    <td>: {{ $absensi->status_kelas ?? '-' }}</td>
                                </tr>
                                <tr>
                                    <th>Tahun Ajaran</th>
                                    <td>: {{ $absensi->tahunAjaran->nama_tahun ?? '-' }}</td>
                                </tr>
                                <tr>
                                    <th>Semester</th>
                                    <td>: {{ $absensi->semester->nama_semester ?? '-' }}</td>
                                </tr>
                                <tr>
                                    <th>Jumlah Siswa</th>
                                    <td>: <span class="badge bg-info">{{ $absensi->absensiSiswa->count() }} Siswa</span></td>
                                </tr>
                            </table>
                        </div>
                    </div>

                    @if($absensi->absensiSiswa->isNotEmpty())
                    <hr>
                    <h5 class="mb-2">Daftar Absensi Siswa</h5>
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped">
                            <thead class="table-light">
                                <tr>
                                    <th>No</th>
                                    <th>NIS</th>
                                    <th>Nama Siswa</th>
                                    <th>Status</th>
                                    <th>Keterangan</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($absensi->absensiSiswa as $index => $abs)
                                <tr>
                                    <td>{{ $index + 1 }}</td>
                                    <td>{{ $abs->siswa->nis ?? '-' }}</td>
                                    <td>{{ $abs->siswa->nama ?? '-' }}</td>
                                    <td>
                                        @php $statusKey = strtolower((string) $abs->status); @endphp
                                        <span class="status-badge status-{{ $abs->siswa->id }}">
                                        @if($statusKey === 'hadir')
                                            <span class="badge bg-success">Hadir</span>
                                        @elseif($statusKey === 'terlambat' || $statusKey === 'telat')
                                            <span class="badge" style="background:#f59e0b;color:#fff;">Terlambat</span>
                                        @elseif($statusKey === 'sakit')
                                            <span class="badge bg-warning">Sakit</span>
                                        @elseif($statusKey === 'izin' || $statusKey === 'ijin')
                                            <span class="badge bg-info">Izin</span>
                                        @elseif(in_array($statusKey, ['alpha', 'alpa', 'alfa', 'absen'], true))
                                            <span class="badge bg-danger">Alpa</span>
                                        @else
                                            <span class="badge bg-secondary">{{ $abs->status }}</span>
                                        @endif
                                        </span>
                                    </td>
                                    <td class="keterangan-{{ $abs->siswa->id }}">
                                        {{ $abs->keterangan ?? '-' }}
                                        @php
                                            $activeIzinForStudent = $activeIzinKegiatan->get($abs->siswa->id, collect());
                                            $izinWithSurat = $activeIzinForStudent->first(fn($izin) => !empty($izin->surat_tugas));
                                        @endphp
                                        @if($izinWithSurat)
                                            <br><a href="{{ asset('storage/' . $izinWithSurat->surat_tugas) }}" target="_blank" class="btn btn-sm btn-outline-primary mt-1"><i class="ti ti-file-text me-1"></i>Lihat Surat</a>
                                        @endif
                                    </td>
                                    <td>
                                        @php
                                            $hasActiveDispensasi = false;
                                            $activeIzinForStudent = $activeIzinKegiatan->get($abs->siswa->id, collect());
                                            if ($activeIzinForStudent->isNotEmpty()) {
                                                $hasActiveDispensasi = $activeIzinForStudent->contains(fn($izin) => $izin->jenis_kegiatan === 'dispensasi');
                                            }
                                        @endphp
                                        @if(auth()->user()->guru_id)
                                            @if(($isGuruPiket ?? false) || auth()->user()->hasAnyRole(['Admin','Kepala Sekolah']))
                                                @if($hasActiveDispensasi && !auth()->user()->hasAnyRole(['Admin','Kepala Sekolah']))
                                                    <span class="text-muted">Terkunci</span>
                                                @else
                                                    <form method="POST" action="{{ route('absensi.siswa.update_status', ['absensi' => $absensi->id, 'siswa' => $abs->siswa->id]) }}" class="d-flex gap-2 align-items-center ajax-update-absensi" data-siswa-id="{{ $abs->siswa->id }}">
                                                        @csrf
                                                        <select name="status" class="form-select form-select-sm">
                                                            <option value="hadir" {{ strtolower($abs->status) === 'hadir' ? 'selected' : '' }}>Hadir</option>
                                                            <option value="terlambat" {{ in_array(strtolower($abs->status), ['terlambat','telat']) ? 'selected' : '' }}>Terlambat</option>
                                                            <option value="sakit" {{ strtolower($abs->status) === 'sakit' ? 'selected' : '' }}>Sakit</option>
                                                            <option value="izin" {{ in_array(strtolower($abs->status), ['izin','ijin']) ? 'selected' : '' }}>Izin</option>
                                                            <option value="alpa" {{ in_array(strtolower($abs->status), ['alpa','alpha','alfa','absen']) ? 'selected' : '' }}>Alpa</option>
                                                        </select>
                                                        <input type="text" name="keterangan" class="form-control form-control-sm" placeholder="Keterangan" value="{{ $abs->keterangan ?? '' }}">
                                                        <button type="submit" class="btn btn-sm btn-primary btn-save">Simpan</button>
                                                    </form>
                                                @endif
                                            @else
                                                <button
                                                    type="button"
                                                    class="btn btn-sm btn-outline-danger btn-lapor-siswa"
                                                    data-bs-toggle="modal"
                                                    data-bs-target="#modalLaporanSiswa"
                                                    data-siswa-id="{{ $abs->siswa->id ?? '' }}"
                                                    data-siswa-nama="{{ $abs->siswa->nama ?? '-' }}"
                                                >
                                                    <i class="ti ti-message-report"></i> Lapor
                                                </button>
                                            @endif
                                        @else
                                            -
                                        @endif
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    @else
                    <div class="alert alert-info">
                        <i class="ti ti-info-circle me-2"></i>
                        Belum ada data absensi siswa untuk kelas ini.
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

@if(auth()->user()->guru_id)
<div class="modal fade" id="modalLaporanSiswa" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="{{ route('absensi.laporan-siswa.store', $absensi->id) }}">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Laporan ke Wali Kelas & Guru BK</h5>
                    <button type="button" class="close" data-bs-dismiss="modal"><span aria-hidden="true">&times;</span></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="siswa_id" id="lapor_siswa_id">
                    <div class="mb-2">
                        <label class="form-label">Nama Siswa</label>
                        <input type="text" id="lapor_siswa_nama" class="form-control" readonly>
                    </div>
                    <div class="mb-2">
                        <label class="form-label">Permasalahan <span class="text-danger">*</span></label>
                        <textarea name="deskripsi_permasalahan" class="form-control" rows="4" required placeholder="Tuliskan permasalahan yang ditemukan pada siswa..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="ti ti-send me-1"></i>Kirim Laporan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    // Handler for Report modal
    document.querySelectorAll('.btn-lapor-siswa').forEach(function (button) {
        button.addEventListener('click', function () {
            document.getElementById('lapor_siswa_id').value = this.getAttribute('data-siswa-id') || '';
            document.getElementById('lapor_siswa_nama').value = this.getAttribute('data-siswa-nama') || '-';
        });
    });

    // Intercept inline update forms (AJAX)
    document.querySelectorAll('form.ajax-update-absensi').forEach(function(form){
        form.addEventListener('submit', function(e){
            e.preventDefault();
            var url = form.getAttribute('action');
            var data = new FormData(form);
            var submitBtn = form.querySelector('button[type="submit"]');
            var originalBtnHtml = submitBtn ? submitBtn.innerHTML : null;
            if (submitBtn) {
                submitBtn.disabled = true;
                submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>...';
            }

            fetch(url, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Accept': 'application/json'
                },
                body: data
            }).then(function(resp){
                return resp.json();
            }).then(function(json){
                if (submitBtn) submitBtn.disabled = false;
                if (json.success) {
                    showToast('Sukses', json.message || 'Perubahan tersimpan');
                    // update DOM: badge and keterangan
                    var siswaId = form.getAttribute('data-siswa-id');
                    if (siswaId) {
                        var badgeContainer = document.querySelector('.status-' + siswaId);
                        if (badgeContainer) {
                            // replace badge HTML based on selected status
                            var selectedStatus = form.querySelector('select[name="status"]').value;
                            var newBadge = '';
                            switch (selectedStatus.toLowerCase()) {
                                case 'hadir': newBadge = '<span class="badge bg-success">Hadir</span>'; break;
                                case 'terlambat': newBadge = '<span class="badge" style="background:#f59e0b;color:#fff;">Terlambat</span>'; break;
                                case 'sakit': newBadge = '<span class="badge bg-warning">Sakit</span>'; break;
                                case 'izin': newBadge = '<span class="badge bg-info">Izin</span>'; break;
                                default: newBadge = '<span class="badge bg-danger">Alpa</span>'; break;
                            }
                            badgeContainer.innerHTML = newBadge;
                        }
                        var ketCell = document.querySelector('.keterangan-' + siswaId);
                        if (ketCell) {
                            var newKet = form.querySelector('input[name="keterangan"]').value || '-';
                            ketCell.innerText = newKet;
                        }
                    }

                    if (json.deleted_pelanggaran && json.deleted_pelanggaran > 0) {
                        showToast('Informasi', 'Pelanggaran terkait telah dihapus: ' + json.deleted_pelanggaran, 'info');
                    }
                } else {
                    showToast('Gagal', json.message || 'Gagal menyimpan perubahan', 'danger');
                }
            }).catch(function(err){
                if (submitBtn) {
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = originalBtnHtml;
                }
                showToast('Error', 'Terjadi kesalahan jaringan', 'danger');
                console.error(err);
            });
        });
    });

    function showToast(title, message, type = 'success'){
        var bg = (type === 'danger') ? 'bg-danger' : (type === 'info' ? 'bg-info' : 'bg-success');
        var toast = document.createElement('div');
        toast.className = 'toast align-items-center text-white ' + bg;
        toast.setAttribute('role','alert');
        toast.style.position = 'fixed';
        toast.style.right = '20px';
        toast.style.top = (20 + (document.querySelectorAll('.toast').length * 60)) + 'px';
        toast.style.zIndex = 99999;
        toast.style.minWidth = '220px';
        toast.innerHTML = '<div class="d-flex"><div class="toast-body"><strong>' + title + ':</strong> ' + message + '</div><button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button></div>';
        document.body.appendChild(toast);
        var bsToast = new bootstrap.Toast(toast, { delay: 4000 });
        bsToast.show();
        toast.addEventListener('hidden.bs.toast', function(){ toast.remove(); });
    }
});
</script>
@endif
@endsection
