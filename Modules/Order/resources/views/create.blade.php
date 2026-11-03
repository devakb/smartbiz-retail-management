@extends('layouts.app')

@section('content')
    <div>
        <x-common.page-breadcrumb pageTitle="Create order" />

        <div class="rounded-2xl border border-gray-200 bg-white p-5 lg:p-6 dark:border-gray-800 dark:bg-white/3">
            <h3 class="mb-5 text-lg font-semibold text-gray-800 lg:mb-7 dark:text-white/90">Create order</h3>

            <form id="order-create-form" method="POST" action="{{ route('order.store') }}" data-ajax="true">
                @csrf

                <div class="grid grid-cols-1 gap-5 md:grid-cols-2">
                    <div>
                        <label for="customer_id" class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
                            Customer<span class="text-error-500">*</span>
                        </label>
                        <select id="customer_id" name="customer_id" required
                            class="js-tom-select"
                            data-placeholder="Select customer">
                            <option value="">Select customer</option>
                            @foreach ($customers as $customer)
                                <option value="{{ $customer->id }}">
                                    {{ $customer->name }}{{ $customer->is_walk_in ? ' (Walk-in)' : '' }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label for="status" class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Status</label>
                        <select id="status" name="status"
                            class="js-tom-select"
                            data-placeholder="Status">
                            @foreach ($statuses as $status)
                                <option value="{{ $status->value }}" @selected($status->value === 'confirmed')>
                                    {{ ucfirst($status->value) }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="md:col-span-2">
                        <label for="notes" class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Notes</label>
                        <textarea id="notes" name="notes" rows="2"
                            class="dark:bg-dark-900 shadow-theme-xs focus:border-brand-300 focus:ring-brand-500/10 dark:focus:border-brand-800 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 focus:ring-3 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90"></textarea>
                    </div>
                </div>

                <div class="mt-6">
                    <div class="mb-3 flex items-center justify-between">
                        <h4 class="text-base font-semibold text-gray-800 dark:text-white/90">Items</h4>
                        <button type="button" id="add-item-row"
                            class="inline-flex items-center justify-center rounded-lg border border-gray-300 px-3 py-2 text-sm font-medium text-gray-700 transition hover:bg-gray-50 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-white/5">
                            Add item
                        </button>
                    </div>

                    <div id="order-items" class="space-y-3"></div>
                    <p class="mt-2 text-xs text-gray-500">Line prices and totals are calculated on the server from current product prices.</p>
                </div>

                <div class="mt-6 flex items-center gap-3">
                    <button type="submit"
                        class="bg-brand-500 hover:bg-brand-600 inline-flex items-center justify-center rounded-lg px-5 py-3 text-sm font-medium text-white transition">
                        Create order
                    </button>
                    <a href="{{ route('order.index') }}"
                        class="inline-flex items-center justify-center rounded-lg border border-gray-300 px-5 py-3 text-sm font-medium text-gray-700 transition hover:bg-gray-50 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-white/5">
                        Cancel
                    </a>
                </div>
            </form>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const products = @json($products);
            const container = document.getElementById('order-items');
            let rowIndex = 0;

            function productOptions() {
                return products.map(p =>
                    `<option value="${p.id}" data-price="${p.selling_price}" data-tax="${p.tax_percent}">
                        ${p.name} (${p.sku || p.upc}) — ${p.selling_price}
                    </option>`
                ).join('');
            }

            function addRow() {
                const index = rowIndex++;
                const row = document.createElement('div');
                row.className = 'grid grid-cols-1 gap-3 rounded-lg border border-gray-200 p-3 md:grid-cols-12 dark:border-gray-800';
                row.innerHTML = `
                    <div class="md:col-span-7">
                        <label class="mb-1 block text-xs text-gray-500">Product</label>
                        <select name="items[${index}][product_id]" required
                            class="js-tom-select"
                            data-placeholder="Select product">
                            <option value="">Select product</option>
                            ${productOptions()}
                        </select>
                    </div>
                    <div class="md:col-span-3">
                        <label class="mb-1 block text-xs text-gray-500">Qty</label>
                        <input type="number" name="items[${index}][quantity]" min="0.001" step="0.001" value="1" required
                            class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-3 text-sm dark:border-gray-700 dark:bg-gray-900" />
                    </div>
                    <div class="md:col-span-2 flex items-end">
                        <button type="button" class="remove-item h-11 w-full rounded-lg border border-gray-300 text-sm text-error-500 dark:border-gray-700">
                            Remove
                        </button>
                    </div>
                `;
                container.appendChild(row);
                row.querySelector('.remove-item').addEventListener('click', () => {
                    if (container.children.length > 1) {
                        row.remove();
                    }
                });
                if (window.SmartBizTomSelect) {
                    window.SmartBizTomSelect.init(row);
                }
            }

            document.getElementById('add-item-row').addEventListener('click', addRow);
            addRow();

            if (window.SmartBizValidation) {
                window.SmartBizValidation.init('#order-create-form', {
                    customer_id: { required: true },
                });
            }
        });
    </script>
@endpush
