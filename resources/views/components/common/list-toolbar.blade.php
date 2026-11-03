@props([
    'action',
    'resetUrl' => null,
    'method' => 'GET',
])

<form method="{{ $method }}" action="{{ $action }}"
    {{ $attributes->class(['mb-5 rounded-2xl border border-gray-200 bg-white p-4 dark:border-gray-800 dark:bg-white/3']) }}>
    <div class="flex flex-col gap-3 xl:flex-row xl:items-center">
        <div class="grid min-w-0 flex-1 grid-cols-1 gap-3 sm:grid-cols-2 xl:flex xl:flex-wrap xl:items-center">
            {{ $slot }}
        </div>

        <div class="flex flex-wrap items-center gap-2 xl:shrink-0">
            @isset($extra)
                {{ $extra }}
            @endisset

            <button type="submit"
                class="bg-brand-500 hover:bg-brand-600 inline-flex h-11 items-center justify-center gap-2 rounded-lg px-4 text-sm font-medium text-white transition">
                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                    <circle cx="11" cy="11" r="7" />
                    <path d="m20 20-3-3" stroke-linecap="round" />
                </svg>
                Filter
            </button>

            @if ($resetUrl)
                <a href="{{ $resetUrl }}"
                    class="inline-flex h-11 w-11 items-center justify-center rounded-lg border border-gray-300 text-gray-600 transition hover:bg-gray-50 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-white/5"
                    title="Reset filters">
                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                        <path d="M3 12a9 9 0 0 1 15.5-6.4M21 12a9 9 0 0 1-15.5 6.4" stroke-linecap="round" />
                        <path d="M3 5v5h5M21 19v-5h-5" stroke-linecap="round" stroke-linejoin="round" />
                    </svg>
                </a>
            @endif
        </div>
    </div>
</form>
