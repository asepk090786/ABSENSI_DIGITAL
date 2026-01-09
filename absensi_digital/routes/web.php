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

// Example resource routes (implement controllers in app/Http/Controllers)
Route::resource('guru','App\Http\Controllers\GuruController');
Route::resource('siswa','App\Http\Controllers\SiswaController');
Route::resource('absensi','App\Http\Controllers\AbsensiController');
Route::resource('nilai','App\Http\Controllers\NilaiController')->only(['index']);

// Jam Belajar routes
Route::middleware(['auth'])->group(function(){
    Route::resource('jam_belajar', JamBelajarController::class)->except(['show']);
    Route::resource('agenda_kelas', AgendaKelasController::class)->only(['index','create','store']);
    
    // Data Master routes
    Route::resource('sekolah', 'App\Http\Controllers\SekolahController');
    Route::resource('kepala_sekolah', 'App\Http\Controllers\KepalaSekolahController');
    
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
    Route::post('/setting/tahun-ajaran/{tahunAjaran}/activate', [SettingController::class, 'activateTahunAjaran'])->name('setting.tahun_ajaran.activate');

    Route::get('/setting/semester', [SettingController::class, 'semester'])->name('setting.semester');
    Route::get('/setting/semester/create', [SettingController::class, 'createSemester'])->name('setting.semester.create');
    Route::post('/setting/semester', [SettingController::class, 'storeSemester'])->name('setting.semester.store');
    Route::post('/setting/semester/{semester}/activate', [SettingController::class, 'activateSemester'])->name('setting.semester.activate');
    
    // Profile routes
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::put('/profile/password', [ProfileController::class, 'updatePassword'])->name('profile.password');
});

// Theme demo route (preview Material Dashboard assets)
Route::get('/theme-demo', function(){
    return view('material_home');
});

