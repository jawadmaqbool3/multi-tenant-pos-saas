<?php

namespace App\Http\Controllers;

use App\Core\Helper;
use App\Exceptions\CustomException;
use App\Models\EmployeePersonalInformation;
use App\Models\Role;
use App\Models\User;
use App\Models\UserRole;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class EmployeeController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    private $thumbWidth;
    private $thumbHeight;
    private $profileDirectory;
    private $role;
    public function __construct()
    {
        $this->role = Role::where('name', 'employee')->filter()->first();
        if ($this->role == false) {
            throw new Exception("Role doesn't exists");
        }
        $this->thumbWidth = 200;
        $this->thumbHeight = 200;
        $this->profileDirectory = 'assets/media/users/profile/';
    }
    public function index(Request $request)
    {
        if ($request->ajax()) {
            return User::filter()->whereHas('role', function ($query) {
                $query->where('id', $this->role->id);
            })->employeeTable();
        }
        return view('employees.index');
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */

    public function create()
    {
        return view('employees.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $this->validate($request, [
            'password' => 'required|same:co_password',
            'co_password' => 'required|min:8',
            'email' => 'required|unique:users,email',
            'name' => 'required|min:3',
            'status' => 'required',
        ]);
        DB::beginTransaction();
        try {

            $image = '';
            if ($request->file('image')) {
                $image = Helper::uploadfile([
                    'file' => $request->file('image'),
                    'path' => $this->profileDirectory,
                    'width' => $this->thumbWidth,
                    'height' => $this->thumbHeight,
                ]);
            }
            $password = Hash::make($request->password);
            $employee = User::create([
                'name' => $request->name,
                'company_id' => auth()->user()->company_id,
                'password' => $password,
                'email' => $request->email,
                'status' => $request->status,
                'image' => $image
            ]);
            $employee->refresh();
            UserRole::create([
                'role_id' => $this->role->id,
                'user_id' => $employee->id
            ]);
            EmployeePersonalInformation::create([
                'father_name' => $request->father_name,
                'contact' => $request->contact,
                'salary' => $request->salary,
                'designation' => $request->designation,
                'address' => $request->address,
                'employee_id' => $employee->id,
            ]);
            DB::commit();
            return response([
                'success' => true,
                'message' => 'Record Added',
                'redirect' => true,
                'url' => route('employees.index'),
            ]);
        } catch (CustomException $e) {
            DB::rollBack();
            return response([
                $e->getLevel() => true,
                'message' => $e->getMessage(),
                'console' => $e->getMessage(),
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
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit(User $employee)
    {
        return view('employees.edit', compact('employee'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, User $employee)
    {
        $this->validate($request, [
            'name' => 'required|min:3',
            'status' => 'required',
        ]);
        DB::beginTransaction();
        try {
            if ($request->password && $request->password != $request->co_password) {
                throw new CustomException('Password mismatch', 'error');
            }

            if (User::where('email', $request->email)->where('id', '!=', $employee->id)->exists()) {
                throw new CustomException('Email already has been taken', 'error');
            }
            $image = $employee->image;
            if ($request->file('image')) {
                if ($employee->image) {
                    Helper::deleteExcept([
                        'files' => [$employee->image],
                        'exceptions' => [],
                        'path' => $this->profileDirectory
                    ]);
                }
                $image = Helper::uploadfile([
                    'file' => $request->file('image'),
                    'path' => $this->profileDirectory,
                    'width' => $this->thumbWidth,
                    'height' => $this->thumbHeight,
                ]);
            }
            $password = $employee->password;
            if ($request->password) {
                $password = Hash::make($request->password);
            }
            User::where('id', $employee->id)->update([
                'name' => $request->name,
                'password' => $password,
                'email' => $request->email,
                'status' => $request->status,
                'image' => $image
            ]);
            UserRole::where('user_id', $employee->id)->delete();
            UserRole::create([
                'role_id' => $this->role->id,
                'user_id' => $employee->id
            ]);
            EmployeePersonalInformation::where('employee_id', $employee->id)->update([
                'father_name' => $request->father_name,
                'contact' => $request->contact,
                'salary' => $request->salary,
                'designation' => $request->designation,
                'address' => $request->address,
            ]);
            DB::commit();
            return response([
                'success' => true,
                'message' => 'Record Updated',
                'redirect' => true,
                'url' => route('employees.index'),
            ]);
        } catch (CustomException $e) {
            DB::rollBack();
            return response([
                $e->getLevel() => true,
                'message' => $e->getMessage(),
                'console' => $e->getMessage(),
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
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(User $employee)
    {
        Helper::deleteExcept([
            'files' => [$employee->image],
            'exceptions' => [],
            'path' => $this->profileDirectory
        ]);
        $employee->delete();
        return response([
            'success' => true,
            'message' => 'Record Deleted',
            'table_reload' => true,
        ]);
    }
}
