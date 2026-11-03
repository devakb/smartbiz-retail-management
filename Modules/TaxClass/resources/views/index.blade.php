@extends('layouts.app')

@section('content')
    <div>
        <x-common.list-header
            title="Tax Classes"
            description="Define reusable tax rates and assign them to products."
        >
            <x-slot:actions>
                @can('tax-classes.create')
                    <x-common.btn-primary href="{{ route('tax-class.create') }}">
                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M12 5v14M5 12h14" stroke-linecap="round" />
                        </svg>
                        Add Tax Class
                    </x-common.btn-primary>
                @endcan
            </x-slot:actions>
        </x-common.list-header>

        <x-common.list-toolbar :action="route('tax-class.index')" :reset-url="route('tax-class.index')">
            <x-common.search-field name="search" :value="$search" placeholder="Search tax classes..." />
        </x-common.list-toolbar>

        <div class="overflow-hidden rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/3">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-800">
                    <thead class="bg-gray-50 dark:bg-white/5">
                        <tr class="text-left text-xs font-medium tracking-wider text-gray-500 uppercase dark:text-gray-400">
                            <th class="px-4 py-3">Name</th>
                            <th class="px-4 py-3">Rate (%)</th>
                            <th class="px-4 py-3">Status</th>
                            <th class="px-4 py-3 text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                        @forelse ($taxClasses as $taxClass)
                            <tr class="text-sm text-gray-700 dark:text-gray-300">
                                <td class="px-4 py-3 whitespace-nowrap font-medium text-gray-800 dark:text-white/90">{{ $taxClass->name }}</td>
                                <td class="px-4 py-3 whitespace-nowrap">{{ number_format((float) $taxClass->rate, 2) }}%</td>
                                <td class="px-4 py-3 whitespace-nowrap">
                                    <x-common.status-badge :active="(bool) $taxClass->is_active" />
                                </td>
                                <td class="px-4 py-3 whitespace-nowrap text-end">
                                    <div class="inline-flex items-center gap-2">
                                        @can('tax-classes.update')
                                            <a href="{{ route('tax-class.edit', $taxClass) }}"
                                                class="text-brand-500 hover:text-brand-600 text-sm font-medium">Edit</a>
                                        @endcan
                                        @can('tax-classes.delete')
                                            <form method="POST" action="{{ route('tax-class.destroy', $taxClass) }}" data-ajax="true"
                                                data-confirm="Archive this tax class?" class="inline">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="text-error-500 hover:text-error-600 text-sm font-medium">Archive</button>
                                            </form>
                                        @endcan
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <x-common.empty-state
                                colspan="4"
                                title="No tax classes found"
                                description="Try adjusting your filters or add a new tax class to get started."
                            >
                                <x-slot:actions>
                                    @can('tax-classes.create')
                                        <x-common.btn-primary href="{{ route('tax-class.create') }}">Add Tax Class</x-common.btn-primary>
                                    @endcan
                                    @if ($hasFilters)
                                        <x-common.btn-secondary href="{{ route('tax-class.index') }}">Clear Filters</x-common.btn-secondary>
                                    @endif
                                </x-slot:actions>
                            </x-common.empty-state>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if ($taxClasses->hasPages())
                <div class="border-t border-gray-200 px-4 py-3 dark:border-gray-800">{{ $taxClasses->links() }}</div>
            @endif
        </div>
    </div>
@endsection
