<?php

namespace Modules\Inventory\Http\Controllers;

use App\Http\Controllers\Controller;

use Modules\Inventory\Models\Product;
use Modules\Inventory\Http\Requests\StoreProductRequest;
use Modules\Inventory\Http\Requests\UpdateProductRequest;
use Modules\Inventory\Services\ProductService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProductController extends Controller
{
    protected $service;

    public function __construct(ProductService $service = null)
    {
        $this->service = $service ?? app(ProductService::class);
    }

    public function index(Request $request)
    {
        if ($request->ajax()) {
            return $this->service->getProducts($request);
        }
        return view('inventory::products.index');
    }

    public function create()
    {
        $categories = $this->service->getCategories();
        $productImageDirectory = $this->service->getProductImageDirectory();
        return view('inventory::products.create', compact('categories', 'productImageDirectory'));
    }

    public function store(StoreProductRequest $request)
    {
        DB::beginTransaction();
        try {
            $this->service->store($request);
            DB::commit();
            return response([
                'success' => true,
                'message' => 'New Product Added',
                'redirect' => true,
                'url' => route('products.index'),
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function show(Product $product)
    {
        //
    }

    public function edit(Product $product)
    {
        $categories = $this->service->getCategories();
        $productImageDirectory = $this->service->getProductImageDirectory();
        return view('inventory::products.edit', compact('product', 'categories', 'productImageDirectory'));
    }

    public function update(UpdateProductRequest $request, Product $product)
    {
        DB::beginTransaction();
        try {
            $this->service->update($request, $product);
            DB::commit();
            return response([
                'success' => true,
                'message' => 'Product Updated',
                'redirect' => true,
                'url' => route('products.index'),
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function destroy(Product $product)
    {
        $this->service->delete($product);
        return response([
            'success' => true,
            'message' => 'Product Deleted',
            'table_reload' => true,
        ]);
    }

    public function print_barcode(Product $product)
    {
        return view('inventory::products.barcode', compact('product'));
    }
}
