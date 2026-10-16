<?php

use Modules\RBAC\Http\Controllers\PermissionController;
use Modules\RBAC\Http\Controllers\RoleController;
use Illuminate\Support\Facades\Route;

Route::group(['middleware' => ['auth']], function () {
    Route::resource('roles', RoleController::class)->middleware('permission:view role');
    Route::resource('permissions', PermissionController::class)->only(['index'])->middleware('permission:view permission');
});
