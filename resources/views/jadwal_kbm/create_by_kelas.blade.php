@extends('layouts.app', ['pageSlug' => 'jadwal-kbm'])

@section('title','Atur Jadwal KBM')

@section('content')
<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header">
                <div class="row align-items-center">
                    <div class="col">
                        <h4 class="card-title mb-0">Atur Jadwal KBM - {{ $kelas->nama_kelas }}</h4>
                        <p class="text-muted mb-0 mt-1">
                            <small>
                                <i class="ti ti-user me-1"></i>Wali Kelas: {{ $kelas->waliKelas->nama ?? '-' }} | 
                                <i class="ti ti-layer ms-2 me-1"></i>Tingkat: {{ $kelas->tingkat_kelas ?? '-' }}
                                @if($kelas->jurusan)
                                    | <i class="ti ti-books ms-2 me-1"></i>Jurusan: {{ $kelas->jurusan }}
                                @endif
                            </small>
                        </p>
                    </div>
                    <div class="col-auto d-flex gap-2">
                        <a href="{{ route('jadwal-kbm.print', $kelas->id) }}" class="btn btn-info btn-sm" target="_blank">
                            <i class="ti ti-printer me-1"></i>Print Jadwal
                        </a>
                        <a href="{{ route('jadwal-kbm.index') }}" class="btn btn-secondary btn-sm">
                            <i class="ti ti-arrow-left me-1"></i>Kembali
                        </a>
                    </div>
                </div>
            </div>
            <div class="card-body">
                @if(session('error'))
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <i class="ti ti-alert-circle me-2"></i>{{ session('error') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif

                <form action="{{ route('jadwal-kbm.store') }}" method="POST" id="formJadwal">
                    @csrf
                    <input type="hidden" name="kelas_id" value="{{ $kelas->id }}">

                    <div class="alert alert-info">
                        <i class="ti ti-info-circle me-2"></i>
                        <strong>Petunjuk:</strong> Pilih guru dan mata pelajaran untuk setiap jam KBM. Sistem akan memvalidasi apakah guru tersedia di waktu yang dipilih.
                    </div>

                    <!-- Tabs untuk setiap hari -->
                    <ul class="nav nav-tabs mb-3" id="hariTab" role="tablist">
                        @php
                            $hariList = ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
                        @endphp
                        @foreach($hariList as $index => $hari)
                        <li class="nav-item" role="presentation">
                            <button class="nav-link {{ $index === 0 ? 'active' : '' }}" 
                                    id="{{ strtolower($hari) }}-tab" 
                                    data-bs-toggle="tab" 
                                    data-bs-target="#{{ strtolower($hari) }}" 
                                    type="button" 
                                    role="tab">
                                {{ $hari }}
                            </button>
                        </li>
                        @endforeach
                    </ul>

                    <div class="tab-content" id="hariTabContent">
                        @foreach($hariList as $index => $hari)
                        <div class="tab-pane fade {{ $index === 0 ? 'show active' : '' }}" 
                             id="{{ strtolower($hari) }}" 
                             role="tabpanel">
                            
                            @php
                                $jamHari = $jamBelajarByHari->get($hari, collect());
                                $existingHari = $existingJadwal->get($hari, collect());
                            @endphp

                            @if($jamHari->isEmpty())
                                <div class="alert alert-warning">
                                    Tidak ada jam KBM yang diatur untuk hari {{ $hari }}
                                </div>
                            @else
                                <div class="table-responsive">
                                    <table class="table table-bordered">
                                        <thead>
                                            <tr>
                                                <th width="10%">Jam Ke</th>
                                                <th width="15%">Waktu</th>
                                                <th width="10%">Jenis</th>
                                                <th width="30%">Mata Pelajaran</th>
                                                <th width="30%">Guru Pengajar</th>
                                                <th width="5%">
                                                    <button type="button" class="btn btn-sm btn-success" onclick="copyFromPrevious('{{ strtolower($hari) }}')">
                                                        <i class="ti ti-copy"></i>
                                                    </button>
                                                </th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($jamHari as $jam)
                                            @php
                                                $existing = $existingHari->firstWhere('jam_ke', $jam->urutan);
                                            @endphp
                                            <tr class="jadwal-row" data-hari="{{ $hari }}" data-jam="{{ $jam->urutan }}">
                                                <td class="text-center">{{ $jam->urutan }}</td>
                                                <td>{{ $jam->jam_mulai }} - {{ $jam->jam_selesai }}</td>
                                                <td>
                                                    <span class="badge {{ $jam->jenis === 'KBM' ? 'bg-primary' : 'bg-secondary' }}">
                                                        {{ $jam->jenis }}
                                                    </span>
                                                </td>
                                                <td>
                                                    @if($jam->jenis === 'KBM')
                                                        <input type="hidden" name="jadwal[{{ $hari }}_{{ $jam->urutan }}][hari]" value="{{ $hari }}">
                                                        <input type="hidden" name="jadwal[{{ $hari }}_{{ $jam->urutan }}][jam_ke]" value="{{ $jam->urutan }}">
                                                        <input type="hidden" name="jadwal[{{ $hari }}_{{ $jam->urutan }}][jam_belajar_id]" value="{{ $jam->id }}">
                                                        
                                                        <select name="jadwal[{{ $hari }}_{{ $jam->urutan }}][mata_pelajaran_id]" 
                                                                class="form-select form-select-sm mapel-select" 
                                                                data-hari="{{ $hari }}" 
                                                                data-jam="{{ $jam->urutan }}">
                                                            <option value="">-- Pilih Mata Pelajaran --</option>
                                                            @foreach($mataPelajaranList as $mapel)
                                                                <option value="{{ $mapel->id }}" 
                                                                        {{ $existing && $existing->mata_pelajaran_id == $mapel->id ? 'selected' : '' }}>
                                                                    {{ $mapel->nama_mapel }}
                                                                </option>
                                                            @endforeach
                                                        </select>
                                                    @else
                                                        <span class="text-muted">{{ $jam->jenis }}</span>
                                                    @endif
                                                </td>
                                                <td>
                                                    @if($jam->jenis === 'KBM')
                                                        <select name="jadwal[{{ $hari }}_{{ $jam->urutan }}][guru_id]" 
                                                                class="form-select form-select-sm guru-select" 
                                                                data-hari="{{ $hari }}" 
                                                                data-jam="{{ $jam->urutan }}">
                                                            <option value="">-- Pilih Guru --</option>
                                                            @foreach($guruList as $guru)
                                                                <option value="{{ $guru->id }}" 
                                                                        {{ $existing && $existing->guru_id == $guru->id ? 'selected' : '' }}>
                                                                    {{ $guru->nama }}
                                                                </option>
                                                            @endforeach
                                                        </select>
                                                        <small class="text-danger konflik-warning" style="display:none;">
                                                            Guru sudah mengajar di kelas lain!
                                                        </small>
                                                    @else
                                                        <span class="text-muted">-</span>
                                                    @endif
                                                </td>
                                                <td class="text-center">
                                                    @if($jam->jenis === 'KBM')
                                                        <button type="button" class="btn btn-sm btn-danger" onclick="clearJadwal(this)">
                                                            <i class="ti ti-trash"></i>
                                                        </button>
                                                    @endif
                                                </td>
                                            </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @endif
                        </div>
                        @endforeach
                    </div>

                    <div class="mt-4">
                        <button type="submit" class="btn btn-primary">
                            <i class="ti ti-device-floppy me-1"></i>Simpan Jadwal
                        </button>
                        <a href="{{ route('jadwal-kbm.index') }}" class="btn btn-secondary">
                            <i class="ti ti-x me-1"></i>Batal
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    // Check konflik guru saat memilih guru
    $('.guru-select').on('change', function() {
        const $row = $(this).closest('.jadwal-row');
        const guruId = $(this).val();
        const hari = $row.data('hari');
        const jamKe = $row.data('jam');
        const kelasId = {{ $kelas->id }};
        const $warning = $row.find('.konflik-warning');
        
        if (guruId) {
            $.ajax({
                url: '{{ route("jadwal-kbm.check-konflik-guru") }}',
                method: 'POST',
                data: {
                    _token: '{{ csrf_token() }}',
                    guru_id: guruId,
                    hari: hari,
                    jam_ke: jamKe,
                    kelas_id: kelasId
                },
                success: function(response) {
                    if (response.konflik) {
                        $warning.text(`Guru sudah mengajar di ${response.data.kelas.nama_kelas}`).show();
                    } else {
                        $warning.hide();
                    }
                }
            });
        } else {
            $warning.hide();
        }
    });
});

