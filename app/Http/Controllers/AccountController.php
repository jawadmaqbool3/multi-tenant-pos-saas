<?php

namespace App\Http\Controllers;

use App\Exceptions\CustomException;
use App\Models\AccountLevel1;
use App\Models\AccountLevel2;
use App\Models\AccountLevel3;
use App\Models\AccountLevel4;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AccountController extends Controller
{
    public function index(Request $request)
    {
        $accounts = AccountLevel1::with('level2.level3.level4')->filter()->get();
        if ($request->ajax()) {
            $tree =  view('accounts.tree', compact('accounts'))->render();
            return response([
                'success' => true,
                'tree' => $tree
            ]);
        }
        return view('accounts.index', compact('accounts'));
    }

    public function store(Request $request)
    {
        $this->validate($request, [
            'name' => 'required',
            'account' => 'required',
            'status' => 'required',
        ]);
        DB::beginTransaction();
        try {

            switch ($request->level) {
                case 'level1':
                    $parentAccount = AccountLevel1::where('uid', $request->account)->first();
                    $code = AccountLevel2::where('parent_id', $parentAccount->id)->max('code');
                    if ($code) {
                        $code = str_pad($code + 1, 3, 0, STR_PAD_LEFT);
                    } else {
                        $code = $parentAccount->code + 1;
                    }
                    AccountLevel2::create([
                        'name' => $request->name,
                        'code' => $code,
                        'parent_id' => $parentAccount->id,
                        'company_id' => auth()->user()->company_id,
                        'status' => $request->status ?? 'active',
                    ]);
                    break;
                case 'level2':
                    $parentAccount = AccountLevel2::where('uid', $request->account)->first();
                    $code = AccountLevel3::where('parent_id', $parentAccount->id)->max('code');
                    if ($code) {
                        $code = str_replace($parentAccount->code . '-', '', $code);
                    } else {
                        $code = 0;
                    }
                    $code = str_pad($code + 1, 3, 0, STR_PAD_LEFT);
                    $code = $parentAccount->code . '-' . $code;
                    AccountLevel3::create([
                        'name' => $request->name,
                        'code' => $code,
                        'parent_id' => $parentAccount->id,
                        'company_id' => auth()->user()->company_id,
                        'status' => $request->status ?? 'active',
                    ]);
                    break;
                case 'level3':
                    $parentAccount = AccountLevel3::where('uid', $request->account)->first();
                    $code = AccountLevel4::where('parent_id', $parentAccount->id)->max('code');
                    if ($code) {
                        $code = str_replace($parentAccount->code . '-', '', $code);
                    } else {
                        $code = 0;
                    }
                    $code = str_pad($code + 1, 3, 0, STR_PAD_LEFT);
                    $code = $parentAccount->code . '-' . $code;
                    AccountLevel4::create([
                        'name' => $request->name,
                        'code' => $code,
                        'parent_id' => $parentAccount->id,
                        'company_id' => auth()->user()->company_id,
                        'status' => $request->status ?? 'active',
                    ]);
                    break;
                default:
                    $code = AccountLevel1::filter()->max('code');
                    if ($code) {
                        $code = str_pad($code + 1, 3, 0, STR_PAD_LEFT);
                    } else {
                        $code = 0;
                    }
                    $endCode = $code + 99;
                    $code = str_pad($code, 3, 0, STR_PAD_LEFT);
                    $endCode = str_pad($endCode, 3, 0, STR_PAD_LEFT);
                    AccountLevel2::create([
                        'name' => $request->name,
                        'start_code' => $code,
                        'end_code' => $endCode,
                        'company_id' => auth()->user()->company_id,
                        'status' => $request->status ?? 'active',
                    ]);
            }

            DB::commit();
            return response([
                'success' => true,
                'message' => 'Account Added',
                'table_reload' => true,
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

    public function update(Request $request, $account)
    {
        $this->validate($request, [
            'name' => 'required',
            'status' => 'required',
            'level' => 'required',
        ]);
        DB::beginTransaction();
        try {
            $model  = null;
            switch ($request->level) {
                case 'level1':
                    $model = new AccountLevel1();
                    break;
                case 'level2':
                    $model = new AccountLevel2();
                    break;
                case 'level3':
                    $model = new AccountLevel3();
                    break;
                case 'level4':
                    $model = new AccountLevel4();
                    break;
            }

            $model->where('uid', $account)->update([
                'name' => $request->name,
                'status' => $request->status ?? 'active',
            ]);

            DB::commit();
            return response([
                'success' => true,
                'message' => 'Account Updated',
                'table_reload' => true,
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

    public function delete(Request $request, $account)
    {
        DB::beginTransaction();
        try {

            $model  = null;
            switch ($request->level) {
                case 'level1':
                    $model = new AccountLevel1();
                    break;
                case 'level2':
                    $model = new AccountLevel2();
                    break;
                case 'level3':
                    $model = new AccountLevel3();
                    break;
                case 'level4':
                    $model = new AccountLevel4();
                    break;
            }
            $model->where('uid', $account)->delete();
            DB::commit();
            return response([
                'success' => true,
                'message' => 'Account Deleted',
                'table_reload' => true,
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
}
