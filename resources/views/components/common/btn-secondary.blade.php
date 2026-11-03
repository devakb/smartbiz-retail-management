@props([
    'href' => null,
])

@php
    $classes = 'inline-flex h-11 items-center justify-center gap-2 rounded-lg border border-gray-300 px-4 text-sm font-medium text-gray-700 transition hover:bg-gray-50 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-white/5';
@endphp

@if ($href)
    <a href="{{ $href }}" {{ $attributes->class([$classes]) }}>
        {{ $slot }}
    </a>
@else
    <button type="{{ $attributes->get('type', 'button') }}" {{ $attributes->except('type')->class([$classes]) }}>
        {{ $slot }}
    </button>
@endif
