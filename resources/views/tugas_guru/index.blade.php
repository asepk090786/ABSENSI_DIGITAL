@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h3 class="card-title">Tugas Guru - Penugasan Mata Pelajaran</h3>
                    @if(auth()->user()->hasRole('Admin'))
                        <a href="{{ route('tugas_guru.create') }}" class="btn btn-primary">
                            <i class="ti ti-plus"></i> Tambah Tugas
                        </a>
                    @endif
                </div>
                <div class="card-body">
                    @if(session('success'))
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            {{ session('success') }}
                            <button type="button" class="close" data-bs-dismiss="alert" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                    @endif

                    @if(session('error'))
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            {{ session('error') }}
                            <button type="button" class="close" data-bs-dismiss="alert" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                    @endif

                    
                    <ul class="nav nav-tabs mb-4" id="tugasTab" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active" id="semua-tab" data-bs-toggle="tab" data-bs-target="#semua" type="button" role="tab">
                                <i class="ti ti-list me-2"></i>Semua Tugas
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="bebankerja-tab" data-bs-toggle="tab" data-bs-target="#bebankerja" type="button" role="tab">
                                <i class="ti ti-chart-bar me-2"></i>Beban Kerja
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="perguru-tab" data-bs-toggle="tab" data-bs-target="#perguru" type="button" role="tab">
                                <i class="ti ti-user me-2"></i>Per Guru
                            </button>
                        </li>
                    </ul>

                    
                    <div class="tab-content" id="tugasTabContent">
                        
                        <div class="tab-pane fade show active" id="semua" role="tabpanel">
                            @if($items->isEmpty())
                                <div class="alert alert-info">
                                    <i class="ti ti-info-circle"></i> Belum ada data tugas guru.
                                </div>
                            @else
                                @php
                                    $tingkatList = ['X', 'XI', 'XII'];
                                @endphp
                                
                                <div class="accordion" id="tugasAccordion">
                                    @foreach($tingkatList as $indexTingkat => $tingkat)
                                        @if($itemsByTingkat->has($tingkat))
                                        <div class="accordion-item">
                                            <h2 class="accordion-header">
                                                <button class="accordion-button {{ $indexTingkat === 0 ? '' : 'collapsed' }}" type="button" data-bs-toggle="collapse" data-bs-target="#collapse{{ $tingkat }}" aria-expanded="{{ $indexTingkat === 0 ? 'true' : 'false' }}">
                                                    <i class="ti ti-school me-2"></i>
                                                    <strong>Tingkat {{ $tingkat }}</strong>
                                                    <span class="badge bg-primary text-white ms-3">{{ $itemsByTingkat[$tingkat]->count() }} Tugas</span>
                                                </button>
                                            </h2>
                                            <div id="collapse{{ $tingkat }}" class="accordion-collapse collapse {{ $indexTingkat === 0 ? 'show' : '' }}" data-parent="#tugasAccordion">
                                                <div class="accordion-body p-0">
                                                    <div class="table-responsive">
                                                        <table class="table table-vcenter table-hover table-tabler mb-0">
                                                            <thead class="table-light">
                                                                <tr>
                                                                    <th width="5%" class="ps-3">No</th>
                                                                    <th width="20%">Nama Guru</th>
                                                                    <th width="30%">Mata Pelajaran</th>
                                                                    <th width="25%">Kelas</th>
                                                                    <th width="10%" class="text-center">Status</th>
                                                                    <th width="10%" class="text-center">Aksi</th>
                                                                </tr>
                                                            </thead>
                                                            <tbody>
                                                                @php
                                                                    $tugasByGuru = $itemsByTingkat[$tingkat]->groupBy('guru_id');
                                                                    $guruIndex = 0;
                                                                @endphp
                                                                @foreach($tugasByGuru as $guruId => $tugas)
                                                                    @php
                                                                        $guruIndex++;
                                                                        $guru = $tugas->first()->guru;
                                                                        $activeCount = $tugas->where('is_active', true)->count();
                                                                        $totalCount = $tugas->count();
                                                                    @endphp
                                                                    <tr>
                                                                        <td class="text-center ps-3">{{ $guruIndex }}</td>
                                                                        <td>
                                                                            <strong>{{ optional($guru->user)->name ?? optional($guru)->nama ?? 'Guru tidak tersedia' }}</strong>
                                                                            <br>
                                                                            <small class="text-muted">{{ optional($guru)->nip ?? '-' }}</small>
                                                                        </td>
                                                                        @php
                                                                            $uniqueMapels = $tugas->pluck('mataPelajaran')->unique('id');
                                                                            $kelasNames = $tugas->pluck('kelas')->filter()->pluck('nama_kelas')->unique();
                                                                            $hasAllKelas = $tugas->contains(function($it) { return $it->kelas_id === null; });
                                                                        @endphp

                                                                        <td>
                                                                            <ul class="list-unstyled mb-0">
                                                                                @foreach($uniqueMapels as $mapel)
                                                                                    <li><small><strong>{{ $mapel->nama_mapel ?? '-' }}</strong></small></li>
                                                                                @endforeach
                                                                            </ul>
                                                                        </td>

                                                                        <td>
                                                                            @if($hasAllKelas)
                                                                                <span class="badge bg-warning text-dark">Semua Kelas</span>
                                                                            @endif
                                                                            @foreach($kelasNames as $namaKelas)
                                                                                <span class="badge bg-primary text-white ms-1">{{ $namaKelas }}</span>
                                                                            @endforeach
                                                                        </td>
                                                                        <td class="text-center">
                                                                            @if($activeCount === $totalCount)
                                                                                <span class="badge bg-success text-white">Semua Aktif</span>
                                                                            @elseif($activeCount === 0)
                                                                                <span class="badge bg-danger text-white">Semua Nonaktif</span>
                                                                            @else
                                                                                <span class="badge bg-warning text-white">{{ $activeCount }}/{{ $totalCount }} Aktif</span>
                                                                            @endif
                                                                        </td>
                                                                        <td class="text-center">
                                                                            <div class="btn-group" role="group">
                                                                                <a href="{{ route('tugas_guru.show_by_guru', $guru->id) }}" class="btn btn-sm btn-icon btn-ghost-primary" title="Lihat Detail">
                                                                                    <i class="ti ti-eye"></i>
                                                                                </a>
                                                                            </div>
                                                                        </td>
                                                                    </tr>
                                                                @endforeach
                                                            </tbody>
                                                        </table>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        @endif
                                    @endforeach
                                </div>
                            @endif
                        </div>

                        
                        <div class="tab-pane fade" id="bebankerja" role="tabpanel">
                            @if($guruBebanKerja->isEmpty())
                                <div class="alert alert-info">
                                    <i class="ti ti-info-circle"></i> Belum ada guru yang memiliki tugas mengajar.
                                </div>
                            @else
                                <div class="d-flex justify-content-end mb-2 gap-2">
                                    <a href="{{ route('tugas_guru.beban_kerja.export_excel') }}" class="btn btn-sm btn-success">
                                        <i class="ti ti-file-export"></i> Excel
                                    </a>
                                    <a href="{{ route('tugas_guru.beban_kerja.export_pdf') }}" class="btn btn-sm btn-danger" target="_blank">
                                        <i class="ti ti-file-text"></i> PDF
                                    </a>
                                    <a href="{{ route('tugas_guru.beban_kerja.print') }}" class="btn btn-sm btn-primary" target="_blank">
                                        <i class="ti ti-printer"></i> Print
                                    </a>
                                </div>
                                <div class="table-responsive">
                                    <table class="table table-bordered table-hover table-sm">
                                        <thead class="table-light">
                                            <tr>
                                                <th class="text-center" style="width: 50px;">NO</th>
                                                <th style="width: 250px;">NAMA GURU</th>
                                                <th style="width: 120px;">GOL/RUANG</th>
                                                <th style="width: 200px;">MATA PELAJARAN</th>
                                                @foreach($kelasList as $kelas)
                                                    <th class="text-center" style="width: 40px;">{{ $kelas->nama_kelas }}</th>
                                                @endforeach
                                                <th class="text-center" style="width: 80px;">JUMLAH JAM KBM</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($guruBebanKerja as $index => $guru)
                                            @php
                                                $guruMapels = $guru->tugasGuru->groupBy('mata_pelajaran_id');
                                            @endphp
                                            @foreach($guruMapels as $mapelId => $tugasPerMapel)
                                                <tr>
                                                    @if($loop->first)
                                                        <td class="text-center fw-bold" rowspan="{{ $guruMapels->count() }}">{{ $index + 1 }}</td>
                                                        <td rowspan="{{ $guruMapels->count() }}">
                                                            <strong>{{ $guru->nama }}</strong>
                                                            <br>
                                                            <small class="text-muted">{{ $guru->nip ?? '-' }}</small>
                                                        </td>
                                                        <td rowspan="{{ $guruMapels->count() }}">
                                                            {{ $guru->golongan ?? '-' }}<br>
                                                            <small class="text-muted">{{ $guru->ruang ?? '-' }}</small>
                                                        </td>
                                                    @endif
                                                    @php
                                                        $mapel = $tugasPerMapel->first()->mataPelajaran;
                                                    @endphp
                                                    <td>{{ $mapel->nama_mapel ?? '-' }}</td>
                                                    @foreach($kelasList as $kelas)
                                                        @php
                                                            $jumlahJam = 0;
                                                            $tugasKey = $guru->id . '_' . $mapel->id . '_';
                                                            
                                                            // Cek task spesifik untuk kelas ini
                                                            $hasSpesificTask = $tugasPerMapel->contains(function($task) use ($kelas) {
                                                                return $task->kelas_id === $kelas->id;
                                                            });
                                                            
                                                            if ($hasSpesificTask) {
                                                                $key = $guru->id . '_' . $mapel->id . '_' . $kelas->id;
                                                                $jumlahJam = $jadwalKbmJumlah[$key] ?? 0;
                                                            } else {
                                                                // Cek task umum (semua kelas)
                                                                $hasGeneralTask = $tugasPerMapel->contains(function($task) {
                                                                    return $task->kelas_id === null;
                                                                });
                                                                if ($hasGeneralTask) {
                                                                    $key = $guru->id . '_' . $mapel->id . '_' . $kelas->id;
                                                                    $jumlahJam = $jadwalKbmJumlah[$key] ?? 0;
                                                                }
                                                            }
                                                        @endphp
                                                        <td class="text-center">
                                                            @if($jumlahJam > 0)
                                                                <span class="badge bg-success text-white">{{ $jumlahJam }}</span>
                                                            @else
                                                                <span class="text-muted">-</span>
                                                            @endif
                                                        </td>
                                                    @endforeach
                                                    @if($loop->first)
                                                        <td class="text-center fw-bold" rowspan="{{ $guruMapels->count() }}">
                                                            <span class="badge bg-primary text-white">{{ $totalJamPerGuru[$guru->id] ?? 0 }}</span>
                                                        </td>
                                                    @endif
                                                </tr>
                                            @endforeach
                                            @endforeach
                                            <tr class="table-warning fw-bold">
                                                <td colspan="4" class="text-end">JUMLAH JAM KESELURUHAN:</td>
                                                @foreach($kelasList as $kelas)
                                                    <td class="text-center">
                                                        <span class="badge bg-warning text-dark">{{ $totalJamPerKelas[$kelas->id] ?? 0 }}</span>
                                                    </td>
                                                @endforeach
                                                <td class="text-center">
                                                    <span class="badge bg-dark text-white">
                                                        {{ array_sum($totalJamPerKelas) }}
                                                    </span>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            @endif
                        </div>

                        
                            @if($guruList->isEmpty())
                                <div class="alert alert-info">
                                    <i class="ti ti-info-circle"></i> Belum ada guru yang memiliki tugas mengajar.
                                </div>
                            @else
                                <div class="row">
                                    @foreach($guruList as $guru)
                                    <div class="col-md-6 col-lg-4 mb-2">
                                        <div class="card border">
                                            <div class="card-body">
                                                 <h5 class="card-title">{{ $guru->nama }}</h5>
                                                <p class="text-muted mb-2">
                                                    <small>
                                                        <i class="ti ti-id me-1"></i>
                                                        NIP: {{ $guru->nip ?? '-' }}
                                                    </small>
                                                </p>
                                                <p class="text-muted mb-2">
                                                    <small>
                                                        <i class="ti ti-clipboard-check me-1"></i>
                                                        {{ $guru->tugas_guru_count }} Tugas Aktif
                                                    </small>
                                                </p>
                                                <a href="{{ route('tugas_guru.show_by_guru', $guru->id) }}" class="btn btn-sm btn-info btn-modern">
                                                    <i class="ti ti-eye me-1"></i>Lihat Tugas
                                                </a>
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
    </div>
</div>
@endsection
