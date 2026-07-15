@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <h3 class="card-title">Beban Kerja Guru - Cetak</h3>
                    <div class="table-responsive">
                        <table class="table table-bordered table-sm">
                            <thead class="table-light">
                                <tr>
                                    <th>NO</th>
                                    <th>NAMA GURU</th>
                                    <th>GOL/RUANG</th>
                                    <th>MATA PELAJARAN</th>
                                    @foreach($kelasList as $kelas)
                                        <th>{{ $kelas->nama_kelas }}</th>
                                    @endforeach
                                    <th>JUMLAH JAM KBM</th>
                                </tr>
                            </thead>
                            <tbody>
                                @php $no = 0; @endphp
                                @foreach($guruBebanKerja as $guru)
                                    @php $guruMapels = $guru->tugasGuru->groupBy('mata_pelajaran_id'); @endphp
                                    @foreach($guruMapels as $mapelId => $tugasPerMapel)
                                        @php $no++; $mapel = $tugasPerMapel->first()->mataPelajaran; @endphp
                                        <tr>
                                            <td>{{ $no }}</td>
                                            <td>{{ $guru->nama }}</td>
                                            <td>{{ $guru->golongan ?? '-' }} / {{ $guru->ruang ?? '-' }}</td>
                                            <td>{{ $mapel->nama_mapel ?? '-' }}</td>
                                            @php $sumJam = 0; @endphp
                                            @foreach($kelasList as $kelas)
                                                @php
                                                    $jumlahJam = 0;
                                                    $hasSpesificTask = $tugasPerMapel->contains(function($task) use ($kelas) {
                                                        return $task->kelas_id === $kelas->id;
                                                    });
                                                    if ($hasSpesificTask) {
                                                        $key = $guru->id . '_' . $mapel->id . '_' . $kelas->id;
                                                        $jumlahJam = $jadwalKbmJumlah[$key] ?? 0;
                                                    } else {
                                                        $hasGeneralTask = $tugasPerMapel->contains(function($task) { return $task->kelas_id === null; });
                                                        if ($hasGeneralTask) {
                                                            $key = $guru->id . '_' . $mapel->id . '_' . $kelas->id;
                                                            $jumlahJam = $jadwalKbmJumlah[$key] ?? 0;
                                                        }
                                                    }
                                                    $sumJam += $jumlahJam;
                                                @endphp
                                                <td class="text-center">{{ $jumlahJam }}</td>
                                            @endforeach
                                            <td class="text-center">{{ $sumJam }}</td>
                                        </tr>
                                    @endforeach
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <div class="mt-3">
                        <button onclick="window.print()" class="btn btn-primary">Print</button>
                        <a href="{{ route('tugas_guru.index') }}" class="btn btn-secondary">Kembali</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
