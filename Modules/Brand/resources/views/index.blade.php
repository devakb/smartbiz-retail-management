@extends('layouts.app')

@section('content')
    <div>
        <x-common.list-header
            title="Brands"
            description="Manage brand identities linked to your products."
        >
            <x-slot:actions>
                @can('brands.create')
                    <x-common.btn-primary href="{{ route('brand.create') }}">
                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M12 5v14M5 12h14" stroke-linecap="round" />
                        </svg>
                        Add Brand
                    </x-common.btn-primary>
                @endcan
            </x-slot:actions>
        </x-common.list-header>

        <x-common.list-toolbar :action="route('brand.index')" :reset-url="route('brand.index')">
            <x-common.search-field name="search" :value="$search" placeholder="Search brands..." />
        </x-common.list-toolbar>

        <div class="overflow-hidden rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/3">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-800">
                    <thead class="bg-gray-50 dark:bg-white/5">
                        <tr class="text-left text-xs font-medium tracking-wider text-gray-500 uppercase dark:text-gray-400">
                            <th class="px-4 py-3">Logo</th>
                            <th class="px-4 py-3">Name</th>
                            <th class="px-4 py-3">Status</th>
                            <th class="px-4 py-3 text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                        @forelse ($brands as $brand)
                            <tr class="text-sm text-gray-700 dark:text-gray-300">
                                <td class="px-4 py-3 whitespace-nowrap">
                                    @if ($brand->logo)
                                        <img src="{{ Storage::disk('public')->url($brand->logo) }}" alt="{{ $brand->name }}"
                                            class="h-10 w-10 rounded-lg object-cover" />
                                    @else
                                        <span class="text-gray-400">—</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 whitespace-nowrap font-medium text-gray-800 dark:text-white/90">{{ $brand->name }}</td>
                                <td class="px-4 py-3 whitespace-nowrap">
                                    <x-common.status-badge :active="(bool) $brand->is_active" />
                                </td>
                                <td class="px-4 py-3 whitespace-nowrap text-end">
                                    <div class="inline-flex items-center gap-2">
                                        @can('brands.update')
                                            <a href="{{ route('brand.edit', $brand) }}"
                                                class="text-brand-500 hover:text-brand-600 text-sm font-medium">Edit</a>
                                        @endcan
                                        @can('brands.delete')
                                            <form method="POST" action="{{ route('brand.destroy', $brand) }}" data-ajax="true"
                                                data-confirm="Delete this brand?" class="inline">
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
                                colspan="4"
                                title="No brands found"
                                description="Try adjusting your filters or add a new brand to get started."
                            >
                                <x-slot:actions>
                                    @can('brands.create')
                                        <x-common.btn-primary href="{{ route('brand.create') }}">Add Brand</x-common.btn-primary>
                                    @endcan
                                    @if ($hasFilters)
                                        <x-common.btn-secondary href="{{ route('brand.index') }}">Clear Filters</x-common.btn-secondary>
                                    @endif
                                </x-slot:actions>
                            </x-common.empty-state>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if ($brands->hasPages())
                <div class="border-t border-gray-200 px-4 py-3 dark:border-gray-800">{{ $brands->links() }}</div>
            @endif
        </div>
    </div>
@endsection
