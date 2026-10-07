<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreAccountRequest;
use App\Http\Requests\UpdateAccountRequest;
use App\Services\ChatOfAccountService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AccountController extends Controller
{
    protected $service;

    public function __construct(ChatOfAccountService $service)
    {
        $this->service = $service;
    }

    public function index(Request $request)
    {
        $accounts = $this->service->getAccounts();
        if ($request->ajax()) {
            $tree = view('accounts.tree', compact('accounts'))->render();
            return response([
                'success' => true,
                'tree' => $tree
            ]);
        }
        return view('accounts.index', compact('accounts'));
    }

    public function store(StoreAccountRequest $request)
    {
        DB::beginTransaction();
        try {
            $this->service->store($request);

            DB::commit();
            return response([
                'success' => true,
                'message' => 'Account Added',
                'table_reload' => true,
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function update(UpdateAccountRequest $request, $account)
    {
        DB::beginTransaction();
        try {
            $this->service->update($request, $account);

            DB::commit();
            return response([
                'success' => true,
                'message' => 'Account Updated',
                'table_reload' => true,
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function delete(Request $request, $account)
    {
        DB::beginTransaction();
        try {
            $this->service->delete($request, $account);
            DB::commit();
            return response([
                'success' => true,
                'message' => 'Account Deleted',
                'table_reload' => true,
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();
            throw $e;
        }
    }
}
