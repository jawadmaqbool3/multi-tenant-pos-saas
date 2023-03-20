<?php

namespace App\Http\Controllers;

use App\Core\Helper;
use App\Exceptions\CustomException;
use App\Models\Company;
use App\Models\Permission;
use App\Models\PermissionRole;
use App\Models\Role;
use App\Models\Setting;
use App\Models\User;
use App\Models\UserRole;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class CompanyController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */

    private $thumbWidth;
    private $thumbHeight;
    private $profileDirectory;
    private $companyDirectory;
    public function __construct()
    {

        $this->thumbWidth = 200;
        $this->thumbHeight = 200;
        $this->profileDirectory = 'assets/media/users/profile/';
        $this->companyDirectory = 'assets/media/companies/';
    }


    public function index(Request $request)
    {
        if ($request->ajax()) {
            return Company::filter()->datatable();
        }
        return view('companies.index');
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('companies.create');
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
            'company_name' => 'required|min:3',
            'company_email' => 'required|unique:settings,email',
            'status' => 'required',
        ]);

        DB::beginTransaction();
        try {
            $userImage = $companyImage = null;
            if ($request->file('image')) {
                $userImage = Helper::uploadfile([
                    'file' => $request->file('image'),
                    'path' => $this->profileDirectory,
                    'width' => $this->thumbWidth,
                    'height' => $this->thumbHeight,
                ]);
                $settingController = new SettingController();
                $companyImage = $settingController->logoUpload($request->file('image'));
            }
            $company = Company::create([
                'created_by' => auth()->user()->id,
                'status' => $request->status
            ]);
            $company->refresh();
            $asideController = new AsideSettingController();
            $menuLinks = $asideController->menuLinks;
            Setting::create([
                'company_id' => $company->id,
                'name' => $request->company_name,
                'email' => $request->company_name,
                'address' => $request->company_address,
                'asidebar' => json_encode($menuLinks),
                'logo' => $companyImage
            ]);
            $admin_role = Role::create([
                'name' => $request->isManager ? 'manager' : 'admin',
                'company_id' => $company->id
            ]);
            $admin_role->refresh();
            $password = Hash::make($request->password);
            $user = User::create([
                'name' => $request->name,
                'password' => $password,
                'email' => $request->email,
                'company_id' =>  $company->id,
                'status' => $request->status,
                'image' => $userImage,
            ]);
            $user->refresh();
            UserRole::create([
                'role_id' => $admin_role->id,
                'user_id' => $user->id
            ]);
            if ($request->roles) {
                $roles = Role::whereIn('uid', $request->roles)->get();
                $permissions = Permission::whereIn('uid', $request->permissions)->get();
                foreach ($roles as $key => $role) {
                    Role::create([
                        'company_id' => $company->id,
                        'name' => $role->name
                    ]);
                }
            }

            if ($request->permissions) {
                $permissions = Permission::whereIn('uid', $request->permissions)->get();
                foreach ($permissions as $key => $permission) {
                    $permission = Permission::create([
                        'company_id' => $company->id,
                        'name' => $permission->name,
                        'module_id' => $permission->module_id
                    ]);
                    $permission->refresh();
                    PermissionRole::create([
                        'role_id' => $admin_role->id,
                        'permission_id' =>  $permission->id
                    ]);
                }
            }

            $defaultController = new DefaultController();
            $defaultController->initiateAccounts($company);
            $defaultController->initiateVouchers($company);
            $defaultController->initiateAccountSettings($company);
            DB::commit();

            return response([
                'success' => true,
                'message' => 'New Company Registered',
                'redirect' => true,
                'url' => route('companies.index'),
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
     * @param  \App\Models\Company  $company
     * @return \Illuminate\Http\Response
     */
    public function show(Company $company)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Company  $company
     * @return \Illuminate\Http\Response
     */
    public function edit(Company $company)
    {
        return view('companies.edit', compact('company'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Company  $company
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Company $company)
    {
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Company  $company
     * @return \Illuminate\Http\Response
     */
    public function destroy(Company $company)
    {
        $userController = new UserController();
        $productController = new ProductController();
        $categoryController = new CategoryController();
        $dashboardController = new DashboardSettingController();
        foreach ($company->products as $key => $product) {
            $productController->destroy($product);
        }
        foreach ($company->categories as $key => $category) {
            $categoryController->destroy($category);
        }
        foreach ($company->users as $key => $user) {
            $userController->destroy($user);
        }
        if ($company->dashboardSetting) {
            $dashboardController->destroy($company->dashboardSetting);
        }
        Helper::deleteExcept([
            'files' => [$company->setting->logo],
            'exceptions' => [],
            'path' => $this->companyDirectory
        ]);
        $company->delete();
        return response([
            'success' => true,
            'message' => 'Company Deleted',
            'table_reload' => true,
        ]);
    }
}
