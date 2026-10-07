<?php

namespace Modules\Employee\Http\Controllers;

use App\Http\Controllers\Controller;

use Modules\Employee\Services\LoginService;
use Illuminate\Http\Request;

class LoginController extends Controller
{
    protected $service;

    public function __construct(LoginService $service)
    {
        $this->service = $service;
    }

    public function index(Request $request)
    {
        if (auth()->check()) {
            return redirect()->route('dashboard');
        }
        if ($request->email && $request->password) {
            if ($this->service->login($request->email, $request->password)) {
                if ($request->ajax()) {
                    return response(['success' => true, 'reload' => true, 'message' => "Login Success"]);
                }
                return redirect()->back()->with('message', "Login Success");
            }
            if ($request->ajax()) {
                return response(['error' => true, 'reload' => false, 'message' => "Credentials Mismatch"]);
            }
            return redirect()->back()->with('message', "Credentials Mismatch");
        }
        return view('employee::auth.login');
    }

    public function logout(Request $request)
    {
        $this->service->logout();
        return redirect()->route('login');
    }
}
