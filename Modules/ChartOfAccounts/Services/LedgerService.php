<?php

namespace Modules\ChartOfAccounts\Services;

use App\Exceptions\InvalidFieldException;
use Modules\ChartOfAccounts\Repositories\TransactionRepository;

class LedgerService
{
    protected $transactions;
    protected $accounts;
    protected $vouchers;

    public function __construct(
        TransactionRepository $transactions,
        ChatOfAccountService $accounts,
        VoucherService $vouchers
    ) {
        $this->transactions = $transactions;
        $this->accounts = $accounts;
        $this->vouchers = $vouchers;
    }

    /**
     * The account tree used to populate the ledger account selector.
     *
     * @return \Illuminate\Support\Collection
     */
    public function getAccounts()
    {
        return $this->accounts->getAccounts();
    }

    /**
     * Build a ledger for the selected account over an optional date window.
     * The selected account may be any level; a parent account aggregates its
     * descendant Level 4 (postable) accounts.
     *
     * @param  string  $accountUid
     * @param  string|null  $from
     * @param  string|null  $to
     * @return array
     */
    public function getLedger($accountUid, $from = null, $to = null)
    {
        $companyId = optional(auth()->user())->company_id;
        $selected = $this->findAccount($this->getAccounts(), $accountUid);

        if (!$selected) {
            throw new InvalidFieldException('account');
        }

        $accountIds = $selected['level4_ids'];
        $accountType = $selected['level1']->name;

        $opening = $this->openingBalance($accountIds, $accountType, $companyId, $from);
        $entries = $this->buildEntries($accountIds, $accountType, $companyId, $from, $to, $opening);

        return [
            'account' => $selected['account'],
            'level' => $selected['level'],
            'account_type' => $accountType,
            'opening' => $opening,
            'entries' => $entries,
            'closing' => $entries->isEmpty() ? $opening : (float) $entries->last()['balance'],
            'from' => $from,
            'to' => $to,
        ];
    }

    /**
     * @param  array<int, int>  $accountIds
     * @param  string  $accountType
     * @param  int|null  $companyId
     * @param  string|null  $from
     * @return float
     */
    protected function openingBalance(array $accountIds, $accountType, $companyId, $from)
    {
        // With no start date the window begins at the earliest transaction, so
        // there is nothing posted before it.
        if (!$from) {
            return 0.0;
        }

        $totals = $this->transactions->ledgerOpeningTotals($accountIds, $companyId, $from);

        if (!$totals) {
            return 0.0;
        }

        return $this->vouchers->signedAmount($accountType, 'debit', (float) $totals->debit)
            + $this->vouchers->signedAmount($accountType, 'credit', (float) $totals->credit);
    }

    /**
     * @param  array<int, int>  $accountIds
     * @param  string  $accountType
     * @param  int|null  $companyId
     * @param  string|null  $from
     * @param  string|null  $to
     * @param  float  $opening
     * @return \Illuminate\Support\Collection
     */
    protected function buildEntries(array $accountIds, $accountType, $companyId, $from, $to, $opening)
    {
        $running = $opening;

        return $this->transactions->ledgerEntries($accountIds, $companyId, $from, $to)
            ->map(function ($row) use ($accountType, &$running) {
                $running += $this->vouchers->signedAmount($accountType, $row->type, (float) $row->amount);

                return [
                    'date' => $row->date,
                    'voucher_no' => $row->voucher_no,
                    'voucher_type' => $row->voucher_type_short,
                    'description' => $row->description ?: $row->voucher_description,
                    'debit' => $row->type === 'debit' ? (float) $row->amount : 0.0,
                    'credit' => $row->type === 'credit' ? (float) $row->amount : 0.0,
                    'balance' => $running,
                ];
            });
    }

    /**
     * Locate an account anywhere in the tree and collect its postable
     * descendant Level 4 ids.
     *
     * @param  \Illuminate\Support\Collection  $tree
     * @param  string  $uid
     * @return array|null
     */
    protected function findAccount($tree, $uid)
    {
        foreach ($tree as $level1) {
            if ($level1->uid === $uid) {
                return [
                    'account' => $level1,
                    'level' => 'level1',
                    'level1' => $level1,
                    'level4_ids' => $this->collectFromLevel1($level1),
                ];
            }

            foreach ($level1->level2 as $level2) {
                if ($level2->uid === $uid) {
                    return [
                        'account' => $level2,
                        'level' => 'level2',
                        'level1' => $level1,
                        'level4_ids' => $this->collectFromLevel2($level2),
                    ];
                }

                foreach ($level2->level3 as $level3) {
                    if ($level3->uid === $uid) {
                        return [
                            'account' => $level3,
                            'level' => 'level3',
                            'level1' => $level1,
                            'level4_ids' => $this->collectFromLevel3($level3),
                        ];
                    }

                    foreach ($level3->level4 as $level4) {
                        if ($level4->uid === $uid) {
                            return [
                                'account' => $level4,
                                'level' => 'level4',
                                'level1' => $level1,
                                'level4_ids' => [$level4->id],
                            ];
                        }
                    }
                }
            }
        }

        return null;
    }

    /**
     * @return array<int, int>
     */
    protected function collectFromLevel1($level1)
    {
        $ids = [];

        foreach ($level1->level2 as $level2) {
            $ids = array_merge($ids, $this->collectFromLevel2($level2));
        }

        return $ids;
    }

    /**
     * @return array<int, int>
     */
    protected function collectFromLevel2($level2)
    {
        $ids = [];

        foreach ($level2->level3 as $level3) {
            $ids = array_merge($ids, $this->collectFromLevel3($level3));
        }

        return $ids;
    }

    /**
     * @return array<int, int>
     */
    protected function collectFromLevel3($level3)
    {
        $ids = [];

        foreach ($level3->level4 as $level4) {
            $ids[] = $level4->id;
        }

        return $ids;
    }
}
