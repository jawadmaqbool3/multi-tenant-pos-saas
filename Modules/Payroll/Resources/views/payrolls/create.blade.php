@extends('layouts.basic')
@section('content')
    <div id="kt_app_toolbar" class="app-toolbar py-3 py-lg-6">
        <div id="kt_app_toolbar_container" class="app-container container-xxl d-flex flex-stack">
            <div class="page-title d-flex flex-column justify-content-center flex-wrap me-3">
                <h1 class="page-heading d-flex text-dark fw-bold fs-3 flex-column justify-content-center my-0">New Payroll
                    Run</h1>
                <x-breadcrums :links="[
                    'Home' => route('dashboard'),
                    'Payroll Runs' => route('payrolls.index'),
                    'Create' => false,
                ]" />
            </div>
        </div>
    </div>
    <div id="kt_app_content" class="app-content flex-column-fluid">
        <div class="app-container container-xxl">
            <form data-ajax="true" action="{{ route('payrolls.store') }}" class="form" method="POST">
                <div class="card card-flush py-4">
                    <div class="card-header">
                        <div class="card-title">
                            <h2>Payroll Period</h2>
                        </div>
                    </div>
                    <div class="card-body pt-0">
                        <div class="mb-10 fv-row">
                            <label class="required form-label">Period</label>
                            <input type="month" name="period" value="{{ old('period', date('Y-m')) }}"
                                class="form-control mb-2" placeholder="Select month" required />
                            <div class="text-muted fs-7">Select the month this payroll run covers. Each month can only be
                                generated once.</div>
                        </div>
                        <div class="mb-10 fv-row">
                            <label class="form-label">Notes</label>
                            <textarea name="notes" class="form-control mb-2" rows="3" placeholder="Optional notes">{{ old('notes') }}</textarea>
                        </div>
                    </div>
                </div>

                <div class="d-flex justify-content-end">
                    <a href="{{ route('payrolls.index') }}" class="btn btn-light me-5">Cancel</a>
                    <button type="submit" class="btn btn-primary">
                        <span class="indicator-label">Generate Payroll</span>
                        <span class="indicator-progress">Please wait...
                            <span class="spinner-border spinner-border-sm align-middle ms-2"></span></span>
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection
