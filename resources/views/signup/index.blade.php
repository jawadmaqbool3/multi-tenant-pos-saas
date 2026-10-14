@extends('layouts.guest')

@section('title', 'Create your company')

@section('content')
    @php
        $setting = App\Models\Setting::where('company_id', null)->first();
        $hasPlans = $plans->isNotEmpty();
        $steps = [];
        $steps[] = ['key' => 'plan', 'label' => 'Plan'];
        $steps[] = ['key' => 'company', 'label' => 'Company'];
        $steps[] = ['key' => 'account', 'label' => 'Account'];
        $steps[] = ['key' => 'review', 'label' => 'Review'];
    @endphp

    <style>
        .signup-stepper {
            display: flex;
            align-items: flex-start;
        }

        .signup-step-item {
            flex: 1 1 0;
            position: relative;
            text-align: center;
        }

        .signup-step-item:not(:last-child)::after {
            content: '';
            position: absolute;
            top: 16px;
            left: 50%;
            width: 100%;
            height: 2px;
            background: #e4e6ef;
        }

        .signup-step-item.done::after {
            background: #50cd89;
        }

        .signup-step-dot {
            position: relative;
            z-index: 1;
            width: 34px;
            height: 34px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 6px;
            background: #f5f8fa;
            color: #7e8299;
            font-weight: 700;
            border: 2px solid #e4e6ef;
            transition: all .2s ease;
        }

        .signup-step-item.active .signup-step-dot {
            background: #3699ff;
            color: #fff;
            border-color: #3699ff;
            box-shadow: 0 0 0 4px rgba(54, 153, 255, .15);
        }

        .signup-step-item.done .signup-step-dot {
            background: #50cd89;
            color: #fff;
            border-color: #50cd89;
        }

        .signup-step-label {
            font-size: .78rem;
            font-weight: 600;
            color: #a1a5b7;
        }

        .signup-step-item.active .signup-step-label,
        .signup-step-item.done .signup-step-label {
            color: #3f4254;
        }

        .signup-pane {
            display: none;
        }

        .signup-pane.active {
            display: block;
            animation: signupFade .25s ease;
        }

        @keyframes signupFade {
            from {
                opacity: 0;
                transform: translateY(6px);
            }

            to {
                opacity: 1;
                transform: none;
            }
        }

        .signup-plan {
            position: relative;
            display: block;
            height: 100%;
            padding: 1.25rem;
            border: 1px solid #e4e6ef;
            border-radius: .75rem;
            cursor: pointer;
            transition: all .2s ease;
        }

        .signup-plan:hover {
            border-color: #b5b5c3;
        }

        .signup-plan.selected {
            border-color: #3699ff;
            box-shadow: 0 0 0 3px rgba(54, 153, 255, .12);
        }

        .signup-plan-input {
            position: absolute;
            opacity: 0;
            pointer-events: none;
        }

        .signup-plan-check {
            opacity: 0;
            color: #3699ff;
            font-weight: 700;
            transition: opacity .2s ease;
        }

        .signup-plan.selected .signup-plan-check {
            opacity: 1;
        }

        .signup-review-row {
            display: flex;
            justify-content: space-between;
            gap: 1rem;
            padding: .65rem 0;
            border-bottom: 1px dashed #e4e6ef;
        }

        .signup-review-row:last-child {
            border-bottom: 0;
        }
    </style>

    <div class="d-flex flex-column flex-lg-row flex-column-fluid">
        <div class="d-flex flex-column flex-lg-row-fluid w-lg-60 p-6 p-lg-10 order-2 order-lg-1">
            <div class="d-flex flex-center flex-column flex-lg-row-fluid">
                <div class="w-100" style="max-width: 720px;">
                    <div class="text-start mb-8">
                        <div class="eyebrow d-inline-flex mb-2">Get started</div>
                        <h1 class="text-dark fw-bolder mb-2">Create your company</h1>
                        <p class="text-gray-500 fw-semibold mb-0">Set up your workspace in a few quick steps.</p>
                    </div>

                    <div class="signup-stepper mb-9" id="signupStepper">
                        @foreach ($steps as $index => $step)
                            <div class="signup-step-item" data-index="{{ $index }}">
                                <div class="signup-step-dot">{{ $index + 1 }}</div>
                                <div class="signup-step-label">{{ $step['label'] }}</div>
                            </div>
                        @endforeach
                    </div>

                    <form id="signupForm" data-ajax="true" method="POST" action="{{ route('signup.store') }}"
                        enctype="multipart/form-data" novalidate="novalidate">
                        @csrf

                        @if ($hasPlans)
                            <div class="signup-pane" data-pane="plan">
                                <h3 class="fs-4 fw-bold text-dark mb-4">Choose a plan</h3>
                                <p class="text-muted fw-semibold mb-5">A paid plan is required to use the workspace.</p>
                                <div class="row g-4">
                                    @foreach ($plans as $plan)
                                        <div class="col-md-6">
                                            <label class="signup-plan">
                                                <input type="radio" name="plan" value="{{ $plan->uid }}"
                                                    class="signup-plan-input" {{ $loop->first ? 'checked' : '' }} required>
                                                <div class="d-flex justify-content-between align-items-start mb-2">
                                                    <span class="fw-bold text-dark fs-5"
                                                        data-plan-name>{{ $plan->name }}</span>
                                                    <span class="signup-plan-check">
                                                        <x-icon name="check" size="16" />
                                                    </span>
                                                </div>
                                                @if ($plan->description)
                                                    <p class="text-muted fs-7 mb-3">{{ $plan->description }}</p>
                                                @endif
                                                <div class="d-flex flex-wrap align-items-end gap-3">
                                                    @forelse ($plan->prices as $price)
                                                        <div>
                                                            <span
                                                                class="fs-3 fw-bolder text-dark">{{ number_format($price->unit_amount / 100, 2) }}</span>
                                                            <span
                                                                class="text-muted fw-semibold">{{ strtoupper($price->currency) }}</span>
                                                            <span class="text-muted fs-7">/{{ $price->interval_count > 1 ? $price->interval_count . ' ' : '' }}{{ $price->interval }}</span>
                                                        </div>
                                                    @empty
                                                        <span class="badge badge-light-primary">Free</span>
                                                    @endforelse
                                                </div>
                                            </label>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @else
                            <div class="signup-pane" data-pane="plan">
                                <h3 class="fs-4 fw-bold text-dark mb-4">Choose a plan</h3>
                                <div class="alert alert-warning d-flex align-items-center mb-0">
                                    <span class="svg-icon svg-icon-2 svg-icon-warning me-3">
                                        <x-icon name="alert-triangle" :size="20" />
                                    </span>
                                    <span class="fw-semibold">No plans are available yet. Please contact support to get
                                        started.</span>
                                </div>
                            </div>
                        @endif

                        <div class="signup-pane" data-pane="company">
                            <h3 class="fs-4 fw-bold text-dark mb-4">Company details</h3>
                            <div class="row g-4">
                                <div class="col-md-12">
                                    <label class="required form-label fw-semibold text-gray-700">Company name</label>
                                    <input type="text" name="company_name" class="form-control form-control-solid"
                                        placeholder="Acme Inc." required />
                                </div>
                                <div class="col-md-6">
                                    <label class="required form-label fw-semibold text-gray-700">Company email</label>
                                    <input type="email" name="company_email" class="form-control form-control-solid"
                                        placeholder="hello@acme.com" required />
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold text-gray-700">Company address</label>
                                    <input type="text" name="company_address" class="form-control form-control-solid"
                                        placeholder="Street, City" />
                                </div>
                                <div class="col-md-12">
                                    <label class="form-label fw-semibold text-gray-700">Logo
                                        <span class="text-muted fs-7">(optional)</span></label>
                                    <input type="file" name="image" class="form-control form-control-solid"
                                        accept=".png,.jpg,.jpeg" />
                                </div>
                            </div>
                        </div>

                        <div class="signup-pane" data-pane="account">
                            <h3 class="fs-4 fw-bold text-dark mb-4">Admin account</h3>
                            <div class="row g-4">
                                <div class="col-md-12">
                                    <label class="required form-label fw-semibold text-gray-700">Full name</label>
                                    <input type="text" name="name" class="form-control form-control-solid"
                                        placeholder="Jane Doe" required />
                                </div>
                                <div class="col-md-12">
                                    <label class="required form-label fw-semibold text-gray-700">Email</label>
                                    <input type="email" name="email" class="form-control form-control-solid"
                                        placeholder="you@acme.com" required />
                                </div>
                                <div class="col-md-6">
                                    <label class="required form-label fw-semibold text-gray-700">Password</label>
                                    <input type="password" name="password" class="form-control form-control-solid"
                                        placeholder="At least 8 characters" minlength="8" required />
                                </div>
                                <div class="col-md-6">
                                    <label class="required form-label fw-semibold text-gray-700">Confirm password</label>
                                    <input type="password" name="password_confirmation"
                                        class="form-control form-control-solid" placeholder="Repeat password"
                                        minlength="8" required />
                                </div>
                            </div>
                        </div>

                        <div class="signup-pane" data-pane="review">
                            <h3 class="fs-4 fw-bold text-dark mb-4">Review &amp; confirm</h3>
                            <div class="card card-flush bg-light rounded p-5" id="signupReview"></div>
                            <div class="form-check form-check-custom form-check-solid mt-6">
                                <input class="form-check-input" type="checkbox" value="1" id="signupAgree"
                                    name="agree" />
                                <label class="form-check-label fw-semibold text-gray-700" for="signupAgree">
                                    I confirm the details above are correct.
                                </label>
                            </div>
                        </div>

                        <div class="d-flex justify-content-between align-items-center mt-9">
                            <button type="button" class="btn btn-light" id="signupPrev">Back</button>
                            <div class="d-flex align-items-center">
                                <a href="{{ route('login') }}" class="btn btn-link text-muted me-2">Cancel</a>
                                <button type="button" class="btn btn-primary" id="signupNext">
                                    <span class="indicator-label">Continue</span>
                                </button>
                                <button type="submit" class="btn btn-primary d-none" id="signupSubmit">
                                    <span class="indicator-label">Create account</span>
                                </button>
                            </div>
                        </div>
                    </form>

                    <div class="text-center text-muted fw-semibold fs-6 mt-8">
                        Already have an account?
                        <a href="{{ route('login') }}" class="link-primary fw-bold">Sign in</a>
                    </div>
                </div>
            </div>
        </div>

        <div class="d-none d-lg-flex flex-lg-row-fluid w-lg-40 order-1 order-lg-2"
            style="background-image: linear-gradient(135deg, #6c63ff 0%, #4a41e0 55%, #07080f 100%);">
            <div class="d-flex flex-column flex-center py-7 py-lg-15 px-5 px-md-15 w-100">
                <img alt="Logo" src="@logo(large)"
                    class="h-60px h-lg-75px rounded mb-10" />
                <h2 class="text-white fw-bolder text-center mb-4">{{ $setting->name ?? 'POS' }}</h2>
                <div class="fw-semibold text-center" style="color: rgba(255,255,255,.7); max-width: 350px;">Point of
                    sale, inventory and reporting in one place.</div>
            </div>
        </div>
    </div>
