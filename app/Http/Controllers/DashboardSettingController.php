<?php

namespace App\Http\Controllers;

use App\Core\Helper;
use App\Exceptions\CustomException;
use App\Models\Category;
use App\Models\DashboardSetting;
use App\Models\Feature;
use App\Models\Product;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardSettingController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    private $sliderImageDimanensions;
    private $sliderImagesDirectory;
    public function __construct()
    {
        $this->setSliderImageDimensions(1920, 300);
        $this->sliderImagesDirectory = "assets/media/public_dashboard/slider/images/";
    }

    public function index()
    {
        $products = Product::with('feature')->filter()->get();
        $categories = Category::with('feature')->filter()->get();
        $sliderImagesDirectory = $this->sliderImagesDirectory;
        $dashboardSetting = DashboardSetting::filter()->first();
        $sliderImages = [];
        if ($dashboardSetting) {
            foreach (json_decode($dashboardSetting->slider_images) as $image) {
                $sliderImages[] = $image->image;
            }
        }
        return view('settings.publicDashboardSettings', compact('products', 'sliderImagesDirectory', 'categories', 'sliderImages'));
    }



    public function setSliderImageDimensions($width, $height)
    {
        $this->sliderImageDimanensions = [
            'width' => $width,
            'height' => $height,
        ];
    }
    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\DashboardSetting  $dashboardSetting
     * @return \Illuminate\Http\Response
     */
    public function show(DashboardSetting $dashboardSetting)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\DashboardSetting  $dashboardSetting
     * @return \Illuminate\Http\Response
     */
    public function edit(DashboardSetting $dashboardSetting)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\DashboardSetting  $dashboardSetting
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request)
    {

        try {
            $categories = Category::whereIn('uid', $request->featuredCategories ?? [])->filter()->get();
            $products = Product::whereIn('uid', $request->featuredProducts ?? [])->filter()->get();
            Feature::filter()->delete();
            foreach ($categories as $category) {
                Feature::filter()->updateOrCreate([
                    'company_id' => auth()->user()->company_id,
                    'type' => 'category',
                    'feature_id' => $category->id
                ]);
            }
            foreach ($products as $product) {
                Feature::filter()->updateOrCreate([
                    'company_id' => auth()->user()->company_id,
                    'type' => 'product',
                    'feature_id' => $product->id,
                ]);
            }
            $images = [];
            $settings = DashboardSetting::filter()->first();
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
                $settings = DashboardSetting::filter()->update([
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
                $settings = DashboardSetting::create([
                    'company_id' => auth()->user()->company_id,
                    'slider_images' => json_encode($json)
                ]);
            }
            DB::commit();
            return response([
                'success' => true,
                'reload' => true,
                'message' => 'Settings Saved',
            ]);
        } catch (CustomException $e) {
            DB::rollBack();
            return response([
                $e->getLevel() => true,
                'message' => $e->getMessage(),
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
     * @param  \App\Models\DashboardSetting  $dashboardSetting
     * @return \Illuminate\Http\Response
     */
    public function destroy(DashboardSetting $dashboardSetting)
    {
        Helper::deleteExcept([
            'files' => json_decode($dashboardSetting->slider_images),
            'exceptions' => [],
            'path' => $this->sliderImagesDirectory,
        ]);
    }
}
