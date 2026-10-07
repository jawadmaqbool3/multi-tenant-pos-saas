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
        return $this->promotion->query()->filter()->dataTable();
    }

    public function getProducts()
    {
        return $this->product->query()->filter()->get();
    }

    public function store($request)
    {
        if (sizeof($request->product) == 0) {
            throw new NoProductsAddedException();
        }
        $promotion = $this->promotion->create([
            'name' => $request->name,
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
            'company_id' => auth()->user()->company_id
        ]);
        $promotion->refresh();
        foreach ($request->product as $key => $product) {
            $product = $this->product->query()->where('uid', $product)->first();
            $this->promotionProducts->create([
                'promotion_id' => $promotion->id,
                'product_id' => $product->id,
                'amount' => $request->amount[$key],
                'percentage' => $request->percentage[$key],
            ]);
        }
    }

    public function update($request, $promotion)
    {
        if (sizeof($request->product) == 0) {
            throw new NoProductsAddedException();
        }
        $this->promotion->query()->where('id', $promotion->id)->update([
            'name' => $request->name,
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
        ]);
        $this->promotionProducts->query()->where('promotion_id', $promotion->id)->delete();
        foreach ($request->product as $key => $product) {
            $product = $this->product->query()->where('uid', $product)->first();
            $this->promotionProducts->create([
                'promotion_id' => $promotion->id,
                'product_id' => $product->id,
                'amount' => $request->amount[$key],
                'percentage' => $request->percentage[$key],
            ]);
        }
    }

    public function destroy($promotion)
    {
        $promotion->delete();
    }
}
