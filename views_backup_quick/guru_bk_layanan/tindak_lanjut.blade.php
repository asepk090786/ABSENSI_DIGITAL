@extends('layouts.app')

@section('content')
<div class="container-fluid">
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="close" data-dismiss="alert"><span aria-hidden="true">&times;</span></button>
        </div>
    @endif

    @if($errors->any())
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <ul class="mb-0">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
            <button type="button" class="close" data-dismiss="alert"><span aria-hidden="true">&times;</span></button>
        </div>
    @endif

    <div class="row mb-3">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h3 class="card-title mb-0">Menu Cepat Kelas Binaan - Tindak Lanjut BK</h3>
                    <a href="{{ route('guru_bk_layanan.menu', ['kelas' => $kelas->id]) }}" class="btn btn-secondary btn-sm">
                        <i class="ti ti-arrow-left"></i> Kembali ke Menu
                    </a>
                </div>
                <div class="card-body">
                    <div class="d-flex flex-wrap gap-2">
                        @foreach($kelasBinaan as $itemKelas)
                            <a href="{{ route('guru_bk_layanan.tindak_lanjut', ['kelas' => $itemKelas->id]) }}" class="btn {{ (int) $kelas->id === (int) $itemKelas->id ? 'btn-primary' : 'btn-outline-primary' }} btn-sm">
                                {{ $itemKelas->nama_kelas }}
                            </a>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h3 class="card-title mb-0">Ringkasan Siswa - {{ $kelas->nama_kelas }}</h3>
                </div>
                <div class="card-body">
                    @if(($ringkasanSiswa ?? collect())->isEmpty())
                        <div class="alert alert-light border mb-0">Belum ada data siswa pada kelas ini.</div>
                    @else
                        <div class="table-responsive">
                            <table class="table table-sm table-bordered align-middle">
                                <thead class="table-light">
                                    <tr>
                                        <th>No</th>
                                        <th>Nama Siswa</th>
                                        <th>NIS/NISN</th>
                                        <th>Layanan</th>
                                        <th>Pelanggaran</th>
                                        <th>Terlambat</th>
                                        <th>Rekap Absensi</th>
                                        <th>Laporan Guru</th>
                                        <th>Laporan Wali Kelas</th>
                                        <th>Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($ringkasanSiswa as $index => $siswa)
                                        <tr>
                                            <td>{{ $index + 1 }}</td>
                                            <td>{{ $siswa->nama }}</td>
                                            <td>
                                                <div>NIS: {{ $siswa->nis ?: '-' }}</div>
                                                <div>NISN: {{ $siswa->nisn ?: '-' }}</div>
                                            </td>
                                            <td><span class="badge bg-primary">{{ $siswa->total_layanan }}</span></td>
                                            <td><span class="badge bg-danger">{{ $siswa->total_pelanggaran }}</span></td>
                                            <td>
                                                <span class="badge" style="background:#f59e0b;color:#fff;">{{ $siswa->total_terlambat }}x</span>
                                                <div class="small text-muted mt-1">{{ $siswa->total_menit_terlambat }} menit</div>
                                            </td>
                                            <td>
                                                <div class="small">H: {{ $siswa->hadir }}, S: {{ $siswa->sakit }}, I: {{ $siswa->izin }}, A: {{ $siswa->alpa }}, T: {{ $siswa->terlambat_absensi }}</div>
                                            </td>
                                            <td>
                                                @if(!empty($siswa->laporan_guru))
                                                    <div class="small text-muted">{{ $siswa->laporan_guru['tanggal'] }}</div>
                                                    <div class="small">{{ $siswa->laporan_guru['deskripsi'] }}</div>
                                                @else
                                                    <span class="text-muted small">-</span>
                                                @endif
                                            </td>
                                            <td>
                                                @if(!empty($siswa->laporan_wali))
                                                    <div class="small text-muted">{{ $siswa->laporan_wali['tanggal'] }}</div>
                                                    <div class="small">{{ $siswa->laporan_wali['deskripsi'] }}</div>
                                                @else
                                                    <span class="text-muted small">-</span>
                                                @endif
                                            </td>
                                            <td>
                                                <button type="button" class="btn btn-sm {{ (int) $selectedSiswaId === (int) $siswa->id ? 'btn-primary' : 'btn-outline-primary' }} btn-pilih-siswa"
                                                    data-siswa-id="{{ $siswa->id }}"
                                                    data-siswa-nama="{{ $siswa->nama }}"
                                                    data-siswa-nis="{{ $siswa->nis }}"
                                                    data-siswa-nisn="{{ $siswa->nisn }}">
                                                    Pilih
                                                </button>
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

    <div class="row mt-3" id="formTindakLanjutCard">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title mb-0">Format Rencana Tindak Lanjut</h3>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('guru_bk_layanan.tindak_lanjut.store', ['kelas' => $kelas->id]) }}">
                        @csrf
                        <input type="hidden" name="siswa_id" id="siswa_id" value="{{ old('siswa_id', $selectedSiswaId) }}">

                        <div class="row g-3 mb-3">
                            <div class="col-md-6">
                                <label class="form-label">Nama Siswa</label>
                                <input type="text" class="form-control" id="nama_siswa_display" value="{{ old('nama_siswa_display', $selectedSiswa->nama ?? '') }}" readonly>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Kelas</label>
                                <input type="text" class="form-control" value="{{ $kelas->nama_kelas }}" readonly>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">NIS / NISN</label>
                                <input type="text" class="form-control" id="nis_nisn_display" value="{{ old('nis_nisn_display', isset($selectedSiswa) ? (($selectedSiswa->nis ?: '-') . ' / ' . ($selectedSiswa->nisn ?: '-')) : '') }}" readonly>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Waktu</label>
                                <input type="text" name="waktu" class="form-control" value="{{ old('waktu') }}" placeholder="Contoh: 20 Oktober s/d 4 November 2026" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Nama Wali Kelas</label>
                                <input type="text" class="form-control" value="{{ $waliKelasNama }}" readonly>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Nama Guru BK</label>
                                <input type="text" class="form-control" value="{{ $guruBkNama }}" readonly>
                            </div>
                        </div>

                        <div class="table-responsive mb-3">
                            <table class="table table-bordered">
                                <thead class="table-light">
                                    <tr>
                                        <th style="width: 70px;" class="text-center">No</th>
                                        <th>Rencana Kegiatan</th>
                                        <th>Waktu & Tempat</th>
                                        <th>Pihak Terkait</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @php
                                        $oldKegiatan = old('rencana_kegiatan', ['', '', '', '', '']);
                                        $oldWaktuTempat = old('waktu_tempat', ['', '', '', '', '']);
                                        $oldPihakTerkait = old('pihak_terkait', ['', '', '', '', '']);
                                        $maxRows = max(count($oldKegiatan), count($oldWaktuTempat), count($oldPihakTerkait), 5);
                                    @endphp
                                    @for($i = 0; $i < $maxRows; $i++)
                                        <tr>
                                            <td class="text-center align-middle">{{ $i + 1 }}</td>
                                            <td>
                                                <textarea name="rencana_kegiatan[]" class="form-control" rows="2" placeholder="Rencana kegiatan">{{ $oldKegiatan[$i] ?? '' }}</textarea>
                                            </td>
                                            <td>
                                                <textarea name="waktu_tempat[]" class="form-control" rows="2" placeholder="Waktu & tempat">{{ $oldWaktuTempat[$i] ?? '' }}</textarea>
                                            </td>
                                            <td>
                                                <textarea name="pihak_terkait[]" class="form-control" rows="2" placeholder="Pihak terkait">{{ $oldPihakTerkait[$i] ?? '' }}</textarea>
                                            </td>
                                        </tr>
                                    @endfor
                                </tbody>
                            </table>
                        </div>

                        <div class="text-end">
                            <button type="submit" class="btn btn-primary">
                                <i class="ti ti-device-floppy me-1"></i>Simpan Rencana Tindak Lanjut
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <div class="row mt-3">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title mb-0">Riwayat Rencana Tindak Lanjut</h3>
                </div>
                <div class="card-body">
                    @if(($tindakLanjutItems ?? collect())->isEmpty())
                        <div class="alert alert-light border mb-0">Belum ada riwayat tindak lanjut pada kelas ini.</div>
                    @else
                        <div class="table-responsive">
                            <table class="table table-sm table-striped table-bordered align-middle">
                                <thead class="table-light">
                                    <tr>
                                        <th>Tanggal</th>
                                        <th>Nama Siswa</th>
                                        <th>NIS/NISN</th>
                                        <th>Waktu</th>
                                        <th>Penyusun</th>
                                        <th>Rencana</th>
                                        <th>Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($tindakLanjutItems as $item)
                                        <tr>
                                            <td>{{ optional($item->created_at)->format('d/m/Y H:i') }}</td>
                                            <td>{{ $item->nama_siswa }}</td>
                                            <td>{{ $item->nis ?: '-' }} / {{ $item->nisn ?: '-' }}</td>
                                            <td>{{ $item->waktu }}</td>
                                            <td>{{ $item->nama_penyusun ?: '-' }}</td>
                                            <td>
                                                <details>
                                                    <summary>Lihat</summary>
                                                    <div class="mt-2">
                                                        <table class="table table-sm table-bordered mb-0">
                                                            <thead>
                                                                <tr>
                                                                    <th>No</th>
                                                                    <th>Rencana Kegiatan</th>
                                                                    <th>Waktu & Tempat</th>
                                                                    <th>Pihak Terkait</th>
                                                                </tr>
                                                            </thead>
                                                            <tbody>
                                                                @foreach(($item->rencana_items ?? []) as $row)
                                                                    <tr>
                                                                        <td>{{ $row['no'] ?? '-' }}</td>
                                                                        <td>{{ $row['rencana_kegiatan'] ?? '-' }}</td>
                                                                        <td>{{ $row['waktu_tempat'] ?? '-' }}</td>
                                                                        <td>{{ $row['pihak_terkait'] ?? '-' }}</td>
                                                                    </tr>
                                                                @endforeach
                                                            </tbody>
                                                        </table>
                                                    </div>
                                                </details>
                                            </td>
                                            <td>
                                                <div class="d-flex flex-wrap gap-1">
                                                    <a href="{{ route('guru_bk_layanan.tindak_lanjut.print', ['kelas' => $kelas->id, 'tindakLanjut' => $item->id]) }}" target="_blank" class="btn btn-sm btn-dark">
                                                        <i class="ti ti-printer me-1"></i>Print
                                                    </a>
                                                    <a href="{{ route('guru_bk_layanan.tindak_lanjut.pdf', ['kelas' => $kelas->id, 'tindakLanjut' => $item->id]) }}" class="btn btn-sm btn-primary">
                                                        <i class="ti ti-file-type-pdf me-1"></i>PDF
                                                    </a>
                                                </div>
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

