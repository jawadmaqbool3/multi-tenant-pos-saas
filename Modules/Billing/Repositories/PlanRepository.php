<?php

namespace Modules\Billing\Repositories;

use App\Repositories\BaseRepository;
use Modules\Billing\Models\Plan;

class PlanRepository extends BaseRepository
{
    protected $dataTableWith = ['prices', 'subscriptions'];
    protected $dataTableRawColumns = ['action', 'status', 'companies_count'];

    public function __construct()
    {
        parent::__construct(new Plan());

        $this->dataTableEditColumns = [
            'status' => function ($plan) {
                return $this->dt->statusBadge($plan->status, $plan->status == 'active' ? 'btn-success' : 'btn-danger');
            },
            'price_summary' => function ($plan) {
                return $plan->prices->reject(fn ($price) => !$price->active)
                    ->map(fn ($price) => $price->unit_amount . ' ' . strtoupper($price->currency) . ' / ' . $price->interval)
                    ->implode(', ');
            },
            'companies_count' => function ($plan) {
                return $plan->subscriptions
                    ->whereIn('status', ['active', 'trialing'])
                    ->count();
            },
        ];
        $this->dataTableAddColumns = [
            'action' => function ($plan) {
                $btns = [];
                if (auth()->user()->hasPermission('view billing plan')) {
                    $btns[] = $this->dt->viewButton(route('billing.plans.show', $plan->uid), 'Plan details');
                }
                if (auth()->user()->hasPermission('edit billing plan')) {
                    $btns[] = $this->dt->editButton(route('billing.plans.edit', $plan->uid));
                }
                if (auth()->user()->hasPermission('delete billing plan')) {
                    $btns[] = $this->dt->deleteButton($plan->uid, $plan->name);
                }
                return $this->dt->actionButtons($btns);
            },
        ];
    }
}