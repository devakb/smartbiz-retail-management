@extends('layouts.app')

@section('content')
    <div>
        <x-common.page-breadcrumb pageTitle="Order details" />

        <div class="mb-5 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h3 class="text-lg font-semibold text-gray-800 dark:text-white/90">{{ $order->order_number }}</h3>
                <p class="text-sm text-gray-500">
                    Invoice: {{ $order->invoice_number ?: '—' }} ·
                    {{ ucfirst($order->status->value) }} ·
                    {{ ucfirst($order->payment_status->value) }}
                </p>
            </div>
            <div class="flex flex-wrap items-center gap-2">
                <a href="{{ route('order.invoice', $order) }}"
                    class="inline-flex items-center justify-center rounded-lg border border-gray-300 px-4 py-2.5 text-sm font-medium text-gray-700 transition hover:bg-gray-50 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-white/5">
                    Invoice
                </a>
                <a href="{{ route('order.invoice.pdf', $order) }}"
                    class="inline-flex items-center justify-center rounded-lg border border-gray-300 px-4 py-2.5 text-sm font-medium text-gray-700 transition hover:bg-gray-50 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-white/5">
                    Download PDF
                </a>
                @if ($order->status !== \App\Enums\OrderStatus::Cancelled)
                    @canany(['orders.cancel', 'orders.update'])
                        <form method="POST" action="{{ route('order.cancel', $order) }}" data-ajax="true"
                            data-confirm="Cancel this order? Stock will be reversed if deducted." class="inline">
                            @csrf
                            <button type="submit"
                                class="text-error-500 hover:text-error-600 inline-flex items-center justify-center rounded-lg border border-gray-300 px-4 py-2.5 text-sm font-medium transition dark:border-gray-700">
                                Cancel order
                            </button>
                        </form>
                    @endcanany
                @endif
                <a href="{{ route('order.index') }}"
                    class="inline-flex items-center justify-center rounded-lg border border-gray-300 px-4 py-2.5 text-sm font-medium text-gray-700 transition hover:bg-gray-50 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-white/5">
                    Back
                </a>
            </div>
        </div>

        <div class="mb-5 grid grid-cols-1 gap-4 lg:grid-cols-3">
            <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/3 lg:col-span-2">
                <h4 class="mb-3 text-base font-semibold text-gray-800 dark:text-white/90">Details</h4>
                <dl class="grid grid-cols-1 gap-3 sm:grid-cols-2 text-sm">
                    <div>
                        <dt class="text-gray-500">Customer</dt>
                        <dd class="text-gray-800 dark:text-white/90">
                            <a href="{{ route('customer.show', $order->customer) }}" class="text-brand-500 hover:text-brand-600">
                                {{ $order->customer?->name }}
                            </a>
                        </dd>
                    </div>
                    <div>
                        <dt class="text-gray-500">Created by</dt>
                        <dd class="text-gray-800 dark:text-white/90">{{ $order->user?->name }}</dd>
                    </div>
                    <div>
                        <dt class="text-gray-500">Date</dt>
                        <dd class="text-gray-800 dark:text-white/90">{{ $order->created_at?->format('Y-m-d H:i') }}</dd>
                    </div>
                    <div>
                        <dt class="text-gray-500">Notes</dt>
                        <dd class="text-gray-800 dark:text-white/90">{{ $order->notes ?: '—' }}</dd>
                    </div>
                </dl>

                @can('orders.update')
                    @if ($order->status === \App\Enums\OrderStatus::Draft)
                        <form method="POST" action="{{ route('order.status', $order) }}" data-ajax="true" class="mt-4">
                            @csrf
                            @method('PUT')
                            <input type="hidden" name="status" value="confirmed" />
                            <button type="submit"
                                class="bg-brand-500 hover:bg-brand-600 inline-flex items-center justify-center rounded-lg px-4 py-2.5 text-sm font-medium text-white transition">
                                Confirm order
                            </button>
                        </form>
                    @endif
                @endcan
            </div>

            <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/3">
                <h4 class="mb-3 text-base font-semibold text-gray-800 dark:text-white/90">Totals</h4>
                <dl class="space-y-2 text-sm">
                    <div class="flex justify-between">
                        <dt class="text-gray-500">Subtotal</dt>
                        <dd>{{ \App\Support\Money::format($order->subtotal) }}</dd>
                    </div>
                    <div class="flex justify-between">
                        <dt class="text-gray-500">Discount</dt>
                        <dd>{{ \App\Support\Money::format($order->discount_amount) }}</dd>
                    </div>
                    <div class="flex justify-between">
                        <dt class="text-gray-500">Tax</dt>
                        <dd>{{ \App\Support\Money::format($order->tax_amount) }}</dd>
                    </div>
                    <div class="flex justify-between border-t border-gray-200 pt-2 font-semibold dark:border-gray-800">
                        <dt>Total</dt>
                        <dd>{{ \App\Support\Money::format($order->total) }}</dd>
                    </div>
                </dl>
            </div>
        </div>

        <div class="mb-5 rounded-2xl border border-gray-200 bg-white p-5 lg:p-6 dark:border-gray-800 dark:bg-white/3">
            <h4 class="mb-4 text-base font-semibold text-gray-800 dark:text-white/90">Items</h4>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-800">
                    <thead>
                        <tr class="text-left text-xs font-medium tracking-wider text-gray-500 uppercase dark:text-gray-400">
                            <th class="px-3 py-3">Product</th>
                            <th class="px-3 py-3">UPC / SKU</th>
                            <th class="px-3 py-3 text-right">Qty</th>
                            <th class="px-3 py-3 text-right">Unit</th>
                            <th class="px-3 py-3 text-right">Tax %</th>
                            <th class="px-3 py-3 text-right">Line total</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                        @foreach ($order->items as $item)
                            <tr class="text-sm text-gray-700 dark:text-gray-300">
                                <td class="px-3 py-3">{{ $item->product_name }}</td>
                                <td class="px-3 py-3 whitespace-nowrap">{{ $item->upc ?: '—' }} / {{ $item->sku ?: '—' }}</td>
                                <td class="px-3 py-3 text-right">{{ $item->quantity }}</td>
                                <td class="px-3 py-3 text-right">{{ \App\Support\Money::format($item->unit_price) }}</td>
                                <td class="px-3 py-3 text-right">{{ $item->tax_percent }}</td>
                                <td class="px-3 py-3 text-right">{{ \App\Support\Money::format($item->line_total) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <div class="rounded-2xl border border-gray-200 bg-white p-5 lg:p-6 dark:border-gray-800 dark:bg-white/3">
            <div class="mb-4 flex items-center justify-between">
                <h4 class="text-base font-semibold text-gray-800 dark:text-white/90">Payments</h4>
            </div>

            <div class="overflow-x-auto mb-5">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-800">
                    <thead>
                        <tr class="text-left text-xs font-medium tracking-wider text-gray-500 uppercase dark:text-gray-400">
                            <th class="px-3 py-3">Method</th>
                            <th class="px-3 py-3">Amount</th>
                            <th class="px-3 py-3">Reference</th>
                            <th class="px-3 py-3">Paid at</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                        @forelse ($order->payments as $payment)
                            <tr class="text-sm text-gray-700 dark:text-gray-300">
                                <td class="px-3 py-3">{{ ucfirst($payment->method->value) }}</td>
                                <td class="px-3 py-3">{{ \App\Support\Money::format($payment->amount) }}</td>
                                <td class="px-3 py-3">{{ $payment->reference ?: '—' }}</td>
                                <td class="px-3 py-3">{{ $payment->paid_at?->format('Y-m-d H:i') }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-3 py-6 text-center text-sm text-gray-500">No payments recorded.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @can('orders.update')
                @if ($order->status !== \App\Enums\OrderStatus::Cancelled && $order->payment_status !== \App\Enums\PaymentStatus::Paid)
                    <form id="payment-form" method="POST" action="{{ route('order.payments.store', $order) }}" data-ajax="true"
                        class="grid grid-cols-1 gap-3 md:grid-cols-4">
                        @csrf
                        <div>
                            <label class="mb-1 block text-xs text-gray-500">Method</label>
                            <select name="method" required class="js-tom-select" data-placeholder="Method">
                                @foreach (\App\Enums\PaymentMethod::cases() as $method)
                                    <option value="{{ $method->value }}">{{ ucfirst($method->value) }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="mb-1 block text-xs text-gray-500">Amount</label>
                            <input type="number" name="amount" step="0.01" min="0.01" required
                                value="{{ max(0, (float) $order->total - (float) $order->payments->sum('amount')) }}"
                                class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-3 text-sm dark:border-gray-700 dark:bg-gray-900" />
                        </div>
                        <div>
                            <label class="mb-1 block text-xs text-gray-500">Reference</label>
                            <input type="text" name="reference"
                                class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-3 text-sm dark:border-gray-700 dark:bg-gray-900" />
                        </div>
                        <div class="flex items-end">
                            <button type="submit"
                                class="bg-brand-500 hover:bg-brand-600 h-11 w-full rounded-lg px-4 text-sm font-medium text-white transition">
                                Record payment
                            </button>
                        </div>
                    </form>
                @endif
            @endcan
        </div>
    </div>
@endsection
