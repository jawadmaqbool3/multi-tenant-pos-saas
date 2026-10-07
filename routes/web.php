<?php

use Illuminate\Support\Facades\Route;

Route::get('/clear-cache', function () {
    Illuminate\Support\Facades\Artisan::call('optimize:clear');

    return redirect()->back();
})->name('clear.cache');

Route::group(['middleware' => ['auth']], function () {
    Route::get('/', [
        'uses' => 'HomeController@index',
        'as' => 'dashboard',
    ]);

    Route::resource('companies', CompanyController::class);

    Route::group(['prefix' => 'settings'], function () {
        Route::get('general/{setting?}', 'SettingController@index')->name('general.settings');
        Route::post('general/update/{setting?}', 'SettingController@update')->name('general.settings.update');

        Route::get('public/dashboard/', 'DashboardSettingController@index')->name('public.dashboard.settings');
        Route::post('public/dashboard/update', 'DashboardSettingController@update')->name('public.dashboard.settings.update');

        Route::get('aside/', 'AsideSettingController@index')->name('aside.settings');
        Route::post('aside/settings/update', 'AsideSettingController@update')->name('aside.settings.update');
        Route::get('aside/settings/reset', 'AsideSettingController@reset')->name('aside.settings.reset');
    });
});