@endsection

@section('jsSection')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            var form = document.getElementById('signupForm');
            if (!form) {
                return;
            }

            var panes = Array.prototype.slice.call(form.querySelectorAll('.signup-pane'));
            var items = Array.prototype.slice.call(document.querySelectorAll('#signupStepper .signup-step-item'));
            var prevBtn = document.getElementById('signupPrev');
            var nextBtn = document.getElementById('signupNext');
            var submitBtn = document.getElementById('signupSubmit');
            var agree = document.getElementById('signupAgree');
            var current = 0;

            function render() {
                panes.forEach(function(pane, i) {
                    pane.classList.toggle('active', i === current);
                });
                items.forEach(function(item, i) {
                    item.classList.toggle('active', i === current);
                    item.classList.toggle('done', i < current);
                });

                prevBtn.style.visibility = current === 0 ? 'hidden' : 'visible';

                var last = current === panes.length - 1;
                nextBtn.classList.toggle('d-none', last);
                submitBtn.classList.toggle('d-none', !last);

                if (last) {
                    buildReview();
                }
            }

            function validatePane(pane) {
                var fields = pane.querySelectorAll('input[name], select[name], textarea[name]');
                for (var i = 0; i < fields.length; i++) {
                    var field = fields[i];
                    if (field.type === 'radio' || field.type === 'file' || field.type === 'checkbox') {
                        continue;
                    }
                    if (!field.checkValidity()) {
                        field.reportValidity();
                        return false;
                    }
                }

                var radios = pane.querySelectorAll('input[type="radio"][name]');
                if (radios.length && !pane.querySelector('input[name="' + radios[0].name + '"]:checked')) {
                    toastr.error('Please select an option to continue.');
                    return false;
                }

                var password = pane.querySelector('input[name="password"]');
                var confirmation = pane.querySelector('input[name="password_confirmation"]');
                if (password && confirmation && password.value !== confirmation.value) {
                    confirmation.setCustomValidity('Passwords do not match.');
                    confirmation.reportValidity();
                    confirmation.setCustomValidity('');
                    return false;
                }

                return true;
            }

            function reviewRow(label, value) {
                var row = document.createElement('div');
                row.className = 'signup-review-row';

                var key = document.createElement('span');
                key.className = 'text-muted fw-semibold';
                key.textContent = label;

                var val = document.createElement('span');
                val.className = 'fw-bold text-dark text-end';
                val.textContent = value;

                row.appendChild(key);
                row.appendChild(val);
                return row;
            }

            function buildReview() {
                var container = document.getElementById('signupReview');
                container.innerHTML = '';

                var selectedPlan = form.querySelector('input[name="plan"]:checked');
                if (selectedPlan) {
                    var card = selectedPlan.closest('.signup-plan');
                    var name = card ? card.querySelector('[data-plan-name]') : null;
                    container.appendChild(reviewRow('Plan', name ? name.textContent.trim() : selectedPlan.value));
                }

                var data = new FormData(form);
                var rows = [
                    ['Company', 'company_name'],
                    ['Company email', 'company_email'],
                    ['Company address', 'company_address'],
                    ['Admin name', 'name'],
                    ['Admin email', 'email']
                ];

                rows.forEach(function(pair) {
                    var value = data.get(pair[1]);
                    if (value) {
                        container.appendChild(reviewRow(pair[0], value));
                    }
                });
            }

            nextBtn.addEventListener('click', function() {
                if (!validatePane(panes[current])) {
                    return;
                }
                if (current < panes.length - 1) {
                    current++;
                    render();
                }
            });

            prevBtn.addEventListener('click', function() {
                if (current > 0) {
                    current--;
                    render();
                }
            });

            items.forEach(function(item, i) {
                item.addEventListener('click', function() {
                    if (i < current) {
                        current = i;
                        render();
                    }
                });
            });

            form.addEventListener('submit', function(e) {
                for (var i = 0; i < panes.length; i++) {
                    if (!validatePane(panes[i])) {
                        current = i;
                        render();
                        e.preventDefault();
                        e.stopImmediatePropagation();
                        return;
                    }
                }
                if (agree && !agree.checked) {
                    toastr.error('Please confirm your details to continue.');
                    e.preventDefault();
                    e.stopImmediatePropagation();
                }
            });

            form.querySelectorAll('.signup-plan-input').forEach(function(input) {
                input.addEventListener('change', function() {
                    form.querySelectorAll('.signup-plan').forEach(function(card) {
                        card.classList.toggle('selected', card
                            .contains(input) && input.checked);
                    });
                });
            });

            document.addEventListener('fieldValidationError', function(e) {
                var field = e.detail.field;
                var el = form.querySelector('[name="' + field + '"]');
                if (!el) {
                    return;
                }
                var pane = el.closest('.signup-pane');
                if (pane) {
                    var index = panes.indexOf(pane);
                    if (index >= 0) {
                        current = index;
                        render();
                    }
                }
                var target = form.querySelector('[name="' + field + '"]');
                if (target) {
                    target.classList.add('border-danger');
                    try {
                        target.focus();
                    } catch (err) {}
                }
            });

            render();
            var checkedPlan = form.querySelector('.signup-plan-input:checked');
            if (checkedPlan) {
                var label = checkedPlan.closest('.signup-plan');
                if (label) {
                    label.classList.add('selected');
                }
            }

            if (form.querySelectorAll('.signup-plan-input').length === 0) {
                submitBtn.setAttribute('disabled', 'disabled');
                nextBtn.setAttribute('disabled', 'disabled');
            }
        });
    </script>
@endsection
