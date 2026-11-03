@extends('layouts.app')

@section('content')
    <div>
        <x-common.list-header
            title="Offers"
            description="Create discounts and promotions for checkout and POS."
        >
            <x-slot:actions>
                @can('offers.manage')
                    <x-common.btn-primary href="{{ route('offer.create') }}">
                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M12 5v14M5 12h14" stroke-linecap="round" />
                        </svg>
                        Add Offer
                    </x-common.btn-primary>
                @endcan
            </x-slot:actions>
        </x-common.list-header>

        <x-common.list-toolbar :action="route('offer.index')" :reset-url="route('offer.index')">
            <x-common.search-field name="search" :value="$search" placeholder="Search offers..." />
        </x-common.list-toolbar>

        <div class="overflow-hidden rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/3">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-800">
                    <thead class="bg-gray-50 dark:bg-white/5">
                        <tr class="text-left text-xs font-medium tracking-wider text-gray-500 uppercase dark:text-gray-400">
                            <th class="px-4 py-3">Name</th>
                            <th class="px-4 py-3">Code</th>
                            <th class="px-4 py-3">Type</th>
                            <th class="px-4 py-3">Value</th>
                            <th class="px-4 py-3">Scope</th>
                            <th class="px-4 py-3">Status</th>
                            <th class="px-4 py-3">Uses</th>
                            <th class="px-4 py-3 text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                        @forelse ($offers as $offer)
                            <tr class="text-sm text-gray-700 dark:text-gray-300">
                                <td class="px-4 py-3 whitespace-nowrap font-medium text-gray-800 dark:text-white/90">{{ $offer->name }}</td>
                                <td class="px-4 py-3 whitespace-nowrap">{{ $offer->coupon_code ?: '—' }}</td>
                                <td class="px-4 py-3 whitespace-nowrap capitalize">{{ $offer->type->value }}</td>
                                <td class="px-4 py-3 whitespace-nowrap">
                                    @if ($offer->type->value === 'percentage')
                                        {{ rtrim(rtrim(number_format((float) $offer->value, 2, '.', ''), '0'), '.') }}%
                                    @else
                                        {{ number_format((float) $offer->value, 2) }}
                                    @endif
                                </td>
                                <td class="px-4 py-3 whitespace-nowrap capitalize">{{ $offer->scope->value }}</td>
                                <td class="px-4 py-3 whitespace-nowrap">
                                    <x-common.status-badge :active="(bool) $offer->is_active" />
                                </td>
                                <td class="px-4 py-3 whitespace-nowrap">
                                    {{ $offer->usages_count }}@if ($offer->usage_limit)/{{ $offer->usage_limit }}@endif
                                </td>
                                <td class="px-4 py-3 whitespace-nowrap text-end">
                                    <div class="inline-flex items-center gap-2">
                                        @can('offers.manage')
                                            <a href="{{ route('offer.edit', $offer) }}"
                                                class="text-brand-500 hover:text-brand-600 text-sm font-medium">Edit</a>
                                            @if ($offer->is_active)
                                                <form method="POST" action="{{ route('offer.deactivate', $offer) }}" data-ajax="true"
                                                    data-confirm="Deactivate this offer?" class="inline">
                                                    @csrf
                                                    <button type="submit" class="text-sm font-medium text-gray-500 hover:text-gray-700 dark:hover:text-gray-300">Deactivate</button>
                                                </form>
                                            @else
                                                <form method="POST" action="{{ route('offer.activate', $offer) }}" data-ajax="true" class="inline">
                                                    @csrf
                                                    <button type="submit" class="text-success-600 hover:text-success-500 text-sm font-medium">Activate</button>
                                                </form>
                                            @endif
                                            <form method="POST" action="{{ route('offer.destroy', $offer) }}" data-ajax="true"
                                                data-confirm="Delete this offer?" class="inline">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="text-error-500 hover:text-error-600 text-sm font-medium">Delete</button>
                                            </form>
                                        @endcan
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <x-common.empty-state
                                colspan="8"
                                title="No offers found"
                                description="Try adjusting your filters or add a new offer to get started."
                            >
                                <x-slot:actions>
                                    @can('offers.manage')
                                        <x-common.btn-primary href="{{ route('offer.create') }}">Add Offer</x-common.btn-primary>
                                    @endcan
                                    @if ($hasFilters)
                                        <x-common.btn-secondary href="{{ route('offer.index') }}">Clear Filters</x-common.btn-secondary>
                                    @endif
                                </x-slot:actions>
                            </x-common.empty-state>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if ($offers->hasPages())
                <div class="border-t border-gray-200 px-4 py-3 dark:border-gray-800">{{ $offers->links() }}</div>
            @endif
        </div>
    </div>
@endsection
