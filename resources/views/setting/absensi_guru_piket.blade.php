<form action="{{ route('setting.absensi.guru_piket.update') }}" method="POST">
    @csrf
    @method('PUT')

    <div class="alert alert-info">
        Atur apakah Guru Piket boleh menginput atau mengubah absensi guru untuk tanggal yang sudah terlewat. Pengaturan ini hanya berlaku pada fitur Absensi Guru Piket; akses Admin tidak terpengaruh.
    </div>

    <div class="card border">
        <div class="card-body">
            <div class="form-check form-switch">
                <input type="hidden" name="block_past_dates_for_guru_piket" value="0">
                <input
                    class="form-check-input"
                    type="checkbox"
                    id="block_past_dates_for_guru_piket"
                    name="block_past_dates_for_guru_piket"
                    value="1"
                    {{ old('block_past_dates_for_guru_piket', data_get($settings, 'block_past_dates_for_guru_piket', false)) ? 'checked' : '' }}
                >
                <label class="form-check-label" for="block_past_dates_for_guru_piket">
                    Blokir input dan perubahan absensi Guru Piket untuk tanggal terlewat
                </label>
            </div>
            <div class="form-text mt-2">
                Jika diaktifkan, Guru Piket tidak dapat input, mengubah, menghapus, atau menggunakan Hadir Semua pada tanggal sebelum hari ini. Absensi hari ini dan tanggal mendatang tetap mengikuti aturan yang berlaku.
            </div>
        </div>
    </div>

    <div class="mt-4">
        <button type="submit" class="btn btn-primary">Simpan Pengaturan</button>
    </div>
</form>
