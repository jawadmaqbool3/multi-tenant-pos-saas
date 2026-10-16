<?php

namespace Modules\RBAC\Http\Controllers;

use App\Http\Controllers\Controller;

use Modules\RBAC\Services\PermissionService;
use Illuminate\Http\Request;

class PermissionController extends Controller
{
    protected $service;

    public function __construct(PermissionService $service)
    {
        $this->service = $service;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {
            return $this->service->getPermissions();
        }
        return view('rbac::permissions.index');
    }
}