<script>
document.addEventListener('DOMContentLoaded', function () {
    const siswaIdInput = document.getElementById('siswa_id');
    const namaSiswaDisplay = document.getElementById('nama_siswa_display');
    const nisNisnDisplay = document.getElementById('nis_nisn_display');
    const pilihButtons = document.querySelectorAll('.btn-pilih-siswa');

    pilihButtons.forEach(function (button) {
        button.addEventListener('click', function () {
            const siswaId = this.getAttribute('data-siswa-id') || '';
            const siswaNama = this.getAttribute('data-siswa-nama') || '';
            const siswaNis = this.getAttribute('data-siswa-nis') || '-';
            const siswaNisn = this.getAttribute('data-siswa-nisn') || '-';

            if (siswaIdInput) {
                siswaIdInput.value = siswaId;
            }
            if (namaSiswaDisplay) {
                namaSiswaDisplay.value = siswaNama;
            }
            if (nisNisnDisplay) {
                nisNisnDisplay.value = `${siswaNis || '-'} / ${siswaNisn || '-'}`;
            }

            pilihButtons.forEach(function (btn) {
                btn.classList.remove('btn-primary');
                btn.classList.add('btn-outline-primary');
            });

            this.classList.remove('btn-outline-primary');
            this.classList.add('btn-primary');

            const formCard = document.getElementById('formTindakLanjutCard');
            if (formCard) {
                formCard.scrollIntoView({ behavior: 'smooth', block: 'start' });
            }
        });
    });
});
</script>
@endsection
