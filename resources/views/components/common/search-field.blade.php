@props([
    'name' => 'search',
    'value' => '',
    'placeholder' => 'Search…',
])

<div {{ $attributes->class(['relative w-full min-w-64 flex-1']) }}>
    <span class="pointer-events-none absolute inset-y-0 start-0 z-10 flex items-center ps-3.5 text-gray-400">
        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
            <circle cx="11" cy="11" r="7" />
            <path d="m20 20-3-3" stroke-linecap="round" />
        </svg>
    </span>
    <input
        type="search"
        name="{{ $name }}"
        value="{{ $value }}"
        placeholder="{{ $placeholder }}"
        class="shadow-theme-xs focus:border-brand-300 focus:ring-brand-500/10 dark:focus:border-brand-800 h-11 w-full rounded-lg border border-gray-300 bg-white py-2.5 ps-10 pe-4 text-sm text-gray-800 placeholder:text-gray-400 focus:ring-3 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-gray-500"
    />
</div>
