@extends('layouts.app', ['pageSlug' => 'agenda'])

@section('title','Agenda Kelas')

@section('content')
<style>
    .hover-shadow {
        transition: all 0.3s ease;
    }
    .hover-shadow:hover {
        transform: translateY(-5px);
        box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15) !important;
    }

    /* Guru Quick Access Styles */
    .guru-btn {
        transition: all 0.3s ease;
        font-weight: 500;
        min-width: 180px;
        text-align: left;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        padding: 0.5rem 1rem;
        display: inline-flex;
        align-items: center;
        justify-content: flex-start;
    }

    .guru-btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 0.25rem 0.5rem rgba(0, 0, 0, 0.1) !important;
    }

    .guru-btn i {
        flex-shrink: 0;
        margin-right: 0.5rem;
    }

    .guru-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
        gap: 0.75rem;
    }

    @media (max-width: 768px) {
        .guru-grid {
            grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
        }
        .guru-btn {
            min-width: 150px;
            font-size: 0.875rem;
        }
    }

    /* Class Level Colors */
    .card-class-10 {
        border-color: #3b82f6 !important;
        background: linear-gradient(135deg, rgba(59, 130, 246, 0.05) 0%, rgba(96, 165, 250, 0.05) 100%);
    }
    .card-class-10 .card-header-icon {
        color: #3b82f6;
    }
    .card-class-10 .btn-class {
        background-color: #3b82f6;
        border-color: #3b82f6;
        color: white;
    }
    .card-class-10 .btn-class:hover {
        background-color: #2563eb;
        border-color: #2563eb;
    }

    .card-class-11 {
        border-color: #10b981 !important;
        background: linear-gradient(135deg, rgba(16, 185, 129, 0.05) 0%, rgba(52, 211, 153, 0.05) 100%);
    }
    .card-class-11 .card-header-icon {
        color: #10b981;
    }
    .card-class-11 .btn-class {
        background-color: #10b981;
        border-color: #10b981;
        color: white;
    }
    .card-class-11 .btn-class:hover {
        background-color: #059669;
        border-color: #059669;
    }

    .card-class-12 {
        border-color: #f59e0b !important;
        background: linear-gradient(135deg, rgba(245, 158, 11, 0.05) 0%, rgba(251, 191, 36, 0.05) 100%);
    }
    .card-class-12 .card-header-icon {
        color: #f59e0b;
    }
    .card-class-12 .btn-class {
        background-color: #f59e0b;
        border-color: #f59e0b;
        color: white;
    }
    .card-class-12 .btn-class:hover {
        background-color: #d97706;
        border-color: #d97706;
    }

    /* Fallback untuk tingkatan lain */
    .card-class-default {
        border-color: #8b5cf6 !important;
        background: linear-gradient(135deg, rgba(139, 92, 246, 0.05) 0%, rgba(167, 139, 250, 0.05) 100%);
    }
    .card-class-default .card-header-icon {
        color: #8b5cf6;
    }
    .card-class-default .btn-class {
        background-color: #8b5cf6;
        border-color: #8b5cf6;
        color: white;
    }
    .card-class-default .btn-class:hover {
        background-color: #7c3aed;
        border-color: #7c3aed;
    }

    .card-class-badge {
        display: inline-block;
        padding: 0.25rem 0.75rem;
        border-radius: 0.5rem;
        font-size: 0.85rem;
        font-weight: 600;
        margin-bottom: 0.5rem;
    }

    .badge-class-10 {
        background-color: rgba(59, 130, 246, 0.1);
        color: #3b82f6;
    }

    .badge-class-11 {
        background-color: rgba(16, 185, 129, 0.1);
        color: #10b981;
    }

    .badge-class-12 {
        background-color: rgba(245, 158, 11, 0.1);
        color: #f59e0b;
    }

    .badge-class-default {
        background-color: rgba(139, 92, 246, 0.1);
        color: #8b5cf6;
    }
</style>

