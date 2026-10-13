<?php

namespace App\Http\Controllers;

use App\Services\DashboardSettingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardSettingController extends Controller
{
    protected $service;

    public function __construct(DashboardSettingService $service)
    {
        $this->service = $service;
    }

    public function index()
    {
        return view('settings.publicDashboardSettings', $this->service->getIndexData());
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
     * @param  \App\Models\DashboardSetting  $dashboardSetting
     * @return \Illuminate\Http\Response
     */
    public function show($id = null)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function edit($id = null)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\DashboardSetting  $dashboardSetting
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request)
    {

        try {
            $this->service->update($request);
            DB::commit();
            return response([
                'success' => true,
                'reload' => true,
                'message' => 'Settings Saved',
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\DashboardSetting  $dashboardSetting
     * @return \Illuminate\Http\Response
     */
    public function destroy($dashboardSetting = null)
    {
        $this->service->destroy($dashboardSetting);
    }
}
