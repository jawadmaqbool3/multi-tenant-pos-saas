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
    <link rel="shortcut icon" href="@logo(icon)" />
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Inter:300,400,500,600,700" />
    @include('layouts.style')

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
            j.src = '../../../../../../www.googletagmanager.com/gtm5445.html?id=' + i + dl;
            f.parentNode.insertBefore(j, f);
        })(window, document, 'script', 'dataLayer', 'GTM-5FS8GGP');
    </script>
</head>

<body id="kt_body" class="app-blank app-blank bgi-size-cover bgi-position-center bgi-no-repeat">
    <script>
        var defaultThemeMode = "light";
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
            <div class="d-flex flex-column flex-lg-row-fluid w-lg-50 p-10 order-2 order-lg-1">
                <div class="d-flex flex-center flex-column flex-lg-row-fluid">
                    <div class="w-lg-500px w-md-500px p-10">
                        <form data-ajax="true" class="form w-100" novalidate="novalidate" method="POST" action="#">
                            <div class="text-center mb-11">

                                <div class="eyebrow d-inline-flex mb-2">Point of Sale</div>
                                <h1 class="text-dark fw-bolder mb-3">Sign In</h1>
                                <div class="text-gray-500 fw-semibold fs-6">Your own management</div>
                            </div>

                            <div class="fv-row mb-5">
                                <label class="form-label fw-semibold text-gray-700">Email</label>
                                <input type="text" placeholder="name@company.com" name="email" autocomplete="off"
                                    class="form-control form-control-solid" />
                            </div>
                            <div class="fv-row mb-8">
                                <label class="form-label fw-semibold text-gray-700">Password</label>
                                <input type="password" placeholder="Your password" name="password" autocomplete="off"
                                    class="form-control form-control-solid" />
                            </div>

                            <div class="fv-row mb-8">
                                <label class="form-label fw-semibold text-gray-700">Test Accounts</label>
                                <select id="test_account" class="form-select form-select-solid border border-gray-300"
                                    data-placeholder="Select a test account to autofill">
                                    <option value=""></option>
                                    @foreach ($testAccounts as $email => $name)
                                        <option value="{{ $email }}">{{ $name }} &mdash; {{ $email }}
                                        </option>
                                    @endforeach
                                </select>
                                <div class="text-muted fs-7 mt-1">Pick an account to autofill the credentials.</div>
                            </div>

                            <div class="d-grid mb-10">
                                <button type="submit" id="kt_sign_in_submit" class="btn btn-primary">
                                    <span class="indicator-label">Sign In</span>
                                    <span class="indicator-progress">Please wait...
                                        <span class="spinner-border spinner-border-sm align-middle ms-2"></span></span>
                                </button>
                            </div>
                            <div class="text-center text-muted fw-semibold fs-6 mt-4">
                                Don't have an account?
                                <a href="{{ route('signup') }}" class="link-primary fw-bold">Create one</a>
                            </div>
                            <div class="text-center text-muted fw-semibold fs-6 mt-4">Fast, efficient and
                                productive.</div>
                        </form>
                    </div>
                </div>

            </div>
            <div class="d-none d-lg-flex flex-lg-row-fluid w-lg-50 order-1 order-lg-2"
                style="background-image: linear-gradient(135deg, #6c63ff 0%, #4a41e0 55%, #07080f 100%);">
                <div class="d-flex flex-column flex-center py-7 py-lg-15 px-5 px-md-15 w-100">
                    <img alt="Logo" src="@logo(large)"
                        class="h-60px h-lg-75px rounded mb-10" />
                    <h2 class="text-white fw-bolder text-center mb-4">{{ $setting->name }}</h2>
                    <div class="fw-semibold text-center" style="color: rgba(255,255,255,.7); max-width: 350px;">Point
                        of sale, inventory and reporting in one place.</div>
                </div>
            </div>
        </div>
    </div>
    @include('layouts.toast')
    @include('layouts.script')
    <script>
        function fillCredentials(email, password) {
            document.querySelector('input[name="email"]').value = email;
            document.querySelector('input[name="password"]').value = password;
        }

        document.getElementById('test_account').addEventListener('change', function() {
            if (this.value) {
                fillCredentials(this.value, @json($testPassword));
            }
        });
    </script>

</body>

</html>
