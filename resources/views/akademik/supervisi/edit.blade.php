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
            html += '<option value="' + item.id + '"' + (item.id == '{{ old('jadwal_kbm_id', $supervisi->jadwal_kbm_id) }}' ? ' selected' : '') + '>' +
                '[' + item.kelas_nama + '] ' + item.mata_pelajaran + ' - Jam ke ' + item.jam_ke + ' (' + (item.jam_mulai || '-') + ' - ' + (item.jam_selesai || '-') + ')</option>';
        });
        $('#jadwal_kbm_id').html(html);
    });
}

$(function() {
    $('#guru_id').on('change', function() {
        $('#tanggal').val('');
        $('#jadwal_kbm_id').html('<option value="">Pilih tanggal dan guru terlebih dahulu</option>');
    });

    $('#tanggal').on('change', function() {
        loadJadwalOptionsEdit();
    });

    if ($('#guru_id').val() && $('#tanggal').val()) {
        loadJadwalOptionsEdit();
    }
});
</script>
@endpush
