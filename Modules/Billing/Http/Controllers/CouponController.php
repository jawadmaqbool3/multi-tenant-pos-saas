<?php

namespace Modules\Billing\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Billing\Http\Requests\StoreCouponRequest;
use Modules\Billing\Http\Requests\UpdateCouponRequest;
use Modules\Billing\Models\Coupon;
use Modules\Billing\Services\CouponService;

class CouponController extends Controller
{
    protected $service;

    public function __construct(CouponService $service)
    {
        $this->service = $service;
    }

    /**
     * @return \Illuminate\Contracts\View\View|\Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {
            return $this->service->getCoupons();
        }
        return view('billing::coupons.index');
    }

    /**
     * @return \Illuminate\Contracts\View\View
     */
    public function create()
    {
        $data = $this->service->getFormData();
        return view('billing::coupons.create', $data);
    }

    /**
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(StoreCouponRequest $request)
    {
        $this->service->store($request);

        return response([
            'success' => true,
            'message' => 'New Record Added',
            'redirect' => true,
            'url' => route('billing.coupons.index'),
        ]);
    }

    /**
     * @return \Illuminate\Contracts\View\View
     */
    public function edit(Coupon $coupon)
    {
        $data = $this->service->getFormData($coupon);
        return view('billing::coupons.edit', $data);
    }

    /**
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(UpdateCouponRequest $request, Coupon $coupon)
    {
        $this->service->update($request, $coupon);

        return response([
            'success' => true,
            'message' => 'Record Updated',
            'redirect' => true,
            'url' => route('billing.coupons.index'),
        ]);
    }

    /**
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(Coupon $coupon)
    {
        $this->service->delete($coupon);

        return response([
            'success' => true,
            'message' => 'Record Deleted',
            'table_reload' => true,
        ]);
    }
}
