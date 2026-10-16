<?php

namespace Modules\ChartOfAccounts\Repositories;

use App\Repositories\BaseRepository;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

use Modules\ChartOfAccounts\Models\Transaction;

class TransactionRepository extends BaseRepository
{
    public function __construct()
    {
        parent::__construct(new Transaction());
    }

    /**
     * Revenue vs expense per month (YYYY-MM) for a company over a window of
     * months, anchored on the current month. Company scoping mirrors the
     * `Transaction::scopeFilter()` join (voucher -> voucher_type -> company).
     *
     * @param  int  $companyId
     * @param  int  $monthCount
     * @return \Illuminate\Support\Collection
     */
    public function revenueExpenseByMonth($companyId, $monthCount = 12)
    {
        $from = Carbon::now()->startOfMonth()->subMonths($monthCount - 1);

        return $this->query()
            ->selectRaw("
                DATE_FORMAT(vouchers.date, '%Y-%m') as month,
                COALESCE(SUM(CASE WHEN transactions.source = 'instore invoice revenue' AND transactions.type = 'credit' THEN transactions.amount ELSE 0 END), 0) as revenue,
                COALESCE(SUM(CASE WHEN transactions.source = 'instore invoice expense' AND transactions.type = 'debit' THEN transactions.amount ELSE 0 END), 0) as expense
            ")
            ->join('vouchers', 'vouchers.id', '=', 'transactions.voucher_id')
            ->join('voucher_types', 'voucher_types.id', '=', 'vouchers.voucher_type_id')
            ->where('voucher_types.company_id', $companyId)
            ->where('vouchers.type', '!=', 'void')
            ->where('vouchers.date', '>=', $from->toDateString())
            ->groupBy(DB::raw("DATE_FORMAT(vouchers.date, '%Y-%m')"))
            ->get();
    }
}
