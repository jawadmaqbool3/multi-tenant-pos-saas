<?php

use Illuminate\Support\Facades\Route;

Route::get('/clear-cache', function () {
    Illuminate\Support\Facades\Artisan::call('optimize:clear');

    return redirect()->back();
})->name('clear.cache');

Route::get('companies/create', 'CompanyController@create')->name('companies.create');
Route::post('companies', 'CompanyController@store')->name('companies.store');

Route::group(['middleware' => ['auth']], function () {
    Route::get('/', [
        'uses' => 'HomeController@index',
        'as' => 'dashboard',
    ]);

    Route::group(['middleware' => ['role:admin']], function () {
        Route::resource('companies', CompanyController::class)->except(['create', 'store']);
    });

    Route::group(['prefix' => 'settings'], function () {
        Route::get('general/{setting?}', 'SettingController@index')->name('general.settings')->middleware('permission:general setting');
        Route::post('general/update/{setting?}', 'SettingController@update')->name('general.settings.update')->middleware('permission:general setting');

        Route::get('public/dashboard/', 'DashboardSettingController@index')->name('public.dashboard.settings')->middleware('permission:public dashboard setting');
        Route::post('public/dashboard/update', 'DashboardSettingController@update')->name('public.dashboard.settings.update')->middleware('permission:public dashboard setting');

        Route::get('aside/', 'AsideSettingController@index')->name('aside.settings')->middleware('permission:sidebar menu setting');
        Route::post('aside/settings/update', 'AsideSettingController@update')->name('aside.settings.update')->middleware('permission:sidebar menu setting');
        Route::get('aside/settings/reset', 'AsideSettingController@reset')->name('aside.settings.reset')->middleware('permission:sidebar menu setting');
    });
});
