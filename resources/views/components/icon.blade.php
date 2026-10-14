@props([
    'name' => 'circle',
    'size' => 16,
    'strokeWidth' => 2,
])

<svg {{ $attributes->merge(['class' => 'ui-icon']) }} width="{{ $size }}" height="{{ $size }}"
    viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="{{ $strokeWidth }}"
    stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false">
    {!! \App\Support\Icon::path($name) !!}
</svg>
