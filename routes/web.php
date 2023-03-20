<?php


/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;



Route::any('login', [
    'uses' => "LoginController@index",
    'as' => 'login'
]);
Route::post('logout', [
    'uses' => "LoginController@logout",
    'as' => 'logout'
]);

Route::get('/clear-cache', function () {
    $exitCode = Illuminate\Support\Facades\Artisan::call('optimize:clear');
    return redirect()->back();
})->name('clear.cache');



Route::group(['middleware' => ['auth']], function () {
    Route::get('/', [
        'uses' => "HomeController@index",
        'as' => "dashboard"
    ]);

    // Resources
    Route::resource('categories', CategoryController::class);
    Route::resource('products', ProductController::class);
    Route::resource('users', UserController::class);
    Route::resource('employees', EmployeeController::class);
    Route::resource('roles', RoleController::class);
    Route::resource('permissions', PermissionController::class);
    Route::resource('vouchers', VoucherController::class);
    Route::resource('voucher-types', VoucherTypeController::class);
    Route::resource('stocks', StockController::class);
    Route::resource('sales', SaleController::class);
    Route::resource('orders', OrderController::class);
    Route::resource('attendance', AttendanceHistoryController::class);
    Route::resource('promotions', PromotionController::class);
    Route::resource('suppliers', SupplierController::class);
    Route::resource('purchase-orders', PurchaseOrderController::class);
    Route::resource('companies', CompanyController::class);
    Route::resource('managers', ManagerController::class);


    //Groups
    Route::group(['prefix' => 'accounts'], function () {
        Route::get('/', 'AccountController@index')->name('accounts.index');
        Route::post('/store', 'AccountController@store')->name('accounts.store');
        Route::post('/update/{account}', 'AccountController@update')->name('accounts.update');
        Route::post('/delete/{account}', 'AccountController@delete')->name('accounts.delete');
    });



    Route::group(['prefix' => 'order'], function () {
        Route::post('cancel/{id}', "OrderController@cancel")->name('order.cancel');
        Route::post('mark/paid/{order}', "OrderController@markPaid")->name('order.mark.paid');
        Route::post('refund/{id}', "OrderController@refund")->name('order.refund');
    });

    Route::group(['prefix' => 'purchase-order'], function () {

        Route::post('refund/{purchaseOrder}', "PurchaseOrderController@refund")->name('purchase.order.refund');
    });
    Route::group(['prefix' => 'supplier'], function () {
        Route::post('pay/{supplier}', "SupplierController@pay")->name('supplier.pay');
        Route::get('ledger/{supplier}', "SupplierController@ledger")->name('supplier.ledger');
    });

    Route::group(['prefix' => 'settings'], function () {
        Route::get('general/{setting?}', "SettingController@index")->name('general.settings');
        Route::post('general/update/{setting?}', "SettingController@update")->name('general.settings.update');

        Route::get('public/dashboard/', "DashboardSettingController@index")->name('public.dashboard.settings');
        Route::post('public/dashboard/update', "DashboardSettingController@update")->name('public.dashboard.settings.update');

        Route::get('aside/', "AsideSettingController@index")->name('aside.settings');
        Route::post('aside/settings/update', "AsideSettingController@update")->name('aside.settings.update');
        Route::get('aside/settings/reset', "AsideSettingController@reset")->name('aside.settings.reset');

        Route::get('accounts/{company?}', "AccountSettingController@index")->name('account.settings');
        Route::post('accounts/update/{company?}', "AccountSettingController@update")->name('account.settings.update');
    });
});



//Print Routes
Route::group(['prefix' => 'print'], function () {
    Route::get('/sales-order/{order}', [
        'uses' => "OrderController@print",
        'as' => "sales.order.print"
    ]);
    Route::get('/purchase-order/{purchaseOrder}', [
        'uses' => "PurchaseOrderController@print",
        'as' => "purchase.order.print"
    ]);
    Route::get('/stock/{type}', [
        'uses' => "StockController@print",
        'as' => "print.stock"
    ]);
    Route::get('/sales/{from}/{to}', [
        'uses' => "SaleController@print",
        'as' => "print.sales"
    ]);
    Route::get('/barcode/{product}', [
        'uses' => "ProductController@print_barcode",
        'as' => "print.barcode"
    ]);
    Route::get('/print/{voucher}', [
        'uses' => "VoucherController@print",
        'as' => "print.voucher"
    ]);
});
