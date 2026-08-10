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
    var tanggalVal = $('#tanggal').val();
    var valid = availableDates.length === 0 || availableDates.indexOf(tanggalVal) !== -1;

    if (!valid && tanggalVal) {
        $('#tanggal').addClass('is-invalid');
        $('#tanggalHelp').text('Tanggal tidak valid. Pilih tanggal di mana guru memiliki jadwal KBM.');
    } else {
        $('#tanggal').removeClass('is-invalid');
        $('#tanggalHelp').text('Pilih tanggal ketika guru memiliki jadwal KBM. Hanya tanggal yang tersedia dapat dipilih.');
    }

    return valid;
}

function loadAvailableDates() {
    var guruId = $('#guru_id').val();
    if (!guruId) {
        $('#tanggal').val('');
        $('#tanggal').prop('disabled', true);
        $('#tanggal').attr('min', '');
        $('#tanggal').attr('max', '');
        $('#jadwal_kbm_id').html('<option value="">Pilih tanggal dan guru terlebih dahulu</option>');
        availableDates = [];
        validateTanggal();
        return;
    }

    $('#tanggal').prop('disabled', false);
    $.getJSON('{{ url('akademik/supervisi/get-available-dates') }}/' + guruId, function(response) {
        availableDates = response?.dates || [];
        if (!availableDates.length) {
            $('#tanggal').attr('min', '');
            $('#tanggal').attr('max', '');
            $('#tanggalHelp').text('Guru ini tidak memiliki jadwal KBM yang tersedia.');
        } else {
            $('#tanggal').attr('min', availableDates[0]);
            $('#tanggal').attr('max', availableDates[availableDates.length - 1]);
            $('#tanggalHelp').text('Pilih tanggal ketika guru memiliki jadwal KBM. Hanya tanggal yang tersedia dapat dipilih.');
        }
        validateTanggal();
    });
}

function loadJadwalOptions() {
    var guruId = $('#guru_id').val();
    var tanggal = $('#tanggal').val();
    if (!guruId || !tanggal) {
        $('#jadwal_kbm_id').html('<option value="">Pilih tanggal dan guru terlebih dahulu</option>');
        return;
    }

    $.getJSON('{{ url('akademik/supervisi/get-jadwal-options') }}/' + guruId + '/' + tanggal, function(response) {
        if (!response || !response.length) {
            $('#jadwal_kbm_id').html('<option value="">Tidak ada jadwal KBM untuk tanggal ini</option>');
            return;
        }

        var html = '<option value="">Pilih Jadwal KBM</option>';
        response.forEach(function(item) {
            html += '<option value="' + item.id + '">[' + item.kelas_nama + '] ' + item.mata_pelajaran + ' - Jam ke ' + item.jam_ke + ' (' + (item.jam_mulai || '-') + ' - ' + (item.jam_selesai || '-') + ')</option>';
        });
        $('#jadwal_kbm_id').html(html);
    });
}

$(function() {
    $('#guru_id').on('change', function() {
        $('#tanggal').val('');
        $('#jadwal_kbm_id').html('<option value="">Pilih tanggal dan guru terlebih dahulu</option>');
        loadAvailableDates();
    });

    $('#tanggal').on('change', function() {
        if (validateTanggal()) {
            loadJadwalOptions();
        } else {
            $('#jadwal_kbm_id').html('<option value="">Pilih tanggal yang valid terlebih dahulu</option>');
        }
    });

    if ($('#guru_id').val()) {
        loadAvailableDates();
    }
});
</script>
@endpush
