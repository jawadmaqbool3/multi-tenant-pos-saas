<div class="app-container container-fluid d-flex align-items-stretch justify-content-between"
    id="kt_app_header_container">
    <div class="d-flex align-items-center d-lg-none ms-n2 me-2" title="Show sidebar menu">
        <div class="btn btn-icon btn-active-color-primary w-35px h-35px" id="kt_app_sidebar_mobile_toggle">
            <x-icon name="menu" :size="20" />
        </div>
    </div>
    <div class="d-flex align-items-center flex-grow-1 flex-lg-grow-0">
        <a href="{{ route('dashboard') }}" class="d-lg-none">
            <img alt="Logo" src="@logo(small)" class="h-50px rounded" />
        </a>
    </div>
    <div class="d-flex align-items-stretch justify-content-between flex-lg-grow-1" id="kt_app_header_wrapper">
        <div class="app-navbar flex-shrink-0 ms-auto">
            <div class="app-navbar-item ms-1 ms-lg-3">
                <a href="#"
                    class="btn btn-icon btn-custom btn-icon-muted btn-active-light btn-active-color-primary w-35px h-35px w-md-40px h-md-40px"
                    data-kt-menu-trigger="{default:'click', lg: 'hover'}" data-kt-menu-attach="parent"
                    data-kt-menu-placement="bottom-end">
                    <span class="theme-light-show"><x-icon name="sun" :size="20" /></span>
                    <span class="theme-dark-show"><x-icon name="moon" :size="20" /></span>
                </a>
                <div class="menu menu-sub menu-sub-dropdown menu-column menu-rounded menu-title-gray-700 menu-icon-muted menu-active-bg menu-state-color fw-semibold py-4 fs-base w-175px"
                    data-kt-menu="true" data-kt-element="theme-mode-menu">
                    <div class="menu-item px-3 my-0">
                        <a href="#" class="menu-link px-3 py-2" data-kt-element="mode" data-kt-value="light">
                            <span class="menu-icon" data-kt-element="icon">
                                <x-icon name="sun" :size="20" />
                            </span>
                            <span class="menu-title">Light</span>
                        </a>
                    </div>
                    <div class="menu-item px-3 my-0">
                        <a href="#" class="menu-link px-3 py-2" data-kt-element="mode" data-kt-value="dark">
                            <span class="menu-icon" data-kt-element="icon">
                                <x-icon name="moon" :size="20" />
                            </span>
                            <span class="menu-title">Dark</span>
                        </a>
                    </div>
                    <div class="menu-item px-3 my-0">
                        <a href="#" class="menu-link px-3 py-2" data-kt-element="mode" data-kt-value="system">
                            <span class="menu-icon" data-kt-element="icon">
                                <x-icon name="monitor" :size="20" />
                            </span>
                            <span class="menu-title">System</span>
                        </a>
                    </div>
                </div>
            </div>
            <div class="app-navbar-item ms-1 ms-lg-3" id="kt_header_user_menu_toggle">
                <div class="cursor-pointer symbol symbol-35px symbol-md-40px"
                    data-kt-menu-trigger="{default: 'click', lg: 'hover'}" data-kt-menu-attach="parent"
                    data-kt-menu-placement="bottom-end">
                    <img src="{{ auth()->user()->image ? asset('assets/media/users/profile/' . auth()->user()->image) : asset('assets/media/avatars/300-1.jpg') }}"
                        alt="user" />
                </div>
                <div class="menu menu-sub menu-sub-dropdown menu-column menu-rounded menu-gray-800 menu-state-bg menu-state-color fw-semibold py-4 fs-6 w-275px"
                    data-kt-menu="true">
                    <div class="menu-item px-3">
                        <div class="menu-content d-flex align-items-center px-3">
                            <div class="symbol symbol-50px me-5">
                                <img alt="Logo"
                                    src="{{ auth()->user()->image ? asset('assets/media/users/profile/' . auth()->user()->image) : asset('assets/media/avatars/300-1.jpg') }}" />
                            </div>
                            <div class="d-flex flex-column">
                                <div class="fw-bold d-flex align-items-center fs-5">{{ ucwords(auth()->user()->name) }}
                                    <span
                                        class="badge badge-light-success fw-bold fs-8 px-2 py-1 ms-2">{{ auth()->user()->role->name }}</span>
                                </div>
                                <a href="{{ route('profile.edit') }}"
                                    class="fw-semibold text-muted text-hover-primary fs-7">{{ auth()->user()->email }}</a>
                            </div>
                        </div>
                    </div>
                    {{-- <div class="separator my-2"></div> --}}
                    {{-- <div class="menu-item px-5">
                        <a href="../account/overview.html" class="menu-link px-5">My Profile</a>
                    </div> --}}

                    <div class="menu-item px-5">
                        <a href="{{ route('profile.edit') }}" class="menu-link px-5">My Profile</a>
                    </div>



                    <div class="separator my-2"></div>

                    {{-- <div class="menu-item px-5 my-1">
                        <a href="../account/settings.html" class="menu-link px-5">Account Settings</a>
                    </div> --}}
                    
                    <div class="menu-item px-5">
                        <form id="" action="{{ route('logout') }}" method="post">@csrf
                            <button class="menu-link px-5 px-5 w-100 border-0 app-default" type="submit">
                                Sign out
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
