<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\JamBelajarController;
use App\Http\Controllers\AgendaKelasController;
use App\Http\Controllers\SettingController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;

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
});
