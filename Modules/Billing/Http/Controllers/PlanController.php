<?php

namespace Modules\Billing\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Billing\Http\Requests\StorePlanRequest;
use Modules\Billing\Http\Requests\UpdatePlanRequest;
use Modules\Billing\Models\Plan;
use Modules\Billing\Services\PlanService;

class PlanController extends Controller
{
    protected $service;

    public function __construct(PlanService $service)
    {
        $this->service = $service;
    }

    /**
     * @return \Illuminate\Contracts\View\View|\Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {
            return $this->service->getPlans();
        }
        return view('billing::plans.index');
    }

    /**
     * @return \Illuminate\Contracts\View\View
     */
    public function create()
    {
        $data = $this->service->getFormData();
        return view('billing::plans.create', $data);
    }

    /**
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(StorePlanRequest $request)
    {
        $this->service->store($request);

        return response([
            'success' => true,
            'message' => 'New Record Added',
            'redirect' => true,
            'url' => route('billing.plans.index'),
        ]);
    }

    /**
     * @param  \Modules\Billing\Models\Plan  $plan
     * @return \Illuminate\Contracts\View\View
     */
    public function show(Plan $plan)
    {
        $data = $this->service->getDetail($plan);

        if (!$data) {
            abort(404);
        }

        return view('billing::plans.show', $data);
    }

    /**
     * @param  \Modules\Billing\Models\Plan  $plan
     * @return \Illuminate\Contracts\View\View
     */
    public function edit(Plan $plan)
    {
        $data = $this->service->getFormData($plan);
        return view('billing::plans.edit', $data);
    }

    /**
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(UpdatePlanRequest $request, Plan $plan)
    {
        $this->service->update($request, $plan);

        return response([
            'success' => true,
            'message' => 'Record Updated',
            'redirect' => true,
            'url' => route('billing.plans.index'),
        ]);
    }

    /**
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(Plan $plan)
    {
        $this->service->delete($plan);

        return response([
            'success' => true,
            'message' => 'Record Deleted',
            'table_reload' => true,
        ]);
    }
}