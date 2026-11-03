@extends('layouts.app')

@section('content')
    <div class="space-y-6">
        <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h3 class="text-lg font-semibold text-gray-800 dark:text-white/90">SmartBiz Dashboard</h3>
                <p class="text-sm text-gray-500 dark:text-gray-400">Live aggregates from products, customers, orders, and inventory.</p>
            </div>
            @can('reports.view')
                <a href="{{ route('report.index') }}"
                    class="bg-brand-500 hover:bg-brand-600 inline-flex items-center justify-center rounded-lg px-4 py-2.5 text-sm font-medium text-white">
                    Open reports
                </a>
            @endcan
        </div>

        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">
            <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/3">
                <p class="text-sm text-gray-500 dark:text-gray-400">Products</p>
                <p class="mt-2 text-2xl font-semibold text-gray-800 dark:text-white/90">{{ number_format($widgets['total_products']) }}</p>
            </div>
            <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/3">
                <p class="text-sm text-gray-500 dark:text-gray-400">Customers</p>
                <p class="mt-2 text-2xl font-semibold text-gray-800 dark:text-white/90">{{ number_format($widgets['total_customers']) }}</p>
            </div>
            <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/3">
                <p class="text-sm text-gray-500 dark:text-gray-400">Orders</p>
                <p class="mt-2 text-2xl font-semibold text-gray-800 dark:text-white/90">{{ number_format($widgets['total_orders']) }}</p>
            </div>
            <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/3">
                <p class="text-sm text-gray-500 dark:text-gray-400">Low stock</p>
                <p class="mt-2 text-2xl font-semibold text-gray-800 dark:text-white/90">{{ number_format($widgets['low_stock_count']) }}</p>
            </div>
            <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/3">
                <p class="text-sm text-gray-500 dark:text-gray-400">Today's sales</p>
                <p class="mt-2 text-2xl font-semibold text-gray-800 dark:text-white/90">{{ \App\Support\Money::format($widgets['todays_sales']) }}</p>
            </div>
            <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/3">
                <p class="text-sm text-gray-500 dark:text-gray-400">Monthly sales</p>
                <p class="mt-2 text-2xl font-semibold text-gray-800 dark:text-white/90">{{ \App\Support\Money::format($widgets['monthly_sales']) }}</p>
            </div>
            <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/3 sm:col-span-2">
                <p class="text-sm text-gray-500 dark:text-gray-400">Inventory value (cost)</p>
                <p class="mt-2 text-2xl font-semibold text-gray-800 dark:text-white/90">{{ \App\Support\Money::format($widgets['inventory_value']) }}</p>
            </div>
        </div>

        <div class="grid grid-cols-1 gap-4 xl:grid-cols-2">
            <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/3">
                <h4 class="mb-4 text-base font-semibold text-gray-800 dark:text-white/90">Sales (last 7 days)</h4>
                @php
                    $salesChartConfig = [
                        'categories' => $widgets['sales_chart']['categories'],
                        'data' => $widgets['sales_chart']['data'],
                        'seriesName' => 'Sales',
                    ];
                @endphp
                <div data-dashboard-chart="smartbiz-sales" data-config='@json($salesChartConfig)'></div>
            </div>

            <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/3">
                <h4 class="mb-4 text-base font-semibold text-gray-800 dark:text-white/90">Best-selling products</h4>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-800">
                        <thead>
                            <tr class="text-left text-xs font-medium tracking-wider text-gray-500 uppercase dark:text-gray-400">
                                <th class="px-3 py-3">Product</th>
                                <th class="px-3 py-3 text-right">Qty</th>
                                <th class="px-3 py-3 text-right">Revenue</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 dark:divide-gray-800">
                            @forelse ($widgets['best_sellers'] as $item)
                                <tr class="text-sm text-gray-700 dark:text-gray-300">
                                    <td class="px-3 py-3">{{ $item->product_name }}</td>
                                    <td class="px-3 py-3 text-right">{{ $item->qty_sold }}</td>
                                    <td class="px-3 py-3 text-right">{{ \App\Support\Money::format($item->revenue) }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" class="px-3 py-6 text-center text-sm text-gray-500">No sales yet.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/3">
            <h4 class="mb-4 text-base font-semibold text-gray-800 dark:text-white/90">Recent orders</h4>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-800">
                    <thead>
                        <tr class="text-left text-xs font-medium tracking-wider text-gray-500 uppercase dark:text-gray-400">
                            <th class="px-3 py-3">Order #</th>
                            <th class="px-3 py-3">Customer</th>
                            <th class="px-3 py-3">Date</th>
                            <th class="px-3 py-3">Status</th>
                            <th class="px-3 py-3 text-right">Total</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-gray-800">
                        @forelse ($widgets['recent_orders'] as $order)
                            <tr class="text-sm text-gray-700 dark:text-gray-300">
                                <td class="px-3 py-3">
                                    @can('orders.view')
                                        <a href="{{ route('order.show', $order) }}" class="text-brand-500 hover:underline">{{ $order->order_number }}</a>
                                    @else
                                        {{ $order->order_number }}
                                    @endcan
                                </td>
                                <td class="px-3 py-3">{{ $order->customer?->name ?? '—' }}</td>
                                <td class="px-3 py-3">{{ $order->created_at?->format('Y-m-d H:i') }}</td>
                                <td class="px-3 py-3">{{ ucfirst($order->status->value) }}</td>
                                <td class="px-3 py-3 text-right">{{ \App\Support\Money::format($order->total) }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-3 py-6 text-center text-sm text-gray-500">No orders yet.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection
