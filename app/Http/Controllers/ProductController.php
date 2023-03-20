<?php

namespace App\Http\Controllers;

use App\Core\Helper;
use App\Models\Category;
use App\Models\Feature;
use App\Models\Product;
use App\Models\ProductCategories;
use App\Models\Supplier;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;


class ProductController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */


    private $thumbWidth;
    private $thumbHeight;
    private $productImageDirectory;
    public function __construct()
    {
        $this->thumbWidth = 200;
        $this->thumbHeight = 200;
        $this->productImageDirectory = 'assets/media/products/images/';
    }

    public function index(Request $request)
    {
        if ($request->ajax()) {
            if ($request->supplier) {
                return Product::filter()->whereHas('purchaseOrders.supplier', function ($query) use ($request) {
                    $query->where('uid', $request->supplier);
                })->with('purchaseOrders.supplier')->dataTable();
            }
            return Product::filter()->dataTable();
        }
        return view('products.index');
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $categories = Category::filter()->get();
        $productImageDirectory = $this->productImageDirectory;
        return view('products.create', compact('categories', 'productImageDirectory'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $products = Product::count();
        $counts = str_pad(($products + 1), 4, '0', STR_PAD_LEFT);
        $code = Carbon::now()->format('dmy') . $counts;
        $this->validate($request, [
            'name' => 'required',
            'thumbnail' => 'mimes:jpeg,jpg,bmp,png|max:2000',
            'status' => 'required',
        ]);
        DB::beginTransaction();
        try {
            $thumbnail = Helper::uploadfile([
                'file' => $request->file('thumbnail'),
                'path' => 'assets/media/products/thumbs/',
                'width' => $this->thumbWidth,
                'height' => $this->thumbHeight,
            ]);
            $images = [];
            if (is_array($request->images)) {
                foreach ($request->images as $key => $image) {
                    $images[] = Helper::uploadfile([
                        'file' => $image,
                        'path' => $this->productImageDirectory,
                        'width' => $this->thumbWidth,
                        'height' => $this->thumbHeight,
                    ]);
                }
            }
            $product = Product::create([
                'name' => $request->name,
                'thumbnail' => $thumbnail,
                'description' => $request->description,
                'meta_title' => $request->meta_title,
                'meta_keywords' => $request->meta_keywords,
                'tags' => $request->tags,
                'meta_description' => $request->meta_description,
                'published_date' => $request->published_date,
                'unit_price' => $request->unit_price,
                'status' => $request->status,
                'company_id' => auth()->user()->company_id,
                'images' => json_encode($images),
                'slug' => Helper::hyphened($request->name),
                'code' => $code
            ]);
            $product->refresh();
            if (is_array($request->categories)) {
                $categories = Category::whereIn('uid', $request->categories)->select('id')->get();
                foreach ($categories as $key => $category) {
                    ProductCategories::create([
                        'product_id' => $product->id,
                        'category_id' => $category->id,
                    ]);
                }
            }
            if ($request->featured) {
                Feature::filter()->updateOrCreate([
                    'type' => 'product',
                    'company_id' => auth()->user()->company_id,
                    'feature_id' => $product->id
                ]);
            } else {
                Feature::filter()->where([
                    'type' => 'product',
                    'feature_id' => $product->id
                ])->delete();
            }
            DB::commit();
            return response([
                'success' => true,
                'message' => 'New Product Added',
                'redirect' => true,
                'url' => route('products.index'),
            ]);
        } catch (Exception $e) {
            DB::rollBack();
            return response([
                'error' => true,
                'message' => 'Something went wrong',
                'console' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Product  $product
     * @return \Illuminate\Http\Response
     */
    public function show(Product $product)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Product  $product
     * @return \Illuminate\Http\Response
     */
    public function edit(Product $product)
    {
        $categories = Category::filter()->get();
        $productImageDirectory = $this->productImageDirectory;
        return view('products.edit', compact('product', 'categories', 'productImageDirectory'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Product  $product
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Product $product)
    {
        DB::beginTransaction();
        try {
            $thumbnail = $product->thumbnail;
            if ($request->thumbnail) {
                $this->validate($request,[
                    "thumbnail" => "required|mimes:jpeg,jpg,bmp,png|max:2000"
                ]);
                Helper::deleteExcept([
                    'files' => [$product->thumbnail],
                    'exceptions' => [],
                    'path' => 'assets/media/products/thumbs/'
                ]);
                $thumbnail = Helper::uploadfile([
                    'file' => $request->file('thumbnail'),
                    'path' => 'assets/media/products/thumbs/',
                    'width' => $this->thumbWidth,
                    'height' => $this->thumbHeight,
                ]);
            }
            $images = [];
            if (is_array($request->images)) {
                foreach ($request->images as $key => $image) {
                    $images[] = Helper::uploadfile([
                        'file' => $image,
                        'path' => $this->productImageDirectory,
                        'width' => $this->thumbWidth,
                        'height' => $this->thumbHeight,
                    ]);
                }
            }
            if (is_array($request->old_images)) {
                $images = array_merge($images, $request->old_images);
            }
            Helper::deleteExcept([
                'files' => json_decode($product->images),
                'exceptions' => $request->old_images,
                'path' => $this->productImageDirectory
            ]);
            Product::where('id', $product->id)->update([
                'name' => $request->name,
                'thumbnail' => $thumbnail,
                'description' => $request->description,
                'unit_price' => $request->unit_price,
                'meta_title' => $request->meta_title,
                'meta_keywords' => $request->meta_keywords,
                'tags' => $request->tags,
                'meta_description' => $request->meta_description,
                'published_date' => $request->published_date,
                'status' => $request->status,
                'company_id' => auth()->user()->company_id,
                'slug' => Helper::hyphened($request->name),
                'images' => json_encode($images),
            ]);
            ProductCategories::where('product_id', $product->id)->delete();
            if (is_array($request->categories)) {
                $categories = Category::whereIn('uid', $request->categories)->select('id')->get();
                foreach ($categories as $key => $category) {
                    ProductCategories::create([
                        'product_id' => $product->id,
                        'category_id' => $category->id,
                    ]);
                }
            }
            if ($request->featured) {
                Feature::filter()->updateOrCreate([
                    'type' => 'product',
                    'company_id' => auth()->user()->company_id,
                    'feature_id' => $product->id
                ]);
            } else {
                Feature::filter()->where([
                    'type' => 'product',
                    'feature_id' => $product->id
                ])->delete();
            }
            DB::commit();
            return response([
                'success' => true,
                'message' => 'Product Updated',
                'redirect' => true,
                'url' => route('products.index'),
            ]);
        } catch (Exception $e) {
            DB::rollBack();
            return response([
                'error' => true,
                'message' => 'Something went wrong',
                'console' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Product  $product
     * @return \Illuminate\Http\Response
     */
    public function destroy(Product $product)
    {
        Helper::deleteExcept([
            'files' => json_decode($product->images),
            'exceptions' => [],
            'path' => $this->productImageDirectory
        ]);
        Helper::deleteExcept([
            'files' => [$product->thumbnail],
            'exceptions' => [],
            'path' => 'assets/media/products/thumbs/'
        ]);
        $product->delete();
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
