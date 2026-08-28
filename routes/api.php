<?php

use App\Http\Controllers\Api\MobileApiController;
use App\Http\Controllers\Api\MasterDataController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::post('/auth/login', [MobileApiController::class, 'login']);
Route::post('/auth/logout', [MobileApiController::class, 'logout'])->middleware('auth:sanctum');
Route::get('/me', [MobileApiController::class, 'me'])->middleware('auth:sanctum');
Route::get('/dashboard', [MobileApiController::class, 'dashboard'])->middleware('auth:sanctum');
Route::get('/profile', [MobileApiController::class, 'profile'])->middleware('auth:sanctum');
Route::get('/attendance/summary', [MobileApiController::class, 'attendanceSummary'])->middleware('auth:sanctum');
Route::get('/classes', [MobileApiController::class, 'classes'])->middleware('auth:sanctum');
Route::get('/students', [MobileApiController::class, 'students'])->middleware('auth:sanctum');
Route::get('/teachers', [MobileApiController::class, 'teachers'])->middleware('auth:sanctum');
Route::get('/schedule', [MobileApiController::class, 'schedule'])->middleware('auth:sanctum');
Route::get('/master-data', [MasterDataController::class, 'index'])->middleware('api.key');

Route::middleware('auth:sanctum')->prefix('mobile')->group(function () {
    Route::get('/dashboard', [MobileApiController::class, 'mobileDashboard']);
    Route::get('/classes', [MobileApiController::class, 'mobileClasses']);
    Route::get('/classes/{id}', [MobileApiController::class, 'classDetail']);
    Route::get('/classes/{id}/students', [MobileApiController::class, 'classStudents']);
    Route::get('/students', [MobileApiController::class, 'mobileStudents']);
    Route::get('/students/{id}', [MobileApiController::class, 'studentDetail']);
    Route::get('/teachers', [MobileApiController::class, 'mobileTeachers']);
    Route::get('/teachers/{id}', [MobileApiController::class, 'teacherDetail']);
    Route::get('/attendance', [MobileApiController::class, 'mobileAttendance']);
    Route::get('/attendance/{id}', [MobileApiController::class, 'attendanceDetail']);
    Route::post('/attendance/{id}/students', [MobileApiController::class, 'bulkUpdateAttendanceStudents']);
    Route::post('/attendance', [MobileApiController::class, 'createAttendance']);
    Route::get('/attendance/rekap', [MobileApiController::class, 'attendanceRekap']);
    Route::get('/schedule', [MobileApiController::class, 'mobileSchedule']);
    Route::put('/profile', [MobileApiController::class, 'updateProfile']);
    Route::put('/profile/password', [MobileApiController::class, 'changePassword']);
    Route::get('/settings/tahun-ajaran', [MobileApiController::class, 'activeTahunAjaran']);
    Route::get('/settings/semester', [MobileApiController::class, 'activeSemester']);
});

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});
