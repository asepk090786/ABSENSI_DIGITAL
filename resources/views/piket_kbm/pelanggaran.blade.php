@extends('layouts.app')

@section('title', 'Pelanggaran Siswa')

@section('content')
<div class="container-fluid">
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="close" data-bs-dismiss="alert"><span aria-hidden="true">&times;</span></button>
        </div>
    @endif

    @if($errors->any())
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <ul class="mb-0">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
            <button type="button" class="close" data-bs-dismiss="alert"><span aria-hidden="true">&times;</span></button>
        </div>
    @endif

    <div class="card mb-2">
        <div class="card-header border-0 pt-3 pb-2">
            <h3 class="card-title fw-semibold m-0">Pelanggaran - Menu Cepat Kelas Aktif</h3>
        </div>
        <div class="card-body">
            <form method="GET" action="{{ route('piket.pelanggaran.index') }}" class="row g-2 align-items-end mb-2">
                <div class="col-12 col-md-3">
                    <label class="form-label">Tanggal</label>
                    <input type="date" class="form-control" name="tanggal" value="{{ $selectedTanggal }}">
                </div>
                <div class="col-auto">
                    <button type="submit" class="btn btn-primary"><i class="ti ti-search me-1"></i>Tampilkan</button>
                </div>
            </form>

            <div class="d-flex flex-wrap gap-2">
                @forelse($kelasAktif as $kelas)
                    <a href="{{ route('piket.pelanggaran.index', ['kelas_id' => $kelas->id, 'tanggal' => $selectedTanggal]) }}" class="btn {{ (string) $kelasId === (string) $kelas->id ? 'btn-primary' : 'btn-outline-primary' }} btn-sm">
                        {{ $kelas->nama_kelas }}
                    </a>
                @empty
                    <span class="text-muted">Belum ada kelas aktif.</span>
                @endforelse
            </div>
        </div>
    </div>

    @if($kelasId)
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h3 class="card-title fw-semibold m-0">Input Absensi + Pelanggaran Siswa</h3>
                <small class="text-muted">
                    @if($jamKeSatu)
                        Jam ke-1: {{ $jamKeSatu->jam_mulai }} | Perkiraan keterlambatan saat ini: {{ $lateMinutesPreview }} menit
                    @else
                        Jam ke-1 belum diatur
                    @endif
                </small>
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('piket.pelanggaran.store') }}">
                    @csrf
                    <input type="hidden" name="kelas_id" value="{{ $kelasId }}">
                    <input type="hidden" name="tanggal" value="{{ $selectedTanggal }}">

                    <div class="table-responsive">
                        <table class="table table-bordered table-striped">
                            <thead>
                                <tr>
                                    <th rowspan="2" class="align-middle">No</th>
                                    <th rowspan="2" class="align-middle">NIS</th>
                                    <th rowspan="2" class="align-middle">Nama Siswa</th>
                                    <th rowspan="2" class="align-middle">Jabatan Kelas</th>
                                    <th rowspan="2" class="align-middle">Absensi Kelas</th>
                                    <th colspan="5" class="text-center">Status Absensi</th>
                                    <th rowspan="2" class="align-middle">Jenis Pelanggaran</th>
                                    <th rowspan="2" class="align-middle">Point</th>
                                    <th rowspan="2" class="align-middle">Keterangan</th>
                                </tr>
                                <tr>
                                    <th class="text-center">Hadir</th>
                                    <th class="text-center">Terlambat</th>
                                    <th class="text-center">Sakit</th>
                                    <th class="text-center">Izin</th>
                                    <th class="text-center">Alpa</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($siswaList as $index => $siswa)
                                    @php
                                        $existing = $existingBySiswa->get($siswa->id);
                                        $absensi = $absensiBySiswa->get($siswa->id);

                                        $normalizeStatus = function ($status) {
                                            $status = strtolower(trim((string) $status));
                                            $statusMap = [
                                                'alpha' => 'alpa',
                                                'absen' => 'alpa',
                                                'alfa' => 'alpa',
                                                'telat' => 'terlambat',
                                                'late' => 'terlambat',
                                                'hadir' => 'hadir',
                                                'terlambat' => 'terlambat',
                                                'sakit' => 'sakit',
                                                'izin' => 'izin',
                                                'alpa' => 'alpa',
                                            ];
                                            return $statusMap[$status] ?? null;
                                        };

                                        $absensiStatus = $normalizeStatus($absensi->status ?? null);
                                        $pelanggaranStatus = $normalizeStatus($existing->status_absensi ?? null);
                                        $oldStatus = $normalizeStatus(old('status.' . $siswa->id));

                                        if ($oldStatus) {
                                            $selectedStatus = $oldStatus;
                                        } elseif ($absensiStatus) {
                                            $selectedStatus = $absensiStatus;
                                        } elseif ($pelanggaranStatus) {
                                            $selectedStatus = $pelanggaranStatus;
                                        } else {
                                            $selectedStatus = 'hadir';
                                        }

                                        $isLocked = $selectedStatus === 'terlambat';
                                    @endphp
                                    <tr>
                                        <td>{{ $index + 1 }}</td>
                                        <td>{{ $siswa->nis ?: '-' }}</td>
                                        <td>{{ $siswa->nama }}</td>
                                        <td class="text-center align-middle">{{ $siswa->jabatan_kelas ?: '-' }}</td>
                                        <td class="text-center align-middle">
                                            @if($absensi)
                                                @php
                                                    $badgeClass = 'secondary';
                                                    switch ($absensiStatus) {
                                                        case 'hadir': $badgeClass = 'success'; break;
                                                        case 'terlambat': $badgeClass = 'warning'; break;
                                                        case 'sakit': $badgeClass = 'info'; break;
                                                        case 'izin': $badgeClass = 'primary'; break;
                                                        case 'alpa': $badgeClass = 'danger'; break;
                                                    }
                                                @endphp
                                                <span class="badge bg-{{ $badgeClass }}">{{ ucfirst($absensiStatus) }}</span>
                                                @if(!empty($absensi->keterangan))
                                                    <div class="small text-muted">{{ $absensi->keterangan }}</div>
                                                @endif
                                            @else
                                                <span class="text-muted">Belum</span>
                                            @endif
                                        </td>
                                        <td class="text-center align-middle">
                                            <input class="form-check-input" type="radio" name="status[{{ $siswa->id }}]" value="hadir" {{ $selectedStatus === 'hadir' ? 'checked' : '' }} {{ $isLocked ? 'disabled' : '' }} required>
                                        </td>
                                        <td class="text-center align-middle">
                                            <input class="form-check-input" type="radio" name="status[{{ $siswa->id }}]" value="terlambat" {{ $selectedStatus === 'terlambat' ? 'checked' : '' }} {{ $isLocked ? 'disabled' : '' }}>
                                        </td>
                                        <td class="text-center align-middle">
                                            <input class="form-check-input" type="radio" name="status[{{ $siswa->id }}]" value="sakit" {{ $selectedStatus === 'sakit' ? 'checked' : '' }} {{ $isLocked ? 'disabled' : '' }}>
                                        </td>
                                        <td class="text-center align-middle">
                                            <input class="form-check-input" type="radio" name="status[{{ $siswa->id }}]" value="izin" {{ $selectedStatus === 'izin' ? 'checked' : '' }} {{ $isLocked ? 'disabled' : '' }}>
                                        </td>
                                        <td class="text-center align-middle">
                                            <input class="form-check-input" type="radio" name="status[{{ $siswa->id }}]" value="alpa" {{ $selectedStatus === 'alpa' ? 'checked' : '' }} {{ $isLocked ? 'disabled' : '' }}>
                                        </td>
                                        @if($isLocked)
                                            <input type="hidden" name="status[{{ $siswa->id }}]" value="terlambat">
                                        @endif
                                        <td>
                                            <select name="jenis_pelanggaran_id[{{ $siswa->id }}]" class="form-select form-select-sm" {{ $isLocked ? 'disabled' : '' }}>
                                                <option value="">Pilih Jenis</option>
                                                @foreach($jenisPelanggaranOptions as $jenis)
                                                    <option value="{{ $jenis->id }}" data-poin="{{ $jenis->poin_default }}" {{ (string) old('jenis_pelanggaran_id.' . $siswa->id, $existing->jenis_pelanggaran_id ?? '') === (string) $jenis->id ? 'selected' : '' }}>{{ $jenis->nama }}</option>
                                                @endforeach
                                            </select>
                                            @if($isLocked && !empty($existing->jenis_pelanggaran_id))
                                                <input type="hidden" name="jenis_pelanggaran_id[{{ $siswa->id }}]" value="{{ $existing->jenis_pelanggaran_id }}">
                                            @endif
                                        </td>
                                        <td>
                                            <input type="number" min="0" max="1000" class="form-control form-control-sm" name="point[{{ $siswa->id }}]" value="{{ old('point.' . $siswa->id, $existing->poin_pelanggaran ?? 0) }}" placeholder="0" {{ $isLocked ? 'readonly' : '' }}>
                                        </td>
                                        <td>
                                            <input type="text" class="form-control form-control-sm" name="pelanggaran[{{ $siswa->id }}]" value="{{ old('pelanggaran.' . $siswa->id, $existing->deskripsi_pelanggaran ?? '') }}" placeholder="Contoh: Terlambat masuk kelas" {{ $isLocked ? 'readonly' : '' }}>
                                        </td>
                                        <td>
                                            <input type="text" class="form-control form-control-sm" name="keterangan[{{ $siswa->id }}]" value="{{ old('keterangan.' . $siswa->id) }}" placeholder="Opsional" {{ $isLocked ? 'readonly' : '' }}>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="11" class="text-center text-muted">Tidak ada siswa di kelas ini.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    @if($siswaList->isNotEmpty())
                    <div class="text-end">
                        <button type="submit" class="btn btn-primary">
                            <i class="ti ti-device-floppy me-1"></i>Simpan
                        </button>
                    </div>
                    @endif
                </form>
            </div>
        </div>
    @endif
</div>
@endsection
