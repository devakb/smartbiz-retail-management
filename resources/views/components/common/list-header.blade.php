@props([
    'title',
    'description' => null,
    'breadcrumb' => true,
])

<div {{ $attributes->class(['mb-5']) }}>
    <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
        <div class="min-w-0">
            <div class="flex flex-wrap items-center justify-between gap-3">
                <h2 class="text-xl font-semibold text-gray-800 dark:text-white/90">{{ $title }}</h2>

                @if ($breadcrumb)
                    <nav class="sm:hidden" aria-label="Breadcrumb">
                        <ol class="flex items-center gap-1.5">
                            <li>
                                <a class="inline-flex items-center gap-1.5 text-sm text-gray-500 dark:text-gray-400" href="{{ url('/') }}">
                                    {{ __('Home') }}
                                    <svg class="stroke-current rtl:rotate-180" width="17" height="16" viewBox="0 0 17 16" fill="none">
                                        <path d="M6.0765 12.667L10.2432 8.50033L6.0765 4.33366" stroke="currentColor" stroke-width="1.2" stroke-linecap="round" stroke-linejoin="round" />
                                    </svg>
                                </a>
                            </li>
                            <li class="text-sm text-gray-800 dark:text-white/90">{{ $title }}</li>
                        </ol>
                    </nav>
                @endif
            </div>

            @if ($description)
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ $description }}</p>
            @endif
        </div>

        <div class="flex flex-col items-stretch gap-3 sm:items-end">
            @if ($breadcrumb)
                <nav class="hidden sm:block" aria-label="Breadcrumb">
                    <ol class="flex items-center gap-1.5">
                        <li>
                            <a class="inline-flex items-center gap-1.5 text-sm text-gray-500 dark:text-gray-400" href="{{ url('/') }}">
                                {{ __('Home') }}
                                <svg class="stroke-current rtl:rotate-180" width="17" height="16" viewBox="0 0 17 16" fill="none">
                                    <path d="M6.0765 12.667L10.2432 8.50033L6.0765 4.33366" stroke="currentColor" stroke-width="1.2" stroke-linecap="round" stroke-linejoin="round" />
                                </svg>
                            </a>
                        </li>
                        <li class="text-sm text-gray-800 dark:text-white/90">{{ $title }}</li>
                    </ol>
                </nav>
            @endif

            @isset($actions)
                <div class="flex flex-wrap items-center gap-2 sm:justify-end">
                    {{ $actions }}
                </div>
            @endisset
        </div>
    </div>
</div>
