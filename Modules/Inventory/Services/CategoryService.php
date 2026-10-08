<?php

namespace Modules\Inventory\Services;

use App\Core\Helper;
use Modules\Inventory\Models\Category;
use Modules\Inventory\Repositories\CategoryRepository;
use Modules\Inventory\Repositories\FeatureRepository;

class CategoryService
{
    protected $category;
    protected $feature;

    private $thumbWidth;
    private $thumbHeight;

    public function __construct(CategoryRepository $category, FeatureRepository $feature)
    {
        $this->category = $category;
        $this->feature = $feature;
        $this->thumbWidth = 200;
        $this->thumbHeight = 200;
    }

    public function getCategories()
    {
        return $this->category->dataTable($this->category->query()->filter());
    }

    public function store($request)
    {
        $image = '';
        $thumbnail = Helper::uploadfile([
            'file' => $request->file('thumbnail'),
            'path' => 'assets/media/categories/thumbs/',
            'width' => $this->thumbWidth,
            'height' => $this->thumbHeight,
        ]);
        $category = $this->category->create([
            'name' => $request->name,
            'thumbnail' => $thumbnail,
            'description' => $request->description,
            'meta_title' => $request->meta_title,
            'meta_keywords' => $request->meta_keywords,
            'meta_description' => $request->meta_description,
            'slug' => Helper::hyphened($request->name),
            'published_date' => $request->published_date,
            'status' => $request->status,
        ]);
        $category->refresh();
        if ($request->featured) {
            $this->feature->query()->updateOrCreate([
                'type' => 'category',
                'feature_id' => $category->id
            ]);
        }
    }

    public function update($request, Category $category)
    {
        $thumbnail = $category->thumbnail;
        if ($request->thumbnail) {
            Helper::deleteExcept([
                'files' => [$category->thumbnail],
                'exceptions' => [],
                'path' => 'assets/media/categories/thumbs/'
            ]);
            $thumbnail = Helper::uploadfile([
                'file' => $request->file('thumbnail'),
                'path' => 'assets/media/categories/thumbs/',
                'width' => $this->thumbWidth,
                'height' => $this->thumbHeight,
            ]);
        }
        $this->category->query()->where('id', $category->id)->update([
            'name' => $request->name,
            'thumbnail' => $thumbnail,
            'description' => $request->description,
            'meta_title' => $request->meta_title,
            'meta_keywords' => $request->meta_keywords,
            'meta_description' => $request->meta_description,
            'slug' => Helper::hyphened($request->name),
            'published_date' => $request->published_date,
            'status' => $request->status,
        ]);
        if ($request->featured) {
            $this->feature->query()->updateOrCreate([
                'type' => 'category',
                'feature_id' => $category->id
            ]);
        }
    }

    public function getByCompanyId($companyId)
    {
        return $this->category->query()->where('company_id', $companyId)->get();
    }

    public function destroy($category)
    {
        Helper::deleteExcept([
            'files' => [$category->thumbnail],
            'exceptions' => [],
            'path' => 'assets/media/categories/thumbs/'
        ]);
        $category->delete();
    }
}
