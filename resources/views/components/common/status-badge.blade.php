@props([
    'active' => true,
    'activeLabel' => 'Active',
    'inactiveLabel' => 'Inactive',
])

@if ($active)
    <span {{ $attributes->class(['inline-flex items-center rounded-full bg-success-50 px-2.5 py-0.5 text-xs font-medium text-success-700 dark:bg-success-500/15 dark:text-success-400']) }}>
        {{ $activeLabel }}
    </span>
@else
    <span {{ $attributes->class(['inline-flex items-center rounded-full bg-gray-100 px-2.5 py-0.5 text-xs font-medium text-gray-600 dark:bg-white/5 dark:text-gray-400']) }}>
        {{ $inactiveLabel }}
    </span>
@endif