<div class="container-fluid">
    @php
        $hideGuruPicker = auth()->user()->hasAnyRole([
            'Guru', 'Guru Mapel', 'Guru Kelas', 'Wali Kelas', 'Guru BK', 'Guru Piket'
        ]);
        $disableAgendaActions = auth()->user()->hasRole('Siswa') && ! auth()->user()->hasClassPosition();
    @endphp

    @if(!$hideGuruPicker && $guruQuickAccess->isNotEmpty())
    
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-primary">
                <div class="card-header bg-primary">
                    <h5 class="card-title mb-0 text-white">
                        <i class="ti ti-users me-2"></i>Pilih Guru
                    </h5>
                </div>
                <div class="card-body">
                    <div class="guru-grid">
                        @foreach($guruQuickAccess as $g)
                            <a href="{{ route('agenda_kelas.index', ['guru_id' => $g->id, 'jenis_kegiatan' => $filterJenisKegiatan]) }}" 
                               class="btn guru-btn {{ $filterGuruId == $g->id ? 'btn-primary' : 'btn-outline-primary' }}"
                               title="{{ $g->nama }}">
                                <i class="ti ti-user"></i>
                                <span>{{ $g->nama }}</span>
                            </a>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif

    @if($selectedGuru && $kelasQuickAccess->isNotEmpty())
    
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header bg-primary-subtle d-flex justify-content-between align-items-center">
                    <h5 class="card-title fw-semibold m-0">
                        <i class="ti ti-clock-play me-2"></i>Menu Akses Cepat - {{ $selectedGuru->nama }} (Isi Agenda Kelas)
                    </h5>
                    @if(!$hideGuruPicker)
                    <a href="{{ route('agenda_kelas.index') }}" class="btn btn-sm btn-outline-secondary">
                        <i class="ti ti-x me-1"></i>Reset
                    </a>
                    @endif
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        @foreach($kelasQuickAccess as $kelas)
                        @php
                            // Ekstrak tingkat kelas dari nama kelas (ambil 2 digit pertama)
                            $namaKelas = $kelas->nama_kelas;
                            $tingkatKelas = (int) substr($namaKelas, 0, 2);
                            
                            // Tentukan warna dan class berdasarkan tingkat kelas
                            if ($tingkatKelas == 10) {
                                $borderColor = '#3b82f6';
                                $bgColor = 'rgba(59, 130, 246, 0.05)';
                                $iconColor = '#3b82f6';
                                $btnColor = '#3b82f6';
                                $btnHover = '#2563eb';
                                $badgeColor = '#3b82f6';
                                $badgeBg = 'rgba(59, 130, 246, 0.1)';
                                $tingkatLabel = 'Kelas X';
                            } elseif ($tingkatKelas == 11) {
                                $borderColor = '#10b981';
                                $bgColor = 'rgba(16, 185, 129, 0.05)';
                                $iconColor = '#10b981';
                                $btnColor = '#10b981';
                                $btnHover = '#059669';
                                $badgeColor = '#10b981';
                                $badgeBg = 'rgba(16, 185, 129, 0.1)';
                                $tingkatLabel = 'Kelas XI';
                            } elseif ($tingkatKelas == 12) {
                                $borderColor = '#f59e0b';
                                $bgColor = 'rgba(245, 158, 11, 0.05)';
                                $iconColor = '#f59e0b';
                                $btnColor = '#f59e0b';
                                $btnHover = '#d97706';
                                $badgeColor = '#f59e0b';
                                $badgeBg = 'rgba(245, 158, 11, 0.1)';
                                $tingkatLabel = 'Kelas XII';
                            } else {
                                $borderColor = '#8b5cf6';
                                $bgColor = 'rgba(139, 92, 246, 0.05)';
                                $iconColor = '#8b5cf6';
                                $btnColor = '#8b5cf6';
                                $btnHover = '#7c3aed';
                                $badgeColor = '#8b5cf6';
                                $badgeBg = 'rgba(139, 92, 246, 0.1)';
                                $tingkatLabel = 'Kelas Lainnya';
                            }
                        @endphp
                        <div class="col-md-6 col-lg-4 col-xl-3">
                            <div class="card border-2 h-100 hover-shadow" 
                                 style="border-color: {{ $borderColor }} !important; 
                                        background: linear-gradient(135deg, {{ $bgColor }} 0%, {{ $bgColor }} 100%);
                                        transition: all 0.3s ease;">
                                <div class="card-body text-center">
                                    <div class="card-class-badge" 
                                         style="background-color: {{ $badgeBg }}; 
                                                color: {{ $badgeColor }};
                                                display: inline-block;
                                                padding: 0.25rem 0.75rem;
                                                border-radius: 0.5rem;
                                                font-size: 0.85rem;
                                                font-weight: 600;
                                                margin-bottom: 0.5rem;">
                                        {{ $tingkatLabel }}
                                    </div>
                                    <div class="mb-2">
                                        <i class="ti ti-book-2" style="font-size: 48px; color: {{ $iconColor }} !important;"></i>
                                    </div>
                                    <h5 class="card-title mb-2" style="font-weight: 700; font-size: 1.25rem;">{{ $kelas->nama_kelas }}</h5>
                                    @if(!$sekolah || $sekolah->shouldShowNamaWaliKelasForUser(auth()->user()))
                                        @if($kelas->wali_nama)
                                        <p class="text-muted small mb-2">
                                            <i class="ti ti-user me-1"></i>{{ $kelas->wali_nama }}
                                        </p>
                                        @endif
                                    @else
                                        <p class="text-muted small mb-2">
                                            <i class="ti ti-alert-triangle me-1"></i>
                                            {!! optional($sekolah)->wali_kelas_hidden_message ?: 'Nama wali kelas disembunyikan oleh administrator.' !!}
                                        </p>
                                    @endif
                                    @unless($disableAgendaActions)
                                    <a href="{{ route('agenda_kelas.create', ['kelas_id' => $kelas->id, 'jenis_kegiatan' => $filterJenisKegiatan]) }}" 
                                       class="btn btn-sm w-100" 
                                       style="background-color: {{ $btnColor }} !important; 
                                              border-color: {{ $btnColor }} !important; 
                                              color: white !important;
                                              transition: all 0.3s ease;"
                                       onmouseover="this.style.backgroundColor='{{ $btnHover }}'; this.style.borderColor='{{ $btnHover }}';"
                                       onmouseout="this.style.backgroundColor='{{ $btnColor }}'; this.style.borderColor='{{ $btnColor }}';">
                                        <i class="ti ti-edit me-1"></i>Isi Agenda Kelas
                                    </a>
                                    @endunless
                                    <button type="button" class="btn btn-sm w-100 mt-2 btn-outline-secondary" 
                                            data-bs-toggle="modal" 
                                            data-bs-target="#previewAgendaModal"
                                            onclick="loadAgendaPreview({{ $kelas->id }}, {{ $selectedGuru ? $selectedGuru->id : 'null' }})">
                                        <i class="ti ti-eye me-1"></i>Preview Agenda
                                    </button>
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <div>
                        <h4 class="card-title fw-semibold m-0">
                            Data Agenda Kelas
                            @if($selectedGuru)
                                <span class="badge bg-primary text-white ms-2">{{ $selectedGuru->nama }}</span>
                            @endif
                        </h4>
                    </div>
                    @unless($disableAgendaActions)
                    <a href="{{ route('agenda_kelas.create') }}" class="btn btn-primary btn-sm">
                        <i class="ti ti-plus"></i> Tambah Agenda
                    </a>
                    @endunless
                </div>
                <div class="card-body">
                    @if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
                    @if(session('error'))<div class="alert alert-danger">{{ session('error') }}</div>@endif

                    <form method="GET" action="{{ route('agenda_kelas.index') }}" class="row g-2 mb-2">
                        @if($filterGuruId)
                            <input type="hidden" name="guru_id" value="{{ $filterGuruId }}">
                        @endif
                        <div class="col-md-4 col-lg-3">
                            <select name="jenis_kegiatan" class="form-select form-select-sm" onchange="this.form.submit()">
                                <option value="">Semua Jenis Kegiatan</option>
                                <option value="kbm" {{ $filterJenisKegiatan === 'kbm' ? 'selected' : '' }}>KBM</option>
                                <option value="pengembangan_diri" {{ $filterJenisKegiatan === 'pengembangan_diri' ? 'selected' : '' }}>Pengembangan Diri</option>
                            </select>
                        </div>
                        <div class="col-md-auto">
                            <a href="{{ route('agenda_kelas.index', ['guru_id' => $filterGuruId]) }}" class="btn btn-sm btn-outline-secondary">
                                Reset Filter
                            </a>
                        </div>
                    </form>

                    @if($items->isEmpty())
                        <div class="alert alert-info">
                            <i class="ti ti-info-circle"></i> Belum ada data agenda kelas.
                        </div>
                    @else
                        <div class="table-responsive">
                            <table class="table table-vcenter table-hover table-tabler">
                                <thead>
                                    <tr>
                                        <th>No</th>
                                        <th>Tanggal</th>
                                        <th>Kelas</th>
                                        <th>Guru</th>
                                        <th>Jam KBM</th>
                                        <th>Kegiatan</th>
                                        <th>Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                @foreach($items as $index => $it)
                                    <tr>
                                        <td>{{ $index + 1 }}</td>
                                        <td>{{ \Carbon\Carbon::parse($it->tanggal)->format('d/m/Y') }}</td>
                                        <td>{{ $it->kelas->nama_kelas ?? '-' }}</td>
                                        <td>{{ $it->guru->nama ?? '-' }}</td>
                                        <td>{{ $it->jamBelajar->jam_mulai ?? '-' }} - {{ $it->jamBelajar->jam_selesai ?? '-' }}</td>
                                        <td>
                                            @if(($it->jenis_kegiatan ?? 'kbm') === 'pengembangan_diri')
                                                <span class="badge bg-info text-white mb-1">Pengembangan Diri</span><br>
                                                <strong>{{ $it->nama_kegiatan ?? '-' }}</strong><br>
                                                <small>{{ Str::limit(strip_tags($it->kegiatan), 50) }}</small>
                                            @else
                                                <span class="badge bg-primary text-white mb-1">KBM</span><br>
                                                {{ Str::limit(strip_tags($it->kegiatan), 50) }}
                                            @endif
                                        </td>
                                        <td>
                                            @unless($disableAgendaActions)
                                        <div class="btn-group" role="group">
                                                <a href="{{ route('agenda_kelas.show', $it->id) }}" class="btn btn-sm btn-info btn-modern" title="Lihat">
                                                    <i class="ti ti-eye"></i>
                                                </a>
                                                <a href="{{ route('agenda_kelas.edit', $it->id) }}" class="btn btn-sm btn-warning" title="Edit">
                                                    <i class="ti ti-edit"></i>
                                                </a>
                                                <form action="{{ route('agenda_kelas.destroy', $it->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Yakin ingin menghapus agenda ini?')">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-sm btn-danger" title="Hapus">
                                                        <i class="ti ti-trash"></i>
                                                    </button>
                                                </form>
                                            </div>
                                        @endunless
                                        </td>
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


