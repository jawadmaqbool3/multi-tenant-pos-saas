<?php

namespace App\Http\Controllers;

use App\Models\AsideSetting;
use App\Services\AsideSettingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AsideSettingController extends Controller
{
    protected $service;
    public $menuLinks;

    public function __construct(AsideSettingService $service = null)
    {
        $this->service = $service ?? app(AsideSettingService::class);
        $this->menuLinks = $this->service->getMenuLinks();
    }

    public function index()
    {
        $settings = $this->service->getSettings()['settings'];
        $menuLinks = $this->service->getMenuLinks();
        return view('settings.asideSettings', compact('settings', 'menuLinks'));
    }

    public function create()
    {
        //
    }

    public function store(Request $request)
    {
        //
    }

    public function show(AsideSetting $asideSetting)
    {
        //
    }

    public function edit(AsideSetting $asideSetting)
    {
        //
    }

    public function update(Request $request)
    {
        $this->validate($request, [
            'categories' => 'required',
        ]);
        DB::beginTransaction();
        try {
            $this->service->update($request);
            DB::commit();
            return response([
                'success' => true,
                'message' => 'Settings Updated',
                'redirect' => true,
                'url' => route('clear.cache'),
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function destroy(AsideSetting $asideSetting)
    {
        //
    }

    public function reset()
    {
        $this->service->reset();
        return redirect()->route('clear.cache');
    }
}
