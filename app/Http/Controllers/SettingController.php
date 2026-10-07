<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use App\Services\SettingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SettingController extends Controller
{
    protected $service;

    public function __construct(SettingService $service)
    {
        $this->service = $service;
    }

    public function index(Setting $setting = null)
    {
        $setting = $this->service->getSetting($setting);
        return view('settings.generalSettings', compact('setting'));
    }

    public function update(Request $request, Setting $setting = null)
    {
        $this->validate($request, [
            'name' => 'required',
            'email' => 'required',
            'address' => 'required',
        ]);
        DB::beginTransaction();
        try {
            $this->service->update($request, $setting);

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
}
