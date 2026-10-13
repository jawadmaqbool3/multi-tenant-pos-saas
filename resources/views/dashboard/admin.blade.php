@extends('layouts.basic')
@section('content')
    <div class="d-flex flex-column flex-column-fluid">
        <div id="kt_app_toolbar" class="app-toolbar py-3 py-lg-6">
            <div id="kt_app_toolbar_container" class="app-container container-xxl">
                <div class="row">
                    <div class="col-lg-6">
                        <div class="page-title">
                            <h1 class="page-heading d-flex text-dark fw-bold fs-3 flex-column justify-content-center my-0">
                                SaaS Dashboard</h1>
                            <x-breadcrums :links="[
                                'Home' => route('dashboard'),
                                'Dashboard' => false,
                            ]" />
                        </div>
                    </div>
                    <div class="col-lg-6">
                        <div class="">
                            <a href="{{ route('companies.create') }}"
                                class="mt-2 ms-2 float-end btn btn-sm fw-bold btn-primary">Create Company</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div id="kt_app_content" class="app-content flex-column-fluid">
            <div id="kt_app_content_container" class="app-container container-xxl">
                <div class="row g-5 g-xl-10 mb-xl-10">
                    <div class="col-md-6 col-lg-3 mb-md-5 mb-xl-0">
                        <div class="card card-flush h-100">
                            <div class="card-body d-flex flex-column justify-content-center">
                                <span class="fs-2hx fw-bold text-dark me-2 lh-1 ls-n2">{{ $stats['companies'] }}</span>
                                <span class="text-gray-400 pt-1 fw-semibold fs-6">Total Companies</span>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6 col-lg-3 mb-md-5 mb-xl-0">
                        <div class="card card-flush h-100">
                            <div class="card-body d-flex flex-column justify-content-center">
                                <span
                                    class="fs-2hx fw-bold text-success me-2 lh-1 ls-n2">{{ $stats['activeCompanies'] }}</span>
                                <span class="text-gray-400 pt-1 fw-semibold fs-6">Active Companies</span>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6 col-lg-3 mb-md-5 mb-xl-0">
                        <div class="card card-flush h-100">
                            <div class="card-body d-flex flex-column justify-content-center">
                                <span
                                    class="fs-2hx fw-bold text-warning me-2 lh-1 ls-n2">{{ $stats['inactiveCompanies'] }}</span>
                                <span class="text-gray-400 pt-1 fw-semibold fs-6">Inactive Companies</span>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6 col-lg-3 mb-md-5 mb-xl-0">
                        <div class="card card-flush h-100">
                            <div class="card-body d-flex flex-column justify-content-center">
                                <span class="fs-2hx fw-bold text-primary me-2 lh-1 ls-n2">{{ $stats['users'] }}</span>
                                <span class="text-gray-400 pt-1 fw-semibold fs-6">Tenant Users</span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row gy-5 g-xl-10">
                    <div class="col-xl-12">
                        <div class="card card-flush h-xl-100">
                            <div class="card-header pt-7">
                                <h3 class="card-title align-items-start flex-column">
                                    <span class="card-label fw-bold text-dark">Recent Companies</span>
                                </h3>
                                <div class="card-toolbar">
                                    <a href="{{ route('companies.index') }}" class="btn btn-sm btn-light">View All</a>
                                </div>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-row-dashed table-row-gray-300 align-middle gs-0 gy-4">
                                        <thead>
                                            <tr class="border-0">
                                                <th class="fw-bold p-0">Company</th>
                                                <th class="fw-bold p-0">Email</th>
                                                <th class="fw-bold p-0">Status</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @forelse ($stats['recentCompanies'] as $company)
                                                <tr>
                                                    <td>{{ $company->setting->name ?? '-' }}</td>
                                                    <td>{{ $company->setting->email ?? '-' }}</td>
                                                    <td>
                                                        <span
                                                            class="badge {{ $company->status == 'active' ? 'badge-light-success' : 'badge-light-warning' }}">
                                                            {{ ucfirst($company->status) }}
                                                        </span>
                                                    </td>
                                                </tr>
                                            @empty
                                                <tr>
                                                    <td colspan="3" class="text-center text-muted py-5">No companies yet
                                                    </td>
                                                </tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
