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

Route::middleware('guest')->group(function () {
    Route::get('login',[AuthController::class,'showLogin'])->name('login');
    Route::post('login',[AuthController::class,'login'])->name('login.post');
});
// Note: public Help page removed temporarily. Admin editor remains under /help/admin
Route::post('logout',[AuthController::class,'logout'])->name('logout');

Route::get('/home', [DashboardController::class, 'index'])->middleware('auth')->name('home');

// Wali Kelas routes
Route::middleware(['auth'])->prefix('wali-kelas')->name('wali_kelas.')->group(function () {
    Route::get('/', [App\Http\Controllers\WaliKelasController::class, 'index'])->name('index');
    Route::get('/siswa', [App\Http\Controllers\WaliKelasController::class, 'siswa'])->name('siswa');
    Route::post('/siswa/{siswa}/jabatan', [App\Http\Controllers\WaliKelasController::class, 'updateJabatan'])->name('siswa.jabatan.update');
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
    Route::post('siswa/bulk-delete', ['App\Http\Controllers\SiswaController', 'bulkDelete'])->name('siswa.bulk-delete');
    Route::get('siswa-export', ['App\Http\Controllers\SiswaController', 'export'])->name('siswa.export');
    Route::get('siswa-template', ['App\Http\Controllers\SiswaController', 'templateDownload'])->name('siswa.template');
    Route::post('siswa-import', ['App\Http\Controllers\SiswaController', 'import'])->name('siswa.import');

    Route::resource('ekstrakurikuler', EkstrakurikulerController::class)->only(['index', 'create', 'store']);

    // Ekstrakurikuler Routes (full feature)
    Route::prefix('ekskul')->name('ekskul.')->group(function () {
        Route::get('/', [App\Http\Controllers\EkskulController::class, 'index'])->name('index');
        Route::get('/create', [App\Http\Controllers\EkskulController::class, 'create'])->name('create');
        Route::post('/', [App\Http\Controllers\EkskulController::class, 'store'])->name('store');
        Route::get('/{ekskul}/edit', [App\Http\Controllers\EkskulController::class, 'edit'])->name('edit');
        Route::put('/{ekskul}', [App\Http\Controllers\EkskulController::class, 'update'])->name('update');
        Route::delete('/{ekskul}', [App\Http\Controllers\EkskulController::class, 'destroy'])->name('destroy');
        Route::get('/{ekskul}/anggota', [App\Http\Controllers\EkskulController::class, 'manageAnggota'])->name('anggota');
        Route::post('/{ekskul}/anggota/status', [App\Http\Controllers\EkskulController::class, 'updateStatusAnggota'])->name('anggota.status');
        Route::post('/{ekskul}/anggota/bulk', [App\Http\Controllers\EkskulController::class, 'storeAnggotaBulk'])->name('anggota.bulk');
        Route::post('/{ekskul}/daftar', [App\Http\Controllers\EkskulController::class, 'daftar'])->name('daftar');
        Route::get('/{ekskul}/jadwal', [App\Http\Controllers\EkskulController::class, 'jadwal'])->name('jadwal');
        Route::post('/{ekskul}/jadwal', [App\Http\Controllers\EkskulController::class, 'storeJadwal'])->name('jadwal.store');
        Route::delete('/{ekskul}/jadwal/{jadwal}', [App\Http\Controllers\EkskulController::class, 'deleteJadwal'])->name('jadwal.delete');
        Route::get('/{ekskul}/agenda', [App\Http\Controllers\EkskulController::class, 'agenda'])->name('agenda');
        Route::post('/{ekskul}/agenda', [App\Http\Controllers\EkskulController::class, 'storeAgenda'])->name('agenda.store');
        Route::get('/{ekskul}/absensi/{agenda?}', [App\Http\Controllers\EkskulController::class, 'absensi'])->name('absensi');
        Route::post('/{ekskul}/absensi', [App\Http\Controllers\EkskulController::class, 'storeAbsensi'])->name('absensi.store');
        Route::get('/{ekskul}/absensi-pembina', [App\Http\Controllers\EkskulController::class, 'absensiPembina'])->name('absensi_pembina');
        Route::post('/{ekskul}/absensi-pembina', [App\Http\Controllers\EkskulController::class, 'storeAbsensiPembina'])->name('absensi_pembina.store');
        Route::get('/{ekskul}/bukti', [App\Http\Controllers\EkskulController::class, 'buktiKegiatan'])->name('bukti');
        Route::post('/{ekskul}/bukti', [App\Http\Controllers\EkskulController::class, 'storeBuktiKegiatan'])->name('bukti.store');
        Route::get('/{ekskul}/rekap', [App\Http\Controllers\EkskulController::class, 'rekap'])->name('rekap');
    });

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
    Route::post('absensi/verification/refresh', ['App\Http\Controllers\AbsensiController', 'refreshVerification'])->name('absensi.verification.refresh');
    Route::post('absensi/verification/save', ['App\Http\Controllers\AbsensiController', 'saveVerificationConfig'])->name('absensi.verification.save');
    Route::get('absensi/generate', ['App\Http\Controllers\AbsensiController', 'generateForm'])->name('absensi.generate.form');
    Route::post('absensi/generate', ['App\Http\Controllers\AbsensiController', 'generateStore'])->name('absensi.generate.store');
    Route::delete('absensi/delete-by-date', ['App\Http\Controllers\AbsensiController', 'destroyByDate'])->name('absensi.destroy-by-date');
    Route::get('absensi/laporan-siswa/print', ['App\Http\Controllers\AbsensiController', 'printLaporanSiswa'])->name('absensi.laporan-siswa.print');
    Route::get('absensi/laporan-siswa/export', ['App\Http\Controllers\AbsensiController', 'exportLaporanSiswa'])->name('absensi.laporan-siswa.export');
    Route::get('absensi/laporan-guru/print', ['App\Http\Controllers\AbsensiController', 'printLaporanGuru'])->name('absensi.laporan-guru.print');
    Route::get('absensi/guru/print', ['App\Http\Controllers\AbsensiController', 'printGuruRekap'])->name('absensi.guru.print');
    Route::get('piket-kbm/pelanggaran', ['App\Http\Controllers\PiketPelanggaranController', 'index'])->name('piket.pelanggaran.index');
    Route::post('piket-kbm/pelanggaran', ['App\Http\Controllers\PiketPelanggaranController', 'store'])->name('piket.pelanggaran.store');
    Route::get('absensi/bk-monitoring/export', ['App\Http\Controllers\AbsensiController', 'exportBkMonitoring'])->name('absensi.bk-monitoring.export');
    Route::post('absensi/{absensi}/laporan-siswa', ['App\Http\Controllers\AbsensiController', 'storeLaporanSiswa'])->name('absensi.laporan-siswa.store');
    // Pikret: allow update status for a student in an absensi record
    Route::post('absensi/{absensi}/siswa/{siswa}/status', ['App\Http\Controllers\AbsensiController', 'updateSiswaStatus'])->name('absensi.siswa.update_status');
    Route::post('absensi/verify-student', ['App\Http\Controllers\AbsensiController', 'verifyStudent'])->name('absensi.verify.student');
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
        Route::get('jadwal-kbm/kelas/{kelas}', [JadwalKbmController::class, 'showByKelas'])->name('jadwal-kbm.show-by-kelas');
        Route::get('jadwal-kbm/keseluruhan', [JadwalKbmController::class, 'showKeseluruhan'])->name('jadwal-kbm.keseluruhan');
        Route::get('jadwal-kbm/hari-ini', [JadwalKbmController::class, 'showToday'])->name('jadwal-kbm.today');
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
    Route::post('jam-belajar/copy-pattern', [JamBelajarController::class, 'copyPattern'])->name('jam_belajar.copy_pattern');
    Route::post('jam-belajar/save-as-pattern', [JamBelajarController::class, 'saveAsPattern'])->name('jam_belajar.save_as_pattern');
    Route::post('jam-belajar/apply-pattern', [JamBelajarController::class, 'applyPattern'])->name('jam_belajar.apply_pattern');
    Route::get('jam-belajar/patterns', [JamBelajarController::class, 'patterns'])->name('jam_belajar.patterns');
    Route::delete('jam-belajar/patterns/{pola}', [JamBelajarController::class, 'deletePattern'])->name('jam_belajar.delete_pattern');
    Route::get('jam-belajar-export', [JamBelajarController::class, 'export'])->name('jam_belajar.export');
    Route::get('jam-belajar-template', [JamBelajarController::class, 'templateDownload'])->name('jam_belajar.template');
    Route::post('jam-belajar-import', [JamBelajarController::class, 'import'])->name('jam_belajar.import');
    Route::delete('jam-belajar-destroy-all', [JamBelajarController::class, 'destroyAll'])->name('jam_belajar.destroy_all');
    
    Route::get('agenda_kelas/preview', [AgendaKelasController::class, 'preview'])->name('agenda_kelas.preview');
    Route::resource('agenda_kelas', AgendaKelasController::class)->only(['index','create','store','show','edit','update','destroy']);
    
    Route::get('agenda_guru/export', [AgendaGuruController::class, 'export'])->name('agenda_guru.export');
    Route::get('agenda_guru/absensi/export/pdf', [AgendaGuruController::class, 'exportAbsensiGuruPdf'])->name('agenda_guru.absensi.export.pdf');
    Route::get('agenda_guru/absensi/export/excel', [AgendaGuruController::class, 'exportAbsensiGuruExcel'])->name('agenda_guru.absensi.export.excel');
    Route::get('agenda_guru/absensi/export/range/{type}', [AgendaGuruController::class, 'exportAbsensiGuruRange'])->name('agenda_guru.absensi.export.range');
    Route::get('agenda_guru/absensi/export/month/{type}', [AgendaGuruController::class, 'exportAbsensiGuruMonth'])->name('agenda_guru.absensi.export.month');
    Route::post('agenda_guru/absensi', [AgendaGuruController::class, 'storeAbsensiGuru'])->name('agenda_guru.absensi.store');
    Route::resource('agenda_guru', AgendaGuruController::class)->only(['index','create','store','edit','update','destroy']);
    
    Route::resource('jenis_kegiatan', JenisKegiatanController::class)->middleware('auth');
    Route::get('jenis_pelanggaran/export', ['App\Http\Controllers\JenisPelanggaranController', 'export'])->name('jenis_pelanggaran.export');
    Route::get('jenis_pelanggaran/template', ['App\Http\Controllers\JenisPelanggaranController', 'template'])->name('jenis_pelanggaran.template');
    Route::post('jenis_pelanggaran/import', ['App\Http\Controllers\JenisPelanggaranController', 'import'])->name('jenis_pelanggaran.import');
    Route::resource('jenis_pelanggaran', 'App\Http\Controllers\JenisPelanggaranController')->except(['show']);

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
        Route::get('kelas-binaan/{kelas}/kartu-kendali', [App\Http\Controllers\GuruBkLayananController::class, 'kartuKendali'])->name('kartu_kendali');
        Route::post('kelas-binaan/{kelas}/kartu-kendali', [App\Http\Controllers\GuruBkLayananController::class, 'storeKartuKendali'])->name('kartu_kendali.store');
        Route::get('kelas-binaan/{kelas}/kartu-kendali/print', [App\Http\Controllers\GuruBkLayananController::class, 'printKartuKendali'])->name('kartu_kendali.print');
        Route::get('kelas-binaan/{kelas}/tindak-lanjut', [App\Http\Controllers\GuruBkLayananController::class, 'tindakLanjut'])->name('tindak_lanjut');
        Route::post('kelas-binaan/{kelas}/tindak-lanjut', [App\Http\Controllers\GuruBkLayananController::class, 'storeTindakLanjut'])->name('tindak_lanjut.store');
        Route::get('kelas-binaan/{kelas}/tindak-lanjut/{tindakLanjut}/print', [App\Http\Controllers\GuruBkLayananController::class, 'printTindakLanjut'])->name('tindak_lanjut.print');
        Route::get('kelas-binaan/{kelas}/tindak-lanjut/{tindakLanjut}/pdf', [App\Http\Controllers\GuruBkLayananController::class, 'pdfTindakLanjut'])->name('tindak_lanjut.pdf');
    });
    Route::post('guru_piket/generate', ['App\Http\Controllers\GuruPiketController', 'generate'])->name('guru_piket.generate');
    Route::post('guru_piket/bulk-destroy', ['App\Http\Controllers\GuruPiketController', 'bulkDestroy'])->name('guru_piket.bulk_destroy');
    Route::get('guru_piket/download', ['App\Http\Controllers\GuruPiketController', 'download'])->name('guru_piket.download');
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
    Route::get('tugas-guru/beban-kerja/export-excel', ['App\Http\Controllers\TugasGuruController', 'exportBebanKerjaExcel'])->name('tugas_guru.beban_kerja.export_excel');
    Route::get('tugas-guru/beban-kerja/export-pdf', ['App\Http\Controllers\TugasGuruController', 'exportBebanKerjaPdf'])->name('tugas_guru.beban_kerja.export_pdf');
    Route::get('tugas-guru/beban-kerja/print', ['App\Http\Controllers\TugasGuruController', 'printBebanKerja'])->name('tugas_guru.beban_kerja.print');
    Route::get('sk-tugas', [App\Http\Controllers\SkTugasController::class, 'index'])->name('sk_tugas.index');
    Route::get('sk-tugas/create', [App\Http\Controllers\SkTugasController::class, 'create'])->name('sk_tugas.create')->middleware('role:Admin');
    Route::post('sk-tugas', [App\Http\Controllers\SkTugasController::class, 'store'])->name('sk_tugas.store')->middleware('role:Admin');
    Route::get('sk-tugas/{sk_tugas}/edit', [App\Http\Controllers\SkTugasController::class, 'edit'])->name('sk_tugas.edit')->middleware('role:Admin');
    Route::put('sk-tugas/{sk_tugas}', [App\Http\Controllers\SkTugasController::class, 'update'])->name('sk_tugas.update')->middleware('role:Admin');
    Route::get('sk-tugas/{sk_tugas}/preview', [App\Http\Controllers\SkTugasController::class, 'preview'])->name('sk_tugas.preview');
    Route::post('sk-tugas/{sk_tugas}/toggle-visibility', [App\Http\Controllers\SkTugasController::class, 'toggleVisibility'])->name('sk_tugas.toggle_visibility')->middleware('role:Admin');
    Route::delete('sk-tugas/{sk_tugas}', [App\Http\Controllers\SkTugasController::class, 'destroy'])->name('sk_tugas.destroy')->middleware('role:Admin');
    Route::get('sk-tugas/{sk_tugas}/download', [App\Http\Controllers\SkTugasController::class, 'download'])->name('sk_tugas.download');

    Route::resource('tugas_guru', 'App\Http\Controllers\TugasGuruController');
    
    // Rencana Pembelajaran routes - custom routes BEFORE resource to avoid conflicts
    Route::get('rencana_pembelajaran/import-form', 'App\Http\Controllers\RencanaPembelajaranController@importForm')->name('rencana_pembelajaran.import_form');
    Route::get('rencana_pembelajaran/template-download', 'App\Http\Controllers\RencanaPembelajaranController@templateDownload')->name('rencana_pembelajaran.template');
    Route::post('rencana_pembelajaran/bulk-delete', 'App\Http\Controllers\RencanaPembelajaranController@bulkDelete')->name('rencana_pembelajaran.bulkDelete');
    Route::post('rencana_pembelajaran/import-word', 'App\Http\Controllers\RencanaPembelajaranController@import')->name('rencana_pembelajaran.import_word');
    Route::get('rencana_pembelajaran/{rencanaPembelajaran}/export-word', 'App\Http\Controllers\RencanaPembelajaranController@export')->name('rencana_pembelajaran.export_word');
    Route::resource('rencana_pembelajaran', 'App\Http\Controllers\RencanaPembelajaranController');
    
    // Materi Pembelajaran routes
    Route::resource('materi_pembelajaran', 'App\Http\Controllers\MateriPembelajaranController');

    // Siswa Pembelajaran routes
    Route::prefix('siswa/pembelajaran')->name('siswa.pembelajaran.')->middleware('auth')->group(function () {
        Route::get('materi', [App\Http\Controllers\SiswaPembelajaranController::class, 'index'])->name('materi');
        Route::get('materi/{id}', [App\Http\Controllers\SiswaPembelajaranController::class, 'show'])->name('show');
    });
    
    Route::resource('kegiatan', 'App\Http\Controllers\KegiatanController');

    // Pengembangan Diri module
    Route::get('pengembangan', [App\Http\Controllers\PengembanganController::class, 'index'])->name('pengembangan.index');
    Route::get('pengembangan/create', [App\Http\Controllers\PengembanganController::class, 'create'])->name('pengembangan.create');
    Route::post('pengembangan', [App\Http\Controllers\PengembanganController::class, 'store'])->name('pengembangan.store');
    // Pengembangan template sertifikat
    Route::get('pengembangan/templates', [App\Http\Controllers\PengembanganTemplateController::class, 'index'])->name('pengembangan.templates.index');
    Route::get('pengembangan/templates/create', [App\Http\Controllers\PengembanganTemplateController::class, 'create'])->name('pengembangan.templates.create');
    Route::post('pengembangan/templates', [App\Http\Controllers\PengembanganTemplateController::class, 'store'])->name('pengembangan.templates.store');
    Route::get('pengembangan/templates/{id}/edit', [App\Http\Controllers\PengembanganTemplateController::class, 'edit'])->name('pengembangan.templates.edit');
    Route::put('pengembangan/templates/{id}', [App\Http\Controllers\PengembanganTemplateController::class, 'update'])->name('pengembangan.templates.update');
    Route::delete('pengembangan/templates/{id}', [App\Http\Controllers\PengembanganTemplateController::class, 'destroy'])->name('pengembangan.templates.destroy');
    Route::get('pengembangan/sertifikat/my', [App\Http\Controllers\PengembanganController::class, 'myCertificates'])->name('pengembangan.my_certificates');
    Route::get('pengembangan/sertifikat/{id}/download', [App\Http\Controllers\PengembanganController::class, 'downloadCertificate'])->name('pengembangan.certificates.download');
    Route::get('pengembangan/verify/{code}', [App\Http\Controllers\PengembanganController::class, 'verify'])->name('pengembangan.verify');
    Route::get('pengembangan/{id}', [App\Http\Controllers\PengembanganController::class, 'show'])->name('pengembangan.show');
    Route::post('pengembangan/{id}/generate-certificates', [App\Http\Controllers\PengembanganController::class, 'generateCertificates'])->name('pengembangan.generate_certificates');
    Route::get('pengembangan/{id}/edit', [App\Http\Controllers\PengembanganController::class, 'edit'])->name('pengembangan.edit');
    Route::put('pengembangan/{id}', [App\Http\Controllers\PengembanganController::class, 'update'])->name('pengembangan.update');
    Route::delete('pengembangan/{id}', [App\Http\Controllers\PengembanganController::class, 'destroy'])->name('pengembangan.destroy');
    Route::get('pengembangan/{id}/preview-certificate', [App\Http\Controllers\PengembanganController::class, 'previewCertificate'])->name('pengembangan.preview_certificate');
    
    // ASC Timetable routes
    Route::get('asc-timetable', ['App\Http\Controllers\AscTimetableController', 'index'])->name('asc_timetable.index');
    Route::post('asc-timetable/parse', ['App\Http\Controllers\AscTimetableController', 'parseXml'])->name('asc_timetable.parse');
    Route::post('asc-timetable/confirm-import', ['App\Http\Controllers\AscTimetableController', 'confirmImport'])->name('asc_timetable.confirm_import');
    Route::get('asc-timetable/download-template', ['App\Http\Controllers\AscTimetableController', 'downloadTemplate'])->name('asc_timetable.download_template');
    
    // User Management routes
    Route::get('users/admin', [UserManagementController::class, 'index'])->name('users.admin')->defaults('role', 'Admin');
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
        Route::get('/setting/absensi', [SettingController::class, 'absensi'])->name('setting.absensi');
        Route::put('/setting/absensi', [SettingController::class, 'updateAbsensi'])->name('setting.absensi.update');
        Route::put('/setting/jadwal-visibility', [SettingController::class, 'updateJadwalVisibility'])->name('setting.jadwal_visibility.update');
        // Database backup settings and actions
        Route::get('/setting/backup', [SettingController::class, 'backupIndex'])->name('setting.backup');
        Route::post('/setting/backup/manual', [SettingController::class, 'backupManual'])->name('setting.backup.manual');
        Route::post('/setting/backup/settings', [SettingController::class, 'backupUpdateSettings'])->name('setting.backup.settings');
        Route::get('/setting/backup/download/{name}', [SettingController::class, 'backupDownload'])->name('setting.backup.download');
        Route::delete('/setting/backup/{name}', [SettingController::class, 'backupDelete'])->name('setting.backup.delete');
        // Admin Help page management (list/create/edit/delete)
        Route::get('/help/admin', [App\Http\Controllers\HelpController::class, 'adminIndex'])->name('help.admin.index');
        Route::get('/help/admin/create', [App\Http\Controllers\HelpController::class, 'create'])->name('help.admin.create');
        Route::post('/help/admin', [App\Http\Controllers\HelpController::class, 'storeAdmin'])->name('help.admin.store');
        Route::get('/help/admin/{slug}/edit', [App\Http\Controllers\HelpController::class, 'edit'])->name('help.admin.edit');
        Route::put('/help/admin/{slug}', [App\Http\Controllers\HelpController::class, 'update'])->name('help.admin.update');
        Route::delete('/help/admin/{slug}', [App\Http\Controllers\HelpController::class, 'destroy'])->name('help.admin.destroy');
    });
    
    // Profile routes
    Route::middleware('auth')->group(function () {
        Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
        Route::get('/panduan', [ProfileController::class, 'panduan'])->name('profile.panduan');
        Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');
        Route::put('/profile/password', [ProfileController::class, 'updatePassword'])->name('profile.updatePassword');
    });
});

// Public Help pages (no auth required)
Route::get('/help', [App\Http\Controllers\HelpController::class, 'publicIndex'])->name('help.public.index');
Route::get('/help/{slug}', [App\Http\Controllers\HelpController::class, 'show'])->name('help.show');

// File serving route - serve storage files via controller instead of direct symlink
Route::get('/storage/{path}', [App\Http\Controllers\FileServeController::class, 'serve'])->where('path', '.*');

// Theme demo route (preview Material Dashboard assets)
Route::get('/theme-demo', function(){
    return view('material_home');
});

