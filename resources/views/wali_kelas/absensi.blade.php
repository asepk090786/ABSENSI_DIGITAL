@extends('layouts.app', ['pageSlug' => 'wali-kelas'])

@section('title', 'Absensi Kelas - Wali Kelas')

@section('content')
<div class="container-fluid">
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
                    <a href="{{ route('wali_kelas.index') }}" class="btn btn-secondary">
                        <i class="ti ti-arrow-left me-1"></i>Kembali
                    </a>
                </div>
                <div class="card-body">
                    <div class="mb-4">
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

                    @if($absensi->isEmpty())
                        <div class="alert alert-info">
                            <i class="ti ti-info-circle me-2"></i>Belum ada data absensi untuk kelas ini.
                        </div>
                    @else
                        <div class="table-responsive">
                            <table class="table table-vcenter table-hover table-tabler">
                                <thead>
                                    <tr>
                                        <th width="5%">No</th>
                                        <th>Tanggal</th>
                                        <th>Jam KBM</th>
                                        <th>Guru</th>
                                        <th>Hadir</th>
                                        <th>Sakit</th>
                                        <th>Izin</th>
                                        <th>Alpa</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($absensi as $index => $a)
                                    <tr>
                                        <td>{{ $index + 1 }}</td>
                                        <td>{{ $a->tanggal ? \Carbon\Carbon::parse($a->tanggal)->format('d/m/Y') : '-' }}</td>
                                        <td>{{ $a->jam_mulai ?? '-' }} - {{ $a->jam_selesai ?? '-' }}</td>
                                        <td>{{ $a->guru_nama ?? '-' }}</td>
                                        <td><span class="badge bg-success">{{ $rekapCounts[$a->id]->hadir ?? 0 }}</span></td>
                                        <td><span class="badge bg-warning">{{ $rekapCounts[$a->id]->sakit ?? 0 }}</span></td>
                                        <td><span class="badge bg-info">{{ $rekapCounts[$a->id]->izin ?? 0 }}</span></td>
                                        <td><span class="badge bg-danger">{{ $rekapCounts[$a->id]->alpha ?? 0 }}</span></td>
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
@endsection
