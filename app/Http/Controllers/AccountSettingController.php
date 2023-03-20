<?php

namespace App\Http\Controllers;

use App\Exceptions\CustomException;
use App\Models\AccountLevel1;
use App\Models\AccountLevel2;
use App\Models\AccountLevel3;
use App\Models\AccountLevel4;
use App\Models\AccountSetting;
use App\Models\Company;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AccountSettingController extends Controller
{


    public function index(Company $company = null)
    {
        if ($company) {
            $accountSetting = AccountSetting::where('company_id', $company->id)->get();
        } else {
            $accountSetting = AccountSetting::filter()->get();
        }
        $accounts = AccountLevel1::filter()->get();
        return view('settings.accountSettings', compact('accountSetting', 'accounts'));
    }


    public function update(Request $request, Company $company = null)
    {

        DB::beginTransaction();
        try {
            foreach ($request->settings as $key => $setting) {

                $_setting = AccountSetting::where('uid', $setting["setting"])->first();

                $level = $setting["level"];
                $account = null;
                switch ($level) {
                    case 'level1':
                        $account = AccountLevel1::where('uid', $setting['account'])->first();
                        break;
                    case 'level2':
                        $account = AccountLevel2::where('uid', $setting['account'])->first();
                        break;
                    case 'level3':
                        $account = AccountLevel3::where('uid', $setting['account'])->first();
                        break;
                    case 'level4':
                        $account = AccountLevel4::where('uid', $setting['account'])->first();
                        break;
                }
                AccountSetting::where('id', $_setting->id)->update([
                    "level" => $setting["level"],
                    "account_id" => $account ? $account->id : null
                ]);
            }


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
        $setting = AccountSetting::where('company_id', auth()->user()->company_id)->where('short', $short)->first();
        if (!$setting) {
            throw new CustomException("Account Setting (" . $short . ") not found.", "error");
        }
        $account = $setting->account;
        if (!$account) {
            throw new CustomException("Account Setting (" . $setting->name . ") not set.", "error");
        }
        return $account;
    }
}