<div class="modal fade" id="previewAgendaModal" tabindex="-1" role="dialog" aria-labelledby="previewAgendaLabel" aria-hidden="true">
    <div class="modal-dialog modal-fullscreen-lg-down modal-xl" role="document">
        <div class="modal-content">
            <div class="modal-header bg-primary">
                <h5 class="modal-title text-white" id="previewAgendaLabel">
                    <i class="ti ti-file-pdf me-2"></i>Preview Agenda Kelas
                </h5>
                <button type="button" class="close text-white" data-bs-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
            </div>
            <div class="modal-body p-0" style="height: 600px;">
                <div id="pdfContainer" class="w-100 h-100">
                    <div class="d-flex justify-content-center align-items-center h-100">
                        <div class="text-center text-muted">
                            <div class="spinner-border text-primary mb-2" role="status">
                                <span class="visually-hidden">Loading...</span>
                            </div>
                            <p>Memuat preview agenda...</p>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <a href="#" id="downloadLink" class="btn btn-success" target="_blank" style="display: none;">
                    <i class="ti ti-download me-1"></i>Unduh PDF
                </a>
                <a href="#" id="printLink" class="btn btn-info" target="_blank" style="display: none;">
                    <i class="ti ti-printer me-1"></i>Print
                </a>
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>

