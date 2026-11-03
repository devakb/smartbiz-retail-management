@extends('layouts.app')

@section('content')
    <div>
        <x-common.page-breadcrumb pageTitle="Settings" />

        <div class="rounded-2xl border border-gray-200 bg-white p-5 lg:p-6 dark:border-gray-800 dark:bg-white/3">
            <h3 class="mb-5 text-lg font-semibold text-gray-800 lg:mb-7 dark:text-white/90">Application settings</h3>

            <form id="settings-form" method="POST" action="{{ route('settings.update') }}" data-ajax="true">
                @csrf
                @method('PUT')

                <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
                    <div>
                        <label for="currency_code" class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
                            Currency code<span class="text-error-500">*</span>
                        </label>
                        <input type="text" id="currency_code" name="currency_code"
                            value="{{ old('currency_code', $settings['currency_code']) }}" required maxlength="10"
                            class="dark:bg-dark-900 shadow-theme-xs focus:border-brand-300 focus:ring-brand-500/10 dark:focus:border-brand-800 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 focus:ring-3 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90" />
                    </div>

                    <div>
                        <label for="currency_symbol" class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
                            Currency symbol<span class="text-error-500">*</span>
                        </label>
                        <input type="text" id="currency_symbol" name="currency_symbol"
                            value="{{ old('currency_symbol', $settings['currency_symbol']) }}" required maxlength="10"
                            class="dark:bg-dark-900 shadow-theme-xs focus:border-brand-300 focus:ring-brand-500/10 dark:focus:border-brand-800 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 focus:ring-3 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90" />
                    </div>

                    <div>
                        <label for="currency_position" class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
                            Currency position<span class="text-error-500">*</span>
                        </label>
                        <select id="currency_position" name="currency_position" required
                            class="js-tom-select"
                            data-placeholder="Currency position">
                            <option value="before" @selected(old('currency_position', $settings['currency_position']) === 'before')>Before amount</option>
                            <option value="after" @selected(old('currency_position', $settings['currency_position']) === 'after')>After amount</option>
                        </select>
                    </div>

                    <div>
                        <label for="currency_decimals" class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
                            Currency decimals<span class="text-error-500">*</span>
                        </label>
                        <input type="number" id="currency_decimals" name="currency_decimals" min="0" max="6"
                            value="{{ old('currency_decimals', $settings['currency_decimals']) }}" required
                            class="dark:bg-dark-900 shadow-theme-xs focus:border-brand-300 focus:ring-brand-500/10 dark:focus:border-brand-800 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 focus:ring-3 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90" />
                    </div>

                    <div class="sm:col-span-2">
                        <label for="theme_color" class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
                            Theme color<span class="text-error-500">*</span>
                        </label>
                        <div class="flex items-center gap-3">
                            <input type="color" id="theme_color_picker"
                                value="{{ old('theme_color', $settings['theme_color']) }}"
                                class="h-11 w-14 cursor-pointer rounded-lg border border-gray-300 bg-transparent p-1 dark:border-gray-700"
                                oninput="document.getElementById('theme_color').value = this.value" />
                            <input type="text" id="theme_color" name="theme_color"
                                value="{{ old('theme_color', $settings['theme_color']) }}" required
                                pattern="^#([A-Fa-f0-9]{6}|[A-Fa-f0-9]{3})$"
                                class="dark:bg-dark-900 shadow-theme-xs focus:border-brand-300 focus:ring-brand-500/10 dark:focus:border-brand-800 h-11 w-full max-w-xs rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 focus:ring-3 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90"
                                oninput="if (/^#[A-Fa-f0-9]{6}$/.test(this.value)) document.getElementById('theme_color_picker').value = this.value" />
                        </div>
                    </div>
                </div>

                <div class="mt-6">
                    <button type="submit"
                        class="bg-brand-500 hover:bg-brand-600 inline-flex items-center justify-center rounded-lg px-5 py-3 text-sm font-medium text-white transition">
                        Save settings
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            if (window.SmartBizValidation) {
                window.SmartBizValidation.init('#settings-form', {
                    currency_code: { required: true, maxlength: 10 },
                    currency_symbol: { required: true, maxlength: 10 },
                    currency_position: { required: true },
                    currency_decimals: { required: true, number: true, min: 0, max: 6 },
                    theme_color: { required: true },
                });
            }
        });
    </script>
@endpush
