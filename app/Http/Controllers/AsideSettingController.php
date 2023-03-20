<?php

namespace App\Http\Controllers;

use App\Exceptions\CustomException;
use App\Models\AsideSetting;
use App\Models\Setting;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AsideSettingController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */


    public $menuLinks;
    public function index()
    {
        $menuLinks = $this->menuLinks;
        $settings = json_decode(Setting::select('asidebar')->filter()->first()->asidebar);
        return view('settings.asideSettings', compact('settings', 'menuLinks'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\AsideSetting  $asideSetting
     * @return \Illuminate\Http\Response
     */
    public function show(AsideSetting $asideSetting)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\AsideSetting  $asideSetting
     * @return \Illuminate\Http\Response
     */
    public function edit(AsideSetting $asideSetting)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\AsideSetting  $asideSetting
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request)
    {
        $this->validate($request, [
            'categories' => 'required',
        ]);
        DB::beginTransaction();
        try {
            $setting = Setting::filter()->first();
            Setting::where('id', $setting->id)->update([
                'asidebar' => $request->categories,
            ]);
            DB::commit();
            return response([
                'success' => true,
                'message' => 'Settings Updated',
                'redirect' => true,
                'url' => route('clear.cache'),
            ]);
        } catch (CustomException $e) {
            DB::rollBack();
            return response([
                $e->getLevel() => true,
                'message' => $e->getMessage(),
            ]);
        } catch (Exception $e) {
            DB::rollBack();
            return response([
                'error' => true,
                'message' => 'Something went wrong',
                'console' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\AsideSetting  $asideSetting
     * @return \Illuminate\Http\Response
     */
    public function destroy(AsideSetting $asideSetting)
    {
        //
    }

    public function reset()
    {
        $menu = $this->menuLinks;

        $setting = Setting::filter()->first();
        $setting = Setting::where('id', $setting->id)->update([
            'asidebar' => json_encode($menu)
        ]);
        return redirect()->route('clear.cache');
    }



    public function __construct()
    {
        $this->menuLinks = [

            [
                'name' => 'SaaS',
                'icon' => 'bi bi-share',
                'links' => [
                    [
                        'title' => 'Companies',
                        'permission' => 'view company',
                        'link' => route('companies.index'),
                    ],
                    [
                        'title' => 'Managers',
                        'permission' => 'view manager',
                        'link' => route('managers.index'),
                    ],
                ],
            ],
            [
                'name' => 'Bank',
                'icon' => 'bi bi-bank',
                'links' => [
                    [
                        'title' => 'Accounts',
                        'permission' => 'view account',
                        'link' => route('accounts.index'),
                    ]
                ],
            ],
            [
                'name' => 'Inventory',
                'icon' => 'bi bi-shop',
                'links' => [
                    [
                        'title' => 'Categories',
                        'permission' => 'view category',
                        'link' => route('categories.index'),
                    ],
                    [
                        'title' => 'Products',
                        'permission' => 'view product',
                        'link' => route('products.index'),
                    ],
                ],
            ],
            [
                'name' => 'Orders',
                'icon' => 'bi bi-cart',
                'links' => [
                    [
                        'title' => 'Sales Orders',
                        'permission' => 'view order',
                        'link' => route('orders.index'),
                    ],
                    [
                        'title' => 'Purchase Orders',
                        'permission' => 'view purchase orders',
                        'link' => route('purchase-orders.index'),
                    ],
                ],
            ],
            [
                'name' => 'Promotions',
                'icon' => 'bi bi-award-fill',
                'links' => [
                    [
                        'title' => 'Promotions',
                        'permission' => 'view promotion',
                        'link' => route('promotions.index'),
                    ],
                ],
            ],
            [
                'name' => 'Reports',
                'icon' => 'bi bi-file-earmark-bar-graph',
                'links' => [
                    [
                        'title' => 'Stocks',
                        'permission' => 'view stocks',
                        'link' => route('stocks.index'),
                    ],
                    [
                        'title' => 'Sales',
                        'permission' => 'view sales',
                        'link' => route('sales.index'),
                    ],
                    [
                        'title' => 'Employee Attendance',
                        'permission' => 'view employee attendance report',
                        'link' => route('attendance.index'),
                    ],
                ],
            ],
            [
                'name' => 'HR',
                'icon' => 'bi bi-person-bounding-box',
                'links' => [
                    [
                        'title' => 'Employees',
                        'permission' => 'view employee',
                        'link' => route('employees.index'),
                    ],
                    [
                        'title' => 'Suppliers',
                        'permission' => 'view supplier',
                        'link' => route('suppliers.index'),
                    ],
                ],
            ],
            [
                'name' => 'Users',
                'icon' => 'bi bi-people',
                'links' => [
                    [
                        'title' => 'User',
                        'permission' => 'view user',
                        'link' => route('users.index'),
                    ],
                ],
            ],
            [
                'name' => 'Access Control',
                'icon' => 'bi bi-lock',
                'links' => [
                    [
                        'title' => 'Roles',
                        'permission' => 'view role',
                        'link' => route('roles.index'),
                    ],
                    [
                        'title' => 'Permissions',
                        'permission' => 'view permission',
                        'link' => route('permissions.index'),
                    ],
                ],
            ],
            [
                'name' => 'Settings',
                'icon' => 'bi bi-gear',
                'links' => [
                    [
                        'title' => 'General',
                        'permission' => 'general setting',
                        'link' => route('general.settings'),
                    ],
                    [
                        'title' => 'Public Dashboard',
                        'permission' => 'public dashboard setting',
                        'link' => route('public.dashboard.settings'),
                    ],
                    [
                        'title' => 'Sidebar',
                        'permission' => 'sidebar menu setting',
                        'link' => route('aside.settings'),
                    ],
                    [
                        'title' => 'Accounts',
                        'permission' => 'account settings',
                        'link' => route('account.settings'),
                    ],
                ],
            ],
        ];
    }
}
