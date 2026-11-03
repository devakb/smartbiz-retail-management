@php
    $selectedProducts = collect(old('product_ids', $offer?->products?->pluck('id')->all() ?? []))->map(fn ($id) => (string) $id);
    $selectedCategories = collect(old('category_ids', $offer?->categories?->pluck('id')->all() ?? []))->map(fn ($id) => (string) $id);
    $currentScope = old('scope', $offer?->scope?->value ?? 'order');
@endphp

<div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
    <div>
        <label for="name" class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
            Name<span class="text-error-500">*</span>
        </label>
        <input type="text" id="name" name="name" value="{{ old('name', $offer?->name) }}" required maxlength="255"
            class="dark:bg-dark-900 shadow-theme-xs focus:border-brand-300 focus:ring-brand-500/10 dark:focus:border-brand-800 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 focus:ring-3 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90" />
    </div>

    <div>
        <label for="coupon_code" class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
            Coupon code
        </label>
        <input type="text" id="coupon_code" name="coupon_code" value="{{ old('coupon_code', $offer?->coupon_code) }}" maxlength="64"
            class="dark:bg-dark-900 shadow-theme-xs focus:border-brand-300 focus:ring-brand-500/10 dark:focus:border-brand-800 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 focus:ring-3 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90" />
        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Optional. Must be unique when set.</p>
    </div>

    <div>
        <label for="type" class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
            Type<span class="text-error-500">*</span>
        </label>
        <select id="type" name="type" required
            class="js-tom-select"
            data-placeholder="Select type">
            @foreach ($types as $type)
                <option value="{{ $type->value }}" @selected((string) old('type', $offer?->type?->value) === $type->value)>
                    {{ ucfirst($type->value) }}
                </option>
            @endforeach
        </select>
    </div>

    <div>
        <label for="value" class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
            Value<span class="text-error-500">*</span>
        </label>
        <input type="number" step="0.01" min="0.01" id="value" name="value"
            value="{{ old('value', $offer?->value) }}" required
            class="dark:bg-dark-900 shadow-theme-xs focus:border-brand-300 focus:ring-brand-500/10 dark:focus:border-brand-800 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 focus:ring-3 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90" />
    </div>

    <div>
        <label for="min_purchase" class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
            Min purchase amount
        </label>
        <input type="number" step="0.01" min="0" id="min_purchase" name="min_purchase"
            value="{{ old('min_purchase', $offer?->min_purchase) }}"
            class="dark:bg-dark-900 shadow-theme-xs focus:border-brand-300 focus:ring-brand-500/10 dark:focus:border-brand-800 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 focus:ring-3 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90" />
    </div>

    <div>
        <label for="max_discount" class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
            Max discount amount
        </label>
        <input type="number" step="0.01" min="0" id="max_discount" name="max_discount"
            value="{{ old('max_discount', $offer?->max_discount) }}"
            class="dark:bg-dark-900 shadow-theme-xs focus:border-brand-300 focus:ring-brand-500/10 dark:focus:border-brand-800 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 focus:ring-3 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90" />
    </div>

    <div>
        <label for="usage_limit" class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
            Usage limit
        </label>
        <input type="number" min="1" step="1" id="usage_limit" name="usage_limit"
            value="{{ old('usage_limit', $offer?->usage_limit) }}"
            class="dark:bg-dark-900 shadow-theme-xs focus:border-brand-300 focus:ring-brand-500/10 dark:focus:border-brand-800 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 focus:ring-3 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90" />
    </div>

    <div>
        <label for="per_customer_limit" class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
            Per customer limit
        </label>
        <input type="number" min="1" step="1" id="per_customer_limit" name="per_customer_limit"
            value="{{ old('per_customer_limit', $offer?->per_customer_limit) }}"
            class="dark:bg-dark-900 shadow-theme-xs focus:border-brand-300 focus:ring-brand-500/10 dark:focus:border-brand-800 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 focus:ring-3 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90" />
    </div>

    <div>
        <label for="starts_at" class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
            Starts at
        </label>
        <input type="datetime-local" id="starts_at" name="starts_at"
            value="{{ old('starts_at', optional($offer?->starts_at)?->format('Y-m-d\TH:i')) }}"
            class="dark:bg-dark-900 shadow-theme-xs focus:border-brand-300 focus:ring-brand-500/10 dark:focus:border-brand-800 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 focus:ring-3 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90" />
    </div>

    <div>
        <label for="ends_at" class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
            Ends at
        </label>
        <input type="datetime-local" id="ends_at" name="ends_at"
            value="{{ old('ends_at', optional($offer?->ends_at)?->format('Y-m-d\TH:i')) }}"
            class="dark:bg-dark-900 shadow-theme-xs focus:border-brand-300 focus:ring-brand-500/10 dark:focus:border-brand-800 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 focus:ring-3 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90" />
    </div>

    <div>
        <label for="scope" class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
            Scope<span class="text-error-500">*</span>
        </label>
        <select id="scope" name="scope" required
            class="js-tom-select"
            data-placeholder="Select scope">
            @foreach ($scopes as $scope)
                <option value="{{ $scope->value }}" @selected((string) $currentScope === $scope->value)>
                    {{ ucfirst($scope->value) }}
                </option>
            @endforeach
        </select>
    </div>

    <div class="sm:col-span-2" id="product-scope-fields" @if ($currentScope !== 'product') style="display:none" @endif>
        <label for="product_ids" class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
            Products<span class="text-error-500">*</span>
        </label>
        <select id="product_ids" name="product_ids[]" multiple
            class="js-tom-select"
            data-placeholder="Select products">
            @foreach ($products as $product)
                <option value="{{ $product->id }}" @selected($selectedProducts->contains((string) $product->id))>
                    {{ $product->name }}
                </option>
            @endforeach
        </select>
    </div>

    <div class="sm:col-span-2" id="category-scope-fields" @if ($currentScope !== 'category') style="display:none" @endif>
        <label for="category_ids" class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
            Categories<span class="text-error-500">*</span>
        </label>
        <select id="category_ids" name="category_ids[]" multiple
            class="js-tom-select"
            data-placeholder="Select categories">
            @foreach ($categories as $category)
                <option value="{{ $category->id }}" @selected($selectedCategories->contains((string) $category->id))>
                    {{ $category->name }}
                </option>
            @endforeach
        </select>
    </div>

    <div class="sm:col-span-2">
        <label class="inline-flex items-center gap-2 text-sm font-medium text-gray-700 dark:text-gray-400">
            <input type="hidden" name="is_active" value="0" />
            <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $offer?->is_active ?? true))
                class="text-brand-500 focus:ring-brand-500/20 h-4 w-4 rounded border-gray-300 dark:border-gray-700" />
            Active
        </label>
    </div>
</div>
