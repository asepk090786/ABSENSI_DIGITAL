@extends('layouts.app')

@section('title', 'Daftar Nilai')

@section('content')
<div class="modal fade" id="modalPilihRekapNilai" tabindex="-1" aria-labelledby="modalPilihRekapNilaiLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalPilihRekapNilaiLabel">Pilih Rekap Penilaian</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
            </div>
            <div class="modal-body">
                <div class="d-grid gap-2">
                    <a href="#" class="btn btn-outline-primary text-start" id="linkRekapFormatif" data-route="{{ route('nilai.rekap.formatif') }}">
                        <i class="ti ti-clipboard-list me-2"></i>Rekap Asesmen Formatif
                    </a>
                    <a href="#" class="btn btn-outline-success text-start" id="linkRekapSumatif" data-route="{{ route('nilai.rekap.sumatif') }}">
                        <i class="ti ti-checklist me-2"></i>Rekap Asesmen Sumatif
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="modalBobotNilaiAkhir" tabindex="-1" aria-labelledby="modalBobotNilaiAkhirLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalBobotNilaiAkhirLabel">Atur Bobot Nilai Akhir</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
            </div>
            <div class="modal-body">
                <form method="POST" action="{{ route('nilai.bobot-akhir-asesmen.update') }}" id="formBobotNilaiAkhir">
                    @csrf
                    <input type="hidden" name="kelas_id" id="bobotKelasId" value="{{ old('kelas_id') }}">
                    <input type="hidden" name="mapel_id" id="bobotMapelId" value="{{ old('mapel_id') }}">
                    @if($errors->has('bobot_formatif'))
                        <div class="alert alert-danger">{{ $errors->first('bobot_formatif') }}</div>
                    @endif
                    <div class="mb-3">
                        <label class="form-label" for="bobotFormatif">Asesmen Formatif (%)</label>
                        <input type="number" class="form-control" name="bobot_formatif" id="bobotFormatif" min="0" max="100" step="0.01" value="{{ old('bobot_formatif', 50) }}" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label" for="bobotSumatif">Asesmen Sumatif (%)</label>
                        <input type="number" class="form-control" name="bobot_sumatif" id="bobotSumatif" min="0" max="100" step="0.01" value="{{ old('bobot_sumatif', 50) }}" required>
                    </div>
                    <div class="form-hint">Jumlah persentase formatif dan sumatif harus 100%.</div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                <button type="submit" class="btn btn-primary" form="formBobotNilaiAkhir"><i class="ti ti-device-floppy me-1"></i>Simpan Bobot</button>
            </div>
        </div>
    </div>
</div>

@push('js')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const modal = document.getElementById('modalPilihRekapNilai');
    if (modal) modal.addEventListener('show.bs.modal', function (event) {
        const trigger = event.relatedTarget;
        if (!trigger) return;

        ['linkRekapFormatif', 'linkRekapSumatif'].forEach(function (linkId) {
            const link = document.getElementById(linkId);
            const url = new URL(link.dataset.route, window.location.origin);
            url.searchParams.set('kelas_id', trigger.dataset.kelasId || '');
            if (trigger.dataset.mapelId) {
                url.searchParams.set('mapel_id', trigger.dataset.mapelId);
            } else {
                url.searchParams.delete('mapel_id');
            }
            link.href = url.toString();
        });
    });

    const weightModal = document.getElementById('modalBobotNilaiAkhir');
    if (weightModal) weightModal.addEventListener('show.bs.modal', function (event) {
        const trigger = event.relatedTarget;
        if (!trigger) return;
        document.getElementById('bobotKelasId').value = trigger.dataset.kelasId;
        document.getElementById('bobotMapelId').value = trigger.dataset.mapelId;
        document.getElementById('bobotFormatif').value = trigger.dataset.bobotFormatif || 50;
        document.getElementById('bobotSumatif').value = trigger.dataset.bobotSumatif || 50;
    });

    const weightForm = document.getElementById('formBobotNilaiAkhir');
    if (weightForm) weightForm.addEventListener('submit', function (event) {
        const formative = Number(document.getElementById('bobotFormatif').value);
        const summative = Number(document.getElementById('bobotSumatif').value);
        if (Math.abs(formative + summative - 100) > 0.001) {
            event.preventDefault();
            window.alert('Total bobot formatif dan sumatif harus 100%.');
        }
    });

    @if($errors->has('bobot_formatif'))
        bootstrap.Modal.getOrCreateInstance(weightModal).show();
    @endif
});
</script>
@endpush

<div class="page-header d-print-none">
    <div class="container-xl">
        <div class="row g-2 align-items-center">
            <div class="col">
                <h2 class="page-title">
                    <i class="ti ti-report-analytics me-2"></i>{{ $isEditMode ? 'Edit Nilai Harian' : 'Daftar Nilai Harian' }}
                </h2>
                <div class="text-muted mt-1">Kelola data nilai harian siswa per mata pelajaran</div>
                @if($tahunAjaranAktif && $semesterAktif)
                <div class="text-muted small mt-2">
                    <i class="ti ti-calendar me-1"></i>{{ $tahunAjaranAktif->tahun_ajaran }} - <strong>{{ $semesterAktif->nama_semester }}</strong>
                </div>
                @endif
            </div>
            <div class="col-auto ms-auto d-print-none">
                @php
                    $isSiswaWithoutClassPosition = auth()->user()->hasRole('Siswa') && ! auth()->user()->hasClassPosition();
                @endphp
                @if($showBackNavigation)
                    <a href="{{ $backUrl }}" class="btn btn-outline-secondary">
                        <i class="ti ti-arrow-left me-1"></i>Kembali
                    </a>
                @elseif(!$isSiswaWithoutClassPosition)
                <div >
                    <a href="#" class="btn btn-outline-primary d-none d-sm-inline-block" data-bs-toggle="modal" data-bs-target="#modalImportNilai">
                        <i class="ti ti-file-import"></i>
                        Import Excel
                    </a>
                    <a href="#" class="btn btn-primary d-none d-sm-inline-block" data-bs-toggle="modal" data-bs-target="#modalTambahNilai">
                        <i class="ti ti-plus"></i>
                        Tambah Nilai
                    </a>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>

