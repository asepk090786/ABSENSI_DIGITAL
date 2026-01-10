<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\JamBelajarController;
use App\Http\Controllers\AgendaKelasController;
use App\Http\Controllers\SettingController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\UserManagementController;
use App\Http\Controllers\ProfileController;

Route::get('/', function(){
    return redirect()->route('home');
});

Route::get('login',[AuthController::class,'showLogin'])->name('login');
Route::post('login',[AuthController::class,'login'])->name('login.post');
Route::post('logout',[AuthController::class,'logout'])->name('logout');

Route::get('/home', [DashboardController::class, 'index'])->middleware('auth')->name('home');

// Jam Belajar routes
Route::middleware(['auth'])->group(function(){
    // Guru routes with export/import
    Route::resource('guru','App\Http\Controllers\GuruController');
    Route::get('guru-export', ['App\Http\Controllers\GuruController', 'export'])->name('guru.export');
    Route::get('guru-template', ['App\Http\Controllers\GuruController', 'templateDownload'])->name('guru.template');
    Route::post('guru-import', ['App\Http\Controllers\GuruController', 'import'])->name('guru.import');
    
    Route::resource('siswa','App\Http\Controllers\SiswaController');
    Route::get('siswa-export', ['App\Http\Controllers\SiswaController', 'export'])->name('siswa.export');
    Route::get('siswa-template', ['App\Http\Controllers\SiswaController', 'templateDownload'])->name('siswa.template');
    Route::post('siswa-import', ['App\Http\Controllers\SiswaController', 'import'])->name('siswa.import');

    Route::resource('kelas','App\Http\Controllers\KelasController');
    Route::get('kelas-export', ['App\Http\Controllers\KelasController', 'export'])->name('kelas.export');
    Route::get('kelas-template', ['App\Http\Controllers\KelasController', 'templateDownload'])->name('kelas.template');
    Route::post('kelas-import', ['App\Http\Controllers\KelasController', 'import'])->name('kelas.import');
    Route::get('kelas/{kelas}/siswa-export', ['App\Http\Controllers\KelasController', 'studentExport'])->name('kelas.siswa.export');
    Route::get('kelas/{kelas}/siswa-template', ['App\Http\Controllers\KelasController', 'studentTemplate'])->name('kelas.siswa.template');
    Route::post('kelas/{kelas}/siswa-import', ['App\Http\Controllers\KelasController', 'studentImport'])->name('kelas.siswa.import');
    Route::post('kelas/{kelas}/siswa-add', ['App\Http\Controllers\KelasController', 'addStudent'])->name('kelas.siswa.add');
    Route::resource('absensi','App\Http\Controllers\AbsensiController');
    Route::resource('nilai','App\Http\Controllers\NilaiController')->only(['index']);
    
    Route::resource('jam_belajar', JamBelajarController::class)->except(['show']);
    Route::get('jam-belajar-export', [JamBelajarController::class, 'export'])->name('jam_belajar.export');
    Route::get('jam-belajar-template', [JamBelajarController::class, 'templateDownload'])->name('jam_belajar.template');
    Route::post('jam-belajar-import', [JamBelajarController::class, 'import'])->name('jam_belajar.import');
    Route::resource('agenda_kelas', AgendaKelasController::class)->only(['index','create','store']);
    
    // Data Master routes
    Route::resource('sekolah', 'App\Http\Controllers\SekolahController');
    Route::resource('kepala_sekolah', 'App\Http\Controllers\KepalaSekolahController');
    Route::resource('mata_pelajaran', 'App\Http\Controllers\MataPelajaranController');
    Route::get('mata-pelajaran-export', ['App\Http\Controllers\MataPelajaranController', 'export'])->name('mata_pelajaran.export');
    Route::get('mata-pelajaran-template', ['App\Http\Controllers\MataPelajaranController', 'templateDownload'])->name('mata_pelajaran.template');
    Route::post('mata-pelajaran-import', ['App\Http\Controllers\MataPelajaranController', 'import'])->name('mata_pelajaran.import');
    
    // User Management routes
    Route::resource('users', UserManagementController::class)->only(['index','create','store','show','destroy']);
    Route::patch('users/{user}/activate', [UserManagementController::class, 'activate'])->name('users.activate');
    Route::get('users/export/excel', [UserManagementController::class, 'export'])->name('users.export');
    Route::get('users/template/download', [UserManagementController::class, 'templateDownload'])->name('users.template');
    Route::post('users/import/excel', [UserManagementController::class, 'import'])->name('users.import');
    Route::delete('users/bulk/delete', [UserManagementController::class, 'bulkDelete'])->name('users.bulkDelete');

    // Settings routes (tahun ajaran & semester)
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
    
    // Profile routes
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::put('/profile/password', [ProfileController::class, 'updatePassword'])->name('profile.password');
});

// Theme demo route (preview Material Dashboard assets)
Route::get('/theme-demo', function(){
    return view('material_home');
});

