<div class="d-flex flex-column gap-7 gap-lg-10 w-100 w-lg-300px mb-7 me-lg-10">
    <div class="card card-flush py-4">
        <div class="card-header">
            <div class="card-title">
                <h2>Thumbnail</h2>
            </div>
        </div>
        <div class="card-body text-center pt-0">
            <style>
                .image-input-placeholder {
                    background-image: url('{{ asset('assets/media/svg/files/blank-image.svg') }}');
                }

                [data-theme="dark"] .image-input-placeholder {
                    background-image: url('{{ asset('assets/media/svg/files/blank-image-dark.svg') }}');
                }
            </style>
            <div class="image-input image-input-empty image-input-outline image-input-placeholder mb-3"
                data-kt-image-input="true">
                <div class="image-input-wrapper w-150px h-150px"
                    style="background-image: url('{{ @$product ? asset('/assets/media/products/thumbs/') . '/' . $product->thumbnail : '' }}')">
                </div>
                <label class="btn btn-icon btn-circle btn-active-color-primary w-25px h-25px bg-body shadow"
                    data-kt-image-input-action="change" data-bs-toggle="tooltip" title="Change avatar">
                    <i class="bi bi-pencil-fill fs-7"></i>
                    <input type="file" name="thumbnail" accept=".png, .jpg, .jpeg" />
                    <input type="hidden" name="avatar_remove" />
                </label>
                <span class="btn btn-icon btn-circle btn-active-color-primary w-25px h-25px bg-body shadow"
                    data-kt-image-input-action="cancel" data-bs-toggle="tooltip" title="Cancel avatar">
                    <i class="bi bi-x fs-2"></i>
                </span>
                <span class="btn btn-icon btn-circle btn-active-color-primary w-25px h-25px bg-body shadow"
                    data-kt-image-input-action="remove" data-bs-toggle="tooltip" title="Remove avatar">
                    <i class="bi bi-x fs-2"></i>
                </span>
            </div>
            <div class="text-muted fs-7">Set the category thumbnail image. Only *.png, *.jpg and *.jpeg
                image files are accepted</div>
        </div>
    </div>
    <div class="card card-flush py-4">
        <div class="card-header">
            <div class="card-title">
                <h2>Status</h2>
            </div>
            <div class="card-toolbar">
                <div class="rounded-circle 
                @if (@$product->status == 'unpublished') bg-danger
                @elseif(@$product->status == 'scheduled')
                bg-warning
                @else
                bg-success @endif          
                 w-15px h-15px"
                    id="status_sign">
                </div>
            </div>
        </div>
        <div class="card-body pt-0">
            <select name="status" class="form-select mb-2" data-control="select2" data-hide-search="true"
                data-placeholder="Select an option" id="status" onchange="">
                <option></option>
                <option data-status-sign="bg-success" {{ @$product->status == 'published' ? 'selected' : '' }}
                    value="published" selected="selected">
                    Published</option>
                <option data-status-sign="bg-warning" {{ @$product->status == 'scheduled' ? 'selected' : '' }}
                    value="scheduled">Scheduled</option>
                <option data-status-sign="bg-danger" {{ @$product->status == 'unpublished' ? 'selected' : '' }}
                    value="unpublished">Unpublished
                </option>
            </select>
            <div class="text-muted fs-7">Set the category status.</div>
            <div class="
            @if (@$product->status != 'scheduled') d-none @endif
            mt-10 date-selector">
                <label for="" class="form-label">Select
                    publishing date and time</label>
                <input class="form-control form-control-solid" placeholder="Pick date rage" id="date_picker"
                    name="published_date" value="{{ @$product->published_date }}" />
            </div>
        </div>
    </div>
    <div class="card card-flush py-4">
        <div class="card-header">
            <div class="card-title">
                <h2>Product Details</h2>
            </div>
        </div>
        <div class="card-body pt-0">
            <label class="form-label">Categories</label>
            <select name="categories[]" class="form-select mb-2" data-control="select2"
                data-placeholder="Select an option" data-allow-clear="true" multiple="multiple">
                <option></option>
                @foreach ($categories as $category)
                    <option
                        @isset($product)
                        @foreach ($product->categories as $_category)
                            @if ($category->id == $_category->id)
                                selected
                                @break
                            @endif @endforeach
                            @endisset
                        value="{{ $category->uid }}">{{ $category->name }}</option>
                @endforeach
            </select>
            <div class="text-muted fs-7 mb-7">Add product to a category.</div>
            @can('create category')
                <a href="{{ route('categories.create') }}" class="btn btn-light-primary btn-sm mb-10">
                    <span class="svg-icon svg-icon-2">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none"
                            xmlns="http://www.w3.org/2000/svg">
                            <rect opacity="0.5" x="11" y="18" width="12" height="2" rx="1"
                                transform="rotate(-90 11 18)" fill="currentColor"></rect>
                            <rect x="6" y="11" width="12" height="2" rx="1"
                                fill="currentColor"></rect>
                        </svg>
                    </span>
                    Create new category</a>
            @endcan
            <label class="form-label d-block">Tags</label>
            <input class="form-control" value="{{ @$product->tags }}" id="input_tag" name="tags" />
            <div class="text-muted fs-7">Add tags to a product.</div>
        </div>
    </div>
    <div class="card card-flush p-4">
        <div class="col-md-12">
            <div class="form-check form-switch form-check-custom form-check-solid">
                <input name="featured" @if (@$product->feature) checked @endif class="form-check-input"
                    type="checkbox" value="1" id="featured">
                <label class="form-check-label" for="featured">
                    Featured
                </label>
            </div>
        </div>
    </div>
