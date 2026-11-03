@php
    $isEdit = isset($product) && $product;
    $taxClasses = $taxClasses ?? collect();
@endphp

<div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
    <div>
        <label for="upc" class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
            UPC<span class="text-error-500">*</span>
        </label>
        <input type="text" id="upc" name="upc" value="{{ old('upc', $product?->upc) }}" required maxlength="64"
            class="dark:bg-dark-900 shadow-theme-xs focus:border-brand-300 focus:ring-brand-500/10 dark:focus:border-brand-800 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 focus:ring-3 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90" />
        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">{{ __('UPC is never auto-generated. Enter the barcode manually or scan it.') }}</p>
    </div>

    <div>
        <label for="sku" class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
            SKU<span class="text-error-500">*</span>
        </label>
        <div class="flex gap-2">
            <input type="text" id="sku" name="sku" value="{{ old('sku', $product?->sku) }}" required maxlength="64"
                class="dark:bg-dark-900 shadow-theme-xs focus:border-brand-300 focus:ring-brand-500/10 dark:focus:border-brand-800 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 focus:ring-3 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90" />
            <button type="button" id="suggest-sku-btn"
                class="inline-flex shrink-0 items-center justify-center rounded-lg border border-gray-300 px-3 py-2 text-sm font-medium text-gray-700 transition hover:bg-gray-50 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-white/5">
                Suggest
            </button>
        </div>
    </div>

    <div class="sm:col-span-2">
        <label for="name" class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
            Name<span class="text-error-500">*</span>
        </label>
        <input type="text" id="name" name="name" value="{{ old('name', $product?->name) }}" required maxlength="255"
            class="dark:bg-dark-900 shadow-theme-xs focus:border-brand-300 focus:ring-brand-500/10 dark:focus:border-brand-800 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 focus:ring-3 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90" />
    </div>

    <div>
        <div class="mb-1.5 flex items-center justify-between gap-2">
            <label for="category_id" class="block text-sm font-medium text-gray-700 dark:text-gray-400">
                {{ __('Category') }}<span class="text-error-500">*</span>
            </label>
            @can('categories.create')
                <x-ui.dialog>
                    <button type="button" @click="show()"
                        class="text-brand-500 hover:text-brand-600 dark:text-brand-400 dark:hover:text-brand-300 text-sm font-medium whitespace-nowrap">
                        {{ __('Add Category') }}
                    </button>

                    <x-ui.dialog.content class="max-w-sm">
                        <x-ui.dialog.header class="gap-1 pe-10 text-start">
                            <x-ui.dialog.title class="text-lg font-semibold text-gray-800 dark:text-white/90">
                                {{ __('Add Category') }}
                            </x-ui.dialog.title>
                            <x-ui.dialog.description class="text-sm text-gray-500 dark:text-gray-400">
                                {{ __('Create a category and select it for this product.') }}
                            </x-ui.dialog.description>
                        </x-ui.dialog.header>

                        <form id="quick-add-category-form" method="POST" action="{{ route('category.store') }}" class="mt-4 space-y-4">
                            @csrf
                            <input type="hidden" name="is_active" value="1" />

                            <div>
                                <label for="quick_category_name" class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
                                    {{ __('Name') }}<span class="text-error-500">*</span>
                                </label>
                                <input type="text" id="quick_category_name" name="name" required maxlength="255"
                                    class="dark:bg-dark-900 shadow-theme-xs focus:border-brand-300 focus:ring-brand-500/10 dark:focus:border-brand-800 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 focus:ring-3 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90" />
                            </div>

                            <div>
                                <label for="quick_category_parent_id" class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
                                    {{ __('Parent category') }}
                                </label>
                                <select id="quick_category_parent_id" name="parent_id"
                                    class="dark:bg-dark-900 shadow-theme-xs focus:border-brand-300 focus:ring-brand-500/10 dark:focus:border-brand-800 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 focus:ring-3 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90">
                                    <option value="">{{ __('None') }}</option>
                                    @foreach ($categories as $category)
                                        <option value="{{ $category->id }}">{{ $category->name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <x-ui.dialog.footer class="mx-0 mb-0 gap-3 p-0 pt-2 sm:justify-end">
                                <x-ui.dialog.close as-child>
                                    <x-ui.button type="button" variant="outline">{{ __('Cancel') }}</x-ui.button>
                                </x-ui.dialog.close>
                                <x-ui.button type="submit">{{ __('Save Category') }}</x-ui.button>
                            </x-ui.dialog.footer>
                        </form>
                    </x-ui.dialog.content>
                </x-ui.dialog>
            @endcan
        </div>
        <select id="category_id" name="category_id" required
            class="js-tom-select"
            data-placeholder="{{ __('Select category') }}">
            <option value="">{{ __('Select category') }}</option>
            @foreach ($categories as $category)
                <option value="{{ $category->id }}" @selected((string) old('category_id', $product?->category_id) === (string) $category->id)>
                    {{ $category->name }}
                </option>
            @endforeach
        </select>
    </div>

    <div>
        <div class="mb-1.5 flex items-center justify-between gap-2">
            <label for="brand_id" class="block text-sm font-medium text-gray-700 dark:text-gray-400">
                {{ __('Brand') }}
            </label>
            @can('brands.create')
                <x-ui.dialog>
                    <button type="button" @click="show()"
                        class="text-brand-500 hover:text-brand-600 dark:text-brand-400 dark:hover:text-brand-300 text-sm font-medium whitespace-nowrap">
                        {{ __('Add Brand') }}
                    </button>

                    <x-ui.dialog.content class="max-w-sm">
                        <x-ui.dialog.header class="gap-1 pe-10 text-start">
                            <x-ui.dialog.title class="text-lg font-semibold text-gray-800 dark:text-white/90">
                                {{ __('Add Brand') }}
                            </x-ui.dialog.title>
                            <x-ui.dialog.description class="text-sm text-gray-500 dark:text-gray-400">
                                {{ __('Create a brand and select it for this product.') }}
                            </x-ui.dialog.description>
                        </x-ui.dialog.header>

                        <form id="quick-add-brand-form" method="POST" action="{{ route('brand.store') }}" class="mt-4 space-y-4">
                            @csrf
                            <input type="hidden" name="is_active" value="1" />

                            <div>
                                <label for="quick_brand_name" class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
                                    {{ __('Name') }}<span class="text-error-500">*</span>
                                </label>
                                <input type="text" id="quick_brand_name" name="name" required maxlength="255"
                                    class="dark:bg-dark-900 shadow-theme-xs focus:border-brand-300 focus:ring-brand-500/10 dark:focus:border-brand-800 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 focus:ring-3 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90" />
                            </div>

                            <x-ui.dialog.footer class="mx-0 mb-0 gap-3 p-0 pt-2 sm:justify-end">
                                <x-ui.dialog.close as-child>
                                    <x-ui.button type="button" variant="outline">{{ __('Cancel') }}</x-ui.button>
                                </x-ui.dialog.close>
                                <x-ui.button type="submit">{{ __('Save Brand') }}</x-ui.button>
                            </x-ui.dialog.footer>
                        </form>
                    </x-ui.dialog.content>
                </x-ui.dialog>
            @endcan
        </div>
        <select id="brand_id" name="brand_id"
            class="js-tom-select"
            data-placeholder="{{ __('Select brand') }}"
            data-allow-clear>
            <option value="">{{ __('No brand') }}</option>
            @foreach ($brands as $brand)
                <option value="{{ $brand->id }}" @selected((string) old('brand_id', $product?->brand_id) === (string) $brand->id)>
                    {{ $brand->name }}
                </option>
            @endforeach
        </select>
    </div>

    <div class="sm:col-span-2">
        <label for="description" class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
            Description
        </label>
        <textarea id="description" name="description" rows="3"
            class="dark:bg-dark-900 shadow-theme-xs focus:border-brand-300 focus:ring-brand-500/10 dark:focus:border-brand-800 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 focus:ring-3 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90">{{ old('description', $product?->description) }}</textarea>
    </div>

    <div>
        <label for="mrp" class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
            MRP<span class="text-error-500">*</span>
        </label>
        <input type="number" step="0.01" min="0" id="mrp" name="mrp"
            value="{{ old('mrp', $product?->mrp ?? '0.00') }}" required
            class="dark:bg-dark-900 shadow-theme-xs focus:border-brand-300 focus:ring-brand-500/10 dark:focus:border-brand-800 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 focus:ring-3 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90" />
    </div>

    <div>
        <label for="selling_price" class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
            Selling price<span class="text-error-500">*</span>
        </label>
        <input type="number" step="0.01" min="0" id="selling_price" name="selling_price"
            value="{{ old('selling_price', $product?->selling_price ?? '0.00') }}" required
            class="dark:bg-dark-900 shadow-theme-xs focus:border-brand-300 focus:ring-brand-500/10 dark:focus:border-brand-800 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 focus:ring-3 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90" />
    </div>

    <div>
        <label for="tax_class_id" class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
            Tax class
        </label>
        <select id="tax_class_id" name="tax_class_id"
            class="js-tom-select"
            data-placeholder="Select tax class"
            data-allow-clear>
            <option value="">No tax</option>
            @foreach ($taxClasses as $taxClass)
                <option value="{{ $taxClass->id }}" @selected((string) old('tax_class_id', $product?->tax_class_id) === (string) $taxClass->id)>
                    {{ $taxClass->name }} ({{ number_format((float) $taxClass->rate, 2) }}%)
                </option>
            @endforeach
        </select>
    </div>

    <div>
        <label for="min_stock_level" class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
            Min stock level
        </label>
        <input type="number" step="0.001" min="0" id="min_stock_level" name="min_stock_level"
            value="{{ old('min_stock_level', $product?->min_stock_level ?? '0') }}"
            class="dark:bg-dark-900 shadow-theme-xs focus:border-brand-300 focus:ring-brand-500/10 dark:focus:border-brand-800 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 focus:ring-3 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90" />
    </div>

    <div>
        <label for="unit" class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
            Unit
        </label>
        <input type="text" id="unit" name="unit" value="{{ old('unit', $product?->unit ?? 'pcs') }}" maxlength="32"
            class="dark:bg-dark-900 shadow-theme-xs focus:border-brand-300 focus:ring-brand-500/10 dark:focus:border-brand-800 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 focus:ring-3 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90" />
    </div>

    <div>
        <label for="image" class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
            Image
        </label>
        <input type="file" id="image" name="image" accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp"
            class="dark:bg-dark-900 shadow-theme-xs focus:border-brand-300 focus:ring-brand-500/10 dark:focus:border-brand-800 block w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 focus:ring-3 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90" />
        @if ($product?->image)
            <div class="mt-3">
                <img src="{{ Storage::disk('public')->url($product->image) }}" alt="{{ $product->name }}"
                    class="h-16 w-16 rounded-lg object-cover" />
            </div>
        @endif
    </div>

    <div class="sm:col-span-2">
        <label class="inline-flex items-center gap-2 text-sm font-medium text-gray-700 dark:text-gray-400">
            <input type="hidden" name="is_active" value="0" />
            <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $product?->is_active ?? true))
                class="text-brand-500 focus:ring-brand-500/20 h-4 w-4 rounded border-gray-300 dark:border-gray-700" />
            Active
        </label>
    </div>
</div>
