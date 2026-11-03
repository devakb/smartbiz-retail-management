@extends('layouts.app')

@section('content')
    <div>
        <x-common.page-breadcrumb pageTitle="Edit stock" />

        <div class="rounded-2xl border border-gray-200 bg-white p-5 lg:p-6 dark:border-gray-800 dark:bg-white/3">
            <h3 class="mb-2 text-lg font-semibold text-gray-800 dark:text-white/90">{{ $product->name }}</h3>
            <p class="mb-5 text-sm text-gray-500 dark:text-gray-400">
                UPC {{ $product->upc }} · SKU {{ $product->sku }}
                @if ($product->category)
                    · {{ $product->category->name }}
                @endif
            </p>

            <form id="inventory-edit-form" method="POST" action="{{ route('inventory.update', $product) }}" data-ajax="true">
                @csrf
                @method('PUT')

                <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
                    <div>
                        <label for="stock_quantity" class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
                            Stock quantity<span class="text-error-500">*</span>
                        </label>
                        <input type="number" step="0.001" min="0" id="stock_quantity" name="stock_quantity"
                            value="{{ old('stock_quantity', $product->stock_quantity) }}" required
                            class="dark:bg-dark-900 shadow-theme-xs focus:border-brand-300 focus:ring-brand-500/10 dark:focus:border-brand-800 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 focus:ring-3 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90" />
                    </div>

                    <div>
                        <label for="min_stock_level" class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
                            Min stock level<span class="text-error-500">*</span>
                        </label>
                        <input type="number" step="0.001" min="0" id="min_stock_level" name="min_stock_level"
                            value="{{ old('min_stock_level', $product->min_stock_level) }}" required
                            class="dark:bg-dark-900 shadow-theme-xs focus:border-brand-300 focus:ring-brand-500/10 dark:focus:border-brand-800 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 focus:ring-3 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90" />
                    </div>

                    <div class="sm:col-span-2">
                        <label for="reason" class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
                            Reason<span class="text-error-500">*</span>
                        </label>
                        <input type="text" id="reason" name="reason" value="{{ old('reason') }}" required maxlength="255"
                            placeholder="Why is stock being updated?"
                            class="dark:bg-dark-900 shadow-theme-xs focus:border-brand-300 focus:ring-brand-500/10 dark:focus:border-brand-800 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 focus:ring-3 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90" />
                    </div>
                </div>

                <div class="mt-6 flex items-center gap-3">
                    <button type="submit"
                        class="bg-brand-500 hover:bg-brand-600 inline-flex items-center justify-center rounded-lg px-5 py-3 text-sm font-medium text-white transition">
                        Save
                    </button>
                    <a href="{{ route('inventory.index') }}"
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
            if (window.jQuery && jQuery.fn.validate) {
                jQuery('#inventory-edit-form').validate({
                    rules: {
                        stock_quantity: { required: true, number: true, min: 0 },
                        min_stock_level: { required: true, number: true, min: 0 },
                        reason: { required: true, maxlength: 255 },
                    },
                    errorElement: 'p',
                    errorClass: 'mt-1.5 text-xs text-error-500',
                });
            }
        });
    </script>
@endpush