</div>
<div class="d-flex flex-column flex-row-fluid gap-7 gap-lg-10">
    <div class="card card-flush py-4">
        <div class="card-header">
            <div class="card-title">
                <h2>General</h2>
            </div>
        </div>
        <div class="card-body pt-0">
            <div class="mb-10 row">
                <div class="col-md-9">
                    <label class="required form-label">Product Name</label>
                    <input type="text" name="name" value="{{ @$product->name }}" class="form-control mb-2"
                        placeholder="Product name" />
                    <div class="text-muted fs-7">A product name is required and recommended to be unique.
                    </div>
                </div>
                <div class="col-md-3">
                    <label class=" form-label">Price</label>
                    <input type="number" step="0.1" name="unit_price" value="{{ @$product->unit_price }}"
                        class="form-control mb-2" placeholder="Product name" />
                </div>
            </div>
            <div>
                <label class="form-label">Description</label>
                <div id="quill_1" data-name="description" class="quill-input min-h-200px mb-2">
                    {!! @$product->description !!}</div>
                <div class="text-muted fs-7">Set a description to the category for better visibility.</div>
            </div>
        </div>
    </div>
    <div class="card card-flush py-4">
        <div class="card-header">
            <div class="card-title">
                <h2>Meta Options</h2>
            </div>
        </div>
        <div class="card-body pt-0">
            <div class="mb-10">
                <label class="form-label">Meta Tag Title</label>
                <input value="{{ @$product->meta_title }}" type="text" class="form-control mb-2"
                    name="meta_title" placeholder="Meta tag name" />
                <div class="text-muted fs-7">Set a meta tag title. Recommended to be simple and precise
                    keywords.</div>
            </div>
            <div class="mb-10">
                <label class="form-label">Meta Tag Description</label>
                <textarea id="" name="meta_description" class="form-control form-control-solid min-h-100px mb-2">
                    {!! @$product->meta_description !!}</textarea>
                <div class="text-muted fs-7">Set a meta tag description to the category for increased SEO
                    ranking.</div>
            </div>
            <div>
                <label class="form-label">Meta Tag Keywords</label>
                <input id="" name="meta_keywords" class="form-control mb-2"
                    value="{{ @$product->meta_keywords }}" />
                <div class="text-muted fs-7">Set a list of keywords that the category is related to.
                    Separate the keywords by adding a comma
                    <code>,</code>between each keyword.
                </div>
            </div>
        </div>
    </div>

    <x-media-uploader :directory="$productImageDirectory" title="Product Images" :images="json_decode(@$product->images) ?? []"
        tag-line="Supported JPG, JPEG, PNG" />

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
