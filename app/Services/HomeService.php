<?php

namespace App\Services;

use Modules\Order\Repositories\OrderRepository;
use Modules\Inventory\Repositories\ProductRepository;
use Modules\ChartOfAccounts\Repositories\TransactionRepository;
use Modules\Employee\Repositories\UserRepository;
use App\Repositories\CompanyRepository;
use Carbon\Carbon;

class HomeService
{
    protected $order;
    protected $product;
    protected $transaction;
    protected $user;
    protected $company;

    public function __construct(
        OrderRepository $order,
        ProductRepository $product,
        TransactionRepository $transaction,
        UserRepository $user,
        CompanyRepository $company
    ) {
        $this->order = $order;
        $this->product = $product;
        $this->transaction = $transaction;
        $this->user = $user;
        $this->company = $company;
    }

    /**
     * Products used by the quick order / add stock modals.
     *
     * @return \Illuminate\Support\Collection
     */
    public function getProducts()
    {
        return $this->product->query()->filter()->select('uid', 'name', 'purchase_price', 'unit_price', 'stock', 'code')->get();
    }

    /**
     * Today's revenue / expense / profit (with a day-over-day direction).
     */
    public function profitCard(): array
    {
        $today = Carbon::now();
        $previousDay = Carbon::now()->subDay();

        $todayExpense = $this->transactionAmount('instore invoice expense', 'debit', $today);
        $todayRevenue = $this->transactionAmount('instore invoice revenue', 'credit', $today);
        $previousDayExpense = $this->transactionAmount('instore invoice expense', 'debit', $previousDay);
        $previousDayRevenue = $this->transactionAmount('instore invoice revenue', 'credit', $previousDay);

        $todayProfit = $todayRevenue - $todayExpense;
        $previousDayProfit = $previousDayRevenue - $previousDayExpense;

        return [
            'chart_type' => 'donut',
            'series' => [round($todayRevenue, 2), round($todayExpense, 2)],
            'labels' => ['Revenue', 'Expense'],
            'kpi' => [
                'value' => round($todayProfit, 2),
                'format' => 'currency',
                'label' => 'Today Profit',
                'direction' => $todayProfit >= $previousDayProfit ? 'positive' : 'negative',
            ],
            'legend' => [
                ['label' => 'Expense', 'value' => round($todayExpense, 2), 'format' => 'currency', 'color' => 'danger'],
                ['label' => 'Revenue', 'value' => round($todayRevenue, 2), 'format' => 'currency', 'color' => 'primary'],
            ],
        ];
    }

    /**
     * Last 7 days of instore revenue as a bar chart.
     */
    public function averageSalesChart(): array
    {
        $day = Carbon::now();
        $sales = [];
        $days = [];
        $total = 0;

        for ($i = 0; $i < 7; $i++) {
            $amount = $this->transactionAmount('instore invoice revenue', 'credit', $day);
            $sales[] = round($amount, 2);
            $days[] = $day->format('D');
            $total += $amount;
            $day = $day->copy()->subDay();
        }

        $sales = array_reverse($sales);
        $days = array_reverse($days);
        $average = round($total / 7, 2);

        return [
            'chart_type' => 'bar',
            'series' => [['name' => 'Per day sales', 'data' => $sales]],
            'categories' => $days,
            'kpi' => [
                'value' => $average,
                'format' => 'currency',
                'label' => 'Average Daily Sales',
                'direction' => end($sales) >= $average ? 'positive' : 'negative',
            ],
        ];
    }

    /**
     * Revenue + credit-transaction count for the current month.
     */
    public function monthlySales(): array
    {
        $today = Carbon::now();
        $amount = $this->transactionAmount('instore invoice revenue', 'credit', $today, 'Y-m');
        $count = $this->transactionCount('instore invoice revenue', 'credit', $today, 'Y-m');
        $previousAmount = $this->transactionAmount('instore invoice revenue', 'credit', $today->copy()->subMonth(), 'Y-m');

        return [
            'value' => round($amount, 2),
            'format' => 'currency',
            'direction' => $amount >= $previousAmount ? 'positive' : 'negative',
            'secondary' => $count,
            'secondary_format' => 'number',
        ];
    }

    /**
     * Orders placed this month.
     */
    public function monthlyOrders(): array
    {
        $today = Carbon::now();
        $amount = $this->ordersAmount($today, null);
        $count = $this->ordersCount($today, null);
        $previousAmount = $this->ordersAmount($today->copy()->subMonth(), null);

        return [
            'value' => round($amount, 2),
            'format' => 'currency',
            'direction' => $amount >= $previousAmount ? 'positive' : 'negative',
            'secondary' => $count,
            'secondary_format' => 'number',
        ];
    }