<script>
let selectedKelasId = null;

// Function untuk load preview agenda
function loadAgendaPreview(kelasId, guruId = null) {
    selectedKelasId = kelasId;
    const pdfContainer = document.getElementById('pdfContainer');
    let previewUrl = `{{ route('agenda_kelas.preview') }}?kelas_id=${kelasId}`;
    if (guruId) {
        previewUrl += `&guru_id=${guruId}`;
    }
    
    // Show loading
    pdfContainer.innerHTML = `
        <div class="d-flex justify-content-center align-items-center h-100">
            <div class="text-center text-muted">
                <div class="spinner-border text-primary mb-2" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
                <p>Memuat preview agenda...</p>
            </div>
        </div>
    `;
    
    // Load PDF in iframe
    setTimeout(() => {
        pdfContainer.innerHTML = `
            <iframe 
                src="${previewUrl}" 
                class="w-100 h-100" 
                style="border: none;"
                onerror="handleIframeError()">
            </iframe>
        `;
        
        // Show download and print buttons
        document.getElementById('downloadLink').style.display = 'inline-block';
        document.getElementById('downloadLink').href = previewUrl;
        
        document.getElementById('printLink').style.display = 'inline-block';
        document.getElementById('printLink').href = previewUrl;
    }, 500);
}

// Handle iframe error
function handleIframeError() {
    const pdfContainer = document.getElementById('pdfContainer');
    pdfContainer.innerHTML = `
        <div class="d-flex justify-content-center align-items-center h-100">
            <div class="alert alert-warning text-center">
                <i class="ti ti-alert-circle me-2"></i>
                <strong>Tidak dapat menampilkan preview</strong><br>
                <small>Silakan download PDF untuk melihatnya</small>
            </div>
        </div>
    `;
}
</script>
@endsection
