<?php

namespace App\Http\Controllers;

use App\Exceptions\CustomException;
use App\Models\DashboardSetting;
use App\Services\DashboardSettingService;
use Exception;
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
    public function show(DashboardSetting $dashboardSetting)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\DashboardSetting  $dashboardSetting
     * @return \Illuminate\Http\Response
     */
    public function edit(DashboardSetting $dashboardSetting)
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
     * @param  \App\Models\DashboardSetting  $dashboardSetting
     * @return \Illuminate\Http\Response
     */
    public function destroy(DashboardSetting $dashboardSetting)
    {
        $this->service->destroy($dashboardSetting);
    }
}
