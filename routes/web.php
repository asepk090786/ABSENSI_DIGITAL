<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\JamBelajarController;
use App\Http\Controllers\AgendaKelasController;
use App\Http\Controllers\AgendaGuruController;
use App\Http\Controllers\SettingController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\UserManagementController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\KurikulumController;
use App\Http\Controllers\JadwalKbmController;
use App\Http\Controllers\UpdateController;
use App\Http\Controllers\KegiatanController;
use App\Http\Controllers\JenisKegiatanController;
use App\Http\Controllers\EkstrakurikulerController;

Route::get('/', function(){
    return redirect()->route('home');
});

Route::get('login',[AuthController::class,'showLogin'])->name('login');
Route::post('login',[AuthController::class,'login'])->name('login.post');
Route::post('logout',[AuthController::class,'logout'])->name('logout');

Route::get('/home', [DashboardController::class, 'index'])->middleware('auth')->name('home');

// Wali Kelas routes
Route::middleware(['auth'])->prefix('wali-kelas')->name('wali_kelas.')->group(function () {
    Route::get('/', [App\Http\Controllers\WaliKelasController::class, 'index'])->name('index');
    Route::get('/siswa', [App\Http\Controllers\WaliKelasController::class, 'siswa'])->name('siswa');
    Route::get('/absensi', [App\Http\Controllers\WaliKelasController::class, 'absensi'])->name('absensi');
    Route::get('/laporan-guru', [App\Http\Controllers\WaliKelasController::class, 'laporanGuru'])->name('laporan_guru');
    Route::post('/laporan-guru', [App\Http\Controllers\WaliKelasController::class, 'storeLaporanGuru'])->name('laporan_guru.store');
    Route::get('/nilai', [App\Http\Controllers\WaliKelasController::class, 'nilai'])->name('nilai');
    Route::get('/nilai/{siswa}', [App\Http\Controllers\WaliKelasController::class, 'nilaiSiswa'])->name('nilai_siswa');
});

