@extends('layouts.app')

@section('content')
    <div>
        <x-common.list-header
            title="Inventory"
            description="Track stock levels and update quantities for your products."
        />

        <x-common.stats-row>
            <x-common.stat-card label="Total Products" :value="$stats['total']" tone="brand" />
            <x-common.stat-card label="Healthy Stock" :value="$stats['ok']" tone="success" />
            <x-common.stat-card label="Low Stock" :value="$stats['low']" tone="warning" />
            <x-common.stat-card label="Out of Stock" :value="$stats['out']" tone="error" />
        </x-common.stats-row>

        <x-common.list-toolbar :action="route('inventory.index')" :reset-url="route('inventory.index')">
            <x-common.search-field name="search" :value="$search" placeholder="Search by name, UPC, SKU..." />
            <label class="inline-flex h-11 items-center gap-2 rounded-lg border border-gray-300 px-3 text-sm text-gray-700 dark:border-gray-700 dark:text-gray-300">
                <input type="checkbox" name="low_stock" value="1" @checked($lowStock)
                    class="text-brand-500 h-4 w-4 rounded border-gray-300" />
                Low stock only
            </label>
        </x-common.list-toolbar>

        <div class="overflow-hidden rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/3">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-800">
                    <thead class="bg-gray-50 dark:bg-white/5">
                        <tr class="text-left text-xs font-medium tracking-wider text-gray-500 uppercase dark:text-gray-400">
                            <th class="px-4 py-3">Product</th>
                            <th class="px-4 py-3">UPC</th>
                            <th class="px-4 py-3">SKU</th>
                            <th class="px-4 py-3">Stock</th>
                            <th class="px-4 py-3">Min</th>
                            <th class="px-4 py-3">Status</th>
                            <th class="px-4 py-3 text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                        @forelse ($products as $product)
                            @php
                                $isOut = (float) $product->stock_quantity <= 0;
                                $isLow = ! $isOut && (float) $product->stock_quantity <= (float) $product->min_stock_level;
                            @endphp
                            <tr class="text-sm text-gray-700 dark:text-gray-300">
                                <td class="px-4 py-3 whitespace-nowrap font-medium text-gray-800 dark:text-white/90">{{ $product->name }}</td>
                                <td class="px-4 py-3 whitespace-nowrap">{{ $product->upc }}</td>
                                <td class="px-4 py-3 whitespace-nowrap">{{ $product->sku }}</td>
                                <td class="px-4 py-3 whitespace-nowrap {{ $isLow || $isOut ? 'text-error-500 font-semibold' : '' }}">
                                    {{ $product->stock_quantity }}
                                </td>
                                <td class="px-4 py-3 whitespace-nowrap">{{ $product->min_stock_level }}</td>
                                <td class="px-4 py-3 whitespace-nowrap">
                                    @if ($isOut)
                                        <x-common.status-badge :active="false" inactive-label="Out of stock" />
                                    @elseif ($isLow)
                                        <span class="inline-flex items-center rounded-full bg-warning-50 px-2.5 py-0.5 text-xs font-medium text-warning-700 dark:bg-warning-500/15 dark:text-warning-400">Low</span>
                                    @else
                                        <x-common.status-badge :active="true" active-label="OK" />
                                    @endif
                                </td>
                                <td class="px-4 py-3 whitespace-nowrap text-end">
                                    @can('inventory.adjust')
                                        <a href="{{ route('inventory.edit', $product) }}"
                                            class="text-brand-500 hover:text-brand-600 text-sm font-medium">Edit</a>
                                    @endcan
                                </td>
                            </tr>
                        @empty
                            <x-common.empty-state
                                colspan="7"
                                title="No inventory records found"
                                description="Try adjusting your filters or add products first."
                            >
                                <x-slot:actions>
                                    @if ($hasFilters)
                                        <x-common.btn-secondary href="{{ route('inventory.index') }}">Clear Filters</x-common.btn-secondary>
                                    @endif
                                </x-slot:actions>
                            </x-common.empty-state>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if ($products->hasPages())
                <div class="border-t border-gray-200 px-4 py-3 dark:border-gray-800">{{ $products->links() }}</div>
            @endif
        </div>
    </div>
@endsection
