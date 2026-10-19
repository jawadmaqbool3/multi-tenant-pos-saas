<?php

namespace Modules\ChartOfAccounts\Repositories;

use App\Repositories\BaseRepository;
use App\Support\Icon;
use Carbon\Carbon;

use Modules\ChartOfAccounts\Models\Voucher;

class VoucherRepository extends BaseRepository
{
    protected $dataTableWith = ['creator'];
    protected $dataTableRawColumns = ['action', 'image', 'status', 'type'];

    public function __construct()
    {
        parent::__construct(new Voucher());

        $this->dataTableEditColumns = [
            'date' => function ($voucher) {
                return Carbon::createFromFormat('Y-m-d', $voucher->date)->format('d M, Y');
            },
            'type' => function ($voucher) {
                if ($voucher->type === 'void') {
                    return '<span class="badge badge-light-warning">Void</span>';
                }
                return '<span class="badge badge-light-primary">' . ucfirst($voucher->type) . '</span>';
            },
        ];
        $this->dataTableAddColumns = [
            'action' => function ($voucher) {
                $btns = [];

                if (auth()->user()->hasPermission('delete voucher') && $voucher->type != 'void') {
                    $btns[] = '<a href="#" class="mx-1 float-end btn btn-icon btn-bg-light btn-active-color-danger btn-sm me-1 modal-button" '
                        . 'data-bs-target="#void_modal" data-bs-toggle="modal" '
                        . 'data-modal_id="' . $voucher->uid . '" '
                        . 'data-modal_title="Void ' . $voucher->voucher_no . '?" '
                        . 'data-modal_name="' . $voucher->voucher_no . '">'
                        . Icon::svg('x-circle', 16) .
                        '</a>';
                }

                $btns[] = '<a href="' . route('print.voucher', $voucher->uid) . '" class="mx-1 float-end btn btn-icon btn-bg-light btn-active-color-primary btn-sm me-1">'
                    . Icon::svg('printer', 16) .
                    '</a>';

                return $this->dt->actionButtons($btns);
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