function clearJadwal(btn) {
    const $row = $(btn).closest('tr');
    $row.find('select').val('').trigger('change');
}

function copyFromPrevious(currentHari) {
    const hariList = ['senin', 'selasa', 'rabu', 'kamis', 'jumat', 'sabtu'];
    const currentIndex = hariList.indexOf(currentHari);
    
    if (currentIndex === 0) {
        alert('Tidak ada hari sebelumnya untuk disalin');
        return;
    }
    
    const previousHari = hariList[currentIndex - 1];
    
    if (confirm(`Salin jadwal dari ${previousHari.toUpperCase()}?`)) {
        $(`#${previousHari} .jadwal-row`).each(function(index) {
            const jamKe = $(this).data('jam');
            const mapelVal = $(this).find('.mapel-select').val();
            const guruVal = $(this).find('.guru-select').val();
            
            const $targetRow = $(`#${currentHari} .jadwal-row[data-jam="${jamKe}"]`);
            $targetRow.find('.mapel-select').val(mapelVal);
            $targetRow.find('.guru-select').val(guruVal).trigger('change');
        });
    }
}

// Validasi sebelum submit
$('#formJadwal').on('submit', function(e) {
    let hasKonflik = false;
    $('.konflik-warning:visible').each(function() {
        hasKonflik = true;
    });
    
    if (hasKonflik) {
        e.preventDefault();
        alert('Masih ada konflik jadwal guru. Silakan periksa kembali.');
        return false;
    }
});
</script>
@endpush
