<?php

namespace Modules\Billing\Repositories;

use App\Repositories\BaseRepository;
use Modules\Billing\Models\Subscription;

class SubscriptionRepository extends BaseRepository
{
    public function __construct()
    {
        parent::__construct(new Subscription());
    }

    /**
     * The subscription currently granting a company access (active/trialing,
     * unexpired).
     */
    public function activeForCompany(int $companyId): ?Subscription
    {
        return $this->query()
            ->where('company_id', $companyId)
            ->whereIn('status', ['active', 'trialing'])
            ->where(function ($query) {
                $query->whereNull('current_period_end')
                    ->orWhere('current_period_end', '>', now());
            })
            ->latest('id')
            ->first();
    }

    /**
     * The most recent non-terminal subscription (used to show renewal details
     * even when the status is past_due/unpaid).
     */
    public function currentForCompany(int $companyId): ?Subscription
    {
        return $this->query()
            ->where('company_id', $companyId)
            ->whereIn('status', ['active', 'trialing', 'past_due', 'unpaid'])
            ->latest('id')
            ->first();
    }

    public function countByStatuses(array $statuses): int
    {
        return $this->query()->whereIn('status', $statuses)->count();
    }

    /**
     * Active subscriptions with their price/plan eager loaded (platform MRR).
     *
     * @return \Illuminate\Support\Collection
     */
    public function activeWithPrice()
    {
        return $this->query()
            ->whereIn('status', ['active', 'trialing'])
            ->with(['price', 'plan'])
            ->get();
    }
}
