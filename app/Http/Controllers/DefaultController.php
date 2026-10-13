<?php

namespace App\Http\Controllers;

use App\Services\DefaultService;

class DefaultController extends Controller
{
    protected $service;

    public function __construct(DefaultService $service = null)
    {
        $this->service = $service ?: app(DefaultService::class);
    }

    public function initiateAccounts($company = null)
    {
        return $this->service->initiateAccounts($company);
    }

    public function initiateVouchers($company = null)
    {
        return $this->service->initiateVouchers($company);
    }

    public function initiateAccountSettings($company = null)
    {
        return $this->service->initiateAccountSettings($company);
    }
}
