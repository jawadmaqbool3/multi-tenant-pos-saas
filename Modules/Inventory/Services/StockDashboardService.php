<?php

namespace Modules\Inventory\Services;

use Modules\Inventory\Repositories\ProductRepository;

class StockDashboardService
{
    protected $products;

    public function __construct(ProductRepository $products)
    {
        $this->products = $products;
    }

    /**
     * Number of low-stock products (1-9 units left).
     */
    public function lowCard(): array
    {
        return [
            'value' => $this->products->lowStockCount($this->companyId()),
            'format' => 'number',
            'secondary_label' => 'Below 10 units',
            'link' => ['label' => 'View stock', 'url' => route('stocks.index')],
        ];
    }

    /**
     * Number of out-of-stock products.
     */
    public function outCard(): array
    {
        return [
            'value' => $this->products->outOfStockCount($this->companyId()),
            'format' => 'number',
            'secondary_label' => 'Zero units',
            'link' => ['label' => 'View stock', 'url' => route('stocks.index')],
        ];
    }

    /**
     * Inventory value grouped by category.
     */
    public function valueByCategoryChart(): array
    {
        $companyId = $this->companyId();

        $rows = $this->products->stockValueByCategory($companyId);

        $categories = [];
        $data = [];

        foreach ($rows as $row) {
            $categories[] = $row->category;
            $data[] = round((float) $row->value, 2);
        }

        return [
            'chart_type' => 'bar',
            'series' => [['name' => 'Stock value', 'data' => $data]],
            'categories' => $categories,
            'kpi' => [
                'value' => round($this->products->stockValue($companyId), 2),
                'format' => 'currency',
                'label' => 'Inventory value',
            ],
        ];
    }

    /**
     * @return int|null
     */
    protected function companyId()
    {
        return auth()->user() ? auth()->user()->company_id : null;
    }
}
