<?php

namespace App\Services;

use App\Core\Helper;
use App\Services\DefaultService;
use Modules\Employee\Services\UserService;
use Modules\Inventory\Services\ProductService;
use Modules\Inventory\Services\CategoryService;
use App\Services\AsideSettingService;
use App\Repositories\CompanyRepository;
use Modules\RBAC\Repositories\RoleRepository;
use Modules\RBAC\Services\RoleProvisioningService;
use App\Repositories\SettingRepository;
use Modules\Employee\Repositories\UserRepository;
use Modules\RBAC\Repositories\UserRoleRepository;
use Modules\Billing\Repositories\InvoiceRepository;
use Modules\Billing\Services\PlanLimitService;
use Illuminate\Support\Facades\Hash;

class CompanyService
{
    protected $company;
    protected $setting;
    protected $role;
    protected $user;
    protected $userRole;
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
        SettingService $settingService,
        CategoryService $categoryService,
        DashboardSettingService $dashboardSettingService
    ) {
        $this->company = $company;
        $this->setting = $setting;
        $this->role = $role;
        $this->user = $user;
        $this->userRole = $userRole;
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

    /**
     * Detail/history payload for a single company (uid or model).
     *
     * @param  mixed  $company
     * @return array|null
     */
    public function getDetail($company)
    {
        if (is_string($company)) {
            $company = $this->company->query()->where('uid', $company)->first();
        }

        if (!$company) {
            return null;
        }

        $company->loadMissing(['setting', 'subscriptions.plan', 'subscriptions.price']);

        $limitService = app(PlanLimitService::class);
        $plan = $limitService->planForCompany($company);

        $usage = [];

        if ($plan) {
            foreach (['employees' => 'Employees', 'roles' => 'Roles'] as $key => $label) {
                $limit = $limitService->limitFor($key, $plan);
                $used = $limitService->usage($key, $company);

                $usage[] = [
                    'key' => $key,
                    'label' => $label,
                    'used' => $used,
                    'limit' => $limit,
                    'percent' => $limit ? min(100, (int) round($used / max(1, $limit) * 100)) : null,
                ];
            }
        }

        $invoices = app(InvoiceRepository::class);
        $revenue = $invoices->paidRevenueByMonthForCompany($company->id, 6);

        return [
            'company' => $company,
            'setting' => $company->setting,
            'plan' => $plan,
            'usage' => $usage,
            'subscriptions' => $company->subscriptions->sortByDesc('id')->values(),
            'invoices' => $invoices->latestForCompany($company->id, 10),
            'revenue' => $revenue,
            'revenueLabels' => $this->company->monthLabels(6),
            'stats' => [
                'subscriptions' => $company->subscriptions->count(),
                'invoices' => $invoices->countForCompany($company->id),
                'paid' => $invoices->paidTotalForCompany($company->id),
                'currency' => strtoupper(config('billing.currency')),
            ],
        ];
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
                'field' => 'image',
            ]);
            $companyImage = $this->settingService->logoUpload($request->file('image'));
        }
        $company = $this->company->create([
            'created_by' => optional(auth()->user())->id ?? $this->user->query()->whereNull('company_id')->value('id'),
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
            'name' => 'company',
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

        // Auto-provision every default role for the new company and attach the
        // shared global permissions to each of them.
        $provisioning = app(RoleProvisioningService::class);
        $provisioning->provisionForCompany($company->id, $request->status);

        // Optional manual selections from the form are additive.
        if ($request->roles) {
            $roles = $this->role->query()->whereIn('uid', $request->roles)->get();
            foreach ($roles as $key => $role) {
                $provisioning->ensureRole($company->id, $role->name, $request->status);
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
