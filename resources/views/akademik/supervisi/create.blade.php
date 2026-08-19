@extends('layouts.app', ['pageSlug' => 'supervisi'])

@section('title', 'Tambah Supervisi')

@section('content')
<div class="row">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h4 class="card-title mb-0">Tambah Pengaturan Jadwal Supervisi</h4>
            </div>
            <div class="card-body">
                <form action="{{ route('akademik.supervisi.store') }}" method="POST" id="formSupervisi">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label">Guru</label>
                        <select name="guru_id" id="guru_id" class="form-select @error('guru_id') is-invalid @enderror" required>
                            <option value="">Pilih Guru</option>
                            @foreach($guruList as $guru)
                                <option value="{{ $guru->id }}" {{ old('guru_id') == $guru->id ? 'selected' : '' }}>{{ $guru->nama }}</option>
                            @endforeach
                        </select>
                        @error('guru_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Tanggal Supervisi</label>
                        <input type="date" name="tanggal" id="tanggal" class="form-control @error('tanggal') is-invalid @enderror" value="{{ old('tanggal') }}" required>
                        <div id="tanggalHelp" class="form-text">Pilih tanggal ketika guru memiliki jadwal KBM. Hanya tanggal yang tersedia dapat dipilih.</div>
                        @error('tanggal') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Jadwal KBM</label>
                        <select name="jadwal_kbm_id" id="jadwal_kbm_id" class="form-select @error('jadwal_kbm_id') is-invalid @enderror" required>
                            <option value="">Pilih tanggal dan guru terlebih dahulu</option>
                        </select>
                        @error('jadwal_kbm_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Keterangan</label>
                        <textarea name="keterangan" class="form-control @error('keterangan') is-invalid @enderror">{{ old('keterangan') }}</textarea>
                        @error('keterangan') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <button type="submit" class="btn btn-primary">Simpan</button>
                    <a href="{{ route('akademik.supervisi') }}" class="btn btn-secondary">Batal</a>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('js')
<script>
var availableDates = [];

function validateTanggal() {
    var tanggalEl = document.getElementById('tanggal');
    var tanggalVal = tanggalEl ? tanggalEl.value : '';
    var valid = availableDates.length === 0 || availableDates.indexOf(tanggalVal) !== -1;

    if (!valid && tanggalVal) {
        tanggalEl.classList.add('is-invalid');
        var tanggalHelp = document.getElementById('tanggalHelp');
        if (tanggalHelp) tanggalHelp.textContent = 'Tanggal tidak valid. Pilih tanggal di mana guru memiliki jadwal KBM.';
    } else {
        if (tanggalEl) tanggalEl.classList.remove('is-invalid');
        var tanggalHelp = document.getElementById('tanggalHelp');
        if (tanggalHelp) tanggalHelp.textContent = 'Pilih tanggal ketika guru memiliki jadwal KBM. Hanya tanggal yang tersedia dapat dipilih.';
    }

    return valid;
}

function loadAvailableDates() {
    var guruIdEl = document.getElementById('guru_id');
    var guruId = guruIdEl ? guruIdEl.value : '';
    var tanggalEl = document.getElementById('tanggal');
    var jadwalSelect = document.getElementById('jadwal_kbm_id');

    if (!guruId) {
        if (tanggalEl) {
            tanggalEl.value = '';
            tanggalEl.disabled = true;
            tanggalEl.removeAttribute('min');
            tanggalEl.removeAttribute('max');
        }
        if (jadwalSelect) jadwalSelect.innerHTML = '<option value="">Pilih tanggal dan guru terlebih dahulu</option>';
        availableDates = [];
        validateTanggal();
        return;
    }

    if (tanggalEl) tanggalEl.disabled = false;
    fetch('{{ url('akademik/supervisi/get-available-dates') }}/' + encodeURIComponent(guruId))
        .then(function(response) {
            console.log('loadAvailableDates response status:', response.status);
            return response.json();
        })
        .then(function(response) {
            console.log('loadAvailableDates response:', response);
            availableDates = (response && response.dates) ? response.dates : [];
            var tanggalHelp = document.getElementById('tanggalHelp');
            if (!availableDates.length) {
                if (tanggalEl) {
                    tanggalEl.removeAttribute('min');
                    tanggalEl.removeAttribute('max');
                }
                if (tanggalHelp) tanggalHelp.textContent = 'Guru ini tidak memiliki jadwal KBM yang tersedia.';
            } else {
                if (tanggalEl) {
                    tanggalEl.setAttribute('min', availableDates[0]);
                    tanggalEl.setAttribute('max', availableDates[availableDates.length - 1]);
                }
                if (tanggalHelp) tanggalHelp.textContent = 'Pilih tanggal ketika guru memiliki jadwal KBM. Hanya tanggal yang tersedia dapat dipilih.';
            }
            validateTanggal();
        })
        .catch(function() {
            availableDates = [];
            validateTanggal();
        });
}

function loadJadwalOptions() {
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
        .then(function(response) {
            console.log('loadJadwalOptions response status:', response.status);
            return response.json();
        })
        .then(function(response) {
            console.log('loadJadwalOptions response:', response);
            if (!jadwalSelect) return;
            if (!response || !response.length) {
                jadwalSelect.innerHTML = '<option value="">Tidak ada jadwal KBM untuk tanggal ini</option>';
                return;
            }

            var html = '<option value="">Pilih Jadwal KBM</option>';
            response.forEach(function(item) {
                html += '<option value="' + item.id + '">[' + item.kelas_nama + '] ' + item.mata_pelajaran + ' - Jam ke ' + item.jam_ke + ' (' + (item.jam_mulai || '-') + ' - ' + (item.jam_selesai || '-') + ')</option>';
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
    console.log('DOMContentLoaded: guruSelect', !!guruSelect, 'tanggalInput', !!tanggalInput);

    if (guruSelect) {
        guruSelect.addEventListener('change', function() {
            console.log('guru changed to:', this.value);
            if (tanggalInput) tanggalInput.value = '';
            var jadwalSelect = document.getElementById('jadwal_kbm_id');
            if (jadwalSelect) jadwalSelect.innerHTML = '<option value="">Pilih tanggal dan guru terlebih dahulu</option>';
            loadAvailableDates();
        });
    }

    if (tanggalInput) {
        tanggalInput.addEventListener('change', function() {
            console.log('tanggal changed to:', this.value);
            if (validateTanggal()) {
                loadJadwalOptions();
            } else {
                var jadwalSelect = document.getElementById('jadwal_kbm_id');
                if (jadwalSelect) jadwalSelect.innerHTML = '<option value="">Pilih tanggal yang valid terlebih dahulu</option>';
            }
        });
    }

    if (guruSelect && guruSelect.value) {
        console.log('initial load for guru:', guruSelect.value);
        loadAvailableDates();
    }
});
</script>
@endpush
