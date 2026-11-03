@extends('layouts.app')

@section('content')
    <div>
        <x-common.page-breadcrumb pageTitle="Edit tax class" />

        <div class="rounded-2xl border border-gray-200 bg-white p-5 lg:p-6 dark:border-gray-800 dark:bg-white/3">
            <h3 class="mb-5 text-lg font-semibold text-gray-800 lg:mb-7 dark:text-white/90">Edit tax class</h3>

            <form id="tax-class-edit-form" method="POST" action="{{ route('tax-class.update', $taxClass) }}" data-ajax="true">
                @csrf
                @method('PUT')
                @include('taxclass::partials.form', ['taxClass' => $taxClass])

                <div class="mt-6 flex items-center gap-3">
                    <button type="submit"
                        class="bg-brand-500 hover:bg-brand-600 inline-flex items-center justify-center rounded-lg px-5 py-3 text-sm font-medium text-white transition">
                        Save changes
                    </button>
                    <a href="{{ route('tax-class.index') }}"
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
            if (window.SmartBizValidation) {
                window.SmartBizValidation.init('#tax-class-edit-form', {
                    name: { required: true, maxlength: 255 },
                    rate: { required: true, number: true, min: 0, max: 100 },
                });
            }
        });
    </script>
@endpush
