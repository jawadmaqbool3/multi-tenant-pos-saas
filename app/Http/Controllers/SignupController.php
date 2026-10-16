<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreSignupRequest;
use App\Services\SignupService;
use Illuminate\Support\Facades\DB;

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

        return view('signup.index');
    }

    /**
     * Provision the company + admin account from a self-signup, sign the
     * owner in and send them to the plans/pricing page.
     *
     * @param  \App\Http\Requests\StoreSignupRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreSignupRequest $request)
    {
        DB::beginTransaction();
        try {
            $company = $this->service->register($request);
            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            throw $e;
        }

        $user = $this->service->findUser($company->id, $request->email);

        if ($user) {
            auth()->login($user);
            $request->session()->regenerate();
        }

        return response([
            'success' => true,
            'message' => 'Your workspace is ready. Choose a plan to get started.',
            'redirect' => true,
            'url' => route('billing.pricing'),
        ]);
    }
}
