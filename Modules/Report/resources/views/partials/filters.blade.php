@php
    $show = $showFilters ?? [];
    $action = $action ?? url()->current();
@endphp

<form method="GET" action="{{ $action }}" class="mb-5 grid grid-cols-1 gap-3 md:grid-cols-2 xl:grid-cols-4">
    @if (in_array('date', $show, true))
        <input type="date" name="date_from" value="{{ $filters['date_from'] }}"
            class="dark:bg-dark-900 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 text-sm dark:border-gray-700 dark:bg-gray-900 dark:text-white/90" />
        <input type="date" name="date_to" value="{{ $filters['date_to'] }}"
            class="dark:bg-dark-900 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 text-sm dark:border-gray-700 dark:bg-gray-900 dark:text-white/90" />
    @endif

    @if (in_array('customer', $show, true))
        <select name="customer_id" class="js-tom-select" data-placeholder="All customers" data-allow-clear>
            <option value="">All customers</option>
            @foreach ($filterOptions['customers'] as $customer)
                <option value="{{ $customer->id }}" @selected((string) $filters['customer_id'] === (string) $customer->id)>
                    {{ $customer->name }}
                </option>
            @endforeach
        </select>
    @endif

    @if (in_array('product', $show, true))
        <select name="product_id" class="js-tom-select" data-placeholder="All products" data-allow-clear>
            <option value="">All products</option>
            @foreach ($filterOptions['products'] as $product)
                <option value="{{ $product->id }}" @selected((string) $filters['product_id'] === (string) $product->id)>
                    {{ $product->name }}
                </option>
            @endforeach
        </select>
    @endif

    @if (in_array('category', $show, true))
        <select name="category_id" class="js-tom-select" data-placeholder="All categories" data-allow-clear>
            <option value="">All categories</option>
            @foreach ($filterOptions['categories'] as $category)
                <option value="{{ $category->id }}" @selected((string) $filters['category_id'] === (string) $category->id)>
                    {{ $category->name }}
                </option>
            @endforeach
        </select>
    @endif

    @if (in_array('brand', $show, true))
        <select name="brand_id" class="js-tom-select" data-placeholder="All brands" data-allow-clear>
            <option value="">All brands</option>
            @foreach ($filterOptions['brands'] as $brand)
                <option value="{{ $brand->id }}" @selected((string) $filters['brand_id'] === (string) $brand->id)>
                    {{ $brand->name }}
                </option>
            @endforeach
        </select>
    @endif

    @if (in_array('payment_method', $show, true))
        <select name="payment_method" class="js-tom-select" data-placeholder="All payment methods" data-allow-clear>
            <option value="">All payment methods</option>
            @foreach ($filterOptions['paymentMethods'] as $method)
                <option value="{{ $method->value }}" @selected($filters['payment_method'] === $method->value)>
                    {{ strtoupper($method->value) }}
                </option>
            @endforeach
        </select>
    @endif

    @if (in_array('order_status', $show, true))
        <select name="order_status" class="js-tom-select" data-placeholder="All statuses" data-allow-clear>
            <option value="">All statuses</option>
            @foreach ($filterOptions['orderStatuses'] as $status)
                <option value="{{ $status->value }}" @selected($filters['order_status'] === $status->value)>
                    {{ ucfirst($status->value) }}
                </option>
            @endforeach
        </select>
    @endif

    @if (in_array('movement_type', $show, true))
        <select name="movement_type" class="js-tom-select" data-placeholder="All movement types" data-allow-clear>
            <option value="">All movement types</option>
            @foreach ($movementTypes ?? [] as $type)
                <option value="{{ $type->value }}" @selected(($filters['movement_type'] ?? null) === $type->value)>
                    {{ ucfirst(str_replace('_', ' ', $type->value)) }}
                </option>
            @endforeach
        </select>
    @endif

    @if (in_array('offer', $show, true))
        <select name="offer_id" class="js-tom-select" data-placeholder="All offers" data-allow-clear>
            <option value="">All offers</option>
            @foreach ($offers ?? [] as $offer)
                <option value="{{ $offer->id }}" @selected((string) ($filters['offer_id'] ?? '') === (string) $offer->id)>
                    {{ $offer->name }}
                </option>
            @endforeach
        </select>
    @endif

    <div class="flex gap-2">
        <button type="submit"
            class="inline-flex items-center justify-center rounded-lg border border-gray-300 px-4 py-2.5 text-sm font-medium text-gray-700 transition hover:bg-gray-50 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-white/5">
            Filter
        </button>
        <a href="{{ $action }}"
            class="inline-flex items-center justify-center rounded-lg border border-gray-300 px-4 py-2.5 text-sm font-medium text-gray-700 transition hover:bg-gray-50 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-white/5">
            Reset
        </a>
    </div>
</form>
