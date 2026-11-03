@extends('layouts.app')

@section('content')
    <div>
        <x-common.page-breadcrumb pageTitle="Create user" />

        <div class="rounded-2xl border border-gray-200 bg-white p-5 lg:p-6 dark:border-gray-800 dark:bg-white/3">
            <h3 class="mb-5 text-lg font-semibold text-gray-800 lg:mb-7 dark:text-white/90">Create user</h3>

            <form id="user-create-form" method="POST" action="{{ route('users.store') }}" data-ajax="true">
                @csrf
                @include('user::partials.form', ['user' => null, 'roles' => $roles])

                <div class="mt-6 flex items-center gap-3">
                    <button type="submit"
                        class="bg-brand-500 hover:bg-brand-600 inline-flex items-center justify-center rounded-lg px-5 py-3 text-sm font-medium text-white transition">
                        Create user
                    </button>
                    <a href="{{ route('users.index') }}"
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
                window.SmartBizValidation.init('#user-create-form', {
                    name: { required: true, maxlength: 255 },
                    email: { required: true, email: true, maxlength: 255 },
                    password: { required: true, minlength: 8 },
                    password_confirmation: { required: true, equalTo: '#password' },
                    role: { required: true },
                });
            }
        });
    </script>
@endpush
