<form action="{{ route('setting.absensi.update') }}" method="POST">
    @csrf
    @method('PUT')
    <div class="alert alert-info small mb-4">
    Aturan ini mengontrol apakah guru atau siswa dengan jabatan kelas dapat mengubah absensi untuk tanggal yang sudah lewat, serta apakah guru dapat mengedit agenda kelas di tanggal sebelumnya. Tanggal di masa depan tetap selalu diblokir.
</div>

<div class="row">
    <div class="col-md-6">
        <div class="card h-100 border">
            <div class="card-body">
                <div class="form-check form-switch">
                    <input type="hidden" name="allow_edit_past_for_guru" value="0">
                    <input class="form-check-input" type="checkbox" id="allow_edit_past_for_guru" name="allow_edit_past_for_guru" value="1" {{ old('allow_edit_past_for_guru', data_get($settings, 'allow_edit_past_for_guru', false)) ? 'checked' : '' }}>
                    <label class="form-check-label" for="allow_edit_past_for_guru">Izinkan guru mengedit absensi tanggal lampau</label>
                </div>
                <div class="form-text mt-2">Jika aktif, guru bisa mengubah absensi untuk tanggal sebelumnya dan juga mengedit agenda kelas yang dibuat di tanggal sebelumnya. Jika nonaktif, hanya admin/kepala/wali kelas/guru BK yang bisa.</div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card h-100 border">
            <div class="card-body">
                <div class="form-check form-switch">
                    <input type="hidden" name="allow_edit_past_for_siswa_officer" value="0">
                    <input class="form-check-input" type="checkbox" id="allow_edit_past_for_siswa_officer" name="allow_edit_past_for_siswa_officer" value="1" {{ old('allow_edit_past_for_siswa_officer', data_get($settings, 'allow_edit_past_for_siswa_officer', false)) ? 'checked' : '' }}>
                    <label class="form-check-label" for="allow_edit_past_for_siswa_officer">Izinkan siswa dengan jabatan mengedit absensi tanggal lampau</label>
                </div>
                <div class="form-text mt-2">Jika aktif, siswa yang memiliki jabatan kelas bisa mengubah absensi tanggal sebelumnya untuk kelasnya.</div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card h-100 border">
            <div class="card-body">
                <div class="mb-3">
                    <label class="form-label" for="verification_timeout_seconds">Timeout Kode Verifikasi (detik)</label>
                    <input type="number" min="10" max="3600" class="form-control" id="verification_timeout_seconds" name="verification_timeout_seconds" value="{{ old('verification_timeout_seconds', $settings['verification_timeout_seconds'] ?? 300) }}">
                </div>
                <div class="form-text mt-2">Waktu dalam detik sebelum kode verifikasi otomatis diperbarui. Nilai yang disarankan antara 60-600 detik.</div>
            </div>
        </div>
    </div>
</div>

<div class="mt-4">
    <button type="submit" class="btn btn-primary">Simpan Pengaturan</button>
    <a href="{{ route('tahun_ajaran.index') }}" class="btn btn-outline-secondary ms-2">Kembali</a>
</div>
</form>
