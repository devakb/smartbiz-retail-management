@props([])

<div {{ $attributes->class(['mb-5 grid grid-cols-1 gap-3 sm:grid-cols-2 xl:grid-cols-4']) }}>
    {{ $slot }}
</div>
