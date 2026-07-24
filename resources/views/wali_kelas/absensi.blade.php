@extends('layouts.app', ['pageSlug' => 'wali-kelas'])

@section('title', 'Absensi Kelas - Wali Kelas')

@section('content')
<div class="container-fluid wali-kelas-absensi">
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <div>
                        <h4 class="card-title fw-semibold mb-1">
                            <i class="ti ti-calendar-check me-2"></i>Absensi Kelas
                        </h4>
                        <p class="text-muted mb-0">Kelas: <strong>{{ $kelasBinaan->nama_kelas ?? '-' }}</strong></p>
                    </div>
                    <div class="d-flex gap-2">
                        <a href="{{ route('absensi.create', ['kelas_id' => $kelasBinaan->id, 'tanggal' => $selectedTanggal ?? now()->format('Y-m-d'), 'back' => route('wali_kelas.absensi')]) }}" class="btn btn-primary">
                            <i class="ti ti-pencil me-1"></i>Input Absensi
                        </a>
                        <a href="{{ route('wali_kelas.index') }}" class="btn btn-secondary">
                            <i class="ti ti-arrow-left me-1"></i>Kembali
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <h5 class="mb-2">Filter Absensi</h5>
                            <form method="GET" action="{{ route('wali_kelas.absensi') }}" class="d-flex gap-2">
                                <input type="date" class="form-control" name="tanggal" value="{{ old('tanggal', $selectedTanggal ?? now()->format('Y-m-d')) }}">
                                <button type="submit" class="btn btn-primary">Tampilkan</button>
                            </form>
                        </div>
                        <div class="col-md-6 text-md-end align-self-end">
                            <div class="text-muted">Tanggal terpilih: {{ 
                                
                                (
                                    $selectedTanggal ? 
                                        \Carbon\Carbon::parse($selectedTanggal)->format('d/m/Y') : 
                                        now()->format('d/m/Y') 
                                )
                            }}</div>
                        </div>
                    </div>

                    <div class="mb-4">
                        <h5 class="mb-2">Ringkasan Kehadiran</h5>
                        <div class="d-flex flex-wrap gap-2 mb-3">
                            <span class="badge bg-danger">Total Tidak Masuk: {{ $absensiSummary['tidak_masuk'] ?? 0 }}</span>
                            <span class="badge" style="background:#f59e0b;color:#fff;">Total Terlambat: {{ $absensiSummary['terlambat'] ?? 0 }}</span>
                        </div>
                        <h5 class="mb-2">Akumulasi Terlambat Bulan Ini</h5>
                        @if(($akumulasiTerlambatBulanan ?? collect())->isEmpty())
                            <div class="alert alert-light border mb-0">Belum ada data keterlambatan bulan ini.</div>
                        @else
                            <div class="table-responsive">
                                <table class="table table-sm table-bordered">
                                    <thead class="table-light">
                                        <tr>
                                            <th>No</th>
                                            <th>Siswa</th>
                                            <th>Total Terlambat</th>
                                            <th>Total Menit</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($akumulasiTerlambatBulanan as $index => $row)
                                            <tr>
                                                <td>{{ $index + 1 }}</td>
                                                <td>{{ $row->nama_siswa }} <small class="text-muted d-block">NIS: {{ $row->nis_siswa ?: '-' }}</small></td>
                                                <td><span class="badge" style="background:#f59e0b;color:#fff;">{{ $row->total_terlambat }}x</span></td>
                                                <td><span class="badge bg-danger">{{ $row->total_menit_terlambat ?? 0 }} menit</span></td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @endif
                    </div>

                    @if(isset($siswa) && $siswa->isEmpty())
                        <div class="alert alert-info">
                            <i class="ti ti-info-circle me-2"></i>Tidak ada siswa di kelas ini.
                        </div>
                    @else
                        <div class="row">
                            @foreach($siswa as $st)
                                @php
                                    $statusRow = $dailyStatusMap->has($st->id) ? $dailyStatusMap->get($st->id) : null;
                                    $status = $statusRow->status ?? null;
                                @endphp
                                <div class="student-card mb-3">
                                    <div class="card h-100 shadow-sm">
                                        <div class="card-body d-flex flex-column">
                                            <div class="text-center mb-2">
                                                @if(!empty($st->foto) && \Storage::disk('public')->exists($st->foto))
                                                    <img src="{{ asset('storage/' . $st->foto) }}" alt="Foto {{ $st->nama }}" class="student-photo img-fluid rounded mx-auto d-block">
                                                @else
                                                    <div class="student-photo-placeholder bg-light d-flex align-items-center justify-content-center mx-auto">
                                                        <i class="ti ti-user" style="font-size:32px; color:#ccd6df"></i>
                                                    </div>
                                                @endif
                                            </div>
                                            <h6 class="text-center mb-2">{{ $st->nama }}</h6>
                                            <div class="d-flex flex-wrap gap-2 justify-content-center mb-2">
                                                <div class="form-check form-check-inline">
                                                    <input class="form-check-input" type="checkbox" disabled {{ strtolower($status) === 'hadir' ? 'checked' : '' }}>
                                                    <label class="form-check-label">Hadir</label>
                                                </div>
                                                <div class="form-check form-check-inline">
                                                    <input class="form-check-input" type="checkbox" disabled {{ in_array(strtolower($status ?? ''), ['alpa','alpha','alfa','absen','tidak_hadir']) ? 'checked' : '' }}>
                                                    <label class="form-check-label">Alpa</label>
                                                </div>
                                                <div class="form-check form-check-inline">
                                                    <input class="form-check-input" type="checkbox" disabled {{ in_array(strtolower($status ?? ''), ['izin','ijin']) ? 'checked' : '' }}>
                                                    <label class="form-check-label">Izin</label>
                                                </div>
                                                <div class="form-check form-check-inline">
                                                    <input class="form-check-input" type="checkbox" disabled {{ strtolower($status) === 'sakit' ? 'checked' : '' }}>
                                                    <label class="form-check-label">Sakit</label>
                                                </div>
                                            </div>
                                            <div class="mt-auto text-center">
                                                @php
                                                    $badgeClass = 'btn-secondary';
                                                    if($status) {
                                                        $s = strtolower($status);
                                                        if(in_array($s, ['alpa','alpha','alfa','absen','tidak_hadir'])) $badgeClass = 'btn-danger';
                                                        elseif(in_array($s, ['terlambat','telat'])) $badgeClass = 'btn-warning';
                                                        elseif($s === 'hadir') $badgeClass = 'btn-success';
                                                        elseif(in_array($s, ['izin','ijin'])) $badgeClass = 'btn-info';
                                                        elseif($s === 'sakit') $badgeClass = 'btn-warning';
                                                    }
                                                @endphp
                                                <button class="btn {{ $badgeClass }} btn-sm">{{ $status ? ucfirst($status) : 'Belum ada' }}</button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
