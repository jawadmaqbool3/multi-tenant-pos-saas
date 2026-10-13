<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateAsideSettingRequest;
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

    public function show($id = null)
    {
        //
    }

    public function edit($id = null)
    {
        //
    }

    public function update(UpdateAsideSettingRequest $request)
    {
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

    public function destroy($id = null)
    {
        //
    }

    public function reset()
    {
        $this->service->reset();
        return redirect()->route('clear.cache');
    }
}
