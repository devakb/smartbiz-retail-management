@extends('layouts.app')

@section('content')
    <div>
        <x-common.page-breadcrumb pageTitle="Create offer" />

        <div class="rounded-2xl border border-gray-200 bg-white p-5 lg:p-6 dark:border-gray-800 dark:bg-white/3">
            <h3 class="mb-5 text-lg font-semibold text-gray-800 lg:mb-7 dark:text-white/90">Create offer</h3>

            <form id="offer-create-form" method="POST" action="{{ route('offer.store') }}" data-ajax="true">
                @csrf
                @include('offer::partials.form', ['offer' => null])

                <div class="mt-6 flex items-center gap-3">
                    <button type="submit"
                        class="bg-brand-500 hover:bg-brand-600 inline-flex items-center justify-center rounded-lg px-5 py-3 text-sm font-medium text-white transition">
                        Create offer
                    </button>
                    <a href="{{ route('offer.index') }}"
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
            const scopeSelect = document.getElementById('scope');
            const productFields = document.getElementById('product-scope-fields');
            const categoryFields = document.getElementById('category-scope-fields');

            function syncScopeFields() {
                const scope = scopeSelect?.value;
                if (productFields) productFields.style.display = scope === 'product' ? '' : 'none';
                if (categoryFields) categoryFields.style.display = scope === 'category' ? '' : 'none';
            }

            scopeSelect?.addEventListener('change', syncScopeFields);
            scopeSelect?.tomselect?.on('change', syncScopeFields);
            syncScopeFields();

            if (window.SmartBizValidation) {
                window.SmartBizValidation.init('#offer-create-form', {
                    name: { required: true, maxlength: 255 },
                    type: { required: true },
                    value: { required: true },
                    scope: { required: true },
                });
            }
        });
    </script>
@endpush
