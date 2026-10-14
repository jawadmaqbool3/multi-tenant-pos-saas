<?php

namespace Modules\ChartOfAccounts\Repositories;

use App\Repositories\BaseRepository;
use App\Support\Icon;
use Carbon\Carbon;

use Modules\ChartOfAccounts\Models\Voucher;

class VoucherRepository extends BaseRepository
{
    protected $dataTableWith = ['creator'];
    protected $dataTableRawColumns = ['action', 'image', 'status'];

    public function __construct()
    {
        parent::__construct(new Voucher());

        $this->dataTableEditColumns = [
            'date' => function ($voucher) {
                return Carbon::createFromFormat('Y-m-d', $voucher->date)->format('d M, Y');
            },
        ];
        $this->dataTableAddColumns = [
            'action' => function ($voucher) {
                return $this->dt->actionButtons([
                    '<a href="' . route('print.voucher', $voucher->uid) . '" class="mx-1 float-end btn btn-icon btn-bg-light btn-active-color-primary btn-sm me-1">'
                        . Icon::svg('printer', 16) .
                    '</a>'
                ]);
            },
        ];
    }

    public function supplierLedgerDataTable($query)
    {
        return $this->dataTable($query, [
            'editColumns' => [
                'description' => function ($voucher) {
                    return ucwords($voucher->description);
                },
                'amount' => function ($voucher) {
                    if ($voucher->voucherType->short == 'APV') {
                        return "<span class='text-danger'>" . $voucher->amount . "</span>";
                    } elseif ($voucher->voucherType->short == 'BPV') {
                        return "<span class='text-success'>" . $voucher->amount . "</span>";
                    }
                },
            ],
            'rawColumns' => ['amount'],
        ]);
    }
}
