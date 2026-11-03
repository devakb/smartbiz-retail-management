@extends('layouts.app')

@section('content')
    <div>
        <x-common.page-breadcrumb pageTitle="Edit product" />

        <div class="rounded-2xl border border-gray-200 bg-white p-5 lg:p-6 dark:border-gray-800 dark:bg-white/3">
            <h3 class="mb-5 text-lg font-semibold text-gray-800 lg:mb-7 dark:text-white/90">Edit product</h3>

            <form id="product-edit-form" method="POST" action="{{ route('product.update', $product) }}" data-ajax="true"
                enctype="multipart/form-data">
                @csrf
                @method('PUT')
                @include('product::partials.form', [
                    'product' => $product,
                    'categories' => $categories,
                    'brands' => $brands,
                    'taxClasses' => $taxClasses,
                ])

                <div class="mt-6 flex items-center gap-3">
                    <button type="submit"
                        class="bg-brand-500 hover:bg-brand-600 inline-flex items-center justify-center rounded-lg px-5 py-3 text-sm font-medium text-white transition">
                        Save changes
                    </button>
                    <a href="{{ route('product.index') }}"
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
            const suggestSkuUrl = @json(route('product.suggest-sku'));

            document.getElementById('suggest-sku-btn')?.addEventListener('click', async function () {
                const name = document.getElementById('name')?.value || '';
                try {
                    const response = await fetch(suggestSkuUrl + '?name=' + encodeURIComponent(name), {
                        headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                        credentials: 'same-origin',
                    });
                    const payload = await response.json();
                    if (payload.data?.sku) {
                        document.getElementById('sku').value = payload.data.sku;
                    }
                } catch (e) {}
            });

            if (window.jQuery && jQuery.fn.validate) {
                jQuery('#product-edit-form').validate({
                    rules: {
                        upc: { required: true, maxlength: 64 },
                        sku: { required: true, maxlength: 64 },
                        name: { required: true, maxlength: 255 },
                        category_id: { required: true },
                        mrp: { required: true, number: true, min: 0 },
                        selling_price: { required: true, number: true, min: 0 },
                    },
                    errorElement: 'p',
                    errorClass: 'mt-1.5 text-xs text-error-500',
                });
            }
        });
    </script>
    @include('product::partials.quick-add-scripts')
@endpush
