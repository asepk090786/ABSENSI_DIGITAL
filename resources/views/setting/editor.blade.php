<form action="{{ route('setting.editor.update') }}" method="POST">
    @csrf
    @method('PUT')

    <div class="alert alert-info small mb-4">
        Konfigurasi ini mengontrol integrasi Collabora Online untuk editor modul.
        Gunakan URL server Collabora yang valid dan aktifkan token WOPI jika diperlukan.
    </div>

    <div class="row">
        <div class="col-md-6">
            <div class="card h-100 border">
                <div class="card-body">
                    <div class="form-check form-switch">
                        <input type="hidden" name="collabora_enabled" value="0">
                        <input class="form-check-input" type="checkbox" id="collabora_enabled" name="collabora_enabled" value="1" {{ old('collabora_enabled', data_get($settings, 'collabora_enabled', true)) ? 'checked' : '' }}>
                        <label class="form-check-label" for="collabora_enabled">Aktifkan Collabora Online</label>
                    </div>
                    <div class="form-text mt-2">Jika nonaktif, integrasi Collabora tidak digunakan dan editor modul akan tetap berjalan tanpa WOPI.</div>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card h-100 border">
                <div class="card-body">
                    <div class="form-check form-switch">
                        <input type="hidden" name="collabora_require_wopi_token" value="0">
                        <input class="form-check-input" type="checkbox" id="collabora_require_wopi_token" name="collabora_require_wopi_token" value="1" {{ old('collabora_require_wopi_token', data_get($settings, 'collabora_require_wopi_token', true)) ? 'checked' : '' }}>
                        <label class="form-check-label" for="collabora_require_wopi_token">Wajibkan token WOPI</label>
                    </div>
                    <div class="form-text mt-2">Jika aktif, permintaan WOPI harus menyertakan token untuk akses file yang lebih aman.</div>
                </div>
            </div>
        </div>
    </div>

        <div class="row mt-4">
            <div class="col-md-6">
                <div class="card h-100 border">
                    <div class="card-body">
                        <div class="mb-3">
                            <label class="form-label" for="collabora_url">URL Server Collabora</label>
                            <input type="url" class="form-control" id="collabora_url" name="collabora_url" value="{{ old('collabora_url', data_get($settings, 'collabora_url', config('services.collabora.url'))) }}">
                        </div>
                        <div class="form-text">Contoh: https://collabora.example.com</div>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card h-100 border">
                    <div class="card-body">
                        <div class="mb-3">
                            <label class="form-label" for="collabora_wopi_host">WOPI Host Utama</label>
                            <input type="url" class="form-control" id="collabora_wopi_host" name="collabora_wopi_host" value="{{ old('collabora_wopi_host', data_get($settings, 'collabora_wopi_host', config('services.collabora.wopi_host'))) }}">
                        </div>
                        <div class="form-text">Host utama yang digunakan untuk menghasilkan URL WOPI. Biasanya sama dengan APP_URL.</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row mt-4">
            <div class="col-md-12">
                <div class="card h-100 border">
                    <div class="card-body">
                        <div class="mb-3">
                            <label class="form-label" for="collabora_allowed_wopi_hosts">Host WOPI yang Diizinkan</label>
                            <textarea class="form-control" id="collabora_allowed_wopi_hosts" name="collabora_allowed_wopi_hosts" rows="3" placeholder="https://trial.sman1pontang.sch.id&#10;https://simadis.sman1pontang.sch.id">{{ old('collabora_allowed_wopi_hosts', data_get($settings, 'collabora_allowed_wopi_hosts', '')) }}</textarea>
                        </div>
                        <div class="form-text">Daftar host WOPI yang diizinkan diintegrasikan ke Collabora Online. Satu host per baris. Jika dikosongkan, akan menggunakan APP_URL dan WOPI Host Utama.</div>
                    </div>
                </div>
            </div>
        </div>

    <div class="mt-4">
        <button type="submit" class="btn btn-primary">Simpan Pengaturan</button>
        <a href="{{ route('tahun_ajaran.index') }}" class="btn btn-outline-secondary ms-2">Kembali</a>
    </div>
</form>
