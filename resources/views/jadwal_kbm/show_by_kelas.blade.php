@extends('layouts.app', ['pageSlug' => 'jadwal-kbm'])

@section('title','Jadwal Kelas')

@section('content')
<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header border-0 pt-3 pb-2">
                <div class="row align-items-center">
                    <div class="col">
                        <h4 class="card-title fw-semibold m-0">Jadwal Kelas - {{ $kelas->nama_kelas }}</h4>
                        <p class="text-muted mb-0 mt-1">
                            <small>
                                @if($tahunAjaranAktif && $semesterAktif)
                                <i class="ti ti-calendar me-1"></i>{{ $tahunAjaranAktif->nama_tahun }} - {{ $semesterAktif->nama_semester }}
                                @endif
                            </small>
                        </p>
                    </div>
                    <div class="col-auto">
                        <a href="{{ route('home') }}" class="btn btn-secondary btn-sm">
                            <i class="ti ti-arrow-left me-1"></i>Kembali
                        </a>
                    </div>
                </div>
            </div>
            <div class="card-body">
                @if($jadwalKelas->isEmpty())
                    <div class="alert alert-info">
                        <i class="ti ti-info-circle me-2"></i>Belum ada jadwal untuk kelas ini.
                    </div>
                @else
                    @php
                        $hariList = ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
                    @endphp

                    @foreach($hariList as $hari)
                        @php
                            $jadwalHari = $jadwalKelas->get($hari, collect());
                        @endphp

                        @if($jadwalHari->isNotEmpty())
                            <div class="mb-4">
                                <h5 class="border-bottom pb-2 mb-2">
                                    <i class="ti ti-calendar me-2"></i>{{ $hari }}
                                </h5>
                                <div class="table-responsive">
                                    <table class="table table-bordered table-hover">
                                        <thead class="table-light">
                                            <tr>
                                                <th width="10%" class="text-center">Jam Ke</th>
                                                <th width="20%">Waktu</th>
                                                <th width="30%">Mata Pelajaran</th>
                                                <th width="40%">Guru</th>
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
                                                    <small class="text-muted">{{ $jadwal->mataPelajaran->kode_mapel ?? '-' }}</small>
                                                </td>
                                                <td>
                                                    <span class="badge bg-primary text-white">{{ $jadwal->guru->nama }}</span>
                                                </td>
                                            </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        @endif
                    @endforeach
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
