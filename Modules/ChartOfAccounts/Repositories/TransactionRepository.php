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
     * Ledger entries for a set of Level 4 account ids, optionally constrained
     * to a date window. Void vouchers are excluded. The voucher columns are
     * selected directly (instead of relying on the inverted `voucher` hasMany
     * relation) so callers get flat rows.
     *
     * @param  array<int, int>  $accountIds
     * @param  int|null  $companyId
     * @param  string|null  $from
     * @param  string|null  $to
     * @return \Illuminate\Support\Collection
     */
    public function ledgerEntries(array $accountIds, $companyId = null, $from = null, $to = null)
    {
        if (empty($accountIds)) {
            return collect();
        }

        return $this->query()
            ->select([
                'transactions.amount',
                'transactions.type',
                'transactions.description',
                'vouchers.voucher_no',
                'vouchers.date',
                'vouchers.description as voucher_description',
                'voucher_types.short as voucher_type_short',
            ])
            ->join('vouchers', 'vouchers.id', '=', 'transactions.voucher_id')
            ->join('voucher_types', 'voucher_types.id', '=', 'vouchers.voucher_type_id')
            ->whereIn('transactions.account_id', $accountIds)
            ->where('vouchers.type', '!=', 'void')
            ->when($companyId, function ($query) use ($companyId) {
                return $query->where('voucher_types.company_id', $companyId);
            })
            ->when($from, function ($query) use ($from) {
                return $query->whereDate('vouchers.date', '>=', $from);
            })
            ->when($to, function ($query) use ($to) {
                return $query->whereDate('vouchers.date', '<=', $to);
            })
            ->orderBy('vouchers.date')
            ->orderBy('transactions.id')
            ->get();
    }

    /**
     * Total debit and credit posted to a set of Level 4 account ids strictly
     * before the given date (the ledger's opening balance).
     *
     * @param  array<int, int>  $accountIds
     * @param  int|null  $companyId
     * @param  string|null  $before
     * @return object|null
     */
    public function ledgerOpeningTotals(array $accountIds, $companyId = null, $before = null)
    {
        if (empty($accountIds)) {
            return null;
        }

        return $this->query()
            ->selectRaw("COALESCE(SUM(CASE WHEN transactions.type = 'debit' THEN transactions.amount ELSE 0 END), 0) as debit")
            ->selectRaw("COALESCE(SUM(CASE WHEN transactions.type = 'credit' THEN transactions.amount ELSE 0 END), 0) as credit")
            ->join('vouchers', 'vouchers.id', '=', 'transactions.voucher_id')
            ->join('voucher_types', 'voucher_types.id', '=', 'vouchers.voucher_type_id')
            ->whereIn('transactions.account_id', $accountIds)
            ->where('vouchers.type', '!=', 'void')
            ->when($companyId, function ($query) use ($companyId) {
                return $query->where('voucher_types.company_id', $companyId);
            })
            ->when($before, function ($query) use ($before) {
                return $query->whereDate('vouchers.date', '<', $before);
            })
            ->first();
    }

    /**
     * Debit/credit totals per Level 4 account id, up to (and including) an
     * optional as-of date. Used to build point-in-time reports such as the
     * balance sheet. Void vouchers are excluded.
     *
     * @param  int|null  $companyId
     * @param  string|null  $asOf
     * @return \Illuminate\Support\Collection  keyed by account_id
     */
    public function accountTotals($companyId = null, $asOf = null)
    {
        return $this->query()
            ->selectRaw('transactions.account_id')
            ->selectRaw("COALESCE(SUM(CASE WHEN transactions.type = 'debit' THEN transactions.amount ELSE 0 END), 0) as debit")
            ->selectRaw("COALESCE(SUM(CASE WHEN transactions.type = 'credit' THEN transactions.amount ELSE 0 END), 0) as credit")
            ->join('vouchers', 'vouchers.id', '=', 'transactions.voucher_id')
            ->join('voucher_types', 'voucher_types.id', '=', 'vouchers.voucher_type_id')
            ->where('vouchers.type', '!=', 'void')
            ->when($companyId, function ($query) use ($companyId) {
                return $query->where('voucher_types.company_id', $companyId);
            })
            ->when($asOf, function ($query) use ($asOf) {
                return $query->whereDate('vouchers.date', '<=', $asOf);
            })
            ->groupBy('transactions.account_id')
            ->get()
            ->keyBy('account_id');
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
