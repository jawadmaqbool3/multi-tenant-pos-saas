<?php

namespace Modules\Billing\Repositories;

use App\Repositories\BaseRepository;
use Modules\Billing\Models\Coupon;

class CouponRepository extends BaseRepository
{
    protected $dataTableWith = ['redemptions'];
    protected $dataTableRawColumns = ['action', 'status', 'code'];

    public function __construct()
    {
        parent::__construct(new Coupon());

        $this->dataTableFilterColumns = [
            'code' => function ($query, $keyword) {
                $query->where(function ($q) use ($keyword) {
                    $q->where('billing_coupons.code', 'like', '%' . $keyword . '%')
                        ->orWhere('billing_coupons.name', 'like', '%' . $keyword . '%');
                });
            },
        ];

        $this->dataTableEditColumns = [
            'code' => function ($coupon) {
                return '<span class="fw-bold text-gray-800">' . e($coupon->code) . '</span>'
                    . ($coupon->name ? '<div class="text-muted fs-7">' . e($coupon->name) . '</div>' : '');
            },
            'discount' => function ($coupon) {
                return $coupon->discountLabel();
            },
            'duration_label' => function ($coupon) {
                if ($coupon->duration === 'repeating') {
                    return 'Repeating · ' . (int) $coupon->duration_in_months . ' mo';
                }

                return ucfirst($coupon->duration);
            },
            'redemptions_count' => function ($coupon) {
                return $coupon->redemptions->count();
            },
            'status' => function ($coupon) {
                return $this->dt->statusBadge(
                    $coupon->status,
                    $coupon->status === 'active' ? 'btn-success' : 'btn-danger'
                );
            },
        ];

        $this->dataTableAddColumns = [
            'action' => function ($coupon) {
                $btns = [];
                if (auth()->user()->hasPermission('edit coupon')) {
                    $btns[] = $this->dt->editButton(route('billing.coupons.edit', $coupon->uid));
                }
                if (auth()->user()->hasPermission('delete coupon')) {
                    $btns[] = $this->dt->deleteButton($coupon->uid, $coupon->code);
                }
                return $this->dt->actionButtons($btns);
            },
        ];
    }
}
