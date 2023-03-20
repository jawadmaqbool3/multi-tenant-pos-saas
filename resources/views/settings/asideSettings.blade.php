@extends('layouts.basic')
@section('content')
    <style>
        #aside_links .btn-reverse {
            display: none;
        }

        #aside_links input {
            display: none;
        }

        #categories .dropzone i {
            margin-top: 5px !important;
        }


        #categories .dropzone .title {
            display: none;
        }

        #categories .dropzone .link {
            display: flex !important;
        }

        #categories .dropzone input {
            padding: 0px 10px !important;
        }

        .link {
            cursor: move;
        }
    </style>
    <div class="mb-5 mb-xl-8">
        <div class="card-header border-0 py-5">
            <h3 class="card-title align-items-start flex-column w-100">
                <span class="card-label fw-bold fs-3 mb-1">Aside Settings</span>
            </h3>
            <div class="row">
                <div class="col-md-12">
                    <x-breadcrums :links="[
                        'Home' => route('dashboard'),
                        'Aside Settings' => false,
                    ]" />
                </div>
            </div>
        </div>
        <form id="aside_form" action="{{ route('aside.settings.update') }}" method="POST">

            <div class="row">
                <div class="col-sm-4">
                    <div class="card card-flush py-4">
                        <div class="card-header">
                            <div class="card-title">
                                <h2>All Links</h2>
                            </div>
                        </div>
                        <div class="card-body pt-0">

                            <div class="row" id="aside_links">
                                @foreach ($menuLinks as $menuLink)
                                    @foreach ($menuLink['links'] as $link)
                                        @php
                                            $flag = true;
                                        @endphp
                                        @foreach ($settings as $category)
                                            @foreach ($category->links as $_link)
                                                @php
                                                    if ($link['link'] == $_link->link) {
                                                        $flag = false;
                                                    }
                                                @endphp
                                            @endforeach
                                        @endforeach
                                        @if ($flag)
                                            <div draggable="true" ondragstart="drag(event)"
                                                id="dragable{{ $loop->iteration }}" data-index="{{ $loop->iteration }}"
                                                data-link="{{ $link['link'] }}"
                                                data-permission="{{ $link['permission'] }}"
                                                class="col-12 p-3 fs-5 fw-bolder text-dark mt-3 border link"><i
                                                    class="bi bi-justify-right  fs-5 fw-bolder text-dark"></i>
                                                <input type="text" name="" value="{{ $link['title'] }}"
                                                    id="" class="form-control form-control-solid">
                                                <span class="title">{{ $link['title'] }}</span>
                                                <span><i
                                                        class="bi bi-x float-end fs-1 fw-bolder text-dark mt-1 btn-reverse"></i></span>
                                            </div>
                                        @endif
                                    @endforeach
                                @endforeach

                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-sm-8">
                    <div class="card card-flush py-4">
                        <div class="card-body pt-0">
                            <div class="row">
                                <div class="col-12 p-0">
                                    <button type="button" class="btn btn-primary btn-sm float-end mb-3"
                                        id="btn_add_category">
                                        <i class="fa fa-plus"></i>
                                    </button>
                                </div>
                            </div>
                            <div class="row" id="categories">
                                @foreach ($settings as $category)
                                    <div class="col-12 mt-10 category">
                                        <div class="card">
                                            <div class="card-body p-0">
                                                <div class="row">
                                                    <div class="col-5 ps-0">
                                                        <div class="mb-3">
                                                            <label for="" class="form-label">Icon</label>
                                                            <div class="d-flex">
                                                                <select data-selected="{{ $category->icon }}" name=""
                                                                    id=""
                                                                    class="form-control form-control-solid icon _select2 rounded-end-0"
                                                                    placeholder="">
                                                                </select>
                                                                <button disabled=""
                                                                    class="icon-check btn btn-secondary rounded-start-0"><i
                                                                        class="{{ $category->icon }}"></i></button>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="col-5">
                                                        <div class="mb-3">
                                                            <label for="" class="form-label">Name</label>
                                                            <input type="text" value="{{ $category->name }}"
                                                                name="" id=""
                                                                class="form-control form-control-solid name" placeholder=""
                                                                aria-describedby="helpId">
                                                        </div>
                                                    </div>
                                                    <div class="col-2 pe-0">
                                                        <div class="mb-3">
                                                            <button type="button"
                                                                class="btn mt-8 btn-danger float-end btn-remove-category">
                                                                <i class="bi bi-x"></i>
                                                            </button>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div style="border: 2px dashed #999"
                                                    class="row   rounded p-5 dropzone text-start"
                                                    id="_dropzone{{ $loop->iteration }}" ondrop="drop(event)"
                                                    ondragover="allowDrop(event)">
                                                    @foreach ($category->links as $link)
                                                        <div draggable="true" ondragstart="drag(event)"
                                                            id="_dragable{{ $loop->iteration }}" data-index="_3"
                                                            data-link="{{ $link->link }}"
                                                            data-permission="{{ $link->permission }}"
                                                            data-title="{{ $link->title }}"
                                                            class="col-12 p-3 fs-5 fw-bolder text-dark mt-3 border link">
                                                            <i class="bi bi-justify-right  fs-5 fw-bolder text-dark"></i>
                                                            <input type="text" name=""
                                                                value="{{ $link->title }}" id=""
                                                                class="form-control form-control-solid">
                                                            <span class="title">{{ $link->title }}</span>
                                                            <span><i
                                                                    class="bi bi-x float-end fs-1 fw-bolder text-dark mt-1 btn-reverse"></i></span>
                                                        </div>
                                                    @endforeach
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                            <div class="row">
                                <div class="col-12 p-0 mt-5">
                                    <a href="{{ route('aside.settings.reset') }}" class="float-start ms-2 btn btn-danger"
                                        type="button">Reset</a>
                                    <button class="float-end ms-2 btn btn-primary" type="submit">Submit</button>
                                    <a href="{{ route('dashboard') }}" class="float-end btn btn-light">Cancel</a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
