<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\UserService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class UserController extends Controller
{
    protected $service;

    public function __construct(UserService $service = null)
    {
        $this->service = $service ?? app(UserService::class);
    }

    public function index(Request $request)
    {
        if ($request->ajax()) {
            return $this->service->getUsers();
        }
        return view('users.index');
    }

    public function create()
    {
        $roles = $this->service->getRoles();
        return view('users.create', compact('roles'));
    }

    public function store(Request $request)
    {
        $this->validate($request, [
            'password' => 'required|same:co_password',
            'co_password' => 'required|min:8',
            'email' => 'required|unique:users,email',
            'name' => 'required|min:3',
            'role' => 'required',
            'status' => 'required',
        ]);

        DB::beginTransaction();
        try {
            $this->service->store($request);

            DB::commit();
            return response([
                'success' => true,
                'message' => 'Record Added',
                'redirect' => true,
                'url' => route('users.index'),
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function show($id)
    {
        //
    }

    public function edit(User $user)
    {
        $roles = $this->service->getRoles();
        return view('users.edit', compact('roles', 'user'));
    }

    public function update(Request $request, User $user)
    {
        $this->validate($request, [
            'name' => 'required|min:3',
            'role' => 'required',
            'status' => 'required',
        ]);
        DB::beginTransaction();
        try {
            $this->service->update($request, $user);

            DB::commit();
            return response([
                'success' => true,
                'message' => 'Record Updated',
                'redirect' => true,
                'url' => route('users.index'),
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function destroy(User $user)
    {
        $this->service->delete($user);
        return response([
            'success' => true,
            'message' => 'Record Deleted',
            'table_reload' => true,
        ]);
    }
}
