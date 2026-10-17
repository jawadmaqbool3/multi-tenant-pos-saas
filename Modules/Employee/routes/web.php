<?php

use Modules\Employee\Http\Controllers\AttendanceHistoryController;
use Modules\Employee\Http\Controllers\LoginController;
use Modules\Employee\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

Route::any('login', [
    'uses' => 'Modules\Employee\Http\Controllers\LoginController@index',
    'as' => 'login',
]);
Route::post('logout', [
    'uses' => 'Modules\Employee\Http\Controllers\LoginController@logout',
    'as' => 'logout',
]);

Route::group(['middleware' => ['auth']], function () {
    Route::resource('users', UserController::class)->middleware('permission:view user');

    // Self-service profile: available to every authenticated user regardless of RBAC.
    Route::get('profile', [UserController::class, 'profile'])->name('profile.edit');
    Route::put('profile', [UserController::class, 'updateProfile'])->name('profile.update');

    // Personal attendance history (employees). Declared before the resource so
    // `attendance/me` is not captured by `attendance/{attendance}`.
    Route::get('attendance/me', [AttendanceHistoryController::class, 'me'])
        ->name('attendance.me')->middleware('permission:view own attendance');

    Route::resource('attendance', AttendanceHistoryController::class)
        ->middleware('permission:view employee attendance report,view own attendance');
});
