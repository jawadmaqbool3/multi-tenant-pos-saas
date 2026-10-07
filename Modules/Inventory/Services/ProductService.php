<?php

namespace Modules\Inventory\Services;

use App\Core\Helper;
use Modules\Inventory\Repositories\CategoryRepository;
use Modules\Inventory\Repositories\FeatureRepository;
use Modules\Inventory\Repositories\ProductCategoriesRepository;
use Modules\Inventory\Repositories\ProductRepository;
use Carbon\Carbon;

class ProductService
{
    protected $products;
    protected $categories;
    protected $features;
    protected $productCategories;
    protected $thumbWidth = 200;
    protected $thumbHeight = 200;
    protected $productImageDirectory = 'assets/media/products/images/';

    public function __construct(ProductRepository $products, CategoryRepository $categories, FeatureRepository $features, ProductCategoriesRepository $productCategories)
    {
        $this->products = $products;
        $this->categories = $categories;
        $this->features = $features;
        $this->productCategories = $productCategories;
    }


    public function getProductByUid($uid)
    {
        return $this->products->query()->where('uid', $uid)->first();
    }

    public function getProductById($id)
    {
        return $this->products->query()->where('id', $id)->first();
    }

    public function updateStock($id, $stock)
    {
        return $this->products->query()->where('id', $id)->update(['stock' => $stock]);
    }

    public function updateStockByUid($uid, $stock)
    {
        return $this->products->query()->where('uid', $uid)->update(['stock' => $stock]);
    }

    public function updateDetails($id, $data)
    {
        return $this->products->query()->where('id', $id)->update($data);
    }

    public function getProductImageDirectory()
    {
        return $this->productImageDirectory;
    }

    public function getProducts($request)
    {
        if ($request->supplier) {
            return $this->products->query()->filter()->whereHas('purchaseOrders.supplier', function ($query) use ($request) {
                $query->where('uid', $request->supplier);
            })->with('purchaseOrders.supplier')->dataTable();
        }
        return $this->products->query()->filter()->dataTable();
    }

    public function getCategories()
    {
        return $this->categories->query()->filter()->get();
    }

    public function generateCode()
    {
        $products = $this->products->query()->count();
        $counts = str_pad(($products + 1), 4, '0', STR_PAD_LEFT);
        return Carbon::now()->format('dmy') . $counts;
    }

    public function store($request)
    {
        $code = $this->generateCode();
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
        $product = $this->products->create([
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
            $categories = $this->categories->query()->whereIn('uid', $request->categories)->select('id')->get();
            foreach ($categories as $key => $category) {
                $this->productCategories->create([
                    'product_id' => $product->id,
                    'category_id' => $category->id,
                ]);
            }
        }
        if ($request->featured) {
            $this->features->query()->filter()->updateOrCreate([
                'type' => 'product',
                'company_id' => auth()->user()->company_id,
                'feature_id' => $product->id
            ]);
        } else {
            $this->features->query()->filter()->where([
                'type' => 'product',
                'feature_id' => $product->id
            ])->delete();
        }
        return $product;
    }

    public function update($request, $product)
    {
        $thumbnail = $product->thumbnail;
        if ($request->thumbnail) {
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
        $this->products->query()->where('id', $product->id)->update([
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
        $this->productCategories->query()->where('product_id', $product->id)->delete();
        if (is_array($request->categories)) {
            $categories = $this->categories->query()->whereIn('uid', $request->categories)->select('id')->get();
            foreach ($categories as $key => $category) {
                $this->productCategories->create([
                    'product_id' => $product->id,
                    'category_id' => $category->id,
                ]);
            }
        }
        if ($request->featured) {
            $this->features->query()->filter()->updateOrCreate([
                'type' => 'product',
                'company_id' => auth()->user()->company_id,
                'feature_id' => $product->id
            ]);
        } else {
            $this->features->query()->filter()->where([
                'type' => 'product',
                'feature_id' => $product->id
            ])->delete();
        }
    }

    public function delete($product)
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
    }
}
