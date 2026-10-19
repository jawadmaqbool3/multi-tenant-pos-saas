@php
    $setting = App\Models\Setting::where('company_id', null)->first();
    $testAccounts = App\Support\DefaultAccounts::all();
    $testPassword = App\Support\DefaultAccounts::PASSWORD;
@endphp
<!DOCTYPE html>

<html lang="en">

<head>
    <title>Login | {{$setting->name}}</title>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <link rel="shortcut icon" href="@logo(icon)" />
    @include('layouts.style')

    <style>
        .login-form-wrap {
            max-width: 460px;
            margin: 0 auto;
        }

        .login-title {
            font-size: 1.9rem;
            font-weight: 800;
            letter-spacing: -0.02em;
            color: var(--ui-ink);
            margin-bottom: 0.5rem;
        }

        .login-subtitle {
            color: var(--ui-ink-2);
            font-size: 0.95rem;
            font-weight: 500;
        }

        .login-input {
            min-height: 46px;
        }

        .login-test .form-select {
            min-height: 46px;
        }

        .login-form-wrap .form-control:focus,
        .login-form-wrap .form-select:focus {
            box-shadow: var(--ui-focus-ring) !important;
        }

        .login-alert {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            background: var(--kt-danger-light);
            color: var(--kt-danger-active);
            border-radius: var(--ui-radius-sm);
            padding: 0.75rem 0.9rem;
            font-size: 0.875rem;
            font-weight: 600;
        }

        .login-alert[hidden] {
            display: none;
        }

        .login-test {
            background: var(--ui-wash-a);
            border: 1px dashed var(--ui-line-hi);
            border-radius: var(--ui-radius);
            padding: 1rem 1.1rem;
        }

        .login-test-badge {
            font-family: var(--ui-font-mono);
            font-size: 0.62rem;
            font-weight: 600;
            letter-spacing: 0.14em;
            text-transform: uppercase;
            color: var(--ui-brand-2);
            background: var(--ui-surface);
            border: 1px solid var(--ui-line);
            border-radius: var(--ui-radius-pill);
            padding: 0.22rem 0.6rem;
        }

        .login-test .text-muted,
        .login-footer-note {
            color: var(--ui-ink-2) !important;
        }

        .login-create a {
            color: var(--kt-primary) !important;
        }

        .login-create a:hover,
        .login-create a:focus {
            color: var(--kt-primary-active) !important;
        }

        .login-test-filled {
            color: var(--ui-mint-ink);
            font-weight: 600;
            text-align: right;
        }

        .login-submit {
            min-height: 46px;
            font-weight: 700;
        }

        .login-create {
            color: var(--ui-ink-2);
            font-size: 0.9rem;
        }

        .login-brand-mobile {
            gap: 0.65rem;
        }

        .login-brand-mobile-name {
            font-family: var(--ui-font-display);
            font-weight: 700;
            color: var(--ui-ink);
        }

        .login-brand-logo {
            height: 64px;
            max-width: 220px;
            object-fit: contain;
        }

        .login-brand-name {
            font-size: 1.8rem;
            font-weight: 800;
            letter-spacing: -0.015em;
        }

        .login-brand-tagline {
            color: rgba(255, 255, 255, 0.72);
            max-width: 360px;
            font-weight: 500;
        }

        .login-brand-points {
            max-width: 320px;
            width: 100%;
        }

        .login-brand-points li {
            display: flex;
            align-items: center;
            gap: 0.7rem;
            color: rgba(255, 255, 255, 0.85);
            font-weight: 600;
            font-size: 0.9rem;
            margin-bottom: 0.7rem;
        }

        .login-brand-points li::before {
            content: "";
            flex-shrink: 0;
            width: 6px;
            height: 6px;
            border-radius: 50%;
            background: var(--ui-mint);
            box-shadow: 0 0 0 4px rgba(0, 212, 170, 0.18);
        }

        @media (max-width: 575.98px) {
            .login-title {
                font-size: 1.6rem;
            }
        }

        @media (prefers-reduced-motion: reduce) {
            .login-form-wrap,
            .login-form-wrap * {
                transition: none !important;
                animation: none !important;
            }
        }
    </style>

    <script>
        (function(w, d, s, l, i) {
            w[l] = w[l] || [];
            w[l].push({
                'gtm.start': new Date().getTime(),
                event: 'gtm.js'
            });
            var f = d.getElementsByTagName(s)[0],
                j = d.createElement(s),
                dl = l != 'dataLayer' ? '&l=' + l : '';
            j.async = true;
            j.src = 'https://www.googletagmanager.com/gtm.js?id=' + i + dl;
            f.parentNode.insertBefore(j, f);
        })(window, document, 'script', 'dataLayer', 'GTM-5FS8GGP');
    </script>