<div class="page-body">
    <div class="container-xl">
        @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="ti ti-check me-2"></i>{{ session('success') }}
            <button type="button" class="close" data-bs-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        </div>
        @endif
        @if(session('warning'))
        <div class="alert alert-warning alert-dismissible fade show" role="alert">
            <div class="fw-bold mb-2">{{ session('warning') }}</div>
            @if(session('import_errors'))
            <ul class="mb-0">
                @foreach(session('import_errors') as $err)
                    <li>{{ $err }}</li>
                @endforeach
            </ul>
            @endif
            <button type="button" class="close" data-bs-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        </div>
        @endif

        @if(request()->get('debug') === '1')
        <div class="card mb-2">
            <div class="card-header border-0 pt-3 pb-2">
                <h3 class="card-title">Debug Rencana Pembelajaran (Guru Login)</h3>
            </div>
            <div class="card-body">
                @if($debugRencana)
                    <div class="mb-2">Total rencana ditemukan: <strong>{{ $debugRencana['total'] }}</strong></div>
                    @if($debugRencana['total'] > 0)
                        <div class="table-responsive">
                            <table class="table table-sm table-vcenter">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Judul</th>
                                        <th>Kelas</th>
                                        <th>Mapel</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($debugRencana['sample'] as $rp)
                                        <tr>
                                            <td>{{ $rp->id }}</td>
                                            <td>{{ $rp->judul }}</td>
                                            <td>{{ $rp->kelas->nama_kelas ?? '-' }}</td>
                                            <td>{{ $rp->mataPelajaran->nama_mapel ?? '-' }}</td>
                                            <td>{{ $rp->status ?? '-' }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                @else
                    <div class="text-muted">Tidak ada data debug untuk guru login.</div>
                @endif
            </div>
        </div>
        @endif

        @if($isAdminOrKepala ?? false)
        <div class="card mb-2">
            <div class="card-header border-0 pt-3 pb-2">
                <h3 class="card-title">Lihat Nilai (Sederhana)</h3>
            </div>
            <div class="card-body">
                <form method="GET" action="{{ route('nilai.index') }}" class="row g-2 align-items-end">
                    <div class="col-12 col-md-4 col-lg-3">
                        <label class="form-label">Kelas</label>
                        <select class="form-select" name="kelas_id">
                            <option value="">Semua Kelas</option>
                            @foreach(($kelasOptions ?? collect()) as $kelas)
                                <option value="{{ $kelas->id }}" {{ (string) $kelasId === (string) $kelas->id ? 'selected' : '' }}>
                                    {{ $kelas->nama_kelas }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-12 col-md-4 col-lg-4">
                        <label class="form-label">Mata Pelajaran</label>
                        <select class="form-select" name="mapel_id">
                            <option value="">Semua Mata Pelajaran</option>
                            @foreach(($mapelOptions ?? collect()) as $mapel)
                                <option value="{{ $mapel->id }}" {{ (string) $mapelId === (string) $mapel->id ? 'selected' : '' }}>
                                    {{ $mapel->nama_mapel }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-auto">
                        <button type="submit" class="btn btn-primary">
                            <i class="ti ti-search me-1"></i>Tampilkan
                        </button>
                    </div>
                    <div class="col-auto">
                        <a href="{{ route('nilai.index') }}" class="btn btn-outline-secondary btn-modern">Reset</a>
                    </div>
                </form>
            </div>
        </div>
        @endif

        @if($isAdminOrKepala ?? false)
        <div class="card mb-2">
            <div class="card-header border-0 pt-3 pb-2">
                <h3 class="card-title">Rekap Nilai Diinput Guru</h3>
            </div>
            <div class="card-body">
                <form method="GET" action="{{ route('nilai.index') }}" class="row g-2 align-items-end mb-2">
                    @if($kelasId)
                        <input type="hidden" name="kelas_id" value="{{ $kelasId }}">
                    @endif
                    @if($mapelId)
                        <input type="hidden" name="mapel_id" value="{{ $mapelId }}">
                    @endif
                    <div class="col-12 col-md-4 col-lg-3">
                        <label for="tanggal_nilai" class="form-label">Tanggal Input Nilai</label>
                        <input
                            type="date"
                            class="form-control"
                            id="tanggal_nilai"
                            name="tanggal_nilai"
                            value="{{ $selectedTanggalNilai ?? now()->format('Y-m-d') }}"
                        >
                    </div>
                    <div class="col-auto">
                        <button type="submit" class="btn btn-primary">
                            <i class="ti ti-search me-1"></i>Tampilkan
                        </button>
                    </div>
                </form>

                @if(($rekapInputGuru ?? collect())->isEmpty())
                    <div class="alert alert-info mb-0">
                        <i class="ti ti-info-circle me-1"></i>Belum ada input nilai guru pada tanggal dipilih.
                    </div>
                @else
                    <div class="table-responsive">
                        <table class="table table-vcenter table-striped table-hover">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>Guru</th>
                                    <th>Total Record</th>
                                    <th>Nilai Terisi</th>
                                    <th>Rata-Rata Nilai</th>
                                    <th>Kelas</th>
                                    <th>Mapel</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach(($rekapInputGuru ?? collect()) as $index => $rekap)
                                    <tr>
                                        <td>{{ $index + 1 }}</td>
                                        <td>{{ $rekap->guru_nama }}</td>
                                        <td><span class="badge bg-primary">{{ $rekap->total_record }}</span></td>
                                        <td><span class="badge bg-success">{{ $rekap->total_terisi }}</span></td>
                                        <td>{{ $rekap->rata_nilai !== null ? number_format((float) $rekap->rata_nilai, 2) : '-' }}</td>
                                        <td>{{ $rekap->total_kelas }}</td>
                                        <td>{{ $rekap->total_mapel }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        </div>

        <div class="card mb-2">
            <div class="card-header border-0 pt-3 pb-2">
                <h3 class="card-title">Daftar Nilai Sudah Diinput Guru</h3>
                <div class="card-actions text-muted small">
                    Maksimal 300 data terbaru
                </div>
            </div>
            <div class="card-body">
                @if(($daftarInputNilaiGuru ?? collect())->isEmpty())
                    <div class="alert alert-info mb-0">
                        <i class="ti ti-info-circle me-1"></i>Belum ada nilai terisi oleh guru pada filter yang dipilih.
                    </div>
                @else
                    <div class="table-responsive">
                        <table class="table table-vcenter table-striped table-hover">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>Tanggal</th>
                                    <th>Guru</th>
                                    <th>Kelas</th>
                                    <th>Mata Pelajaran</th>
                                    <th>Siswa</th>
                                    <th>Komponen</th>
                                    <th>Nilai</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach(($daftarInputNilaiGuru ?? collect()) as $index => $row)
                                    <tr>
                                        <td>{{ $index + 1 }}</td>
                                        <td>{{ \Carbon\Carbon::parse($row->tanggal)->format('d/m/Y') }}</td>
                                        <td>{{ $row->guru_nama }}</td>
                                        <td>{{ $row->nama_kelas }}</td>
                                        <td>{{ $row->nama_mapel }}</td>
                                        <td>{{ $row->nama_siswa }}</td>
                                        <td>{{ $row->nama_komponen ?: 'Harian' }}</td>
                                        <td><span class="badge bg-success">{{ rtrim(rtrim(number_format((float) $row->nilai, 2, '.', ''), '0'), '.') }}</span></td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        </div>
        @endif

        <div class="card">
            <div class="card-header border-0 pt-3 pb-2">
                <h3 class="card-title">Data Nilai Harian</h3>
                @if($kelasId || $mapelId)
                    <div class="text-muted ms-3">
                        Tanggal: {{ optional($items->first())->tanggal ? \Carbon\Carbon::parse($items->first()->tanggal)->format('d/m/Y') : '-' }}
                        | Kelas: {{ $filterKelasName ?? '-' }}
                        | Mapel: {{ $filterMapelName ?? '-' }}
                    </div>
                @endif
                <div class="card-actions">
                    <input type="text" class="form-control form-control-sm" placeholder="Cari..." id="searchInput">
                </div>
            </div>
            <div class="card-body">
                @php
                    $kelasTugasTable = collect();
                    $kelasPerTugas = ($quickMenus ?? collect());

                    if ($kelasPerTugas->isEmpty()) {
                        $kelasPerTugas = ($kelasOptions ?? collect())->map(function ($kelas) use ($mapelId, $mapelOptions) {
                            $mapel = collect($mapelOptions ?? [])->firstWhere('id', $mapelId);
                            return (object) [
                                'kelas_id' => $kelas->id ?? null,
                                'kelas' => (object) ['nama_kelas' => $kelas->nama_kelas ?? '-'],
                                'mata_pelajaran_id' => $mapelId,
                                'mataPelajaran' => $mapel,
                            ];
                        });
                    }

                    if ($kelasPerTugas->isNotEmpty()) {
                        $kelasTugasTable = $kelasPerTugas
                            ->groupBy(function ($item) {
                                return ($item->kelas_id ?? '') . '-' . ($item->mata_pelajaran_id ?? $item->mapel_id ?? '');
                            })
                            ->map(function ($group) use ($items, $nilaiAkhirByKelasMapel) {
                                $first = $group->first();
                                $kelasId = $first->kelas_id ?? null;
                                $mapelId = $first->mata_pelajaran_id ?? $first->mapel_id ?? null;
                                $kelasName = $first->kelas->nama_kelas ?? $first->nama_kelas ?? '-';
                                $mapelName = $first->mataPelajaran->nama_mapel ?? $first->nama_mapel ?? '-';
                                $nilaiKelas = collect($items ?? [])->where('kelas_id', $kelasId)->where('mapel_id', $mapelId)->whereNotNull('nilai');
                                $pairKey = $kelasId . '-' . ($mapelId ?? '');
                                $nilaiAkhirData = $nilaiAkhirByKelasMapel[$pairKey] ?? null;
                                $formatif = $nilaiAkhirData['rata_formatif'] ?? null;
                                $sumatif = $nilaiAkhirData['rata_sumatif'] ?? null;

                                return (object) [
                                    'kelas_id' => $kelasId,
                                    'mapel_id' => $mapelId,
                                    'kelas' => $kelasName,
                                    'mapel' => $mapelName,
                                    'formatif' => $formatif !== null ? number_format((float) $formatif, 2) : '-',
                                    'sumatif' => $sumatif !== null ? number_format((float) $sumatif, 2) : '-',
                                    'nilai_akhir' => $nilaiAkhirData['nilai_akhir'] ?? null,
                                    'bobot_formatif' => $nilaiAkhirData['bobot_formatif'] ?? 50,
                                    'bobot_sumatif' => $nilaiAkhirData['bobot_sumatif'] ?? 50,
                                    'keterangan' => $nilaiKelas->isNotEmpty() ? 'Data nilai tersedia' : 'Belum ada data nilai',
                                ];
                            })
                            ->sortBy(function ($row) {
                                return mb_strtolower($row->kelas . '-' . $row->mapel);
                            })
                            ->values();
                    }
                @endphp

                @if((!($isViewMode ?? false)) && !$showInputForm && $kelasTugasTable->isNotEmpty())
                    <div class="table-responsive">
                        <table class="table table-vcenter table-striped table-hover" id="tableNilai">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>Kelas</th>
                                    <th>Mata Pelajaran</th>
                                    <th>Asesmen Formatif</th>
                                    <th>Asesmen Sumatif</th>
                                    <th>Nilai Akhir</th>
                                    <th>Aksi</th>
                                    <th>Keterangan</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($kelasTugasTable as $index => $row)
                                    <tr>
                                        <td>{{ $index + 1 }}</td>
                                        <td>{{ $row->kelas }}</td>
                                        <td>{{ $row->mapel }}</td>
                                        <td>
                                            <div class="small text-muted mb-1">Rata-rata: {{ $row->formatif }}</div>
                                            @if($row->mapel_id)
                                                <a href="{{ route('nilai.asesmen.formatif', ['kelas_id' => $row->kelas_id, 'mapel_id' => $row->mapel_id]) }}" class="btn btn-sm btn-outline-primary" title="Kelola Asesmen Formatif">Kelola</a>
                                            @endif
                                        </td>
                                        <td>
                                            <div class="small text-muted mb-1">Rata-rata: {{ $row->sumatif }}</div>
                                            @if($row->mapel_id)
                                                <a href="{{ route('nilai.asesmen.sumatif', ['kelas_id' => $row->kelas_id, 'mapel_id' => $row->mapel_id]) }}" class="btn btn-sm btn-outline-success" title="Kelola Asesmen Sumatif">Kelola</a>
                                            @endif
                                        </td>
                                        <td class="text-center">
                                            <span class="fw-semibold">{{ $row->nilai_akhir !== null ? number_format($row->nilai_akhir, 2) : '-' }}</span>
                                            @if($row->mapel_id)
                                                <a href="{{ route('nilai.rekap.akhir-asesmen', ['kelas_id' => $row->kelas_id, 'mapel_id' => $row->mapel_id]) }}" class="btn btn-sm btn-outline-info ms-1" title="Lihat rincian nilai akhir" aria-label="Lihat rincian nilai akhir"><i class="ti ti-eye"></i></a>
                                            @endif
                                        </td>
                                        <td class="text-center">
                                            <div class="d-flex justify-content-center gap-2">
                                                @if($row->mapel_id)
                                                    <button type="button" class="btn btn-sm btn-info text-white" title="Lihat rekap nilai" aria-label="Lihat rekap nilai" data-bs-toggle="modal" data-bs-target="#modalPilihRekapNilai" data-kelas-id="{{ $row->kelas_id }}" data-mapel-id="{{ $row->mapel_id }}"><i class="ti ti-eye"></i></button>
                                                    <button type="button" class="btn btn-sm btn-primary" title="Atur bobot nilai akhir" aria-label="Atur bobot nilai akhir" data-bs-toggle="modal" data-bs-target="#modalBobotNilaiAkhir" data-kelas-id="{{ $row->kelas_id }}" data-mapel-id="{{ $row->mapel_id }}" data-bobot-formatif="{{ $row->bobot_formatif }}" data-bobot-sumatif="{{ $row->bobot_sumatif }}"><i class="ti ti-edit"></i></button>
                                                @endif
                                            </div>
                                        </td>
                                        <td>{{ $row->keterangan }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    @if($isViewMode ?? false)
                        @if(($nilaiTableRows ?? collect())->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-vcenter table-hover" id="tableNilai">
                                <thead>
                                    <tr>
                                        <th>No</th>
                                        <th>Nama Siswa</th>
                                        @forelse(($nilaiKomponenColumns ?? collect()) as $komponen)
                                            <th class="text-center">{{ strtoupper($komponen->nama) }}</th>
                                        @empty
                                            <th class="text-center">KOMPONEN</th>
                                        @endforelse
                                        <th class="text-center">JUMLAH</th>
                                        <th class="text-center">RATA-RATA</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach(($nilaiTableRows ?? collect()) as $index => $row)
                                    <tr>
                                        <td>{{ $index + 1 }}</td>
                                        <td>{{ $row->nama_siswa }}</td>
                                        @forelse(($nilaiKomponenColumns ?? collect()) as $komponen)
                                            @php
                                                $komponenKey = $komponen->key;
                                                $nilai = $row->nilai_by_komponen[$komponenKey] ?? null;
                                            @endphp
                                            <td class="text-center">
                                                @if($nilai !== null && $nilai !== '')
                                                    <span class="badge bg-success">{{ rtrim(rtrim(number_format((float) $nilai, 2, '.', ''), '0'), '.') }}</span>
                                                @else
                                                    <span class="text-muted">-</span>
                                                @endif
                                            </td>
                                        @empty
                                            <td class="text-center">-</td>
                                        @endforelse
                                        <td class="text-center fw-bold">{{ $row->jumlah !== null ? number_format($row->jumlah, 2) : '-' }}</td>
                                        <td class="text-center fw-bold">{{ $row->rata_rata !== null ? number_format($row->rata_rata, 2) : '-' }}</td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        @else
                        <div class="empty">
                            <div class="empty-img"><img src="{{ asset('tabler/static/illustrations/undraw_printing_invoices_5r4r.svg') }}" height="128" alt=""></div>
                            <p class="empty-title">Belum ada data nilai</p>
                            <p class="empty-subtitle text-muted">Gunakan tombol "Tambah Nilai" untuk membuat penilaian pertama.</p>
                        </div>
                        @endif
                    @elseif(!$showInputForm)
                        @if($isViewMode && !empty($selectedTanggalNilai) && ($nilaiTableRows ?? collect())->count() > 0)
                            <form method="POST" action="{{ route('nilai.update-batch') }}">
                                @csrf
                                <input type="hidden" name="kelas_id" value="{{ $kelasId }}">
                                <input type="hidden" name="mapel_id" value="{{ $mapelId }}">
                                <input type="hidden" name="tanggal_nilai" value="{{ request('tanggal_nilai') }}">
                                <input type="hidden" name="rencana_pembelajaran_id" value="{{ request('rencana_pembelajaran_id') }}">
                                <input type="hidden" name="komponen_id" value="{{ request('komponen_id') }}">
                                <input type="hidden" name="assessment_uuid" value="{{ $selectedAssessmentUuid }}">
                                <input type="hidden" name="edit" value="{{ $isEditMode ? 1 : '' }}">
                                <input type="hidden" name="source" value="{{ request('source') }}">
                                <div class="d-flex justify-content-end mb-2">
                                    <button type="submit" class="btn btn-sm btn-primary">
                                        <i class="ti ti-device-floppy me-1"></i>Simpan Nilai
                                    </button>
                                </div>
                                <div class="table-responsive">
                                    <table class="table table-vcenter table-hover" id="tableNilai">
                                        <thead>
                                            <tr>
                                                <th>No</th>
                                                <th>Nama Siswa</th>
                                                @forelse(($nilaiKomponenColumns ?? collect()) as $komponen)
                                                    <th class="text-center">{{ strtoupper($komponen->nama) }}</th>
                                                @empty
                                                    <th class="text-center">KOMPONEN</th>
                                                @endforelse
                                                <th class="text-center">JUMLAH</th>
                                                <th class="text-center">RATA-RATA</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach(($nilaiTableRows ?? collect()) as $index => $row)
                                            <tr>
                                                <td>{{ $index + 1 }}</td>
                                                <td>{{ $row->nama_siswa }}</td>
                                                @forelse(($nilaiKomponenColumns ?? collect()) as $komponen)
                                                    @php
                                                        $komponenKey = $komponen->key;
                                                        $nilaiId = $row->nilai_id_by_komponen[$komponenKey] ?? null;
                                                        $nilai = $row->nilai_by_komponen[$komponenKey] ?? null;
                                                    @endphp
                                                    <td class="text-center">
                                                        @if($nilaiId)
                                                            <input type="number" name="nilai[{{ $nilaiId }}]" class="form-control form-control-sm text-center" min="0" max="100" step="0.01" value="{{ $nilai }}" style="width: 100px; margin: 0 auto;">
                                                        @else
                                                            <span class="text-muted">-</span>
                                                        @endif
                                                    </td>
                                                @empty
                                                    <td class="text-center">-</td>
                                                @endforelse
                                                <td class="text-center fw-bold">{{ $row->jumlah !== null ? number_format($row->jumlah, 2) : '-' }}</td>
                                                <td class="text-center fw-bold">{{ $row->rata_rata !== null ? number_format($row->rata_rata, 2) : '-' }}</td>
                                            </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </form>
                        @elseif($kelasId || $mapelId)
                            @if(($penilaianList ?? collect())->isNotEmpty())
                            <div class="table-responsive">
                                <table class="table table-vcenter table-hover">
                                    <thead>
                                        <tr>
                                            <th>No</th>
                                            <th>Penilaian</th>
                                            <th>Jenis Penilaian</th>
                                            <th>Tanggal</th>
                                            <th>Persentase</th>
                                            <th class="text-center">Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($penilaianList as $index => $penilaian)
                                        <tr>
                                            <td>{{ $index + 1 }}</td>
                                            <td>
                                                <div class="fw-semibold">{{ $penilaian->nama_penilaian }}</div>
                                                <div class="text-muted small">{{ $penilaian->rencana_judul ?: 'Tanpa judul rencana' }}</div>
                                            </td>
                                            <td>{{ $penilaian->jenis_penilaian }}</td>
                                            <td>{{ \Carbon\Carbon::parse($penilaian->tanggal)->format('d/m/Y') }}</td>
                                            <td>{{ $penilaian->persentase !== null ? rtrim(rtrim(number_format((float) $penilaian->persentase, 2, '.', ''), '0'), '.') . '%' : '-' }}</td>
                                            <td class="text-center">
                                                <a href="{{ route('nilai.index', ['kelas_id' => $kelasId, 'mapel_id' => $mapelId, 'tanggal_nilai' => $penilaian->tanggal, 'rencana_pembelajaran_id' => $penilaian->rencana_pembelajaran_id, 'komponen_id' => $penilaian->komponen_id ?? 0, 'assessment_uuid' => $penilaian->assessment_uuid, 'source' => 'nilai', 'view' => 1]) }}" class="btn btn-sm btn-info" title="Lihat">
                                                    <i class="ti ti-eye"></i>
                                                </a>
                                                <a href="{{ route('nilai.index', ['kelas_id' => $kelasId, 'mapel_id' => $mapelId, 'tanggal_nilai' => $penilaian->tanggal, 'rencana_pembelajaran_id' => $penilaian->rencana_pembelajaran_id, 'komponen_id' => $penilaian->komponen_id ?? 0, 'assessment_uuid' => $penilaian->assessment_uuid, 'edit' => 1, 'source' => 'nilai']) }}" class="btn btn-sm btn-primary" title="Input Nilai">
                                                    <i class="ti ti-edit me-1"></i>Input Nilai
                                                </a>
                                                <form action="{{ route('nilai.penilaian.destroy') }}" method="POST" class="d-inline" onsubmit="return confirm('Hapus penilaian ini? Semua nilai yang terkait akan ikut terhapus.')">
                                                    @csrf
                                                    @method('DELETE')
                                                    <input type="hidden" name="tanggal" value="{{ $penilaian->tanggal }}">
                                                    <input type="hidden" name="rencana_pembelajaran_id" value="{{ $penilaian->rencana_pembelajaran_id }}">
                                                    <input type="hidden" name="komponen_id" value="{{ $penilaian->komponen_id ?? '' }}">
                                                    <input type="hidden" name="assessment_uuid" value="{{ $penilaian->assessment_uuid ?? '' }}">
                                                    <input type="hidden" name="kelas_id" value="{{ $kelasId }}">
                                                    <input type="hidden" name="mapel_id" value="{{ $mapelId }}">
                                                    <button type="submit" class="btn btn-sm btn-danger" title="Hapus">
                                                        <i class="ti ti-trash"></i>
                                                    </button>
                                                </form>
                                            </td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                            @else
                                <div class="empty py-4">
                                    <p class="empty-title">Belum ada penilaian tersimpan</p>
                                    <p class="empty-subtitle text-muted">Gunakan tombol Tambah Nilai untuk membuat penilaian pertama.</p>
                                </div>
                            @endif
                        @else
                            <div class="text-muted">Pilih kelas untuk melihat daftar penilaian yang sudah disimpan.</div>
                        @endif
                    @elseif($kelasId || $mapelId)
                        @if(($nilaiTableRows ?? collect())->count() > 0)
                        <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-2">
                            <div class="d-flex flex-wrap align-items-center gap-2">
                        <form method="POST" action="{{ route('nilai.store') }}" class="m-0">
                            @csrf
                            <input type="hidden" name="tanggal" value="{{ request('tanggal_nilai') }}">
                            <input type="hidden" name="kelas_id" value="{{ $kelasId }}">
                            <input type="hidden" name="mapel_id" value="{{ $mapelId }}">
                            <input type="hidden" name="rencana_pembelajaran_id" value="{{ request('rencana_pembelajaran_id') }}">
                            <input type="hidden" name="materi_pelajaran" value="{{ optional($items->first())->materi_pelajaran }}">
                            <input type="hidden" name="jenis_penilaian" value="{{ optional($items->first())->komponen_id === null ? '' : optional($items->first())->nama_komponen }}">
                                                        <input type="hidden" name="kategori_asesmen" value="{{ optional($items->first())->kategori_asesmen }}">
                            <input type="hidden" name="return_to" value="nilai">
                            <button type="submit" class="btn btn-sm btn-outline-primary">
                                <i class="ti ti-plus me-1"></i>Tambah Kolom Nilai
                            </button>
                        </form>
                        <button type="button" class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#modalImportGridNilai">
                            <i class="ti ti-file-import me-1"></i>Import Excel
                        </button>
                            </div>
                            <button type="submit" form="formSimpanNilai" class="btn btn-sm btn-primary">
                                <i class="ti ti-device-floppy me-1"></i>Simpan Nilai
                            </button>
                        </div>
                        @foreach($nilaiKomponenColumns as $komponen)
                        <form id="hapus-kolom-{{ md5($komponen->key) }}" method="POST" action="{{ route('nilai.penilaian.destroy') }}" class="d-none" onsubmit="return confirm('Hapus penilaian ini beserta semua nilainya?')">
                            @csrf
                            @method('DELETE')
                            <input type="hidden" name="tanggal" value="{{ request('tanggal_nilai') }}">
                            <input type="hidden" name="kelas_id" value="{{ $kelasId }}">
                            <input type="hidden" name="mapel_id" value="{{ $mapelId }}">
                            <input type="hidden" name="rencana_pembelajaran_id" value="{{ request('rencana_pembelajaran_id') }}">
                            <input type="hidden" name="komponen_id" value="{{ $komponen->id ?: '' }}">
                            <input type="hidden" name="assessment_uuid" value="{{ $komponen->assessment_uuid }}">
                            <input type="hidden" name="return_to" value="nilai">
                            <input type="hidden" name="edit" value="{{ $isEditMode ? 1 : '' }}">
                            <input type="hidden" name="source" value="{{ request('source') }}">
                        </form>
                        @endforeach
                        <form id="formSimpanNilai" method="POST" action="{{ route('nilai.update-batch') }}">
                            @csrf
                            <input type="hidden" name="kelas_id" value="{{ $kelasId }}">
                        <input type="hidden" name="mapel_id" value="{{ $mapelId }}">
                        <input type="hidden" name="tanggal_nilai" value="{{ request('tanggal_nilai') }}">
                        <input type="hidden" name="rencana_pembelajaran_id" value="{{ request('rencana_pembelajaran_id') }}">
                        <input type="hidden" name="komponen_id" value="{{ request('komponen_id') }}">
                        <input type="hidden" name="assessment_uuid" value="{{ $selectedAssessmentUuid }}">
                        <input type="hidden" name="edit" value="{{ $isEditMode ? 1 : '' }}">
                        <input type="hidden" name="source" value="{{ request('source') }}">
                        <div class="table-responsive">
                        <table class="table table-vcenter table-hover" id="tableNilai">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>Nama Siswa</th>
                                    @forelse(($nilaiKomponenColumns ?? collect()) as $komponen)
                                        <th class="text-center">
                                            <div class="d-flex align-items-center justify-content-center gap-2">
                                                <span>{{ strtoupper($komponen->nama) }}</span>
                                                <button type="submit" form="hapus-kolom-{{ md5($komponen->key) }}" class="btn btn-sm btn-outline-danger" title="Hapus kolom penilaian">
                                                    <i class="ti ti-trash"></i>
                                                </button>
                                            </div>
                                        </th>
                                    @empty
                                        <th class="text-center">KOMPONEN</th>
                                    @endforelse
                                    <th class="text-center">JUMLAH</th>
                                    <th class="text-center">RATA-RATA</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach(($nilaiTableRows ?? collect()) as $index => $row)
                                <tr>
                                    <td>{{ $index + 1 }}</td>
                                    <td>{{ $row->nama_siswa }}</td>
                                    @forelse(($nilaiKomponenColumns ?? collect()) as $komponen)
                                        @php
                                                    $komponenKey = $komponen->key;
                                                    $nilaiId = $row->nilai_id_by_komponen[$komponenKey] ?? null;
                                                    $nilai = $row->nilai_by_komponen[$komponenKey] ?? null;
                                        @endphp
                                        <td class="text-center">
                                            @if($nilaiId)
                                                <input type="number" name="nilai[{{ $nilaiId }}]" class="form-control form-control-sm text-center" min="0" max="100" step="0.01" value="{{ $nilai }}" style="width: 100px; margin: 0 auto;">
                                            @else
                                                <span class="text-muted">-</span>
                                            @endif
                                        </td>
                                    @empty
                                        <td class="text-center">-</td>
                                    @endforelse
                                    <td class="text-center fw-bold">{{ $row->jumlah !== null ? number_format($row->jumlah, 2) : '-' }}</td>
                                    <td class="text-center fw-bold">{{ $row->rata_rata !== null ? number_format($row->rata_rata, 2) : '-' }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                        </div>
                    </form>
                    @else
                    <div class="empty">
                        <div class="empty-img"><img src="{{ asset('tabler/static/illustrations/undraw_printing_invoices_5r4r.svg') }}" height="128" alt="">
                        </div>
                        <p class="empty-title">Belum ada data nilai</p>
                        <p class="empty-subtitle text-muted">
                            Tambahkan nilai siswa dengan mengklik tombol "Tambah Nilai" di atas.
                        </p>
                        @unless($isSiswaWithoutClassPosition || $showBackNavigation)
                        <div class="empty-action">
                            <a href="#" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalTambahNilai">
                                <i class="ti ti-plus"></i>
                                Tambah Nilai Pertama
                            </a>
                        </div>
                        @endunless
                    </div>
                    @endif
                @else
                    <div class="text-muted">Pilih kelas untuk membuka penilaian.</div>
                @endif
            </div>
        </div>
        @endif

        
        <div class="row mt-2">
            <div class="col-md-12">
                <div class="card card-sm">
                    <div class="card-body">
                        <div class="row align-items-center">
                            <div class="col-auto">
                                <i class="ti ti-info-circle text-blue" style="font-size: 32px;"></i>
                            </div>
                            <div class="col">
                                <div class="font-weight-medium">Informasi</div>
                                <div class="text-muted">
                                    Sistem nilai menggunakan tabel <strong>nilai_harian</strong> dan terhubung ke <strong>rencana pembelajaran</strong>.
                                    Pastikan data guru, kelas, mata pelajaran, dan rencana pembelajaran sudah tersedia sebelum membuat nilai harian.
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>


<div class="modal fade" id="modalTambahNilai" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Tambah Nilai Harian</h5>
                <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
            </div>
            <div class="modal-body">
                <form method="POST" action="{{ route('nilai.store') }}" id="nilaiForm">
                    @csrf
                    <div class="mb-2">
                        <label class="form-label">Tanggal</label>
                        <input type="date" class="form-control" name="tanggal" value="{{ $selectedTanggalNilai }}" required>
                    </div>
                    <input type="hidden" name="return_to" value="nilai">
                    <input type="hidden" name="rencana_pembelajaran_id" value="{{ request('rencana_pembelajaran_id') }}">
                    <div class="mb-2">
                        <label class="form-label">Kelas</label>
                        <select class="form-select" name="kelas_id" id="nilaiKelasSelect" required @if(empty($kelasOptions) || $kelasOptions->isEmpty()) disabled @endif>
                            <option value="">Pilih Kelas...</option>
                            @foreach($kelasOptions as $kelas)
                                <option value="{{ $kelas->id }}" {{ (string) $kelasId === (string) $kelas->id ? 'selected' : '' }}>{{ $kelas->nama_kelas }}</option>
                            @endforeach
                        </select>
                        @if(empty($kelasOptions) || $kelasOptions->isEmpty())
                            <small class="text-muted d-block mt-1">Tidak ada kelas sesuai jadwal Anda.</small>
                        @endif
                    </div>
                    <div class="mb-2">
                        <label class="form-label">Mata Pelajaran</label>
                        <select class="form-select" name="mapel_id" id="nilaiMapelSelect" required @if(empty($mapelOptions) || $mapelOptions->isEmpty()) disabled @endif>
                            <option value="">Pilih Mata Pelajaran...</option>
                            @foreach($mapelOptions as $mapel)
                                <option value="{{ $mapel->id }}" {{ (string) $mapelId === (string) $mapel->id ? 'selected' : '' }}>{{ $mapel->nama_mapel }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-2">
                        <label class="form-label">Materi Pelajaran</label>
                        <input type="text" class="form-control" name="materi_pelajaran" placeholder="Contoh: Menjelaskan teks deskripsi / latihan soal bilangan">
                        <small class="text-muted d-block mt-1">Opsional, bisa diisi manual bila tidak memakai modul ajar.</small>
                    </div>
                    <div class="mb-2">
                        <label class="form-label">Kategori Asesmen</label>
                        <select class="form-select" name="kategori_asesmen" required>
                            <option value="">Pilih kategori asesmen...</option>
                            <option value="formatif">Asesmen Formatif</option>
                            <option value="sumatif">Asesmen Sumatif</option>
                        </select>
                    </div>
                    <div class="mb-2">
                        <label class="form-label">Jenis Penilaian</label>
                        <select class="form-select" name="jenis_penilaian">
                            <option value="">Harian / Umum</option>
                            @php
                                $jenisPenilaianList = [
                                    'Asesmen awal/diagnostik',
                                    'Observasi',
                                    'Tanya jawab',
                                    'Diskusi',
                                    'Kuis',
                                    'Latihan/tugas',
                                    'Praktik/unjuk kerja',
                                    'Penilaian diri',
                                    'Penilaian antarteman',
                                    'Refleksi',
                                    'Umpan balik',
                                    'Tindak lanjut (remedial/pengayaan)',
                                ];
                            @endphp
                            @foreach($jenisPenilaianList as $jenis)
                                <option value="{{ $jenis }}">{{ $jenis }}</option>
                            @endforeach
                        </select>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                <button type="submit" class="btn btn-primary" form="nilaiForm">Simpan</button>
            </div>
        </div>
    </div>
</div>


<div class="modal fade" id="modalImportNilai" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Import Nilai Harian (Excel)</h5>
                <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
            </div>
            <div class="modal-body">
                <form method="POST" action="{{ route('nilai.import') }}" enctype="multipart/form-data" id="nilaiImportForm">
                    @csrf
                    <input type="hidden" name="assessment_uuid" id="nilaiImportAssessmentUuid">
                    <input type="hidden" name="komponen_id" id="nilaiImportKomponenId">
                    <input type="hidden" name="return_to" value="nilai">
                    <div class="text-muted small mb-2" id="nilaiImportTargetLabel"></div>
                    <div class="mb-2">
                        <a
                            href="{{ $kelasId ? route('nilai.template', ['kelas_id' => $kelasId]) : '#' }}"
                            data-template-base="{{ route('nilai.template') }}"
                            id="nilaiTemplateDownloadBtn"
                            class="btn btn-outline-secondary btn-sm {{ $kelasId ? '' : 'disabled' }}"
                            @if(!$kelasId) aria-disabled="true" @endif
                        >
                            <i class="ti ti-download me-1"></i>Download Template
                        </a>
                        @if(!$kelasId)
                            <small class="text-muted d-block mt-1">Pilih Menu Cepat Penilaian dulu untuk menentukan kelas.</small>
                        @endif
                    </div>
                    <div class="mb-2">
                        <label class="form-label">Tanggal</label>
                        <input type="date" class="form-control" name="tanggal" id="nilaiImportTanggal" value="{{ $selectedTanggalNilai }}" required>
                    </div>
                    <div class="mb-2">
                        <label class="form-label">Kelas</label>
                        <select class="form-select" name="kelas_id" id="nilaiImportKelasSelect" required>
                            <option value="">Pilih Kelas...</option>
                            @foreach($kelasOptions as $kelas)
                                <option value="{{ $kelas->id }}" {{ (string) $kelasId === (string) $kelas->id ? 'selected' : '' }}>{{ $kelas->nama_kelas }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-2">
                        <label class="form-label">Mata Pelajaran</label>
                        <select class="form-select" name="mapel_id" id="nilaiImportMapelSelect" required>
                            <option value="">Pilih Mata Pelajaran...</option>
                            @foreach($mapelOptions as $mapel)
                                <option value="{{ $mapel->id }}" {{ (string) $mapelId === (string) $mapel->id ? 'selected' : '' }}>{{ $mapel->nama_mapel }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-2">
                        <label class="form-label">Rencana Pembelajaran</label>
                        <select class="form-select" name="rencana_pembelajaran_id" id="nilaiImportRencanaSelect">
                            <option value="">Tanpa Rencana</option>
                        </select>
                        <small class="text-muted d-block mt-1">Kolom wajib: nis/nisn dan nilai.</small>
                    </div>
                    <div class="mb-2">
                        <label class="form-label">Kategori Asesmen</label>
                        <select class="form-select" name="kategori_asesmen" required>
                            <option value="">Pilih kategori asesmen...</option>
                            <option value="formatif">Asesmen Formatif</option>
                            <option value="sumatif">Asesmen Sumatif</option>
                        </select>
                    </div>
                    <div class="mb-2">
                        <label class="form-label">File Excel</label>
                        <input type="file" class="form-control" name="file" accept=".xlsx,.xls" required>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                <button type="submit" class="btn btn-primary" form="nilaiImportForm">Import</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="modalImportGridNilai" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Import Nilai dari Template Grid</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form method="POST" action="{{ route('nilai.import-input') }}" enctype="multipart/form-data" id="nilaiGridImportForm">
                    @csrf
                    <input type="hidden" name="kelas_id" value="{{ $kelasId }}">
                    <input type="hidden" name="mapel_id" value="{{ $mapelId }}">
                    <input type="hidden" name="tanggal_nilai" value="{{ $selectedTanggalNilai }}">
                    <input type="hidden" name="rencana_pembelajaran_id" value="{{ request('rencana_pembelajaran_id') }}">
                    <input type="hidden" name="komponen_id" value="{{ request('komponen_id') }}">
                    <input type="hidden" name="assessment_uuid" value="{{ $selectedAssessmentUuid }}">
                    <input type="hidden" name="confirm_import" id="nilaiGridConfirmImport" value="">
                    <input type="hidden" name="confirm_overwrite" id="nilaiGridConfirmOverwrite" value="">
                    <div class="mb-3">
                        <a href="{{ route('nilai.template-input', array_filter([
                            'kelas_id' => $kelasId,
                            'mapel_id' => $mapelId,
                            'tanggal_nilai' => $selectedTanggalNilai,
                            'rencana_pembelajaran_id' => request('rencana_pembelajaran_id'),
                            'komponen_id' => request('komponen_id'),
                            'assessment_uuid' => $selectedAssessmentUuid,
                        ], function ($value) { return $value !== null && $value !== ''; })) }}" class="btn btn-outline-secondary btn-sm">
                            <i class="ti ti-download me-1"></i>Unduh Template Grid
                        </a>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">File Template</label>
                        <input type="file" class="form-control" name="file" id="nilaiGridImportFile" accept=".xlsx,.xls" required>
                    </div>
                    <div id="nilaiGridImportPreview" class="d-none mt-3">
                        <div id="nilaiGridImportSummary" class="alert alert-info mb-2"></div>
                        <div class="table-responsive">
                            <table class="table table-sm table-bordered" id="nilaiGridPreviewTable">
                                <thead></thead>
                                <tbody></tbody>
                            </table>
                        </div>
                        <div id="nilaiGridPreviewLimit" class="small text-muted"></div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                <button type="button" class="btn btn-outline-primary" id="nilaiGridPreviewButton">Cek Data</button>
                <button type="submit" class="btn btn-primary" id="nilaiGridImportButton" form="nilaiGridImportForm" disabled>Import Nilai</button>
            </div>
        </div>
    </div>
</div>

@endsection

@push('js')
<script>
    // Search functionality
    document.getElementById('searchInput')?.addEventListener('keyup', function() {
        let filter = this.value.toLowerCase();
        let rows = document.querySelectorAll('#tableNilai tbody tr');
        
        rows.forEach(row => {
            let text = row.textContent.toLowerCase();
            row.style.display = text.includes(filter) ? '' : 'none';
        });
    });

    var nilaiKelasSelect = document.getElementById('nilaiKelasSelect');
    var nilaiMapelSelect = document.getElementById('nilaiMapelSelect');
    var nilaiRencanaSelect = document.getElementById('nilaiRencanaSelect');
    var nilaiImportKelasSelect = document.getElementById('nilaiImportKelasSelect');
    var nilaiImportMapelSelect = document.getElementById('nilaiImportMapelSelect');
    var nilaiImportRencanaSelect = document.getElementById('nilaiImportRencanaSelect');
    var nilaiImportAssessmentUuid = document.getElementById('nilaiImportAssessmentUuid');
    var nilaiImportKomponenId = document.getElementById('nilaiImportKomponenId');
    var nilaiImportTargetLabel = document.getElementById('nilaiImportTargetLabel');
    var nilaiImportTanggal = document.getElementById('nilaiImportTanggal');
    var nilaiTemplateDownloadBtn = document.getElementById('nilaiTemplateDownloadBtn');
    var nilaiGridImportForm = document.getElementById('nilaiGridImportForm');
    var nilaiGridImportFile = document.getElementById('nilaiGridImportFile');
    var nilaiGridConfirmImport = document.getElementById('nilaiGridConfirmImport');
    var nilaiGridConfirmOverwrite = document.getElementById('nilaiGridConfirmOverwrite');
    var nilaiGridPreviewButton = document.getElementById('nilaiGridPreviewButton');
    var nilaiGridImportButton = document.getElementById('nilaiGridImportButton');
    var nilaiGridImportPreview = document.getElementById('nilaiGridImportPreview');
    var nilaiGridImportSummary = document.getElementById('nilaiGridImportSummary');
    var nilaiGridPreviewTable = document.getElementById('nilaiGridPreviewTable');
    var nilaiGridPreviewLimit = document.getElementById('nilaiGridPreviewLimit');
    var mapelByKelas = @json(($mapelByKelas ?? collect())->toArray());
    var rencanaByMapel = @json($rencanaByMapel ?? []);
    var allMapelOptions = @json(($mapelOptions ?? collect())->map(function($mapel) {
        return ['id' => $mapel->id, 'nama' => $mapel->nama_mapel];
    })->values()->toArray());

    // Initialize tooltips (letakkan setelah semua var declaration untuk menghindari ReferenceError)
    if (typeof $ !== 'undefined' && $.fn && typeof $.fn.tooltip === 'function') {
        $('[data-bs-toggle="tooltip"]').tooltip();
    }

    function resetSelect(selectEl, placeholder) {
        if (!selectEl) return;
        selectEl.innerHTML = '';
        const option = document.createElement('option');
        option.value = '';
        option.textContent = placeholder;
        selectEl.appendChild(option);
    }

    function fillOptions(selectEl, items, placeholder) {
        resetSelect(selectEl, placeholder);
        if (!selectEl || !items || !items.length) return;
        items.forEach(function(item) {
            const option = document.createElement('option');
            option.value = item.id;
            option.textContent = item.nama;
            selectEl.appendChild(option);
        });
    }

    function getRencanaItems(kelasId, mapelId) {
        // Coba cari berdasarkan kelas + mapel dulu
        if (kelasId && mapelId && rencanaByMapel[kelasId] && rencanaByMapel[kelasId][mapelId]) {
            return rencanaByMapel[kelasId][mapelId].map(function(item) {
                return { id: item.id, nama: item.judul };
            });
        }

        // Fallback: jika tidak ada untuk kelas tertentu, tampilkan semua rencana untuk mapel yang dipilih
        if (mapelId && rencanaByMapel['*'] && rencanaByMapel['*'][mapelId]) {
            return rencanaByMapel['*'][mapelId].map(function(item) {
                return { id: item.id, nama: item.judul };
            });
        }

        return [];
    }

    function updateRencanaOptions() {
        const kelasId = nilaiKelasSelect ? nilaiKelasSelect.value : '';
        const mapelId = nilaiMapelSelect ? nilaiMapelSelect.value : '';
        const rencanaItems = getRencanaItems(kelasId, mapelId);
        fillOptions(nilaiRencanaSelect, rencanaItems, 'Pilih Rencana...');
    }

    function updateImportRencanaOptions() {
        const kelasId = nilaiImportKelasSelect ? nilaiImportKelasSelect.value : '';
        const mapelId = nilaiImportMapelSelect ? nilaiImportMapelSelect.value : '';
        const rencanaItems = getRencanaItems(kelasId, mapelId);
        fillOptions(nilaiImportRencanaSelect, rencanaItems, 'Pilih Rencana...');
    }

    function onKelasChange() {
        const kelasId = nilaiKelasSelect ? nilaiKelasSelect.value : '';
        const selectedMapelId = nilaiMapelSelect ? nilaiMapelSelect.value : '';
        const mapelItems = kelasId && mapelByKelas[kelasId] ? mapelByKelas[kelasId] : allMapelOptions;
        fillOptions(nilaiMapelSelect, mapelItems, 'Pilih Mata Pelajaran...');
        if (nilaiMapelSelect && mapelItems.some(function(item) { return String(item.id) === String(selectedMapelId); })) {
            nilaiMapelSelect.value = selectedMapelId;
        }
        updateRencanaOptions();
    }

    function updateTemplateDownloadLink() {
        if (!nilaiTemplateDownloadBtn) return;
        const kelasId = nilaiImportKelasSelect ? nilaiImportKelasSelect.value : '';
        const baseUrl = nilaiTemplateDownloadBtn.dataset.templateBase || '#';

        if (!kelasId) {
            nilaiTemplateDownloadBtn.setAttribute('href', '#');
            nilaiTemplateDownloadBtn.classList.add('disabled');
            nilaiTemplateDownloadBtn.setAttribute('aria-disabled', 'true');
            return;
        }

        nilaiTemplateDownloadBtn.setAttribute('href', baseUrl + '?kelas_id=' + encodeURIComponent(kelasId));
        nilaiTemplateDownloadBtn.classList.remove('disabled');
        nilaiTemplateDownloadBtn.removeAttribute('aria-disabled');
    }

    function onImportKelasChange() {
        const kelasId = nilaiImportKelasSelect ? nilaiImportKelasSelect.value : '';
        const selectedMapelId = nilaiImportMapelSelect ? nilaiImportMapelSelect.value : '';
        const mapelItems = kelasId && mapelByKelas[kelasId] ? mapelByKelas[kelasId] : allMapelOptions;

        fillOptions(nilaiImportMapelSelect, mapelItems, 'Pilih Mata Pelajaran...');
        if (nilaiImportMapelSelect && mapelItems.some(function(item) { return String(item.id) === String(selectedMapelId); })) {
            nilaiImportMapelSelect.value = selectedMapelId;
        }
        updateImportRencanaOptions();
        updateTemplateDownloadLink();
    }

    if (nilaiKelasSelect) {
        nilaiKelasSelect.addEventListener('change', onKelasChange);
    }
    if (nilaiMapelSelect) {
        nilaiMapelSelect.addEventListener('change', updateRencanaOptions);
    }
    if (nilaiImportKelasSelect) {
        nilaiImportKelasSelect.addEventListener('change', onImportKelasChange);
    }
    if (nilaiImportMapelSelect) {
        nilaiImportMapelSelect.addEventListener('change', updateImportRencanaOptions);
    }

    const nilaiModal = document.getElementById('modalTambahNilai');
    const nilaiImportModal = document.getElementById('modalImportNilai');
    if (nilaiModal) {
        nilaiModal.addEventListener('shown.bs.modal', function() {
            onKelasChange();
            updateRencanaOptions();
            if (window && window.console) {
                console.log('rencanaByMapel keys', Object.keys(rencanaByMapel || {}));
            }
        });
    }

    if (nilaiMapelSelect && nilaiMapelSelect.value) {
        updateRencanaOptions();
    }

    if (nilaiImportModal) {
        nilaiImportModal.addEventListener('show.bs.modal', function(event) {
            const trigger = event.relatedTarget;
            if (!trigger || !trigger.hasAttribute('data-assessment-uuid')) {
                if (nilaiImportAssessmentUuid) nilaiImportAssessmentUuid.value = '';
                if (nilaiImportKomponenId) nilaiImportKomponenId.value = '';
                if (nilaiImportTargetLabel) nilaiImportTargetLabel.textContent = '';
                return;
            }

            if (nilaiImportTanggal) nilaiImportTanggal.value = trigger.dataset.tanggal || '';
            if (nilaiImportKelasSelect) nilaiImportKelasSelect.value = trigger.dataset.kelasId || '';
            onImportKelasChange();
            if (nilaiImportMapelSelect) nilaiImportMapelSelect.value = trigger.dataset.mapelId || '';
            updateImportRencanaOptions();
            if (nilaiImportRencanaSelect) nilaiImportRencanaSelect.value = trigger.dataset.rencanaId || '';
            if (nilaiImportAssessmentUuid) nilaiImportAssessmentUuid.value = trigger.dataset.assessmentUuid || '';
            if (nilaiImportKomponenId) nilaiImportKomponenId.value = trigger.dataset.komponenId || '';
            if (nilaiImportTargetLabel) nilaiImportTargetLabel.textContent = 'Kolom tujuan: ' + (trigger.dataset.columnName || 'Nilai');
        });
        nilaiImportModal.addEventListener('shown.bs.modal', function() {
            updateTemplateDownloadLink();
        });
    }

    if (nilaiGridImportForm && nilaiGridPreviewButton) {
        let gridImportOverwriteCount = 0;
        const resetGridImportPreview = function() {
            nilaiGridConfirmImport.value = '';
            nilaiGridConfirmOverwrite.value = '';
            gridImportOverwriteCount = 0;
            nilaiGridImportButton.disabled = true;
            nilaiGridImportPreview.classList.add('d-none');
            nilaiGridPreviewTable.querySelector('thead').replaceChildren();
            nilaiGridPreviewTable.querySelector('tbody').replaceChildren();
            nilaiGridPreviewLimit.textContent = '';
        };

        const showGridImportErrors = function(errors) {
            resetGridImportPreview();
            nilaiGridImportPreview.classList.remove('d-none');
            nilaiGridImportSummary.className = 'alert alert-danger mb-2';
            const messages = Array.isArray(errors) ? errors : Object.values(errors || {}).flat();
            nilaiGridImportSummary.textContent = messages.join(' ');
        };

        nilaiGridImportFile.addEventListener('change', resetGridImportPreview);
        nilaiGridPreviewButton.addEventListener('click', async function() {
            if (!nilaiGridImportFile.files.length) {
                showGridImportErrors(['Pilih file template terlebih dahulu.']);
                return;
            }

            resetGridImportPreview();
            nilaiGridPreviewButton.disabled = true;
            nilaiGridPreviewButton.textContent = 'Memeriksa...';

            try {
                const response = await fetch(@json(route('nilai.preview-input')), {
                    method: 'POST',
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                    body: new FormData(nilaiGridImportForm),
                });
                const result = await response.json().catch(function() { return {}; });
                if (!response.ok) {
                    showGridImportErrors(result.errors || [result.message || ('Server menolak pemeriksaan file (HTTP ' + response.status + ').')]);
                    return;
                }

                const columns = ['No', 'Nama', 'Jenis Penilaian', 'Kelas'].concat(result.columns || []);
                const headerRow = document.createElement('tr');
                columns.forEach(function(label) {
                    const cell = document.createElement('th');
                    cell.textContent = label;
                    headerRow.appendChild(cell);
                });
                nilaiGridPreviewTable.querySelector('thead').replaceChildren(headerRow);

                const previewRows = (result.rows || []).map(function(values) {
                    const row = document.createElement('tr');
                    values.forEach(function(value) {
                        const cell = document.createElement('td');
                        cell.textContent = value === null || value === '' ? '-' : String(value);
                        row.appendChild(cell);
                    });
                    return row;
                });
                nilaiGridPreviewTable.querySelector('tbody').replaceChildren(...previewRows);
                nilaiGridImportPreview.classList.remove('d-none');
                nilaiGridImportSummary.className = 'alert alert-success mb-2';
                nilaiGridImportSummary.textContent = 'Akan mengimpor ' + result.column_count + ' kolom penilaian untuk ' + result.row_count + ' siswa. ' + result.new_column_count + ' kolom baru akan dibuat otomatis.';
                gridImportOverwriteCount = Number(result.overwrite_count || 0);
                if (gridImportOverwriteCount > 0) {
                    nilaiGridImportSummary.textContent += ' ' + gridImportOverwriteCount + ' nilai tersimpan akan ditimpa.';
                }
                nilaiGridPreviewLimit.textContent = 'Pratinjau menampilkan maksimal 10 baris pertama. Periksa kode jenis dan nilai sebelum melanjutkan.';
                nilaiGridConfirmImport.value = '1';
                nilaiGridImportButton.disabled = false;
            } catch (error) {
                showGridImportErrors(['Gagal memeriksa file: ' + (error.message || 'kesalahan jaringan')]);
            } finally {
                nilaiGridPreviewButton.disabled = false;
                nilaiGridPreviewButton.textContent = 'Cek Data';
            }
        });

        nilaiGridImportForm.addEventListener('submit', function(event) {
            if (nilaiGridConfirmImport.value !== '1') {
                event.preventDefault();
                showGridImportErrors(['Klik Cek Data dan pastikan pratinjau sesuai sebelum import.']);
                return;
            }

            if (gridImportOverwriteCount > 0 && nilaiGridConfirmOverwrite.value !== '1') {
                event.preventDefault();
                const confirmed = window.confirm('Ada ' + gridImportOverwriteCount + ' nilai tersimpan yang akan ditimpa. Lanjutkan import?');
                if (confirmed) {
                    nilaiGridConfirmOverwrite.value = '1';
                    nilaiGridImportForm.requestSubmit(nilaiGridImportButton);
                }
            }
        });
    }

    onImportKelasChange();
</script>
@endpush
