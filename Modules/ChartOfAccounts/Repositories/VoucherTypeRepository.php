<?php

namespace Modules\ChartOfAccounts\Repositories;

use App\Repositories\BaseRepository;

use Modules\ChartOfAccounts\Models\VoucherType;

class VoucherTypeRepository extends BaseRepository
{
    public function __construct()
    {
        parent::__construct(new VoucherType());

        $this->dataTableAddColumns = [
            'action' => function ($voucherType) {
                $btns = [];
                if (auth()->user()->hasPermission('edit voucher type')) {
                    $btns[] = '<a data-bs-target="#edit_modal" data-bs-toggle="modal" data-base-url="' . route('voucher-types.update', $voucherType->uid) . '" data-modal_id="' . $voucherType->uid . '" data-modal_name="' . $voucherType->name . '" data-modal_title="Edit ' . $voucherType->name . '?" class="mx-1 float-end btn btn-icon btn-bg-light btn-active-color-primary btn-sm me-1 modal-button">'
                        . $this->dt->editIconSvg() . '</a>';
                }
                if (auth()->user()->hasPermission('delete voucher type')) {
                    $btns[] = $this->dt->deleteButton($voucherType->uid, $voucherType->name, '', 'data-modal_short="' . $voucherType->short . '"');
                }
                return $this->dt->actionButtons($btns);
            },
        ];
    }
}
