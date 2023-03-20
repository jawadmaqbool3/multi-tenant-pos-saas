<?php

namespace App\Http\Controllers;

use App\Models\Permission;
use App\Models\PermissionRole;
use App\Models\Role;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RoleController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {
            return Role::filter()->dataTable();
        }
        return view('roles.index');
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('roles.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        DB::beginTransaction();
        try {
            $request->validate([
                'status' => 'required',
                'name' => 'required'
            ]);


            $role = Role::create([
                'name' => $request->name,
                'status' => $request->status,
                'company_id' => auth()->user()->compny_id
            ]);


            $role->refresh();

            if (is_array($request->permissions)) {
                $permissions = Permission::whereIn('uid', $request->permissions)->get();
                foreach ($permissions as $permission) {
                    PermissionRole::create([
                        'permission_id' => $permission->id,
                        'role_id' => $role->id
                    ]);
                }
            }
            DB::commit();
            return response([
                'success' => true,
                'message' => 'New Record Added',
                'redirect' => true,
                'url' => route('roles.index'),
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

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Role  $role
     * @return \Illuminate\Http\Response
     */
    public function show(Role $role)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Role  $role
     * @return \Illuminate\Http\Response
     */
    public function edit(Role $role)
    {
        return view('roles.edit', compact('role'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Role  $role
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Role $role)
    {
        DB::beginTransaction();
        try {
            $request->validate([
                'status' => 'required',
                'name' => 'required'
            ]);


            Role::where('id', $role->id)->update([
                'name' => $request->name,
                'status' => $request->status
            ]);
            PermissionRole::where('role_id', $role->id)->delete();
            if (is_array($request->permissions)) {
                $permissions = Permission::whereIn('uid', $request->permissions)->get();
                foreach ($permissions as $permission) {
                    PermissionRole::create([
                        'permission_id' => $permission->id,
                        'role_id' => $role->id
                    ]);
                }
            }
            DB::commit();
            return response([
                'success' => true,
                'message' => 'Record Updated',
                'redirect' => true,
                'url' => route('roles.index'),
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

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Role  $role
     * @return \Illuminate\Http\Response
     */
    public function destroy(Role $role)
    {
        $role->delete();
        return response([
            'success' => true,
            'message' => 'Record Deleted',
            'table_reload' => true,
        ]);
    }
}
