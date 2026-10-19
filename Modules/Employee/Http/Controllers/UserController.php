<?php

namespace Modules\Employee\Http\Controllers;

use App\Http\Controllers\Controller;

use Modules\Employee\Models\User;
use Modules\Employee\Http\Requests\StoreUserRequest;
use Modules\Employee\Http\Requests\UpdateUserRequest;
use Modules\Employee\Http\Requests\UpdateProfileRequest;
use Modules\Employee\Services\UserService;
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
        $filter = $request->query('filter');

        if ($request->ajax()) {
            return $this->service->getUsers($filter);
        }
        return view('employee::users.index', compact('filter'));
    }

    public function create()
    {
        $roles = $this->service->getRoles();
        return view('employee::users.create', compact('roles'));
    }

    public function store(StoreUserRequest $request)
    {

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

    /**
     * Self-service profile, available to every authenticated user regardless
     * of RBAC permissions.
     */
    public function profile()
    {
        $user = auth()->user();
        return view('employee::users.profile', compact('user'));
    }

    public function updateProfile(UpdateProfileRequest $request)
    {
        DB::beginTransaction();
        try {
            $this->service->updateProfile($request, auth()->user());

            DB::commit();
            return response([
                'success' => true,
                'message' => 'Profile Updated',
                'reload' => true,
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function edit(User $user)
    {
        $roles = $this->service->getRoles();
        return view('employee::users.edit', compact('roles', 'user'));
    }

    public function update(UpdateUserRequest $request, User $user)
    {
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
