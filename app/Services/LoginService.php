<?php

namespace App\Services;

use App\Repositories\SettingRepository;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

class LoginService
{
    protected $setting;

    public function __construct(SettingRepository $setting)
    {
        $this->setting = $setting;
    }

    public function login($email, $password)
    {
        if (!Auth::attempt([
            'email' => $email,
            'password' => $password,
            'status' => 'active',
        ])) {
            return false;
        }
        $setting = $this->setting->query()->where('company_id', auth()->user()->company_id)->first();
        Session::put('setting', $setting);
        return true;
    }

    public function logout()
    {
        auth()->logout();
    }
}
