@extends('layouts.basic')
@section('content')
    <div class="mb-5 mb-xl-8">
        <div class="card-header border-0 py-5">
            <h3 class="card-title align-items-start flex-column w-100">
                <span class="card-label fw-bold fs-3 mb-1">Public Dashboard Settings</span>
            </h3>
            <div class="row">
                <div class="col-md-12">
                    <x-breadcrums :links="[
                        'Home' => route('dashboard'),
                        'Public Dashboard Settings' => false,
                    ]" />
                </div>
            </div>
        </div>
        <form data-ajax="true" action="{{ route('public.dashboard.settings.update') }}" method="POST">

            <div class="row">
                <div class="col-md-6">
                    <div class="card card-flush py-4">
                        <div class="card-body pt-0">
                            <div class="row">
                                <div class="mb-3">
                                    <label for="" class="form-label">Featured Products</label>
                                    <select name="featuredProducts[]" multiple id=""
                                        class="_select2 form-control form-control-solid">
                                        @foreach ($products as $product)
                                            <option @if ($product->feature) selected @endif
                                                value="{{ $product->uid }}">{{ $product->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card card-flush py-4">
                        <div class="card-body pt-0">
                            <div class="row">
                                <div class="mb-3">
                                    <label for="" class="form-label">Featured Categories</label>
                                    <select name="featuredCategories[]" multiple id=""
                                        class="_select2 form-control form-control-solid">
                                        @foreach ($categories as $category)
                                            <option @if ($category->feature) selected @endif
                                                value="{{ $category->uid }}">{{ $category->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-12">
                    <x-media-uploader :directory="$sliderImagesDirectory" title="Slider Images" :images="@$sliderImages ?? []"
                        tag-line="Supported JPG, JPEG, PNG" />
                </div>
                <div class="col-md-12 mt-3">
                    <div class="d-flex justify-content-end">
                        <a href="{{ route('products.index') }}" id="kt_ecommerce_add_product_cancel"
                            class="btn btn-light me-5">Cancel</a>
                        <button type="submit" id="kt_ecommerce_add_category_submit" class="btn btn-primary">
                            <span class="indicator-label">Save Changes</span>
                            <span class="indicator-progress">Please wait...
                                <span class="spinner-border spinner-border-sm align-middle ms-2"></span></span>
                        </button>
                    </div>
                </div>
            </div>
        </form>
    </div>
@endsection

@section('jsSection')
    @include('settings._js')
    @include('custome_js.media')
@endsection