<style>
.wali-kelas-absensi .badge { color: #fff !important; }
</style>

<style>
/* Student photo 3:4 aspect ratio, modest size */
.wali-kelas-absensi .student-photo {
    width: 150px;
    height: 200px;
    object-fit: cover;
}
.wali-kelas-absensi .student-photo-placeholder {
    width: 150px;
    height: 200px;
    border-radius: 6px;
}
@media (max-width: 576px) {
    .wali-kelas-absensi .student-photo,
    .wali-kelas-absensi .student-photo-placeholder {
        width: 120px;
        height: 160px;
    }
}
</style>

<style>
/* Grid: responsive number of columns for student cards
   - xs: 2 columns
   - sm (>=576px): 3 columns
   - md (>=768px): 4 columns
   - lg (>=992px): 5 columns
*/
.wali-kelas-absensi .row { display: flex; flex-wrap: wrap; margin-left: -8px; margin-right: -8px; }
.wali-kelas-absensi .student-card { flex: 0 0 50%; max-width: 50%; padding: 0 8px; box-sizing: border-box; }
.wali-kelas-absensi .student-card .card { height: 100%; }

@media (min-width: 576px) {
    .wali-kelas-absensi .student-card { flex: 0 0 33.3333%; max-width: 33.3333%; }
}
@media (min-width: 768px) {
    .wali-kelas-absensi .student-card { flex: 0 0 25%; max-width: 25%; }
}
@media (min-width: 992px) {
    .wali-kelas-absensi .student-card { flex: 0 0 20%; max-width: 20%; }
}
</style>
@endsection
