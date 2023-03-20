<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Product;
use App\Models\Transaction;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax) {
            if ($request->charts) {
                $response = [];
                //Profit Chart
                if (auth()->user()->hasPermission('dashboard profit loss report')) {
                    $today = Carbon::create()->now();
                    $previousDay = Carbon::create()->subDay();
                    $todayExpense = Transaction::filter()->whereHas('voucher', function ($query) {
                        $query->where('type', '!=', 'void');
                    })
                        ->where('source', 'instore invoice expense')
                        ->where('type', 'debit')
                        ->where('created_at', 'LIKE', $today->format('Y-m-d') . '%')
                        ->sum('amount');

                    $todayRevenue = Transaction::filter()->whereHas('voucher', function ($query) {
                        $query->where('type', '!=', 'void');
                    })
                        ->where('source', 'instore invoice revenue')
                        ->where('type', 'credit')
                        ->where('created_at', 'LIKE', $today->format('Y-m-d') . '%')
                        ->sum('amount');

                    $previousDayExpense = Transaction::filter()->whereHas('voucher', function ($query) {
                        $query->where('type', '!=', 'void');
                    })
                        ->where('source', 'instore invoice expense')
                        ->where('type', 'debit')
                        ->where('created_at', 'LIKE', $previousDay->format('Y-m-d') . '%')
                        ->sum('amount');

                    $previousDayRevenue = Transaction::filter()->whereHas('voucher', function ($query) {
                        $query->where('type', '!=', 'void');
                    })
                        ->where('source', 'instore invoice revenue')
                        ->where('type', 'credit')
                        ->where('created_at', 'LIKE', $previousDay->format('Y-m-d') . '%')
                        ->sum('amount');

                    $todayProfit = $todayRevenue - $todayExpense;
                    $previousDayProfit = $previousDayRevenue - $previousDayExpense;
                    $response["profitChart"] = [
                        "revenue" => round($todayRevenue, 2),
                        "expense" => round($todayExpense, 2),
                        "profit" => round($todayProfit, 2),
                        "direction" => $todayProfit >= $previousDayProfit ? 'positive' : 'negative'
                    ];
                }
                //Average Sales Chart
                if (auth()->user()->hasPermission('dashboard daily average sales')) {
                    $day = $today;
                    $averageSalesChart = [];
                    $total = 0;
                    for ($i = 0; $i < 7; $i++) {
                        $amount = Transaction::filter()->whereHas('voucher', function ($query) {
                            $query->where('type', '!=', 'void');
                        })
                            ->where('source', 'instore invoice revenue')
                            ->where('type', 'credit')
                            ->where('created_at', 'LIKE', $day->format('Y-m-d') . '%')
                            ->sum('amount');

                        $averageSalesChart['sales'][] = round($amount, 2);
                        $averageSalesChart['days'][] = $day->format('D');
                        $total += $amount;
                        $day = $day->subDay();
                    }
                    $response["averageSalesChart"]['sales'] = array_reverse($averageSalesChart['sales']);
                    $response["averageSalesChart"]['days'] = array_reverse($averageSalesChart['days']);
                    $response["averageSalesChart"]['averageSalePerDay'] = round(($total / 7), 2);
                    $response["averageSalesChart"]['direction'] = $todayRevenue >= ($total / 7) ? 'positive' : 'negative';
                }
                //salesThisMonth
                if (auth()->user()->hasPermission('dashboard monthly sales')) {
                    $today = Carbon::create()->now();
                    $amount = Transaction::filter()->whereHas('voucher', function ($query) {
                        $query->where('type', '!=', 'void');
                    })
                        ->where('source', 'instore invoice revenue')
                        ->where('type', 'credit')
                        ->where('created_at', 'LIKE', $today->format('Y-m') . '%')
                        ->sum('amount');

                    $count = Transaction::filter()->whereHas('voucher', function ($query) {
                        $query->where('type', '!=', 'void');
                    })
                        ->where('source', 'instore invoice revenue')
                        ->where('type', 'credit')
                        ->where('created_at', 'LIKE', $today->format('Y-m') . '%')
                        ->count();

                    $previousMonthAmount = Transaction::filter()->whereHas('voucher', function ($query) {
                        $query->where('type', '!=', 'void');
                    })
                        ->where('source', 'instore invoice revenue')
                        ->where('type', 'credit')
                        ->where('created_at', 'LIKE', $today->subMonth()->format('Y-m') . '%')
                        ->sum('amount');
                    $response["salesThisMonth"]["direction"] = $amount >= $previousMonthAmount ? "positive" : "negative";
                    $response["salesThisMonth"]["count"] = $count;
                    $response["salesThisMonth"]["amount"] = round($amount, 2);
                }
                //ordersThisMonth
                if (auth()->user()->hasPermission('dashboard monthly orders')) {
                    $today = Carbon::create()->now();
                    $amount = Order::filter()->where('created_at', 'LIKE', $today->format('Y-m') . '%')
                        ->sum('amount');
                    $count = Order::filter()->where('created_at', 'LIKE', $today->format('Y-m') . '%')
                        ->count();
                    $previousMonthAmount = Order::filter()->where('created_at', 'LIKE', $today->subMonth()->format('Y-m') . '%')
                        ->sum('amount');
                    $response["ordersThisMonth"]["direction"] = $amount >= $previousMonthAmount ? "positive" : "negative";
                    $response["ordersThisMonth"]["count"] = $count;
                    $response["ordersThisMonth"]["amount"] = round($amount, 2);
                }
                //cancelledOrderThisMonth
                if (auth()->user()->hasPermission('dashboard monthly cancelled orders')) {
                    $today = Carbon::create()->now();
                    $amount = Order::filter()->where('created_at', 'LIKE', $today->format('Y-m') . '%')
                        ->where('status', 'cancelled')
                        ->sum('amount');
                    $count = Order::filter()->where('created_at', 'LIKE', $today->format('Y-m') . '%')
                        ->where('status', 'cancelled')
                        ->count();
                    $previousMonthAmount = Order::filter()->where('created_at', 'LIKE', $today->subMonth()->format('Y-m') . '%')
                        ->where('status', 'cancelled')
                        ->sum('amount');
                    $response["cancelledThisMonth"]["direction"] = $amount >= $previousMonthAmount ? "positive" : "negative";
                    $response["cancelledThisMonth"]["count"] = $count;
                    $response["cancelledThisMonth"]["amount"] = round($amount, 2);
                }
                //refundedOrderThisMonth
                if (auth()->user()->hasPermission('dashboard monthly refunded orders')) {
                    $today = Carbon::create()->now();
                    $amount = Order::filter()->where('created_at', 'LIKE', $today->format('Y-m') . '%')
                        ->where('status', 'refunded')
                        ->sum('amount');
                    $count = Order::filter()->where('created_at', 'LIKE', $today->format('Y-m') . '%')
                        ->where('status', 'refunded')
                        ->count();
                    $previousMonthAmount = Order::filter()->where('created_at', 'LIKE', $today->subMonth()->format('Y-m') . '%')
                        ->where('status', 'refunded')
                        ->sum('amount');
                    $response["refundedThisMonth"]["direction"] = $amount >= $previousMonthAmount ? "positive" : "negative";
                    $response["refundedThisMonth"]["count"] = $count;
                    $response["refundedThisMonth"]["amount"] = round($amount, 2);
                }
                //CutomersThisMonthAndTodayHeroes
                if (auth()->user()->hasPermission('dashboard monthly customers')) {
                    $today = Carbon::create()->now();
                    $thisMonthCustomers = User::filter()->whereHas('role', function ($query) {
                        $query->where('name', 'customer');
                    })
                        ->where('users.created_at', 'LIKE', $today->format('Y-m') . '%')
                        ->count();

                    $today = Carbon::create()->now();
                    $lastMonthCustomers = User::filter()->whereHas('role', function ($query) {
                        $query->where('name', 'customer');
                    })
                        ->where('users.created_at', 'LIKE', $today->subMonth()->format('Y-m') . '%')
                        ->count();
                    $today = Carbon::create()->now();
                    $todayHeroes = User::filter()->whereHas('role', function ($query) {
                        $query->where('name', 'customer');
                    })->whereHas('orders', function ($query) use ($today) {
                        $query->where('orders.created_at', 'LIKE', $today->format('Y-m-d') . '%')
                            ->where('status', 'paid');
                    })
                        ->get();

                    $response["cutomers"]["thisMonthCustomers"] = $thisMonthCustomers;
                    $response["cutomers"]["direction"] = $thisMonthCustomers >= $lastMonthCustomers ? 'positive' : 'negative';
                    $response["cutomers"]["todayHeroes"] = $todayHeroes;
                }
                //Recent Orders
                if (auth()->user()->hasPermission('dashboard recent orders')) {
                    $response["recentOrders"] = Order::filter()->orderBy('date', 'DESC')->with('user')->limit(5)->get();
                }
                $response['success'] = true;
                return response($response);
            } else {
                $products = Product::filter()->select('uid', 'name', 'purchase_price', 'unit_price', 'stock', 'code')->get();
                return response([
                    'products' => $products,
                    'success' => true
                ]);
            }
        }
        return view('dashboard.dashboard');
    }
}
