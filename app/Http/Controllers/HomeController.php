<?php

namespace App\Http\Controllers;

use App\Dashboard\DashboardService;
use App\Services\HomeService;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    protected $service;
    protected $dashboard;

    public function __construct(HomeService $service, DashboardService $dashboard)
    {
        $this->service = $service;
        $this->dashboard = $dashboard;
    }

    public function index(Request $request)
    {
        if ($request->ajax && !$request->widgets) {
            return response([
                'products' => $this->service->getProducts(),
                'success' => true,
            ]);
        }

        $widgets = $this->dashboard->widgetsFor(auth()->user());

        if (auth()->user()->hasRole('admin')) {
            return view('dashboard.admin', [
                'widgets' => $widgets,
            ]);
        }

        return view('dashboard.dashboard', [
            'widgets' => $widgets,
        ]);
    }

    /**
     * Batched widget payload for the current user's dashboard.
     */
    public function widgets(Request $request)
    {
        $widgets = $this->dashboard->widgetsFor(auth()->user());

        return response([
            'success' => true,
            'widgets' => $this->dashboard->payload($widgets),
        ]);
    }

    /**
     * Single widget payload (lazy load / manual refresh).
     */
    public function widget(Request $request, $key)
    {
        $data = $this->dashboard->payloadFor(auth()->user(), $key);

        if ($data === null) {
            return response([
                'success' => false,
                'message' => 'Widget not available.',
            ], 404);
        }

        return response([
            'success' => true,
            'key' => $key,
            'widget' => $data,
        ]);
    }
}
