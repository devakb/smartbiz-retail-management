@props([
    'href' => null,
])

@php
    $classes = 'bg-brand-500 hover:bg-brand-600 inline-flex h-11 items-center justify-center gap-2 rounded-lg px-4 text-sm font-medium text-white transition';
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
