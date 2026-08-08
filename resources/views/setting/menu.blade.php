<div class="row">
    <div class="col-md-12">
        <h5 class="mb-3">Menu Guru</h5>
        <form action="{{ route('setting.menu.update') }}" method="POST" id="menuForm">
            @csrf
            @method('POST')

            <div class="card mb-3">
                <div class="card-header fw-semibold">Akademik</div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <div class="form-check form-switch">
                                <input type="checkbox" class="form-check-input" name="guru_menus[]" value="akademik_jadwal_kbm" id="menu_akademik_jadwal_kbm" {{ in_array('akademik_jadwal_kbm', $menuVisibility['guru'] ?? []) ? 'checked' : '' }}>
                                <label class="form-check-label" for="menu_akademik_jadwal_kbm">Jadwal KBM</label>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-check form-switch">
                                <input type="checkbox" class="form-check-input" name="guru_menus[]" value="akademik_jadwal_piket" id="menu_akademik_jadwal_piket" {{ in_array('akademik_jadwal_piket', $menuVisibility['guru'] ?? []) ? 'checked' : '' }}>
                                <label class="form-check-label" for="menu_akademik_jadwal_piket">Jadwal Piket</label>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-check form-switch">
                                <input type="checkbox" class="form-check-input" name="guru_menus[]" value="akademik_pengaturan_jam" id="menu_akademik_pengaturan_jam" {{ in_array('akademik_pengaturan_jam', $menuVisibility['guru'] ?? []) ? 'checked' : '' }}>
                                <label class="form-check-label" for="menu_akademik_pengaturan_jam">Pengaturan Jam</label>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-check form-switch">
                                <input type="checkbox" class="form-check-input" name="guru_menus[]" value="akademik_pengembangan_diri" id="menu_akademik_pengembangan_diri" {{ in_array('akademik_pengembangan_diri', $menuVisibility['guru'] ?? []) ? 'checked' : '' }}>
                                <label class="form-check-label" for="menu_akademik_pengembangan_diri">Pengembangan Diri</label>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-check form-switch">
                                <input type="checkbox" class="form-check-input" name="guru_menus[]" value="akademik_beban_kerja_guru" id="menu_akademik_beban_kerja_guru" {{ in_array('akademik_beban_kerja_guru', $menuVisibility['guru'] ?? []) ? 'checked' : '' }}>
                                <label class="form-check-label" for="menu_akademik_beban_kerja_guru">Beban Kerja Guru</label>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-check form-switch">
                                <input type="checkbox" class="form-check-input" name="guru_menus[]" value="akademik_sk_tugas" id="menu_akademik_sk_tugas" {{ in_array('akademik_sk_tugas', $menuVisibility['guru'] ?? []) ? 'checked' : '' }}>
                                <label class="form-check-label" for="menu_akademik_sk_tugas">SK TUGAS</label>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-check form-switch">
                                <input type="checkbox" class="form-check-input" name="guru_menus[]" value="akademik_komponen_penilaian" id="menu_akademik_komponen_penilaian" {{ in_array('akademik_komponen_penilaian', $menuVisibility['guru'] ?? []) ? 'checked' : '' }}>
                                <label class="form-check-label" for="menu_akademik_komponen_penilaian">Komponen Penilaian</label>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-check form-switch">
                                <input type="checkbox" class="form-check-input" name="guru_menus[]" value="akademik_mata_pelajaran" id="menu_akademik_mata_pelajaran" {{ in_array('akademik_mata_pelajaran', $menuVisibility['guru'] ?? []) ? 'checked' : '' }}>
                                <label class="form-check-label" for="menu_akademik_mata_pelajaran">Mata Pelajaran</label>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-check form-switch">
                                <input type="checkbox" class="form-check-input" name="guru_menus[]" value="akademik_modul_ajar" id="menu_akademik_modul_ajar" {{ in_array('akademik_modul_ajar', $menuVisibility['guru'] ?? []) ? 'checked' : '' }}>
                                <label class="form-check-label" for="menu_akademik_modul_ajar">Modul Ajar</label>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-check form-switch">
                                <input type="checkbox" class="form-check-input" name="guru_menus[]" value="akademik_editor_modul" id="menu_akademik_editor_modul" {{ in_array('akademik_editor_modul', $menuVisibility['guru'] ?? []) ? 'checked' : '' }}>
                                <label class="form-check-label" for="menu_akademik_editor_modul">Editor Modul</label>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card mb-3">
                <div class="card-header fw-semibold">Pembelajaran</div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <div class="form-check form-switch">
                                <input type="checkbox" class="form-check-input" name="guru_menus[]" value="pembelajaran_absensi" id="menu_pembelajaran_absensi" {{ in_array('pembelajaran_absensi', $menuVisibility['guru'] ?? []) ? 'checked' : '' }}>
                                <label class="form-check-label" for="menu_pembelajaran_absensi">Absensi</label>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-check form-switch">
                                <input type="checkbox" class="form-check-input" name="guru_menus[]" value="pembelajaran_agenda_kelas" id="menu_pembelajaran_agenda_kelas" {{ in_array('pembelajaran_agenda_kelas', $menuVisibility['guru'] ?? []) ? 'checked' : '' }}>
                                <label class="form-check-label" for="menu_pembelajaran_agenda_kelas">Agenda Kelas</label>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-check form-switch">
                                <input type="checkbox" class="form-check-input" name="guru_menus[]" value="pembelajaran_agenda_guru" id="menu_pembelajaran_agenda_guru" {{ in_array('pembelajaran_agenda_guru', $menuVisibility['guru'] ?? []) ? 'checked' : '' }}>
                                <label class="form-check-label" for="menu_pembelajaran_agenda_guru">Agenda Guru</label>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-check form-switch">
                                <input type="checkbox" class="form-check-input" name="guru_menus[]" value="pembelajaran_nilai" id="menu_pembelajaran_nilai" {{ in_array('pembelajaran_nilai', $menuVisibility['guru'] ?? []) ? 'checked' : '' }}>
                                <label class="form-check-label" for="menu_pembelajaran_nilai">Nilai</label>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-check form-switch">
                                <input type="checkbox" class="form-check-input" name="guru_menus[]" value="pembelajaran_rekap_nilai" id="menu_pembelajaran_rekap_nilai" {{ in_array('pembelajaran_rekap_nilai', $menuVisibility['guru'] ?? []) ? 'checked' : '' }}>
                                <label class="form-check-label" for="menu_pembelajaran_rekap_nilai">Rekap Nilai</label>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-check form-switch">
                                <input type="checkbox" class="form-check-input" name="guru_menus[]" value="pembelajaran_materi" id="menu_pembelajaran_materi" {{ in_array('pembelajaran_materi', $menuVisibility['guru'] ?? []) ? 'checked' : '' }}>
                                <label class="form-check-label" for="menu_pembelajaran_materi">Materi</label>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-check form-switch">
                                <input type="checkbox" class="form-check-input" name="guru_menus[]" value="pembelajaran_pembina_ekskul" id="menu_pembelajaran_pembina_ekskul" {{ in_array('pembelajaran_pembina_ekskul', $menuVisibility['guru'] ?? []) ? 'checked' : '' }}>
                                <label class="form-check-label" for="menu_pembelajaran_pembina_ekskul">Pembina Ekskul</label>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card mb-3">
                <div class="card-header fw-semibold">Piket KBM</div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <div class="form-check form-switch">
                                <input type="checkbox" class="form-check-input" name="guru_menus[]" value="piket_kbm_absensi_guru" id="menu_piket_kbm_absensi_guru" {{ in_array('piket_kbm_absensi_guru', $menuVisibility['guru'] ?? []) ? 'checked' : '' }}>
                                <label class="form-check-label" for="menu_piket_kbm_absensi_guru">Absensi Guru</label>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-check form-switch">
                                <input type="checkbox" class="form-check-input" name="guru_menus[]" value="piket_kbm_absensi_siswa" id="menu_piket_kbm_absensi_siswa" {{ in_array('piket_kbm_absensi_siswa', $menuVisibility['guru'] ?? []) ? 'checked' : '' }}>
                                <label class="form-check-label" for="menu_piket_kbm_absensi_siswa">Absensi Siswa</label>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-check form-switch">
                                <input type="checkbox" class="form-check-input" name="guru_menus[]" value="piket_kbm_pelanggaran" id="menu_piket_kbm_pelanggaran" {{ in_array('piket_kbm_pelanggaran', $menuVisibility['guru'] ?? []) ? 'checked' : '' }}>
                                <label class="form-check-label" for="menu_piket_kbm_pelanggaran">Pelanggaran</label>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card mb-3">
                <div class="card-header fw-semibold">Wali Kelas</div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <div class="form-check form-switch">
                                <input type="checkbox" class="form-check-input" name="guru_menus[]" value="wali_kelas_dashboard" id="menu_wali_kelas_dashboard" {{ in_array('wali_kelas_dashboard', $menuVisibility['guru'] ?? []) ? 'checked' : '' }}>
                                <label class="form-check-label" for="menu_wali_kelas_dashboard">Dashboard</label>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-check form-switch">
                                <input type="checkbox" class="form-check-input" name="guru_menus[]" value="wali_kelas_data_siswa" id="menu_wali_kelas_data_siswa" {{ in_array('wali_kelas_data_siswa', $menuVisibility['guru'] ?? []) ? 'checked' : '' }}>
                                <label class="form-check-label" for="menu_wali_kelas_data_siswa">Data Siswa</label>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-check form-switch">
                                <input type="checkbox" class="form-check-input" name="guru_menus[]" value="wali_kelas_absensi_kelas" id="menu_wali_kelas_absensi_kelas" {{ in_array('wali_kelas_absensi_kelas', $menuVisibility['guru'] ?? []) ? 'checked' : '' }}>
                                <label class="form-check-label" for="menu_wali_kelas_absensi_kelas">Absensi Kelas</label>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-check form-switch">
                                <input type="checkbox" class="form-check-input" name="guru_menus[]" value="wali_kelas_laporan_guru" id="menu_wali_kelas_laporan_guru" {{ in_array('wali_kelas_laporan_guru', $menuVisibility['guru'] ?? []) ? 'checked' : '' }}>
                                <label class="form-check-label" for="menu_wali_kelas_laporan_guru">Laporan Guru</label>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-check form-switch">
                                <input type="checkbox" class="form-check-input" name="guru_menus[]" value="wali_kelas_nilai_siswa" id="menu_wali_kelas_nilai_siswa" {{ in_array('wali_kelas_nilai_siswa', $menuVisibility['guru'] ?? []) ? 'checked' : '' }}>
                                <label class="form-check-label" for="menu_wali_kelas_nilai_siswa">Nilai Siswa</label>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-check form-switch">
                                <input type="checkbox" class="form-check-input" name="guru_menus[]" value="wali_kelas_rekap_nilai" id="menu_wali_kelas_rekap_nilai" {{ in_array('wali_kelas_rekap_nilai', $menuVisibility['guru'] ?? []) ? 'checked' : '' }}>
                                <label class="form-check-label" for="menu_wali_kelas_rekap_nilai">Rekap Nilai</label>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card mb-3">
                <div class="card-header fw-semibold">Guru BK</div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <div class="form-check form-switch">
                                <input type="checkbox" class="form-check-input" name="guru_menus[]" value="guru_bk" id="menu_guru_bk" {{ in_array('guru_bk', $menuVisibility['guru'] ?? []) ? 'checked' : '' }}>
                                <label class="form-check-label" for="menu_guru_bk">Tampilkan Menu Guru BK</label>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <h5 class="mb-3">Menu Siswa</h5>
            <div class="card mb-3">
                <div class="card-header fw-semibold">Pembelajaran</div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <div class="form-check form-switch">
                                <input type="checkbox" class="form-check-input" name="siswa_menus[]" value="pembelajaran_materi" id="menu_siswa_pembelajaran_materi" {{ in_array('pembelajaran_materi', $menuVisibility['siswa'] ?? []) ? 'checked' : '' }}>
                                <label class="form-check-label" for="menu_siswa_pembelajaran_materi">Materi</label>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="mt-4">
                <button type="submit" class="btn btn-primary">Simpan Pengaturan Menu</button>
                <a href="{{ route('tahun_ajaran.index') }}" class="btn btn-outline-secondary ms-2">Kembali</a>
            </div>
        </form>
    </div>
</div>
