@extends('layouts.app')

@section('content')
    <div>
        <x-common.list-header
            title="Orders"
            description="Review sales orders, payment status, and invoices."
        >
            <x-slot:actions>
                @can('orders.create')
                    <x-common.btn-primary href="{{ route('order.create') }}">
                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M12 5v14M5 12h14" stroke-linecap="round" />
                        </svg>
                        Create Order
                    </x-common.btn-primary>
                @endcan
            </x-slot:actions>
        </x-common.list-header>

        <x-common.stats-row>
            <x-common.stat-card label="Total Orders" :value="$stats['total']" tone="brand" />
            <x-common.stat-card label="Confirmed" :value="$stats['confirmed']" tone="success" />
            <x-common.stat-card label="Draft" :value="$stats['draft']" tone="warning" />
            <x-common.stat-card label="Cancelled" :value="$stats['cancelled']" tone="error" />
        </x-common.stats-row>

        <x-common.list-toolbar :action="route('order.index')" :reset-url="route('order.index')">
            <x-common.search-field name="search" :value="$filters['search']" placeholder="Search order / invoice #..." />
            <x-common.select-field name="status" placeholder="All Statuses" :allow-clear="true" wrapper-class="w-full xl:w-40">
                <option value="">All Statuses</option>
                @foreach ($statuses as $status)
                    <option value="{{ $status->value }}" @selected($filters['status'] === $status->value)>
                        {{ ucfirst($status->value) }}
                    </option>
                @endforeach
            </x-common.select-field>
            <x-common.select-field name="payment_status" placeholder="All Payments" :allow-clear="true" wrapper-class="w-full xl:w-40">
                <option value="">All Payments</option>
                @foreach ($paymentStatuses as $paymentStatus)
                    <option value="{{ $paymentStatus->value }}" @selected($filters['payment_status'] === $paymentStatus->value)>
                        {{ ucfirst($paymentStatus->value) }}
                    </option>
                @endforeach
            </x-common.select-field>
            <x-common.select-field name="customer_id" placeholder="All Customers" :allow-clear="true">
                <option value="">All Customers</option>
                @foreach ($customers as $customer)
                    <option value="{{ $customer->id }}" @selected((string) $filters['customer_id'] === (string) $customer->id)>
                        {{ $customer->name }}
                    </option>
                @endforeach
            </x-common.select-field>
            <input type="date" name="date_from" value="{{ $filters['date_from'] }}"
                class="dark:bg-dark-900 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-3 text-sm dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 lg:w-40" />
            <input type="date" name="date_to" value="{{ $filters['date_to'] }}"
                class="dark:bg-dark-900 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-3 text-sm dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 lg:w-40" />
        </x-common.list-toolbar>

        <div class="overflow-hidden rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/3">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-800">
                    <thead class="bg-gray-50 dark:bg-white/5">
                        <tr class="text-left text-xs font-medium tracking-wider text-gray-500 uppercase dark:text-gray-400">
                            <th class="px-4 py-3">Order #</th>
                            <th class="px-4 py-3">Customer</th>
                            <th class="px-4 py-3">Date</th>
                            <th class="px-4 py-3">Status</th>
                            <th class="px-4 py-3">Payment</th>
                            <th class="px-4 py-3 text-end">Total</th>
                            <th class="px-4 py-3 text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                        @forelse ($orders as $order)
                            <tr class="text-sm text-gray-700 dark:text-gray-300">
                                <td class="px-4 py-3 whitespace-nowrap font-medium text-gray-800 dark:text-white/90">{{ $order->order_number }}</td>
                                <td class="px-4 py-3 whitespace-nowrap">{{ $order->customer?->name }}</td>
                                <td class="px-4 py-3 whitespace-nowrap">{{ $order->created_at?->format('Y-m-d H:i') }}</td>
                                <td class="px-4 py-3 whitespace-nowrap capitalize">{{ $order->status->value }}</td>
                                <td class="px-4 py-3 whitespace-nowrap capitalize">{{ $order->payment_status->value }}</td>
                                <td class="px-4 py-3 whitespace-nowrap text-end">{{ \App\Support\Money::format($order->total) }}</td>
                                <td class="px-4 py-3 whitespace-nowrap text-end">
                                    <a href="{{ route('order.show', $order) }}"
                                        class="text-brand-500 hover:text-brand-600 text-sm font-medium">View</a>
                                </td>
                            </tr>
                        @empty
                            <x-common.empty-state
                                colspan="7"
                                title="No orders found"
                                description="Try adjusting your filters or create a new order to get started."
                            >
                                <x-slot:actions>
                                    @can('orders.create')
                                        <x-common.btn-primary href="{{ route('order.create') }}">Create Order</x-common.btn-primary>
                                    @endcan
                                    @if ($hasFilters)
                                        <x-common.btn-secondary href="{{ route('order.index') }}">Clear Filters</x-common.btn-secondary>
                                    @endif
                                </x-slot:actions>
                            </x-common.empty-state>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if ($orders->hasPages())
                <div class="border-t border-gray-200 px-4 py-3 dark:border-gray-800">{{ $orders->links() }}</div>
            @endif
        </div>
    </div>
@endsection
