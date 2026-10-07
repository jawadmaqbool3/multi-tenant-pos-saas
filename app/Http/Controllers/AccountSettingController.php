<?php

namespace App\Http\Controllers;

use App\Exceptions\CustomException;
use App\Models\Company;
use App\Services\AccountSettingService;
use Exception;
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
        return view('settings.accountSettings', compact('accountSetting', 'accounts'));
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
        } catch (CustomException $e) {
            DB::rollBack();
            return response([
                $e->getLevel() => true,
                'message' => $e->getMessage(),
            ]);
        } catch (Exception $e) {
            DB::rollBack();
            return response([
                'error' => true,
                'message' => 'Something went wrong',
                'console' => $e->getMessage(),
            ]);
        }
    }

    public function getAccount($short)
    {
        return $this->service->getAccount($short);
    }
}
