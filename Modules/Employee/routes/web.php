<?php

use Modules\Employee\Http\Controllers\AttendanceHistoryController;
use Modules\Employee\Http\Controllers\EmployeeController;
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
    Route::resource('employees', EmployeeController::class)->middleware('permission:view employee');
    Route::resource('attendance', AttendanceHistoryController::class)->middleware('permission:view employee attendance report');
});
