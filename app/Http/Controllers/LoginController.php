<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

class LoginController extends Controller
{
    public function index(Request $request)
    {
        if (auth()->check()) {
            return redirect()->route('dashboard');
        }
        if ($request->email && $request->password) {
            $response = null;
            if (Auth::attempt([
                'email' => $request->email,
                'password' => $request->password,
                'status' => 'active',
            ])) {
                $setting = Setting::where('company_id', auth()->user()->company_id)->first();
                Session::put('setting', $setting);
                if ($request->ajax()) {
                    $response = response(['success' => true, 'reload' => true, 'message' => "Login Success"]);
                } else {
                    $response = redirect()->back()->with('message', "Login Success");
                }
            } else {
                if ($request->ajax()) {
                    $response = response(['error' => true, 'reload' => false, 'message' => "Credentials Mismatch"]);
                } else {
                    $response = redirect()->back()->with('message', "Credentials Mismatch");
                }
            }
            return $response;
        }
        return view('auth.login');
    }

    public function logout(Request $request)
    {
        auth()->logout();
        return redirect()->route('login');
    }
}
