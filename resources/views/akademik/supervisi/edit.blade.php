@extends('layouts.app', ['pageSlug' => 'supervisi'])

@section('title', 'Edit Supervisi')

@section('content')
<div class="row">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h4 class="card-title mb-0">Edit Pengaturan Jadwal Supervisi</h4>
            </div>
            <div class="card-body">
                <form action="{{ route('akademik.supervisi.update', $supervisi) }}" method="POST" id="formSupervisiEdit">
                    @csrf
                    @method('PUT')
                    <div class="mb-3">
                        <label class="form-label">Guru</label>
                        <select name="guru_id" id="guru_id" class="form-select @error('guru_id') is-invalid @enderror" required>
                            <option value="">Pilih Guru</option>
                            @foreach($guruList as $guru)
                                <option value="{{ $guru->id }}" {{ old('guru_id', $supervisi->guru_id) == $guru->id ? 'selected' : '' }}>{{ $guru->nama }}</option>
                            @endforeach
                        </select>
                        @error('guru_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Tanggal Supervisi</label>
                        <input type="date" name="tanggal" id="tanggal" class="form-control @error('tanggal') is-invalid @enderror" value="{{ old('tanggal', optional($supervisi->tanggal)->format('Y-m-d')) }}" required>
                        <div class="form-text">Pilih tanggal ketika guru memiliki jadwal KBM.</div>
                        @error('tanggal') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Jadwal KBM</label>
                        <select name="jadwal_kbm_id" id="jadwal_kbm_id" class="form-select @error('jadwal_kbm_id') is-invalid @enderror" required>
                            <option value="">Pilih tanggal dan guru terlebih dahulu</option>
                            @foreach($scheduleOptions as $option)
                                <option value="{{ $option->id }}" {{ old('jadwal_kbm_id', $supervisi->jadwal_kbm_id) == $option->id ? 'selected' : '' }}>
                                    [{{ $option->kelas->nama_kelas ?? '-' }}] {{ $option->mataPelajaran->nama_mapel ?? '-' }} - Jam ke {{ $option->jam_ke }} ({{ $option->jamBelajar->jam_mulai ?? '-' }} - {{ $option->jamBelajar->jam_selesai ?? '-' }})
                                </option>
                            @endforeach
                        </select>
                        @error('jadwal_kbm_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Keterangan</label>
                        <textarea name="keterangan" class="form-control @error('keterangan') is-invalid @enderror">{{ old('keterangan', $supervisi->keterangan) }}</textarea>
                        @error('keterangan') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
                    <a href="{{ route('akademik.supervisi') }}" class="btn btn-secondary">Batal</a>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('js')
<script>
function loadJadwalOptionsEdit() {
    var guruIdEl = document.getElementById('guru_id');
    var tanggalEl = document.getElementById('tanggal');
    var jadwalSelect = document.getElementById('jadwal_kbm_id');
    var guruId = guruIdEl ? guruIdEl.value : '';
    var tanggal = tanggalEl ? tanggalEl.value : '';

    if (!guruId || !tanggal) {
        if (jadwalSelect) jadwalSelect.innerHTML = '<option value="">Pilih tanggal dan guru terlebih dahulu</option>';
        return;
    }

    fetch('{{ url('akademik/supervisi/get-jadwal-options') }}/' + encodeURIComponent(guruId) + '/' + encodeURIComponent(tanggal))
        .then(function(response) { return response.json(); })
        .then(function(response) {
            if (!jadwalSelect) return;
            if (!response || !response.length) {
                jadwalSelect.innerHTML = '<option value="">Tidak ada jadwal KBM untuk tanggal ini</option>';
                return;
            }

            var html = '<option value="">Pilih Jadwal KBM</option>';
            var selectedId = '{{ old('jadwal_kbm_id', $supervisi->jadwal_kbm_id) }}';
            response.forEach(function(item) {
                var selected = (item.id == selectedId) ? ' selected' : '';
                html += '<option value="' + item.id + '"' + selected + '>' +
                    '[' + item.kelas_nama + '] ' + item.mata_pelajaran + ' - Jam ke ' + item.jam_ke + ' (' + (item.jam_mulai || '-') + ' - ' + (item.jam_selesai || '-') + ')</option>';
            });
            jadwalSelect.innerHTML = html;
        })
        .catch(function() {
            if (jadwalSelect) jadwalSelect.innerHTML = '<option value="">Gagal memuat jadwal</option>';
        });
}

document.addEventListener('DOMContentLoaded', function() {
    var guruSelect = document.getElementById('guru_id');
    var tanggalInput = document.getElementById('tanggal');

    if (guruSelect) {
        guruSelect.addEventListener('change', function() {
            if (tanggalInput) tanggalInput.value = '';
            var jadwalSelect = document.getElementById('jadwal_kbm_id');
            if (jadwalSelect) jadwalSelect.innerHTML = '<option value="">Pilih tanggal dan guru terlebih dahulu</option>';
        });
    }

    if (tanggalInput) {
        tanggalInput.addEventListener('change', function() {
            loadJadwalOptionsEdit();
        });
    }

    if (guruSelect && guruSelect.value && tanggalInput && tanggalInput.value) {
        loadJadwalOptionsEdit();
    }
});
</script>
@endpush
