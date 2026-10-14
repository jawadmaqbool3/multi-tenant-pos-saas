@extends('layouts.guest')

@section('title', 'Plans & pricing')

@section('content')
    @php
        $count = $plans->count();
        $isSlider = $count > 3;
    @endphp

    <style>
        .pricing-shell {
            display: flex;
            flex-direction: column;
            min-height: 100vh;
            background: var(--ui-void);
        }

        .pricing-topbar {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 1.25rem 2rem;
            background: var(--ui-surface);
            border-bottom: 1px solid var(--ui-line);
        }

        .pricing-brand {
            display: inline-flex;
            align-items: center;
            gap: 0.6rem;
            font-weight: 800;
            font-size: 1.1rem;
            color: var(--ui-ink);
            text-decoration: none;
            transition: color 0.15s ease;
        }

        .pricing-brand:hover {
            color: var(--kt-primary);
        }

        .pricing-brand img {
            height: 30px;
            width: auto;
        }

        .pricing-main {
            flex: 1;
            padding: 3rem 1.5rem 4rem;
        }

        .pricing-heading {
            max-width: 680px;
            margin: 0 auto 3rem;
            text-align: center;
        }

        .pricing--grid .pricing-track {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 360px));
            justify-content: center;
            gap: 1.75rem;
        }

        .pricing--slider {
            position: relative;
            max-width: 1200px;
            margin: 0 auto;
        }

        .pricing--slider .pricing-track {
            position: relative;
            display: flex;
            gap: 1.75rem;
            overflow-x: auto;
            scroll-snap-type: x mandatory;
            scroll-behavior: smooth;
            padding: 0.5rem 0.25rem 1.5rem;
            scrollbar-width: none;
            -ms-overflow-style: none;
        }

        .pricing--slider .pricing-track::-webkit-scrollbar {
            display: none;
        }

        .pricing-card {
            position: relative;
            display: flex;
            flex-direction: column;
            background: var(--ui-card);
            border: 1px solid var(--ui-line-hi);
            border-radius: 1rem;
            padding: 2rem 1.75rem;
            box-shadow: var(--ui-card-shadow);
            transition: transform 0.18s ease, box-shadow 0.18s ease, border-color 0.18s ease;
        }

        .pricing-card:hover {
            transform: translateY(-4px);
            border-color: var(--kt-primary);
            box-shadow: var(--ui-shadow-lg);
        }

        .pricing-card.is-popular {
            border-color: var(--kt-primary);
            box-shadow: var(--ui-glow);
        }

        .pricing--slider .pricing-card {
            flex: 0 0 340px;
            max-width: 340px;
            scroll-snap-align: center;
        }

        @media (max-width: 576px) {
            .pricing--slider .pricing-card {
                flex-basis: 84%;
                max-width: 84%;
            }
        }

        .pricing-ribbon {
            position: absolute;
            top: 1.15rem;
            right: 1.15rem;
            padding: 0.28rem 0.65rem;
            border-radius: 999px;
            background: var(--kt-primary);
            color: var(--kt-primary-inverse, #ffffff);
            font-size: 0.68rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.06em;
        }

        .pricing-card-name {
            font-size: 1.25rem;
            font-weight: 800;
            color: var(--ui-ink);
            margin-bottom: 0.35rem;
        }

        .pricing-card-desc {
            font-size: 0.85rem;
            font-weight: 600;
            color: var(--ui-muted);
            margin-bottom: 0;
            min-height: 2.4rem;
        }

        .pricing-price {
            display: flex;
            align-items: flex-end;
            gap: 0.3rem;
            margin: 1.25rem 0 1.5rem;
        }

        .pricing-amount {
            font-size: 2.75rem;
            font-weight: 800;
            line-height: 1;
            letter-spacing: -0.02em;
            color: var(--ui-ink);
        }

        .pricing-currency {
            font-weight: 700;
            color: var(--ui-muted);
        }

        .pricing-interval {
            font-size: 0.85rem;
            font-weight: 600;
            color: var(--ui-muted);
        }

        .pricing-features {
            list-style: none;
            margin: 1.5rem 0 0;
            padding: 1.5rem 0 0;
            border-top: 1px solid var(--ui-line);
            display: flex;
            flex-direction: column;
            gap: 0.85rem;
        }

        .pricing-features li {
            display: flex;
            align-items: center;
            gap: 0.65rem;
        }

        .pricing-feature-icon {
            flex: 0 0 22px;
            width: 22px;
            height: 22px;
            border-radius: 50%;
            display: inline-flex;
            align-items: center;
            justify-content: center;
        }

        .pricing-features li:not(.is-off) .pricing-feature-icon {
            background: var(--kt-success-light);
            color: var(--kt-success);
        }

        .pricing-features li.is-off .pricing-feature-icon {
            background: var(--kt-light);
            color: var(--ui-muted);
        }

        .pricing-feature-label {
            font-size: 0.9rem;
            font-weight: 600;
            color: var(--ui-ink-2);
        }

        .pricing-features li.is-off .pricing-feature-label {
            color: var(--ui-muted);
            text-decoration: line-through;
        }

        .pricing-nav {
            position: absolute;
            top: 50%;
            transform: translateY(-50%);
            z-index: 3;
            width: 44px;
            height: 44px;
            border-radius: 50%;
            border: 1px solid var(--ui-line-hi);
            background: var(--ui-surface);
            color: var(--ui-ink-2);
            display: inline-flex;
            align-items: center;
            justify-content: center;
            box-shadow: var(--ui-card-shadow);
            cursor: pointer;
            transition: background 0.15s ease, color 0.15s ease, border-color 0.15s ease, opacity 0.15s ease;
        }

        .pricing-nav:hover {
            background: var(--kt-primary);
            border-color: var(--kt-primary);
            color: var(--kt-primary-inverse, #ffffff);
        }

        .pricing-nav--prev {
            left: -14px;
        }

        .pricing-nav--next {
            right: -14px;
        }

        .pricing-nav[disabled] {
            opacity: 0.35;
            cursor: default;
        }

        @media (max-width: 991px) {
            .pricing-nav {
                display: none;
            }
        }

        .pricing-dots {
            display: flex;
            justify-content: center;
            gap: 0.5rem;
            margin-top: 1.5rem;
        }

        .pricing-dot {
            width: 9px;
            height: 9px;
            padding: 0;
            border: 0;
            border-radius: 50%;
            background: var(--ui-line-hi);
            cursor: pointer;
            transition: width 0.18s ease, background 0.18s ease;
        }

        .pricing-dot.is-active {
            width: 26px;
            border-radius: 999px;
            background: var(--kt-primary);
        }
    </style>

    <div class="pricing-shell">
        <header class="pricing-topbar">
            <a href="{{ url('/') }}" class="pricing-brand">
                <img src="@logo(small)" alt="@setting(name)" />
                <span>@setting(name)</span>
            </a>

            <div class="d-flex align-items-center gap-3">
                <span class="text-muted fw-semibold fs-7 d-none d-md-inline">{{ auth()->user()->email }}</span>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="btn btn-sm btn-light">Sign out</button>
                </form>
            </div>
        </header>

        <main class="pricing-main">
            <div class="pricing-heading">
                <div class="eyebrow d-inline-flex mb-2">Plans &amp; pricing</div>
                <h1 class="text-dark fw-bolder mb-3">Choose the plan that fits your business</h1>
                <p class="text-gray-500 fw-semibold mb-0">
                    Your account is active. Subscribe to a plan to unlock your workspace &mdash;
                    upgrade or change anytime.
                </p>
            </div>

            @if (!$configured)
                <div class="alert alert-warning d-flex align-items-center mb-8 mx-auto" style="max-width: 720px;">
                    <span class="svg-icon svg-icon-2 svg-icon-warning me-3">
                        <x-icon name="alert-triangle" :size="20" />
                    </span>
                    <span class="fw-semibold">Billing is not configured yet. Please contact the administrator.</span>
                </div>
            @endif

            @if ($plans->isEmpty())
                <div class="card card-flush mx-auto" style="max-width: 720px;">
                    <div class="card-body text-center py-10">
                        <h3 class="fw-bold text-dark mb-2">No plans available</h3>
                        <p class="text-muted mb-0">There are no public plans to subscribe to yet. Please contact
                            support.</p>
                    </div>
                </div>
            @else
                <div class="pricing {{ $isSlider ? 'pricing--slider' : 'pricing--grid' }}"
                    data-slider="{{ $isSlider ? 'true' : 'false' }}">
                    @if ($isSlider)
                        <button type="button" class="pricing-nav pricing-nav--prev" aria-label="Previous plans"
                            data-pricing-prev>
                            <x-icon name="chevron-left" :size="20" />
                        </button>
                    @endif

                    <div class="pricing-track" data-pricing-track>
                        @foreach ($plans as $plan)
                            @php
                                $firstPrice = $plan->prices->first();
                                $featureLines = $features[$plan->uid] ?? [];
                                $isPopular = $count > 1 && $loop->index === intdiv($count, 2);
                            @endphp

                            <article class="pricing-card {{ $isPopular ? 'is-popular' : '' }}">
                                @if ($isPopular)
                                    <span class="pricing-ribbon">Most popular</span>
                                @endif

                                <h3 class="pricing-card-name">{{ $plan->name }}</h3>
                                <p class="pricing-card-desc">{{ $plan->description }}</p>

                                <div class="pricing-price">
                                    <span
                                        class="pricing-amount">{{ number_format(($firstPrice->unit_amount ?? 0) / 100, 2) }}</span>
                                    <span
                                        class="pricing-currency">{{ strtoupper($firstPrice->currency ?? $currency) }}</span>
                                    <span class="pricing-interval">
                                        /{{ ($firstPrice->interval_count ?? 1) > 1 ? $firstPrice->interval_count . ' ' : '' }}{{ $firstPrice->interval ?? 'month' }}
                                    </span>
                                </div>

                                <form data-ajax="true" method="POST"
                                    action="{{ route('billing.subscribe.checkout') }}">
                                    @csrf
                                    <input type="hidden" name="price" value="{{ $firstPrice->uid }}" />
                                    <button type="submit"
                                        class="btn {{ $isPopular ? 'btn-primary' : 'btn-light-primary' }} w-100"
                                        {{ $configured ? '' : 'disabled' }}>
                                        Subscribe
                                    </button>
                                </form>

                                <ul class="pricing-features">
                                    @foreach ($featureLines as $line)
                                        <li class="{{ $line['included'] ? '' : 'is-off' }}">
                                            <span class="pricing-feature-icon">
                                                <x-icon :name="$line['included'] ? 'check' : 'minus'" :size="14"
                                                    :strokeWidth="2.5" />
                                            </span>
                                            <span class="pricing-feature-label">{{ $line['label'] }}</span>
                                        </li>
                                    @endforeach
                                </ul>
                            </article>
                        @endforeach
                    </div>

                    @if ($isSlider)
                        <button type="button" class="pricing-nav pricing-nav--next" aria-label="Next plans"
                            data-pricing-next>
                            <x-icon name="chevron-right" :size="20" />
                        </button>
                    @endif
                </div>

                @if ($isSlider)
                    <div class="pricing-dots" data-pricing-dots>
                        @foreach ($plans as $plan)
                            <button type="button" class="pricing-dot {{ $loop->first ? 'is-active' : '' }}"
                                data-index="{{ $loop->index }}" aria-label="Go to {{ $plan->name }}"></button>
                        @endforeach
                    </div>
                @endif
            @endif
        </main>
    </div>
@endsection

@section('jsSection')
    <script>
        (function() {
            var root = document.querySelector('.pricing--slider');
            if (!root) {
                return;
            }

            var track = root.querySelector('[data-pricing-track]');
            var cards = Array.prototype.slice.call(track.querySelectorAll('.pricing-card'));
            var prev = root.querySelector('[data-pricing-prev]');
            var next = root.querySelector('[data-pricing-next]');
            var dots = Array.prototype.slice.call(document.querySelectorAll('[data-pricing-dots] .pricing-dot'));

            if (!cards.length) {
                return;
            }

            function step() {
                return cards.length > 1 ? cards[1].offsetLeft - cards[0].offsetLeft : cards[0].offsetWidth;
            }

            function update() {
                var max = track.scrollWidth - track.clientWidth;

                if (prev) {
                    prev.disabled = track.scrollLeft <= 2;
                }

                if (next) {
                    next.disabled = track.scrollLeft >= max - 2;
                }

                var index = Math.round(track.scrollLeft / step());
                index = Math.max(0, Math.min(cards.length - 1, index));

                dots.forEach(function(dot, i) {
                    dot.classList.toggle('is-active', i === index);
                });
            }

            if (prev) {
                prev.addEventListener('click', function() {
                    track.scrollBy({ left: -step(), behavior: 'smooth' });
                });
            }

            if (next) {
                next.addEventListener('click', function() {
                    track.scrollBy({ left: step(), behavior: 'smooth' });
                });
            }

            dots.forEach(function(dot) {
                dot.addEventListener('click', function() {
                    var index = parseInt(dot.dataset.index, 10) || 0;
                    track.scrollTo({ left: cards[index].offsetLeft - cards[0].offsetLeft, behavior: 'smooth' });
                });
            });

            track.addEventListener('scroll', function() {
                window.requestAnimationFrame(update);
            });

            window.addEventListener('resize', update);
            update();
        })();
    </script>
@endsection