</head>

<body id="kt_body" class="app-blank bgi-size-cover bgi-position-center bgi-no-repeat">
    <script>
        var defaultThemeMode = "system";
        var themeMode;
        if (document.documentElement) {
            if (document.documentElement.hasAttribute("data-theme-mode")) {
                themeMode = document.documentElement.getAttribute("data-theme-mode");
            } else {
                if (localStorage.getItem("data-theme") !== null) {
                    themeMode = localStorage.getItem("data-theme");
                } else {
                    themeMode = defaultThemeMode;
                }
            }
            if (themeMode === "system") {
                themeMode = window.matchMedia("(prefers-color-scheme: dark)").matches ? "dark" : "light";
            }
            document.documentElement.setAttribute("data-theme", themeMode);
        }
    </script>
    <noscript>
        <iframe src="https://www.googletagmanager.com/ns.html?id=GTM-5FS8GGP" height="0" width="0"
            style="display:none;visibility:hidden"></iframe>
    </noscript>
    <div class="d-flex flex-column flex-root" id="kt_app_root">
        <div class="d-flex flex-column flex-lg-row flex-column-fluid">
            <div class="d-flex flex-column flex-lg-row-fluid w-lg-55 order-2 order-lg-1">
                <div class="d-flex flex-center flex-column flex-lg-row-fluid py-8 py-lg-15 px-5 px-md-10">
                    <div class="login-form-wrap w-100">
                        <div class="login-brand-mobile d-flex d-lg-none align-items-center mb-8">
                            <img alt="" src="@logo(icon)" class="h-35px rounded" />
                            <span class="login-brand-mobile-name">{{ $setting->name }}</span>
                        </div>

                        <div class="mb-8">
                            <div class="eyebrow d-inline-flex mb-3">Point of Sale</div>
                            <h1 class="login-title">Welcome back</h1>
                            <p class="login-subtitle mb-0">Sign in to continue to {{ $setting->name }}.</p>
                        </div>

                        <form data-ajax="true" class="form w-100" novalidate="novalidate" method="POST" action="#">
                            <div class="login-alert mb-5" id="login_error" role="alert" hidden>
                                <x-icon name="alert-circle" size="18" />
                                <span id="login_error_text"></span>
                            </div>

                            <div class="fv-row mb-5">
                                <label class="form-label fw-semibold text-gray-700" for="login_email">Email</label>
                                <input id="login_email" type="email" placeholder="name@company.com" name="email"
                                    autocomplete="username" spellcheck="false" autocapitalize="off"
                                    class="form-control form-control-solid login-input" />
                            </div>
                            <div class="fv-row mb-5">
                                <label class="form-label fw-semibold text-gray-700" for="login_password">Password</label>
                                <input id="login_password" type="password" placeholder="Your password" name="password"
                                    autocomplete="current-password"
                                    class="form-control form-control-solid login-input" />
                            </div>

                            <div class="login-test mb-8">
                                <div class="d-flex align-items-center justify-content-between gap-3 mb-3">
                                    <label class="form-label fw-semibold text-gray-700 mb-0"
                                        for="test_account">Test accounts</label>
                                    <span class="login-test-badge">Demo</span>
                                </div>
                                <select id="test_account" class="form-select form-select-solid"
                                    aria-describedby="test_account_help test_account_filled"
                                    data-placeholder="Select a test account to autofill">
                                    <option value=""></option>
                                    @foreach ($testAccounts as $email => $name)
                                        <option value="{{ $email }}">{{ $name }} &mdash; {{ $email }}
                                        </option>
                                    @endforeach
                                </select>
                                <div
                                    class="d-flex align-items-start justify-content-between gap-3 flex-wrap mt-2">
                                    <div class="text-muted fs-7" id="test_account_help">Pick an account to autofill
                                        the credentials.</div>
                                    <div class="login-test-filled fs-7 d-none" id="test_account_filled"
                                        aria-live="polite"></div>
                                </div>
                            </div>

                            <div class="mb-0">
                                <button type="submit" id="kt_sign_in_submit" class="btn btn-primary login-submit w-100">
                                    <span class="indicator-label">Sign in</span>
                                    <span class="indicator-progress">Please wait...
                                        <span
                                            class="spinner-border spinner-border-sm align-middle ms-2"></span></span>
                                </button>
                            </div>
                        </form>

                        <div class="text-center login-create mt-8">
                            Don't have an account?
                            <a href="{{ route('signup') }}" class="link-primary fw-bold ms-1">Create one</a>
                        </div>
                        <div class="text-center login-footer-note fs-7 mt-4">Fast, efficient and
                            productive.</div>
                    </div>
                </div>

            </div>
            <div class="d-none d-lg-flex flex-lg-row-fluid w-lg-45 order-1 order-lg-2"
                style="background-image: linear-gradient(135deg, #6c63ff 0%, #4a41e0 55%, #07080f 100%);">
                <div class="d-flex flex-column flex-center py-15 px-5 px-md-15 w-100">
                    <img alt="Logo" src="@logo(large)"
                        class="login-brand-logo rounded mb-8" />
                    <h2 class="login-brand-name text-white text-center mb-3">{{ $setting->name }}</h2>
                    <div class="login-brand-tagline fw-semibold text-center mb-8">Point of sale, inventory and
                        reporting in one place.</div>
                    <ul class="login-brand-points list-unstyled mb-0">
                        <li>Point of sale</li>
                        <li>Inventory tracking</li>
                        <li>Reports &amp; insights</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
    @include('layouts.toast')
    @include('layouts.script')
    <script>
        var loginSubmitBtn = document.getElementById('kt_sign_in_submit');
        var loginErrorBox = document.getElementById('login_error');
        var loginErrorText = document.getElementById('login_error_text');
        var testAccountSelect = document.getElementById('test_account');
        var testAccountFilled = document.getElementById('test_account_filled');

        function fillCredentials(email, password) {
            document.querySelector('input[name="email"]').value = email;
            document.querySelector('input[name="password"]').value = password;
        }

        function hideLoginError() {
            loginErrorBox.hidden = true;
            loginErrorText.textContent = '';
        }

        function showLoginError(message) {
            loginErrorText.textContent = message;
            loginErrorBox.hidden = false;
        }

        testAccountSelect.addEventListener('change', function() {
            if (this.value) {
                fillCredentials(this.value, @json($testPassword));
                testAccountFilled.textContent = 'Filled: ' + this.options[this.selectedIndex].text;
                testAccountFilled.classList.remove('d-none');
                hideLoginError();
            } else {
                testAccountFilled.textContent = '';
                testAccountFilled.classList.add('d-none');
            }
        });

        ['login_email', 'login_password'].forEach(function(id) {
            document.getElementById(id).addEventListener('input', function() {
                this.classList.remove('border-danger');
                hideLoginError();
            });
        });

        document.querySelector('form[data-ajax]').addEventListener('submit', function() {
            loginSubmitBtn.setAttribute('data-kt-indicator', 'on');
        });

        $(document).ajaxComplete(function(event, xhr) {
            loginSubmitBtn.removeAttribute('data-kt-indicator');
            var response = xhr.responseJSON;
            if (response && response.error && response.message && !response.success) {
                showLoginError(response.message);
            }
        });
    </script>

</body>

</html>
