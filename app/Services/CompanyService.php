<?php

namespace App\Services;

use App\Core\Helper;
use App\Services\DefaultService;
use Modules\Employee\Services\UserService;
use Modules\Inventory\Services\ProductService;
use Modules\Inventory\Services\CategoryService;
use App\Services\AsideSettingService;
use App\Repositories\CompanyRepository;
use Modules\RBAC\Repositories\PermissionRepository;
use Modules\RBAC\Repositories\PermissionRoleRepository;
use Modules\RBAC\Repositories\RoleRepository;
use App\Repositories\SettingRepository;
use Modules\Employee\Repositories\UserRepository;
use Modules\RBAC\Repositories\UserRoleRepository;
use Illuminate\Support\Facades\Hash;

class CompanyService
{
    protected $company;
    protected $setting;
    protected $role;
    protected $user;
    protected $userRole;
    protected $permission;
    protected $permissionRole;
    protected $settingService;
    protected $categoryService;
    protected $dashboardSettingService;

    private $thumbWidth;
    private $thumbHeight;
    private $profileDirectory;
    private $companyDirectory;

    public function __construct(
        CompanyRepository $company,
        SettingRepository $setting,
        RoleRepository $role,
        UserRepository $user,
        UserRoleRepository $userRole,
        PermissionRepository $permission,
        PermissionRoleRepository $permissionRole,
        SettingService $settingService,
        CategoryService $categoryService,
        DashboardSettingService $dashboardSettingService
    ) {
        $this->company = $company;
        $this->setting = $setting;
        $this->role = $role;
        $this->user = $user;
        $this->userRole = $userRole;
        $this->permission = $permission;
        $this->permissionRole = $permissionRole;
        $this->settingService = $settingService;
        $this->categoryService = $categoryService;
        $this->dashboardSettingService = $dashboardSettingService;
        $this->thumbWidth = 200;
        $this->thumbHeight = 200;
        $this->profileDirectory = 'assets/media/users/profile/';
        $this->companyDirectory = 'assets/media/companies/';
    }

    public function getCompanies()
    {
        return $this->company->dataTable($this->company->query()->filter());
    }

    public function store($request)
    {
        $userImage = $companyImage = null;
        if ($request->file('image')) {
            $userImage = Helper::uploadfile([
                'file' => $request->file('image'),
                'path' => $this->profileDirectory,
                'width' => $this->thumbWidth,
                'height' => $this->thumbHeight,
            ]);
            $companyImage = $this->settingService->logoUpload($request->file('image'));
        }
        $company = $this->company->create([
            'created_by' => auth()->id() ?? $this->user->query()->whereNull('company_id')->value('id'),
            'status' => $request->status
        ]);
        $company->refresh();
        $menuLinks = app(AsideSettingService::class)->getTenantMenuLinks();
        $this->setting->create([
            'company_id' => $company->id,
            'name' => $request->company_name,
            'email' => $request->company_email,
            'address' => $request->company_address,
            'asidebar' => json_encode($menuLinks),
            'logo' => $companyImage
        ]);
        $companyRole = $this->role->create([
            'name' => $request->isManager ? 'manager' : 'company',
            'company_id' => $company->id,
            'status' => $request->status,
        ]);
        $companyRole->refresh();
        $password = Hash::make($request->password);
        $user = $this->user->create([
            'name' => $request->name,
            'password' => $password,
            'email' => $request->email,
            'company_id' =>  $company->id,
            'status' => $request->status,
            'image' => $userImage,
        ]);
        $user->refresh();
        $this->userRole->create([
            'role_id' => $companyRole->id,
            'user_id' => $user->id
        ]);
        if ($request->roles) {
            $roles = $this->role->query()->whereIn('uid', $request->roles)->get();
            foreach ($roles as $key => $role) {
                $this->role->create([
                    'company_id' => $company->id,
                    'name' => $role->name,
                    'status' => $request->status,
                ]);
            }
        }

        if ($request->permissions) {
            $permissions = $this->permission->query()
                ->whereIn('uid', $request->permissions)
                ->where('is_platform', false)
                ->get();
            foreach ($permissions as $key => $permission) {
                $permission = $this->permission->create([
                    'company_id' => $company->id,
                    'name' => $permission->name,
                    'module_id' => $permission->module_id,
                    'is_platform' => false,
                ]);
                $permission->refresh();
                $this->permissionRole->create([
                    'role_id' => $companyRole->id,
                    'permission_id' =>  $permission->id
                ]);
            }
        }

        $defaultService = app(DefaultService::class);
        $defaultService->initiateAccounts($company);
        $defaultService->initiateVouchers($company);
        $defaultService->initiateAccountSettings($company);

        return $company;
    }

    public function destroy($company)
    {
        $productService = app(ProductService::class);
        $userService = app(UserService::class);
        foreach ($productService->getByCompanyId($company->id) as $key => $product) {
            $productService->delete($product);
        }
        foreach ($this->categoryService->getByCompanyId($company->id) as $key => $category) {
            $this->categoryService->destroy($category);
        }
        foreach ($userService->getByCompanyId($company->id) as $key => $user) {
            $userService->delete($user);
        }
        if ($company->dashboardSetting) {
            $this->dashboardSettingService->destroy($company->dashboardSetting);
        }
        Helper::deleteExcept([
            'files' => [$company->setting->logo],
            'exceptions' => [],
            'path' => $this->companyDirectory
        ]);
        $company->delete();
    }
}
