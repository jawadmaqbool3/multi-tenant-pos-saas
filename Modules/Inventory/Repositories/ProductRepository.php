<?php

namespace Modules\Inventory\Repositories;

use App\Repositories\BaseRepository;

use Modules\Inventory\Models\Product;

class ProductRepository extends BaseRepository
{
    protected $dataTableRawColumns = ['action', 'thumbnail', 'status'];

    public function __construct()
    {
        parent::__construct(new Product());

        $this->dataTableEditColumns = [
            'thumbnail' => function ($product) {
                return $this->dt->thumbnail('assets/media/products/thumbs', $product->thumbnail);
            },
            'status' => function ($product) {
                return $this->dt->statusBadge(...$this->productStatus($product));
            },
        ];
        $this->dataTableAddColumns = [
            'action' => function ($product) {
                $btns = [];
                if (auth()->user()->hasPermission('edit product')) {
                    $btns[] = $this->dt->editButton(route('products.edit', $product->uid), 'edit product');
                }
                if (auth()->user()->hasPermission('delete product') && $product->stockHistories()->count() == 0) {
                    $btns[] = $this->dt->deleteButton($product->uid, $product->name, 'delete product');
                }
                if (auth()->user()->hasPermission('print barcode')) {
                    $btns[] = '<a title="generate bar-code" target="_blank" href="' . route('print.barcode', $product->uid) . '" class="mx-1 float-end btn btn-icon btn-bg-light btn-active-color-primary btn-sm modal-button">
                        <span class="svg-icon svg-icon-3"><span class="bi bi-qr-code"></span></span>
                    </a>';
                }
                return $this->dt->actionButtons($btns);
            },
        ];
    }

    public function stockTable($query)
    {
        return $this->dataTable($query, [
            'editColumns' => [
                'stock' => function ($product) {
                    if ($product->stock == 0) {
                        return '<span class="badge py-3 px-4 fs-7 badge-light-danger">Out of Stock</span>';
                    } elseif ($product->stock < 10) {
                        return '<span class="badge py-3 px-4 fs-7 badge-light-warning">Low Stock</span>';
                    }
                    return '<span class="badge py-3 px-4 fs-7 badge-light-primary">In Stock</span>';
                },
            ],
            'addColumns' => [
                'quantity' => function ($product) {
                    return $product->stock . ' PCS';
                },
            ],
            'rawColumns' => ['stock'],
        ]);
    }

    protected function productStatus($product)
    {
        if ($product->status == 'published') {
            return [$product->status, 'btn-success'];
        } elseif ($product->status == 'unpublished') {
            return [$product->status, 'btn-warning'];
        }
        return [$product->status . ' (' . date('d M, Y h:i A', strtotime($product->published_date)) . ')', 'btn-danger'];
    }
}
