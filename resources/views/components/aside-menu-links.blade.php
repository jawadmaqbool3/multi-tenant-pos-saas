@if (count($links))
    @foreach ($links as $menu)
        <div class="menu-item pt-5">
            <div class="menu-content">
                <span class="menu-heading fw-bold text-uppercase fs-7">{{ $menu['title'] }}</span>
            </div>
        </div>
        @foreach ($menu['categories'] as $category)
            <div data-kt-menu-trigger="click" class="menu-item menu-accordion">
                <span class="menu-link">
                    <span class="menu-icon">
                        <span class="svg-icon svg-icon-2">
                            <i class="{{ $category['icon'] }} fs-4"></i>
                        </span>
                    </span>
                    <span class="menu-title">{{ $category['name'] }}</span>
                    <span class="menu-arrow"></span>
                </span>
                <div class="menu-sub menu-sub-accordion">
                    @foreach ($category['links'] as $link)
                        <div class="menu-item">
                            <a class="menu-link" href="{{ $link['link'] }}">
                                <span class="menu-bullet">
                                    <span class="bullet bullet-dot"></span>
                                </span>
                                <span class="menu-title">{{ $link['title'] }}</span>
                            </a>
                        </div>
                    @endforeach
                </div>
            </div>
        @endforeach
    @endforeach
@endif
