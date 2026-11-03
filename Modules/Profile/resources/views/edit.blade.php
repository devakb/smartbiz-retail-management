@extends('layouts.app')

@section('content')
    <div>
        <x-common.page-breadcrumb pageTitle="Profile" />

        <div class="space-y-6">
            <div class="rounded-2xl border border-gray-200 bg-white p-5 lg:p-6 dark:border-gray-800 dark:bg-white/3">
                <h3 class="mb-5 text-lg font-semibold text-gray-800 lg:mb-7 dark:text-white/90">Personal information</h3>

                <form id="profile-form" method="POST" action="{{ route('profile.update') }}" data-ajax="true">
                    @csrf
                    @method('PUT')
                    <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
                        <div>
                            <label for="name" class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
                                Name<span class="text-error-500">*</span>
                            </label>
                            <input type="text" id="name" name="name" value="{{ old('name', $user->name) }}" required
                                class="dark:bg-dark-900 shadow-theme-xs focus:border-brand-300 focus:ring-brand-500/10 dark:focus:border-brand-800 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 placeholder:text-gray-400 focus:ring-3 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90" />
                        </div>
                        <div>
                            <label for="email" class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
                                Email<span class="text-error-500">*</span>
                            </label>
                            <input type="email" id="email" name="email" value="{{ old('email', $user->email) }}" required
                                class="dark:bg-dark-900 shadow-theme-xs focus:border-brand-300 focus:ring-brand-500/10 dark:focus:border-brand-800 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 placeholder:text-gray-400 focus:ring-3 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90" />
                        </div>
                    </div>
                    <div class="mt-6">
                        <button type="submit"
                            class="bg-brand-500 hover:bg-brand-600 inline-flex items-center justify-center rounded-lg px-5 py-3 text-sm font-medium text-white transition">
                            Save changes
                        </button>
                    </div>
                </form>
            </div>

            <div class="rounded-2xl border border-gray-200 bg-white p-5 lg:p-6 dark:border-gray-800 dark:bg-white/3">
                <h3 class="mb-5 text-lg font-semibold text-gray-800 lg:mb-7 dark:text-white/90">Change password</h3>

                <form id="password-form" method="POST" action="{{ route('profile.password') }}" data-ajax="true">
                    @csrf
                    <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
                        <div class="sm:col-span-2">
                            <label for="current_password" class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
                                Current password<span class="text-error-500">*</span>
                            </label>
                            <input type="password" id="current_password" name="current_password" required autocomplete="current-password"
                                class="dark:bg-dark-900 shadow-theme-xs focus:border-brand-300 focus:ring-brand-500/10 dark:focus:border-brand-800 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 focus:ring-3 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90" />
                        </div>
                        <div>
                            <label for="password" class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
                                New password<span class="text-error-500">*</span>
                            </label>
                            <input type="password" id="password" name="password" required autocomplete="new-password"
                                class="dark:bg-dark-900 shadow-theme-xs focus:border-brand-300 focus:ring-brand-500/10 dark:focus:border-brand-800 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 focus:ring-3 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90" />
                        </div>
                        <div>
                            <label for="password_confirmation" class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
                                Confirm password<span class="text-error-500">*</span>
                            </label>
                            <input type="password" id="password_confirmation" name="password_confirmation" required autocomplete="new-password"
                                class="dark:bg-dark-900 shadow-theme-xs focus:border-brand-300 focus:ring-brand-500/10 dark:focus:border-brand-800 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 focus:ring-3 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90" />
                        </div>
                    </div>
                    <div class="mt-6">
                        <button type="submit"
                            class="bg-brand-500 hover:bg-brand-600 inline-flex items-center justify-center rounded-lg px-5 py-3 text-sm font-medium text-white transition">
                            Update password
                        </button>
                    </div>
                </form>
            </div>

            <div class="rounded-2xl border border-gray-200 bg-white p-5 lg:p-6 dark:border-gray-800 dark:bg-white/3">
                <div class="mb-5 flex items-center justify-between gap-3 lg:mb-7">
                    <h3 class="text-lg font-semibold text-gray-800 dark:text-white/90">Login records</h3>
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-800">
                        <thead>
                            <tr class="text-left text-xs font-medium tracking-wider text-gray-500 uppercase dark:text-gray-400">
                                <th class="px-3 py-3">When</th>
                                <th class="px-3 py-3">Status</th>
                                <th class="px-3 py-3">IP</th>
                                <th class="px-3 py-3">User agent</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                            @forelse ($loginRecords as $record)
                                <tr class="text-sm text-gray-700 dark:text-gray-300">
                                    <td class="px-3 py-3 whitespace-nowrap">{{ $record->logged_in_at?->format('Y-m-d H:i') }}</td>
                                    <td class="px-3 py-3">
                                        <span @class([
                                            'inline-flex rounded-full px-2.5 py-0.5 text-xs font-medium',
                                            'bg-success-50 text-success-700 dark:bg-success-500/15 dark:text-success-400' => $record->status === 'success',
                                            'bg-error-50 text-error-700 dark:bg-error-500/15 dark:text-error-400' => $record->status !== 'success',
                                        ])>
                                            {{ ucfirst($record->status) }}
                                        </span>
                                    </td>
                                    <td class="px-3 py-3 whitespace-nowrap">{{ $record->ip_address ?? '—' }}</td>
                                    <td class="px-3 py-3 max-w-md truncate" title="{{ $record->user_agent }}">{{ $record->user_agent ?? '—' }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="px-3 py-8 text-center text-sm text-gray-500 dark:text-gray-400">
                                        No login records yet.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if ($loginRecords->hasPages())
                    <div class="mt-4">
                        {{ $loginRecords->links() }}
                    </div>
                @endif
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            if (window.SmartBizValidation) {
                window.SmartBizValidation.init('#profile-form', {
                    name: { required: true, maxlength: 255 },
                    email: { required: true, email: true, maxlength: 255 },
                });

                window.SmartBizValidation.init('#password-form', {
                    current_password: { required: true },
                    password: { required: true, minlength: 8 },
                    password_confirmation: { required: true, equalTo: '#password' },
                });
            }
        });
    </script>
@endpush
