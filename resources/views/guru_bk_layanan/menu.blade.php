@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row mb-2">
        <div class="col-12 d-flex justify-content-between align-items-center">
            <div>
                <h3 class="mb-1">Menu Kelas Binaan BK</h3>
                <p class="text-muted mb-0">Kelas: <strong>{{ $kelas->nama_kelas }}</strong></p>
            </div>
            <a href="{{ route('absensi.index') }}" class="btn btn-secondary">
                <i class="ti ti-arrow-left"></i> Kembali
            </a>
        </div>
    </div>

    <div class="row row-cards mb-4">
        <div class="col-sm-6 col-lg-3">
            <div class="card">
                <div class="card-body text-center">
                    <div class="h1 text-primary mb-2">{{ $stats->total_siswa ?? 0 }}</div>
                    <div class="text-muted">Siswa Aktif</div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-lg-3">
            <div class="card">
                <div class="card-body text-center">
                    <div class="h1 text-success mb-2">{{ $stats->absensi_count ?? 0 }}</div>
                    <div class="text-muted">Total Absensi</div>
                    @if($stats->last_absensi_date)
                        <div class="text-muted small">Terakhir: {{ \Carbon\Carbon::parse($stats->last_absensi_date)->translatedFormat('d F Y') }}</div>
                    @endif
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-lg-3">
            <div class="card">
                <div class="card-body text-center">
                    <div class="h1 text-warning mb-2">{{ $stats->agenda_count ?? 0 }}</div>
                    <div class="text-muted">Agenda Kelas</div>
                    @if($stats->last_agenda_date)
                        <div class="text-muted small">Terakhir: {{ \Carbon\Carbon::parse($stats->last_agenda_date)->translatedFormat('d F Y') }}</div>
                    @endif
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-lg-3">
            <div class="card">
                <div class="card-body text-center">
                    <div class="h1 text-danger mb-2">{{ $stats->laporan_wali_kelas_count ?? 0 }}</div>
                    <div class="text-muted">Laporan Wali Kelas</div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-md-6 col-lg-3">
            <a href="{{ route('guru_bk_layanan.layanan', ['kelas' => $kelas->id]) }}" class="btn btn-outline-primary w-100 py-3" style="height: auto;">
                <div class="text-center">
                    <i class="ti ti-heart-handshake" style="font-size: 44px;"></i>
                    <div class="mt-2 fw-bold">Layanan BK</div>
                </div>
            </a>
        </div>

        <div class="col-md-6 col-lg-3">
            <a href="{{ route('guru_bk_layanan.daftar_hadir', ['kelas' => $kelas->id]) }}" class="btn btn-outline-success w-100 py-3" style="height: auto;">
                <div class="text-center">
                    <i class="ti ti-user-check" style="font-size: 44px;"></i>
                    <div class="mt-2 fw-bold">Daftar Hadir Layanan BK</div>
                </div>
            </a>
        </div>

        <div class="col-md-6 col-lg-3">
            <a href="{{ route('guru_bk_layanan.pembinaan', ['kelas' => $kelas->id]) }}" class="btn btn-outline-warning w-100 py-3" style="height: auto;">
                <div class="text-center">
                    <i class="ti ti-users-group" style="font-size: 44px;"></i>
                    <div class="mt-2 fw-bold">Pembinaan BK</div>
                </div>
            </a>
        </div>

        <div class="col-md-6 col-lg-3">
            <a href="{{ route('guru_bk_layanan.tindak_lanjut', ['kelas' => $kelas->id]) }}" class="btn btn-outline-danger w-100 py-3" style="height: auto;">
                <div class="text-center">
                    <i class="ti ti-arrow-forward-up" style="font-size: 44px;"></i>
                    <div class="mt-2 fw-bold">Tindak Lanjut</div>
                </div>
            </a>
        </div>

        <div class="col-md-6 col-lg-3">
            <a href="{{ route('guru_bk_layanan.kartu_kendali', ['kelas' => $kelas->id]) }}" class="btn btn-outline-secondary w-100 py-3" style="height: auto;">
                <div class="text-center">
                    <i class="ti ti-clipboard-list" style="font-size: 44px;"></i>
                    <div class="mt-2 fw-bold">Kartu Kendali Pelanggaran</div>
                </div>
            </a>
        </div>

        <div class="col-md-6 col-lg-3">
            <button type="button" class="btn btn-outline-dark w-100 py-3 btn-print-preview" style="height: auto;" data-print-url="{{ route('guru_bk_layanan.layanan.print', ['kelas' => $kelas->id]) }}" data-bs-toggle="modal" data-bs-target="#printPreviewModal">
                <div class="text-center">
                    <i class="ti ti-printer" style="font-size: 44px;"></i>
                    <div class="mt-2 fw-bold">Print Output Layanan BK</div>
                </div>
            </button>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex flex-wrap justify-content-between align-items-center gap-2">
                    <div>
                        <h5 class="card-title mb-1">Rekap Kehadiran Per Kelas Binaan</h5>
                        <p class="text-muted mb-0">Menampilkan rekap kehadiran siswa berdasarkan filter waktu yang dipilih.</p>
                    </div>
                </div>
                <div class="card-body">
                    <form method="GET" class="row g-2 align-items-end mb-3">
                        <input type="hidden" name="periode" id="periodeInput" value="{{ $selectedPeriode ?? 'bulanan' }}">
                        <div class="col-12 col-md-4 col-lg-3">
                            <label class="form-label mb-1">Jenis Filter</label>
                            <select class="form-select" id="periodeSelect">
                                <option value="harian" {{ ($selectedPeriode ?? 'bulanan') === 'harian' ? 'selected' : '' }}>Harian</option>
                                <option value="mingguan" {{ ($selectedPeriode ?? 'bulanan') === 'mingguan' ? 'selected' : '' }}>Mingguan</option>
                                <option value="bulanan" {{ ($selectedPeriode ?? 'bulanan') === 'bulanan' ? 'selected' : '' }}>Bulanan</option>
                                <option value="rentang" {{ ($selectedPeriode ?? 'bulanan') === 'rentang' ? 'selected' : '' }}>Rentang Waktu</option>
                            </select>
                        </div>
                        <div class="col-12 col-md-4 col-lg-3" id="tanggalFilterGroup">
                            <label class="form-label mb-1">Tanggal</label>
                            <input type="date" name="tanggal" class="form-control" value="{{ $selectedTanggal ?? now()->format('Y-m-d') }}">
                        </div>
                        <div class="col-12 col-md-4 col-lg-3" id="tanggalMulaiGroup" style="display:none;">
                            <label class="form-label mb-1">Tanggal Mulai</label>
                            <input type="date" name="tanggal_mulai" class="form-control" value="{{ $tanggalMulai ?? '' }}">
                        </div>
                        <div class="col-12 col-md-4 col-lg-3" id="tanggalSelesaiGroup" style="display:none;">
                            <label class="form-label mb-1">Tanggal Selesai</label>
                            <input type="date" name="tanggal_selesai" class="form-control" value="{{ $tanggalSelesai ?? '' }}">
                        </div>
                        <div class="col-auto">
                            <button type="submit" class="btn btn-primary">
                                <i class="ti ti-search me-1"></i>Tampilkan
                            </button>
                        </div>
                    </form>

                    <div class="alert alert-light border mb-3">
                        <strong>Rentang tampilan:</strong> {{ \Carbon\Carbon::parse($startDate ?? now()->format('Y-m-d'))->translatedFormat('d F Y') }} s.d. {{ \Carbon\Carbon::parse($endDate ?? now()->format('Y-m-d'))->translatedFormat('d F Y') }}
                    </div>

                    <div class="table-responsive">
                        <table class="table table-vcenter table-hover table-tabler">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>Nama Siswa</th>
                                    <th>NIS</th>
                                    @if(($isDailyDetail ?? false) && !empty($jamColumns))
                                        @foreach($jamColumns as $jamColumn)
                                            <th>{{ $jamColumn['label'] }}</th>
                                        @endforeach
                                    @else
                                        <th>Hadir</th>
                                        <th>Sakit</th>
                                        <th>Izin</th>
                                        <th>Terlambat</th>
                                        <th>Tidak Hadir</th>
                                        <th>Total Rekap</th>
                                    @endif
                                </tr>
                            </thead>
                            <tbody>
                                @if(($isDailyDetail ?? false) && !empty($jamColumns))
                                    @forelse($dailyAttendanceRows ?? collect() as $index => $row)
                                        <tr>
                                            <td>{{ $index + 1 }}</td>
                                            <td>{{ $row->nama_siswa ?? '-' }}</td>
                                            <td>{{ $row->nis ?? '-' }}</td>
                                            @foreach($row->cells ?? [] as $cell)
                                                <td>
                                                    <span class="badge rounded-pill px-2 py-2" style="min-width:46px; {{ $cell['style'] }}">{{ $cell['text'] }}</span>
                                                </td>
                                            @endforeach
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="{{ 3 + count($jamColumns) }}" class="text-center text-muted py-4">Belum ada data rekap kehadiran untuk periode ini.</td>
                                        </tr>
                                    @endforelse
                                @else
                                    @forelse($rekapAbsensi ?? collect() as $index => $row)
                                        <tr>
                                            <td>{{ $index + 1 }}</td>
                                            <td>{{ $row->nama_siswa ?? '-' }}</td>
                                            <td>{{ $row->nis ?? '-' }}</td>
                                            <td>
                                                @php
                                                    $dailyStatus = ($row->hadir ?? 0) > 0 && ($row->tidak_hadir ?? 0) == 0 ? 'green' : 'yellow';
                                                @endphp
                                                <span class="badge rounded-pill px-3 py-2 {{ $dailyStatus === 'green' ? 'bg-success' : 'bg-warning' }} text-white">
                                                    {{ $row->hadir ?? 0 }} Hari Hadir
                                                </span>
                                            </td>
                                            <td><span class="badge bg-warning text-white">{{ $row->sakit ?? 0 }}</span></td>
                                            <td><span class="badge bg-info text-white">{{ $row->izin ?? 0 }}</span></td>
                                            <td><span class="badge bg-orange text-white">{{ $row->terlambat ?? 0 }}</span></td>
                                            <td><span class="badge bg-danger text-white">{{ $row->tidak_hadir ?? 0 }}</span></td>
                                            <td><span class="badge bg-secondary text-white">{{ $row->total_rekap ?? 0 }}</span></td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="9" class="text-center text-muted py-4">Belum ada data rekap kehadiran untuk periode ini.</td>
                                        </tr>
                                    @endforelse
                                @endif
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="printPreviewModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Preview Print Layanan BK</h5>
                <button type="button" class="close" data-bs-dismiss="modal"><span aria-hidden="true">&times;</span></button>
            </div>
            <div class="modal-body p-0" style="height: 75vh;">
                <iframe id="printPreviewFrame" src="" style="width:100%;height:100%;border:0;"></iframe>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                <button type="button" class="btn btn-primary" id="btnPrintFromPreview">
                    <i class="ti ti-printer me-1"></i>Print
                </button>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const previewButtons = document.querySelectorAll('.btn-print-preview');
    const frame = document.getElementById('printPreviewFrame');
    const printBtn = document.getElementById('btnPrintFromPreview');

    previewButtons.forEach(function (button) {
        button.addEventListener('click', function () {
            frame.src = this.getAttribute('data-print-url');
        });
    });

    if (printBtn) {
        printBtn.addEventListener('click', function () {
            if (frame && frame.contentWindow) {
                frame.contentWindow.focus();
                frame.contentWindow.print();
            }
        });
    }

    const periodeSelect = document.getElementById('periodeSelect');
    const periodeInput = document.getElementById('periodeInput');
    const tanggalFilterGroup = document.getElementById('tanggalFilterGroup');
    const tanggalMulaiGroup = document.getElementById('tanggalMulaiGroup');
    const tanggalSelesaiGroup = document.getElementById('tanggalSelesaiGroup');

    function toggleFilterFields() {
        if (!periodeSelect || !periodeInput) return;
        const value = periodeSelect.value;
        periodeInput.value = value;

        if (value === 'rentang') {
            tanggalFilterGroup.style.display = 'none';
            tanggalMulaiGroup.style.display = '';
            tanggalSelesaiGroup.style.display = '';
        } else {
            tanggalFilterGroup.style.display = '';
            tanggalMulaiGroup.style.display = 'none';
            tanggalSelesaiGroup.style.display = 'none';
        }
    }

    if (periodeSelect) {
        periodeSelect.addEventListener('change', toggleFilterFields);
        toggleFilterFields();
    }
});
</script>
@endsection