    /**
     * Orders cancelled this month (an increase is shown inverted/red).
     */
    public function monthlyCancelledOrders(): array
    {
        $today = Carbon::now();
        $amount = $this->ordersAmount($today, 'cancelled');
        $count = $this->ordersCount($today, 'cancelled');
        $previousAmount = $this->ordersAmount($today->copy()->subMonth(), 'cancelled');

        return [
            'value' => round($amount, 2),
            'format' => 'currency',
            'direction' => $amount >= $previousAmount ? 'positive' : 'negative',
            'inverse' => true,
            'secondary' => $count,
            'secondary_format' => 'number',
        ];
    }

    /**
     * Orders refunded this month (an increase is shown inverted/red).
     */
    public function monthlyRefundedOrders(): array
    {
        $today = Carbon::now();
        $amount = $this->ordersAmount($today, 'refunded');
        $count = $this->ordersCount($today, 'refunded');
        $previousAmount = $this->ordersAmount($today->copy()->subMonth(), 'refunded');

        return [
            'value' => round($amount, 2),
            'format' => 'currency',
            'direction' => $amount >= $previousAmount ? 'positive' : 'negative',
            'inverse' => true,
            'secondary' => $count,
            'secondary_format' => 'number',
        ];
    }

    /**
     * New customers this month plus today's buyers (heroes).
     */
    public function monthlyCustomers(): array
    {
        $today = Carbon::now();
        $thisMonth = $this->customerCount($today);
        $lastMonth = $this->customerCount($today->copy()->subMonth());

        $todayHeroIds = $this->order->query()->filter()
            ->where('orders.created_at', 'LIKE', $today->format('Y-m-d') . '%')
            ->where('status', 'paid')
            ->with('orderUser')
            ->get()
            ->pluck('orderUser')->flatten()->pluck('user_id')->unique();

        $heroes = $this->user->query()->filter()->whereHas('role', function ($query) {
            $query->where('name', 'customer');
        })->whereIn('users.id', $todayHeroIds)
            ->take(8)
            ->get()
            ->map(function ($user) {
                return ['name' => $user->name, 'image' => $user->image];
            })->values()->all();

        return [
            'value' => $thisMonth,
            'format' => 'number',
            'direction' => $thisMonth >= $lastMonth ? 'positive' : 'negative',
            'inverse' => true,
            'heroes' => $heroes,
        ];
    }

    /**
     * The five most recent orders.
     */
    public function recentOrders(): array
    {
        $orders = $this->order->query()->filter()->orderBy('date', 'DESC')->with('orderUser.user')->limit(5)->get();

        $rows = $orders->map(function ($order) {
            $user = optional($order->orderUser)->first()->user ?? null;
            $customer = $user ? $user->name : 'Instore Order';

            $color = 'success';
            if ($order->status == 'refunded') {
                $color = 'danger';
            } elseif ($order->status == 'cancelled') {
                $color = 'warning';
            } elseif ($order->status == 'paid') {
                $color = 'primary';
            }

            return [
                $order->order_no,
                $customer,
                ['text' => $order->status, 'badge' => $color],
            ];
        })->all();

        return [
            'columns' => ['Order', 'Customer', 'Status'],
            'rows' => $rows,
            'empty' => 'Nothing found',
        ];
    }

    /**
     * @return float
     */
    protected function transactionAmount(string $source, string $type, Carbon $date, string $format = 'Y-m-d'): float
    {
        return (float) $this->transaction->query()->filter()->whereHas('voucher', function ($query) {
            $query->where('type', '!=', 'void');
        })
            ->where('source', $source)
            ->where('type', $type)
            ->where('created_at', 'LIKE', $date->format($format) . '%')
            ->sum('amount');
    }

    /**
     * @return int
     */
    protected function transactionCount(string $source, string $type, Carbon $date, string $format = 'Y-m-d'): int
    {
        return $this->transaction->query()->filter()->whereHas('voucher', function ($query) {
            $query->where('type', '!=', 'void');
        })
            ->where('source', $source)
            ->where('type', $type)
            ->where('created_at', 'LIKE', $date->format($format) . '%')
            ->count();
    }

    /**
     * @return float
     */
    protected function ordersAmount(Carbon $date, ?string $status): float
    {
        $query = $this->order->query()->filter()->where('created_at', 'LIKE', $date->format('Y-m') . '%');

        if ($status) {
            $query->where('status', $status);
        }

        return (float) $query->sum('amount');
    }

    /**
     * @return int
     */
    protected function ordersCount(Carbon $date, ?string $status): int
    {
        $query = $this->order->query()->filter()->where('created_at', 'LIKE', $date->format('Y-m') . '%');

        if ($status) {
            $query->where('status', $status);
        }

        return $query->count();
    }

    protected function customerCount(Carbon $date): int
    {
        return $this->user->query()->filter()->whereHas('role', function ($query) {
            $query->where('name', 'customer');
        })
            ->where('users.created_at', 'LIKE', $date->format('Y-m') . '%')
            ->count();
    }
}
