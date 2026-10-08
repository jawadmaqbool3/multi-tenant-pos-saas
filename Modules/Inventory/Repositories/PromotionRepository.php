<?php

namespace Modules\Inventory\Repositories;

use App\Repositories\BaseRepository;

use Modules\Inventory\Models\Promotion;

class PromotionRepository extends BaseRepository
{
    public function __construct()
    {
        parent::__construct(new Promotion());

        $this->dataTableAddColumns = [
            'action' => function ($promotion) {
                $btns = [];
                if (auth()->user()->hasPermission('edit promotion')) {
                    $btns[] = $this->editButton(route('promotions.edit', $promotion->uid), 'edit promotion');
                }
                if (auth()->user()->hasPermission('delete promotion')) {
                    $btns[] = $this->deleteButton($promotion->uid, $promotion->name, 'delete promotion');
                }
                if (auth()->user()->hasPermission('print barcode')) {
                    $btns[] = '<a title="generate bar-code" target="_blank" href="' . route('print.barcode', $promotion->uid) . '" class="mx-1 float-end btn btn-icon btn-bg-light btn-active-color-primary btn-sm modal-button">
                        <span class="svg-icon svg-icon-3"><span class="bi bi-qr-code"></span></span>
                    </a>';
                }
                return $this->actionButtons($btns);
            },
        ];
    }
}
