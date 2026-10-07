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
                        'Account Settings' => false,
                    ]" />
                </div>
            </div>
        </div>
        <form id="account_setting_form" action="{{ route('account.settings.update') }}" method="POST">

            <div class="row">
                <div class="col-sm-12">
                    <div class="card card-flush py-4">
                        <div class="card-header">
                            <div class="card-title">
                                <h2>Account Settings</h2>
                            </div>
                        </div>
                        <div class="card-body pt-0">

                            <div class="row" id="aside_links">
                                @foreach ($accountSetting as $setting)
                                    <div class="col-md-6 setting-card" data-setting="{{ $setting->uid }}">
                                        <div class="card">
                                            <div class="card-body">
                                                <div class="mb-3">
                                                    <label for="_{{ $setting->id }}_{{ $setting->name }}"
                                                        class="form-label">{{ $setting->name }}
                                                        ({{ $setting->short }})
                                                    </label>
                                                    <x-account-selector :selected="$setting->account ? $setting->account->uid : 'all'" selectLevel="" :account="$accounts"
                                                        isRequired="true" accountLevel="all" class="account-selector"
                                                        id="_{{ $setting->id }}_{{ $setting->name }}" name="account[]" />
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                            <div class="row">
                                <div class="col-12 p-0 mt-5">
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
    <script>
        $(document).ready(function() {
            $('._select2').select2();
            $(document).on('submit', '#account_setting_form', function(e) {
                e.preventDefault();
                let settings = [];
                $('.setting-card').each(function() {
                    let setting = [];
                    settings.push({
                        setting: $(this).data('setting'),
                        account: $(this).find('.account-selector').val(),
                        level: $(this).find('.account-selector option:selected').data(
                            'level')
                    });
                });
                if (settings.length == 0) {
                    toastr.warning('Nothing to save.', "Warning", {
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
                $.ajax({
                    url: url,
                    data: {
                        settings: settings,
                        _token: _token
                    },
                    type: method,
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
        });
    </script>
@endsection
