<?php

namespace Modules\Inventory\Repositories;

use App\Repositories\BaseRepository;
use App\Support\Icon;
use Illuminate\Support\Facades\DB;

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
                    $btns[] = '<a title="generate bar-code" target="_blank" href="' . route('print.barcode', $product->uid) . '" class="mx-1 float-end btn btn-icon btn-bg-light btn-active-color-primary btn-sm modal-button">'
                        . Icon::svg('qr-code', 16) .
                        '</a>';
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

    /**
     * Products with stock between 1 and 9 (the "low stock" threshold used
     * across the UI).
     *
     * @param  int  $companyId
     * @return int
     */
    public function lowStockCount($companyId)
    {
        return $this->query()
            ->where('company_id', $companyId)
            ->where('stock', '>', 0)
            ->where('stock', '<', 10)
            ->count();
    }

    /**
     * Total products for a company — the count that consumes the plan's
     * `products` limit.
     *
     * @param  int  $companyId
     * @return int
     */
    public function countForCompany($companyId)
    {
        return $this->query()->where('company_id', $companyId)->count();
    }

    /**
     * Products with zero stock.
     *
     * @param  int  $companyId
     * @return int
     */
    public function outOfStockCount($companyId)
    {
        return $this->query()
            ->where('company_id', $companyId)
            ->where('stock', 0)
            ->count();
    }

    /**
     * Inventory value (stock x purchase price) grouped by category. Products
     * with no category are bucketed under "Uncategorised".
     *
     * @param  int  $companyId
     * @return \Illuminate\Support\Collection
     */
    public function stockValueByCategory($companyId)
    {
        $withCategory = $this->query()
            ->selectRaw('
                COALESCE(categories.name, "Uncategorised") as category,
                SUM(products.stock * products.purchase_price) as value
            ')
            ->leftJoin('product_categories', 'product_categories.product_id', '=', 'products.id')
            ->leftJoin('categories', 'categories.id', '=', 'product_categories.category_id')
            ->where('products.company_id', $companyId)
            ->groupBy(DB::raw('COALESCE(categories.name, "Uncategorised")'))
            ->orderByDesc('value')
            ->get();

        return $withCategory;
    }

    /**
     * Total inventory value (stock x purchase price) for a company.
     *
     * @param  int  $companyId
     * @return float
     */
    public function stockValue($companyId)
    {
        return (float) $this->query()
            ->where('company_id', $companyId)
            ->selectRaw('COALESCE(SUM(stock * purchase_price), 0) as value')
            ->value('value');
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
