<?php

namespace Modules\Supplier\Repositories;

use App\Repositories\BaseRepository;

use Modules\Supplier\Models\Supplier;

class SupplierRepository extends BaseRepository
{
    protected $dataTableRawColumns = ['action', 'thumbnail', 'status', 'name'];

    public function __construct()
    {
        parent::__construct(new Supplier());

        $this->dataTableEditColumns = [
            'thumbnail' => function ($supplier) {
                return $this->dt->thumbnail('assets/media/suppliers/thumbs', $supplier->thumbnail);
            },
            'name' => function ($supplier) {
                return '<a href="' . route('suppliers.show', $supplier->uid) . '">' . ucwords($supplier->name) . '</button>';
            },
            'status' => function ($supplier) {
                return $this->dt->statusButton($supplier->status, $supplier->status == 'active' ? 'btn-success' : 'btn-warning');
            },
        ];
        $this->dataTableAddColumns = [
            'action' => function ($supplier) {
                $btns = [];
                if (auth()->user()->hasPermission('edit supplier')) {
                    $btns[] = $this->dt->editButton(route('suppliers.edit', $supplier->uid), 'edit supplier');
                }
                if (auth()->user()->hasPermission('delete supplier')) {
                    $btns[] = $this->dt->deleteButton($supplier->uid, $supplier->name, 'delete supplier');
                }
                if (auth()->user()->hasPermission('pay supplier')) {
                    $btns[] = '<a href="#" title="Pay Supplier" data-bs-target="#pay_supplier_modal" data-bs-toggle="modal" data-modal_balance="' . $supplier->balance . '" data-modal_max="' . $supplier->balance . '" data-modal_id="' . $supplier->uid . '" data-modal_name="' . $supplier->name . '" data-modal_title="Pay to ' . $supplier->name . '?" class="mx-1 float-end btn btn-icon btn-bg-light btn-active-color-primary btn-sm modal-button">
                        <span class="svg-icon svg-icon-3"><span class="bi bi-check fs-3"></span></span>
                    </a>';
                }
                return $this->dt->actionButtons($btns);
            },
        ];
    }
}
