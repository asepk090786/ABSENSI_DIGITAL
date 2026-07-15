@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h3 class="card-title">Tugas Guru - Penugasan Mata Pelajaran</h3>
                    <a href="{{ route('tugas_guru.create') }}" class="btn btn-primary">
                        <i class="ti ti-plus"></i> Tambah Tugas
                    </a>
                </div>
                <div class="card-body">
                    @if(session('success'))
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            {{ session('success') }}
                            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                    @endif

                    @if(session('error'))
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            {{ session('error') }}
                            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                    @endif

                    <!-- Tab Navigation -->
                    <ul class="nav nav-tabs mb-4" id="tugasTab" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active" id="semua-tab" data-toggle="tab" data-target="#semua" type="button" role="tab">
                                <i class="ti ti-list me-2"></i>Semua Tugas
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="perguru-tab" data-toggle="tab" data-target="#perguru" type="button" role="tab">
                                <i class="ti ti-user me-2"></i>Per Guru
                            </button>
                        </li>
                    </ul>

                    <!-- Tab Content -->
                    <div class="tab-content" id="tugasTabContent">
                        <!-- Tab Semua Tugas -->
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
                                                <button class="accordion-button {{ $indexTingkat === 0 ? '' : 'collapsed' }}" type="button" data-toggle="collapse" data-target="#collapse{{ $tingkat }}" aria-expanded="{{ $indexTingkat === 0 ? 'true' : 'false' }}">
                                                    <i class="ti ti-school me-2"></i>
                                                    <strong>Tingkat {{ $tingkat }}</strong>
                                                    <span class="badge bg-primary ms-3">{{ $itemsByTingkat[$tingkat]->count() }} Tugas</span>
                                                </button>
                                            </h2>
                                            <div id="collapse{{ $tingkat }}" class="accordion-collapse collapse {{ $indexTingkat === 0 ? 'show' : '' }}" data-parent="#tugasAccordion">
                                                <div class="accordion-body p-0">
                                                    <div class="table-responsive">
                                                        <table class="table table-striped table-hover mb-0">
                                                            <thead class="table-light">
                                                                <tr>
                                                                    <th width="5%" class="ps-3">No</th>
                                                                    <th width="20%">Nama Guru</th>
                                                                    <th width="25%">Mata Pelajaran</th>
                                                                    <th width="20%">Kelas</th>
                                                                    <th width="10%" class="text-center">Status</th>
                                                                    <th width="20%" class="text-center">Aksi</th>
                                                                </tr>
                                                            </thead>
                                                            <tbody>
                                                                @foreach($itemsByTingkat[$tingkat] as $index => $item)
                                                                    <tr>
                                                                        <td class="text-center ps-3">{{ $index + 1 }}</td>
                                                                        <td>
                                                                            <strong>{{ $item->guru->user->name ?? $item->guru->nama }}</strong>
                                                                            <br>
                                                                            <small class="text-muted">{{ $item->guru->nip ?? '-' }}</small>
                                                                        </td>
                                                                        <td>{{ $item->mataPelajaran->nama_mapel ?? '-' }}</td>
                                                                        <td>
                                                                            @if($item->kelas)
                                                                                <span class="badge bg-blue">{{ $item->kelas->nama_kelas }}</span>
                                                                            @else
                                                                                <span class="badge bg-secondary">Semua Kelas</span>
                                                                            @endif
                                                                        </td>
                                                                        <td class="text-center">
                                                                            @if($item->is_active)
                                                                                <span class="badge bg-success">Aktif</span>
                                                                            @else
                                                                                <span class="badge bg-secondary">Nonaktif</span>
                                                                            @endif
                                                                        </td>
                                                                        <td class="text-center">
                                                                            <div class="btn-group" role="group">
                                                                                <a href="{{ route('tugas_guru.show', $item->id) }}" class="btn btn-sm btn-icon btn-ghost-primary" title="Lihat Detail">
                                                                                    <i class="ti ti-eye"></i>
                                                                                </a>
                                                                                <a href="{{ route('tugas_guru.edit', $item->id) }}" class="btn btn-sm btn-icon btn-ghost-warning" title="Edit">
                                                                                    <i class="ti ti-edit"></i>
                                                                                </a>
                                                                                <form action="{{ route('tugas_guru.destroy', $item->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Yakin ingin menghapus tugas ini?')">
                                                                                    @csrf
                                                                                    @method('DELETE')
                                                                                    <button type="submit" class="btn btn-sm btn-icon btn-ghost-danger" title="Hapus">
                                                                                        <i class="ti ti-trash"></i>
                                                                                    </button>
                                                                                </form>
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

                        <!-- Tab Per Guru -->
                        <div class="tab-pane fade" id="perguru" role="tabpanel">
                            @if($guruList->isEmpty())
                                <div class="alert alert-info">
                                    <i class="ti ti-info-circle"></i> Belum ada guru yang memiliki tugas mengajar.
                                </div>
                            @else
                                <div class="row">
                                    @foreach($guruList as $guru)
                                    <div class="col-md-6 col-lg-4 mb-3">
                                        <div class="card border">
                                            <div class="card-body">
                                                <h5 class="card-title">{{ $guru->user->name ?? $guru->nama }}</h5>
                                                <p class="text-muted mb-2">
                                                    <small>
                                                        <i class="ti ti-id me-1"></i>
                                                        NIP: {{ $guru->nip ?? '-' }}
                                                    </small>
                                                </p>
                                                <p class="text-muted mb-3">
                                                    <small>
                                                        <i class="ti ti-clipboard-check me-1"></i>
                                                        {{ $guru->tugas_guru_count }} Tugas Aktif
                                                    </small>
                                                </p>
                                                <a href="{{ route('tugas_guru.show_by_guru', $guru->id) }}" class="btn btn-info btn-sm">
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
