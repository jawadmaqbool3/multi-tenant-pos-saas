@extends('layouts.basic')
@section('title', 'My Profile')
@section('content')
    <div id="kt_app_toolbar" class="app-toolbar py-3 py-lg-6">
        <div id="kt_app_toolbar_container" class="app-container container-xxl d-flex flex-stack">
            <div class="page-title d-flex flex-column justify-content-center flex-wrap me-3">
                <h1 class="page-heading d-flex text-dark fw-bold fs-3 flex-column justify-content-center my-0">My Profile
                </h1>
                <x-breadcrums :links="[
                    'Home' => route('dashboard'),
                    'My Profile' => false,
                ]" />
            </div>
        </div>
    </div>
    <div id="kt_app_content" class="app-content flex-column-fluid">
        <div class="app-container container-xxl">
            <form data-ajax="true" action="{{ route('profile.update') }}"
                class="form d-flex flex-column flex-lg-row" method="POST" enctype="multipart/form-data">
                @method('PUT')
                <div class="d-flex flex-column gap-7 gap-lg-10 w-100 w-lg-300px mb-7 me-lg-10">
                    <div class="card card-flush py-4">
                        <div class="card-header">
                            <div class="card-title">
                                <h2>Avatar</h2>
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
                                    style="background-image: url('{{ $user->image ? asset('assets/media/users/profile/') . '/' . $user->image : '' }}')">
                                </div>
                                <label class="btn btn-icon btn-circle btn-active-color-primary w-25px h-25px bg-body shadow"
                                    data-kt-image-input-action="change" data-bs-toggle="tooltip" title="Change avatar">
                                    <x-icon name="pencil" size="14" />
                                    <input type="file" name="image" accept=".png, .jpg, .jpeg" />
                                    <input type="hidden" name="avatar_remove" />
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
                            <div class="text-muted fs-7">Only *.png, *.jpg and *.jpeg image files are accepted.</div>
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
                        <div class="card-body my-3">
                            <div class="fv-row">
                                <label class="required form-label">Name</label>
                                <input type="text" name="name" value="{{ $user->name }}" class="form-control mb-2"
                                    placeholder="Your name" required />
                            </div>
                        </div>
                        <div class="card-body pt-0">
                            <div class="fv-row">
                                <label class="required form-label">Email</label>
                                <input type="email" name="email" value="{{ $user->email }}" class="form-control mb-2"
                                    placeholder="Your email" required />
                            </div>
                        </div>
                        <div class="card-body pt-0">
                            <div class="row">
                                <div class="col-md-6 my-3">
                                    <label class="form-label">New Password</label>
                                    <input type="password" name="password" class="form-control mb-2"
                                        placeholder="Leave blank to keep current" autocomplete="new-password" />
                                    <div class="text-muted fs-7">Leave blank to keep your current password.</div>
                                </div>
                                <div class="col-md-6 my-3">
                                    <label class="form-label">Confirm New Password</label>
                                    <input type="password" name="co_password" class="form-control mb-2"
                                        placeholder="Repeat new password" autocomplete="new-password" />
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="d-flex justify-content-end">
                        <a href="{{ route('dashboard') }}" class="btn btn-light me-5">Cancel</a>
                        <button type="submit" class="btn btn-primary">
                            <span class="indicator-label">Save Changes</span>
                            <span class="indicator-progress">Please wait...
                                <span class="spinner-border spinner-border-sm align-middle ms-2"></span></span>
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
@endsection
