<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreSignupRequest;
use App\Services\SignupService;
use Illuminate\Support\Facades\DB;
use Modules\Billing\Models\Plan;

class SignupController extends Controller
{
    protected $service;

    public function __construct(SignupService $service)
    {
        $this->service = $service;
    }

    /**
     * Show the public multi-step signup wizard.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        if (auth()->check()) {
            return redirect()->route('dashboard');
        }

        $plans = Plan::query()
            ->where('status', 'active')
            ->where('is_public', true)
            ->whereHas('prices', function ($query) {
                $query->where('active', true)->where('unit_amount', '>', 0);
            })
            ->with(['prices' => function ($query) {
                $query->where('active', true)->orderBy('unit_amount');
            }])
            ->orderBy('sort')
            ->orderBy('name')
            ->get();

        return view('signup.index', compact('plans'));
    }

    /**
     * Provision the company + admin account from a self-signup.
     *
     * @param  \App\Http\Requests\StoreSignupRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreSignupRequest $request)
    {
        DB::beginTransaction();
        try {
            $this->service->register($request);
            DB::commit();

            return response([
                'success' => true,
                'message' => 'Your company has been registered. Please sign in to continue.',
                'redirect' => true,
                'url' => route('login'),
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();
            throw $e;
        }
    }
}
