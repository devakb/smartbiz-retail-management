@php
    $query = request()->query();
    $formats = $formats ?? ['csv', 'xlsx'];
@endphp

<div class="flex flex-wrap gap-2">
    @foreach ($formats as $format)
        <a href="{{ route($exportBase.'.export', array_merge($query, ['format' => $format])) }}"
            class="inline-flex items-center justify-center rounded-lg border border-gray-300 px-3 py-2 text-sm font-medium text-gray-700 transition hover:bg-gray-50 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-white/5">
            Export {{ strtoupper($format) }}
        </a>
    @endforeach
</div>
