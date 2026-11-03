@extends('layouts.app')

@section('content')
    <div>
        <x-common.page-breadcrumb pageTitle="Customer details" />

        <div class="mb-5 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <h3 class="text-lg font-semibold text-gray-800 dark:text-white/90">
                {{ $customer->name }}
                @if ($customer->is_walk_in)
                    <span class="text-sm font-normal text-gray-500">(Walk-in)</span>
                @endif
            </h3>
            <div class="flex items-center gap-2">
                <a href="{{ route('customer.edit', $customer) }}"
                    class="bg-brand-500 hover:bg-brand-600 inline-flex items-center justify-center rounded-lg px-4 py-2.5 text-sm font-medium text-white transition">
                    Edit
                </a>
                <a href="{{ route('customer.index') }}"
                    class="inline-flex items-center justify-center rounded-lg border border-gray-300 px-4 py-2.5 text-sm font-medium text-gray-700 transition hover:bg-gray-50 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-white/5">
                    Back
                </a>
            </div>
        </div>

        <div class="mb-5 grid grid-cols-1 gap-4 md:grid-cols-3">
            <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/3">
                <p class="text-xs text-gray-500 uppercase">Orders</p>
                <p class="mt-1 text-2xl font-semibold text-gray-800 dark:text-white/90">{{ $orderCount }}</p>
            </div>
            <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/3">
                <p class="text-xs text-gray-500 uppercase">Total spent</p>
                <p class="mt-1 text-2xl font-semibold text-gray-800 dark:text-white/90">{{ \App\Support\Money::format($totalSpent) }}</p>
            </div>
            <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/3">
                <p class="text-xs text-gray-500 uppercase">Outstanding</p>
                <p class="mt-1 text-2xl font-semibold text-gray-800 dark:text-white/90">{{ \App\Support\Money::format($outstanding) }}</p>
            </div>
        </div>

        <div class="mb-5 rounded-2xl border border-gray-200 bg-white p-5 lg:p-6 dark:border-gray-800 dark:bg-white/3">
            <h4 class="mb-4 text-base font-semibold text-gray-800 dark:text-white/90">Contact</h4>
            <dl class="grid grid-cols-1 gap-4 sm:grid-cols-2 text-sm">
                <div>
                    <dt class="text-gray-500">Email</dt>
                    <dd class="text-gray-800 dark:text-white/90">{{ $customer->email ?: '—' }}</dd>
                </div>
                <div>
                    <dt class="text-gray-500">Phone</dt>
                    <dd class="text-gray-800 dark:text-white/90">{{ $customer->phone ?: '—' }}</dd>
                </div>
                <div>
                    <dt class="text-gray-500">Billing address</dt>
                    <dd class="text-gray-800 dark:text-white/90 whitespace-pre-line">{{ $customer->billing_address ?: '—' }}</dd>
                </div>
                <div>
                    <dt class="text-gray-500">Shipping address</dt>
                    <dd class="text-gray-800 dark:text-white/90 whitespace-pre-line">{{ $customer->shipping_address ?: '—' }}</dd>
                </div>
            </dl>
        </div>

        <div class="rounded-2xl border border-gray-200 bg-white p-5 lg:p-6 dark:border-gray-800 dark:bg-white/3">
            <h4 class="mb-4 text-base font-semibold text-gray-800 dark:text-white/90">Purchase history</h4>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-800">
                    <thead>
                        <tr class="text-left text-xs font-medium tracking-wider text-gray-500 uppercase dark:text-gray-400">
                            <th class="px-3 py-3">Order</th>
                            <th class="px-3 py-3">Date</th>
                            <th class="px-3 py-3">Status</th>
                            <th class="px-3 py-3">Payment</th>
                            <th class="px-3 py-3 text-right">Total</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                        @forelse ($orders as $order)
                            <tr class="text-sm text-gray-700 dark:text-gray-300">
                                <td class="px-3 py-3 whitespace-nowrap">
                                    <a href="{{ route('order.show', $order) }}" class="text-brand-500 hover:text-brand-600 font-medium">
                                        {{ $order->order_number }}
                                    </a>
                                </td>
                                <td class="px-3 py-3 whitespace-nowrap">{{ $order->created_at?->format('Y-m-d H:i') }}</td>
                                <td class="px-3 py-3 whitespace-nowrap">{{ ucfirst($order->status->value) }}</td>
                                <td class="px-3 py-3 whitespace-nowrap">{{ ucfirst($order->payment_status->value) }}</td>
                                <td class="px-3 py-3 whitespace-nowrap text-right">{{ \App\Support\Money::format($order->total) }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-3 py-8 text-center text-sm text-gray-500 dark:text-gray-400">
                                    No orders yet.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection
