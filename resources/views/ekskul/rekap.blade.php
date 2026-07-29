@extends('layouts.app', ['pageSlug' => 'ekskul'])

@section('title', 'Rekap Absensi - ' . $ekskul->nama)

@section('content')
<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h4 class="card-title fw-semibold m-0">Rekap Absensi: {{ $ekskul->nama }}</h4>
                <a href="{{ route('ekskul.index') }}" class="btn btn-secondary btn-sm">Kembali</a>
            </div>
            <div class="card-body">
                <form method="GET" class="row g-2 mb-3">
                    <div class="col-md-4">
                        <label class="form-label">Tanggal Mulai</label>
                        <input type="date" name="tanggal_mulai" class="form-control" value="{{ $tanggalMulai }}">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Tanggal Selesai</label>
                        <input type="date" name="tanggal_selesai" class="form-control" value="{{ $tanggalSelesai }}">
                    </div>
                    <div class="col-md-4 d-flex align-items-end">
                        <button type="submit" class="btn btn-primary w-100"><i class="ti ti-filter me-1"></i>Filter</button>
                    </div>
                </form>

                <div class="table-responsive">
                    <table class="table table-vcenter table-hover">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>NIS</th>
                                <th>Nama Siswa</th>
                                <th>Kelas</th>
                                <th class="text-center">Hadir</th>
                                <th class="text-center">Izin</th>
                                <th class="text-center">Sakit</th>
                                <th class="text-center">Alpha</th>
                                <th class="text-center">Total</th>
                                <th class="text-center">%</th>
                            </tr>
                        </thead>
                        <tbody>
                        @forelse($rekap as $index => $r)
                            <tr>
                                <td>{{ $index + 1 }}</td>
                                <td>{{ $r->siswa->nis ?? '-' }}</td>
                                <td class="fw-semibold">{{ $r->siswa->nama ?? '-' }}</td>
                                <td>{{ $r->siswa->kelas->nama_kelas ?? '-' }}</td>
                                <td class="text-center text-success fw-bold">{{ $r->total_hadir }}</td>
                                <td class="text-center text-warning">{{ $r->total_izin }}</td>
                                <td class="text-center text-info">{{ $r->total_sakit }}</td>
                                <td class="text-center text-danger">{{ $r->total_alpha }}</td>
                                <td class="text-center">{{ $r->total_pertemuan }}</td>
                                <td class="text-center">
                                    @if($r->persentase >= 80)
                                        <span class="badge bg-success">{{ $r->persentase }}%</span>
                                    @elseif($r->persentase >= 60)
                                        <span class="badge bg-warning">{{ $r->persentase }}%</span>
                                    @else
                                        <span class="badge bg-danger">{{ $r->persentase }}%</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="10" class="text-center text-muted py-4">Belum ada data absensi.</td></tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection