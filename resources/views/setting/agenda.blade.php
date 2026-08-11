<form action="{{ route('setting.agenda.update') }}" method="POST">
    @csrf
    @method('PUT')
    <div class="alert alert-info small mb-4">
        Aturan ini mengontrol apakah guru atau siswa dengan jabatan kelas dapat mengedit <strong>Agenda Kelas</strong> untuk tanggal yang sudah lewat. Tanggal di masa depan tetap selalu bisa diedit.
    </div>

    <div class="row">
        <div class="col-md-6">
            <div class="card h-100 border">
                <div class="card-body">
                    <div class="form-check form-switch">
                        <input type="hidden" name="allow_edit_past_for_guru" value="0">
                        <input class="form-check-input" type="checkbox" id="allow_edit_past_for_guru" name="allow_edit_past_for_guru" value="1" {{ old('allow_edit_past_for_guru', data_get($settings, 'allow_edit_past_for_guru', false)) ? 'checked' : '' }}>
                        <label class="form-check-label" for="allow_edit_past_for_guru">Izinkan guru mengedit agenda kelas tanggal lampau</label>
                    </div>
                    <div class="form-text mt-2">Jika aktif, guru bisa mengubah agenda kelas untuk tanggal sebelumnya. Jika nonaktif, hanya admin, kepala sekolah, wali kelas, dan guru BK yang bisa mengedit agenda tanggal lampau.</div>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card h-100 border">
                <div class="card-body">
                    <div class="form-check form-switch">
                        <input type="hidden" name="allow_edit_past_for_siswa_officer" value="0">
                        <input class="form-check-input" type="checkbox" id="allow_edit_past_for_siswa_officer" name="allow_edit_past_for_siswa_officer" value="1" {{ old('allow_edit_past_for_siswa_officer', data_get($settings, 'allow_edit_past_for_siswa_officer', false)) ? 'checked' : '' }}>
                        <label class="form-check-label" for="allow_edit_past_for_siswa_officer">Izinkan siswa dengan jabatan mengedit agenda kelas tanggal lampau</label>
                    </div>
                    <div class="form-text mt-2">Jika aktif, siswa yang memiliki jabatan kelas bisa mengubah agenda kelas tanggal sebelumnya untuk kelasnya. Jika nonaktif, siswa dengan jabatan tidak bisa mengedit agenda yang sudah terlewat.</div>
                </div>
            </div>
        </div>
    </div>

    <div class="mt-4">
        <button type="submit" class="btn btn-primary">Simpan Pengaturan</button>
        <a href="{{ route('tahun_ajaran.index') }}" class="btn btn-outline-secondary ms-2">Kembali</a>
    </div>
</form>
