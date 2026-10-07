<?php

namespace Modules\ChartOfAccounts\Http\Controllers;

use App\Http\Controllers\Controller;

use App\Models\Company;
use Modules\ChartOfAccounts\Services\AccountSettingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AccountSettingController extends Controller
{
    protected $service;

    public function __construct(AccountSettingService $service)
    {
        $this->service = $service;
    }

    public function index(Company $company = null)
    {
        $accountSetting = $this->service->getAccountSettings($company);
        $accounts = $this->service->getAccounts();
        return view('chartofaccounts::settings.accountSettings', compact('accountSetting', 'accounts'));
    }

    public function update(Request $request, Company $company = null)
    {
        DB::beginTransaction();
        try {
            $this->service->update($request);

            DB::commit();
            return response([
                'success' => true,
                'message' => 'Settings Updated',
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function getAccount($short)
    {
        return $this->service->getAccount($short);
    }
}