@endsection




@section('jsSection')
    <script src="{{ asset('assets/js/custom/icon.js') }}"></script>
    <script>
        var iconsOptions = '';
        icons.forEach(icon => {
            iconsOptions +=
                `<option value="${icon}">${(icon.replace("bi bi-", "").replace("-", " ").toUpperCase())}</option>`;
        });

        function allowDrop(ev) {
            ev.preventDefault();
        }

        function drag(ev) {
            ev.dataTransfer.setData("text", ev.target.id);
        }

        function drop(ev) {
            ev.preventDefault();
            let element = $('#' + ev.dataTransfer.getData("text"));
            let target = $(ev.target);
            let dropZone = null;
            let orders = [];
            if ($(target).hasClass('dropzone')) {
                dropZone = target;
            } else if ($(target).parent().hasClass('dropzone')) {
                dropZone = target.parent('.dropzone');
            }
            if ($(target).hasClass('link')) {
                element.remove();
                dropZone.find('.link').each(function() {
                    if ($(target).data('index') == $(this).data('index')) {
                        orders.push(element);
                    }
                    orders.push($(this));
                });
            } else {
                dropZone.find('.link').each(function() {
                    orders.push($(this));
                });
                orders.push(element);
            }
            $(dropZone).empty();
            orders.forEach(element => {
                $(dropZone).append(element);
            });
        }
        $(document).ready(function() {


            $(document).on('click', '.btn-reverse', function() {
                let element = $(this).parents('.link');
                let dropzone = $(this).parents('.dropzone');
                $('#aside_links').append(element);

                if (dropzone.find('.link').length == 0) {
                    dropzone.html(`<h3 class="text-center">Drop Links Here</h3>`);
                }
            });
            $(document).on('click', '#btn_add_category', function() {
                createCategory();
            });


            function createCategory() {




                $('#categories').append(`
                        <div class="col-12 mt-10 category">
                            <div class="card">
                                <div class="card-body p-0">
                                    <div class="row">
                                        <div class="col-5 ps-0">
                                            <div class="mb-3">
                                                <label for="" class="form-label">Icon</label>
                                                <div class="d-flex">
                                                    <select name="" id=""
                                                    class="form-control form-control-solid icon _select2 rounded-end-0" placeholder=""
                                                    aria-describedby="helpId">
                                                    ${iconsOptions}
                                                </select>
                                                <button disabled class="icon-check btn btn-secondary rounded-start-0"><i class="bi bi-alarm-fill"></i></button>
                                                    </div>
                                            </div>
                                        </div>
                                        <div class="col-5">
                                            <div class="mb-3">
                                                <label for="" class="form-label">Name</label>
                                                <input type="text" name="" id=""
                                                    class="form-control form-control-solid name" placeholder=""
                                                    aria-describedby="helpId">
                                            </div>
                                        </div>
                                        <div class="col-2 pe-0">
                                            <div class="mb-3">
                                                <button type="button" class="btn mt-8 btn-danger float-end btn-remove-category">
                                                    <i class="bi bi-x"></i>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                    <div style="border: 2px dashed #999"
                                        class="row   rounded p-5 dropzone text-start" id="dropzone1"
                                        ondrop="drop(event)" ondragover="allowDrop(event)">
                                        <h3 class="text-center">Drop Links Here</h3>
                                    </div>
                                </div>
                            </div>
                        </div>
            `);

                $('.category:last-child ._select2').select2();
            }

            $(document).on('click', '.btn-remove-category', function() {
                let category = $(this).parents('.category');
                category.find('.link').each(function() {
                    $('#aside_links').append($(this));
                })
                category.remove();
            });

            $(document).on('change', '.icon', function() {
                $(this).siblings('.icon-check').find('i').removeAttr("class").addClass($(this).val());
            });



            $(document).on('submit', '#aside_form', function(e) {
                e.preventDefault();
                let categories = [];
                $('#categories .category').each(function() {
                    let links = [];
                    let icon = $(this).find('.icon').val();
                    let name = $(this).find('.name').val();
                    $(this).find('.link').each(function() {
                        links.push({
                            "link": $(this).data('link'),
                            "permission": $(this).data('permission'),
                            "title": $(this).find('input').val(),
                        });
                    });
                    categories.push({
                        "links": links,
                        "icon": icon,
                        "name": name
                    });
                });
                if (categories.length == 0) {
                    toastr.warning('Add some categories first.', "Warning", {
                        timeOut: 5000,
                        extendedTimeOut: 0,
                        closeButton: true,
                        closeDuration: 0
                    });
                    return;
                }
                var selector = $(this).find('button[type="submit"]');
                selector.attr('disabled', true);
                let url = $(this).prop('action');
                let method = $(this).prop('method');
                let _token = "{{ csrf_token() }}";
                let formData = new FormData();
                formData.append('_token', _token);
                formData.append('categories', JSON.stringify(categories));
                $.ajax({
                    url: url,
                    data: formData,
                    type: method,
                    processData: false,
                    cache: false,
                    contentType: false,
                    success: function(response) {
                        if (response.success) {
                            toastr.success(
                                response.message,
                                "Success", {
                                    timeOut: 5000,
                                    extendedTimeOut: 0,
                                    closeButton: true,
                                    closeDuration: 0
                                }
                            );
                        } else if (response.error) {

                            toastr.error(
                                response.message,
                                "Error", {
                                    timeOut: 5000,
                                    extendedTimeOut: 0,
                                    closeButton: true,
                                    closeDuration: 0
                                }
                            );
                        } else if (response.warning) {
                            toastr.warning(
                                response.message,
                                "warning", {
                                    timeOut: 5000,
                                    extendedTimeOut: 0,
                                    closeButton: true,
                                    closeDuration: 0
                                }
                            );
                        }
                        if (response.reload) {
                            window.location.reload();
                        }
                        if (response.redirect) {
                            window.location.href = response.url;
                        }
                        if (response.table_reload) {
                            const tableReload = new Event("table_reload");
                            document.dispatchEvent(tableReload)
                        }
                        if (response.event) {
                            const event = new Event(response.event.name);
                            document.dispatchEvent(event)
                        }
                    },
                    error: function(response) {


                    },
                    complete: function() {
                        selector.removeAttr('disabled');
                    }
                });







            });

            $('._select2').each(function() {
                $(this).html(iconsOptions).select2();
                $(this).val($(this).data('selected')).trigger('change');
            });
        });
    </script>
@endsection