// Jam Belajar routes
Route::middleware(['auth'])->group(function(){
    // Guru routes with export/import
    Route::resource('guru','App\Http\Controllers\GuruController');
    Route::post('guru/bulk-delete', ['App\Http\Controllers\GuruController', 'bulkDelete'])->name('guru.bulk-delete');
    Route::post('guru/{guru}/generate-account', ['App\Http\Controllers\GuruController', 'generateAccount'])->name('guru.generate-account');
    Route::get('guru-export', ['App\Http\Controllers\GuruController', 'export'])->name('guru.export');
    Route::get('guru-template', ['App\Http\Controllers\GuruController', 'templateDownload'])->name('guru.template');
    Route::post('guru-import', ['App\Http\Controllers\GuruController', 'import'])->name('guru.import');
    
    Route::resource('siswa','App\Http\Controllers\SiswaController');
    Route::get('siswa-export', ['App\Http\Controllers\SiswaController', 'export'])->name('siswa.export');
    Route::get('siswa-template', ['App\Http\Controllers\SiswaController', 'templateDownload'])->name('siswa.template');
    Route::post('siswa-import', ['App\Http\Controllers\SiswaController', 'import'])->name('siswa.import');

    Route::resource('ekstrakurikuler', EkstrakurikulerController::class)->only(['index', 'create', 'store']);

    Route::resource('kelas','App\Http\Controllers\KelasController');
    Route::get('kelas-export', ['App\Http\Controllers\KelasController', 'export'])->name('kelas.export');
    Route::get('kelas-template', ['App\Http\Controllers\KelasController', 'templateDownload'])->name('kelas.template');
    Route::post('kelas-import', ['App\Http\Controllers\KelasController', 'import'])->name('kelas.import');
    Route::get('kelas/{kela}/siswa-export', ['App\Http\Controllers\KelasController', 'studentExport'])->name('kelas.siswa.export');
    Route::get('kelas/{kela}/siswa-template', ['App\Http\Controllers\KelasController', 'studentTemplate'])->name('kelas.siswa.template');
    
    // Debug test route
    Route::get('test-template-structure/{id}', function($id) {
        $export = new \App\Exports\KelasSiswaTemplateExportNew($id);
        $data = $export->array();
        return response()->json([
            'class' => get_class($export),
            'total_rows' => count($data),
            'row_1' => $data[0] ?? null,
            'row_7_header' => $data[6] ?? null,
            'column_count' => isset($data[6]) ? count($data[6]) : 0,
        ]);
    });
    
    Route::post('kelas/{kela}/siswa-import', ['App\Http\Controllers\KelasController', 'studentImport'])->name('kelas.siswa.import');
    Route::post('kelas/{kela}/siswa-add', ['App\Http\Controllers\KelasController', 'addStudent'])->name('kelas.siswa.add');
    Route::post('kelas/{kela}/siswa-assign', ['App\Http\Controllers\KelasController', 'assignExistingStudent'])->name('kelas.siswa.assign');
    Route::delete('kelas/{kela}/siswa/{siswa}', ['App\Http\Controllers\KelasController', 'deleteStudent'])->name('kelas.siswa.delete');
    Route::post('kelas/{kela}/siswa-bulk-delete', ['App\Http\Controllers\KelasController', 'bulkDeleteStudent'])->name('kelas.siswa.bulk-delete');
    Route::post('kelas/{kela}/siswa-bulk-deactivate', ['App\Http\Controllers\KelasController', 'bulkDeactivateStudent'])->name('kelas.siswa.bulk-deactivate');
    Route::post('kelas/{kela}/siswa-bulk-activate', ['App\Http\Controllers\KelasController', 'bulkActivateStudent'])->name('kelas.siswa.bulk-activate');
    Route::get('absensi/get-siswa', ['App\Http\Controllers\AbsensiController', 'getSiswa'])->name('absensi.get-siswa');
    Route::get('piket-kbm/pelanggaran', ['App\Http\Controllers\PiketPelanggaranController', 'index'])->name('piket.pelanggaran.index');
    Route::post('piket-kbm/pelanggaran', ['App\Http\Controllers\PiketPelanggaranController', 'store'])->name('piket.pelanggaran.store');
    Route::get('absensi/bk-monitoring/export', ['App\Http\Controllers\AbsensiController', 'exportBkMonitoring'])->name('absensi.bk-monitoring.export');
    Route::post('absensi/{absensi}/laporan-siswa', ['App\Http\Controllers\AbsensiController', 'storeLaporanSiswa'])->name('absensi.laporan-siswa.store');
    Route::resource('absensi','App\Http\Controllers\AbsensiController');
    Route::resource('nilai','App\Http\Controllers\NilaiController')->only(['index', 'store']);
    Route::post('nilai/update-batch', ['App\Http\Controllers\NilaiController', 'updateBatch'])->name('nilai.update-batch');
    Route::post('nilai/import', ['App\Http\Controllers\NilaiController', 'import'])->name('nilai.import');
    Route::get('nilai/template', ['App\Http\Controllers\NilaiController', 'template'])->name('nilai.template');
    
    // Capaian Pembelajaran Export/Import Routes
    Route::get('capaian_pembelajaran/export', ['App\Http\Controllers\CapaianPembelajaranController', 'export'])->name('capaian_pembelajaran.export');
    Route::get('capaian_pembelajaran/template', ['App\Http\Controllers\CapaianPembelajaranController', 'template'])->name('capaian_pembelajaran.template');
    Route::post('capaian_pembelajaran/import', ['App\Http\Controllers\CapaianPembelajaranController', 'import'])->name('capaian_pembelajaran.import');
    
    // Komponen Nilai Export/Import Routes
    Route::get('komponen_nilai/export', ['App\Http\Controllers\KomponenNilaiController', 'export'])->name('komponen_nilai.export');
    Route::get('komponen_nilai/template', ['App\Http\Controllers\KomponenNilaiController', 'template'])->name('komponen_nilai.template');
    Route::post('komponen_nilai/import', ['App\Http\Controllers\KomponenNilaiController', 'import'])->name('komponen_nilai.import');
    
    Route::resource('komponen_nilai', 'App\Http\Controllers\KomponenNilaiController')->except(['show', 'create']);
    Route::resource('capaian_pembelajaran', 'App\Http\Controllers\CapaianPembelajaranController')->except(['show', 'create', 'index']);
    Route::get('capaian_pembelajaran/list', ['App\Http\Controllers\CapaianPembelajaranController', 'list'])->name('capaian_pembelajaran.list');
    
    // Rekap Nilai
    Route::get('rekap-nilai', ['App\Http\Controllers\RekapNilaiController', 'index'])->name('rekap_nilai.index');
    Route::get('rekap-nilai/export', ['App\Http\Controllers\RekapNilaiController', 'export'])->name('rekap_nilai.export');
    
    // Rekap Nilai
    Route::get('rekap-nilai', ['App\Http\Controllers\RekapNilaiController', 'index'])->name('rekap_nilai.index');
    Route::get('rekap-nilai/export', ['App\Http\Controllers\RekapNilaiController', 'export'])->name('rekap_nilai.export');

        // Struktur Kurikulum
        Route::get('kurikulum', [KurikulumController::class, 'index'])->name('kurikulum.index');
        Route::post('kurikulum', [KurikulumController::class, 'store'])->name('kurikulum.store');
        Route::get('kurikulum-export', [KurikulumController::class, 'export'])->name('kurikulum.export');
        Route::post('kurikulum-import', [KurikulumController::class, 'import'])->name('kurikulum.import');
        Route::post('kurikulum-item', [KurikulumController::class, 'addItem'])->name('kurikulum.add-item');
        Route::put('kurikulum-item/{id}', [KurikulumController::class, 'updateItem'])->name('kurikulum.update-item');
        Route::delete('kurikulum-item/{id}', [KurikulumController::class, 'deleteItem'])->name('kurikulum.delete-item');
    
        // Jadwal KBM routes
        Route::get('jadwal-kbm', [JadwalKbmController::class, 'index'])->name('jadwal-kbm.index');
        Route::get('jadwal-kbm/create-by-kelas/{kelas}', [JadwalKbmController::class, 'createByKelas'])->name('jadwal-kbm.create-by-kelas');
        Route::get('jadwal-kbm/print/{kelas}', [JadwalKbmController::class, 'printByKelas'])->name('jadwal-kbm.print');
        Route::get('jadwal-kbm/export-pdf/{kelas}', [JadwalKbmController::class, 'exportPdfByKelas'])->name('jadwal-kbm.export-pdf');
        Route::get('jadwal-kbm/export-pdf-guru/{guru}', [JadwalKbmController::class, 'exportPdfByGuru'])->name('jadwal-kbm.export-pdf-guru');
        Route::get('jadwal-kbm/export-pdf-keseluruhan', [JadwalKbmController::class, 'exportPdfKeseluruhan'])->name('jadwal-kbm.export-pdf-keseluruhan');
        Route::get('jadwal-kbm/export-pdf-keseluruhan-mapel', [JadwalKbmController::class, 'exportPdfKeseluruhanMapel'])->name('jadwal-kbm.export-pdf-keseluruhan-mapel');
        Route::post('jadwal-kbm/store', [JadwalKbmController::class, 'store'])->name('jadwal-kbm.store');
        Route::get('jadwal-kbm/guru/{guru}', [JadwalKbmController::class, 'showByGuru'])->name('jadwal-kbm.show-by-guru');
        Route::get('jadwal-kbm/keseluruhan', [JadwalKbmController::class, 'showKeseluruhan'])->name('jadwal-kbm.keseluruhan');
        Route::put('jadwal-kbm/{id}', [JadwalKbmController::class, 'update'])->name('jadwal-kbm.update');
        Route::delete('jadwal-kbm/{id}', [JadwalKbmController::class, 'destroy'])->name('jadwal-kbm.destroy');
        Route::delete('jadwal-kbm-destroy-all', [JadwalKbmController::class, 'destroyAll'])->name('jadwal-kbm.destroy-all');
        Route::get('jadwal-kbm/get-jadwal-by-kelas/{kelas}', [JadwalKbmController::class, 'getJadwalByKelas'])->name('jadwal-kbm.get-by-kelas');
        Route::get('jadwal-kbm/get-guru-by-mapel', [JadwalKbmController::class, 'getGuruByMapel'])->name('jadwal-kbm.get-guru-by-mapel');
        Route::post('jadwal-kbm/check-konflik-guru', [JadwalKbmController::class, 'checkKonflikGuru'])->name('jadwal-kbm.check-konflik-guru');
        Route::put('jadwal-kbm/update-header', [JadwalKbmController::class, 'updateHeader'])->name('jadwal-kbm.update-header');
    
        // Pengaturan Jam Belajar routes (old jadwal_kbm)
        Route::get('pengaturan-jam', [JamBelajarController::class, 'index'])->name('jadwal_kbm.index');
        Route::get('pengaturan-jam/create', [JamBelajarController::class, 'create'])->name('jadwal_kbm.create');
        Route::post('pengaturan-jam', [JamBelajarController::class, 'store'])->name('jadwal_kbm.store');
        Route::get('pengaturan-jam/{jam_belajar}/edit', [JamBelajarController::class, 'edit'])->name('jadwal_kbm.edit');
        Route::put('pengaturan-jam/{jam_belajar}', [JamBelajarController::class, 'update'])->name('jadwal_kbm.update');
        Route::delete('pengaturan-jam/{jam_belajar}', [JamBelajarController::class, 'destroy'])->name('jadwal_kbm.destroy');
        Route::get('pengaturan-jam-export', [JamBelajarController::class, 'export'])->name('jadwal_kbm.export');
        Route::get('pengaturan-jam-template', [JamBelajarController::class, 'templateDownload'])->name('jadwal_kbm.template');
        Route::post('pengaturan-jam-import', [JamBelajarController::class, 'import'])->name('jadwal_kbm.import');
    
    // Keep old jam_belajar routes for backward compatibility
    Route::resource('jam_belajar', JamBelajarController::class)->except(['show']);
    Route::post('jam-belajar/insert-slot', [JamBelajarController::class, 'insertSlot'])->name('jam_belajar.insert_slot');
    Route::get('jam-belajar-export', [JamBelajarController::class, 'export'])->name('jam_belajar.export');
    Route::get('jam-belajar-template', [JamBelajarController::class, 'templateDownload'])->name('jam_belajar.template');
    Route::post('jam-belajar-import', [JamBelajarController::class, 'import'])->name('jam_belajar.import');
    Route::delete('jam-belajar-destroy-all', [JamBelajarController::class, 'destroyAll'])->name('jam_belajar.destroy_all');
    
    Route::get('agenda_kelas/preview', [AgendaKelasController::class, 'preview'])->name('agenda_kelas.preview');
    Route::resource('agenda_kelas', AgendaKelasController::class)->only(['index','create','store','show','edit','update','destroy']);
    
    Route::get('agenda_guru/export', [AgendaGuruController::class, 'export'])->name('agenda_guru.export');
    Route::post('agenda_guru/absensi', [AgendaGuruController::class, 'storeAbsensiGuru'])->name('agenda_guru.absensi.store');
    Route::resource('agenda_guru', AgendaGuruController::class)->only(['index','create','store','edit','update','destroy']);
    
    Route::resource('jenis_kegiatan', JenisKegiatanController::class)->middleware('auth');

    // Maintenance update routes
    Route::get('maintenance/update', [UpdateController::class, 'index'])->name('maintenance.update.index');
    Route::post('maintenance/update/run', [UpdateController::class, 'run'])->name('maintenance.update.run');
    
    // Data Master routes
    Route::resource('sekolah', 'App\Http\Controllers\SekolahController');
    Route::resource('kepala_sekolah', 'App\Http\Controllers\KepalaSekolahController');
    Route::resource('wakil_kepala_sekolah', 'App\Http\Controllers\WakilKepalaSekolahController');
    Route::resource('guru_bk', 'App\Http\Controllers\GuruBkController');
    Route::prefix('guru-bk')->name('guru_bk_layanan.')->group(function () {
        Route::get('kelas-binaan/{kelas}', [App\Http\Controllers\GuruBkLayananController::class, 'menu'])->name('menu');
        Route::get('kelas-binaan/{kelas}/layanan', [App\Http\Controllers\GuruBkLayananController::class, 'layanan'])->name('layanan');
        Route::post('kelas-binaan/{kelas}/layanan', [App\Http\Controllers\GuruBkLayananController::class, 'storeLayanan'])->name('layanan.store');
        Route::get('kelas-binaan/{kelas}/layanan/print', [App\Http\Controllers\GuruBkLayananController::class, 'printLayanan'])->name('layanan.print');
        Route::get('kelas-binaan/{kelas}/daftar-hadir', [App\Http\Controllers\GuruBkLayananController::class, 'daftarHadir'])->name('daftar_hadir');
        Route::get('kelas-binaan/{kelas}/daftar-hadir/print', [App\Http\Controllers\GuruBkLayananController::class, 'printDaftarHadir'])->name('daftar_hadir.print');
        Route::get('kelas-binaan/{kelas}/pembinaan', [App\Http\Controllers\GuruBkLayananController::class, 'pembinaan'])->name('pembinaan');
        Route::post('kelas-binaan/{kelas}/pembinaan', [App\Http\Controllers\GuruBkLayananController::class, 'storePembinaan'])->name('pembinaan.store');
        Route::get('kelas-binaan/{kelas}/pembinaan/rekap-absensi', [App\Http\Controllers\GuruBkLayananController::class, 'rekapAbsensiSiswa'])->name('pembinaan.rekap_absensi');
        Route::get('kelas-binaan/{kelas}/pembinaan/print', [App\Http\Controllers\GuruBkLayananController::class, 'printPembinaan'])->name('pembinaan.print');
        Route::get('kelas-binaan/{kelas}/tindak-lanjut', [App\Http\Controllers\GuruBkLayananController::class, 'tindakLanjut'])->name('tindak_lanjut');
    });
    Route::resource('guru_piket', 'App\Http\Controllers\GuruPiketController');
    Route::resource('pembina', 'App\Http\Controllers\PembinaController');
    Route::get('mata-pelajaran/guru', ['App\Http\Controllers\MataPelajaranController', 'guruIndex'])->name('mata_pelajaran.guru');
    Route::resource('mata_pelajaran', 'App\Http\Controllers\MataPelajaranController');
    Route::get('mata-pelajaran-export', ['App\Http\Controllers\MataPelajaranController', 'export'])->name('mata_pelajaran.export');
    Route::get('mata-pelajaran-template', ['App\Http\Controllers\MataPelajaranController', 'templateDownload'])->name('mata_pelajaran.template');
    Route::post('mata-pelajaran-import', ['App\Http\Controllers\MataPelajaranController', 'import'])->name('mata_pelajaran.import');
    
    // Tugas Guru routes
    Route::get('tugas-guru/get-kelas-by-tingkat', ['App\Http\Controllers\TugasGuruController', 'getKelasByTingkat'])->name('tugas_guru.get_kelas_by_tingkat');
    Route::get('tugas-guru/guru/{guru}', ['App\Http\Controllers\TugasGuruController', 'showByGuru'])->name('tugas_guru.show_by_guru');
    Route::resource('tugas_guru', 'App\Http\Controllers\TugasGuruController');
    
    // Rencana Pembelajaran routes - custom routes BEFORE resource to avoid conflicts
    Route::get('rencana_pembelajaran/import-form', 'App\Http\Controllers\RencanaPembelajaranController@importForm')->name('rencana_pembelajaran.import_form');
    Route::get('rencana_pembelajaran/template-download', 'App\Http\Controllers\RencanaPembelajaranController@templateDownload')->name('rencana_pembelajaran.template');
    Route::post('rencana_pembelajaran/bulk-delete', 'App\Http\Controllers\RencanaPembelajaranController@bulkDelete')->name('rencana_pembelajaran.bulkDelete');
    Route::post('rencana_pembelajaran/import-word', 'App\Http\Controllers\RencanaPembelajaranController@import')->name('rencana_pembelajaran.import_word');
    Route::get('rencana_pembelajaran/{rencanaPembelajaran}/export-word', 'App\Http\Controllers\RencanaPembelajaranController@export')->name('rencana_pembelajaran.export_word');
    Route::resource('rencana_pembelajaran', 'App\Http\Controllers\RencanaPembelajaranController');
    
    Route::resource('kegiatan', 'App\Http\Controllers\KegiatanController');
    
    // ASC Timetable routes
    Route::get('asc-timetable', ['App\Http\Controllers\AscTimetableController', 'index'])->name('asc_timetable.index');
    Route::post('asc-timetable/parse', ['App\Http\Controllers\AscTimetableController', 'parseXml'])->name('asc_timetable.parse');
    Route::post('asc-timetable/confirm-import', ['App\Http\Controllers\AscTimetableController', 'confirmImport'])->name('asc_timetable.confirm_import');
    Route::get('asc-timetable/download-template', ['App\Http\Controllers\AscTimetableController', 'downloadTemplate'])->name('asc_timetable.download_template');
    
    // User Management routes
    Route::resource('users', UserManagementController::class)->only(['index','create','store','show','edit','update','destroy']);
    Route::patch('users/{user}/activate', [UserManagementController::class, 'activate'])->name('users.activate');
    Route::get('users/export/excel', [UserManagementController::class, 'export'])->name('users.export');
    Route::get('users/template/download', [UserManagementController::class, 'templateDownload'])->name('users.template');
    Route::post('users/import/excel', [UserManagementController::class, 'import'])->name('users.import');
    Route::delete('users/bulk/delete', [UserManagementController::class, 'bulkDelete'])->name('users.bulkDelete');

    // Settings routes (tahun ajaran & semester) - Only Admin and Kepala Sekolah
    Route::middleware(['admin.or.kepala'])->group(function () {
        Route::get('/setting', [SettingController::class, 'index'])->name('tahun_ajaran.index');
        Route::get('/setting/tahun-ajaran', [SettingController::class, 'tahunAjaran'])->name('setting.tahun_ajaran');
        Route::get('/setting/tahun-ajaran/create', [SettingController::class, 'createTahunAjaran'])->name('setting.tahun_ajaran.create');
        Route::post('/setting/tahun-ajaran', [SettingController::class, 'storeTahunAjaran'])->name('setting.tahun_ajaran.store');
        Route::get('/setting/tahun-ajaran/{tahunAjaran}', [SettingController::class, 'showTahunAjaran'])->name('setting.tahun_ajaran.show');
        Route::get('/setting/tahun-ajaran/{tahunAjaran}/edit', [SettingController::class, 'editTahunAjaran'])->name('setting.tahun_ajaran.edit');
        Route::put('/setting/tahun-ajaran/{tahunAjaran}', [SettingController::class, 'updateTahunAjaran'])->name('setting.tahun_ajaran.update');
        Route::delete('/setting/tahun-ajaran/{tahunAjaran}', [SettingController::class, 'destroyTahunAjaran'])->name('setting.tahun_ajaran.destroy');
        Route::post('/setting/tahun-ajaran/{tahunAjaran}/activate', [SettingController::class, 'activateTahunAjaran'])->name('setting.tahun_ajaran.activate');
        Route::post('/setting/tahun-ajaran/{tahunAjaran}/deactivate', [SettingController::class, 'deactivateTahunAjaran'])->name('setting.tahun_ajaran.deactivate');

        Route::get('/setting/semester', [SettingController::class, 'semester'])->name('setting.semester');
        Route::get('/setting/semester/create', [SettingController::class, 'createSemester'])->name('setting.semester.create');
        Route::post('/setting/semester', [SettingController::class, 'storeSemester'])->name('setting.semester.store');
        Route::get('/setting/semester/{semester}', [SettingController::class, 'showSemester'])->name('setting.semester.show');
        Route::get('/setting/semester/{semester}/edit', [SettingController::class, 'editSemester'])->name('setting.semester.edit');
        Route::put('/setting/semester/{semester}', [SettingController::class, 'updateSemester'])->name('setting.semester.update');
        Route::delete('/setting/semester/{semester}', [SettingController::class, 'destroySemester'])->name('setting.semester.destroy');
        Route::post('/setting/semester/{semester}/activate', [SettingController::class, 'activateSemester'])->name('setting.semester.activate');
        Route::post('/setting/semester/{semester}/deactivate', [SettingController::class, 'deactivateSemester'])->name('setting.semester.deactivate');
        
        Route::get('/setting/header', [SettingController::class, 'header'])->name('setting.header');
        Route::put('/setting/header', [SettingController::class, 'updateHeader'])->name('setting.header.update');
    });
    
    // Profile routes
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::put('/profile/password', [ProfileController::class, 'updatePassword'])->name('profile.password');
});

// File serving route - serve storage files via controller instead of direct symlink
Route::get('/storage/{path}', [App\Http\Controllers\FileServeController::class, 'serve'])->where('path', '.*');

// Theme demo route (preview Material Dashboard assets)
Route::get('/theme-demo', function(){
    return view('material_home');
});

