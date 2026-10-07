<?php

namespace App\Services;

use App\Core\Helper;
use App\Repositories\CategoryRepository;
use App\Repositories\DashboardSettingRepository;
use App\Repositories\FeatureRepository;
use App\Repositories\ProductRepository;

class DashboardSettingService
{
    protected $dashboardSetting;
    protected $product;
    protected $category;
    protected $feature;

    private $sliderImageDimanensions;
    private $sliderImagesDirectory;

    public function __construct(
        DashboardSettingRepository $dashboardSetting,
        ProductRepository $product,
        CategoryRepository $category,
        FeatureRepository $feature
    ) {
        $this->dashboardSetting = $dashboardSetting;
        $this->product = $product;
        $this->category = $category;
        $this->feature = $feature;
        $this->setSliderImageDimensions(1920, 300);
        $this->sliderImagesDirectory = "assets/media/public_dashboard/slider/images/";
    }

    public function setSliderImageDimensions($width, $height)
    {
        $this->sliderImageDimanensions = [
            'width' => $width,
            'height' => $height,
        ];
    }

    public function getIndexData()
    {
        $products = $this->product->query()->with('feature')->filter()->get();
        $categories = $this->category->query()->with('feature')->filter()->get();
        $sliderImagesDirectory = $this->sliderImagesDirectory;
        $dashboardSetting = $this->dashboardSetting->query()->filter()->first();
        $sliderImages = [];
        if ($dashboardSetting) {
            foreach (json_decode($dashboardSetting->slider_images) as $image) {
                $sliderImages[] = $image->image;
            }
        }
        return compact('products', 'sliderImagesDirectory', 'categories', 'sliderImages');
    }

    public function update($request)
    {
        $categories = $this->category->query()->whereIn('uid', $request->featuredCategories ?? [])->filter()->get();
        $products = $this->product->query()->whereIn('uid', $request->featuredProducts ?? [])->filter()->get();
        $this->feature->query()->filter()->delete();
        foreach ($categories as $category) {
            $this->feature->query()->filter()->updateOrCreate([
                'company_id' => auth()->user()->company_id,
                'type' => 'category',
                'feature_id' => $category->id
            ]);
        }
        foreach ($products as $product) {
            $this->feature->query()->filter()->updateOrCreate([
                'company_id' => auth()->user()->company_id,
                'type' => 'product',
                'feature_id' => $product->id,
            ]);
        }
        $images = [];
        $settings = $this->dashboardSetting->query()->filter()->first();
        if (is_array($request->images)) {
            foreach ($request->images as $key => $image) {
                $images[] = Helper::uploadfile([
                    'file' => $image,
                    'path' => $this->sliderImagesDirectory,
                    'width' => $this->sliderImageDimanensions["width"],
                    'height' => $this->sliderImageDimanensions["height"],
                ]);
            }
        }

        $json = [];

        if ($settings) {
            $imagesToBeDeleted = [];
            $oldImages = json_decode($settings->slider_images);


            foreach ($oldImages as $key => $image) {
                $imagesToBeDeleted[] = $image->image;
                if ($request->old_images) {
                    foreach ($request->old_images as $key => $_image) {
                        if ($image->image == $_image) {
                            $json[] = $image;
                        }
                    }
                }
            }


            Helper::deleteExcept([
                'files' => $imagesToBeDeleted,
                'exceptions' => $request->old_images ?? [],
                'path' => $this->sliderImagesDirectory,
            ]);

            foreach ($images as $key => $image) {
                $json[] = [
                    "image" => $image,
                    "title" => "Some title",
                    "tagline" => "Some tagline",
                    "url" => "/home"
                ];
            }
            $settings = $this->dashboardSetting->query()->filter()->update([
                'slider_images' => json_encode($json)
            ]);
        } else {
            foreach ($images as $key => $image) {
                $json[] = [
                    "image" => $image,
                    "title" => "Some title",
                    "tagline" => "Some tagline",
                    "url" => "/home"
                ];
            }
            $settings = $this->dashboardSetting->create([
                'company_id' => auth()->user()->company_id,
                'slider_images' => json_encode($json)
            ]);
        }
    }

    public function destroy($dashboardSetting)
    {
        Helper::deleteExcept([
            'files' => json_decode($dashboardSetting->slider_images),
            'exceptions' => [],
            'path' => $this->sliderImagesDirectory,
        ]);
    }
}
