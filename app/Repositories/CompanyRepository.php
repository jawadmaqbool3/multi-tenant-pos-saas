<?php

namespace App\Repositories;

use App\Models\Company;
use Illuminate\Support\Carbon;

class CompanyRepository extends BaseRepository
{
    protected $dataTableWith = ['setting', 'subscriptions.plan'];
    protected $dataTableRawColumns = ['action', 'setting.logo', 'status'];

    public function __construct()
    {
        parent::__construct(new Company());

        $this->dataTableEditColumns = [
            'status' => function ($company) {
                return $this->dt->statusBadge($company->status, $company->status == 'active' ? 'btn-success' : 'btn-danger');
            },
            'setting.logo' => function ($company) {
                $logo = @$company->setting->logo;

                if ($logo && is_file(public_path('assets/media/small/logo/' . $logo))) {
                    return $this->dt->thumbnail('assets/media/small/logo', $logo);
                }

                return $this->dt->letterAvatar(@$company->setting->name ?: 'Company');
            },
            'setting.name' => function ($company) {
                return $company->setting->name ?? null;
            },
            'subscription_plan' => function ($company) {
                $subscription = $company->subscriptions
                    ->whereIn('status', ['active', 'trialing'])
                    ->filter(function ($subscription) {
                        return !$subscription->current_period_end
                            || Carbon::parse($subscription->current_period_end)->isFuture();
                    })
                    ->sortByDesc('id')
                    ->first();

                return $subscription && $subscription->plan ? $subscription->plan->name : 'No plan';
            },
        ];
        $this->dataTableAddColumns = [
            'action' => function ($company) {
                $btns = [];
                if (auth()->user()->hasPermission('view company')) {
                    $btns[] = $this->dt->viewButton(route('companies.show', $company->uid), 'Company details');
                }
                if (auth()->user()->hasPermission('edit company')) {
                    $btns[] = $this->dt->editButton(route('general.settings', @$company->setting->uid));
                }
                if (auth()->user()->hasPermission('delete company')) {
                    $btns[] = $this->dt->deleteButton($company->uid, @$company->setting->name);
                }
                return $this->dt->actionButtons($btns);
            },
        ];
    }

    public function countStatus(string $status): int
    {
        return $this->query()->where('status', $status)->count();
    }

    /**
     * Companies created per month for the last $months months (platform chart).
     *
     * @return array<int, int>  values aligned to oldest-first
     */
    public function newByMonth(int $months = 6): array
    {
        $counts = [];

        for ($i = $months - 1; $i >= 0; $i--) {
            $date = now()->startOfMonth()->subMonths($i);
            $counts[] = $this->query()
                ->where('created_at', '>=', $date->toDateString())
                ->where('created_at', '<', $date->copy()->addMonth()->toDateString())
                ->count();
        }

        return $counts;
    }

    /**
     * Human month labels (oldest-first) matching newByMonth().
     *
     * @return array<int, string>
     */
    public function monthLabels(int $months = 6): array
    {
        $labels = [];

        for ($i = $months - 1; $i >= 0; $i--) {
            $labels[] = now()->startOfMonth()->subMonths($i)->format('M');
        }

        return $labels;
    }
}
