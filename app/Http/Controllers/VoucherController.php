<?php

namespace App\Http\Controllers;

use App\Models\AccountLevel4;
use App\Models\Transaction;
use App\Models\Voucher;
use Illuminate\Http\Request;

class VoucherController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */

    protected $voucher;
    protected $rules = [
        'Asset' => [
            'debit' => '+',
            'credit' => '-'
        ],
        'Liability' => [
            'debit' => '-',
            'credit' => '+'
        ],
        'Equity' => [
            'debit' => '-',
            'credit' => '+'
        ],
        'Revenue' => [
            'debit' => '-',
            'credit' => '+'
        ],
        'Expense' => [
            'debit' => '+',
            'credit' => '-'
        ]
    ];

    public function  getOperation($account, $type)
    {
        return $this->rules[$account->name][$type];
    }
    public function __construct($voucher = null)
    {
        $this->voucher = $voucher;
    }

    public function index(Request $request)
    {
        if ($request->ajax()) {
            return Voucher::filter()->dataTable();
        }
        return view('vouchers.index');
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Voucher  $voucher
     * @return \Illuminate\Http\Response
     */
    public function show(Voucher $voucher)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Voucher  $voucher
     * @return \Illuminate\Http\Response
     */
    public function edit(Voucher $voucher)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Voucher  $voucher
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Voucher $voucher)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Voucher  $voucher
     * @return \Illuminate\Http\Response
     */
    public function destroy(Voucher $voucher)
    {
        //
    }


    public function voidVoucher()
    {
        $voucher = $this->voucher;
        foreach ($voucher->transactions as $transaction) {
            $account = $transaction->account;
            $level1 = $account->level3->level2->level1;
            $operation = $this->getOperation($level1, $transaction->type);
            //Void works in reverse
            if ($operation == '+') {
                AccountLevel4::where('id', $account->id)->update([
                    'balance' => $account->balance - $transaction->amount
                ]);
            } elseif ($operation == '-') {
                AccountLevel4::where('id', $account->id)->update([
                    'balance' => $account->balance + $transaction->amount
                ]);
            }
        }
        Voucher::where('id', $voucher->id)->update([
            'type' => 'void'
        ]);
        return true;
    }
}
