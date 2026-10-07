<?php

namespace App\Services;

use App\Core\Helper;
use App\Exceptions\CustomException;
use Illuminate\Support\Facades\Artisan;
use App\Models\Setting;
use App\Repositories\PermissionRepository;
use App\Repositories\RoleRepository;
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

    public function getSetting(Setting $setting = null)
    {
        if ($setting == false) {
            $setting = $this->setting->query()->filter()->first();
        }
        return $setting;
    }

    public function update($request, $setting)
    {
        if ($setting == false) {
            $setting = $this->setting->query()->filter()->first();
        }
        $filename = $setting->logo;
        if ($request->file('logo')) {
            if ($setting == false) {
                throw new CustomException('Setting not found.', 'error');
            }
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
            $allPermissions = $this->permission->query()->whereIn('uid', $request->permissions)->get();
            $permissionToKeep = [];
            foreach ($allPermissions as $key => $permission) {
                $_permission = $this->permission->query()->where('name', $permission->name)->where('company_id', $setting->company_id)->first();
                if (!$_permission) {
                    $_permission = $this->permission->create([
                        'module_id' => $permission->module_id,
                        'name' => $permission->name,
                        'company_id' => $setting->company_id,
                    ]);
                }
                $permissionToKeep[] = $_permission->uid;
            }
            $this->permission->query()->whereNotIn('uid', $permissionToKeep)->where('company_id', $setting->company_id)->delete();
        }
        if (is_array($request->roles)) {
            $allRoles = $this->role->query()->whereIn('uid', $request->roles)->get();
            $rolesToKeep = [];
            foreach ($allRoles as $key => $role) {
                $_role = $this->role->query()->where('name', $role->name)->where('company_id', $setting->company_id)->first();
                if (!$_role) {
                    $_role = $this->role->create([
                        'name' => $role->name,
                        'company_id' => $role->company_id,
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
                throw new CustomException('Failed to upload image', 'error');
            }
        }
        return $filename;
    }
}
