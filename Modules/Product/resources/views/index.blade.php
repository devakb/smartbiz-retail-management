@extends('layouts.app')

@section('content')
    <div>
        <x-common.list-header
            title="Products"
            description="Manage your product catalog, inventory, and details in one place."
        >
            <x-slot:actions>
                @can('products.create')
                    <x-common.btn-primary href="{{ route('product.create') }}">
                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M12 5v14M5 12h14" stroke-linecap="round" />
                        </svg>
                        Add Product
                    </x-common.btn-primary>
                @endcan
            </x-slot:actions>
        </x-common.list-header>

        <x-common.stats-row>
            <x-common.stat-card label="Total Products" :value="$stats['total']" tone="brand">
                <x-slot:icon>
                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                        <path d="M3 8.5 12 4l9 4.5v7L12 20l-9-4.5v-7Z" stroke-linejoin="round" />
                        <path d="M12 12v8M3 8.5l9 3.5 9-3.5" stroke-linecap="round" stroke-linejoin="round" />
                    </svg>
                </x-slot:icon>
            </x-common.stat-card>
            <x-common.stat-card label="In Stock" :value="$stats['in_stock']" tone="success">
                <x-slot:icon>
                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                        <path d="M3 8.5 12 4l9 4.5v7L12 20l-9-4.5v-7Z" stroke-linejoin="round" />
                        <path d="m9 12 2 2 4-4" stroke-linecap="round" stroke-linejoin="round" />
                    </svg>
                </x-slot:icon>
            </x-common.stat-card>
            <x-common.stat-card label="Low Stock" :value="$stats['low_stock']" tone="warning">
                <x-slot:icon>
                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                        <path d="M12 9v4M12 17h.01M10.3 4.3 2.6 17.5A2 2 0 0 0 4.3 20.5h15.4a2 2 0 0 0 1.7-3L13.7 4.3a2 2 0 0 0-3.4 0Z" stroke-linecap="round" stroke-linejoin="round" />
                    </svg>
                </x-slot:icon>
            </x-common.stat-card>
            <x-common.stat-card label="Out of Stock" :value="$stats['out_of_stock']" tone="error">
                <x-slot:icon>
                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                        <circle cx="12" cy="12" r="9" />
                        <path d="m15 9-6 6M9 9l6 6" stroke-linecap="round" />
                    </svg>
                </x-slot:icon>
            </x-common.stat-card>
        </x-common.stats-row>

        <x-common.list-toolbar :action="route('product.index')" :reset-url="route('product.index')">
            <x-common.search-field
                name="search"
                :value="$search"
                placeholder="Search by name, UPC, SKU..."
                class="xl:min-w-72 xl:max-w-sm"
            />

            <x-common.select-field name="category_id" placeholder="All Categories" :allow-clear="true">
                <option value="">All Categories</option>
                @foreach ($categories as $category)
                    <option value="{{ $category->id }}" @selected((string) $categoryId === (string) $category->id)>
                        {{ $category->name }}
                    </option>
                @endforeach
            </x-common.select-field>

            <x-common.select-field name="brand_id" placeholder="All Brands" :allow-clear="true">
                <option value="">All Brands</option>
                @foreach ($brands as $brand)
                    <option value="{{ $brand->id }}" @selected((string) $brandId === (string) $brand->id)>
                        {{ $brand->name }}
                    </option>
                @endforeach
            </x-common.select-field>

            <x-common.select-field name="is_active" placeholder="All Statuses" :allow-clear="true" wrapper-class="w-full xl:w-40">
                <option value="">All Statuses</option>
                <option value="1" @selected((string) $isActive === '1')>Active</option>
                <option value="0" @selected((string) $isActive === '0')>Inactive</option>
            </x-common.select-field>
        </x-common.list-toolbar>

        <div class="overflow-hidden rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/3">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-800">
                    <thead class="bg-gray-50 dark:bg-white/5">
                        <tr class="text-left text-xs font-medium tracking-wider text-gray-500 uppercase dark:text-gray-400">
                            <th class="px-4 py-3">Name</th>
                            <th class="px-4 py-3">UPC</th>
                            <th class="px-4 py-3">SKU</th>
                            <th class="px-4 py-3">Category</th>
                            <th class="px-4 py-3">Brand</th>
                            <th class="px-4 py-3">Stock</th>
                            <th class="px-4 py-3">Status</th>
                            <th class="px-4 py-3 text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                        @forelse ($products as $product)
                            <tr class="text-sm text-gray-700 dark:text-gray-300">
                                <td class="px-4 py-3 whitespace-nowrap font-medium text-gray-800 dark:text-white/90">{{ $product->name }}</td>
                                <td class="px-4 py-3 whitespace-nowrap">{{ $product->upc }}</td>
                                <td class="px-4 py-3 whitespace-nowrap">{{ $product->sku }}</td>
                                <td class="px-4 py-3 whitespace-nowrap">{{ $product->category?->name ?? '—' }}</td>
                                <td class="px-4 py-3 whitespace-nowrap">{{ $product->brand?->name ?? '—' }}</td>
                                <td class="px-4 py-3 whitespace-nowrap">{{ $product->stock_quantity }}</td>
                                <td class="px-4 py-3 whitespace-nowrap">
                                    <x-common.status-badge :active="(bool) $product->is_active" />
                                </td>
                                <td class="px-4 py-3 whitespace-nowrap text-end">
                                    <div class="inline-flex items-center gap-2">
                                        @can('products.update')
                                            <a href="{{ route('product.edit', $product) }}"
                                                class="text-brand-500 hover:text-brand-600 text-sm font-medium">Edit</a>
                                        @endcan

                                        @can('products.delete')
                                            <form method="POST" action="{{ route('product.destroy', $product) }}" data-ajax="true"
                                                data-confirm="Delete this product?" class="inline">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="text-error-500 hover:text-error-600 text-sm font-medium">
                                                    Delete
                                                </button>
                                            </form>
                                        @endcan
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <x-common.empty-state
                                colspan="8"
                                title="No products found"
                                description="Try adjusting your filters or add a new product to get started."
                            >
                                <x-slot:actions>
                                    @can('products.create')
                                        <x-common.btn-primary href="{{ route('product.create') }}">
                                            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                <path d="M12 5v14M5 12h14" stroke-linecap="round" />
                                            </svg>
                                            Add Product
                                        </x-common.btn-primary>
                                    @endcan
                                    @if ($hasFilters)
                                        <x-common.btn-secondary href="{{ route('product.index') }}">
                                            Clear Filters
                                        </x-common.btn-secondary>
                                    @endif
                                </x-slot:actions>
                            </x-common.empty-state>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if ($products->hasPages())
                <div class="border-t border-gray-200 px-4 py-3 dark:border-gray-800">
                    {{ $products->links() }}
                </div>
            @endif
        </div>
    </div>
@endsection
