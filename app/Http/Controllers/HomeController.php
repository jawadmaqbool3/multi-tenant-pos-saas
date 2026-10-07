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
        return view('dashboard.dashboard');
    }
}
