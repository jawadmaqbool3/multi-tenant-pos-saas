<?php

namespace Modules\Inventory\Services;

use App\Exceptions\NoProductsAddedException;
use Modules\Inventory\Repositories\ProductRepository;
use Modules\Inventory\Repositories\PromotionProductsRepository;
use Modules\Inventory\Repositories\PromotionRepository;

class PromotionService
{
    protected $promotion;
    protected $product;
    protected $promotionProducts;

    public function __construct(
        PromotionRepository $promotion,
        ProductRepository $product,
        PromotionProductsRepository $promotionProducts
    ) {
        $this->promotion = $promotion;
        $this->product = $product;
        $this->promotionProducts = $promotionProducts;
    }

    public function getPromotions()
    {
        return $this->promotion->dataTable($this->promotion->query()->filter());
    }

    public function getProducts()
    {
        return $this->product->query()->filter()->get();
    }

    public function store($request)
    {
        $products = $request->product;
        if (!is_array($products) || count($products) === 0) {
            throw new NoProductsAddedException();
        }
        $promotion = $this->promotion->create([
            'name' => $request->name,
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
            'company_id' => auth()->user()->company_id
        ]);
        $promotion->refresh();
        foreach ($products as $key => $productUid) {
            $product = $this->product->query()->where('uid', $productUid)->first();
            if (!$product) {
                continue;
            }
            $this->promotionProducts->create([
                'promotion_id' => $promotion->id,
                'product_id' => $product->id,
                'amount' => $request->amount[$key] ?? 0,
                'percentage' => $request->percentage[$key] ?? 0,
            ]);
        }
    }

    public function update($request, $promotion)
    {
        $products = $request->product;
        if (!is_array($products) || count($products) === 0) {
            throw new NoProductsAddedException();
        }
        $this->promotion->query()->where('id', $promotion->id)->update([
            'name' => $request->name,
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
        ]);
        $this->promotionProducts->query()->where('promotion_id', $promotion->id)->delete();
        foreach ($products as $key => $productUid) {
            $product = $this->product->query()->where('uid', $productUid)->first();
            if (!$product) {
                continue;
            }
            $this->promotionProducts->create([
                'promotion_id' => $promotion->id,
                'product_id' => $product->id,
                'amount' => $request->amount[$key] ?? 0,
                'percentage' => $request->percentage[$key] ?? 0,
            ]);
        }
    }

    public function destroy($promotion)
    {
        $promotion->delete();
    }
}
