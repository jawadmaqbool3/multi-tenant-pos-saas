@extends('layouts.basic')
@section('content')
    <div class="mb-5 mb-xl-8">
        <div class="card-header border-0 py-5">
            <h3 class="card-title align-items-start flex-column w-100">
                <span class="card-label fw-bold fs-3 mb-1">General Settings</span>
            </h3>
            <div class="row">
                <div class="col-md-12">
                    <x-breadcrums :links="[
                        'Home' => route('dashboard'),
                        'General Settings' => false,
                    ]" />
                </div>
            </div>
        </div>
        <form data-ajax="true" action="{{ route('general.settings.update', $setting->uid) }}" method="POST">

            <div class="row">
                <div class="col-sm-4">
                    <div class="card card-flush py-4">
                        <div class="card-header">
                            <div class="card-title">
                                <h2>Logo</h2>
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
                                    style="background-image: url('@logo(medium)')">
                                </div>
                                <label class="btn btn-icon btn-circle btn-active-color-primary w-25px h-25px bg-body shadow"
                                    data-kt-image-input-action="change" data-bs-toggle="tooltip" title="Change avatar">
                                    <x-icon name="pencil" size="14" />
                                    <input type="file" name="logo" accept=".png, .jpg, .jpeg" />
                                </label>
                                <span class="btn btn-icon btn-circle btn-active-color-primary w-25px h-25px bg-body shadow"
                                    data-kt-image-input-action="cancel" data-bs-toggle="tooltip" title="Cancel avatar">
                                    <x-icon name="x" size="16" />
                                </span>
                                <span class="btn btn-icon btn-circle btn-active-color-primary w-25px h-25px bg-body shadow"
                                    data-kt-image-input-action="remove" data-bs-toggle="tooltip" title="Remove avatar">
                                    <x-icon name="x" size="16" />
                                </span>
                            </div>
                            <div class="text-muted fs-7">Set the logo for app. Only *.png, *.jpg and *.jpeg
                                image files are accepted</div>
                        </div>
                    </div>
                    <div class="card card-flush p-4 my-5">
                        <div class="col-md-12">
                            <div class="form-check form-switch form-check-custom form-check-solid">
                                <input name="maintenanceMode" @if ($setting->maintenance_mode) checked @endif
                                    class="form-check-input" type="checkbox" value="1" id="maintenanceMode">
                                <label class="form-check-label" for="maintenanceMode">
                                    Maintenance mode
                                </label>
                            </div>
                        </div>
                    </div>
                   

                </div>
                <div class="col-sm-8">
                    <div class="card card-flush py-4">

                        <div class="card-body pt-0">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="name" class="form-label">Name</label>
                                        <input type="text" id="name" name="name" value="{{ $setting->name }}"
                                            class="form-control" placeholder="App Name">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="email" class="form-label">Email</label>
                                        <input type="text" id="email" name="email" value="{{ $setting->email }}"
                                            class="form-control" placeholder="Email">
                                    </div>
                                </div>
                                <div class="col-md-12">
                                    <div class="mb-3">
                                        <label for="address" class="form-label">Address</label>
                                        <textarea type="text" id="address" name="address" class="form-control">{{ $setting->address }}</textarea>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="contact" class="form-label">Contact</label>
                                        <input type="text" id="contact" name="contact" value="{{ $setting->contact }}"
                                            class="form-control" placeholder="Contact">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="appURL" class="form-label">URL</label>
                                        <input type="text" id="appURL" name="appURL" value="{{ $setting->url }}"
                                            class="form-control" placeholder="App URL">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="appURL" class="form-label">Facebook</label>
                                        <input type="text" id="facebook" name="facebook"
                                            value="{{ $setting->facebook }}" class="form-control"
                                            placeholder="Facebook Link">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="instagram" class="form-label">Instagram</label>
                                        <input type="text" id="instagram" name="instagram"
                                            value="{{ $setting->instagram }}" class="form-control"
                                            placeholder="Instagram Link">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="whatsapp" class="form-label">Whatsapp</label>
                                        <input type="text" id="whatsapp" name="whatsapp"
                                            value="{{ $setting->whatsapp }}" class="form-control"
                                            placeholder="Whatsapp Link">
                                    </div>
                                </div>
                                <div class="col-md-12 mt-5 mb-2">
                                    <div class="d-flex justify-content-end">
                                        <a href="{{ route('dashboard') }}" id="kt_ecommerce_add_product_cancel"
                                            class="btn btn-light me-5">Cancel</a>
                                        <button type="submit" id="" class="btn btn-primary">
                                            <span class="indicator-label">Save Changes</span>
                                            <span class="indicator-progress">Please wait...
                                                <span
                                                    class="spinner-border spinner-border-sm align-middle ms-2"></span></span>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    @if (
                        $setting->company &&
                            auth()->user()->hasPermission('create company'))
                        <div class="card card-flush py-4 my-5">

                            <div class="card-body pt-0">
                                <div class="row">
                                    <div class="col-md-12">
                                        <x-permission-selector :company="@$setting->company" />
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </form>
    </div>
@endsection

@section('jsSection')
    @include('settings._js')
@endsection
