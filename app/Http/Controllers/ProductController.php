<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Services\ProductService;
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
        return view('products.index');
    }

    public function create()
    {
        $categories = $this->service->getCategories();
        $productImageDirectory = $this->service->getProductImageDirectory();
        return view('products.create', compact('categories', 'productImageDirectory'));
    }

    public function store(Request $request)
    {
        $this->validate($request, [
            'name' => 'required',
            'thumbnail' => 'mimes:jpeg,jpg,bmp,png|max:2000',
            'status' => 'required',
        ]);
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
        return view('products.edit', compact('product', 'categories', 'productImageDirectory'));
    }

    public function update(Request $request, Product $product)
    {
        DB::beginTransaction();
        try {
            if ($request->thumbnail) {
                $this->validate($request, [
                    "thumbnail" => "required|mimes:jpeg,jpg,bmp,png|max:2000"
                ]);
            }
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
        return view('products.barcode', compact('product'));
    }
}
