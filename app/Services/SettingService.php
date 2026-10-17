<?php

namespace App\Services;

use App\Core\Helper;
use App\Exceptions\ImageUploadFailedException;
use App\Exceptions\SettingNotFoundException;
use App\Models\Setting;
use Illuminate\Support\Facades\Artisan;
use Modules\RBAC\Repositories\PermissionRepository;
use Modules\RBAC\Repositories\RoleRepository;
use App\Repositories\SettingRepository;
use Illuminate\Support\Facades\Session;

class SettingService
{
    protected $setting;
    protected $permission;
    protected $role;

    private $logoDimensions;
    private $logoDirectories;

    public function __construct(
        SettingRepository $setting,
        PermissionRepository $permission,
        RoleRepository $role
    ) {
        $this->setting = $setting;
        $this->permission = $permission;
        $this->role = $role;
        $this->setDimensions(
            [
                "large" => [
                    "width" => 400,
                    "height" => 400,
                ],
                "medium" => [
                    "width" => 200,
                    "height" => 200,
                ],
                "small" => [
                    "width" => 100,
                    "height" => 100,
                ],
                "icon" => [
                    "width" => 40,
                    "height" => 40,
                ],
            ]
        );
        $this->setDirectories(
            [
                "large" => "/assets/media/large/logo/",
                "medium" => "/assets/media/medium/logo/",
                "small" => "/assets/media/small/logo/",
                "icon" => "/assets/media/icons/logo/",
            ]
        );
    }

    public function setDimensions($dimensions)
    {
        $this->logoDimensions = $dimensions;
    }

    public function setDirectories($directories)
    {
        $this->logoDirectories = $directories;
    }

    public function getSetting($setting = null)
    {
        return $this->resolveSetting($setting);
    }

    protected function resolveSetting($setting = null)
    {
        if ($setting instanceof Setting) {
            return $setting;
        }

        if (is_string($setting) && $setting !== '') {
            return $this->setting->query()->where('uid', $setting)->first();
        }

        return $this->setting->query()->filter()->first();
    }

    public function update($request, $setting)
    {
        $setting = $this->resolveSetting($setting);

        if (!$setting) {
            throw new SettingNotFoundException();
        }

        $filename = $setting->logo;
        if ($request->file('logo')) {
            $filename = $this->logoUpload($request->file('logo'), $setting->logo);
        }
        $this->setting->query()->where('id', $setting->id)->update([
            "maintenance_mode" => $request->maintenanceMode ?? 0,
            "name" => $request->name,
            "email" => $request->email,
            "address" => $request->address,
            "contact" => $request->contact,
            "url" => $request->appURL,
            "logo" => $filename,
            "facebook" => $request->facebook,
            "instagram" => $request->instagram,
            "whatsapp" => $request->whatsapp,
        ]);

        if (is_array($request->permissions)) {
            $provisioning = app(\Modules\RBAC\Services\RoleProvisioningService::class);
            $companyRole = $this->role->query()
                ->where('company_id', $setting->company_id)
                ->where('name', 'company')
                ->first();

            if ($companyRole) {
                $allPermissions = $this->permission->query()->whereIn('uid', $request->permissions)->get();
                $provisioning->syncRolePermissions($companyRole->id, $allPermissions->pluck('id')->all());
            }
        }
        if (is_array($request->roles)) {
            $allRoles = $this->role->query()->whereIn('uid', $request->roles)->get();
            $rolesToKeep = [];
            foreach ($allRoles as $key => $role) {
                $_role = $this->role->query()->where('name', $role->name)->where('company_id', $setting->company_id)->first();
                if (!$_role) {
                    $_role = $this->role->create([
                        'name' => $role->name,
                        'company_id' => $setting->company_id,
                    ]);
                }
                $rolesToKeep[] = $_role->uid;
            }
            $this->role->query()->whereNotIn('uid', $rolesToKeep)->where('company_id', $setting->company_id)->delete();
        }
        Session::put('setting', $setting->refresh());
        Artisan::call('optimize:clear');
    }

    public function logoUpload($new, $old = null)
    {
        $filename = date('ymdHis') . "." . $new->getClientOriginalExtension();
        foreach ($this->logoDirectories as $key => $directory) {
            Helper::deleteExcept([
                'files' => [$old],
                'exceptions' => [],
                'path' => $directory
            ]);
            $image = Helper::uploadfile([
                'file' => $new,
                'path' => $directory,
                'width' => $this->logoDimensions[$key]["width"],
                'height' => $this->logoDimensions[$key]["height"],
                'filename' => $filename
            ]);
            if ($image == false) {
                throw new ImageUploadFailedException();
            }
        }
        return $filename;
    }
}
