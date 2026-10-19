@php
    $iconMap = [
        'bi bi-share' => 'share-2',
        'bi bi-bank' => 'landmark',
        'bi bi-shop' => 'store',
        'bi bi-cart' => 'shopping-cart',
        'bi bi-award-fill' => 'award',
        'bi bi-file-earmark-bar-graph' => 'bar-chart',
        'bi bi-people' => 'users',
        'bi bi-person-bounding-box' => 'user',
        'bi bi-lock' => 'lock',
        'bi bi-gear' => 'settings',
        'bi bi-building' => 'building',
        'bi bi-credit-card' => 'credit-card',
        'bi bi-receipt' => 'file-text',
    ];
@endphp
@if (count($links))
    @foreach ($links as $menu)
        @if (!($flat ?? false))
            <div class="menu-item pt-5">
                <div class="menu-content">
                    <span class="menu-heading fw-bold text-uppercase fs-7">{{ $menu['title'] }}</span>
                </div>
            </div>
        @endif
        @foreach ($menu['categories'] as $category)
            @if ($flat ?? false)
                @foreach ($category['links'] as $link)
                    <div class="menu-item {{ !empty($link['active']) ? 'here' : '' }}">
                        <a class="menu-link {{ !empty($link['active']) ? 'active' : '' }}" href="{{ $link['link'] }}">
                            <span class="menu-icon">
                                <span class="svg-icon svg-icon-2">
                                    <x-icon :name="$iconMap[$link['icon'] ?? ($category['icon'] ?? '')] ?? 'circle'" :size="18" />
                                </span>
                            </span>
                            <span class="menu-title">{{ $link['title'] }}</span>
                        </a>
                    </div>
                @endforeach
            @else
                <div data-kt-menu-trigger="click" class="menu-item menu-accordion {{ !empty($category['active']) ? 'hover show' : '' }}">
                    <span class="menu-link">
                        <span class="menu-icon">
                            <span class="svg-icon svg-icon-2">
                                <x-icon :name="$iconMap[$category['icon']] ?? 'circle'" :size="18" />
                            </span>
                        </span>
                        <span class="menu-title">{{ $category['name'] }}</span>
                        <span class="menu-arrow"></span>
                    </span>
                    <div class="menu-sub menu-sub-accordion {{ !empty($category['active']) ? 'show' : '' }}">
                        @foreach ($category['links'] as $link)
                            <div class="menu-item">
                                <a class="menu-link {{ !empty($link['active']) ? 'active' : '' }}" href="{{ $link['link'] }}">
                                    <span class="menu-bullet">
                                        <span class="bullet bullet-dot"></span>
                                    </span>
                                    <span class="menu-title">{{ $link['title'] }}</span>
                                </a>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif
        @endforeach
    @endforeach
@endif
