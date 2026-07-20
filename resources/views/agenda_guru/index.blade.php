@extends('layouts.app', ['pageSlug' => 'agenda_guru'])

@section('title','Agenda Mengajar Guru')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <div>
                        <h4 class="card-title fw-semibold m-0">
                            <i class="ti ti-book-2 me-2"></i>Agenda Mengajar Guru (Jurnal Harian)
                        </h4>
                    </div>
                    <div class="btn-group" role="group">
                        <a href="{{ route('agenda_guru.create') }}" class="btn btn-primary btn-sm">
                            <i class="ti ti-plus"></i> Tambah Agenda Guru
                        </a>
                        <button type="button" class="btn btn-sm btn-success btn-modern" data-bs-toggle="modal" data-bs-target="#filterModal">
                            <i class="ti ti-calendar"></i> Filter Bulan
                        </button>
                        <a href="{{ route('agenda_guru.export', ['bulan' => $bulan, 'tahun' => $tahunFilter]) }}" class="btn btn-sm btn-info btn-modern">
                            <i class="ti ti-file-pdf"></i> Export PDF
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    @if(session('success'))<div class="alert alert-success alert-dismissible fade show"><i class="ti ti-check me-2"></i>{{ session('success') }}<button type="button" class="close" data-bs-dismiss="alert"><span aria-hidden="true">&times;</span></button></div>@endif
                    @if(session('error'))<div class="alert alert-danger alert-dismissible fade show"><i class="ti ti-alert-circle me-2"></i>{{ session('error') }}<button type="button" class="close" data-bs-dismiss="alert"><span aria-hidden="true">&times;</span></button></div>@endif

                    
                    <div class="mb-2">
                        <h5 class="text-muted">
                            <span class="badge bg-primary">{{ $monthName[$bulan] }} {{ $tahunFilter }}</span>
                        </h5>
                    </div>

                    
                    @if($agendaList->isEmpty())
                        <div class="alert alert-info">
                            <i class="ti ti-info-circle"></i> Belum ada agenda untuk bulan {{ $monthName[$bulan] }} {{ $tahunFilter }}.
                        </div>
                    @else
                        <div class="table-responsive">
                            <table class="table table-bordered table-striped table-hover">
                                <thead class="table-light">
                                    <tr>
                                        <th style="width: 5%;">NO</th>
                                        <th style="width: 12%;">HARI / TANGGAL</th>
                                        <th style="width: 12%;">JAM PELAJARAN</th>
                                        <th style="width: 35%;">KEGIATAN / MATERI AJAR</th>
                                        <th style="width: 20%;">KETERANGAN</th>
                                        <th style="width: 16%;">AKSI</th>
                                    </tr>
                                </thead>
                                <tbody>
                                @php
                                    $no = 1;
                                    $lastDate = null;
                                @endphp
                                @foreach($agendaList as $item)
                                    @php
                                        $currentDate = $item->tanggal->format('Y-m-d');
                                        $dayName = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
                                        $day = $dayName[$item->tanggal->dayOfWeek];
                                        $dateStr = $day . ', ' . $item->tanggal->format('d/m/Y');
                                    @endphp
                                    <tr>
                                        <td class="text-center"><strong>{{ $no++ }}</strong></td>
                                        <td><strong>{{ $dateStr }}</strong></td>
                                        <td class="text-center">
                                            @if($item->jamBelajar)
                                                @if(!empty($item->jamBelajar->urutan))
                                                    <div class="fw-semibold">Jam Ke-{{ $item->jamBelajar->urutan }}</div>
                                                @endif
                                                <div>{{ $item->jamBelajar->jam_mulai }} <small class="text-muted">s/d {{ $item->jamBelajar->jam_selesai }}</small></div>
                                            @else
                                                -
                                            @endif
                                        </td>
                                        <td>
                                            <strong>{{ Str::limit(strip_tags($item->kegiatan), 100) }}</strong>
                                            @php
                                                $absensiSummary = $item->getAbsensiSummary();
                                                if ($absensiSummary['total'] > 0) {
                                                    echo '<br><small class="badge bg-success">Hadir: ' . $absensiSummary['hadir'] . '/' . $absensiSummary['total'] . '</small>';
                                                }
                                            @endphp
                                        </td>
                                        <td class="text-muted text-sm">
                                            <small>Dibuat: {{ $item->created_at->format('d/m/Y H:i') }}</small>
                                        </td>
                                        <td>
                                            <div class="btn-group btn-group-sm" role="group">
                                                <a href="{{ route('agenda_guru.edit', $item->id) }}" class="btn btn-warning" title="Edit">
                                                    <i class="ti ti-edit"></i>
                                                </a>
                                                <form action="{{ route('agenda_guru.destroy', $item->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Yakin ingin menghapus agenda ini?')">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-danger" title="Hapus">
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

                        
                        <div class="row mt-4">
                            <div class="col-md-4">
                                <div class="card border-primary">
                                    <div class="card-body">
                                        <h6 class="card-title text-muted">Total Agenda</h6>
                                        <h3 class="text-primary">{{ $agendaList->count() }}</h3>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="card border-info">
                                    <div class="card-body">
                                        <h6 class="card-title text-muted">Hari Mengajar</h6>
                                        <h3 class="text-info">{{ $agendaByDate->count() }}</h3>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="card border-success">
                                    <div class="card-body">
                                        <h6 class="card-title text-muted">Mata Pelajaran</h6>
                                        <h3 class="text-success">{{ $mataPelajaran ? Str::limit($mataPelajaran->nama_mapel, 20) : '-' }}</h3>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>


<div class="modal fade" id="filterModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Pilih Bulan dan Tahun</h5>
                <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
            </div>
            <form action="{{ route('agenda_guru.index') }}" method="GET">
                <div class="modal-body">
                    <div class="mb-2">
                        <label class="form-label">Bulan</label>
                        <select class="form-select" name="bulan" required>
                            @php
                                $bulanName = ['', 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
                            @endphp
                            @for($m = 1; $m <= 12; $m++)
                                <option value="{{ $m }}" {{ $bulan == $m ? 'selected' : '' }}>
                                    {{ $bulanName[$m] }}
                                </option>
                            @endfor
                        </select>
                    </div>
                    <div class="mb-2">
                        <label class="form-label">Tahun</label>
                        <select class="form-select" name="tahun" required>
                            @for($y = now()->year - 2; $y <= now()->year + 1; $y++)
                                <option value="{{ $y }}" {{ $tahunFilter == $y ? 'selected' : '' }}>
                                    {{ $y }}
                                </option>
                            @endfor
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Tampilkan</button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection
