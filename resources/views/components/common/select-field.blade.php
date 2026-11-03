@props([
    'name',
    'placeholder' => 'Select…',
    'allowClear' => false,
    'searchable' => false,
    'wrapperClass' => 'w-full xl:w-44',
])

<div class="{{ $wrapperClass }}">
    <select
        name="{{ $name }}"
        class="js-tom-select"
        data-placeholder="{{ $placeholder }}"
        @if ($allowClear) data-allow-clear @endif
        @if ($searchable) data-searchable @endif
        {{ $attributes->except(['class']) }}
    >
        {{ $slot }}
    </select>
</div>
