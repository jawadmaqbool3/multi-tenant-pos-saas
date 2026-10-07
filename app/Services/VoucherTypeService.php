<?php

namespace App\Services;

use App\Repositories\AccountLevel1Repository;
use App\Repositories\VoucherTypeRepository;

class VoucherTypeService
{
    protected $voucherTypes;
    protected $accountLevel1;

    public function __construct(VoucherTypeRepository $voucherTypes, AccountLevel1Repository $accountLevel1)
    {
        $this->voucherTypes = $voucherTypes;
        $this->accountLevel1 = $accountLevel1;
    }

    public function getVoucherTypes()
    {
        return $this->voucherTypes->query()->filter()->dataTable();
    }

    public function getAccounts()
    {
        return $this->accountLevel1->query()->filter()->get();
    }

    public function store($request)
    {
        return $this->voucherTypes->create([
            'name' => $request->name,
            'short' => $request->short,
            'company_id' => auth()->user()->company_id
        ]);
    }

    public function update($request, $voucherType)
    {
        return $this->voucherTypes->query()->where('id', $voucherType->id)->update([
            'name' => $request->name,
            'short' => $request->short
        ]);
    }

    public function delete($voucherType)
    {
        return $this->voucherTypes->delete($voucherType->id);
    }
}
