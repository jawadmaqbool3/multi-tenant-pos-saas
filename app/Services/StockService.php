<?php

namespace App\Services;

use App\Repositories\ProductRepository;

class StockService
{
    protected $products;

    public function __construct(ProductRepository $products)
    {
        $this->products = $products;
    }

    public function getStockTable($request)
    {
        $table = $this->products->query();
        if ($request->status) {
            switch ($request->status) {
                case 'in_stock':
                    $table = $table->filter()->where('stock', '>=', 10);
                    break;
                case 'out_of_stock':
                    $table = $table->filter()->where('stock', '=', 0);
                    break;
                case 'low_stock':
                    $table = $table->filter()->where('stock', '<', 10)->where('stock', '>', 0);
                    break;
            }
        }
        return $table->filter()->stockTable();
    }

    public function getPrintProducts($type)
    {
        if ($type == 'all') {
            $products = $this->products->query()->filter()->get();
            $status = 'All Products';
        } elseif ($type == 'in_stock') {
            $status = 'In Stock';
            $products = $this->products->query()->filter()->where('stock', '>=', 10)->get();
        } elseif ($type == 'low_stock') {
            $status = 'Low Stock';
            $products = $this->products->query()->filter()->where('stock', '<', 10)->where('stock', '>', 0)->get();
        } else {
            $status = 'Out of Stock';
            $products = $this->products->query()->filter()->where('stock', 0)->get();
        }
        return ['products' => $products, 'status' => $status];
    }
}
