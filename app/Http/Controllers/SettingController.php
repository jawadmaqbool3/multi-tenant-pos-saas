<?php

namespace App\Http\Controllers;

use App\Core\Helper;
use App\Exceptions\CustomException;
use App\Models\Permission;
use App\Models\Role;
use App\Models\Setting;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use stdClass;

class SettingController extends Controller
{

    private $logoDimensions;
    private $logoDirectories;

    public function __construct()
    {
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

    public function index(Setting $setting = null)
    {

        if ($setting == false) {
            $setting = Setting::filter()->first();
        }
        return view('settings.generalSettings', compact('setting'));
    }

    public function update(Request $request, Setting $setting = null)
    {
        $this->validate($request, [
            'name' => 'required',
            'email' => 'required',
            'address' => 'required',
        ]);
        DB::beginTransaction();
        try {
            if ($setting == false) {
                $setting = Setting::filter()->first();
            }
            $filename = $setting->logo;
            if ($request->file('logo')) {
                if ($setting == false) {
                    throw new CustomException('Setting not found.', 'error');
                }
                $filename = $this->logoUpload($request->file('logo'), $setting->logo);
            }
            Setting::where('id', $setting->id)->update([
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
                $allPermissions = Permission::whereIn('uid', $request->permissions)->get();
                $permissionToKeep = [];
                foreach ($allPermissions as $key => $permission) {
                    $_permission = Permission::where('name', $permission->name)->where('company_id', $setting->company_id)->first();
                    if (!$_permission) {
                        $_permission = Permission::create([
                            'module_id' => $permission->module_id,
                            'name' => $permission->name,
                            'company_id' => $setting->company_id,
                        ]);
                    }
                    $permissionToKeep[] = $_permission->uid;
                }
                Permission::whereNotIn('uid', $permissionToKeep)->where('company_id', $setting->company_id)->delete();
            }
            if (is_array($request->roles)) {
                $allRoles = Role::whereIn('uid', $request->roles)->get();
                $rolesToKeep = [];
                foreach ($allRoles as $key => $role) {
                    $_role = Role::where('name', $role->name)->where('company_id', $setting->company_id)->first();
                    if (!$_role) {
                        $_role = Role::create([
                            'name' => $role->name,
                            'company_id' => $role->company_id,
                        ]);
                    }
                    $rolesToKeep[] = $_role->uid;
                }
                Role::whereNotIn('uid', $rolesToKeep)->where('company_id', $setting->company_id)->delete();
            }
            Session::put('setting', $setting->refresh());
            $cacheController = new CacheController();
            $cacheController->clearOptimize();
            DB::commit();
            return response([
                'success' => true,
                'reload' => true,
                'message' => 'Settings Saved',
            ]);
        } catch (CustomException $e) {
            DB::rollBack();
            return response([
                $e->getLevel() => true,
                'message' => $e->getMessage(),
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
