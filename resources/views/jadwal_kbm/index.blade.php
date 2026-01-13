@extends('layouts.app', ['pageSlug' => 'jadwal-kbm'])

@section('title','Jadwal KBM')

@section('content')
<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header">
                <div class="row align-items-center">
                    <div class="col">
                        <h4 class="card-title mb-0">Jadwal Kegiatan Belajar Mengajar (KBM)</h4>
                    </div>
                </div>
            </div>
            <div class="card-body">
                @if(session('success'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <i class="ti ti-check me-2"></i>{{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif

                @if(session('error'))
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <i class="ti ti-alert-circle me-2"></i>{{ session('error') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif

                <!-- Tab Navigation -->
                <ul class="nav nav-tabs mb-4" id="jadwalTab" role="tablist">
                    <li class="nav-item" role="presentation">
                        <a class="nav-link" href="{{ route('kurikulum.index') }}">
                            <i class="ti ti-books me-2"></i>Struktur Kurikulum
                        </a>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active" id="perkelas-tab" data-bs-toggle="tab" data-bs-target="#perkelas" type="button" role="tab">
                            <i class="ti ti-school me-2"></i>Jadwal Per Kelas
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="perguru-tab" data-bs-toggle="tab" data-bs-target="#perguru" type="button" role="tab">
                            <i class="ti ti-user me-2"></i>Jadwal Per Guru
                        </button>
                    </li>
                </ul>

                <!-- Tab Content -->
                <div class="tab-content" id="jadwalTabContent">
                    <!-- Tab Jadwal Per Kelas -->
                    <div class="tab-pane fade show active" id="perkelas" role="tabpanel">
                        <div class="row">
                            @foreach($kelasList as $kelas)
                            <div class="col-md-6 col-lg-4 mb-3">
                                <div class="card border">
                                    <div class="card-body">
                                        <h5 class="card-title">{{ $kelas->nama_kelas }}</h5>
                                        <p class="text-muted mb-2">
                                            <small>
                                                <i class="ti ti-user me-1"></i>
                                                Wali Kelas: {{ $kelas->waliKelas->nama ?? '-' }}
                                            </small>
                                        </p>
                                        <p class="text-muted mb-3">
                                            <small>
                                                <i class="ti ti-layer me-1"></i>
                                                Tingkat: {{ $kelas->tingkat_kelas ?? '-' }}
                                                @if($kelas->jurusan)
                                                    <span class="ms-2 badge bg-primary-subtle text-primary">{{ $kelas->jurusan }}</span>
                                                @endif
                                            </small>
                                        </p>
                                        <div class="btn-list">
                                            <a href="{{ route('jadwal-kbm.create-by-kelas', $kelas->id) }}" class="btn btn-primary btn-sm">
                                                <i class="ti ti-calendar-event me-1"></i>Atur Jadwal
                                            </a>
                                            <button type="button" class="btn btn-info btn-sm" onclick="viewJadwal({{ $kelas->id }}, '{{ $kelas->nama_kelas }}')">
                                                <i class="ti ti-eye me-1"></i>Lihat
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    </div>

                    <!-- Tab Jadwal Per Guru -->
                    <div class="tab-pane fade" id="perguru" role="tabpanel">
                        <div class="row">
                            @foreach($guruList as $guru)
                            <div class="col-md-6 col-lg-4 mb-3">
                                <div class="card border">
                                    <div class="card-body">
                                        <h5 class="card-title">{{ $guru->nama }}</h5>
                                        <p class="text-muted mb-2">
                                            <small>
                                                <i class="ti ti-id me-1"></i>
                                                NIP: {{ $guru->nip ?? '-' }}
                                            </small>
                                        </p>
                                        <a href="{{ route('jadwal-kbm.show-by-guru', $guru->id) }}" class="btn btn-info btn-sm">
                                            <i class="ti ti-calendar me-1"></i>Lihat Jadwal
                                        </a>
                                    </div>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal View Jadwal -->
<div class="modal fade" id="modalViewJadwal" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Jadwal KBM - <span id="namaKelas"></span></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div id="jadwalContent" class="table-responsive">
                    <!-- Jadwal will be loaded here -->
                </div>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
function viewJadwal(kelasId, namaKelas) {
    $('#namaKelas').text(namaKelas);
    $('#jadwalContent').html('<div class="text-center"><div class="spinner-border" role="status"></div></div>');
    
    $.ajax({
        url: `/jadwal-kbm/get-jadwal-by-kelas/${kelasId}`,
        method: 'GET',
        success: function(data) {
            if (data.length === 0) {
                $('#jadwalContent').html('<div class="alert alert-info">Belum ada jadwal untuk kelas ini.</div>');
            } else {
                let html = '<table class="table table-bordered table-hover">';
                html += '<thead><tr><th>Hari</th><th>Jam Ke</th><th>Waktu</th><th>Mata Pelajaran</th><th>Guru</th></tr></thead>';
                html += '<tbody>';
                
                data.forEach(jadwal => {
                    html += '<tr>';
                    html += `<td>${jadwal.hari}</td>`;
                    html += `<td>${jadwal.jam_ke}</td>`;
                    html += `<td>${jadwal.jam_belajar.jam_mulai} - ${jadwal.jam_belajar.jam_selesai}</td>`;
                    html += `<td>${jadwal.mata_pelajaran.nama_mapel}</td>`;
                    html += `<td>${jadwal.guru.nama}</td>`;
                    html += '</tr>';
                });
                
                html += '</tbody></table>';
                $('#jadwalContent').html(html);
            }
        },
        error: function() {
            $('#jadwalContent').html('<div class="alert alert-danger">Gagal memuat jadwal.</div>');
        }
    });
    
    $('#modalViewJadwal').modal('show');
}
</script>
@endpush
