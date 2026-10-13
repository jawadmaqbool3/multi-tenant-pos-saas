<?php

namespace App\Http\Controllers;

use App\Services\HomeService;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    protected $service;

    public function __construct(HomeService $service)
    {
        $this->service = $service;
    }

    public function index(Request $request)
    {
        if ($request->ajax) {
            if ($request->charts) {
                $response = $this->service->getCharts();
                return response($response);
            } else {
                $products = $this->service->getProducts();
                return response([
                    'products' => $products,
                    'success' => true
                ]);
            }
        }

        if (auth()->user()->hasRole('admin')) {
            return view('dashboard.admin', [
                'stats' => $this->service->getSuperadminStats(),
            ]);
        }

        return view('dashboard.dashboard');
    }
}
