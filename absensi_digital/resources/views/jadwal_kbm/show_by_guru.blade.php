@extends('layouts.app', ['pageSlug' => 'jadwal-kbm'])

@section('title','Jadwal Mengajar Guru')

@section('content')
<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header">
                <div class="row align-items-center">
                    <div class="col">
                        <h4 class="card-title mb-0">Jadwal Mengajar - {{ $guru->nama }}</h4>
                        <p class="text-muted mb-0 mt-1">
                            <small>
                                <i class="ti ti-id me-1"></i>NIP: {{ $guru->nip ?? '-' }}
                                @if($tahunAjaranAktif && $semesterAktif)
                                | <i class="ti ti-calendar ms-2 me-1"></i>{{ $tahunAjaranAktif->nama }} - {{ $semesterAktif->nama }}
                                @endif
                            </small>
                        </p>
                    </div>
                    <div class="col-auto">
                        <a href="{{ route('jadwal-kbm.index') }}" class="btn btn-secondary btn-sm">
                            <i class="ti ti-arrow-left me-1"></i>Kembali
                        </a>
                        <button onclick="window.print()" class="btn btn-info btn-sm">
                            <i class="ti ti-printer me-1"></i>Cetak
                        </button>
                    </div>
                </div>
            </div>
            <div class="card-body">
                @if($jadwalGuru->isEmpty())
                    <div class="alert alert-info">
                        <i class="ti ti-info-circle me-2"></i>
                        Belum ada jadwal mengajar untuk guru ini.
                    </div>
                @else
                    @php
                        $hariList = ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
                    @endphp

                    @foreach($hariList as $hari)
                        @php
                            $jadwalHari = $jadwalGuru->get($hari, collect());
                        @endphp
                        
                        @if($jadwalHari->isNotEmpty())
                        <div class="mb-4">
                            <h5 class="border-bottom pb-2 mb-3">
                                <i class="ti ti-calendar me-2"></i>{{ $hari }}
                            </h5>
                            <div class="table-responsive">
                                <table class="table table-bordered table-hover">
                                    <thead class="table-light">
                                        <tr>
                                            <th width="10%" class="text-center">Jam Ke</th>
                                            <th width="20%">Waktu</th>
                                            <th width="30%">Mata Pelajaran</th>
                                            <th width="40%">Kelas</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($jadwalHari->sortBy('jam_ke') as $jadwal)
                                        <tr>
                                            <td class="text-center">{{ $jadwal->jam_ke }}</td>
                                            <td>
                                                <i class="ti ti-clock me-1"></i>
                                                {{ $jadwal->jamBelajar->jam_mulai }} - {{ $jadwal->jamBelajar->jam_selesai }}
                                            </td>
                                            <td>
                                                <strong>{{ $jadwal->mataPelajaran->nama_mapel }}</strong>
                                                <br>
                                                <small class="text-muted">
                                                    <i class="ti ti-book me-1"></i>{{ $jadwal->mataPelajaran->kode_mapel }}
                                                </small>
                                            </td>
                                            <td>
                                                <span class="badge bg-primary">{{ $jadwal->kelas->nama_kelas }}</span>
                                                @if($jadwal->kelas->tingkat_kelas)
                                                <small class="text-muted ms-2">Tingkat: {{ $jadwal->kelas->tingkat_kelas }}</small>
                                                @endif
                                                @if($jadwal->kelas->jurusan)
                                                <small class="text-muted ms-2">Jurusan: {{ $jadwal->kelas->jurusan }}</small>
                                                @endif
                                            </td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        @endif
                    @endforeach

                    <!-- Summary -->
                    <div class="alert alert-light mt-4">
                        <div class="row">
                            <div class="col-md-4">
                                <strong>Total Jam Mengajar:</strong> 
                                {{ $jadwalGuru->flatten()->count() }} jam/minggu
                            </div>
                            <div class="col-md-4">
                                <strong>Jumlah Kelas:</strong> 
                                {{ $jadwalGuru->flatten()->pluck('kelas_id')->unique()->count() }} kelas
                            </div>
                            <div class="col-md-4">
                                <strong>Jumlah Mata Pelajaran:</strong> 
                                {{ $jadwalGuru->flatten()->pluck('mata_pelajaran_id')->unique()->count() }} mapel
                            </div>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

@if(!$jadwalGuru->isEmpty())
<!-- Tabel Ringkasan Mingguan -->
<div class="row mt-4">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">Ringkasan Jadwal Mingguan</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered text-center">
                        <thead class="table-primary">
                            <tr>
                                <th>Jam</th>
                                @foreach($hariList as $hari)
                                <th>{{ $hari }}</th>
                                @endforeach
                            </tr>
                        </thead>
                        <tbody>
                            @php
                                $maxJam = $jadwalGuru->flatten()->max('jam_ke') ?? 10;
                            @endphp
                            @for($jam = 1; $jam <= $maxJam; $jam++)
                            <tr>
                                <td><strong>{{ $jam }}</strong></td>
                                @foreach($hariList as $hari)
                                    @php
                                        $jadwalHari = $jadwalGuru->get($hari, collect());
                                        $jadwalJam = $jadwalHari->firstWhere('jam_ke', $jam);
                                    @endphp
                                    <td class="{{ $jadwalJam ? 'bg-light' : '' }}">
                                        @if($jadwalJam)
                                            <div class="small">
                                                <strong>{{ $jadwalJam->mataPelajaran->nama_mapel }}</strong><br>
                                                <span class="text-muted">{{ $jadwalJam->kelas->nama_kelas }}</span>
                                            </div>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                @endforeach
                            </tr>
                            @endfor
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endif

@endsection

@push('styles')
<style>
@media print {
    .card-header .btn,
    .btn-list,
    nav,
    .sidebar {
        display: none !important;
    }
    
    .card {
        border: none;
        box-shadow: none;
    }
    
    body {
        background: white;
    }
}
</style>
@endpush
