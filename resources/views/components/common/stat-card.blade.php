@props([
    'label',
    'value' => 0,
    'tone' => 'default',
])

@php
    $tones = [
        'default' => 'bg-gray-100 text-gray-600 dark:bg-white/5 dark:text-gray-300',
        'success' => 'bg-success-50 text-success-600 dark:bg-success-500/15 dark:text-success-400',
        'warning' => 'bg-warning-50 text-warning-600 dark:bg-warning-500/15 dark:text-warning-400',
        'error' => 'bg-error-50 text-error-600 dark:bg-error-500/15 dark:text-error-400',
        'brand' => 'bg-brand-50 text-brand-600 dark:bg-brand-500/15 dark:text-brand-400',
    ];
    $iconClass = $tones[$tone] ?? $tones['default'];
@endphp

<div {{ $attributes->class(['rounded-2xl border border-gray-200 bg-white p-4 dark:border-gray-800 dark:bg-white/3']) }}>
    <div class="flex items-center gap-3">
        <div class="{{ $iconClass }} flex h-11 w-11 shrink-0 items-center justify-center rounded-xl">
            {{ $icon ?? '' }}
        </div>
        <div class="min-w-0">
            <p class="text-sm text-gray-500 dark:text-gray-400">{{ $label }}</p>
            <p class="text-title-sm font-semibold text-gray-800 dark:text-white/90">{{ $value }}</p>
        </div>
    </div>
</div>
