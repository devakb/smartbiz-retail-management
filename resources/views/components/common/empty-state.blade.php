@props([
    'title' => 'No records found',
    'description' => 'Try adjusting your filters or add a new record to get started.',
    'colspan' => 1,
])

<tr>
    <td colspan="{{ $colspan }}" class="px-3 py-14">
        <div class="mx-auto flex max-w-md flex-col items-center text-center">
            <div class="mb-4 flex h-16 w-16 items-center justify-center rounded-2xl bg-brand-50 text-brand-500 dark:bg-brand-500/15 dark:text-brand-400">
                {{ $icon ?? '' }}
                @unless (isset($icon))
                    <svg class="h-8 w-8" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6">
                        <path d="M3 8.5 12 4l9 4.5v7L12 20l-9-4.5v-7Z" stroke-linejoin="round" />
                        <path d="M12 12v8M3 8.5l9 3.5 9-3.5" stroke-linecap="round" stroke-linejoin="round" />
                    </svg>
                @endunless
            </div>
            <h4 class="text-base font-semibold text-gray-800 dark:text-white/90">{{ $title }}</h4>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ $description }}</p>
            @isset($actions)
                <div class="mt-5 flex flex-wrap items-center justify-center gap-2">
                    {{ $actions }}
                </div>
            @endisset
        </div>
    </td>
</tr>
